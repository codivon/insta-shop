<?php

namespace App\Support\Wallet;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Support\Banking;
use App\Support\Fmt;
use App\Support\SellerNotifier;
use Illuminate\Support\Facades\DB;

/**
 * Wallet ledger service.
 *
 * A paid order credits every shop that has items in it — AFTER deducting
 * the platform commission (batch 80):
 *   • base = that shop's goods total (unit_price × quantity) — the
 *     shipping cost and the discount code are NEVER part of the base;
 *   • the SELLER's own commission override (percent|fixed) wins, else the
 *     global default from settings («تنظیمات فروشگاه‌ها»);
 *   • the deposit report states exactly how much commission was cut.
 * Withdrawals reserve the balance immediately (pending); rejecting a
 * withdrawal refunds it.
 */
final class WalletService
{
    /** Credit the shops of a paid order (called inside finalizePaid). */
    public static function creditOrder(Order $order): void
    {
        // Query OrderItem DIRECTLY — the Order::items() relation appends
        // orderBy('id') which would inject "ORDER BY id" into this GROUP BY
        // aggregate and crash under MySQL 8's ONLY_FULL_GROUP_BY
        // (SQLSTATE[42000] 1055: "Expression #1 of ORDER BY clause is not
        // in GROUP BY clause"). Aggregates must never inherit the
        // relation's default ordering.
        $shares = OrderItem::query()
            ->where('order_id', $order->id)
            ->selectRaw('shop_id, SUM(unit_price * quantity) as total')
            ->groupBy('shop_id')
            ->get();

        $snapshot = [];

        foreach ($shares as $share) {
            $shopId = (int) $share->shop_id;
            $itemsTotal = max(0, (int) $share->total);

            // order_items carry shop_id — wallets belong to the seller
            $shop = Shop::query()->find($shopId);

            if (! $shop || ! $shop->seller_id) {
                continue;
            }

            $sellerId = (int) $shop->seller_id;

            /** @var Seller $seller */
            $seller = Seller::query()->find($sellerId);

            [$commissionType, $commissionValue] = $seller?->resolvedCommission() ?? ['percent', 0];
            $commission = $itemsTotal > 0 && $seller ? $seller->commissionFor($itemsTotal) : 0;
            $payout = max(0, $itemsTotal - $commission);

            $snapshot[] = [
                'shop_id' => $shopId,
                'shop_name' => (string) ($shop->name ?? ''),
                'items_total' => $itemsTotal,
                'commission_type' => $commissionType,
                'commission_value' => (int) $commissionValue,
                'commission_amount' => $commission,
                'payout' => $payout,
            ];

            if ($payout <= 0) {
                continue;
            }

            $wallet = Wallet::ensureForSeller($sellerId);

            $exists = WalletTransaction::query()
                ->where('wallet_id', $wallet->id)
                ->where('order_id', $order->id)
                ->exists();

            if ($exists) {
                continue; // idempotent
            }

            $wallet->increment('balance', $payout);

            WalletTransaction::query()->create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransaction::TYPE_DEPOSIT,
                'amount' => $payout,
                'commission' => $commission,
                'status' => WalletTransaction::STATUS_DONE,
                'order_id' => $order->id,
                'description' => self::depositDescription($order, $itemsTotal, $commission, $commissionType, $commissionValue),
            ]);

