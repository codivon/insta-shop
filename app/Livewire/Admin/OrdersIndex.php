<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Support\Fmt;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — all orders (list / status filter / search by refs & phone).
 */
#[Layout('layouts.admin-slot')]
class OrdersIndex extends Component
{
    public string $search = '';

    #[Url]
    public string $status = 'all'; // all|pending|paid|failed|canceled

    public int $perPage = 12;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function mount(): void
    {
        if (! in_array($this->status, ['all', ...Order::STATUSES], true)) {
            $this->status = 'all';
        }
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'status'], true)) {
            $this->perPage = 12;
            $this->hasMore = true;
        }
    }

    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 400) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage   += 12;
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $byStatus = Order::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $out = ['all' => (int) $byStatus->sum()];

        foreach (Order::STATUSES as $status) {
            $out[$status] = (int) ($byStatus[$status] ?? 0);
        }

        return $out;
    }

    public function getOrdersProperty(): Collection
    {
        $term = Fmt::toLatin($this->search);

        $query = Order::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('receipt_ref', 'like', "%{$term}%")
                ->orWhere('gateway_ref', 'like', "%{$term}%")
                ->orWhere('delivery_phone', 'like', "%{$term}%")
                ->orWhereHas('customer', fn ($c) => $c->where('phone', 'like', "%{$term}%"))))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->with(['customer:id,full_name,phone', 'items'])
            ->latest('id');

        $total = (clone $query)->count();
        $rows  = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.admin.orders-index', [
            'orders'  => $this->orders,
            'stats'   => $this->stats,
            'hasMore' => $this->hasMore,
        ])->title('مدیریت سفارش‌ها')
          ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — سفارش‌ها']);
    }
}
