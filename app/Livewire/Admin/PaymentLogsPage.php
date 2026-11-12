<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\PaymentLog;
use App\Support\Payments\PaymentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin — «تراکنش‌های بانکی» (v0.28.0): the payment events feed moved OUT
 * of the gateway settings page into its own standalone section, so the
 * settings page stays exactly that — settings. Every purchase attempt,
 * gateway redirect, split (تسهیم) decision and verification lands in
 * payment_logs and is audited here with filters + search + pagination.
 */
#[Layout('layouts.admin-slot')]
#[Title('تراکنش‌های بانکی')]
class PaymentLogsPage extends Component
{
    use GuardsPermissions;
    use WithPagination;

    protected static string $permission = 'admin.payment.logs';

    /** 'all' | gateway key */
    public string $gateway = 'all';

    /** 'all' | event key */
    public string $event = 'all';

    /** 'all' | '1' (موفق) | '0' (ناموفق) */
    public string $result = 'all';

    /** free search: order id / transaction id / message */
    public string $search = '';

    public function updatedGateway(): void
    {
        $this->resetPage();
    }

    public function updatedEvent(): void
    {
        $this->resetPage();
    }

    public function updatedResult(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->gateway = 'all';
        $this->event = 'all';
        $this->result = 'all';
        $this->search = '';

        $this->resetPage();
    }

    /** Human-readable payment-log event label (moved from PaymentSettings). */
    public function eventLabel(string $event): string
    {
        return match ($event) {
            'purchase' => 'درخواست پرداخت',
            'redirect' => 'هدایت به درگاه',
            'split_purchase' => 'درخواست پرداخت تسهیمی',
            'split_redirect' => 'هدایت به درگاه (تسهیمی)',
            'split_fallback' => 'بازگشت از تسهیم به روش معمولی',
            'precheck_failed' => 'عدم تأیید مقدماتی',
            'purchase_blocked' => 'درگاه مسدود شد',
            'verify_ok' => 'تأیید پرداخت',
            'verify_failed' => 'خطای تأیید',
            'verify_amount_mismatch' => 'ناهمخوانی مبلغ پرداخت',
            default => $event,
        };
    }

    /** Badge tone per event — green for good news, rose for failures, amber for warnings. */
    public function eventTone(string $event, bool $ok): string
    {
        if ($event === 'verify_ok') {
            return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400';
        }

        if (! $ok) {
            return 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400';
        }

        return str_starts_with($event, 'split')
            ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400'
            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300';
    }

    public function getLogsProperty(): LengthAwarePaginator
    {
        return PaymentLog::query()
            ->with('order:id,status,gateway,total_amount')
            ->when($this->gateway !== 'all', fn ($q) => $q->where('gateway', $this->gateway))
            ->when($this->event !== 'all', fn ($q) => $q->where('event', $this->event))
            ->when($this->result !== 'all', fn ($q) => $q->where('ok', $this->result === '1'))
            ->when(trim($this->search) !== '', function ($q): void {
                $term = trim($this->search);

                $q->where(function ($inner) use ($term): void {
                    $inner->where('transaction_id', 'like', "%{$term}%")
                        ->orWhere('message', 'like', "%{$term}%")
                        ->orWhere('gateway', 'like', "%{$term}%");

                    if (ctype_digit($term)) {
                        // the displayed «code» is just the zero-padded order id
                        $inner->orWhere('order_id', (int) $term);
                    }
                });
            })
            ->latest('id')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.admin.payment.payment-logs-page', [
            'logs' => $this->logs,
            'gateways' => PaymentService::GATEWAYS,
            'events' => [
                'purchase' => 'درخواست پرداخت',
                'redirect' => 'هدایت به درگاه',
                'split_purchase' => 'درخواست پرداخت تسهیمی',
                'split_redirect' => 'هدایت به درگاه (تسهیمی)',
                'split_fallback' => 'بازگشت از تسهیم به روش معمولی',
                'precheck_failed' => 'عدم تأیید مقدماتی',
                'purchase_blocked' => 'درگاه مسدود شد',
                'verify_ok' => 'تأیید پرداخت',
                'verify_failed' => 'خطای تأیید',
                'verify_amount_mismatch' => 'ناهمخوانی مبلغ پرداخت',
            ],
        ])->title('تراکنش‌های بانکی')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — تراکنش‌های بانکی']);
    }
}
