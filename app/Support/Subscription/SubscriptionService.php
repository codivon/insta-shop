<?php

namespace App\Support\Subscription;

use App\Models\Plan;
use App\Models\Seller;
use App\Models\SellerSubscription;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Support\Fmt;
use App\Support\JDate;
use App\Support\SellerNotifier;
use Illuminate\Support\Facades\DB;

/**
 * Subscription engine (v0.22.0) — ONE place for every entitlement write:
 *
 *   grant()              → اعطای اشتراک (مدیر یا خرید) با قوانین زیر:
 *     • no active sub            → شروع از همین حالت؛
 *     • same plan already active → تمدید/انباشت: از انتهای اشتراک فعلی
 *                                  (خرید دوباره = طولانی‌تر شدن اشتراک)؛
 *     • different plan active    → جایگزینی فوری: اشتراک قبلی همین حالت
 *                                  بسته می‌شود (ends_at = now) و پلن جدید
 *                                  از همین حالت فعال می‌شود.
 *   purchaseFromWallet() → خرید از کیف پول فروشنده (اکشن اتمیک:
 *                          قفل ردیف کیف پول، کسر موجودی، ثبت تراکنش
 *                          «plan» و سپس grant — یا همه یا هیچ).
 *   grantAdmin()         → اعطای دستی مدیر (رایگان/هدیه یا مبلغ دلخواه).
 *   startTrial()         → دورهٔ آزمایشی خودکار فروشندگان تازه‌تأیید
 *                          (تنظیم shops.subscription_trial_days).
 *   revoke()             → لغو: ends_at بسته می‌شود، تاریخچه می‌ماند.
 *
 * Design notes:
 *  • «فعال» یعنی ends_at > now — انقضا با خودِ تاریخ انجام می‌شود؛ هیچ
 *    کرونی وضعیت را نمی‌چرخاند و هیچ رکوردی گم نمی‌شود.
 *  • تراکنش کیف پول از نوع TYPE_PLAN ثبت می‌شود تا در گردش حساب
 *    «خرید اشتراک» دیده شود (عیناً مثل برداشت، مبلغ کم می‌شود).
 *  • یادآوری پایان اشتراک (۳ روز مانده) جداگانه در
 *    subscriptions:remind اجرا می‌شود؛ این سرویس با reminded_at کاری ندارد.
 */
final class SubscriptionService
{
    /* ───────────────────────── grant core ───────────────────────── */

    /**
     * Grant ONE plan-duration to a seller following the stacking rules
     * above. Returns the created row. Not transactional by itself —
     * wrap in DB::transaction when money is involved.
     */
    public static function grant(Seller $seller, Plan $plan, int $pricePaid, string $source, ?string $note = null): SellerSubscription
    {
        $active = SellerSubscription::activeFor($seller->id);

        $startsAt = now();
        $replaced = collect();

        if ($active !== null) {
            if ($active->plan_id !== null
                && (int) $active->plan_id === (int) $plan->id
                // renewal stacking only when the seller's ENTIRE active set
                // is this plan — replacements below close everything else
                && SellerSubscription::query()
                    ->where('seller_id', $seller->id)
                    ->where('ends_at', '>', now())
                    ->where(fn ($q) => $q->whereNull('plan_id')->orWhere('plan_id', '!=', $plan->id))
                    ->doesntExist()) {
                // SAME plan → renewal stacking: begin where the current one ends.
                $startsAt = $active->ends_at;
            } else {
                // Different plan (or trial) → immediate replacement: EVERY
                // still-active row closes (stacked renewals leave several
                // open rows — closing only the newest would leak the rest).
                $replaced = SellerSubscription::query()
                    ->where('seller_id', $seller->id)
                    ->where('ends_at', '>', now())
                    ->get();
            }
        }

        $endsAt = $startsAt->copy()->addDays(max(1, $plan->duration_days));

        $row = SellerSubscription::query()->create([
            'seller_id' => $seller->id,
            'plan_id' => $plan->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'price_paid' => max(0, $pricePaid),
            'source' => $source,
            'is_trial' => false,
            'note' => $note,
        ]);

        // v0.23.0 — «کمیسیون پلن، در کمیسیون اختصاصی فروشنده بنشیند»:
        // whatever commission the plan carries (percent / fixed — or NO
        // override = back to the global rate) is written into the seller's
        // PERSONAL override so the admin sees the effective rate in
        // «ویرایش فروشنده» and the resolved rate can never drift from the
        // plan the seller actually paid for.
        $seller->forceFill([
            'commission_type' => $plan->hasCommissionOverride() ? $plan->commission_type : null,
            'commission_value' => $plan->hasCommissionOverride() ? (int) $plan->commission_value : null,
        ])->save();

        foreach ($replaced as $old) {
            $old->forceFill([
                'ends_at' => now(),
                'note' => trim((string) $old->note.' — جایگزین‌شده با پلن '.$plan->title),
            ])->save();
        }

        return $row;
    }

    /* ───────────────────────── wallet purchase ───────────────────────── */

