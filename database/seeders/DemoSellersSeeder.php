<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\Shop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * The DEMO seller ecosystem (v0.17.14) — 9 approved sellers with realistic
 * Persian names, each owning one storefront: the digital flagship
 * «demo_store» (v0.17.9) + 8 specialty shops (خانه و آشپزخانه، بوتیک مد،
 * آرایشی، سلامت، سوپرمارکت، ابزار و خودرو، کتاب و فرهنگ، کودک و سفر).
 *
 * Every shop owns whole level-1 category roots (data/demo-sellers.php) —
 * DemoProductSeeder places each demo product inside its root's owner so
 * the marketplace looks like one big store surrounded by boutiques.
 *
 * Idempotent: sellers matched on the demo phone, shop on seller_id;
 * logo files are copied to the public disk once.
 *
 * حذف کامل: `php artisan demo:remove` همهٔ ۹ فروشگاه و محصولات/استوری‌های
 * وابسته را پاک می‌کند (یا خط DemoSellersSeeder را از DatabaseSeeder بردارید).
 */
class DemoSellersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $built = 0;

        foreach (self::sellers() as $def) {
            $this->ensureSellerAndShop($def);

            $built++;
        }

        $this->command?->line("  <fg=green>✓</> {$built} فروشگاه نمونهٔ تأییدشده (۹ فروشنده با نام‌های واقعی) آماده شد.");
    }

    /** The demo seller/shop definitions (shared with the other demo seeders). */
    public static function sellers(): array
    {
        return require database_path('seeders/data/demo-sellers.php');
    }

    /** All demo seller phones — the stable identity of the whole demo data set. */
    public static function sellerPhones(): array
    {
        return array_column(self::sellers(), 'phone');
    }

    /** Demo shops (with seller) keyed by the storefront handle. */
    public static function shopByHandle(): Collection
    {
        return Shop::query()
            ->whereHas('seller', fn ($q) => $q->whereIn('phone', self::sellerPhones()))
            ->with('seller:id,phone,instagram_username,full_name,status')
            ->get()
            ->keyBy(fn (Shop $shop) => (string) $shop->storefrontHandle());
    }

    /** Root (level-1 category name) → the demo shop that owns its catalog. */
    public static function shopByRoot(): array
    {
        $map = [];

        foreach (self::sellers() as $def) {
            foreach ($def['roots'] as $root) {
                $map[$root] = $def['handle'];
            }
        }

        return $map;
    }

    /** Create-or-refresh ONE demo seller + its shop (+ bundled logo). */
    private function ensureSellerAndShop(array $def): void
    {
        $seller = Seller::query()->updateOrCreate(
            ['phone' => $def['phone']],
            [
                'instagram_username' => $def['handle'],
                'full_name' => $def['full_name'],
                'status' => 'approved',
                'phone_verified_at' => now(),
            ],
        );

        Shop::query()->updateOrCreate(
            ['seller_id' => $seller->id],
            [
                'name' => $def['shop_name'],
                'instagram_username' => $def['handle'],
                'about' => $def['about'],
                'logo_path' => $this->storeLogo($def),
                'phone' => $def['shop_phone'],
                'address' => $def['address'],
                'status' => 'active',
                'show_stock' => true,
            ],
        );
    }

    /** Copy the bundled logo into the public disk (once). */
    private function storeLogo(array $def): ?string
    {
        $source = database_path('seeders/data/demo/logos/'.$def['logo']);

        if (! is_file($source)) {
            return Shop::query()
                ->whereHas('seller', fn ($q) => $q->where('phone', $def['phone']))
                ->value('logo_path'); // artwork not bundled → keep whatever exists
        }

        $target = $def['logo_target'];

        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($source));
        }

        return $target;
    }
}
