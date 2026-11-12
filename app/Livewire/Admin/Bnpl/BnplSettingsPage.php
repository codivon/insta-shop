<?php

namespace App\Livewire\Admin\Bnpl;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Admin\Concerns\InteractsWithGatewayIcons;
use App\Models\BnplSettlement;
use App\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\Fmt;
use App\Support\JDate;
use App\Support\Payments\BnplSettlements;
use App\Support\Payments\PaymentService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin — «تنظیمات پرداخت اقساطی» (v0.31.0): the DEDICATED section of the
 * BNPL (installment) trio (دیجی‌پی / اسنپ‌پی / ترب‌پی). One rich card per
 * gateway with every knob the platform needs:
 *
 *   • فعال‌سازی + اعتبارنامه‌های OAuth (moved here from payment settings),
 *   • تعداد اقساط نمایشی (۲–۱۲),
 *   • سیاست تسویه: دوره (هر ماه / هر ۲ ماه / هر ۳ ماه) + روزِ ماه (۱–۲۹)
 *     + حالت (خودکار با کران / دستی فقط مدیر),
 *   • پنجرهٔ مبلغ خرید اقساطی (حداقل/حداکثر — سقف اعتباری واقعی درگاه‌ها),
 *   • شبیه‌ساز محلی + مدیریت آیکون (v0.29 block) و پیش‌نمایش زندهٔ سیاست.
 *
 * The ENGINE side lives in BnplSettlements (frequency-aware due dates +
 * auto-only cron sweep); the ops ledger stays in «تسویه اقساطی»
 * (admin.bnpl.settlements).
 */
#[Layout('layouts.admin-slot')]
#[Title('تنظیمات پرداخت اقساطی')]
class BnplSettingsPage extends Component
{
    use GuardsPermissions;
    use InteractsWithGatewayIcons;

    protected static string $permission = 'admin.bnpl.settings';

    /** @var array<string, bool> per-gateway master toggles */
    public array $enabled = [];

    /** @var array<string, array<string, string>> OAuth credentials */
    public array $fields = [];

    /** @var array<string, string> اقساط نمایشی ۲–۱۲ */
    public array $installments = [];

    /** @var array<string, string> روزِ واریز تسویه ۱–۲۹ */
    public array $settlementDay = [];

    /** @var array<string, string> دورهٔ تسویه: 1 = هر ماه، 2 = هر ۲ ماه، 3 = هر ۳ ماه */
    public array $settlementFrequency = [];

    /** @var array<string, string> حالت تسویه: auto = کران روزانه، manual = فقط واریز دستی مدیر */
    public array $settlementMode = [];

    /** @var array<string, string> حداقل مبلغ کالا (تومان — ۰ = بدون محدودیت) */
    public array $minAmount = [];

    /** @var array<string, string> حداکثر مبلغ کالا (تومان — ۰ = بی‌نهایت) */
    public array $maxAmount = [];

    /** @var array<string, bool> شبیه‌ساز محلی (فقط محیط توسعه اثر دارد) */
    public array $mock = [];

    protected function iconFlashKey(): string
    {
        return 'admin-bnpl-flash';
    }

    public function mount(): void
    {
        foreach (PaymentService::BNPL_GATEWAYS as $name => $label) {
            $this->enabled[$name] = PaymentService::isEnabled($name);

            foreach (PaymentService::BNPL_CREDENTIAL_FIELDS[$name] ?? [] as $field => $fieldLabel) {
                $this->fields[$name][$field] = (string) Setting::get("payment.{$name}.{$field}", '');
            }

            $this->installments[$name] = (string) PaymentService::bnplInstallments($name);
            $this->settlementDay[$name] = (string) PaymentService::bnplSettlementDay($name);
            $this->settlementFrequency[$name] = (string) PaymentService::bnplSettlementFrequency($name);
            $this->settlementMode[$name] = PaymentService::bnplSettlementMode($name);
            $this->minAmount[$name] = (string) PaymentService::bnplMinAmount($name);
            $this->maxAmount[$name] = (string) PaymentService::bnplMaxAmount($name);
            $this->mock[$name] = PaymentService::bnplMockEnabled($name);
        }
    }

