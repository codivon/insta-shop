<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Product;
use App\Models\ProductVariantValue;
use App\Models\Shop;
use App\Models\ShopAttribute;
use App\Models\ShopFilter;
use App\Models\ShopFilterItem;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * «فیلترینگ» — seller-defined storefront filters (batch 72 + attribute rework).
 *
 * A filter = یک عنوان + چند مشخصه. Two kinds of filterable attributes:
 *   • مشخصات محصولات — attribute titles INSIDE the specifications groups of
 *     THIS seller's own products (سایز، نسخه بلوتوث، نور پس‌زمینه…).
 *   • ویژگی‌های فروشگاه — the shop's own attribute fields (رنگ، گارانتی،
 *     سایز و…) with the values actually used on the products' variants.
 * On the storefront, every published filter becomes a block per attribute
 * with the distinct values the seller actually used.
 */
#[Layout('layouts.seller-slot')]
class FilteringManager extends Component
{
    use NotifiesOnValidation;

    /* ══════════ modal state ══════════ */

    public bool $showModal = false;

    public ?int $editId = null;

    /* ══════════ form ══════════ */

    public string $title = '';

    public bool $published = true;

    /** Selected spec attribute titles of the filter (سایز، نسخه بلوتوث…). */
    public array $specNames = [];

    /** Selected shop attribute ids of the filter (رنگ، سایز، گارانتی…). */
    public array $attrIds = [];

    /* ══════════ spec picker modal ══════════ */

    public bool $showSpecPicker = false;

    public string $specSearch = '';

    /** Delete confirmation id. */
    public ?int $confirmDeleteId = null;

    public function mount(): void
    {
        $this->shop(); // ensure the shop exists
    }

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    /* ══════════ filter modal lifecycle ══════════ */

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $filter = $this->findFilter($id);

        if (! $filter) {
            return;
        }

        $this->resetForm();
        $this->resetErrorBag();

        $this->editId    = $filter->id;
        $this->title     = (string) $filter->title;
        $this->published = (bool) $filter->published;
        $this->specNames = $filter->items->where('kind', ShopFilterItem::KIND_SPEC)->pluck('spec_name')->all();
        $this->attrIds   = $filter->items->where('kind', ShopFilterItem::KIND_ATTRIBUTE)->pluck('shop_attribute_id')->map(fn ($id) => (int) $id)->all();

