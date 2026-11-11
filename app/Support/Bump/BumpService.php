<?php

namespace App\Support\Bump;

use App\Models\BumpPlan;
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
 * Bump engine (v0.24.0 — «نردبانِ طرح‌دار») — ONE place for every
 * entitlement write, the twin of SubscriptionService:
 *
 *   enabled()            → toggle روشن/خاموش نردبان (تنظیمات فروشگاه‌ها).
 *   plans()              → طرح‌های قابل فروش (مدیر از صفحهٔ «طرح‌های
 *                          نردبان» تعریف می‌کند: «مثلاً ۱۰ روز با قیمت X»).
 *   purchaseFromWallet() → خرید طرح از کیف پول فروشنده (اتمیک: قفل کیف
 *                          پول، کسر، تراکنش TYPE_BUMP، سپس grant — یا
 *                          همه یا هیچ).
 *   grant()              → خودِ اعطا: محصولِ با نردبانِ فعال → تمدید
 *                          انباشتی از انتهای نردبان فعلی (مثل اشتراک،
 *                          خرید دوباره = طولانی‌تر شدن نردبان)؛ محصولِ
 *                          بدون نردبان فعال → ردیف تازه از همین حالت.
 *
 * Design notes:
 *  • «فعال» یعنی ends_at > now — انقضا با خودِ تاریخ انجام می‌شود و هیچ
 *    کرونی وضعیت نمی‌چرخاند (عیناً مثل اشتراک‌ها).
 *  • نردبانِ محصولِ ناموجود/غیرمنتشرشده/متعلق به دیگری رد می‌شود.
 *  • v0.23.0's flat «nرخ روزانه» is retired: the per-day price and the
 *    fixed chips (۳/۷/۱۴/۳۰) are gone — pricing is EXACTLY what the
 *    admin typed into each bump plan.
 */
final class BumpService
{
    /** Master switch — «تنظیمات فروشگاه‌ها ← نردبان» (false = everywhere hidden). */
    public static function enabled(): bool
    {
        return (bool) Setting::get('shops.bump_enabled', false);
    }

    /** Sellable bump plans (active, shortest first) for the seller panel. */
    public static function plans()
    {
        return BumpPlan::sellable();
    }

    /* ───────────────────────── grant core ───────────────────────── */

    /**
     * Grant ONE plan-window to a product. Same-product stacking: the new
     * window begins where the running one ends. Returns the created row.
     */
    public static function grant(Seller $seller, Product $product, BumpPlan $plan, string $source, ?string $note = null): ProductBump
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
            'ends_at' => $startsAt->copy()->addDays(max(1, $plan->days)),
            'price_paid' => max(0, (int) $plan->price),
            'bump_plan_id' => $plan->id,
            'source' => $source,
            'note' => $note,
        ]);

        return $row;
    }

    /* ───────────────────────── wallet purchase ───────────────────────── */

    /**
     * Seller self-service purchase of ONE bump plan: debit the wallet and
     * start/extend the bump atomically. Returns [ok, bump|null, error] —
     * ok=false carries a Persian reason (insufficient balance, disabled,
     * wrong product…).
     */
    public static function purchaseFromWallet(Seller $seller, Product $product, BumpPlan $plan): array
    {
        if (! self::enabled()) {
            return [false, null, 'نردبان در حال حاضر غیرفعال است. با پشتیبانی تماس بگیرید.'];
        }

        if (! $plan->is_active) {
            return [false, null, 'این طرح نردبان دیگر قابل خرید نیست.'];
        }

        if ((int) $product->shop?->seller_id !== (int) $seller->id || $product->status !== Product::STATUS_PUBLISHED) {
            return [false, null, 'این محصول قابل نردبان شدن نیست — فقط محصولات منتشرشدهٔ فروشگاه خودتان.'];
        }

        $price = max(0, (int) $plan->price);

        [$ok, $row] = DB::transaction(function () use ($seller, $product, $plan, $price) {
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
                    'description' => 'خرید نردبان «'.$product->name.'» — '.$plan->title,
                ]);
            }

            return [true, self::grant($seller, $product, $plan, ProductBump::SOURCE_WALLET)];
        });

        if (! $ok) {
            return [false, null, 'موجودی کیف پول شما برای این طرح نردبان کافی نیست.'];
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
