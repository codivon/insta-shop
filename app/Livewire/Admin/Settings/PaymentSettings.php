<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\Fmt;
use App\Support\ImagePayload;
use App\Support\Payments\GatewayIcons;
use App\Support\Payments\PaymentService;
use App\Support\Payments\SplitPlan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Admin — payment gateway settings, v0.28.0 redesign (نمونهٔ مشتری):
 * a list of expandable gateway ROWS — each row toggles its gateway
 * ON/OFF (چند درگاه می‌توانند هم‌زمان فعال باشند؛ مشتری در صفحه پرداخت
 * بین درگاه‌های فعال انتخاب می‌کند), carries its own credentials + the
 * split (تسهیم) panel inside, supports checkbox bulk enable/disable,
 * row sorting and the trash that clears a gateway's saved credentials.
 *
 * The DEFAULT gateway (پیش‌فرض) is what checkout preselects and what
 * wallet deposits fall back to. «تراکنش‌های بانکی» moved to the
 * standalone admin.payment.logs page (v0.28.0).
 */
#[Layout('layouts.admin-slot')]
class PaymentSettings extends Component
{
    use GuardsPermissions;
    use WithFileUploads;

    protected static string $permission = 'admin.settings.payment';

    /** The DEFAULT gateway — preselected at checkout + wallet deposits. */
    public string $gateway = 'local';

    /** @var array<string, string> nested: fields[gateway][field] */
    public array $fields = [];

    /** @var array<string, bool> per-gateway ON/OFF toggles (multi-enable). */
    public array $enabled = [];

    /** @var array<string, bool> split toggles per split-capable gateway */
    public array $split = [];

    /** Row ordering — the «جدیدترین»-style dropdown of the reference design. */
    public string $sort = 'default';

    /** @var list<string> rows picked with the leading checkboxes (bulk actions). */
    public array $selected = [];

    /**
     * v0.29.0 — custom gateway ICONS (قابل تغییر): iconFiles[gateway] holds
     * the live Livewire temp upload; each pick is validated + stored
     * IMMEDIATELY (updated() hook below) — it does not wait for «ذخیره
     * تنظیمات» so the preview refreshes in place.
     *
     * @var array<string, mixed>
     */
    public array $iconFiles = [];

    public function mount(): void
    {
        $this->gateway = PaymentService::activeGateway();

        foreach (PaymentService::GATEWAYS as $name => $label) {
            $this->enabled[$name] = PaymentService::isEnabled($name);

            foreach (PaymentService::CREDENTIAL_FIELDS[$name] ?? [] as $field => $fieldLabel) {
                $this->fields[$name][$field] = (string) Setting::get("payment.{$name}.{$field}", '');
            }
        }

        foreach (array_keys(SplitPlan::CAPABLE) as $name) {
            $this->split[$name] = SplitPlan::enabled($name);
        }
    }

    /** Sorted gateway map — default order / enabled-first / disabled-first. */
    public function getSortedGatewaysProperty(): array
    {
        $gateways = PaymentService::GATEWAYS;

        $pick = fn (bool $want): array => array_filter(
            $gateways,
            fn (string $label, string $key): bool => (bool) ($this->enabled[$key] ?? false) === $want,
            ARRAY_FILTER_USE_BOTH,
        );

        return match ($this->sort) {
            'enabled' => $pick(true) + $pick(false),
            'disabled' => $pick(false) + $pick(true),
            default => $gateways,
        };
    }

    /** How many toggles are ON (local counts only where it is allowed). */
    public function getEnabledCountProperty(): int
    {
        $count = 0;

        foreach (PaymentService::GATEWAYS as $name => $_) {
            if (($this->enabled[$name] ?? false) && ($name !== 'local' || PaymentService::localGatewayAllowed())) {
                $count++;
            }
        }

        return $count;
    }

    /** Bulk actions on the checked rows — state only; «ذخیره تنظیمات» persists. */
    public function bulkEnable(): void
    {
        $this->applyBulk(true);
    }

    public function bulkDisable(): void
    {
        $this->applyBulk(false);
    }

    private function applyBulk(bool $on): void
    {
        foreach ($this->selected as $name) {
            if (array_key_exists($name, PaymentService::GATEWAYS)) {
                $this->enabled[$name] = $on;
            }
        }

        $this->selected = [];

        session()->flash('admin-payment-flash', $on
            ? 'درگاه‌های انتخاب‌شده روشن شدند — برای ثبت نهایی، «ذخیره تنظیمات» را بزنید.'
            : 'درگاه‌های انتخاب‌شده خاموش شدند — برای ثبت نهایی، «ذخیره تنظیمات» را بزنید.');
    }

