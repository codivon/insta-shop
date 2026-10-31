<?php

namespace App\Livewire\Admin\Settings;

use App\Models\PaymentLog;
use App\Models\Setting;
use App\Support\Payments\PaymentService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin — payment gateway settings: pick the active gateway and fill its
 * credentials (stored in the settings table, env values as fallback).
 */
#[Layout('layouts.admin-slot')]
class PaymentSettings extends Component
{
    public string $gateway = 'local';

    /** @var array<string, string> nested: fields[gateway][field] */
    public array $fields = [];

    public function mount(): void
    {
        $this->gateway = PaymentService::activeGateway();

        foreach (PaymentService::GATEWAYS as $name => $label) {
            foreach (PaymentService::CREDENTIAL_FIELDS[$name] ?? [] as $field => $fieldLabel) {
                $this->fields[$name][$field] = (string) Setting::get("payment.{$name}.{$field}", '');
            }
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

        Setting::setMany($pairs);

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
            'logs' => $this->logs,
        ])->title('تنظیمات درگاه پرداخت')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — تنظیمات درگاه']);
    }
}
