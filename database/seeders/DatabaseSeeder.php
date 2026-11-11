<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database (idempotent — safe to re-run).
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SmsSettingsSeeder::class,
            PaymentSettingsSeeder::class,
            SmsTemplateSeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
            CategorySeeder::class,

            // ── پلن‌های اشتراک پیشنهادی (v0.22.0) — از پنل «پلن‌های اشتراک»
            //    قیمت و کمیسیون هر پلن را می‌توانید ویرایش کنید ──
            PlanSeeder::class,

            // ── طرح‌های نردبان پیشنهادی (v0.24.0) — مدت + قیمت؛ از پنل
            //    «طرح‌های نردبان» می‌توانید طرح اضافه/ویرایش کنید ──
            BumpPlanSeeder::class,

            // ── محتوای نمونه (دمو): ۴ اسلایدر + ۴ بنر + ۹ فروشگاه نمونه
            //    (فروشنده‌های واقعی‌نما) با محصولات توزیع‌شده بین فروشگاه‌ها
            //    (هر زیر‌دسته ≥ ۳ کالا) + حمل‌ونقل، کارت، فیلترها و ۱۰ استوری
            //    برای هر فروشگاه ──
            // حذف کامل: «php artisan demo:remove» را اجرا کنید یا همین چهار خط را بردارید.
            DemoBannerSeeder::class,
            DemoSellersSeeder::class,
            DemoProductSeeder::class,
            DemoShopSetupSeeder::class,
        ]);
    }
}
