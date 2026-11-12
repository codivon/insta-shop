<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\PaymentLog;
use App\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\Payments\PaymentService;
use App\Support\Payments\SplitPlan;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin — payment gateway settings: pick the active gateway and fill its
 * credentials (stored in the settings table, env values as fallback).
 *
 * v0.27.0 — پرداخت تسهیمی: split-capable gateways (payping,
 * asanpardakht) get an extra toggle — ON = the seller's share goes
 * DIRECTLY to the seller's bank destination through the gateway's split
 * API; OFF (default) = the seller's share is credited to the in-app
 * wallet after verification.
 */
#[Layout('layouts.admin-slot')]
class PaymentSettings extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.settings.payment';

    public string $gateway = 'local';

    /** @var array<string, string> nested: fields[gateway][field] */
    public array $fields = [];

    /** @var array<string, bool> split toggles per split-capable gateway */
    public array $split = [];

    public function mount(): void
    {
        $this->gateway = PaymentService::activeGateway();

        foreach (PaymentService::GATEWAYS as $name => $label) {
            foreach (PaymentService::CREDENTIAL_FIELDS[$name] ?? [] as $field => $fieldLabel) {
                $this->fields[$name][$field] = (string) Setting::get("payment.{$name}.{$field}", '');
            }
        }

        foreach (array_keys(SplitPlan::CAPABLE) as $name) {
            $this->split[$name] = SplitPlan::enabled($name);
        }
    }

    public function save(): void
    {
        if (! array_key_exists($this->gateway, PaymentService::GATEWAYS)) {
            $this->addError('gateway', 'درگاه نامعتبر است.');

            return;
        }

        $pairs = ['payment.gateway' => $this->gateway];

        foreach (PaymentService::CREDENTIAL_FIELDS[$this->gateway] ?? [] as $field => $fieldLabel) {
            $pairs["payment.{$this->gateway}.{$field}"] = trim((string) ($this->fields[$this->gateway][$field] ?? ''));
        }

        // تسهیم toggles — ALL capable gateways are persisted together so
        // the state never depends on which gateway is currently selected.
        foreach (array_keys(SplitPlan::CAPABLE) as $name) {
            $pairs["payment.{$name}.split_enabled"] = ! empty($this->split[$name]) ? '1' : '0';
        }

        $snapshot = ActivityLogger::settingsSnapshot(array_keys($pairs));

        Setting::setMany($pairs);

        $splitNote = collect($pairs)
            ->filter(fn ($value, $key) => str_ends_with($key, '.split_enabled'))
            ->map(fn ($value, $key) => (PaymentService::GATEWAYS[explode('.', $key)[1]] ?? $key)
                .' = '.($value === '1' ? 'روشن' : 'خاموش'))
            ->implode('، ');

        ActivityLogger::logSettingsChanged(
            'تنظیمات درگاه پرداخت ویرایش شد (درگاه فعال: '.PaymentService::GATEWAYS[$this->gateway].')'
            .($splitNote !== '' ? ' — پرداخت تسهیمی: '.$splitNote : ''),
            $snapshot,
            $pairs,
        );

        session()->flash('admin-payment-flash', 'تنظیمات پرداخت ذخیره شد؛ درگاه فعال اکنون «'.PaymentService::GATEWAYS[$this->gateway].'» است.');
    }

    public function getLogsProperty(): Collection
    {
        return PaymentLog::query()->with('order:id,status')->latest('id')->limit(12)->get();
    }

    /** Human-readable payment-log event label. */
    public function eventLabel(string $event): string
    {
        return match ($event) {
            'purchase' => 'درخواست پرداخت',
            'redirect' => 'هدایت به درگاه',
            'precheck_failed' => 'عدم تأیید مقدماتی',
            'verify_ok' => 'تأیید پرداخت',
            'verify_failed' => 'خطای تأیید',
            default => $event,
        };
    }

    public function render()
    {
        return view('livewire.admin.settings.payment-settings', [
            'gateways' => PaymentService::GATEWAYS,
            'fields' => PaymentService::CREDENTIAL_FIELDS,
            'splitCapable' => SplitPlan::CAPABLE,
            'logs' => $this->logs,
        ])->title('تنظیمات درگاه پرداخت')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — تنظیمات درگاه']);
    }
}