    /**
     * Seller self-service purchase: debit the wallet and activate the
     * plan atomically. Returns [ok, subscription|null] — ok=false means
     * insufficient balance (never throws for that case).
     */
    public static function purchaseFromWallet(Seller $seller, Plan $plan): array
    {
        [$ok, $row] = DB::transaction(function () use ($seller, $plan) {
            $wallet = Wallet::query()
                ->where('seller_id', $seller->id)
                ->lockForUpdate()
                ->first();

            $wallet ??= Wallet::query()->create(['seller_id' => $seller->id]);

            $price = max(0, (int) $plan->price);

            if ($wallet->balance < $price) {
                return [false, null];
            }

            if ($price > 0) {
                $wallet->decrement('balance', $price);

                WalletTransaction::query()->create([
                    'wallet_id' => $wallet->id,
                    'type' => WalletTransaction::TYPE_PLAN,
                    'amount' => $price,
                    'commission' => 0,
                    'status' => WalletTransaction::STATUS_DONE,
                    'description' => 'خرید اشتراک «'.$plan->title.'» — '.$plan->durationLabel(),
                ]);
            }

            return [true, self::grant($seller, $plan, $price, SellerSubscription::SOURCE_WALLET)];
        });

        if ($ok && $row !== null) {
            self::notifyActivated($seller, $row, 'پلن «'.$plan->title.'» با پرداخت از کیف پول فعال شد 🎉');
        }

        return [$ok, $row];
    }

    /* ───────────────────────── admin grant ───────────────────────── */

    /**
     * Manual admin grant — free/gift when $pricePaid is 0, or the amount
     * the admin actually received (card-to-card) for the books.
     */
    public static function grantAdmin(Seller $seller, Plan $plan, int $pricePaid = 0, ?string $note = null): SellerSubscription
    {
        $row = self::grant($seller, $plan, $pricePaid, SellerSubscription::SOURCE_ADMIN, $note);

        self::notifyActivated($seller, $row, $pricePaid > 0
            ? 'پلن «'.$plan->title.'» برای شما فعال شد ✅'
            : 'پلن «'.$plan->title.'» به‌صورت هدیه برای شما فعال شد 🎁');

        return $row;
    }

    /* ───────────────────────── trial ───────────────────────── */

    /**
     * Auto trial for freshly-approved sellers. Fires ONLY when the admin
     * configured shops.subscription_trial_days > 0 AND the seller has
     * never had any subscription row (a one-time gift per seller).
     * Commission during the trial stays the resolved default — the trial
     * waives the PLAN FEE, not the commission.
     */
    public static function startTrial(Seller $seller): ?SellerSubscription
    {
        $days = (int) Setting::get('shops.subscription_trial_days', 0);

        if ($days <= 0) {
            return null;
        }

        $hasAny = SellerSubscription::query()->where('seller_id', $seller->id)->exists();

        if ($hasAny) {
            return null;
        }

        $row = SellerSubscription::query()->create([
            'seller_id' => $seller->id,
            'plan_id' => null,
            'starts_at' => now(),
            'ends_at' => now()->addDays($days),
            'price_paid' => 0,
            'source' => SellerSubscription::SOURCE_TRIAL,
            'is_trial' => true,
        ]);

        SellerNotifier::notify(
            $seller,
            'subscription.status',
            'دورهٔ آزمایشی '.Fmt::toFa($days).' روزه فعال شد 🎁',
            'تا پایان این دوره، هزینهٔ پلن از شما گرفته نمی‌شود. برای دیدن پلن‌ها به «اشتراک و ارتقا» سر بزنید.',
            route('seller.subscription'),
            'crown',
            $row,
        );

        return $row;
    }

    /* ───────────────────────── revoke ───────────────────────── */

    /** Admin revoke — close a STILL-ACTIVE row now, keep the history. */
    public static function revoke(SellerSubscription $row): bool
    {
        if (! $row->isActive()) {
            return false;
        }

        $row->forceFill([
            'ends_at' => now(),
            'note' => trim((string) $row->note.' — توسط مدیر لغو شد'),
        ])->save();

        SellerNotifier::notify(
            $row->seller,
            'subscription.status',
            'اشتراک «'.$row->title().'» لغو شد ⛔',
            'اشتراک فعال فروشگاه شما توسط پشتیبانی لغو شد. برای ادامه از صفحهٔ «اشتراک و ارتقا» پلن تهیه کنید.',
            route('seller.subscription'),
            'crown',
            $row,
        );

        return true;
    }

    /* ───────────────────────── notify helper ───────────────────────── */

    private static function notifyActivated(Seller $seller, SellerSubscription $row, string $title): void
    {
        SellerNotifier::notify(
            $seller,
            'subscription.status',
            $title,
            'اعتبار تا '.JDate::date($row->ends_at).' معتبر است — کمیسیون و مزایا بلافاصله اعمال می‌شوند.',
            route('seller.subscription'),
            'crown',
            $row,
        );
    }
}
