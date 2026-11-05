<?php

namespace Database\Seeders;

use App\Models\BankCard;
use App\Models\Carrier;
use App\Models\CarrierTariff;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopFilter;
use App\Models\ShopFilterItem;
use App\Models\Story;
use App\Models\StoryView;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Full «everything a shop can have» setup for the DEMO shop (v0.17.11) —
 * so a visitor previewing the project sees every feature alive:
 *
 *   ①  روش‌های ارسال (carriers) با تعرفهٔ پله‌ای وزنی درون/برون‌استانی،
 *      پوشش شهرهای منتخب (select_city)، ارسال رایگان بالای مبلغ مشخص و
 *      بازهٔ زمانی ارسال (متن ثابت + انتخابی کاربر) — مثل صفحهٔ
 *      «حمل و نقل» پنل فروشنده.
 *   ②  کارت بانکی پیشفرض فروشنده (شماره کارت + شبا نمونه).
 *   ③  فیلترهای فروشگاه (برند/گارانتی/رنگ) از روی مشخصات واقعیِ محصولات.
 *   ④  ۱۰ استوری تأییدشده با تصویر اختصاصی (AI)، کپشن فارسی، اتصال به
 *      محصول واقعی فروشگاه + لینک «مشاهده و خرید» — و بازدیدهای پخش‌شده
 *      در ۱۴ روز اخیر تا نمودارهای داشبورد ادمین هم زنده باشند.
 *
 * Idempotent: carriers matched on (shop_id, title), tariffs/cities rebuilt
 * per carrier, bank card on (seller_id, card_number), filters on
 * (shop_id, title) + items on (filter, spec_name), stories on
 * (shop_id, media_path).
 *
 * حذف کامل: `php artisan demo:remove` همهٔ این‌ها را با حذف فروشگاه/
 * فروشنده cascade می‌کند و پوشهٔ استوری‌ها را هم از دیسک پاک می‌کند.
 */
class DemoShopSetupSeeder extends Seeder
{
    use WithoutModelEvents;

    private const SELLER_PHONE = '989120000000'; // normalized (E.164 without +) — matches Phone::normalize()

    private const STORY_DIR = 'stories/demo-store';

    /** Demo stories: file index → flagship product slug + Persian caption. */
    private const STORIES = [
        1 => ['apple-iphone-15-pro-max-256', 'آیفون ۱۵ پرو مکس رسید! تیتانیومی، خوش‌دست و با گارانتی رسمی 📱'],
        2 => ['samsung-galaxy-s24-ultra-512', 'گلکسی S24 اولترا با قلم S Pen — هوش مصنوعی روی مچ شما ✨'],
        3 => ['asus-vivobook-15-x1504', 'لپ‌تاپ ایسوس VivoBook برای دانشجوها و اداری‌ها — ۱۶ گیگ رم، i7 🖥'],
        4 => ['apple-airpods-pro-2-usb-c', 'ایرپاد پرو ۲ نویز کنسلینگ فعال — امروز با تخفیف ویژه 🎧'],
        5 => ['casio-mtp-1303-watch', 'ساعت کلاسیک کاسیو — شیک، بادوام و مناسب هر ست روزانه ⌚'],
        6 => ['philips-food-processor-hr7320', 'غذاساز فیلپس؛ آشپزی روزانه را نصف کنید 👩‍🍳'],
        7 => ['bosch-gsr-120-li-drill', 'دریل شارژی بوش؛ ابزار حرفه‌ای برای خانه و کارگاه 🔩'],
        8 => ['dior-sauvage-edp-100', 'عطر دیور ساواج — رایحهٔ ماندگار برای شب‌های خاص 🌙'],
        9 => ['nike-air-zoom-sneakers', 'کتانی نایک ایر زوم؛ سبک، خنک و مناسب دویدن روزانه 👟'],
        10 => ['samsung-smart-tv-55-cu7000', 'تلویزیون هوشمند سامسونگ ۵۵ اینچ؛ سینمای خانه شما 📺'],
    ];

    public function run(): void
    {
        $shop = Shop::query()
            ->whereHas('seller', fn ($q) => $q->where('phone', self::SELLER_PHONE))
            ->first();

        if ($shop === null) {
            $this->command?->warn('  فروشگاه نمونه پیدا نشد — DemoShopSetupSeeder بعد از DemoProductSeeder اجرا شود.');

            return;
        }

        $this->seedShipping($shop);
        $this->seedBankCard($shop->seller);
        $this->seedFilters($shop);
        $this->seedStories($shop);
    }

    // ── ① حمل و نقل ──────────────────────────────────────────────────

