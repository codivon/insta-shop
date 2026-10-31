<?php

namespace App\Livewire\Seller;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Shop;
use App\Support\Fmt;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.seller-slot')]
class ProductForm extends Component
{
    use WithFileUploads;

    public ?int $productId = null;

    public int $shopId = 0;

    public string $name = '';

    public string $description = '';

    public string $price = '';

    public string $compareAtPrice = '';

    public string $stock = '0';

    public int $categoryId = 0;

    public string $status = 'draft';

    /** Newly picked photos (TemporaryUploadedFile[]) */
    public array $photos = [];

    public function mount(?Product $product = null): void
    {
        $shop = $this->shop();

        $this->shopId = $shop->id;

        if ($product !== null) {
            abort_unless($product->shop_id === $shop->id, 404, 'محصول یافت نشد.');

            $this->productId       = $product->id;
            $this->name            = $product->name;
            $this->description     = (string) $product->description;
            $this->price           = (string) $product->price;
            $this->compareAtPrice  = $product->compare_at_price ? (string) $product->compare_at_price : '';
            $this->stock           = (string) $product->stock;
            $this->categoryId      = (int) $product->shop_category_id;
            $this->status          = $product->status;
        }
    }

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    public function updatedPrice(string $value): void
    {
        $this->price = Fmt::toLatin($value);
    }

    public function updatedCompareAtPrice(string $value): void
    {
        $this->compareAtPrice = Fmt::toLatin($value);
    }

    public function updatedStock(string $value): void
    {
        $this->stock = Fmt::toLatin($value);
    }

    public function removeNewPhoto(int $index): void
    {
        unset($this->photos[$index]);
    }

    public function removeImage(int $imageId): void
    {
        $image = ProductImage::query()->find($imageId);

        if (! $image || $image->product->shop_id !== $this->shop()->id) {
            return;
        }

        $image->deleteFile();
        $image->delete();
    }

    public function makeMain(int $imageId): void
    {
        $image = ProductImage::query()->find($imageId);

        if (! $image || $image->product->shop_id !== $this->shop()->id) {
            return;
        }

        $minSort = (int) $image->product->images()->min('sort');

        $image->update(['sort' => $minSort - 1]);
    }

    public function save(): void
    {
        $existingCount = $this->productId
            ? ProductImage::query()->where('product_id', $this->productId)->count()
            : 0;

        $this->validate([
            'name'            => ['required', 'string', 'min:2', 'max:150'],
            'description'     => ['nullable', 'string', 'max:5000'],
            'price'           => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'compareAtPrice'  => ['nullable', 'numeric', 'min:1', 'max:999999999999', 'gte:price'],
            'stock'           => ['required', 'integer', 'min:0', 'max:1000000'],
            'categoryId'      => ['nullable', 'integer', Rule::in($this->shop()->categories()->pluck('id')->all())],
            'status'          => ['required', 'in:draft,published'],
            'photos'          => ['nullable', 'array', 'max:'.max(0, 6 - $existingCount)],
            'photos.*'        => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required'       => 'نام محصول را وارد کنید.',
            'name.min'            => 'نام محصول حداقل ۲ کاراکتر است.',
            'price.required'      => 'قیمت محصول را وارد کنید.',
            'price.min'           => 'قیمت نمی‌تواند منفی باشد.',
            'compareAtPrice.gte'  => 'قیمت قبل از تخفیف باید بیشتر یا مساوی قیمت فعلی باشد.',
            'photos.max'          => 'حداکثر ۶ تصویر برای هر محصول مجاز است (تصاویر فعلی + جدید).',
            'photos.*.image'      => 'فایل انتخاب‌شده تصویر نیست.',
            'photos.*.max'        => 'حجم هر تصویر حداکثر ۲ مگابایت است.',
        ]);

        $product = $this->productId
            ? Product::query()->findOrFail($this->productId)
            : new Product;

        abort_if($product->exists && $product->shop_id !== $this->shop()->id, 404);

        $product->fill([
            'shop_id'           => $this->shop()->id,
            'shop_category_id'  => $this->categoryId ?: null,
            'name'              => trim($this->name),
            'description'       => trim($this->description) ?: null,
            'price'             => (int) $this->price,
            'compare_at_price'  => $this->compareAtPrice !== '' ? (int) $this->compareAtPrice : null,
            'stock'             => (int) $this->stock,
            'status'            => $this->status,
        ]);
        $product->save();

        // Store the newly picked gallery images.
        $baseSort = $product->images()->count() === 0 ? 0 : ((int) $product->images()->max('sort') + 1);

        foreach ($this->photos as $i => $photo) {
            $path = $photo->store("products/{$product->id}", 'public');

            ProductImage::query()->create([
                'product_id' => $product->id,
                'path'       => $path,
                'sort'       => $baseSort + $i,
            ]);
        }

        session()->flash('shop-flash', $this->productId ? 'محصول به‌روزرسانی شد.' : 'محصول جدید اضافه شد.');

        $this->redirect(route('seller.shop'), navigate: true);
    }

    public function render()
    {
        return view('livewire.seller.product-form', [
            'existingImages' => $this->existingImages,
        ])->title($this->productId ? 'ویرایش محصول' : 'افزودن محصول');
    }

    // ── Computed ─────────────────────────────────────────────────────

    /** Already-saved gallery images (edit mode only). */
    public function getExistingImagesProperty(): \Illuminate\Support\Collection
    {
        if (! $this->productId) {
            return collect();
        }

        return ProductImage::query()
            ->where('product_id', $this->productId)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }
}
