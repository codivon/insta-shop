<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopCategory;
use App\Support\Fmt;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * «ایجاد / ویرایش کد تخفیف» — two-tab form (اطلاعات / محدودیت‌ها) built
 * from the seller community's sample: title, auto-generated code, Jalali
 * window, percent/amount value with ceiling, minimum order rules, allowed
 * customers, quantity caps and the include/exclude product scope.
 */
#[Layout('layouts.seller-slot', ['hideTabbar' => true])]
class DiscountForm extends Component
{
    use NotifiesOnValidation;

    public ?int $discountId = null;

    /* ══════════ tab: اطلاعات ══════════ */

    public string $title = '';

    public string $code = '';

    /** Jalali-friendly ISO dates picked with x-jdate-picker (Y-m-d). */
    public string $start_date = '';

    public string $end_date = '';

    public string $type = Discount::TYPE_AMOUNT;

    public string $price = '';

    public string $percent = '';

    public string $discount_ceiling = '';

    public string $description = '';

    /* ══════════ tab: محدودیت‌ها ══════════ */

    public string $usersSearch = '';

    /** Selected customer ids (empty = everyone). */
    public array $customerIds = [];

    public string $least_price = '';

    public string $least_products_count = '';

    public string $quantity = '';

    public string $quantity_per_user = '';

    public string $include_type = Discount::INCLUDE_ALL;

    /** Selected category/product ids for include_type = category|product. */
    public array $includeIds = [];

    public string $exclude_type = Discount::EXCLUDE_NONE;

    public array $excludeIds = [];

    public bool $only_first_purchase = false;

    public bool $not_discount_products = false;

    public bool $published = true;

    public function mount(?Discount $discount = null): void
    {
        $shop = $this->shop();

        if ($discount !== null && $discount->exists) {
            abort_unless($discount->shop_id === $shop->id, 404, 'کد تخفیف یافت نشد.');

            $this->discountId           = $discount->id;
            $this->title                = (string) $discount->title;
            $this->code                 = (string) $discount->code;
            $this->start_date           = $discount->starts_at?->toDateString() ?? '';
            $this->end_date             = $discount->ends_at?->toDateString() ?? '';
            $this->type                 = $discount->type;
            $this->price                = $discount->price > 0 ? (string) $discount->price : '';
            $this->percent              = $discount->percent > 0 ? (string) $discount->percent : '';
            $this->discount_ceiling     = $discount->discount_ceiling > 0 ? (string) $discount->discount_ceiling : '';
            $this->description          = (string) ($discount->description ?? '');
            $this->customerIds          = array_map('intval', (array) ($discount->customer_ids ?? []));
            $this->least_price          = $discount->least_price > 0 ? (string) $discount->least_price : '';
            $this->least_products_count = $discount->least_products_count > 0 ? (string) $discount->least_products_count : '';
            $this->quantity             = $discount->quantity > 0 ? (string) $discount->quantity : '';
            $this->quantity_per_user    = $discount->quantity_per_user > 0 ? (string) $discount->quantity_per_user : '';
            $this->include_type         = $discount->include_type;
            $this->includeIds           = array_map('intval', (array) ($discount->include_ids ?? []));
            $this->exclude_type         = $discount->exclude_type;
            $this->excludeIds           = array_map('intval', (array) ($discount->exclude_ids ?? []));
            $this->only_first_purchase  = $discount->only_first_purchase;
            $this->not_discount_products = $discount->not_discount_products;
            $this->published            = $discount->published;
        }
    }

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    /* ══════════ input normalizers ══════════ */

    public function updatedPrice(string $value): void
    {
        $this->price = Fmt::toLatin($value);
    }

    public function updatedDiscountCeiling(string $value): void
    {
        $this->discount_ceiling = Fmt::toLatin($value);
    }

    public function updatedLeastPrice(string $value): void
    {
        $this->least_price = Fmt::toLatin($value);
    }

    public function updatedLeastProductsCount(string $value): void
    {
        $this->least_products_count = Fmt::toLatin($value);
    }

    public function updatedQuantity(string $value): void
    {
        $this->quantity = Fmt::toLatin($value);
    }

    public function updatedQuantityPerUser(string $value): void
    {
        $this->quantity_per_user = Fmt::toLatin($value);
    }

