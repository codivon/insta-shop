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
 * Demo catalog (v0.17.9) — یک فروشگاه نمونهٔ تأییدشده + محصول واقعی‌نمای
 * باسلام/دیجی‌کالا-سبک برای هر ۱۴ دستهٔ اصلی (۳ یا ۴ کالا در هر دسته،
 * جمعاً ۴۳ محصول) با تنوع قیمت، تخفیف «compare_at_price»، چند ردیف
 * «فروش ویژه»، توضیحات فارسی و گروه‌های مشخصهٔ دیجی‌کالا-سبک.
 *
 * Images ship inside the repo (database/seeders/data/demo/products) and
 * are copied into the public disk on seed — no manual upload needed.
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

        $this->command?->line("  <fg=green>✓</> فروشگاه نمونه با {$created} محصول در دسته‌بندی‌ها ساخته شد.");
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
