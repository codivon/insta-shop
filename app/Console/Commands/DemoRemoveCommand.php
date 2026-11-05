<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\Story;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes EVERYTHING the demo seeders (DemoBannerSeeder / DemoProductSeeder /
 * DemoShopSetupSeeder) created — the sample seller + shop + its products and
 * galleries, shipping methods + weight tariffs + covered cities, the bank
 * card, published shop filters, the 10 demo stories and all their files on
 * the public disk.
 *
 * The demo rows are found by fixed conventions (not a registry file):
 *   - seller phone 989120000000 (normalized) → its shop cascades products / carriers /
 *     tariffs / filter rows / stories / story views / bank card
 *   - product gallery files under products/demo/
 *   - story media under stories/demo-store/
 *   - banner rows whose image_path starts with banners/demo-
 *
 * Usage: php artisan demo:remove
 */
class DemoRemoveCommand extends Command
{
    protected $signature = 'demo:remove';

    protected $description = 'حذف کامل محتوای نمونه (فروشگاه، محصولات، حمل‌ونقل، فیلترها، استوری‌ها، بنرها و تصاویر سیدر دمو)';

    private const SELLER_PHONE = '989120000000'; // normalized (E.164 without +) — matches Phone::normalize()

    public function handle(): int
    {
        $products = 0;
        $banners = 0;
        $stories = 0;

        // ── demo shop + everything owned by it (shop delete cascades rows) ──
        $seller = Seller::query()->where('phone', self::SELLER_PHONE)->first();

        if ($seller !== null) {
            $shop = Shop::query()->where('seller_id', $seller->id)->first();

            if ($shop !== null) {
                $products = $shop->products()->count();
                $stories = Story::query()->where('shop_id', $shop->id)->count();

                if ($shop->logo_path !== null && str_starts_with($shop->logo_path, 'shops/demo-')) {
                    Storage::disk('public')->delete($shop->logo_path);
                }

                $shop->delete(); // cascades: products, galleries, carts, favorites,
                // carriers (+tariffs/cities), shop_filters (+items),
                // stories (+views/likes/comments), shipments…
            }

            $seller->delete(); // cascades the bank card(s)

            $this->line("فروشگاه نمونه با {$products} محصول و {$stories} استوری حذف شد.");
        } else {
            $this->line('فروشگاه نمونه‌ای یافت نشد (قبلاً حذف شده).');
        }

        // ── leftover demo gallery + story files (rows cascade with the shop) ──
        if (Storage::disk('public')->deleteDirectory('products/demo')) {
            $this->line('فایل‌های تصویر products/demo پاک شدند.');
        }

        if (Storage::disk('public')->deleteDirectory('stories/demo-store')) {
            $this->line('فایل‌های استوری stories/demo-store پاک شدند.');
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