    /** Persist the three gateways — enabled + credentials + the whole policy block. */
    public function save(): void
    {
        $this->validate([
            'settlementFrequency.*' => ['required', 'in:1,2,3'],
            'settlementMode.*' => ['required', 'in:auto,manual'],
        ]);

        $pairs = [];

        foreach (PaymentService::BNPL_GATEWAYS as $name => $label) {
            $installments = max(2, min(12, (int) Fmt::toLatin((string) ($this->installments[$name] ?? '4'))));
            $day = max(1, min(29, (int) Fmt::toLatin((string) ($this->settlementDay[$name] ?? '1'))));
            $frequency = (int) Fmt::toLatin((string) ($this->settlementFrequency[$name] ?? '1'));
            $mode = ($this->settlementMode[$name] ?? 'auto') === 'manual' ? 'manual' : 'auto';
            $min = max(0, (int) Fmt::toLatin((string) ($this->minAmount[$name] ?? '0')));
            $max = max(0, (int) Fmt::toLatin((string) ($this->maxAmount[$name] ?? '0')));

            if ($min > 0 && $max > 0 && $max < $min) {
                $this->addError("minAmount.{$name}", 'حداکثر مبلغ نمی‌تواند کمتر از حداقل باشد.');

                continue;
            }

            $pairs["payment.{$name}.enabled"] = ! empty($this->enabled[$name]) ? '1' : '0';
            $pairs["payment.{$name}.installments"] = (string) $installments;
            $pairs["payment.{$name}.settlement_day"] = (string) $day;
            $pairs["payment.{$name}.settlement_frequency_months"] = (string) $frequency;
            $pairs["payment.{$name}.settlement_mode"] = $mode;
            $pairs["payment.{$name}.min_amount"] = (string) $min;
            $pairs["payment.{$name}.max_amount"] = (string) $max;
            $pairs["payment.{$name}.mock"] = (! empty($this->mock[$name]) && PaymentService::localGatewayAllowed()) ? '1' : '0';

            foreach (PaymentService::BNPL_CREDENTIAL_FIELDS[$name] ?? [] as $field => $fieldLabel) {
                $value = trim((string) ($this->fields[$name][$field] ?? ''));

                if ($value !== (string) Setting::get("payment.{$name}.{$field}", '')) {
                    $pairs["payment.{$name}.{$field}"] = $value;
                }
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $snapshot = ActivityLogger::settingsSnapshot(array_keys($pairs));

        Setting::setMany($pairs);

        $policy = collect(PaymentService::BNPL_GATEWAYS)
            ->map(fn (string $label, string $name): string => $label.' ('.PaymentService::bnplSettlementPolicyLabel($name)
                .($this->enabled[$name] ?? false ? ' — فعال' : ' — غیرفعال').')')
            ->implode('، ');

        ActivityLogger::logSettingsChanged(
            'تنظیمات پرداخت اقساطی ویرایش شد — '.$policy,
            $snapshot,
            $pairs,
        );

        session()->flash('admin-bnpl-flash', 'تنظیمات پرداخت اقساطی ذخیره شد — سیاست تسویه از خرید بعدیِ هر درگاه اعمال می‌شود.');
    }

    /** Trash a BNPL gateway's saved credentials (env stays). */
    public function deleteCredentials(string $name): void
    {
        $fields = array_keys(PaymentService::BNPL_CREDENTIAL_FIELDS[$name] ?? []);

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
            'اعتبارنامه‌های ذخیره‌شدهٔ درگاه اقساطی «'.(PaymentService::BNPL_GATEWAYS[$name] ?? $name).'» حذف شد',
            $snapshot,
            array_fill_keys(
                array_map(fn (string $field): string => "payment.{$name}.{$field}", $fields),
                '',
            ),
        );

        session()->flash('admin-bnpl-flash', 'اعتبارنامه‌های ذخیره‌شدهٔ «'.(PaymentService::BNPL_GATEWAYS[$name] ?? $name).'» پاک شد (مقادیر فایل env همچنان معتبرند).');
    }

    /** The ledger stats (same scope as the تسویه اقساطی page chips). */
    public function getStatsProperty(): array
    {
        return [
            'pending_count' => BnplSettlement::query()->where('status', 'pending')->count(),
            'pending_amount' => (int) BnplSettlement::query()->where('status', 'pending')->sum('amount'),
            'due_now_count' => BnplSettlement::query()
                ->where('status', 'pending')->whereDate('due_date', '<=', today())->count(),
            'overdue_count' => BnplSettlement::query()
                ->where('status', 'pending')->whereDate('due_date', '<', today())->count(),
            'paid_amount' => (int) BnplSettlement::query()->where('status', 'paid')->sum('amount'),
            'last_run' => Setting::get('payment.bnpl_last_settle_run'),
        ];
    }

    /**
     * Per-gateway policy preview: the due date a purchase made RIGHT NOW
     * would get (with the edited day/frequency from the form state) +
     * the saved policy chips — the admin sees the rhythm before saving.
     *
     * @return array<string, array{frequency: int, day: int, mode: string, policy: string, next: string, nextInDays: int, pending: int, pendingAmount: int}>
     */
    public function getPolicyProperty(): array
    {
        $policy = [];

        foreach (PaymentService::BNPL_GATEWAYS as $name => $label) {
            $frequency = max(1, min(3, (int) Fmt::toLatin((string) ($this->settlementFrequency[$name] ?? '1'))));
            $day = max(1, min(29, (int) Fmt::toLatin((string) ($this->settlementDay[$name] ?? '1'))));

            $due = today()->day($day);

            if (! $due->greaterThan(today())) {
                $due = $due->addMonthNoOverflow();
            }

            if ($frequency > 1) {
                $due = $due->addMonthNoOverflow($frequency - 1);
            }

            $pending = BnplSettlement::query()
                ->where('gateway', $name)
                ->where('status', 'pending');

            $policy[$name] = [
                'frequency' => $frequency,
                'day' => $day,
                'mode' => ($this->settlementMode[$name] ?? 'auto') === 'manual' ? 'manual' : 'auto',
                'policy' => match ($frequency) {
                    2 => 'هر ۲ ماه یک‌بار',
                    3 => 'هر ۳ ماه یک‌بار',
                    default => 'هر ماه',
                },
                'next' => JDate::date($due),
                'nextInDays' => max(0, (int) today()->diffInDays($due)),
                'pending' => (clone $pending)->count(),
                'pendingAmount' => (int) (clone $pending)->sum('amount'),
            ];
        }

        return $policy;
    }

    /** Jalali stamp of the last cron sweep (chip in the header). */
    public function getLastRunFaProperty(): string
    {
        $raw = Setting::get('payment.bnpl_last_settle_run');

        if (! $raw) {
            return '';
        }

        try {
            return JDate::dateTime(Carbon::parse($raw));
        } catch (Throwable) {
            return '';
        }
    }

    public function render()
    {
        return view('livewire.admin.bnpl.settings-page', [
            'bnplGateways' => PaymentService::BNPL_GATEWAYS,
            'bnplFields' => PaymentService::BNPL_CREDENTIAL_FIELDS,
            'mockAllowed' => PaymentService::localGatewayAllowed(),
            'stats' => $this->stats,
            'policy' => $this->policy,
            'lastRunFa' => $this->lastRunFa,
        ])->title('تنظیمات پرداخت اقساطی')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — تنظیمات پرداخت اقساطی']);
    }
}