    private function seedShipping(Shop $shop): void
    {
        $tehranProvince = DB::table('provinces')->where('name', 'like', '%تهران%')->first();
        $originCityId = $tehranProvince !== null
            ? DB::table('cities')->where('province_id', $tehranProvince->id)->where('name', 'like', '%تهران%')->value('id')
            : null;

        // ── روش ۱: پیک موتوری (فقط شهرهای منتخب، ارسال همان روز) ──
        $bike = Carrier::query()->updateOrCreate(
            ['shop_id' => $shop->id, 'title' => 'پیک موتوری تهران'],
            [
                'is_active' => true,
                'waiting_time' => '۲ تا ۴ ساعت',
                'max_package_weight' => 10000,
                'min_package_weight' => null,
                'description' => 'تحویل همان روز در محدودهٔ شهر تهران. سفارش‌های ثبت‌شده تا ساعت ۱۵ همان روز ارسال می‌شوند.',
                'origin_province_id' => $tehranProvince->id ?? null,
                'origin_city_id' => $originCityId,
                'carriage_forward' => false,
                'free_shipping_weight' => null,
                'free_shipping_price' => 3000000,   // بالای ۳ میلیون رایگان
                'extra_cost' => 0,
                'covered_cities' => 'select_city',
                'delivery_time_type' => 'default',
                'start_days_after_order' => 0,
                'default_delivery_range' => 'ارسال در همان روز کاری',
                'sort' => 1,
            ],
        );

        // ── روش ۲: پست پیشتاز سراسری (تمام شهرها، بازهٔ انتخابی مشتری) ──
        $post = Carrier::query()->updateOrCreate(
            ['shop_id' => $shop->id, 'title' => 'پست پیشتاز سراسری'],
            [
                'is_active' => true,
                'waiting_time' => '۲ تا ۴ روز کاری',
                'max_package_weight' => 30000,
                'min_package_weight' => null,
                'description' => 'ارسال به تمام شهرهای کشور با پست پیشتاز. کد رهگیری بلافاصله پس از تحویل مرسوله پیامک می‌شود.',
                'origin_province_id' => $tehranProvince->id ?? null,
                'origin_city_id' => $originCityId,
                'carriage_forward' => false,
                'free_shipping_weight' => null,
                'free_shipping_price' => 5000000,   // بالای ۵ میلیون رایگان
                'extra_cost' => 9000,               // هر کیلو اضافه
                'covered_cities' => 'all',
                'delivery_time_type' => 'user_select',
                'start_days_after_order' => 1,
                'default_delivery_range' => null,
                'sort' => 2,
            ],
        );

        // پله‌های وزنی — in_province / out_province
        $this->syncTariffs($bike, [
            'in_province' => [[1500, 35000], [5000, 45000], [10000, 60000]],
            'out_province' => [[1500, 48000], [5000, 60000], [10000, 80000]],
        ]);
        $this->syncTariffs($post, [
            'in_province' => [[1000, 32000], [3000, 42000], [5000, 52000], [10000, 70000], [30000, 95000]],
            'out_province' => [[1000, 42000], [3000, 55000], [5000, 68000], [10000, 90000], [30000, 120000]],
        ]);

        // شهرهای تحت پوشش پیک: چند شهر بزرگ (تهران + البرز + مراکز استان‌ها)
        $bike->cities()->sync(
            DB::table('cities')
                ->whereIn('name', ['تهران', 'کرج', 'مشهد', 'اصفهان', 'شیراز', 'اهواز', 'تبریز'])
                ->pluck('id')
                ->whenEmpty(fn ($c) => DB::table('cities')->when($tehranProvince !== null, fn ($q) => $q->where('province_id', $tehranProvince->id))->limit(6)->pluck('id'))
                ->all()
        );

        $this->command?->line('  <fg=green>✓</> ۲ روش ارسال با تعرفهٔ وزنی و شهرهای تحت پوشش ساخته شد.');
    }

    /** Rebuild the weight tiers of one carrier (idempotent). */
    private function syncTariffs(Carrier $carrier, array $zones): void
    {
        CarrierTariff::query()->where('carrier_id', $carrier->id)->delete();

        foreach ($zones as $zone => $tiers) {
            foreach ($tiers as $i => [$maxWeight, $cost]) {
                CarrierTariff::query()->create([
                    'carrier_id' => $carrier->id,
                    'zone_type' => $zone,
                    'max_weight' => $maxWeight,
                    'cost' => $cost,
                    'sort' => $i + 1,
                ]);
            }
        }
    }

    // ── ② کارت بانکی فروشنده ─────────────────────────────────────────

