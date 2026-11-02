<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\ShopFilter;
use App\Models\ShopFilterItem;
use App\Models\Shop;
use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * «فیلترینگ» — seller-defined storefront filters (batch 72).
 *
 * A filter = یک عنوان + چند مشخصه (سایز، نسخه بلوتوث، نور پس‌زمینه…).
 * The spec attributes are collected ONLY from THIS seller's own products
 * (the attribute titles inside the specifications groups), so filters
 * never collide with other sellers' specs. On the storefront, every
 * published filter becomes a block per attribute with the distinct
 * values the seller actually used on their products.
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
        $this->specNames = $filter->items->pluck('spec_name')->all();

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

        if (count($this->specNames) >= 8) {
            $this->dispatch('filters-toast', message: 'حداکثر ۸ مشخصه برای هر فیلتر.');

            return;
        }

        $this->specNames[] = $name;
    }

    public function removeSpec(string $name): void
    {
        $this->specNames = array_values(array_diff($this->specNames, [$name]));
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

        // replace the spec items
        $filter->items()->delete();

        foreach (array_values(array_unique($this->specNames)) as $name) {
            ShopFilterItem::query()->create([
                'shop_filter_id' => $filter->id,
                'spec_name'      => $name,
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
            'specNames' => ['required', 'array', 'min:1', 'max:8'],
            'specNames.*' => ['string', 'max:80'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'عنوان فیلتر را وارد کنید.',
            'title.min'      => 'عنوان فیلتر حداقل ۲ کاراکتر است.',
            'specNames.required' => 'حداقل یک مشخصه به فیلتر اضافه کنید.',
            'specNames.min'      => 'حداقل یک مشخصه به فیلتر اضافه کنید.',
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
            'productsCount'    => $this->productsCount,
        ])->title('فیلترینگ');
    }
}
