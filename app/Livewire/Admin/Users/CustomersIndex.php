<?php

namespace App\Livewire\Admin\Users;

use App\Models\Customer;
use App\Support\Fmt;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — customers management (list / search / block-unblock).
 */
#[Layout('layouts.admin-slot')]
class CustomersIndex extends Component
{
    public string $search = '';

    #[Url]
    public string $status = 'all'; // all|active|blocked

    public int $perPage = 12;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

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

    public function toggleBlock(int $customerId): void
    {
        $customer = Customer::query()->findOrFail($customerId);

        if ($customer->status === 'blocked') {
            $customer->forceFill(['status' => 'active'])->save();
            session()->flash('admin-users-flash', 'مشتری «'.($customer->full_name ?: Fmt::toFa($customer->phone)).'» رفع مسدودی شد.');
        } else {
            $customer->forceFill(['status' => 'blocked'])->save();
            session()->flash('admin-users-flash', 'مشتری «'.($customer->full_name ?: Fmt::toFa($customer->phone)).'» مسدود شد و دیگر نمی‌تواند وارد شود.');
        }
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $byStatus = Customer::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'all'     => (int) $byStatus->sum(),
            'active'  => (int) ($byStatus['active'] ?? 0),
            'blocked' => (int) ($byStatus['blocked'] ?? 0),
        ];
    }

    public function getCustomersProperty(): Collection
    {
        $term = Fmt::toLatin($this->search);

        $query = Customer::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('phone', 'like', "%{$term}%")
                ->orWhere('full_name', 'like', "%{$this->search}%")))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->withCount('orders')
            ->latest('id');

        $total = (clone $query)->count();
        $rows  = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.admin.users.customers-index', [
            'customers' => $this->customers,
            'stats'     => $this->stats,
            'hasMore'   => $this->hasMore,
        ])->title('مدیریت مشتریان')
          ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — کاربران']);
    }
}
