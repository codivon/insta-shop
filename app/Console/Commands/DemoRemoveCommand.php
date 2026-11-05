<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\Story;
use Database\Seeders\DemoSellersSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes EVERYTHING the demo seeders (DemoBannerSeeder / DemoSellersSeeder /
 * DemoProductSeeder / DemoShopSetupSeeder) created — all 9 sample sellers +
 * shops + their products and galleries, shipping methods + weight tariffs +
 * covered cities, the bank cards, published shop filters, the demo stories
 * and all their files on the public disk.
 *
 * The demo rows are found by fixed conventions (not a registry file):
 *   - sellers on the demo phones of data/demo-sellers.php → each shop
 *     cascades products / carriers / tariffs / filter rows / stories /
 *     story views / bank cards
 *   - shop logos under shops/demo-*
 *   - product gallery files under products/demo/
 *   - story media under stories/demo-*
 *   - banner rows whose image_path starts with banners/demo-
 *
 * Usage: php artisan demo:remove
 */
class DemoRemoveCommand extends Command
{
    protected $signature = 'demo:remove';

    protected $description = 'حذف کامل محتوای نمونه (۹ فروشگاه دمو، محصولات، حمل‌ونقل، فیلترها، استوری‌ها، بنرها و تصاویر سیدر)';

    public function handle(): int
    {
        $products = 0;
        $banners = 0;
        $stories = 0;
        $shops = 0;

        // ── demo shops + everything owned by them (shop delete cascades rows) ──
        $sellers = Seller::query()
            ->whereIn('phone', DemoSellersSeeder::sellerPhones())
            ->get();

        foreach ($sellers as $seller) {
            $shop = Shop::query()->where('seller_id', $seller->id)->first();

            if ($shop !== null) {
                $products += $shop->products()->count();
                $stories += Story::query()->where('shop_id', $shop->id)->count();
                $shops++;

                if ($shop->logo_path !== null && str_starts_with($shop->logo_path, 'shops/demo-')) {
                    Storage::disk('public')->delete($shop->logo_path);
                }

                $shop->delete(); // cascades: products, galleries, carts, favorites,
                // carriers (+tariffs/cities), shop_filters (+items),
                // stories (+views/likes/comments), shipments…
            }

            $seller->delete(); // cascades the bank card(s)
        }

        if ($shops > 0) {
            $this->line("{$shops} فروشگاه نمونه با {$products} محصول و {$stories} استوری حذف شد.");
        } else {
            $this->line('فروشگاه نمونه‌ای یافت نشد (قبلاً حذف شده).');
        }

        // ── leftover demo gallery + story files (rows cascade with the shops) ──
        if (Storage::disk('public')->deleteDirectory('products/demo')) {
            $this->line('فایل‌های تصویر products/demo پاک شدند.');
        }

        foreach (Storage::disk('public')->directories('stories') as $dir) {
            if (str_starts_with(basename($dir), 'demo-') || $dir === 'stories/demo-store') {
                Storage::disk('public')->deleteDirectory($dir);
                $this->line("پوشهٔ استوری {$dir} پاک شد.");
            }
        }

        // ── demo banners / hero slides ──
        Banner::query()
            ->where('image_path', 'like', 'banners/demo-%')
            ->get()
            ->each(function (Banner $banner) use (&$banners): void {
                Storage::disk('public')->delete($banner->image_path);
                $banner->delete();
                $banners++;
            });

        $this->line("{$banners} بنر/اسلاید نمونه حذف شد.");

        $this->info('✅ محتوای نمونه به‌طور کامل پاک شد.');

        return self::SUCCESS;
    }
}
