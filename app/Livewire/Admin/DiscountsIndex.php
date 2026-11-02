<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Discount;
use App\Models\Shop;
use App\Support\Fmt;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — discount codes management (batch 75: FULL CRUD + batch 78
 * shop attribution). Every code belongs to a فروشگاه — the create/edit
 * modal REQUIRES choosing the owner shop, and the table shows the chip.
 */
#[Layout('layouts.admin-slot')]
class DiscountsIndex extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.discounts';

    public string $search = '';

    #[Url]
    public int $shopId = 0;

    public int $perPage = 12;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    /* ══════════ modal ══════════ */

    public bool $showModal = false;

    public ?int $editId = null;

    public int $formShopId = 0;

    public string $formTitle = '';

    public string $formCode = '';

    public string $formType = 'amount'; // amount | percent

    public string $formPrice = '';

    public string $formPercent = '';

    public string $formCeiling = '';

    public string $formStartsAt = '';

    public string $formEndsAt = '';

    public string $formLeastPrice = '';

    public string $formQuantity = '0';

    public bool $formPublished = true;

    public string $formDescription = '';

    public function mount(): void
    {
        //
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'shopId'], true)) {
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

    /* ══════════ modal lifecycle ══════════ */

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEditModal(int $discountId): void
    {
        $discount = Discount::query()->find($discountId);

        if (! $discount) {
            return;
        }

        $this->resetForm();
        $this->resetErrorBag();

        $this->editId       = $discount->id;
        $this->formShopId   = (int) $discount->shop_id;
        $this->formTitle    = (string) $discount->title;
        $this->formCode     = (string) $discount->code;
        $this->formType     = (string) $discount->type;
        $this->formPrice    = (string) $discount->price;
        $this->formPercent  = (string) $discount->percent;
        $this->formCeiling  = $discount->discount_ceiling ? (string) $discount->discount_ceiling : '';
        $this->formStartsAt = $discount->starts_at?->format('Y-m-d') ?? '';
        $this->formEndsAt   = $discount->ends_at?->format('Y-m-d') ?? '';
        $this->formLeastPrice = (string) $discount->least_price;
        $this->formQuantity   = (string) $discount->quantity;
        $this->formPublished  = (bool) $discount->published;
        $this->formDescription = (string) ($discount->description ?? '');

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editId = null;
    }

    private function resetForm(): void
    {
        $this->editId          = null;
        $this->formShopId      = 0;
        $this->formTitle       = '';
        $this->formCode        = '';
        $this->formType        = 'amount';
        $this->formPrice       = '';
        $this->formPercent     = '';
        $this->formCeiling     = '';
        $this->formStartsAt    = '';
        $this->formEndsAt      = '';
        $this->formLeastPrice  = '';
        $this->formQuantity    = '0';
        $this->formPublished   = true;
        $this->formDescription = '';
    }

    public function updatedFormType(): void
    {
        $this->formPrice   = '';
        $this->formPercent = '';
    }

    public function updatedFormPrice($value): void
    {
        $this->formPrice = Fmt::toLatin((string) $value);
    }

    public function updatedFormCeiling($value): void
    {
        $this->formCeiling = Fmt::toLatin((string) $value);
    }

    public function updatedFormLeastPrice($value): void
    {
        $this->formLeastPrice = Fmt::toLatin((string) $value);
    }

    public function updatedFormQuantity($value): void
    {
        $this->formQuantity = Fmt::toLatin((string) $value);
    }

    /* ══════════ persistence ══════════ */

    public function saveDiscount(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        $discount = $this->editId
            ? Discount::query()->find($this->editId)
            : new Discount();

        if (! $discount) {
            return;
        }

        $wasEditing = $this->editId !== null;

        $discount->fill([
            'shop_id'           => $this->formShopId,
            'title'             => trim($this->formTitle),
            'code'              => mb_strtoupper(trim($this->formCode)),
            'type'              => $this->formType,
            'price'             => $this->formType === 'amount' ? (int) $this->formPrice : 0,
            'percent'           => $this->formType === 'percent' ? (int) $this->formPercent : 0,
            'discount_ceiling'  => $this->formCeiling !== '' ? (int) $this->formCeiling : null,
            'starts_at'         => $this->formStartsAt !== '' ? $this->formStartsAt.' 00:00:00' : null,
            'ends_at'           => $this->formEndsAt !== '' ? $this->formEndsAt.' 23:59:59' : null,
            'least_price'       => $this->formLeastPrice !== '' ? (int) $this->formLeastPrice : 0,
            'quantity'          => (int) $this->formQuantity,
            'published'         => $this->formPublished,
            'description'       => trim($this->formDescription) !== '' ? trim($this->formDescription) : null,
        ]);

        $discount->save();

        $this->closeModal();

        session()->flash('admin-catalog-flash', ($wasEditing ? 'کد تخفیف «' : 'کد تخفیف جدید «').$discount->code.'» ذخیره شد. ✅');
    }

    public function togglePublished(int $discountId): void
    {
        $discount = Discount::query()->find($discountId);

        if ($discount) {
            $discount->update(['published' => ! $discount->published]);
        }
    }

    public function deleteDiscount(int $discountId): void
    {
        $discount = Discount::query()->find($discountId);

        if (! $discount) {
            return;
        }

        $code = $discount->code;
        $discount->delete();

        session()->flash('admin-catalog-flash', 'کد تخفیف «'.$code.'» حذف شد.');
    }

    /* ══════════ rules ══════════ */

    protected function rules(): array
    {
        return [
            'formShopId'     => ['required', 'integer', 'min:1', 'exists:shops,id'],
            'formTitle'      => ['required', 'string', 'min:2', 'max:120'],
            'formCode'       => ['required', 'string', 'min:3', 'max:60',
                Rule::unique('discounts', 'code')->ignore($this->editId)],
            'formType'       => ['required', 'in:amount,percent'],
            'formPrice'      => ['exclude_unless:formType,amount', 'required', 'integer', 'min:1000', 'max:999999999999'],
            'formPercent'    => ['exclude_unless:formType,percent', 'required', 'integer', 'min:1', 'max:100'],
            'formCeiling'    => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'formStartsAt'   => ['nullable', 'date'],
            'formEndsAt'     => ['nullable', 'date', 'after_or_equal:formStartsAt'],
            'formLeastPrice' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'formQuantity'   => ['required', 'integer', 'min:0', 'max:1000000'],
            'formPublished'  => ['boolean'],
            'formDescription'=> ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'formShopId.required' => 'فروشگاهِ مالک کد تخفیف را انتخاب کنید.',
            'formShopId.min'      => 'فروشگاهِ مالک کد تخفیف را انتخاب کنید.',
            'formTitle.required'  => 'عنوان کد تخفیف را وارد کنید.',
            'formCode.required'   => 'کد تخفیف را وارد کنید.',
            'formCode.min'        => 'کد تخفیف حداقل ۳ کاراکتر است.',
            'formCode.unique'     => 'این کد تخفیف قبلاً ثبت شده است.',
            'formPrice.required'  => 'مبلغ تخفیف را وارد کنید (حداقل ۱٬۰۰۰ تومان).',
            'formPrice.min'       => 'مبلغ تخفیف حداقل ۱٬۰۰۰ تومان است.',
            'formPercent.required'=> 'درصد تخفیف را وارد کنید.',
            'formPercent.max'     => 'درصد تخفیف حداکثر ۱۰۰ است.',
            'formEndsAt.after_or_equal' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',
        ];
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        return [
            'all'       => Discount::query()->count(),
            'published' => Discount::query()->where('published', true)->count(),
            'shops'     => Discount::query()->distinct('shop_id')->count('shop_id'),
            'used'      => (int) Discount::query()->sum('used_count'),
        ];
    }

    public function getShopOptionsProperty(): Collection
    {
        return Shop::query()->orderBy('name')->get(['id', 'name']);
    }

    public function getDiscountsProperty(): Collection
    {
        $term = Fmt::toLatin($this->search);

        $query = Discount::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('code', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$this->search}%")))
            ->when($this->shopId > 0, fn ($q) => $q->where('shop_id', $this->shopId))
            ->with('shop:id,name')
            ->latest('id');

        $total = (clone $query)->count();
        $rows  = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.admin.discounts-index', [
            'discounts'   => $this->discounts,
            'stats'       => $this->stats,
            'shopOptions' => $this->shopOptions,
            'hasMore'     => $this->hasMore,
        ])->title('کدهای تخفیف')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — تخفیف‌ها']);
    }
}
