<?php

namespace App\Support\Payments;

use App\Models\BnplSettlement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Support\Fmt;
use App\Support\JDate;
use App\Support\SellerNotifier;
use App\Support\Wallet\WalletService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * v0.30.0 — the monthly settlement engine of the BNPL (installment) gateways
 * (دیجی‌پی / اسنپ‌پی / ترب‌پی).
 *
 * WHY: these gateways take the customer's FIRST installment at checkout and
 * let the customer pay the rest over the following months, while they settle
 * the MERCHANT monthly («خرید ۲۵ ماه → تسویهٔ سرِ ماه»). So a paid BNPL order
 * must NOT credit seller wallets instantly — it creates one PENDING
 * BnplSettlement per shop, rendered faded/disabled in the seller's wallet
 * («پرداخت با اسنپ‌پی — بعد از تسویه به کیف پول شما واریز می‌شود») and later
 * deposited by the daily `bnpl:settle` cron (on the gateway's configured
 * settlement day) or by the admin's manual «واریز دستی».
 *
 * Money math per shop (identical base to WalletService):
 *   amount = items_total + bnpl_fee(seller's own surcharge) − commission(items_total)
 * — the fee compensates the seller for the installment sale and is NEVER
 * commissioned; the shipping stays with the platform exactly like direct
 * gateways.
 */
final class BnplSettlements
{
    /**
     * Create the pending settlement rows of a JUST-PAID BNPL order
     * (called from WalletService::creditOrder instead of the wallet
     * credit). Idempotent through the order_id existence check — the
     * double-callback race can call finalizePaid only once, but the guard
     * keeps the invariant airtight.
     */
    public static function createForOrder(Order $order): void
    {
        $shares = OrderItem::query()
            ->where('order_id', $order->id)
            ->selectRaw('shop_id, SUM(unit_price * quantity) as total')
            ->groupBy('shop_id')
            ->get();

        $gateway = (string) $order->bnpl_gateway;
        $fees = (array) ($order->bnpl_fees ?? []); // {shop_id: fee} frozen at checkout
        $paidAt = $order->paid_at ?? now();
        $snapshot = [];

        foreach ($shares as $share) {
            $shopId = (int) $share->shop_id;
            $itemsTotal = max(0, (int) $share->total);

            $shop = Shop::query()->find($shopId);

            if (! $shop || ! $shop->seller_id) {
                continue;
            }

            $sellerId = (int) $shop->seller_id;
            $fee = (int) ($fees[(string) $shopId] ?? $fees[$shopId] ?? 0);

            $exists = BnplSettlement::query()
                ->where('order_id', $order->id)
                ->where('seller_id', $sellerId)
                ->exists();

            if ($exists) {
                continue; // idempotent
            }

            /** @var Seller|null $seller */
            $seller = Seller::query()->find($sellerId);

            [$commissionType, $commissionValue] = $seller?->resolvedCommission() ?? ['percent', 0];
            $commission = $itemsTotal > 0 && $seller ? $seller->commissionFor($itemsTotal) : 0;
            $amount = max(0, $itemsTotal + $fee - $commission);

            $settlement = BnplSettlement::query()->create([
                'order_id' => $order->id,
                'seller_id' => $sellerId,
                'wallet_id' => Wallet::ensureForSeller($sellerId)->id,
                'gateway' => $gateway,
                'amount' => $amount,
                'commission' => $commission,
                'fee' => $fee,
                'items_total' => $itemsTotal,
                'status' => 'pending',
                'due_date' => self::nextSettlementDate($gateway, $paidAt),
            ]);

            $snapshot[] = [
                'shop_id' => $shopId,
                'shop_name' => (string) ($shop->name ?? ''),
                'items_total' => $itemsTotal,
                'commission_type' => $commissionType,
                'commission_value' => (int) $commissionValue,
                'commission_amount' => $commission,
                'payout' => $amount,
                // v0.30.0 — WHERE/WHEN this payout will actually land
                'settlement' => 'bnpl',
                'bnpl_gateway' => $gateway,
                'bnpl_fee' => $fee,
                'bnpl_settlement_id' => $settlement->id,
                'bnpl_due_date' => $settlement->due_date->toDateString(),
            ];

            // seller notification: the money is NOT in the wallet yet — the
            // faded wallet row + this message make the monthly rhythm clear
            SellerNotifier::notify(
                $sellerId,
                'wallet.deposit',
                'پرداخت اقساطی ثبت شد ⏳ '.Fmt::money($amount),
                'سفارش #'.WalletService::orderCode($order).' با '
                    .PaymentService::BNPL_GATEWAYS[$gateway].' پرداخت شد. مبلغ '
                    .Fmt::money($amount).' پس از تسویهٔ '
                    .PaymentService::BNPL_GATEWAYS[$gateway].' (سررسید '
                    .JDate::date($settlement->due_date)
                    .') به کیف پول شما واریز می‌شود.',
                route('seller.wallet'),
                'wallet',
                $settlement,
            );
        }

        // Persist the settlement snapshot (mirrors the sheba/wallet paths —
        // the admin income reports stay complete).
        if ($snapshot !== [] && ! $order->commissions) {
            $order->forceFill(['commissions' => $snapshot])->save();
        }
    }

    /**
     * v0.31.0 — the due date of a BNPL settlement: the NEXT occurrence of
     * the gateway's configured settlement day strictly after the purchase
     * («خرید ۲۵ → روزِ ۱ ماه بعد»؛ «خرید ۵، روزِ ۱۰ → روزِ ۱۰ همین ماه»),
     * pushed (frequency − 1) months further by the gateway's settlement
     * frequency (هر ۲ ماه یک‌بار → +۱ ماه دیگر). Day is clamped to 1..29
     * (no month is shorter than 29 days, so the date never overflows) and
     * addMonthNoOverflow keeps even a day-29 purchase inside its months.
     */
    public static function nextSettlementDate(string $gateway, Carbon $purchasedAt): Carbon
    {
        $day = PaymentService::bnplSettlementDay($gateway);
        $frequency = PaymentService::bnplSettlementFrequency($gateway);

        $due = $purchasedAt->copy()->startOfDay()->day($day);

        if (! $due->greaterThan($purchasedAt->copy()->startOfDay())) {
            $due = $due->addMonthNoOverflow();
        }

        return $frequency > 1 ? $due->addMonthNoOverflow($frequency - 1) : $due;
    }

    /**
     * Deposit ONE pending settlement into the seller wallet and mark it
     * paid. Used by BOTH the daily cron and the admin's manual action
     * (manual=true when the admin clicked the button). Idempotent: the
     * pending→paid claim is atomic (row lock) and the wallet transaction
     * carries wallet_id + order_id so a duplicate deposit is impossible.
     *
     * @return bool true when this call performed the deposit
     */
    public static function deposit(BnplSettlement $settlement, bool $manual = false, ?string $note = null): bool
    {
        // claim pending→paid FIRST (atomic); only the winner credits money
        $claimed = $settlement->claimForDeposit();

        if (! $claimed) {
            return false;
        }

        $wallet = Wallet::ensureForSeller($claimed->seller_id);

        // (re)bind the wallet this deposit actually landed in
        if ((int) $claimed->wallet_id !== (int) $wallet->id) {
            $claimed->forceFill(['wallet_id' => $wallet->id])->save();
        }

        if ($claimed->amount > 0) {
            $wallet->increment('balance', $claimed->amount);
        }

        $description = ($manual ? 'تسویه دستی اقساطی — ' : 'تسویه خودکار اقساطی — ')
            .'سفارش #'.WalletService::orderCode($claimed->order)
            .' از طریق '.PaymentService::BNPL_GATEWAYS[$claimed->gateway]
            .' (کالا: '.Fmt::money($claimed->items_total)
            .($claimed->fee > 0 ? ' — کارمزد اقساط: '.Fmt::money($claimed->fee) : '')
            .($claimed->commission > 0 ? ' — کمیسیون: '.Fmt::money($claimed->commission) : '').')';

        $tx = WalletTransaction::query()->create([
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_BNPL,
            'amount' => $claimed->amount,
            'commission' => $claimed->commission,
            'status' => WalletTransaction::STATUS_DONE,
            'order_id' => $claimed->order_id,
            'description' => $description.($note !== null && trim($note) !== '' ? ' — '.$note : ''),
        ]);

        $claimed->forceFill([
            'wallet_transaction_id' => $tx->id,
            'manual' => $manual,
            'note' => $note !== null && trim($note) !== '' ? mb_substr(trim($note), 0, 190) : null,
        ])->save();

        SellerNotifier::notify(
            $claimed->seller_id,
            'wallet.deposit',
            'تسویه '.PaymentService::BNPL_GATEWAYS[$claimed->gateway].' واریز شد 💰 '.Fmt::money($claimed->amount),
            $description,
            route('seller.wallet'),
            'wallet',
            $tx,
        );

        return true;
    }

    /**
     * v0.31.0 — the AUTO settlement sweep query: PENDING settlements whose
     * due date has arrived (due_date <= today) that belong to gateways in
     * AUTO mode. Manual-mode gateways are NEVER touched by the cron — the
     * admin deposits their rows by hand («واریز دستی» / «واریز گروهی») —
     * and the bnpl:settle dry-run counts exactly this scope.
     */
    public static function autoDueQuery(): Builder
    {
        $autoGateways = collect(PaymentService::BNPL_GATEWAYS)
            ->keys()
            ->filter(fn (string $gateway): bool => PaymentService::bnplSettlementMode($gateway) === 'auto')
            ->values();

        return BnplSettlement::query()
            ->whereIn('gateway', $autoGateways->all())
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', today());
    }

    /**
     * The daily cron sweep: every PENDING settlement of AUTO-mode gateways
     * whose due date has arrived (due_date <= today) gets deposited.
     * Returns [deposited, amount].
     *
     * @return array{0: int, 1: int}
     */
    public static function settleDue(): array
    {
        $deposited = 0;
        $amount = 0;

        self::autoDueQuery()
            ->with(['order:id,bnpl_gateway,bnpl_fee'])
            ->orderBy('due_date')
            ->chunkById(200, function ($rows) use (&$deposited, &$amount): void {
                foreach ($rows as $row) {
                    try {
                        if (self::deposit($row, manual: false)) {
                            $deposited++;
                            $amount += (int) $row->amount;
                        }
                    } catch (Throwable $e) {
                        report($e);
                        logger()->warning('bnpl:settle deposit failed for settlement #'.$row->id.': '.$e->getMessage());
                    }
                }
            });

        return [$deposited, $amount];
    }
}
