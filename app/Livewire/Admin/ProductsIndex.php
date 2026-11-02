<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Product;
use App\Models\Shop;
use App\Support\Fmt;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — products management (batch 75: FULL CRUD + batch 78 shop
 * attribution). Every product carries its owning shop as a chip; the
 * create/edit modal REQUIRES a shop so admin-created goods are always
 * attributed to the right فروشگاه.
 */
#[Layout('layouts.admin-slot')]
class ProductsIndex extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.products';
    public string $search = '';

    #[Url]
    public string $status = 'all'; // all|published|draft

    #[Url]
    public int $shopId = 0;

    public int $perPage = 12;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    /* ══════════ create/edit modal (75 + 78) ══════════ */

    public bool $showModal = false;

    /** Shop picker before opening the full product form (create flow). */
    public bool $shopPickerOpen = false;

    public ?int $editId = null;

    public int $formShopId = 0;

    public int $formCategoryId = 0;

    public string $formName = '';

    public string $formSlug = '';

    public string $formType = Product::TYPE_PHYSICAL;

    public string $formPrice = '';

    public string $formCompareAtPrice = '';

    public string $formStock = '0';

    public string $formWeight = '';

    public string $formUnit = '';

    public string $formStatus = Product::STATUS_DRAFT;

    public string $formDescription = '';

    public function mount(): void
    {
        if (! in_array($this->status, ['all', 'published', 'draft'], true)) {
            $this->status = 'all';
        }
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'status', 'shopId'], true)) {
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

    /**
     * «افزودن محصول» now opens the SHOP PICKER — the full-featured form
     * (images, variants, specs, files, special sale) lives on its own page
     * and needs to know which shop it is creating for.
     */
    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->shopPickerOpen = true;
    }

    public function closeShopPicker(): void
    {
        $this->shopPickerOpen = false;
    }

    public function createForShop(int $shopId): void
    {
        if (! \App\Models\Shop::query()->whereKey($shopId)->exists()) {
            return;
        }

        $this->redirect(route('admin.products.create', ['shop' => $shopId]), navigate: true);
    }

    /** Edit now goes to the FULL-featured product form page. */
    public function openEditModal(int $productId): void
    {
        $product = Product::query()->find($productId);

        if (! $product) {
            return;
        }

        $this->redirect(route('admin.products.edit', $product), navigate: true);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editId = null;
    }

    private function resetForm(): void
    {
        $this->editId             = null;
        $this->formShopId         = 0;
        $this->formCategoryId     = 0;
        $this->formName           = '';
        $this->formSlug           = '';
        $this->formType           = Product::TYPE_PHYSICAL;
        $this->formPrice          = '';
        $this->formCompareAtPrice = '';
        $this->formStock          = '0';
        $this->formWeight         = '';
        $this->formUnit           = '';
        $this->formStatus         = Product::STATUS_DRAFT;
        $this->formDescription    = '';
    }

    public function updatedFormShopId(): void
    {
        $this->formCategoryId = 0;
    }

    public function updatedFormPrice($value): void
    {
        $this->formPrice = Fmt::toLatin((string) $value);
    }

    public function updatedFormCompareAtPrice($value): void
    {
        $this->formCompareAtPrice = Fmt::toLatin((string) $value);
    }

    public function updatedFormStock($value): void
    {
        $this->formStock = Fmt::toLatin((string) $value);
    }

    public function updatedFormWeight($value): void
    {
        $this->formWeight = Fmt::toLatin((string) $value);
    }

    /* ══════════ persistence ══════════ */

    public function saveProduct(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        $product = $this->editId
            ? Product::query()->find($this->editId)
            : new Product();

        if (! $product) {
            return;
        }

        $wasEditing = $this->editId !== null;

        $product->fill([
            'shop_id'           => $this->formShopId,
            'shop_category_id'  => $this->formCategoryId > 0 ? $this->formCategoryId : null,
            'name'              => trim($this->formName),
            'slug'              => trim($this->formSlug) !== '' ? trim($this->formSlug) : null,
            'type'              => $this->formType,
            'price'             => (int) $this->formPrice,
            'compare_at_price'  => $this->formCompareAtPrice !== '' ? (int) $this->formCompareAtPrice : 0,
            'stock'             => (int) $this->formStock,
            'weight_grams'      => $this->formWeight !== '' ? (int) $this->formWeight : 0,
            'unit'              => trim($this->formUnit) !== '' ? trim($this->formUnit) : null,
            'status'            => $this->formStatus,
            'description'       => trim($this->formDescription) !== '' ? trim($this->formDescription) : null,
        ]);

        $product->save();

        // Persian slug auto-generation when left empty (same as the seller form)
        if ($product->slug === null || trim((string) $product->slug) === '') {
            $product->refreshSlug();
        }

        $product->syncAggregates();

        $this->closeModal();

        session()->flash('admin-catalog-flash', ($wasEditing ? 'محصول «' : 'محصول جدید «').$product->name.'» ذخیره شد. ✅');
    }

    public function togglePublish(int $productId): void
    {
        $product = Product::query()->find($productId);

        if (! $product) {
            return;
        }

        $product->update(['status' => $product->status === 'published' ? 'draft' : 'published']);

        // seller notification: «تغییر وضعیت محصول»
        \App\Support\SellerNotifier::notify(
            $product->shop?->seller_id,
            'product.status',
            $product->status === 'published' ? 'محصول شما منتشر شد ✅' : 'محصول شما از انتشار خارج شد',
            'محصول «'.$product->name.'» اکنون '.($product->status === 'published' ? 'در فروشگاه قابل مشاهده است.' : 'پیش‌نویس است و دیده نمی‌شود.'),
            route('seller.products.edit', $product),
            'package',
            $product,
        );

        session()->flash('admin-catalog-flash', 'وضعیت «'.$product->name.'» تغییر کرد.');
    }

    public function deleteProduct(int $productId): void
    {
        $product = Product::query()->with('images')->find($productId);

        if (! $product) {
            return;
        }

        // Remove image files from disk before deleting rows.
        foreach ($product->images as $image) {
            $image->deleteFile();
        }

        $name = $product->name;
        $product->delete();

        session()->flash('admin-catalog-flash', 'محصول «'.$name.'» حذف شد.');
    }

    /* ══════════ rules ══════════ */

    protected function rules(): array
    {
        return [
            'formShopId'        => ['required', 'integer', 'min:1', 'exists:shops,id'],
            'formCategoryId'    => ['nullable', 'integer', Rule::exists('shop_categories', 'id')->where('shop_id', $this->formShopId)],
            'formName'          => ['required', 'string', 'min:2', 'max:120'],
            'formSlug'          => ['nullable', 'string', 'max:190'],
            'formType'          => ['required', 'in:'.Product::TYPE_PHYSICAL.','.Product::TYPE_DOWNLOADABLE],
            'formPrice'         => ['required', 'integer', 'min:0', 'max:999999999999'],
            'formCompareAtPrice'=> ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'formStock'         => ['required', 'integer', 'min:0', 'max:1000000'],
            'formWeight'        => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'formUnit'          => ['nullable', 'string', 'max:30'],
            'formStatus'        => ['required', 'in:'.Product::STATUS_PUBLISHED.','.Product::STATUS_DRAFT],
            'formDescription'   => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'formShopId.required' => 'فروشگاهِ مالک محصول را انتخاب کنید.',
            'formShopId.min'      => 'فروشگاهِ مالک محصول را انتخاب کنید.',
            'formName.required'   => 'نام محصول را وارد کنید.',
            'formName.min'        => 'نام محصول حداقل ۲ کاراکتر است.',
            'formPrice.required'  => 'قیمت محصول را وارد کنید.',
            'formPrice.integer'   => 'قیمت باید عدد باشد.',
            'formCategoryId.exists' => 'دستهٔ انتخابی به این فروشگاه تعلق ندارد.',
        ];
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $byStatus = Product::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'all' => (int) $byStatus->sum(),
            'published' => (int) ($byStatus['published'] ?? 0),
            'draft' => (int) ($byStatus['draft'] ?? 0),
        ];
    }

    /** Shops for the filter dropdown AND the modal's owner selector. */
    public function getShopOptionsProperty(): Collection
    {
        return Shop::query()->orderBy('name')->get(['id', 'name']);
    }

    /** Categories of the selected shop (inside the modal). */
    public function getFormCategoriesProperty(): Collection
    {
        return $this->formShopId > 0
            ? \App\Models\ShopCategory::query()->where('shop_id', $this->formShopId)->orderBy('sort')->orderBy('id')->get(['id', 'name'])
            : collect();
    }

    public function getProductsProperty(): Collection
    {
        $term = Fmt::toLatin($this->search);

        $query = Product::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhereHas('shop', fn ($s) => $s->where('name', 'like', "%{$this->search}%"))))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->shopId > 0, fn ($q) => $q->where('shop_id', $this->shopId))
            ->with(['shop:id,name', 'mainImage'])
            ->latest('id');

        $total = (clone $query)->count();
        $rows = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.admin.products-index', [
            'products' => $this->products,
            'stats' => $this->stats,
            'shopOptions' => $this->shopOptions,
            'formCategories' => $this->formCategories,
            'hasMore' => $this->hasMore,
        ])->title('مدیریت محصولات')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — محصولات']);
    }
}
