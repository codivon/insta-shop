<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Starter subscription plans (v0.22.0) — a sane, editable starting point
 * the client can reprice from the admin panel (پلن‌های اشتراک).
 *
 * Idempotent by title; runs in the installer pipeline automatically
 * (seeders are discovered alphabetically) and inside DatabaseSeeder.
 * Prices are «پیشنهادی» — the admin adjusts them to the market.
 */
class PlanSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // free — client request (v0.23.0): «یک طرح اشتراک به اسم اشتراک
        // رایگان که ۳ ماه رایگان باشد و کمیسیون هم صفر باشد» — the zero
        // commitment entry product (also the perfect acquisition hook).
        Plan::query()->updateOrCreate(
            ['title' => 'اشتراک رایگان'],
            [
                'tagline' => '۳ ماه کامل رایگان — بدون هزینهٔ پلن و بدون کمیسیون',
                'features' => [
                    '۹۰ روز اعتبار کامل',
                    'کمیسیون صفر روی همهٔ سفارش‌ها',
                    'بدون نیاز به پرداخت',
                ],
                'duration_days' => 90,
                'price' => 0,
                'commission_type' => 'percent',
                'commission_value' => 0,
                'boost_level' => 0,
                'is_active' => true,
                'sort' => 0,
            ],
        );

        // monthly — the entry product: the DURATION sells, rate stays global
        Plan::query()->updateOrCreate(
            ['title' => 'پلن ماهانه'],
            [
                'tagline' => 'برای شروع و تست فروش در پلتفرم',
                'features' => [
                    '۳۰ روز اعتبار کامل',
                    'پشتیبانی فروشندگان',
                    'بدون تعهد بلندمدت',
                ],
                'duration_days' => 30,
                'price' => 149000,
                'commission_type' => null,
                'commission_value' => null,
                'boost_level' => 1,
                'is_active' => true,
                'sort' => 1,
            ],
        );

        // half-year — the sweet spot: ~23% cheaper than monthly × 6
        Plan::query()->updateOrCreate(
            ['title' => 'پلن ۶ ماهه'],
            [
                'tagline' => 'به‌صرفه‌ترین انتخاب برای فروشندگان فعال (٪۲۳ تخفیف نسبت به ماهانه)',
                'features' => [
                    '۱۸۰ روز اعتبار کامل',
                    'نشان ویژه روی فروشگاه',
                    'اولویت در پشتیبانی',
                ],
                'duration_days' => 180,
                'price' => 690000,
                'commission_type' => null,
                'commission_value' => null,
                'boost_level' => 1,
                'is_active' => true,
                'sort' => 2,
            ],
        );

        // yearly — the flagship: demonstrates the per-plan commission override
        // (کمیسیون صفر) + the home-page display priority (boost_level 2)
        Plan::query()->updateOrCreate(
            ['title' => 'پلن سالانه طلایی'],
            [
                'tagline' => 'حداکثر سود: کمیسیون صفر به‌مدت یک سال + اولویت ویژه در صفحهٔ اصلی',
                'features' => [
                    '۳۶۵ روز اعتبار کامل',
                    'کمیسیون صفر روی همهٔ سفارش‌ها',
                    'نشان طلایی روی فروشگاه',
                    'اولویت ویژه در صفحهٔ اصلی',
                ],
                'duration_days' => 365,
                'price' => 1190000,
                'commission_type' => 'percent',
                'commission_value' => 0,
                'boost_level' => 2,
                'is_active' => true,
                'sort' => 3,
            ],
        );
    }
}
