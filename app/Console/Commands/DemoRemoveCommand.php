<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Seller;
use App\Models\Shop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes EVERYTHING the demo seeders (DemoBannerSeeder / DemoProductSeeder)
 * created — the sample seller + shop + its products and galleries, the
 * demo banner/slider rows and all their files on the public disk.
 *
 * The demo rows are found by fixed conventions (not a registry file):
 *   - seller phone 09120000000 → its shop cascades products/cart/favorites
 *   - product gallery files under products/demo/
 *   - banner rows whose image_path starts with banners/demo-
 *
 * Usage: php artisan demo:remove
 */
class DemoRemoveCommand extends Command
{
    protected $signature = 'demo:remove';

    protected $description = 'حذف کامل محتوای نمونه (فروشگاه، محصولات، بنرها و تصاویر سیدر دمو)';

    private const SELLER_PHONE = '09120000000';

    public function handle(): int
    {
        $products = 0;
        $banners = 0;

        // ── demo shop + products (shop delete cascades rows) ──
        $seller = Seller::query()->where('phone', self::SELLER_PHONE)->first();

        if ($seller !== null) {
            $shop = Shop::query()->where('seller_id', $seller->id)->first();

            if ($shop !== null) {
                $products = $shop->products()->count();

                if ($shop->logo_path !== null && str_starts_with($shop->logo_path, 'shops/demo-')) {
                    Storage::disk('public')->delete($shop->logo_path);
                }

                $shop->delete();
            }

            $seller->delete();

            $this->line("فروشگاه نمونه و {$products} محصول آن حذف شد.");
        } else {
            $this->line('فروشگاه نمونه‌ای یافت نشد (قبلاً حذف شده).');
        }

        // ── leftover demo gallery files (rows cascade with the shop) ──
        if (Storage::disk('public')->deleteDirectory('products/demo')) {
            $this->line('فایل‌های تصویر products/demo پاک شدند.');
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
