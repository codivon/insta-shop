<?php

namespace Database\Seeders;

use App\Models\BankCard;
use App\Models\Carrier;
use App\Models\CarrierTariff;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\ShopFilter;
use App\Models\ShopFilterItem;
use App\Models\Story;
use App\Models\StoryView;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Full «everything a shop can have» setup for EVERY demo shop (v0.17.14 —
 * was demo_store-only until v0.17.13) — so a visitor previewing the
 * project sees every feature alive on all 9 storefronts:
 *
 *   ①  روش‌های ارسال (carriers) با تعرفهٔ پله‌ای وزنی درون/برون‌استانی،
 *      پوشش شهرهای منتخب (select_city)، ارسال رایگان بالای مبلغ مشخص و
 *      بازهٔ زمانی ارسال — مثل صفحهٔ «حمل و نقل» پنل فروشنده.
 *   ②  کارت بانکی پیشفرض هر فروشنده (شماره کارت + شبا نمونه، متمایز).
 *   ③  فیلترهای فروشگاه (برند/گارانتی/رنگ) از روی مشخصات واقعیِ محصولات.
 *   ④  ۱۰ استوری تأییدشده برای هر فروشگاه: تصاویر AI «همراه محصولِ خودشان»
 *      (v0.17.14 — تصویر استوری گوشی حالا در فروشگاه دیجیتال است و
 *      تصویر غذااساز در فروشگاه خانه و آشپزخانه) + تصاویر AI مکمل برای
 *      فروشگاه‌های بدون تصویر اختصاصی + تصاویر گالری محصولات پرچمدار با
 *      کپشن فارسی و لینک «مشاهده و خرید» + بازدیدهای پخش‌شده در ۱۴ روز
 *      اخیر تا نمودارهای داشبورد ادمین هم زنده باشند.
 *
 * Idempotent: carriers matched on (shop_id, title), tariffs/cities rebuilt
 * per carrier, bank card on (seller_id, card_number), filters on
 * (shop_id, title) + items on (filter, spec_name), stories on
 * (shop_id, media_path) — stale demo story rows (layout قدیمی) حذف
 * می‌شوند تا هیچ استوری تکراری/یتیمی نماند.
 *
 * حذف کامل: `php artisan demo:remove` همهٔ این‌ها را با حذف فروشگاه‌ها/
 * فروشنده‌ها cascade می‌کند و پوشه‌های استوری را هم از دیسک پاک می‌کند.
 */
class DemoShopSetupSeeder extends Seeder
{
    use WithoutModelEvents;

    private const STORY_WINDOW_DAYS = 14;

    /** Per-shop stories count (the storefront ring row is built for 10). */
    private const STORIES_PER_SHOP = 10;