    private function seedBankCard($seller): void
    {
        if ($seller === null) {
            return;
        }

        BankCard::query()->updateOrCreate(
            ['seller_id' => $seller->id, 'card_number' => '6037997512345678'],
            [
                'full_name' => 'فروشگاه نمونه اینستاشاپ',
                'sheba' => 'IR820540102680020817909002',
                'is_default' => true,
            ],
        );

        $this->command?->line('  <fg=green>✓</> کارت بانکی پیشفرض فروشندهٔ نمونه ثبت شد.');
    }

    // ── ③ فیلترهای فروشگاه ───────────────────────────────────────────

    private function seedFilters(Shop $shop): void
    {
        // spec-titles that actually exist among the shop's published products —
        // an empty block is dropped by the storefront, so only seed real ones.
        $titles = $this->specTitles($shop);

        $wanted = [
            ['title' => 'برند', 'spec' => 'برند', 'sort' => 1],
            ['title' => 'گارانتی و اصالت', 'spec' => 'گارانتی', 'sort' => 2],
            ['title' => 'رنگ', 'spec' => 'رنگ', 'sort' => 3],
        ];

        $built = 0;

        foreach ($wanted as $w) {
            if (! $titles->contains($w['spec'])) {
                continue;
            }

            $filter = ShopFilter::query()->updateOrCreate(
                ['shop_id' => $shop->id, 'title' => $w['title']],
                ['published' => true, 'sort' => $w['sort']],
            );

            ShopFilterItem::query()->updateOrCreate(
                ['shop_filter_id' => $filter->id, 'spec_name' => $w['spec']],
                ['kind' => 'spec', 'shop_attribute_id' => null],
            );

            $built++;
        }

        $this->command?->line("  <fg=green>✓</> {$built} فیلتر فروشگاه (برند/گارانتی/رنگ) منتشر شد.");
    }

    /** Distinct spec-item titles across the shop's published products. */
    private function specTitles(Shop $shop)
    {
        return Product::query()
            ->where('shop_id', $shop->id)
            ->where('status', Product::STATUS_PUBLISHED)
            ->get(['specifications'])
            ->flatMap(fn ($p) => collect($p->specifications ?? []))
            ->flatMap(fn ($group) => collect($group['items'] ?? []))
            ->pluck('title')
            ->map(fn ($t) => trim((string) $t))
            ->unique()
            ->values();
    }

    // ── ④ استوری‌های فروشگاه ─────────────────────────────────────────

    private function seedStories(Shop $shop): void
    {
        // flagship products by slug → story product + link
        $products = Product::query()
            ->where('shop_id', $shop->id)
            ->whereIn('slug', array_column(self::STORIES, 0))
            ->get()
            ->keyBy('slug');

        $startsAt = now()->subMinutes(30);
        $endsAt = now()->addDays(3);
        $viewsSeeded = 0;

        foreach (self::STORIES as $index => [$slug, $caption]) {
            $source = database_path('seeders/data/demo/stories/story-'.$index.'.jpg');

            if (! is_file($source)) {
                continue; // artwork not bundled → skip this story quietly
            }

            $target = self::STORY_DIR.'/story-'.$index.'.jpg';

            if (! Storage::disk('public')->exists($target)) {
                Storage::disk('public')->put($target, file_get_contents($source));
            }

            $product = $products[$slug] ?? null;

            $story = Story::query()->updateOrCreate(
                ['shop_id' => $shop->id, 'media_path' => $target],
                [
                    'product_id' => $product?->id,
                    'type' => 'image',
                    'caption' => $caption,
                    'link_url' => $product?->publicUrl(),
                    'link_title' => 'مشاهده و خرید',
                    'likes_enabled' => true,
                    'comments_enabled' => true,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'status' => Story::STATUS_APPROVED,
                    'views' => 40 + $index * 17,
                ],
            );

            // Spread guest story-views over the last 14 days so the admin
            // dashboard charts show a living curve.
            if (! $story->wasRecentlyCreated || StoryView::query()->where('story_id', $story->id)->exists()) {
                continue;
            }

            $rows = [];

            foreach (range(13, 0) as $daysAgo) {
                $day = now()->subDays($daysAgo);

                foreach (range(1, 2 + ($index + $daysAgo) % 4) as $k) {
                    $rows[] = [
                        'story_id' => $story->id,
                        'customer_id' => null,
                        'visitor_token' => 'demo-seed-'.$story->id.'-'.$daysAgo.'-'.$k,
                        'created_at' => $day->copy()->setTime(8 + $k * 2, ($k * 13) % 60),
                        'updated_at' => now(),
                    ];
                }
            }

            StoryView::query()->insert($rows);
            $viewsSeeded += count($rows);
        }

        $this->command?->line('  <fg=green>✓</> استوری‌های فروشگاه نمونه با لینک محصول ساخته شد ('.$viewsSeeded.' بازدید نمونه).');
    }
}