        $this->showModal = true;
    }

    public function closeFilterModal(): void
    {
        $this->showModal = false;
        $this->editId = null;
    }

    private function resetForm(): void
    {
        $this->editId     = null;
        $this->title      = '';
        $this->published  = true;
        $this->specNames  = [];
        $this->attrIds    = [];
        $this->specSearch = '';
    }

    /* ══════════ spec picker ══════════ */

    public function openSpecPicker(): void
    {
        $this->specSearch = '';
        $this->showSpecPicker = true;
    }

    public function closeSpecPicker(): void
    {
        $this->showSpecPicker = false;
    }

    public function toggleSpec(string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            return;
        }

        if (in_array($name, $this->specNames, true)) {
            $this->specNames = array_values(array_diff($this->specNames, [$name]));

            return;
        }

        if ($this->pickedCount() >= 8) {
            $this->dispatch('filters-toast', message: 'حداکثر ۸ مشخصه برای هر فیلتر.');

            return;
        }

        $this->specNames[] = $name;
    }

    public function removeSpec(string $name): void
    {
        $this->specNames = array_values(array_diff($this->specNames, [$name]));
    }

    /** Toggle one shop attribute (ویژگی) of this filter. */
    public function toggleAttr(int $id): void
    {
        if (in_array($id, $this->attrIds, true)) {
            $this->attrIds = array_values(array_diff($this->attrIds, [$id]));

            return;
        }

        if (! $this->shop()->attributes()->whereKey($id)->exists()) {
            return; // never touch another shop's attributes
        }

        if ($this->pickedCount() >= 8) {
            $this->dispatch('filters-toast', message: 'حداکثر ۸ مشخصه برای هر فیلتر.');

            return;
        }

        $this->attrIds[] = $id;
    }

    public function removeAttr(int $id): void
    {
        $this->attrIds = array_values(array_diff($this->attrIds, [$id]));
    }

    /** specNames + attrIds — the «حداکثر ۸ مشخصه» budget. */
    private function pickedCount(): int
    {
        return count($this->specNames) + count($this->attrIds);
    }

    /* ══════════ persistence ══════════ */

    public function saveFilter(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        $filter = $this->editId
            ? $this->findFilter($this->editId)
            : new ShopFilter(['shop_id' => $this->shop()->id]);

        if (! $filter) {
            return;
        }

        $wasEditing = $this->editId !== null;

        $filter->fill([
            'title'     => trim($this->title),
            'published' => $this->published,
        ]);

        if (! $filter->exists) {
            $filter->sort = (int) ShopFilter::query()->where('shop_id', $this->shop()->id)->max('sort') + 1;
        }

        $filter->save();

        // replace the spec/attribute items
        $filter->items()->delete();

        foreach (array_values(array_unique($this->specNames)) as $name) {
            ShopFilterItem::query()->create([
                'shop_filter_id' => $filter->id,
                'spec_name'      => $name,
                'kind'           => ShopFilterItem::KIND_SPEC,
            ]);
        }

        // ویژگی‌های فروشگاه — spec_name carries the attribute name as a
        // denormalized display label so the storefront never needs a join.
        $attributes = $this->shop()->attributes()->whereIn('id', $this->attrIds)->get();

        foreach ($attributes as $attribute) {
            ShopFilterItem::query()->create([
                'shop_filter_id'    => $filter->id,
                'spec_name'         => $attribute->name,
                'kind'              => ShopFilterItem::KIND_ATTRIBUTE,
                'shop_attribute_id' => $attribute->id,
            ]);
        }

        $this->closeFilterModal();

        $this->dispatch('filters-toast', message: $wasEditing
            ? 'فیلتر به‌روزرسانی شد. ✅'
            : 'فیلتر جدید ساخته شد. ✅');
    }

    public function togglePublished(int $id): void
    {
        $filter = $this->findFilter($id);

        if ($filter) {
            $filter->update(['published' => ! $filter->published]);
        }
    }

    public function askDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function deleteFilter(): void
    {
        $filter = $this->findFilter((int) $this->confirmDeleteId);

        $this->confirmDeleteId = null;

        if (! $filter) {
            return;
        }

        $filter->items()->delete();
        $filter->delete();

        $this->dispatch('filters-toast', message: 'فیلتر حذف شد.');
    }

    private function findFilter(int $id): ?ShopFilter
    {
        return ShopFilter::query()->where('shop_id', $this->shop()->id)->find($id);
    }

    /* ══════════ rules ══════════ */

    protected function rules(): array
    {
        return [
            'title'     => ['required', 'string', 'min:2', 'max:80'],
            'published' => ['boolean'],
            'specNames' => ['required_without:attrIds', 'nullable', 'array', 'max:8'],
            'specNames.*' => ['string', 'max:80'],
            'attrIds'   => ['required_without:specNames', 'nullable', 'array', 'max:8'],
            'attrIds.*' => ['integer', Rule::exists('shop_attributes', 'id')->where('shop_id', $this->shop()->id)],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'عنوان فیلتر را وارد کنید.',
            'title.min'      => 'عنوان فیلتر حداقل ۲ کاراکتر است.',
            'specNames.required_without' => 'حداقل یک مشخصه به فیلتر اضافه کنید.',
            'attrIds.required_without'   => 'حداقل یک مشخصه به فیلتر اضافه کنید.',
            'attrIds.*.exists' => 'یکی از ویژگی‌های انتخابی معتبر نیست.',
        ];
    }

    /* ══════════ computed data ══════════ */

    public function getFiltersProperty(): Collection
    {
        return ShopFilter::query()
            ->where('shop_id', $this->shop()->id)
            ->with('items')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }

    /**
     * ALL spec attribute titles (مشخصه‌ها) found in THIS seller's own
     * products — the searchable picker source (سایز / نسخه بلوتوث /
     * نور پس‌زمینه…). These are the item titles INSIDE the specification
     * groups, not the group headers. Fully isolated per seller.
     */
    public function getAvailableSpecsProperty(): Collection
    {
        $search = trim($this->specSearch);

        $names = Product::query()
            ->where('shop_id', $this->shop()->id)
            ->whereNotNull('specifications')
            ->get(['specifications'])
            ->flatMap(fn ($product) => collect($product->specifications ?? []))
            ->flatMap(fn ($group) => collect($group['items'] ?? []))
            ->pluck('title')
            ->map(fn ($title) => trim((string) $title))
            ->filter(fn ($title) => $title !== '')
            ->unique()
            ->sort()
            ->values();

        if ($search !== '') {
            $names = $names->filter(fn ($name) => mb_strpos($name, $search) !== false)->values();
        }

        return $names;
    }

    /**
     * The shop's own attribute fields (ویژگی‌ها — رنگ، سایز، گارانتی و…),
     * searchable with the same box. These read their values from the
     * products' VARIANTS (ProductVariantValue), so the storefront filter
     * shows exactly the values the seller actually used.
     */
    public function getAvailableAttrsProperty(): Collection
    {
        $search = trim($this->specSearch);

        $attrs = $this->shop()->attributes()->orderBy('sort')->orderBy('id')->get(['id', 'name', 'type']);

        if ($search !== '') {
            $attrs = $attrs->filter(fn ($attr) => mb_strpos(trim((string) $attr->name), $search) !== false)->values();
        }

        return $attrs;
    }

    /** id → name of the shop's attributes — renders the selected chips. */
    public function getAttrNameByIdProperty(): array
    {
        return $this->shop()->attributes()->pluck('name', 'id')->all();
    }

    /**
     * Distinct value counts per attribute as USED on this shop's published
     * products' variants (مثلاً «رنگ · ۵ مقدار در محصولات شما»).
     */
    public function getAttrUsedValueCountsProperty(): array
    {
        return $this->usedVariantValues()
            ->toBase() // Eloquent::unique() expects models — group plain strings on the base collection
            ->mapToGroups(fn (ProductVariantValue $row) => [
                (int) $row->shop_attribute_id => trim((string) ($row->value_text ?: ($row->value->value ?? ''))),
            ])
            ->map(fn ($groups) => $groups->filter()->unique()->count())
            ->all();
    }

    /**
     * Every variant-value row of the shop's published products
     * (variant → product → shop), with the preset value preloaded.
     */
    private function usedVariantValues(): Collection
    {
        return ProductVariantValue::query()
            ->whereHas('variant', fn ($q) => $q
                ->where('status', 'active')
                ->whereHas('product', fn ($p) => $p
                    ->where('shop_id', $this->shop()->id)
                    ->where('status', 'published')))
            ->with('value:id,shop_attribute_id,value,color_hex')
            ->get(['shop_attribute_id', 'shop_attribute_value_id', 'value_text']);
    }

    /**
     * Distinct value counts per attribute title — shown as a hint inside
     * the picker so the seller sees how many values the shop actually uses
     * (مثلاً «نسخه بلوتوث · ۳ مقدار»).
     */
    public function getSpecValueCountsProperty(): array
    {
        return Product::query()
            ->where('shop_id', $this->shop()->id)
            ->whereNotNull('specifications')
            ->get(['specifications'])
            ->flatMap(fn ($product) => collect($product->specifications ?? []))
            ->flatMap(fn ($group) => collect($group['items'] ?? []))
            ->groupBy(fn ($item) => trim((string) ($item['title'] ?? '')))
            ->mapWithKeys(function ($items, $title) {
                if ($title === '') {
                    return [];
                }

                $count = $items->pluck('value')
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->unique()
                    ->count();

                return [$title => $count];
            })
            ->all();
    }

    /** Count of the shop's published products (hint text). */
    public function getProductsCountProperty(): int
    {
        return Product::query()
            ->where('shop_id', $this->shop()->id)
            ->where('status', 'published')
            ->count();
    }

    public function render()
    {
        return view('livewire.seller.filtering-manager', [
            'filters'          => $this->filters,
            'availableSpecs'   => $this->availableSpecs,
            'specValueCounts'  => $this->specValueCounts,
            'availableAttrs'   => $this->availableAttrs,
            'attrNameById'     => $this->attrNameById,
            'attrUsedCounts'   => $this->attrUsedValueCounts,
            'productsCount'    => $this->productsCount,
        ])->title('فیلترینگ');
    }
}
