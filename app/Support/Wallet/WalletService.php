<?php

namespace App\Support\Wallet;

use App\Models\Order;
use App\Models\Shop;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Wallet ledger service.
 *
 * A paid order credits every shop that has items in it — each item's
 * line total (unit_price × quantity) goes to that shop's wallet.
 * Withdrawals reserve the balance immediately (pending); rejecting a
 * withdrawal refunds it.
 */
final class WalletService
{
    /** Credit the shops of a paid order (called inside finalizePaid). */
    public static function creditOrder(Order $order): void
    {
        $shares = $order->items()
            ->selectRaw('shop_id, SUM(unit_price * quantity) as total')
            ->groupBy('shop_id')
            ->get();

        foreach ($shares as $share) {
            if ((int) $share->total <= 0) {
                continue;
            }

            // order_items carry shop_id — wallets belong to the seller
            $sellerId = Shop::query()->whereKey($share->shop_id)->value('seller_id');

            if (! $sellerId) {
                continue;
            }

            $wallet = Wallet::ensureForSeller((int) $sellerId);

            $exists = WalletTransaction::query()
                ->where('wallet_id', $wallet->id)
                ->where('order_id', $order->id)
                ->exists();

            if ($exists) {
                continue; // idempotent
            }

            $wallet->increment('balance', (int) $share->total);

            WalletTransaction::query()->create([
                'wallet_id'   => $wallet->id,
                'type'        => WalletTransaction::TYPE_DEPOSIT,
                'amount'      => (int) $share->total,
                'status'      => WalletTransaction::STATUS_DONE,
                'order_id'    => $order->id,
                'description' => 'سهم فروش از سفارش #'.self::orderCode($order),
            ]);
        }
    }

    /** Reserve balance and log a pending withdrawal request (null = insufficient). */
    public static function requestWithdrawal(Wallet $wallet, int $amount, string $sheba): ?WalletTransaction
    {
        return DB::transaction(function () use ($wallet, $amount, $sheba) {
            $fresh = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->first();

            if (! $fresh || $fresh->balance < $amount) {
                return null;
            }

            $fresh->decrement('balance', $amount);

            return WalletTransaction::query()->create([
                'wallet_id'   => $fresh->id,
                'type'        => WalletTransaction::TYPE_WITHDRAWAL,
                'amount'      => $amount,
                'status'      => WalletTransaction::STATUS_PENDING,
                'description' => 'درخواست برداشت به شبا '.$sheba,
                'sheba'       => $sheba,
            ]);
        });
    }

    /** Admin approves a pending withdrawal (money was reserved at request time). */
    public static function approveWithdrawal(WalletTransaction $tx): bool
    {
        if (! $tx->isWithdrawal() || $tx->status !== WalletTransaction::STATUS_PENDING) {
            return false;
        }

        $tx->update([
            'status'      => WalletTransaction::STATUS_DONE,
            'description' => 'برداشت تأییدشده — '.$tx->sheba,
        ]);

        return true;
    }

    /** Admin rejects a pending withdrawal → refund the reserved balance. */
    public static function rejectWithdrawal(WalletTransaction $tx): bool
    {
        if (! $tx->isWithdrawal() || $tx->status !== WalletTransaction::STATUS_PENDING) {
            return false;
        }

        DB::transaction(function () use ($tx) {
            $tx->wallet->increment('balance', $tx->amount);
            $tx->update([
                'status'      => WalletTransaction::STATUS_REJECTED,
                'description' => 'درخواست رد شد — مبلغ به کیف پول بازگشت',
            ]);
        });

        return true;
    }

    /** Human order code (#id padded). */
    public static function orderCode(Order $order): string
    {
        return str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);
    }
}