            // seller notification: «واریز به کیف پول»
            SellerNotifier::notify(
                $sellerId,
                'wallet.deposit',
                'واریز به کیف پول 💰 '.Fmt::money($payout),
                self::depositDescription($order, $itemsTotal, $commission, $commissionType, $commissionValue),
                route('seller.wallet'),
                'wallet',
            );
        }

        // Persist the settlement snapshot (used by the admin income page).
        if ($snapshot !== [] && ! $order->commissions) {
            $order->forceFill(['commissions' => $snapshot])->save();
        }
    }

    /** Deposit report text — must state the deducted commission explicitly. */
    private static function depositDescription(Order $order, int $itemsTotal, int $commission, string $type, int $value): string
    {
        $code = self::orderCode($order);
        $base = 'سهم فروش از سفارش #'.$code.' — کالا: '.Fmt::money($itemsTotal);

        if ($commission <= 0) {
            return $base.' — بدون کمیسیون';
        }

        $label = $type === 'fixed'
            ? 'مبلغ ثابت '.Fmt::money($value)
            : Fmt::toFa($value).'٪';

        return $base.' — کمیسیون ('.$label.'): '.Fmt::money($commission).' کسر شد';
    }

    /**
     * Reserve balance and log a pending withdrawal request (null = insufficient).
     *
     * The destination is the seller's DEFAULT bank CARD (the request box no
     * longer asks for a sheba) — both the masked card number and the card's
     * sheba are snapshotted so the admin can actually pay it out later.
     */
    public static function requestWithdrawal(Wallet $wallet, int $amount, string $sheba, ?string $cardNumber = null): ?WalletTransaction
    {
        return DB::transaction(function () use ($wallet, $amount, $sheba, $cardNumber) {
            $fresh = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->first();

            if (! $fresh || $fresh->balance < $amount) {
                return null;
            }

            $fresh->decrement('balance', $amount);

            return WalletTransaction::query()->create([
                'wallet_id' => $fresh->id,
                'type' => WalletTransaction::TYPE_WITHDRAWAL,
                'amount' => $amount,
                'status' => WalletTransaction::STATUS_PENDING,
                'description' => $cardNumber
                    ? 'درخواست برداشت به کارت '.Banking::maskCard($cardNumber)
                    : 'درخواست برداشت به شبا '.$sheba,
                'sheba' => $sheba,
                'card_number' => $cardNumber,
            ]);
        });
    }

    /**
     * Admin approves a pending withdrawal (money was reserved at request time).
     * The optional note (توضیحات) is shown to the seller verbatim.
     */
    public static function approveWithdrawal(WalletTransaction $tx, ?string $adminNote = null): bool
    {
        if (! $tx->isWithdrawal() || $tx->status !== WalletTransaction::STATUS_PENDING) {
            return false;
        }

        $destination = $tx->card_number
            ? 'کارت '.Banking::maskCard($tx->card_number)
            : (string) $tx->sheba;

        $tx->update([
            'status' => WalletTransaction::STATUS_DONE,
            'admin_note' => $adminNote !== '' && $adminNote !== null ? trim($adminNote) : null,
            'description' => 'برداشت تأییدشده — '.$destination,
        ]);

        SellerNotifier::notify(
            $tx->wallet?->seller_id,
            'wallet.withdraw',
            'درخواست برداشت تأیید شد ✅ '.Fmt::money($tx->amount),
            'مبلغ برداشت به '.$destination.' ارسال شد.'.($tx->admin_note ? ' توضیح مدیر: '.$tx->admin_note : ''),
            route('seller.wallet'),
            'wallet',
            $tx,
        );

        return true;
    }

    /**
     * Admin rejects a pending withdrawal → refund the reserved balance.
     * The note (توضیحات) tells the seller WHY — e.g. wrong card digits.
     */
    public static function rejectWithdrawal(WalletTransaction $tx, ?string $adminNote = null): bool
    {
        if (! $tx->isWithdrawal() || $tx->status !== WalletTransaction::STATUS_PENDING) {
            return false;
        }

        DB::transaction(function () use ($tx, $adminNote) {
            $tx->wallet->increment('balance', $tx->amount);
            $tx->update([
                'status' => WalletTransaction::STATUS_REJECTED,
                'admin_note' => $adminNote !== '' && $adminNote !== null ? trim($adminNote) : null,
                'description' => 'درخواست رد شد — مبلغ به کیف پول بازگشت',
            ]);
        });

        SellerNotifier::notify(
            $tx->wallet?->seller_id,
            'wallet.withdraw',
            'درخواست برداشت رد شد ⛔ '.Fmt::money($tx->amount),
            'مبلغ به کیف پول فروشگاه شما بازگشت.'.($tx->admin_note ? ' توضیح مدیر: '.$tx->admin_note : ''),
            route('seller.wallet'),
            'wallet',
            $tx,
        );

        return true;
    }

    /** Human order code (#id padded). */
    public static function orderCode(Order $order): string
    {
        return str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);
    }
}
