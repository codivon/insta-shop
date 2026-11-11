<?php

namespace App\Support\Bump;

use App\Models\Product;
use App\Models\ProductBump;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Support\Fmt;
use App\Support\JDate;
use App\Support\SellerNotifier;
use Illuminate\Support\Facades\DB;

/**
 * Bump engine (v0.23.0 — «نردبان» به سبک دیوار) — ONE place for every
 * entitlement write, the twin of SubscriptionService:
 *
 *   priceForDays()       → قیمت نردبانِ N روز (نرخ روزانه از
 *                          تنظیمات shops.bump_price_per_day؛ ۰ = نردبان خاموش).
 *   purchaseFromWallet() → خرید از کیف پول فروشنده (اتمیک: قفل کیف پول،
 *                          کسر، تراکنش TYPE_BUMP، سپس grant — یا همه یا هیچ).
 *   grant()              → خودِ اعطا: محصولِ با نردبانِ فعال → تمدید
 *                          انباشتی از انتهای نردبان فعلی (مثل اشتراک،
 *                          خرید دوباره = طولانی‌تر شدن نردبان)؛ محصولِ
 *                          بدون نردبان فعال → ردیف تازه از همین حالت.
 *
 * Design notes:
 *  • «فعال» یعنی ends_at > now — انقضا با خودِ تاریخ انجام می‌شود و هیچ
 *    کرونی وضعیت نمی‌چرخاند (عیناً مثل اشتراک‌ها).
 *  • نردبانِ محصولِ ناموجود/غیرمنتشرشده/متعلق به دیگری رد می‌شود.
 */
final class BumpService
{
    /** Per-day rate in Toman (۰ = نردبان غیرفعال). */
    public static function pricePerDay(): int
    {
        return max(0, (int) Setting::get('shops.bump_price_per_day', 0));
    }

    public static function priceForDays(int $days): int
    {
        return self::pricePerDay() * max(1, $days);
    }

    /** Quick chips for the seller panel (days => price). */
    public static function packages(): array
    {
        $perDay = self::pricePerDay();

        return collect([3, 7, 14, 30])
            ->mapWithKeys(fn (int $days) => [$days => $perDay * $days])
            ->all();
    }

    /* ───────────────────────── grant core ───────────────────────── */

    /**
     * Grant ONE days-window to a product. Same-product stacking: the new
     * window begins where the running one ends. Returns the created row.
     */
    public static function grant(Seller $seller, Product $product, int $days, int $pricePaid, string $source, ?string $note = null): ProductBump
    {
        $running = ProductBump::query()
            ->where('seller_id', $seller->id)
            ->where('product_id', $product->id)
            ->where('ends_at', '>', now())
            ->orderByDesc('ends_at')
            ->first();

        $startsAt = $running?->ends_at ?? now();

        $row = ProductBump::query()->create([
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addDays(max(1, $days)),
            'price_paid' => max(0, $pricePaid),
            'source' => $source,
            'note' => $note,
        ]);

        return $row;
    }

    /* ───────────────────────── wallet purchase ───────────────────────── */

    /**
     * Seller self-service purchase: debit the wallet and start/extend the
     * bump atomically. Returns [ok, bump|null, error] — ok=false carries a
     * Persian reason (insufficient balance, disabled, wrong product…).
     */
    public static function purchaseFromWallet(Seller $seller, Product $product, int $days): array
    {
        $perDay = self::pricePerDay();

        if ($perDay <= 0) {
            return [false, null, 'نردبان در حال حاضر غیرفعال است. با پشتیبانی تماس بگیرید.'];
        }

        if ((int) $product->shop?->seller_id !== (int) $seller->id || $product->status !== Product::STATUS_PUBLISHED) {
            return [false, null, 'این محصول قابل نردبان شدن نیست — فقط محصولات منتشرشدهٔ فروشگاه خودتان.'];
        }

        $days = max(1, min(90, $days));
        $price = self::priceForDays($days);

        [$ok, $row] = DB::transaction(function () use ($seller, $product, $days, $price) {
            $wallet = Wallet::query()
                ->where('seller_id', $seller->id)
                ->lockForUpdate()
                ->first();

            $wallet ??= Wallet::query()->create(['seller_id' => $seller->id]);

            if ($price > 0 && $wallet->balance < $price) {
                return [false, null];
            }

            if ($price > 0) {
                $wallet->decrement('balance', $price);

                WalletTransaction::query()->create([
                    'wallet_id' => $wallet->id,
                    'type' => WalletTransaction::TYPE_BUMP,
                    'amount' => $price,
                    'commission' => 0,
                    'status' => WalletTransaction::STATUS_DONE,
                    'description' => 'خرید نردبان «'.$product->name.'» — '.Fmt::toFa($days).' روز',
                ]);
            }

            return [true, self::grant($seller, $product, $days, $price, ProductBump::SOURCE_WALLET)];
        });

        if (! $ok) {
            return [false, null, 'موجودی کیف پول شما برای این نردبان کافی نیست.'];
        }

        SellerNotifier::notify(
            $seller,
            'system.misc',
            'نردبان «'.$product->name.'» فعال شد 🚀',
            'این محصول تا '.JDate::date($row->ends_at).' در صدر صفحهٔ اول نمایش داده می‌شود ('.Fmt::toFa($row->daysLeft()).' روز).',
            route('seller.bump'),
            'rocket',
            $row,
        );

        return [true, $row, null];
    }
}
