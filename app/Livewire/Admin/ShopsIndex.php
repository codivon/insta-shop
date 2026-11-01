<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;

use App\Models\Shop;
use App\Models\WalletTransaction;
use App\Support\Fmt;
use App\Support\Wallet\WalletService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — shops management (list / search / activate-pause).
 */
#[Layout('layouts.admin-slot')]
class ShopsIndex extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.shops';
    public string $search = '';

    #[Url]
    public string $status = 'all'; // all|active|paused

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
        $this->perPage += 12;
    }

    public function toggleStatus(int $shopId): void
    {
        $shop = Shop::query()->findOrFail($shopId);

        $shop->forceFill(['status' => $shop->status === 'active' ? 'paused' : 'active'])->save();

        session()->flash('admin-catalog-flash',
            ($shop->status === 'active' ? 'فروشگاه «' : 'فروشگاه «').$shop->name.'» '.($shop->status === 'active' ? 'فعال شد.' : 'موقتاً متوقف شد و از دید مشتریان خارج است.'));
    }

    // ── Wallet withdrawal requests (seller wallet approvals) ────────

    public function approveWithdrawal(int $txId): void
    {
        $tx = WalletTransaction::query()->find($txId);

        if (! $tx) {
            return;
        }

        WalletService::approveWithdrawal($tx);

        session()->flash('admin-catalog-flash', 'درخواست برداشت تأیید شد؛ مبلغ به شبا فروشنده واریز شده در نظر گرفته می‌شود.');
    }

    public function rejectWithdrawal(int $txId): void
    {
        $tx = WalletTransaction::query()->find($txId);

        if (! $tx) {
            return;
        }

        WalletService::rejectWithdrawal($tx);

        session()->flash('admin-catalog-flash', 'درخواست برداشت رد شد؛ مبلغ به کیف پول فروشنده بازگشت.');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $byStatus = Shop::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'all' => (int) $byStatus->sum(),
            'active' => (int) ($byStatus['active'] ?? 0),
            'paused' => (int) ($byStatus['paused'] ?? 0),
        ];
    }

    public function getShopsProperty(): Collection
    {
        $term = Fmt::toLatin($this->search);

        $query = Shop::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('slug', 'like', "%{$this->search}%")
                ->orWhere('instagram_username', 'like', '%'.ltrim($this->search, '@').'%')
                ->orWhereHas('seller', fn ($s) => $s->where('phone', 'like', "%{$term}%"))))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->with(['seller:id,full_name,instagram_username,status,phone'])
            ->withCount(['products', 'publishedProducts'])
            ->latest('id');

        $total = (clone $query)->count();
        $rows = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.admin.shops-index', [
            'shops' => $this->shops,
            'stats' => $this->stats,
            'hasMore' => $this->hasMore,
            'withdrawals' => $this->pendingWithdrawals,
        ])->title('مدیریت فروشگاه‌ها')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — فروشگاه‌ها']);
    }

    /** Pending withdrawal requests with their shop/wallet context. */
    public function getPendingWithdrawalsProperty(): Collection
    {
        return WalletTransaction::query()
            ->where('type', WalletTransaction::TYPE_WITHDRAWAL)
            ->where('status', WalletTransaction::STATUS_PENDING)
            ->with(['wallet.seller:id,full_name,instagram_username'])
            ->latest('id')
            ->limit(10)
            ->get();
    }
}