    public function save(): void
    {
        if (! array_key_exists($this->gateway, PaymentService::GATEWAYS)) {
            $this->addError('gateway', 'درگاه نامعتبر است.');

            return;
        }

        $pairs = [];

        /*
         * The default gateway must itself be enabled; when the admin just
         * switched its toggle off, the first enabled gateway takes over.
         */
        $default = $this->gateway;

        if (! ($this->enabled[$default] ?? false)) {
            $firstEnabled = collect(PaymentService::GATEWAYS)
                ->keys()
                ->first(fn (string $name): bool => (bool) ($this->enabled[$name] ?? false)
                    && ($name !== 'local' || PaymentService::localGatewayAllowed()));

            $default = $firstEnabled ?? $this->gateway;
        }

        $pairs['payment.gateway'] = $default;

        // ON/OFF toggles of EVERY gateway (multi-enable is the point).
        foreach (PaymentService::GATEWAYS as $name => $label) {
            $pairs["payment.{$name}.enabled"] = ! empty($this->enabled[$name]) ? '1' : '0';
        }

        // Credentials of EVERY gateway — only actually-changed values are
        // written (keeps the settings table and the activity log clean).
        foreach (PaymentService::GATEWAYS as $name => $label) {
            foreach (PaymentService::CREDENTIAL_FIELDS[$name] ?? [] as $field => $fieldLabel) {
                $value = trim((string) ($this->fields[$name][$field] ?? ''));

                if ($value !== (string) Setting::get("payment.{$name}.{$field}", '')) {
                    $pairs["payment.{$name}.{$field}"] = $value;
                }
            }
        }

        // تسهیم toggles — ALL capable gateways are persisted together so
        // the state never depends on which gateway is currently selected.
        foreach (array_keys(SplitPlan::CAPABLE) as $name) {
            $pairs["payment.{$name}.split_enabled"] = ! empty($this->split[$name]) ? '1' : '0';
        }

        $snapshot = ActivityLogger::settingsSnapshot(array_keys($pairs));

        Setting::setMany($pairs);

        $enabledLabels = collect(PaymentService::GATEWAYS)
            ->filter(fn (string $label, string $name): bool => (bool) ($this->enabled[$name] ?? false)
                && ($name !== 'local' || PaymentService::localGatewayAllowed()))
            ->implode(', ');

        $splitNote = collect($pairs)
            ->filter(fn ($value, $key) => str_ends_with($key, '.split_enabled'))
            ->map(fn ($value, $key) => (PaymentService::GATEWAYS[explode('.', $key)[1]] ?? $key)
                .' = '.($value === '1' ? 'روشن' : 'خاموش'))
            ->implode('، ');

        ActivityLogger::logSettingsChanged(
            'تنظیمات درگاه پرداخت ویرایش شد (پیش‌فرض: '.PaymentService::GATEWAYS[$default]
            .' — فعال: '.($enabledLabels !== '' ? $enabledLabels : 'هیچ').')'
            .($splitNote !== '' ? ' — پرداخت تسهیمی: '.$splitNote : ''),
            $snapshot,
            $pairs,
        );

        $this->gateway = $default;

        session()->flash('admin-payment-flash', $enabledLabels !== ''
            ? 'تنظیمات ذخیره شد؛ درگاه پیش‌فرض «'.PaymentService::GATEWAYS[$default].'» است و '
            .Fmt::fa($this->enabledCount).' درگاه فعال، مشتری در صفحه پرداخت انتخاب می‌کند.'
            : 'تنظیمات ذخیره شد؛ اما هیچ درگاه فعالی وجود ندارد — مشتری نمی‌تواند آنلاین پرداخت کند.');
    }

    /**
     * v0.29.0 — a file was picked for an icon upload: iconFiles.{gateway}
     * → validate + persist right away (PNG/JPG/WebP only — SVG custom
     * icons are refused BY DESIGN: an SVG on a web-served disk is an XSS
     * vector, the bundled brand tiles stay repo-owned and trusted).
     */
    public function updated(string $property, mixed $value): void
    {
        if (! str_starts_with($property, 'iconFiles.')) {
            return;
        }

        $gateway = substr($property, strlen('iconFiles.'));

        if (array_key_exists($gateway, PaymentService::GATEWAYS)) {
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
            'آیکون درگاه «'.PaymentService::GATEWAYS[$gateway].'» تغییر کرد (بارگذاری تصویر سفارشی)',
            $snapshot,
            [GatewayIcons::settingKey($gateway) => $path],
        );

        unset($this->iconFiles[$gateway]); // clear the picker for the next upload

        session()->flash('admin-payment-flash', 'آیکون «'.PaymentService::GATEWAYS[$gateway].'» به‌روزرسانی شد — در پنل مدیریت و صفحهٔ پرداخت نمایش داده می‌شود.');
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
            'آیکون سفارشی درگاه «'.PaymentService::GATEWAYS[$gateway].'» حذف شد — بازگشت به آیکون پیش‌فرض درگاه',
            $snapshot,
            [GatewayIcons::settingKey($gateway) => ''],
        );

        unset($this->iconFiles[$gateway]);

        session()->flash('admin-payment-flash', 'آیکون سفارشی «'.PaymentService::GATEWAYS[$gateway].'» حذف شد — آیکون پیش‌فرض درگاه بازگشت.');
    }

    /** Trash a gateway row → clears its saved credentials (env stays). */
    public function deleteCredentials(string $name): void
    {
        $fields = array_keys(PaymentService::CREDENTIAL_FIELDS[$name] ?? []);

        if ($fields === []) {
            return;
        }

        $snapshot = ActivityLogger::settingsSnapshot(
            array_map(fn (string $field): string => "payment.{$name}.{$field}", $fields),
        );

        foreach ($fields as $field) {
            Setting::forget("payment.{$name}.{$field}");
            $this->fields[$name][$field] = '';
        }

        ActivityLogger::logSettingsChanged(
            'اعتبارنامه‌های ذخیره‌شدهٔ درگاه «'.PaymentService::GATEWAYS[$name].'» حذف شد',
            $snapshot,
            array_fill_keys(
                array_map(fn (string $field): string => "payment.{$name}.{$field}", $fields),
                '',
            ),
        );

        session()->flash('admin-payment-flash', 'اعتبارنامه‌های ذخیره‌شدهٔ «'.PaymentService::GATEWAYS[$name].'» پاک شد (مقادیر فایل env همچنان معتبرند).');
    }

    public function render()
    {
        return view('livewire.admin.settings.payment-settings', [
            'gateways' => PaymentService::GATEWAYS,
            'fields' => PaymentService::CREDENTIAL_FIELDS,
            'splitCapable' => SplitPlan::CAPABLE,
        ])->title('تنظیمات درگاه پرداخت')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — تنظیمات درگاه']);
    }
}
