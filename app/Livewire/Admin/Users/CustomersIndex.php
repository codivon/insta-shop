<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;

use App\Models\Customer;
use App\Support\Fmt;
use App\Support\Phone;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — customers management (list / search / block-unblock) PLUS the
 * batch 75/77 additions: edit modal, delete with confirmation and a
 * «نمایش» deep link into the customer detail page.
 */
#[Layout('layouts.admin-slot')]
class CustomersIndex extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.users.customers';
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
        $this->perPage += 12;
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

    /* ══════════ edit modal (75) ══════════ */

    public bool $showEditModal = false;

    public ?int $editId = null;

    public string $editFullName = '';

    public string $editPhone = '';

    public string $editStatus = 'active';

    public ?int $confirmDeleteId = null;

    public string $deleteConfirmText = '';

    public function openEditModal(int $customerId): void
    {
        $customer = Customer::query()->find($customerId);

        if (! $customer) {
            return;
        }

        $this->editId       = $customer->id;
        $this->editFullName = (string) ($customer->full_name ?? '');
        $this->editPhone    = Phone::toLocal($customer->phone);
        $this->editStatus   = (string) $customer->status;

        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editId = null;
    }

    public function updatedEditPhone($value): void
    {
        $this->editPhone = Fmt::toLatin((string) $value);
    }

    public function saveCustomerEdit(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'editFullName' => ['nullable', 'string', 'min:3', 'max:80'],
            'editPhone'    => ['required', 'string', 'min:10', 'max:14',
                \Illuminate\Validation\Rule::unique('customers', 'phone')->ignore($this->editId)],
            'editStatus'   => ['required', 'in:active,blocked'],
        ], [
            'editFullName.min'   => 'نام باید حداقل ۳ حرف باشد.',
            'editPhone.required' => 'شماره موبایل را وارد کنید.',
            'editPhone.min'      => 'شماره موبایل معتبر نیست.',
            'editPhone.unique'   => 'این شماره برای مشتری دیگری ثبت شده است.',
        ]));

        $customer = Customer::query()->find((int) $this->editId);

        if (! $customer) {
            $this->closeEditModal();

            return;
        }

        $customer->fill([
            'full_name' => trim($this->editFullName) !== '' ? trim($this->editFullName) : null,
            'phone'     => Phone::normalize($this->editPhone),
            'status'    => $this->editStatus,
        ]);

        $customer->save();

        $this->closeEditModal();

        session()->flash('admin-users-flash', 'اطلاعات مشتری «'.($customer->full_name ?: Fmt::toFa($customer->phone)).'» ذخیره شد. ✅');
    }

    /* ══════════ delete (75) ══════════ */

    public function askDelete(int $customerId): void
    {
        $this->confirmDeleteId   = $customerId;
        $this->deleteConfirmText = '';
    }

    public function deleteCustomer(): void
    {
        $customer = Customer::query()->find((int) $this->confirmDeleteId);

        $this->confirmDeleteId   = null;
        $this->deleteConfirmText = '';

        if (! $customer) {
            return;
        }

        $name = $customer->full_name ?: Fmt::toFa($customer->phone);

        if ($this->deleteConfirmText !== (string) $customer->phone) {
            return;
        }

        // orders cascade with the customer (FK cascadeOnDelete)
        $customer->delete();

        session()->flash('admin-users-flash', 'مشتری «'.$name.'» و تمام سفارش‌هایش حذف شد.');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $byStatus = Customer::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'all' => (int) $byStatus->sum(),
            'active' => (int) ($byStatus['active'] ?? 0),
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
        $rows = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function getDeleteCustomerProperty(): ?Customer
    {
        return $this->confirmDeleteId ? Customer::query()->find($this->confirmDeleteId) : null;
    }

    public function render()
    {
        return view('livewire.admin.users.customers-index', [
            'customers' => $this->customers,
            'stats' => $this->stats,
            'hasMore' => $this->hasMore,
        ])->title('مدیریت مشتریان')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — کاربران']);
    }
}
