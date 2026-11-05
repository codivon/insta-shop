<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Seller;
use App\Models\Shop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Demo catalog (v0.17.9 + v0.17.11) — یک فروشگاه نمونهٔ تأییدشده + محصولات
 * واقعی‌نمای باسلام/دیجی‌کالا-سبک:
 *
 *   ① ۴۳ محصول «پرچمدار» دست‌نویس (data/demo-products.php) با گالری اختصاصی
 *      روی زیر‌دسته‌های منتخب هر ۱۴ دستهٔ اصلی.
 *   ② «پُرکنِ» زیر‌دسته‌ها (v0.17.11): هر زیر‌دستهٔ سطح-۲ (فرزندِ مستقیم
 *      دستهٔ اصلی) حداقل ۳ محصول می‌گیرد — کمبودها با ردیف‌های تولیدشده
 *      قطعی (deterministic) از پروفایل همان دستهٔ اصلی (برند/بازهٔ قیمت/
 *      رنگ/گارانتی/واحد) پر می‌شود و گالری آن‌ها به تصاویر عمومیِ همان
 *      خانواده اشاره می‌کند (کپی فایل جدیدی روی دیسک نمی‌شود).
 *
 * Images for the flagship rows ship inside the repo
 * (database/seeders/data/demo/products) and are copied into the public
 * disk on seed — no manual upload needed.
 *
 * Idempotent: the seller is matched on its demo phone, products on
 * (shop_id, slug) and gallery rows on (product_id, sort).
 *
 * حذف کامل: `php artisan demo:remove` فروشگاه، محصولات، تصاویر و
 * بنرهای نمونه را یک‌جا پاک می‌کند (یا خطوط Demo*Seeder را از
 * DatabaseSeeder بردارید).
 */
class DemoProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /** The fixed demo seller identity (also used by demo:remove). */
    private const SELLER_PHONE = '09120000000';

    private const SELLER_HANDLE = 'demo_store';

    public function run(): void
    {
        $seller = $this->ensureSeller();
        $shop = $this->ensureShop($seller);
        $products = require database_path('seeders/data/demo-products.php');

        $created = 0;

        foreach ($products as $group) {
            foreach ($group['products'] as $row) {
                $category = $this->resolveCategory($group['root'], $row['sub']);

                if ($category === null) {
                    $this->command?->warn("  دستهٔ «{$row['sub']}» زیر «{$group['root']}» پیدا نشد — پرش از {$row['name']}");

                    continue;
                }

                $specialEnds = ! empty($row['special'])
                    ? now()->addDays((int) ($row['special_days'] ?? 3))
                    : null;

                $product = Product::query()->updateOrCreate(
                    ['shop_id' => $shop->id, 'slug' => $row['slug']],
                    [
                        'category_id' => $category->id,
                        'name' => $row['name'],
                        'description' => $row['desc'],
                        'specifications' => $row['specs'],
                        'type' => Product::TYPE_PHYSICAL,
                        'price' => (int) $row['price'],
                        'compare_at_price' => $row['compare'] !== null ? (int) $row['compare'] : null,
                        'stock' => (int) ($row['stock'] ?? 5),
                        'weight_grams' => $row['weight_grams'] ?? null,
                        'unit' => $row['unit'] ?? 'تعداد',
                        'status' => Product::STATUS_PUBLISHED,
                        'views' => (int) ($row['views'] ?? 0),
                        'is_special' => (bool) ($row['special'] ?? false),
                        'special_ends_at' => $specialEnds,
                    ],
                );

                $this->syncImages($product, $row['img'], $row['slug']);
                $created++;
            }
        }

        $generated = $this->fillChildCategories($shop);

        if ($generated > 0) {
            $this->command?->line("  <fg=green>✓</> {$generated} محصول تکمیلی برای زیر‌دسته‌های کم‌محصول ساخته شد.");
        }

        $this->command?->line("  <fg=green>✓</> فروشگاه نمونه با {$created} محصول پرچمدار در دسته‌بندی‌ها ساخته شد.");
    }

    // ── helpers ──────────────────────────────────────────────────────

    /** The approved demo seller (idempotent on the demo phone). */
    private function ensureSeller(): Seller
    {
        return Seller::query()->updateOrCreate(
            ['phone' => self::SELLER_PHONE],
            [
                'instagram_username' => self::SELLER_HANDLE,
                'full_name' => 'فروشگاه نمونه اینستاشاپ',
                'status' => 'approved',
                'phone_verified_at' => now(),
            ],
        );
    }

    /** The demo shop (one per seller) + bundled logo artwork. */
    private function ensureShop(Seller $seller): Shop
    {
        $logoPath = null;
        $source = database_path('seeders/data/demo/banners/shop-logo.jpg');

        if (is_file($source)) {
            $logoPath = 'shops/demo-logo.jpg';

            if (! Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->put($logoPath, file_get_contents($source));
            }
        }

        return Shop::query()->updateOrCreate(
            ['seller_id' => $seller->id],
            [
                'name' => 'دیجی‌مارکت اینستاشاپ',
                'instagram_username' => self::SELLER_HANDLE,
                'about' => 'فروشگاه نمونهٔ پلتفرم اینستاشاپ — موبایل، لپ‌تاپ و گجت‌های دیجیتال با قیمت رقابتی و ارسال سریع به سراسر کشور. این فروشگاه و محصولاتش با سیدر نمونه ساخته شده‌اند و می‌توانید هر لحظه با دستور demo:remove حذفشان کنید.',
                'logo_path' => $logoPath,
                'phone' => '02191000000',
                'address' => 'تهران، خیابان ولیعصر، مرکز کامپیوتر پیروزی، پلاک ۱۰',
                'status' => 'active',
                'show_stock' => true,
            ],
        );
    }

    /** Sub-category under its root; fall back to the root itself. */
    private function resolveCategory(string $rootName, string $subName): ?Category
    {
        $sub = Category::query()
            ->where('name', $subName)
            ->whereHas('parent', fn ($q) => $q->where('name', $rootName))
            ->first();

        if ($sub !== null) {
            return $sub;
        }

        return Category::query()
            ->where('name', $rootName)
            ->whereNull('parent_id')
            ->first();
    }

    // ── v0.17.11: child-category fill (≥۳ محصول برای هر زیر‌دستهٔ سطح-۲) ──

    /** Every level-2 category ends up with at least this many demo products. */
    private const CHILD_MIN = 3;

    /**
     * Per-root generation profile: brand pool, Toman price range, unit,
     * colors and warranty variants. Keys must match the «root» values of
     * data/demo-products.php (the level-1 category names).
     */
    private const ROOT_PROFILES = [
        'کالای دیجیتال' => [
            'price' => [380000, 78000000], 'unit' => 'دستگاه',
            'brands' => [['سامسونگ', 'Samsung'], ['شیائومی', 'Xiaomi'], ['انکر', 'Anker'], ['سونی', 'Sony'], ['لنوو', 'Lenovo'], ['جی‌بی‌ال', 'JBL']],
            'colors' => ['مشکی', 'سفید', 'نقره‌ای', 'آبی'],
            'feature' => 'مجهز به آخرین نسل فناوری بی‌سیم و سازگار با همهٔ دستگاه‌های هوشمند',
        ],
        'مد و پوشاک' => [
            'price' => [290000, 4800000], 'unit' => 'عدد',
            'brands' => [['مِلِیتا', 'Melita'], ['ال‌سی وایکیکی', 'LC Waikiki'], ['زری', 'Zari'], ['بارسلونا', 'Barcelona']],
            'colors' => ['کرم', 'مشکی', 'سرمه‌ای', 'زیتونی'],
            'feature' => 'دوخت تمیز و پارچهٔ باکیفیت که بعد از شست‌وشو فرم خود را حفظ می‌کند',
        ],
        'آرایشی و بهداشتی' => [
            'price' => [180000, 6200000], 'unit' => 'عدد',
            'brands' => [['میبلین', 'Maybelline'], ['لورآل', 'Loreal'], ['درماتیکا', 'Dermatica'], ['سراوی', 'CeraVe']],
            'colors' => [],
            'feature' => 'فرمولاسیون ملایم و بدون پارابن، مناسب انواع پوست حتی پوست‌های حساس',
        ],
        'خانه و آشپزخانه' => [
            'price' => [250000, 9500000], 'unit' => 'دستگاه',
            'brands' => [['آرتا', 'Artaa'], ['گرانیت', 'Granite'], ['چینی ایران', 'China Iran'], ['دلپذیر', 'Delpezir']],
            'colors' => ['کرم', 'سبز', 'مشکی'],
            'feature' => 'ساخت باکیفیت با متریال درجه‌یک که سال‌ها در آشپزخانه دوام می‌آورد',
        ],
        'لوازم خانگی برقی' => [
            'price' => [1900000, 68000000], 'unit' => 'دستگاه',
            'brands' => [['فیلپس', 'Philips'], ['اِمرسان', 'Emersun'], ['بوش', 'Bosch'], ['پاکشوما', 'Pakshoma']],
            'colors' => ['استیل', 'سفید', 'مشکی'],
            'feature' => 'موتور قدرتمند و کم‌مصرف با عملکرد بی‌صدای قابل‌قبول برای استفادهٔ خانگی',
        ],
        'مواد غذایی و سوپر مارکت' => [
            'price' => [85000, 3200000], 'unit' => 'بسته',
            'brands' => [['اکبری', 'Akbari'], ['سبلان', 'Sabalan'], ['لاهیجان', 'Lahijan'], ['بهارنارنج', 'Baharnarenj']],
            'colors' => [],
            'feature' => 'تازه و باکیفیت با بسته‌بندی بهداشتی و تاریخ تولید روز',
        ],
        'ورزش و سفر' => [
            'price' => [420000, 9800000], 'unit' => 'عدد',
            'brands' => [['نایک', 'Nike'], ['آدیداس', 'Adidas'], ['کولر پلاس', 'CoolerPlus'], ['فیت‌پلاس', 'FitPlus']],
            'colors' => ['مشکی', 'طوسی', 'قرمز'],
            'feature' => 'مناسب استفادهٔ حرفه‌ای و تفریحی با مقاومت بالا در برابر ضربه و رطوبت',
        ],
        'ابزارآلات و تجهیزات' => [
            'price' => [850000, 24000000], 'unit' => 'دستگاه',
            'brands' => [['بوش', 'Bosch'], ['رونیکس', 'Ronix'], ['ریوند', 'Ryobi'], ['ماکیتا', 'Makita']],
            'colors' => ['آبی', 'مشکی', 'زرد'],
            'feature' => 'قدرت کافی برای کارهای حرفه‌ای ساختمانی و نصب با دوام بالا',
        ],
        'کتاب' => [
            'price' => [95000, 890000], 'unit' => 'جلد',
            'brands' => [['نشر چشمه', 'Cheshmeh'], ['نشر مرکز', 'Markaz'], ['امیرکبیر', 'AmirKabir'], ['ققنوس', 'Ghoghnoos']],
            'colors' => [],
            'feature' => 'چاپ تمیز و صحافی باکیفیت روی کاغذ بالک سبک، مناسب مطالعهٔ طولانی',
        ],
        'فرهنگی هنری' => [
            'price' => [180000, 7800000], 'unit' => 'عدد',
            'brands' => [['گالری سایه', 'Sayeh'], ['نگارخانه مهر', 'Mehr'], ['قلم‌زنی اصفهان', 'Ghalamzani']],
            'colors' => ['مسی', 'فیروزه‌ای', 'چوبی'],
            'feature' => 'کار دست هنرمندان ایرانی با جزئیات ظریف و رنگ‌بندی اصیل',
        ],
        'کودک و نوزاد و اسباب بازی' => [
            'price' => [240000, 6500000], 'unit' => 'عدد',
            'brands' => [['لگو', 'LEGO'], ['پازل ایران', 'PuzzleIran'], ['ببی‌لند', 'BabyLand'], ['تولی', 'Tooly']],
            'colors' => ['آبی', 'صورتی', 'زرد'],
            'feature' => 'مصالح بدون سرب و لبه‌های گرد و ایمن، مطابق سن درج‌شده روی بسته‌بندی',
        ],
        'سلامت و پزشکی' => [
            'price' => [350000, 12000000], 'unit' => 'عدد',
            'brands' => [['ام‌بی‌پی‌الر', 'Omron'], ['بست‌پلاس', 'BestPlus'], ['مدیکال', 'Medical']],
            'colors' => ['سفید', 'آبی'],
            'feature' => 'دارای مجوز ادارهٔ کل تجهیزات پزشکی و مناسب استفادهٔ خانگی',
        ],
        'خودرو و موتورسیکلت' => [
            'price' => [380000, 18000000], 'unit' => 'عدد',
            'brands' => [['ایران‌رادیاتور', 'IranRadiator'], ['بهاران', 'Baharan'], ['موتوژن', 'MotoGen']],
            'colors' => ['مشکی', 'نقره‌ای'],
            'feature' => 'سازگار با خودروهای ایرانی و مطابق استانداردهای سازنده',
        ],
        'طلا و جواهرات' => [
            'price' => [2800000, 95000000], 'unit' => 'عدد',
            'brands' => [['گالری زرین', 'Zarrin'], ['جوهرآرا', 'JoharAra'], ['طلای تهران', 'TehranGold']],
            'colors' => ['طلایی', 'نقره‌ای', 'رزگلد'],
            'feature' => 'آبکاری چندلایه و ضدحساسیت با ضمانت ماندگاری آبکاری',
        ],
    ];

    /** Generic fallbacks (any root). */
    private const COUNTRIES = ['ایران', 'چین', 'ترکیه', 'آلمان'];

    private const TIERS = ['پریمیوم', 'حرفه‌ای', 'اقتصادی', 'مینی', 'نسخهٔ پرو'];

    /**
     * Generate enough deterministic rows so EVERY level-2 category has
     * CHILD_MIN demo products. Reuses the flagship galleries of the same
     * family (no new disk copies). Returns the number of created rows.
     */
    private function fillChildCategories(Shop $shop): int
    {
        // imgKey → flagship slug (the public copies live at products/demo/{slug}-N.jpg)
        $groups = require database_path('seeders/data/demo-products.php');

        $familyImages = [];

        foreach ($groups as $group) {
            foreach ($group['products'] as $row) {
                $familyImages[$group['root']][$row['img']] = $row['slug'];
            }
        }

        $children = Category::query()
            ->whereNotNull('parent_id')
            ->whereHas('parent', fn ($q) => $q->whereNull('parent_id'))
            ->with('parent:id,name')
            ->get();

        $created = 0;

        foreach ($children as $index => $child) {
            $have = Product::query()
                ->where('shop_id', $shop->id)
                ->where('category_id', $child->id)
                ->count();

            for ($n = $have + 1; $n <= self::CHILD_MIN; $n++) {
                $product = $this->createGeneratedProduct($shop, $child, $n, $index, $familyImages);
                $created += $product ? 1 : 0;
            }
        }

        return $created;
    }

    /** Build and persist ONE generated product for a child category. */
    private function createGeneratedProduct(Shop $shop, Category $child, int $n, int $index, array $familyImages): ?Product
    {
        $rootName = $child->parent?->name ?? '';
        $profile = self::ROOT_PROFILES[$rootName] ?? null;

        if ($profile === null) {
            return null; // unknown root — leave it alone instead of seeding junk
        }

        $basalam = $child->basalam_id ?? $child->id;
        $slug = 'demo-c'.$basalam.'-'.$n;
        $seed = crc32($slug);

        [$brandFa, $brandEn] = $profile['brands'][$seed % count($profile['brands'])];
        $tier = self::TIERS[($seed >> 3) % count(self::TIERS)];
        $color = filled($profile['colors']) ? $profile['colors'][($seed >> 5) % count($profile['colors'])] : null;
        $country = self::COUNTRIES[($seed >> 7) % count(self::COUNTRIES)];
        $warrantyPool = ['۱۸ ماه گارانتی شرکتی', '۱۲ ماه گارانتی رسمی', '۶ ماه ضمانت اصالت و سلامت کالا'];
        $warranty = $warrantyPool[($seed >> 9) % count($warrantyPool)];

        [$min, $max] = $profile['price'];
        $price = $min + (int) (($max - $min) * (($seed >> 2) % 97) / 97);
        $price = (int) (round($price / 5000) * 5000);
        $compare = (($seed >> 4) % 10) < 4
            ? (int) (round($price * (1.05 + (($seed >> 6) % 16) / 100) / 5000) * 5000)
            : null;

        $name = trim($child->name).' '.$brandFa.' مدل '.$tier.'-'.mb_substr(strtoupper(dechex($seed)), 0, 4);

        $desc = $name.' از انتخاب‌های پرتکرار دستهٔ «'.trim($child->name).'» در فروشگاه نمونهٔ اینستاشاپ است. '
            .$profile['feature'].'. '
            .'این کالا با '.$warranty.' عرضه می‌شود و خرید آن شامل ارسال سریع و بسته‌بندی ایمن است.';

        $items = [
            ['title' => 'برند', 'value' => $brandFa.' ('.$brandEn.')'],
            ['title' => 'کشور سازنده', 'value' => $country],
        ];

        if ($color !== null) {
            $items[] = ['title' => 'رنگ', 'value' => $color];
        }

        $specs = [
            ['name' => 'مشخصات کلی', 'items' => $items],
            ['name' => 'سایر ویژگی‌ها', 'items' => [
                ['title' => 'گارانتی', 'value' => $warranty],
                ['title' => 'واحد شمارش', 'value' => $profile['unit']],
                ['title' => 'وضعیت', 'value' => 'نو و پلمپ'],
            ]],
        ];

        $special = (($seed >> 8) % 11) === 0;

        $product = Product::query()->updateOrCreate(
            ['shop_id' => $shop->id, 'slug' => $slug],
            [
                'category_id' => $child->id,
                'name' => $name,
                'description' => $desc,
                'specifications' => $specs,
                'type' => Product::TYPE_PHYSICAL,
                'price' => $price,
                'compare_at_price' => $compare,
                'stock' => 3 + (($seed >> 5) % 13),
                'weight_grams' => null,
                'unit' => $profile['unit'],
                'status' => Product::STATUS_PUBLISHED,
                'views' => 15 + (($seed >> 7) % 780),
                'is_special' => $special,
                'special_ends_at' => $special ? now()->addDays(2 + ($n % 4)) : null,
            ],
        );

        $this->attachFamilyImages($product, $rootName, $index + $n, $familyImages);

        return $product;
    }

    /**
     * Point the generated product's gallery at the already-copied public
     * files of a flagship product from the same root family — no extra
     * disk writes, no duplicated payload.
     */
    private function attachFamilyImages(Product $product, string $rootName, int $pick, array $familyImages): void
    {
        $pool = $familyImages[$rootName] ?? [];

        if ($pool === []) {
            return;
        }

        $imgKey = array_keys($pool)[$pick % count($pool)];
        $flagshipSlug = $pool[$imgKey];

        foreach ([1, 2] as $sort) {
            $path = 'products/demo/'.$flagshipSlug.'-'.$sort.'.jpg';

            if (! Storage::disk('public')->exists($path)) {
                continue;
            }

            ProductImage::query()->updateOrCreate(
                ['product_id' => $product->id, 'sort' => $sort],
                ['path' => $path],
            );
        }
    }

    /** Copy the bundled gallery files and mirror them as ProductImage rows. */
    private function syncImages(Product $product, string $imageKey, string $slug): void
    {
        $sources = [
            database_path('seeders/data/demo/products/'.$imageKey.'-1.jpg'),
            database_path('seeders/data/demo/products/'.$imageKey.'-2.jpg'),
        ];

        foreach ($sources as $index => $source) {
            if (! is_file($source)) {
                continue;
            }

            $target = "products/demo/{$slug}-".($index + 1).'.jpg';

            if (! Storage::disk('public')->exists($target)) {
                Storage::disk('public')->put($target, file_get_contents($source));
            }

            ProductImage::query()->updateOrCreate(
                ['product_id' => $product->id, 'sort' => $index + 1],
                ['path' => $target],
            );
        }
    }
}
