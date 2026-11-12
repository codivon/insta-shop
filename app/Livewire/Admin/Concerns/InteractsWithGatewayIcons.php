<?php

namespace App\Livewire\Admin\Concerns;

use App\Support\ActivityLogger;
use App\Support\ImagePayload;
use App\Support\Payments\GatewayIcons;
use App\Support\Payments\PaymentService;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * v0.31.0 — the v0.29.0 custom-gateway-ICON management (upload/validate/
 * store/remove) extracted from PaymentSettings so BOTH the direct-gateway
 * settings page and the dedicated BNPL settings section share the exact
 * same behavior: every pick is validated + stored IMMEDIATELY (does not
 * wait for «ذخیره تنظیمات»), PNG/JPG/WebP only — SVG custom icons are
 * refused BY DESIGN (an SVG on a web-served disk is an XSS vector; the
 * bundled brand tiles stay repo-owned and trusted).
 */
trait InteractsWithGatewayIcons
{
    /**
     * @var array<string, mixed> iconFiles[gateway] = live Livewire temp upload
     */
    public array $iconFiles = [];

    /** The session flash key of the host component (each page has its own). */
    protected function iconFlashKey(): string
    {
        return 'admin-payment-flash';
    }

    /** A file was picked for an icon upload → validate + persist right away. */
    public function updated(string $property, mixed $value): void
    {
        if (! str_starts_with($property, 'iconFiles.')) {
            return;
        }

        $gateway = substr($property, strlen('iconFiles.'));

        if (array_key_exists($gateway, PaymentService::GATEWAYS + PaymentService::BNPL_GATEWAYS)) {
            $this->storeIcon($gateway, $value);
        }
    }

    public function storeIcon(string $gateway, mixed $file): void
    {
        $this->validate([
            "iconFiles.{$gateway}" => ['bail', 'required', 'file', 'max:2048', 'mimes:png,jpg,jpeg,webp', ImagePayload::rule()],
        ]);

        if (! $file instanceof TemporaryUploadedFile) {
            return;
        }

        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: ''));

        if (! in_array($extension, GatewayIcons::ALLOWED_EXTENSIONS, true)) {
            $extension = match ($file->getMimeType()) {
                'image/jpeg' => 'jpg',
                'image/webp' => 'webp',
                default => 'png',
            };
        }

        $contents = (string) file_get_contents($file->getRealPath());

        $path = GatewayIcons::store($gateway, $extension, $contents);

        $snapshot = ActivityLogger::settingsSnapshot([GatewayIcons::settingKey($gateway)]);

        ActivityLogger::logSettingsChanged(
            'آیکون درگاه «'.(PaymentService::GATEWAYS[$gateway] ?? PaymentService::BNPL_GATEWAYS[$gateway] ?? $gateway).'» تغییر کرد (بارگذاری تصویر سفارشی)',
            $snapshot,
            [GatewayIcons::settingKey($gateway) => $path],
        );

        unset($this->iconFiles[$gateway]); // clear the picker for the next upload

        session()->flash($this->iconFlashKey(), 'آیکون «'.(PaymentService::GATEWAYS[$gateway] ?? PaymentService::BNPL_GATEWAYS[$gateway] ?? $gateway).'» به‌روزرسانی شد — در پنل مدیریت و صفحهٔ پرداخت نمایش داده می‌شود.');
    }

    /** Delete a gateway's CUSTOM icon → falls back to its bundled brand tile. */
    public function removeIcon(string $gateway): void
    {
        if (! GatewayIcons::hasCustom($gateway)) {
            return;
        }

        $snapshot = ActivityLogger::settingsSnapshot([GatewayIcons::settingKey($gateway)]);

        GatewayIcons::deleteCustom($gateway);

        ActivityLogger::logSettingsChanged(
            'آیکون سفارشی درگاه «'.(PaymentService::GATEWAYS[$gateway] ?? PaymentService::BNPL_GATEWAYS[$gateway] ?? $gateway).'» حذف شد — بازگشت به آیکون پیش‌فرض درگاه',
            $snapshot,
            [GatewayIcons::settingKey($gateway) => ''],
        );

        unset($this->iconFiles[$gateway]);

        session()->flash($this->iconFlashKey(), 'آیکون سفارشی «'.(PaymentService::GATEWAYS[$gateway] ?? PaymentService::BNPL_GATEWAYS[$gateway] ?? $gateway).'» حذف شد — آیکون پیش‌فرض درگاه بازگشت.');
    }
}
