<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Models\ShopAttribute;
use App\Models\ShopAttributeValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Demo catalog (v0.17.9 → v0.17.14) — یک مارکت‌پلیس نمونه از ۹ فروشگاه
 * تأییدشده (DemoSellersSeeder) با محصولات واقعی‌نمای باسلام/دیجی‌کالا-سبک:
 *
 *   ① ۴۳ محصول «پرچمدار» دست‌نویس (data/demo-products.php) با گالری اختصاصی
 *      روی زیر‌دسته‌های منتخب هر ۱۴ دستهٔ اصلی.
 *   ② توزیع بین فروشگاه‌ها (v0.17.14): هر دستهٔ اصلی متعلق به یک فروشگاهِ
 *      دمو است (data/demo-sellers.php) و همهٔ محصولات آن دسته — پرچمدار و
 *      تولیدی — در همان فروشگاه ساخته می‌شوند؛ کالای دیجیتال در
 *      demo_store، خانه و آشپزخانه در مهرجان و…. روی دیتابیس‌های قدیمی
 *      محصولات با «همان اسلاگ» به فروشگاه مالکِ دسته منتقل می‌شوند
 *      (جابجایی، نه تکرار).
 *   ③ «پُرکنِ» زیر‌دسته‌ها (v0.17.11): هر زیر‌دستهٔ سطح-۲ حداقل ۳ محصول
 *      می‌گیرد — کمبودها با ردیف‌های تولیدشدهٔ قطعی (deterministic) از
 *      پروفایل همان دستهٔ اصلی پر می‌شود و گالری‌ها به تصاویر عمومیِ همان
 *      خانواده اشاره می‌کنند (کپی فایل جدیدی روی دیسک نمی‌شود).
 *   ④ تنوع‌ها (v0.17.12): هر محصولِ دمو حداقل ۲ تنوع قابل‌فروش — ۴۳ محصول
 *      پرچمدار با ماتریس دست‌نویس (data/demo-product-variants.php) و
 *      محصولات تکمیلی با تنوع‌های قطعی هم‌خانواده (رنگ/گارانتی/سایز).
 *      قیمت تنوع اول = قیمت پایهٔ محصول تا قیمت کارت‌ها ثابت بماند و
 *      موجودی = مجموع تنوع‌ها (syncAggregates).
 *
 * Images for the flagship rows ship inside the repo
 * (database/seeders/data/demo/products) and are copied into the public
 * disk on seed — no manual upload needed.
 *
 * Idempotent: demo products are matched on their slug ACROSS all demo
 * shops (created / moved / updated — never duplicated), gallery rows on
 * (product_id, sort).
 *
 * حذف کامل: `php artisan demo:remove` همهٔ فروشگاه‌ها، محصولات، تصاویر و
 * بنرهای نمونه را یک‌جا پاک می‌کند (یا خطوط Demo*Seeder را از
 * DatabaseSeeder بردارید).
 */
class DemoProductSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // v0.17.14 — the 9 demo sellers/shops (idempotent, safe standalone).
        $this->call(DemoSellersSeeder::class);

        $shops = DemoSellersSeeder::shopByHandle();
        $shopByRoot = DemoSellersSeeder::shopByRoot();

        if ($shops->isEmpty()) {
            $this->command?->warn('  هیچ فروشگاه نمونه‌ای ساخته نشد — DemoProductSeeder متوقف شد.');

            return;
        }

        $groups = require database_path('seeders/data/demo-products.php');

        $created = 0;
        $moved = 0;

        foreach ($groups as $group) {
            $shop = $shops->get($shopByRoot[$group['root']] ?? '')
                ?? $shops->get('demo_store'); // unknown root → the flagship store

            foreach ($group['products'] as $row) {
                $category = $this->resolveCategory($group['root'], $row['sub']);

                if ($category === null) {
                    $this->command?->warn("  دستهٔ «{$row['sub']}» زیر «{$group['root']}» پیدا نشد — پرش از {$row['name']}");

                    continue;
                }

                [$product, $didMove] = $this->upsertDemoProduct($shop, $category, $row);

                if ($product === null) {
                    continue;
                }

                $this->syncImages($product, $row['img'], (string) $product->slug);
                $created++;
                $moved += $didMove ? 1 : 0;
            }
        }

        $generated = $this->fillChildCategories($shopByRoot);

        if ($generated > 0) {
            $this->command?->line("  <fg=green>✓</> {$generated} محصول تکمیلی برای زیر‌دسته‌های کم‌محصول ساخته شد.");
        }

        $variants = $this->seedVariants($shops);

        $this->command?->line("  <fg=green>✓</> {$created} محصول پرچمدار بین {$shops->count()} فروشگاه نمونه توزیع شد".($moved > 0 ? " ({$moved} ردیف قدیمی به فروشگاه مالکِ دسته منتقل شد)" : '').'.');
        $this->command?->line("  <fg=green>✓</> {$variants} تنوع قابل‌فروش روی محصولات نمونه ثبت شد (هر محصول ≥ ۲ تنوع).");
    }

    // ── helpers ──────────────────────────────────────────────────────

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

    /**
     * The demo row with this slug — wherever it currently lives among the
     * demo shops. Production databases seeded before v0.17.14 hold every
     * product inside demo_store; matching by slug lets the seeder MOVE
     * those rows to the root-owning shop instead of duplicating them.
     */
    private function findDemoProduct(string $slug): ?Product
    {
        return Product::query()
            ->whereIn('shop_id', $this->demoShopIds())
            ->where('slug', $slug)
            ->first();
    }

    private array $demoShopIdCache = [];

    /** Ids of all demo shops (cached per run). */
    private function demoShopIds(): array
    {
        if ($this->demoShopIdCache === []) {
            $this->demoShopIdCache = Shop::query()
                ->whereHas('seller', fn ($q) => $q->whereIn('phone', DemoSellersSeeder::sellerPhones()))
                ->pluck('id')
                ->all();
        }

        return $this->demoShopIdCache;
    }

    /**
     * Create-or-move-or-update ONE flagship product inside its root-owning
     * shop. Returns [product, moved] — moved=true when an existing demo row
     * changed shop (v0.17.14 distribution on legacy databases).
     */
    private function upsertDemoProduct(Shop $shop, Category $category, array $row): array
    {
        $slug = (string) $row['slug'];
        $product = $this->findDemoProduct($slug);
        $moved = false;

        if ($product === null && Product::query()->where('slug', $slug)->exists()) {
            // slug taken by a NON-demo row (never happens on demo DBs) —
            // suffix ours instead of colliding with the unique index.
            $slug .= '-'.$shop->id;

            $this->command?->warn("  اسلاگ «{$row['slug']}» اشغال است — محصول در {$shop->name} با اسلاگ «{$slug}» ساخته شد.");
        }

        if ($product !== null) {
            $moved = (int) $product->shop_id !== (int) $shop->id;
        } else {
            $product = new Product;
        }

        $specialEnds = ! empty($row['special'])
            ? now()->addDays((int) ($row['special_days'] ?? 3))
            : null;

        $product->forceFill([
            'shop_id' => $shop->id,
            'slug' => $slug,
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
        ])->save();

        return [$product, $moved];
    }

    // ── v0.17.12: variations (تنوع‌های قابل‌فروش هر محصول دمو) ──────────

    /** Extra select attributes every demo shop needs beyond the 3 defaults. */
    private const EXTRA_ATTRIBUTES = [
        'حافظه داخلی' => ['۳۲ گیگابایت', '۶۴ گیگابایت', '۲۵۶ گیگابایت', '۵۱۲ گیگابایت', '۱ ترابایت'],
        'حجم' => ['۶۰ میلی‌لیتر', '۱۰۰ میلی‌لیتر', '۲۰۰ میلی‌لیتر'],
        'وزن' => ['۱ گرم', '۲.۵ گرم', '۵ گرم', '۵۰۰ گرم', '۹۰۰ گرم', '۱ کیلوگرم', '۱.۸ کیلوگرم', '۲۰ کیلوگرم', '۳۰ کیلوگرم', '۴۰ کیلوگرم'],
        'جلد' => ['شومیز', 'سخت', 'گالنگان'],
        'تعداد قطعه' => ['۲۴ عددی', '۳۶ عددی', '۴۸ عددی', '۴۸۰ عددی', '۵۰۰ عددی', '۹۰۰ عددی', '۱۰۰۰ عددی'],
        'قطر' => ['۳۰ سانتی‌متر', '۳۵ سانتی‌متر', '۴۰ سانتی‌متر'],
        'طعم' => ['شکلاتی', 'وانیلی', 'توت‌فرنگی'],
        'سال ضرب' => ['۱۴۰۳', '۱۴۰۴'],
    ];

    /** Extra رنگ swatches (flagship names + generated profile colors) with hex. */
    private const EXTRA_COLORS = [
        'کرم' => '#F5E7C6', 'سرمه‌ای' => '#1F3A5F', 'زیتونی' => '#708238',
        'استیل' => '#B7BDC3', 'طوسی' => '#8A8D91', 'زرد' => '#FACC15',
        'مسی' => '#B87333', 'فیروزه‌ای' => '#2DD4BF', 'چوبی' => '#8B5E3C',
        'رزگلد' => '#E8B4B8', 'نارنجی' => '#F97316',
        'تیتانیوم طبیعی' => '#C2BBB1', 'تیتانیوم آبی' => '#546A7B',
        'تیتانیوم مشکی' => '#2F2F31', 'تیتانیوم خاکستری' => '#6E7A85',
        'تیتانیوم بنفش' => '#8E7C9C',
    ];

    /** Shoe sizes appended to the default سایز field (letter sizes XS..XXL). */
    private const SHOE_SIZES = ['۳۹', '۴۰', '۴۱', '۴۲', '۴۳', '۴۴', '۴۵'];

    /** Option pools for the deterministic generated variants. */
    private const WARRANTY_POOL = ['۱۲ ماه گارانتی', '۱۸ ماه گارانتی'];

    private const SIZE_POOL = ['M', 'L', 'XL'];

    private const VOLUME_POOL = ['۶۰ میلی‌لیتر', '۱۰۰ میلی‌لیتر', '۲۰۰ میلی‌لیتر'];

    private const WEIGHT_POOL = ['۵۰۰ گرم', '۹۰۰ گرم', '۱ کیلوگرم'];

    private const COVER_POOL = ['شومیز', 'سخت'];

    /** Per-root variation plan for generated products (pool + take per dimension). */
    private const VAR_DIMS = [
        'کالای دیجیتال' => [['colors', 2], ['warranty', 1]],
        'مد و پوشاک' => [['colors', 2], ['size', 2]],
        'آرایشی و بهداشتی' => [['volume', 2]],
        'خانه و آشپزخانه' => [['colors', 3]],
        'لوازم خانگی برقی' => [['colors', 2], ['warranty', 1]],
        'مواد غذایی و سوپر مارکت' => [['weight', 2]],
        'ورزش و سفر' => [['colors', 3]],
        'ابزارآلات و تجهیزات' => [['warranty', 2]],
        'کتاب' => [['cover', 2]],
        'فرهنگی هنری' => [['colors', 2]],
        'کودک و نوزاد و اسباب بازی' => [['colors', 2]],
        'سلامت و پزشکی' => [['colors', 2]],
        'خودرو و موتورسیکلت' => [['colors', 2]],
        'طلا و جواهرات' => [['colors', 2]],
    ];

    /**
     * Give EVERY demo product of EVERY demo shop at least 2 sellable
     * variants. Flagships use the hand-written matrix
     * (data/demo-product-variants.php), generated rows get deterministic
     * combos from the same root profile. Returns the variant row count.
     */
    private function seedVariants(Collection $shops): int
    {
        $flagship = require database_path('seeders/data/demo-product-variants.php');

        $count = 0;

        foreach ($shops as $shop) {
            $index = $this->ensureVariationAttributes($shop);

            $products = Product::query()
                ->where('shop_id', $shop->id)
                ->where('type', Product::TYPE_PHYSICAL)
                ->with('category.parent')
                ->get();

            foreach ($products as $product) {
                $specs = $flagship[$product->slug] ?? $this->generateVariantSpecs($product);

                if ($specs === []) {
                    continue; // unknown profile — leave the plain product alone
                }

                $count += $this->syncProductVariants($product, $specs, $index);
            }
        }

        return $count;
    }

    /**
     * Defaults (رنگ/سایز/گارانتی — WithoutModelEvents silences the created
     * hook so re-seeding must call this explicitly) + the extra fields,
     * color swatches and shoe sizes. Returns name → model/id lookup.
     *
     * @return array{attrs: array<string, ShopAttribute>, values: array<string, array<string, int>>}
     */
    private function ensureVariationAttributes(Shop $shop): array
    {
        ShopAttribute::seedDefaultsFor($shop);

        $sort = (int) ($shop->attributes()->max('sort') ?? 0);

        foreach (self::EXTRA_ATTRIBUTES as $name => $values) {
            $attribute = ShopAttribute::query()->updateOrCreate(
                ['shop_id' => $shop->id, 'name' => $name],
                ['type' => 'select', 'is_default' => false, 'sort' => ++$sort],
            );

            foreach ($values as $i => $value) {
                ShopAttributeValue::query()->updateOrCreate(
                    ['shop_attribute_id' => $attribute->id, 'value' => $value],
                    ['sort' => $i],
                );
            }
        }

        $attributes = $shop->attributes()->with('values')->get()->keyBy('name');

        $addValues = function (ShopAttribute $attribute, array $values): void {
            foreach ($values as $i => [$value, $hex]) {
                ShopAttributeValue::query()->updateOrCreate(
                    ['shop_attribute_id' => $attribute->id, 'value' => $value],
                    ['color_hex' => $hex, 'sort' => 100 + $i],
                );
            }
        };

        if (($colors = $attributes->get('رنگ')) !== null) {
            $addValues($colors, array_map(
                fn ($name, $hex) => [$name, $hex],
                array_keys(self::EXTRA_COLORS),
                self::EXTRA_COLORS,
            ));
        }

        if (($sizes = $attributes->get('سایز')) !== null) {
            $addValues($sizes, array_map(fn ($size) => [$size, null], self::SHOE_SIZES));
        }

        // Re-fetch so the index carries every freshly added option value.
        $attributes = $shop->attributes()->with('values')->get()->keyBy('name');

        $values = [];

        foreach ($attributes as $name => $attribute) {
            $values[$name] = $attribute->values->pluck('id', 'value')->all();
        }

        return ['attrs' => $attributes->all(), 'values' => $values];
    }

    /**
     * Idempotent variant sync for ONE product — existing rows are matched
     * on their attribute-pick signature (kept when identical → cart ids
     * survive re-seeding), new combinations are created, leftovers deleted.
     * Ends with syncAggregates() so price = min(variants), stock = Σ.
     */
    private function syncProductVariants(Product $product, array $specs, array $index): int
    {
        $desired = [];

        foreach (array_values($specs) as $i => $spec) {
            $picks = [];

            foreach ($spec['picks'] ?? [] as $attrName => $valueName) {
                $attribute = $index['attrs'][$attrName] ?? null;
                $valueId = $index['values'][$attrName][$valueName] ?? null;

                if ($attribute === null || $valueId === null) {
                    $this->command?->warn("  ویژگی «{$attrName}: {$valueName}» برای {$product->slug} پیدا نشد — پرش از این تنوع.");

                    continue 2;
                }

                $picks[$attribute->id] = $valueId;
            }

            if ($picks === []) {
                continue;
            }

            ksort($picks);

            $signature = collect($picks)->map(fn ($valueId, $attrId) => $attrId.':'.$valueId)->implode('|');

            $desired[$signature] = [
                'picks' => $picks,
                'price' => (int) $spec['price'],
                'stock' => (int) ($spec['stock'] ?? 3),
                'discount' => (int) ($spec['discount'] ?? 0),
                'sort' => count($desired),
            ];
        }

        if (count($desired) < 2) {
            return 0; // a sellable variation matrix needs at least two rows
        }

        $existing = $product->variants()->with('values')->get()
            ->keyBy(fn (ProductVariant $variant) => $variant->values
                ->sortBy('shop_attribute_id')
                ->map(fn ($value) => $value->shop_attribute_id.':'.$value->shop_attribute_value_id)
                ->implode('|'));

        $kept = [];

        foreach ($desired as $signature => $row) {
            $variant = $existing->get($signature);

            $payload = [
                'price' => $row['price'],
                'stock' => $row['stock'],
                'discount_percent' => $row['discount'],
                'discount_expires_at' => $row['discount'] > 0 ? now()->addDays(3 + ($row['sort'] % 4))->endOfDay() : null,
                'status' => 'active',
                'sort' => $row['sort'],
            ];

            if ($variant === null) {
                $variant = $product->variants()->create($payload);

                foreach ($row['picks'] as $attrId => $valueId) {
                    $variant->values()->create([
                        'shop_attribute_id' => $attrId,
                        'shop_attribute_value_id' => $valueId,
                    ]);
                }
            } else {
                $variant->fill($payload)->save();
            }

            $kept[] = $signature;
        }

        foreach ($existing as $signature => $variant) {
            if (! in_array($signature, $kept, true)) {
                $variant->delete(); // stale combination no longer in the spec
            }
        }

        $product->syncAggregates();

        return count($desired);
    }

    /** Deterministic 2-4 variant specs for a generated (filler) product. */
    private function generateVariantSpecs(Product $product): array
    {
        $rootName = $product->category?->parent?->name ?? '';
        $profile = self::ROOT_PROFILES[$rootName] ?? null;
        $plan = self::VAR_DIMS[$rootName] ?? null;

        if ($profile === null || $plan === null) {
            return [];
        }

        $seed = crc32($product->slug);

        $pools = [
            'colors' => $profile['colors'],
            'warranty' => self::WARRANTY_POOL,
            'size' => self::SIZE_POOL,
            'volume' => self::VOLUME_POOL,
            'weight' => self::WEIGHT_POOL,
            'cover' => self::COVER_POOL,
        ];

        $attrNameOf = [
            'colors' => 'رنگ', 'warranty' => 'گارانتی', 'size' => 'سایز',
            'volume' => 'حجم', 'weight' => 'وزن', 'cover' => 'جلد',
        ];

        $dimensions = [];

        foreach ($plan as $i => [$pool, $take]) {
            $values = $pools[$pool] ?? [];

            if ($values === []) {
                continue;
            }

            $count = min(count($values), $take + (($seed >> (11 + $i)) % 2));
            $offset = ($seed >> (13 + $i)) % count($values);

            $picked = [];

            for ($n = 0; $n < $count; $n++) {
                $picked[] = $values[($offset + $n) % count($values)];
            }

            $dimensions[$attrNameOf[$pool]] = $picked;
        }

        if ($dimensions === []) {
            return [];
        }

        // Cartesian product (attribute name → pick), capped at 4 combos.
        $combos = [[]];

        foreach ($dimensions as $attrName => $dimension) {
            $next = [];

            foreach ($combos as $combo) {
                foreach ($dimension as $value) {
                    $next[] = $combo + [$attrName => $value];
                }
            }

            $combos = array_slice($next, 0, 4);
        }

        $specs = [];

        foreach ($combos as $i => $picks) {
            $price = $i === 0
                ? (int) $product->price
                : (int) (round($product->price * (1.01 + (($seed >> (2 + $i)) % 8) / 100) / 1000) * 1000);

            $discount = $i > 0 && (($seed >> (5 + $i)) % 6) === 0
                ? 5 + (($seed >> (7 + $i)) % 8)
                : 0;

            $specs[] = [
                'picks' => $picks,
                'price' => $price,
                'stock' => 1 + (($seed >> (3 + $i)) % 6),
                'discount' => $discount,
            ];
        }

        return $specs;
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
     * CHILD_MIN demo products inside the shop that owns its root.
     * Reuses the flagship galleries of the same family (no new disk
     * copies). Returns the number of created rows.
     */
    private function fillChildCategories(array $shopByRoot): int
    {
        $groups = require database_path('seeders/data/demo-products.php');

        // imgKey → flagship slug (the public copies live at products/demo/{slug}-N.jpg)
        $familyImages = [];

        foreach ($groups as $group) {
            foreach ($group['products'] as $row) {
                $familyImages[$group['root']][$row['img']] = $row['slug'];
            }
        }

        $shops = DemoSellersSeeder::shopByHandle();

        $children = Category::query()
            ->whereNotNull('parent_id')
            ->whereHas('parent', fn ($q) => $q->whereNull('parent_id'))
            ->with('parent:id,name')
            ->get();

        $created = 0;

        foreach ($children as $index => $child) {
            $shop = $shops->get($shopByRoot[$child->parent?->name] ?? '');

            if ($shop === null) {
                continue; // root without a demo owner (non-demo category tree)
            }

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

        $product = $this->findDemoProduct($slug) ?? new Product;

        if ($product->exists && (int) $product->shop_id !== (int) $shop->id) {
            // legacy layout — move the generated row to the root owner too
            $this->command?->line("  محصول تکمیلی «{$slug}» به {$shop->name} منتقل شد.");
        }

        $product->forceFill([
            'shop_id' => $shop->id,
            'slug' => $product->exists ? $product->slug : $slug,
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
        ])->save();

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