    /**
     * The AI story artwork (data/demo/stories/story-{index}.jpg) pinned to
     * its product — the story FOLLOWS the product into whichever demo shop
     * owns it (v0.17.14 root distribution).
     */
    private const AI_STORIES = [
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

    /**
     * Extra AI artwork for the shops without a product-pinned story image
     * (file → Persian caption; links to the shop's storefront).
     */
    private const EXTRA_AI_STORIES = [
        'demo_rozeno' => [
            ['rozeno-1.jpg', 'مراقبت پوستی اصل؛ کرم و سرم‌های محبوب این هفته رزنو با تخفیف ویژه 🌸'],
            ['rozeno-2.jpg', 'رژ و لوازم آرایشی با رنگ‌های تازه — هر روز با نوآوری در رزنو 💄'],
            ['rozeno-3.jpg', 'یک اسپا کوچک در خانه؛ ست‌های مراقبتی رزنو برای آرامش شما 🕯️'],
        ],
        'demo_asa' => [
            ['asa-1.jpg', 'مراقبت سلامتی در خانه؛ فشارسنج و تب‌سنج دیجیتال با گارانتی معتبر 🩺'],
            ['asa-2.jpg', 'ویتامین و مکمل‌های اصل؛ همراه سلامت روزانهٔ خانوادهٔ شما 💊'],
            ['asa-3.jpg', 'سلامتی روی مچ دست شما؛ ساعت‌های هوشمند پایش ضربان قلب ⌚'],
        ],
        'demo_sabalan' => [
            ['sabalan-1.jpg', 'پسته و خشکبار تازه؛ مهمانی‌تان را متفاوت کنید 🥜'],
            ['sabalan-2.jpg', 'عسل طبیعی سبلان؛ شیرینیِ خالص از دل کوهستان 🍯'],
            ['sabalan-3.jpg', 'چای لاهیجان؛ عطر بهار در فنجان‌های صبحانه ☕'],
        ],
    ];

    /** Caption templates for product-image stories ({name} = کوتاه‌شدهٔ نام کالا). */
    private const PRODUCT_CAPTIONS = [
        '«{name}» به تازگی به قفسه‌های ما رسید؛ فقط تا پایان هفته ✨',
        'پیشنهاد ویژهٔ امروز ما: {name} با قیمتی که جای دومش را نمی‌زنید ⚡',
        'اگر دنبال کیفیت واقعی می‌گردید، {name} دقیقاً همان است که می‌خواهید ❤️',
        'پرفروش‌ترین کالای این هفتهٔ ما؛ از دست دادن {name} حیف است! 🔥',
        '{name} — انتخاب مشتری‌هایی که یک بار تجربه‌اش کردند و دوباره برگشتند 🛍️',
        'خبر خوب: {name} دوباره شارژ شد و آمادهٔ ارسال همان روز است 📦',
        'برای کسانی که جزء بهترین‌ها می‌خواهند؛ {name} با ضمانت اصالت ✅',
        'یک انتخاب هوشمندانه برای خرید این هفته: {name} 💡',
        'فرصت محدود! {name} با تخفیف جشنواره‌ای تا پایان ماه 🎉',
        '{name} را حضوری هم می‌توانید ببینید؛ همین امروز سر بزنید 🚪',
        'همراه با ارسال سریع و بسته‌بندی ایمن؛ {name} منتظر شماست 🚚',
        'محبوب‌ترین پیشنهاد پیج این هفته؛ {name} با موجودی محدود ⏳',
    ];

    public function run(): void
    {
        // The seller panel caps RUNNING stories per shop (default 3) — the
        // demo storefronts carry 10 each, so lift the cap for a lively demo.
        if ((int) Setting::get('stories.max_active', 3) < self::STORIES_PER_SHOP) {
            Setting::set('stories.max_active', self::STORIES_PER_SHOP, 'number');
        }

        $shops = DemoSellersSeeder::shopByHandle()->sortBy('id')->values();

        if ($shops->isEmpty()) {
            $this->command?->warn('  فروشگاه نمونه پیدا نشد — DemoShopSetupSeeder بعد از DemoSellersSeeder اجرا شود.');

            return;
        }

        // products pinned by AI artwork, resolved ACROSS all demo shops
        $pinned = Product::query()
            ->whereIn('shop_id', $shops->pluck('id')->all())
            ->whereIn('slug', array_column(self::AI_STORIES, 0))
            ->get()
            ->keyBy('slug');

        $viewsSeeded = 0;
        $storiesSeeded = 0;

        foreach ($shops as $index => $shop) {
            $this->seedShipping($shop);
            $this->seedBankCard($shop->seller, $index, $shop->name);
            $this->seedFilters($shop);

            [$count, $views] = $this->seedStories($shop, $index, $pinned);
            $storiesSeeded += $count;
            $viewsSeeded += $views;
        }

        $this->command?->line("  <fg=green>✓</> حمل‌ونقل، کارت بانکی، فیلترها و {$storiesSeeded} استوری (۱۰ استوری برای هر فروشگاه، {$viewsSeeded} بازدید نمونه) آماده شد.");
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

    private function seedBankCard($seller, int $index, string $shopName): void
    {
        if ($seller === null) {
            return;
        }

        // متمایز اما قطعی: 5678 برای فروشگاه اصلی (سازگار با دیتابیس‌های قدیمی)
        $suffix = str_pad((string) (5678 + $index), 4, '0', STR_PAD_LEFT);

        BankCard::query()->updateOrCreate(
            ['seller_id' => $seller->id, 'card_number' => '603799751234'.$suffix],
            [
                'full_name' => $shopName,
                'sheba' => 'IR8205401026800208179'.str_pad((string) (9002 + $index), 5, '0', STR_PAD_LEFT),
                'is_default' => true,
            ],
        );
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
        }
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

    /**
     * ۱۰ استوری برای یک فروشگاه دمو — ترکیب:
     *   ① تصاویر AI پین‌شده به محصولاتِ همین فروشگاه (کپشن دست‌نویس)
     *   ② تصاویر AI مکمل فروشگاه‌های بدون تصویر اختصاصی (لینک به فروشگاه)
     *   ③ تصاویر گالری محصولات پرچمدار فروشگاه با کپشن‌های قالبی
     * ردیف‌های قدیمیِ خارج از ترکیب جدید حذف می‌شوند (idempotent) و
     * بازدید نمونه فقط برای استوری‌های تازه‌ساخته درج می‌شود.
     *
     * @param  Collection<string, Product>  $pinned  AI artwork slug → product
     * @return array{0: int, 1: int} [story count, seeded view count]
     */
    private function seedStories(Shop $shop, int $shopIndex, Collection $pinned): array
    {
        $handle = (string) $shop->storefrontHandle();
        $desired = $this->desiredStories($shop, $shopIndex, $pinned);

        if ($desired === []) {
            return [0, 0];
        }

        $targets = array_column($desired, 'target');

        // stale rows from an older layout (or a previous composition) — out
        Story::query()
            ->where('shop_id', $shop->id)
            ->where('media_path', 'like', 'stories/demo-%')
            ->whereNotIn('media_path', $targets)
            ->get()
            ->each(fn (Story $story) => $story->delete());

        $startsAt = now()->subMinutes(30);
        $endsAt = now()->addDays(self::STORY_WINDOW_DAYS);
        $viewsSeeded = 0;
        $count = 0;

        foreach ($desired as $seq => $row) {
            $contents = $row['disk'] !== null
                ? Storage::disk('public')->get($row['disk'])
                : @file_get_contents($row['file']);

            if ($contents === false || $contents === null) {
                continue; // artwork not bundled / gallery file gone → skip quietly
            }

            // always sync the file — the composition may re-map a seq to
            // different artwork after a legacy-layout migration
            Storage::disk('public')->put($row['target'], $contents);

            $story = Story::query()->updateOrCreate(
                ['shop_id' => $shop->id, 'media_path' => $row['target']],
                [
                    'product_id' => $row['product']?->id,
                    'type' => 'image',
                    'caption' => $row['caption'],
                    'link_url' => $row['link'],
                    'link_title' => $row['link_title'],
                    'likes_enabled' => true,
                    'comments_enabled' => true,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'status' => Story::STATUS_APPROVED,
                    'views' => 40 + $seq * 17,
                ],
            );

            $count++;

            // Spread guest story-views over the last 14 days so the admin
            // dashboard charts show a living curve (new stories only).
            if (! $story->wasRecentlyCreated || StoryView::query()->where('story_id', $story->id)->exists()) {
                continue;
            }

            $rows = [];

            foreach (range(13, 0) as $daysAgo) {
                $day = now()->subDays($daysAgo);

                foreach (range(1, 2 + ($seq + $daysAgo) % 4) as $k) {
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

        return [$count, $viewsSeeded];
    }

    /**
     * Build the desired 10-story composition of ONE demo shop:
     * product-pinned AI artwork → shop's extra AI artwork → flagship
     * gallery images (main + secondary), cycled with rotating captions
     * until the storefront ring row is full.
     *
     * @return list<array{target: string, file: string|null, disk: string|null, product: Product|null, caption: string, link: string|null, link_title: string|null}>
     */
    private function desiredStories(Shop $shop, int $shopIndex, Collection $pinned): array
    {
        $desired = [];

        // ① AI artwork pinned to THIS shop's products
        foreach (self::AI_STORIES as $fileIndex => [$slug, $caption]) {
            $product = $pinned->get($slug);

            if ($product !== null && (int) $product->shop_id === (int) $shop->id) {
                $desired[] = [
                    'file' => database_path('seeders/data/demo/stories/story-'.$fileIndex.'.jpg'),
                    'disk' => null,
                    'product' => $product,
                    'caption' => $caption,
                    'link' => $product->publicUrl(),
                    'link_title' => 'مشاهده و خرید',
                ];
            }
        }

        // ② shop's extra AI artwork (storefront link)
        $handle = (string) $shop->storefrontHandle();

        foreach (self::EXTRA_AI_STORIES[$handle] ?? [] as [$file, $caption]) {
            $desired[] = [
                'file' => database_path('seeders/data/demo/stories/'.$file),
                'disk' => null,
                'product' => null,
                'caption' => $caption,
                'link' => $shop->storefrontUrl(),
                'link_title' => 'دیدن فروشگاه',
            ];
        }

        // ③ flagship gallery images, main then secondary (fillers reuse the
        //    same family files, so flagships alone give the real variety)
        $pool = [];

        $products = Product::query()
            ->where('shop_id', $shop->id)
            ->where('status', Product::STATUS_PUBLISHED)
            ->orderByDesc('views')
            ->with('images')
            ->get();

        foreach ($products as $product) {
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->path)) {
                    $pool[] = ['disk' => $image->path, 'product' => $product];
                }
            }
        }

        $n = 0;

        while (count($desired) < self::STORIES_PER_SHOP && $pool !== []) {
            $entry = $pool[$n % count($pool)];
            $name = $this->storyProductName($entry['product']);

            $desired[] = [
                'file' => null,
                'disk' => $entry['disk'],
                'product' => $entry['product'],
                'caption' => str_replace('{name}', $name, self::PRODUCT_CAPTIONS[($n + $shopIndex) % count(self::PRODUCT_CAPTIONS)]),
                'link' => $entry['product']->publicUrl(),
                'link_title' => 'مشاهده و خرید',
            ];

            $n++;
        }

        $desired = array_slice($desired, 0, self::STORIES_PER_SHOP);

        // stable per-shop media paths: stories/demo-{…}/story-1..10.jpg
        $dir = 'stories/demo-'.preg_replace('/^demo_/', '', $handle);

        foreach ($desired as $i => &$row) {
            $row['target'] = $dir.'/story-'.($i + 1).'.jpg';
        }

        unset($row);

        return $desired;
    }

    /** A caption-friendly short name (up to the «مدل …» tail, then clamped). */
    private function storyProductName(Product $product): string
    {
        $name = trim((string) $product->name);
        $cut = mb_strpos($name, ' مدل ');

        if ($cut !== false && $cut > 8) {
            $name = trim(mb_substr($name, 0, $cut));
        }

        return mb_substr($name, 0, 48);
    }
}
