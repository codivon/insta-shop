<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Seller\ProductForm as SellerProductForm;
use App\Models\Product;
use App\Models\Shop;
use Livewire\Attributes\Layout;

/**
 * Admin — full-featured product form (ADD/EDIT with EVERYTHING the seller
 * form has: gallery, variants, specs, downloadable files, special sale,
 * pricing, plus the shop-category assignment). It REUSES the seller
 * ProductForm logic end-to-end; only the shop resolution (any shop of the
 * platform, picked via ?shop=), the layout and the post-save landing page
 * differ.
 *
 * Create → /admin/products/create?shop={id}   Edit → /admin/products/{product}/edit
 */
#[Layout('layouts.admin-slot')]
class ProductForm extends SellerProductForm
{
    use GuardsPermissions;

    protected static string $permission = 'admin.products';

    public function mount(?Product $product = null): void
    {
        // Any platform shop: picked via query string, falls back to the
        // first shop so the page never boots into a crash.
        $this->shopId = (int) (request()->query('shop', 0));

        if ($product !== null) {
            $this->shopId = (int) $product->shop_id;
        }

        if ($this->shopId <= 0 || ! Shop::query()->whereKey($this->shopId)->exists()) {
            $this->shopId = (int) (Shop::query()->orderBy('id')->value('id') ?? 0);
        }

        abort_if($this->shopId <= 0, 404, 'هیچ فروشگاهی برای مدیریت محصول وجود ندارد.');

        parent::mount($product);
    }

    /** The shop this product belongs to (admin = the ?shop=/product's shop). */
    protected function shop(): Shop
    {
        return once(fn () => Shop::query()->findOrFail($this->shopId));
    }

    protected function afterSaveUrl(): string
    {
        return route('admin.products');
    }

    public function render()
    {
        return view('livewire.admin.product-form', [
            'shop' => $this->shop(),
            'existingImages' => $this->existingImages,
            'existingFiles' => $this->existingFiles,
            'shopAttributes' => $this->attributesWithValues,
            'categoryTreeJson' => $this->categoryTreeJson(),
            'selectedCategoryLabel' => $this->selectedCategoryLabel(),
            'specTemplates' => $this->specTemplates,
        ])->title($this->productId ? 'ویرایش محصول' : 'افزودن محصول')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — محصولات — '.($this->productId ? 'ویرایش محصول' : 'افزودن محصول')]);
    }
}
