<?php

namespace App\Http\Controllers;

use App\Support\Theme;
use Symfony\Component\HttpFoundation\Response;

/**
 * PWA manifests — one installable app per panel.
 *
 * Seller and customer get SEPARATE manifests (different id, name, icons,
 * start_url) so Chrome/Android installs them as two independent apps on
 * the phone:  «اپ فروشنده»  and the customer shop app.
 *
 * Names ride on the configurable app_name (admin → تنظیمات عمومی) and the
 * theme_color follows the live palette, so rebranding keeps both PWAs in
 * sync without touching this file.
 */
class PwaController extends Controller
{
    public function seller(): Response
    {
        return $this->manifest($this->sellerManifest());
    }

    public function customer(): Response
    {
        return $this->manifest($this->customerManifest());
    }

    private function sellerManifest(): array
    {
        $app = Theme::resolve()['app_name'];

        return [
            // stable identity — changing the name/icon later keeps the
            // installed app instead of installing a second copy
            'id' => '/seller/',
            'name' => $app.' | اپ فروشنده',
            'short_name' => 'فروشنده',
            'description' => 'مدیریت فروشگاه، سفارش‌ها، محصولات و کیف پول — همیشه در جیب شما.',
            'start_url' => '/seller?source=pwa',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'dir' => 'rtl',
            'lang' => 'fa',
            'background_color' => '#ffffff',
            'theme_color' => Theme::resolve()['primary'],
            'icons' => [
                ['src' => '/pwa/icons/seller-192.png',          'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/pwa/icons/seller-512.png',          'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/pwa/icons/seller-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ];
    }

    private function customerManifest(): array
    {
        $app = Theme::resolve()['app_name'];

        return [
            'id' => '/shop/',
            'name' => $app,
            'short_name' => mb_substr($app, 0, 12),
            'description' => 'خرید آسان از فروشگاه‌های اینستاگرامی — سبد خرید، پیگیری سفارش و پشتیبانی در یک اپ.',
            'start_url' => '/shop?source=pwa',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'dir' => 'rtl',
            'lang' => 'fa',
            'background_color' => '#ffffff',
            'theme_color' => Theme::resolve()['primary'],
            'icons' => [
                ['src' => '/pwa/icons/customer-192.png',          'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/pwa/icons/customer-512.png',          'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/pwa/icons/customer-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ];
    }

    private function manifest(array $payload): Response
    {
        return response()
            ->json($payload, 200, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ->header('Content-Type', 'application/manifest+json; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=300'); // short — icon/name updates reach installed apps fast
    }
}