    /* ══════════ actions ══════════ */

    /** «دکمهٔ کلید» کنار کد تخفیف — ساخت خودکار کد خوانا و یکتا. */
    public function generateCode(): void
    {
        $base = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $this->title)) ?: 'SHOP';
        $code = substr($base, 0, 8).strtoupper(\Illuminate\Support\Str::random(4));

        while (Discount::query()->where('code', $code)->whereKeyNot($this->discountId ?? 0)->exists()) {
            $code = substr($base, 0, 8).strtoupper(\Illuminate\Support\Str::random(4));
        }

        $this->code = $code;
    }

    public function toggleCustomer(int $id): void
    {
        if (in_array($id, $this->customerIds, true)) {
            $this->customerIds = array_values(array_diff($this->customerIds, [$id]));
        } else {
            $this->customerIds[] = $id;
        }
    }

    /* ══════════ persistence ══════════ */

    public function save(): void
    {
        try {
            $this->validate($this->rules(), $this->messages());
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $firstMsg = collect($exception->errors())->flatten()->filter()->first();

            $this->dispatch('panel-toast', message: $firstMsg ?: 'لطفاً خطاهای فرم تخفیف را برطرف کنید.');
            $this->dispatch('discount-form-errors', tab: str_starts_with(array_key_first($exception->errors()), 'least_')
                || str_starts_with(array_key_first($exception->errors()), 'quantity')
                || str_starts_with(array_key_first($exception->errors()), 'include')
                || str_starts_with(array_key_first($exception->errors()), 'exclude')
                ? 'limits' : 'info');

            throw $exception;
        }

        if (in_array($this->include_type, [Discount::INCLUDE_CATEGORY, Discount::INCLUDE_PRODUCT], true) && $this->includeIds === []) {
            $this->addError($this->include_type === Discount::INCLUDE_PRODUCT ? 'includeIds' : 'includeIds', 'برای این حالت حداقل یک مورد انتخاب کنید.');
            $this->dispatch('panel-toast', message: 'محصولات یا دسته‌های مشمول تخفیف را انتخاب کنید.');
            $this->dispatch('discount-form-errors', tab: 'limits');

            return;
        }

        $discount = $this->discountId
            ? Discount::query()->where('shop_id', $this->shop()->id)->find($this->discountId)
            : new Discount(['shop_id' => $this->shop()->id]);

        if (! $discount) {
            abort(404);
        }

        $discount->fill([
            'title'                => trim($this->title),
            'code'                 => strtoupper(trim($this->code)),
            'type'                 => $this->type,
            'price'                => $this->type === Discount::TYPE_AMOUNT ? max(0, (int) $this->price) : 0,
            'percent'              => $this->type === Discount::TYPE_PERCENT ? min(100, max(0, (int) $this->percent)) : 0,
            'discount_ceiling'     => $this->type === Discount::TYPE_PERCENT && $this->discount_ceiling !== '' ? max(0, (int) $this->discount_ceiling) : null,
            'starts_at'            => $this->start_date !== '' ? $this->start_date.' 00:00:00' : null,
            'ends_at'              => $this->end_date !== '' ? $this->end_date.' 23:59:59' : null,
            'description'          => trim($this->description) !== '' ? trim($this->description) : null,
            'customer_ids'         => $this->customerIds,
            'least_price'          => max(0, (int) $this->least_price),
            'least_products_count' => max(0, (int) $this->least_products_count),
            'quantity'             => max(0, (int) $this->quantity),
            'quantity_per_user'    => max(0, (int) $this->quantity_per_user),
            'include_type'         => $this->include_type,
            'include_ids'          => in_array($this->include_type, [Discount::INCLUDE_CATEGORY, Discount::INCLUDE_PRODUCT], true) ? $this->includeIds : null,
            'exclude_type'         => $this->exclude_type,
            'exclude_ids'          => $this->exclude_type !== Discount::EXCLUDE_NONE ? $this->excludeIds : null,
            'only_first_purchase'  => $this->only_first_purchase,
            'not_discount_products' => $this->not_discount_products,
            'published'            => $this->published,
        ]);
        $discount->save();

        session()->flash('discount-flash', $this->discountId ? 'کد تخفیف به‌روزرسانی شد. ✅' : 'کد تخفیف جدید ساخته شد. ✅');

        $this->redirect(route('seller.discounts'), navigate: true);
    }

    /* ══════════ rules ══════════ */

    protected function rules(): array
    {
        $shopId = $this->shop()->id;

        return [
            'title'    => ['required', 'string', 'min:2', 'max:120'],
            'code'     => ['required', 'string', 'min:3', 'max:60', 'regex:/^[A-Za-z0-9_-]+$/',
                \Illuminate\Validation\Rule::unique('discounts', 'code')->ignore($this->discountId)],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'type'     => ['required', 'in:'.Discount::TYPE_AMOUNT.','.Discount::TYPE_PERCENT],
            'price'    => ['exclude_unless:type,'.Discount::TYPE_AMOUNT, 'required', 'integer', 'min:1000', 'max:999999999999'],
            'percent'  => ['exclude_unless:type,'.Discount::TYPE_PERCENT, 'required', 'integer', 'min:1', 'max:100'],
            'discount_ceiling' => ['nullable', 'exclude_unless:type,'.Discount::TYPE_PERCENT, 'integer', 'min:0', 'max:999999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'least_price'          => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'least_products_count' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'quantity'             => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'quantity_per_user'    => ['nullable', 'integer', 'min:0', 'max:1000'],
            'include_type' => ['required', 'in:'.Discount::INCLUDE_ALL.','.Discount::INCLUDE_CATEGORY.','.Discount::INCLUDE_PRODUCT],
            'includeIds'   => ['nullable', 'array'],
            'exclude_type' => ['required', 'in:'.Discount::EXCLUDE_NONE.','.Discount::INCLUDE_CATEGORY.','.Discount::INCLUDE_PRODUCT],
            'excludeIds'   => ['nullable', 'array'],
            'only_first_purchase'  => ['boolean'],
            'not_discount_products' => ['boolean'],
            'published' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'عنوان تخفیف را وارد کنید.',
            'title.min'      => 'عنوان حداقل ۲ کاراکتر است.',
            'code.required'  => 'کد تخفیف را وارد کنید یا با دکمهٔ کلید بسازید.',
            'code.unique'    => 'این کد تخفیف قبلاً استفاده شده است؛ کد دیگری بسازید.',
            'code.regex'     => 'کد تخفیف فقط می‌تواند حروف انگلیسی، عدد، خط تیره و زیرخط باشد.',
            'code.min'       => 'کد تخفیف حداقل ۳ کاراکتر است.',
            'end_date.after_or_equal' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',
            'price.required' => 'مقدار تخفیف (تومان) را وارد کنید.',
            'price.min'      => 'مقدار تخفیف حداقل ۱٬۰۰۰ تومان است.',
            'percent.required' => 'درصد تخفیف را وارد کنید.',
        ];
    }

    /* ══════════ computed data ══════════ */

    public function getCategoriesProperty(): Collection
    {
        return ShopCategory::query()->where('shop_id', $this->shop()->id)->orderBy('sort')->orderBy('id')->get(['id', 'name']);
    }

    public function getProductsProperty(): Collection
    {
        return Product::query()
            ->where('shop_id', $this->shop()->id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** Customer search results for the «انتخاب مشتریان» picker. */
    public function getSearchResultsProperty(): Collection
    {
        $term = trim($this->usersSearch);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        return Customer::query()
            ->where(function ($q) use ($term) {
                $q->where('full_name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            })
            ->orderBy('full_name')
            ->limit(8)
            ->get(['id', 'full_name', 'phone']);
    }

    public function getSelectedCustomersProperty(): Collection
    {
        if ($this->customerIds === []) {
            return collect();
        }

        return Customer::query()->whereIn('id', $this->customerIds)->get(['id', 'full_name', 'phone']);
    }

    public function render()
    {
        return view('livewire.seller.discount-form', [
            'categories'        => $this->categories,
            'products'          => $this->products,
            'searchResults'     => $this->searchResults,
            'selectedCustomers' => $this->selectedCustomers,
        ])->title($this->discountId ? 'ویرایش کد تخفیف' : 'ایجاد تخفیف جدید');
    }
}
