<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Demo home visuals (v0.17.9) — the 4 default HERO SLIDER slides
 * (position=home_hero) + the 4 default 2×2 BANNER CARDS
 * (position=home_banner) with ready-made phone/computer artwork.
 *
 * Images ship inside the repo (database/seeders/data/demo/banners) and
 * are copied into the public disk on seed, so `migrate:fresh --seed`
 * rebuilds them without any manual upload.
 *
 * Idempotent: rows are matched on image_path (demo-*), so re-running
 * refreshes titles/links instead of duplicating.
 *
 * حذف: `php artisan demo:remove` همهٔ ردیف‌ها و فایل‌های نمونه را پاک می‌کند
 * (یا خط DemoBannerSeeder::class را از DatabaseSeeder بردارید).
 */
class DemoBannerSeeder extends Seeder
{
    use WithoutModelEvents;

    /** The 4 hero slides (اسلایدر اصلی) — wide 12:5 artwork. */
    private const HEROES = [
        ['file' => 'hero-1.jpg', 'title' => 'گوشی‌های پرچمدار با تخفیف ویژه', 'link' => '/shop/demo_store'],
        ['file' => 'hero-2.jpg', 'title' => 'لپ‌تاپ و کامپیوتر برای کار و بازی', 'link' => '/shop/demo_store'],
        ['file' => 'hero-3.jpg', 'title' => 'گجت‌های هوشمند؛ هدست، ساعت و ایرپاد', 'link' => '/shop/demo_store'],
        ['file' => 'hero-4.jpg', 'title' => 'جشنواره کالای دیجیتال اینستاشاپ', 'link' => '/shop/demo_store'],
    ];

    /** The 4 banner cards (چیدمان ۲×۲) — square-ish 4:3 artwork. */
    private const CARDS = [
        ['file' => 'banner-1.jpg', 'title' => 'موبایل و لوازم جانبی', 'link' => '/shop/categories'],
        ['file' => 'banner-2.jpg', 'title' => 'لپ‌تاپ گیمینگ', 'link' => '/shop/categories'],
        ['file' => 'banner-3.jpg', 'title' => 'هدفون و صوتی', 'link' => '/shop/categories'],
        ['file' => 'banner-4.jpg', 'title' => 'تجهیزات کامپیوتر', 'link' => '/shop/categories'],
    ];

    public function run(): void
    {
        foreach ([...self::HEROES, ...self::CARDS] as $item) {
            $this->copyImage($item['file']);
        }

        foreach (self::HEROES as $index => $item) {
            Banner::query()->updateOrCreate(
                ['position' => Banner::POSITION_HERO, 'image_path' => 'banners/demo-'.$item['file']],
                [
                    'title' => $item['title'],
                    'link_url' => $item['link'],
                    'sort' => $index + 1,
                    'is_active' => true,
                ],
            );
        }

        foreach (self::CARDS as $index => $item) {
            Banner::query()->updateOrCreate(
                ['position' => Banner::POSITION_BANNER, 'image_path' => 'banners/demo-'.$item['file']],
                [
                    'title' => $item['title'],
                    'link_url' => $item['link'],
                    'sort' => $index + 1,
                    'is_active' => true,
                ],
            );
        }

        $this->command?->line('  <fg=green>✓</> بنرها/اسلایدرهای نمونه (۴+۴) آماده شدند.');
    }

    /** Copy the bundled artwork into the public disk (once). */
    private function copyImage(string $file): string
    {
        $target = 'banners/demo-'.$file;
        $source = database_path('seeders/data/demo/banners/'.$file);

        if (! Storage::disk('public')->exists($target)) {
            if (! is_file($source)) {
                throw new \RuntimeException("فایل تصویر نمونه پیدا نشد: {$source} — ابتدا اسکریپت ساخت تصاویر را اجرا کنید.");
            }

            Storage::disk('public')->put($target, file_get_contents($source));
        }

        return $target;
    }
}
