<?php

namespace App\Livewire\Admin\Users;

use App\Models\Seller;
use App\Support\Fmt;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — sellers management (approve / suspend / reactivate).
 */
#[Layout('layouts.admin-slot')]
class SellersIndex extends Component
{
    public string $search = '';

    #[Url]
    public string $status = 'all'; // all|pending|approved|suspended

    public int $perPage = 12;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function mount(): void
    {
        // Deep links from the dashboard (e.g. ?status=pending)
        if (! in_array($this->status, ['all', 'pending', 'approved', 'suspended'], true)) {
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

    // ── Actions ──────────────────────────────────────────────────────

    public function approve(int $sellerId): void
    {
        $seller = Seller::query()->findOrFail($sellerId);

        $seller->forceFill(['status' => 'approved'])->save();
        session()->flash('admin-users-flash', 'فروشنده «'.($seller->full_name ?: '@'.$seller->instagram_username).'» تأیید شد؛ فروشگاه او اکنون برای مشتریان قابل مشاهده است.');
    }

    public function reject(int $sellerId): void
    {
        $seller = Seller::query()->findOrFail($sellerId);

        if ($seller->status !== 'pending') {
            return;
        }

        $seller->forceFill(['status' => 'suspended'])->save();
        session()->flash('admin-users-flash', 'درخواست فروشنده «'.($seller->full_name ?: '@'.$seller->instagram_username).'» رد شد.');
    }

    public function suspend(int $sellerId): void
    {
        $seller = Seller::query()->findOrFail($sellerId);

        $seller->forceFill(['status' => 'suspended'])->save();
        session()->flash('admin-users-flash', 'فروشنده «'.($seller->full_name ?: '@'.$seller->instagram_username).'» غیرفعال شد؛ فروشگاه او از دید مشتریان خارج شد.');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $byStatus = Seller::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'all'       => (int) $byStatus->sum(),
            'pending'   => (int) ($byStatus['pending'] ?? 0),
            'approved'  => (int) ($byStatus['approved'] ?? 0),
            'suspended' => (int) ($byStatus['suspended'] ?? 0),
        ];
    }

    public function getSellersProperty(): Collection
    {
        $term = Fmt::toLatin($this->search);

        $query = Seller::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('phone', 'like', "%{$term}%")
                ->orWhere('full_name', 'like', "%{$this->search}%")
                ->orWhere('instagram_username', 'like', '%'.ltrim($this->search, '@').'%')))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->with('shop:id,seller_id,name,status')
            ->withCount('products')
            ->latest('id');

        $total = (clone $query)->count();
        $rows  = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.admin.users.sellers-index', [
            'sellers' => $this->sellers,
            'stats'   => $this->stats,
            'hasMore' => $this->hasMore,
        ])->title('مدیریت فروشندگان')
          ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — کاربران']);
    }
}
