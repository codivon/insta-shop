<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;

use App\Models\Seller;
use App\Models\Shop;
use App\Support\Fmt;
use App\Support\Phone;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — sellers management (approve / suspend / reactivate) PLUS the
 * batch 75/76/78 additions: full edit modal (with the personal commission
 * override of batch 79), delete with confirmation and a «نمایش» deep
 * link into the seller detail page (admin.users.sellers.show).
 */
#[Layout('layouts.admin-slot')]
class SellersIndex extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.users.sellers';
    public string $search = '';

    #[Url]
    public string $status = 'all'; // all|pending|approved|suspended

    public int $perPage = 12;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    /* ══════════ edit modal (76 + 79) ══════════ */

    public bool $showEditModal = false;

    public ?int $editId = null;

    public string $editFullName = '';

    public string $editInstagram = '';

    public string $editPhone = '';

    public string $editStatus = 'pending';

    /** '' = follow the global default (پیش‌فرض سراسری). */
    public string $editCommissionType = '';

    public string $editCommissionValue = '';

    /* ══════════ delete confirm (75) ══════════ */

    public ?int $confirmDeleteId = null;

    public string $deleteConfirmText = '';

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

    /* ══════════ edit modal ══════════ */

    public function openEditModal(int $sellerId): void
    {
        $seller = Seller::query()->find($sellerId);

        if (! $seller) {
            return;
        }

        $this->editId          = $seller->id;
        $this->editFullName    = (string) ($seller->full_name ?? '');
        $this->editInstagram   = (string) ($seller->instagram_username ?? '');
        $this->editPhone       = Phone::toLocal($seller->phone);
        $this->editStatus      = (string) $seller->status;
        $this->editCommissionType = $seller->hasCommissionOverride() ? (string) $seller->commission_type : '';
        $this->editCommissionValue  = $seller->hasCommissionOverride() ? (string) $seller->commission_value : '';

        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editId = null;
    }

    public function updatedEditCommissionType(): void
    {
        $this->editCommissionValue = '';
    }

    public function updatedEditPhone($value): void
    {
        $this->editPhone = Fmt::toLatin((string) $value);
    }

    public function saveSellerEdit(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->editRules(), $this->editMessages()));

        $seller = Seller::query()->find((int) $this->editId);

        if (! $seller) {
            $this->closeEditModal();

            return;
        }

        $seller->fill([
            'full_name'         => trim($this->editFullName) !== '' ? trim($this->editFullName) : null,
            'instagram_username' => trim($this->editInstagram) !== '' ? trim($this->editInstagram) : null,
            'phone'             => Phone::normalize($this->editPhone),
            'status'            => $this->editStatus,
        ]);

        // commission override: empty type = follow the global default
        if ($this->editCommissionType === '' || trim($this->editCommissionValue) === '') {
            $seller->commission_type  = null;
            $seller->commission_value = null;
        } else {
            $seller->commission_type  = $this->editCommissionType;
            $seller->commission_value = (int) $this->editCommissionValue;
        }

        $seller->save();

        // keep the shop's instagram mirror in sync with the handle
        if ($seller->shop && $seller->instagram_username) {
            Shop::query()->whereKey($seller->shop->id)->update(['instagram_username' => $seller->instagram_username]);
        }

        $this->closeEditModal();

        session()->flash('admin-users-flash', 'اطلاعات فروشنده «'.($seller->full_name ?: '@'.$seller->instagram_username).'» ذخیره شد. ✅');
    }

    private function editRules(): array
    {
        return [
            'editFullName'  => ['nullable', 'string', 'min:3', 'max:80'],
            'editInstagram' => ['nullable', 'string', 'min:2', 'max:60', 'regex:/^[A-Za-z0-9._]+$/',
                \Illuminate\Validation\Rule::unique('sellers', 'instagram_username')->ignore($this->editId)],
            'editPhone'     => ['required', 'string', 'min:10', 'max:14',
                \Illuminate\Validation\Rule::unique('sellers', 'phone')->ignore($this->editId)],
            'editStatus'    => ['required', 'in:pending,approved,suspended'],
            'editCommissionType'  => ['required', 'in:,percent,fixed'],
            'editCommissionValue' => [
                'nullable',
                'integer',
                'min:0',
                \Illuminate\Validation\Rule::excludeIf(fn () => $this->editCommissionType === ''),
                $this->editCommissionType === 'percent' ? 'max:100' : 'max:999999999999',
            ],
        ];
    }

    private function editMessages(): array
    {
        return [
            'editFullName.min'   => 'نام باید حداقل ۳ حرف باشد.',
            'editInstagram.regex' => 'شناسه اینستاگرام فقط حروف، عدد، نقطه و زیرخط می‌تواند باشد.',
            'editInstagram.unique' => 'این شناسه اینستاگرام برای فروشنده دیگری ثبت شده است.',
            'editPhone.required' => 'شماره موبایل را وارد کنید.',
            'editPhone.min'      => 'شماره موبایل معتبر نیست.',
            'editPhone.unique'   => 'این شماره برای فروشنده دیگری ثبت شده است.',
            'editCommissionValue.integer' => 'مقدار کمیسیون باید عدد باشد.',
            'editCommissionValue.min'     => 'کمیسیون نمی‌تواند منفی باشد.',
            'editCommissionValue.max'     => 'درصد کمیسیون حداکثر ۱۰۰ است.',
        ];
    }

    /* ══════════ delete (75) ══════════ */

    public function askDelete(int $sellerId): void
    {
        $this->confirmDeleteId   = $sellerId;
        $this->deleteConfirmText = '';
    }

    public function deleteSeller(): void
    {
        $seller = Seller::query()->with('shop')->find((int) $this->confirmDeleteId);

        $this->confirmDeleteId   = null;
        $this->deleteConfirmText = '';

        if (! $seller) {
            return;
        }

        $name = $seller->full_name ?: '@'.$seller->instagram_username;
        $phone = (string) $seller->phone;

        if ($this->deleteConfirmText !== '' && $this->deleteConfirmText !== $phone) {
            return;
        }

        // shop cascades to products/categories/carriers/filters via FKs
        $seller->shop?->delete();
        $seller->delete();

        session()->flash('admin-users-flash', 'فروشنده «'.$name.'» و تمام داده‌های فروشگاهش حذف شد.');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $byStatus = Seller::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'all' => (int) $byStatus->sum(),
            'pending' => (int) ($byStatus['pending'] ?? 0),
            'approved' => (int) ($byStatus['approved'] ?? 0),
            'suspended' => (int) ($byStatus['suspended'] ?? 0),
        ];
    }

    public function getEditSellerProperty(): ?Seller
    {
        return $this->editId ? Seller::query()->with('shop:id,seller_id,name')->find($this->editId) : null;
    }

    public function getDeleteSellerProperty(): ?Seller
    {
        return $this->confirmDeleteId ? Seller::query()->with('shop:id,seller_id,name')->find($this->confirmDeleteId) : null;
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
        $rows = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.admin.users.sellers-index', [
            'sellers' => $this->sellers,
            'stats' => $this->stats,
            'hasMore' => $this->hasMore,
        ])->title('مدیریت فروشندگان')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — کاربران']);
    }
}
