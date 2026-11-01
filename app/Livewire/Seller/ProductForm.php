<?php

namespace App\Livewire\Seller;

use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Support\Fmt;
use Illuminate\Support\Facades\DB;
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

    /* ── مشخصات محصول ── */
    public string $name = '';

    public string $description = '';

    public int $categoryId = 0;

    public string $productType = Product::TYPE_PHYSICAL;

    public string $weight = '';

    public string $unit = 'تعداد';

    public string $status = 'draft';

    /** Newly picked gallery photos (TemporaryUploadedFile[]) */
    public array $photos = [];

    /* ── تنوع‌ها (physical) ── */
    public array $variants = [];

    /* ── فایل‌های دانلودی (downloadable) ── */
    public array $files = [];

    /** Existing file rows the seller removed */
    public array $removedFileIds = [];

    public function mount(?Product $product = null): void
    {
        $shop = $this->shop();

        $this->shopId = $shop->id;

        if ($product !== null) {
            abort_unless($product->shop_id === $shop->id, 404, 'محصول یافت نشد.');

            $this->productId   = $product->id;
            $this->name        = $product->name;
            $this->description = (string) $product->description;
            $this->categoryId  = (int) $product->shop_category_id;
            $this->productType = $product->type ?: Product::TYPE_PHYSICAL;
            $this->weight      = $product->weight_grams ? (string) $product->weight_grams : '';
            $this->unit        = (string) ($product->unit ?: 'تعداد');
            $this->status      = $product->status;

            $this->loadVariants($product);
        }

        if ($this->productType === Product::TYPE_PHYSICAL && $this->variants === []) {
            $this->addVariant();
        }

        if ($this->productType === Product::TYPE_DOWNLOADABLE && $this->files === []) {
            $this->addFile();
        }
    }

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    /* ══════════════════ input normalizers ══════════════════ */

    public function updatedVariants(mixed $value, ?string $key = null): void
    {
        if ($key === null) {
            return;
        }

        if (preg_match('/\.(price|stock|discount|max_purchase|min_purchase)$/', $key)) {
            data_set($this->variants, $key, Fmt::toLatin((string) $value));
        }
    }

    public function updatedFiles(mixed $value, ?string $key = null): void
    {
        if ($key === null) {
            return;
        }

        if (preg_match('/\.(price|discount)$/', $key)) {
            data_set($this->files, $key, Fmt::toLatin((string) $value));
        }
    }

    public function updatedWeight(string $value): void
    {
        $this->weight = Fmt::toLatin($value);
    }

    public function updatedProductType(string $value): void
    {
        $this->productType = $value === Product::TYPE_DOWNLOADABLE
            ? Product::TYPE_DOWNLOADABLE
            : Product::TYPE_PHYSICAL;

        if ($this->productType === Product::TYPE_PHYSICAL && $this->variants === []) {
            $this->addVariant();
        }

        if ($this->productType === Product::TYPE_DOWNLOADABLE && $this->files === []) {
            $this->addFile();
        }
    }

    /* ══════════════════ variant row ops ══════════════════ */

    public function addVariant(): void
    {
        $values = [];

        foreach ($this->attributesWithValues as $attribute) {
            $values[$attribute->id] = ['value_id' => 0, 'text' => ''];
        }

        $this->variants[] = [
            'id'                  => null,
            'price'               => '',
            'stock'               => '',
            'discount'            => '',
            'discount_expires_at' => '',
            'max_purchase'        => '',
            'min_purchase'        => '',
            'status'              => 'active',
            'values'              => $values,
        ];
    }

    public function removeVariant(int $index): void
    {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);

        if ($this->variants === []) {
            $this->addVariant();
        }
    }

    /* ══════════════════ file row ops ══════════════════ */

    public function addFile(): void
    {
        $this->files[] = [
            'id'       => null,
            'title'    => '',
            'price'    => '',
            'discount' => '',
            'status'   => 'active',
            'file'     => null,
        ];
    }

    public function removeNewFile(int $index): void
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files);

        if ($this->files === []) {
            $this->addFile();
        }
    }

    public function removeExistingFile(int $fileId): void
    {
        $this->removedFileIds[] = $fileId;
        $this->removedFileIds   = array_values(array_unique($this->removedFileIds));
    }

    public function undoRemoveExistingFile(int $fileId): void
    {
        $this->removedFileIds = array_values(array_diff($this->removedFileIds, [$fileId]));
    }

    /* ══════════════════ gallery ops ══════════════════ */

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

    /* ══════════════════ save ══════════════════ */

    public function save(): void
    {
        $this->validateBase();
        $this->productType === Product::TYPE_DOWNLOADABLE
            ? $this->validateFiles()
            : $this->validateVariants();

        $product = $this->productId
            ? Product::query()->findOrFail($this->productId)
            : new Product;

        abort_if($product->exists && $product->shop_id !== $this->shop()->id, 404);

        DB::transaction(function () use ($product) {
            $product->fill([
                'shop_id'          => $this->shop()->id,
                'shop_category_id' => $this->categoryId ?: null,
                'name'             => trim($this->name),
                'description'      => trim($this->description) ?: null,
                'type'             => $this->productType,
                'weight_grams'     => $this->productType === Product::TYPE_PHYSICAL && $this->weight !== '' ? (int) $this->weight : null,
                'unit'             => $this->productType === Product::TYPE_PHYSICAL ? (trim($this->unit) ?: 'تعداد') : null,
                'status'           => $this->status,
            ]);
            $product->save();

            $this->storePhotos($product);

            if ($this->productType === Product::TYPE_PHYSICAL) {
                $this->persistVariants($product);
                $product->files()->get()->each(fn (ProductFile $f) => $f->delete());
            } else {
                $this->persistFiles($product);
                $product->variants()->get()->each(fn (ProductVariant $v) => $v->delete());
            }

            $product->syncAggregates();
        });

        session()->flash('shop-flash', $this->productId ? 'محصول به‌روزرسانی شد.' : 'محصول جدید اضافه شد.');

        $this->redirect(route('seller.shop'), navigate: true);
    }

    protected function validateBase(): void
    {
        $existingCount = $this->productId
            ? ProductImage::query()->where('product_id', $this->productId)->count()
            : 0;

        $this->validate([
            'name'        => ['required', 'string', 'min:2', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'categoryId'  => ['nullable', 'integer', Rule::in(array_merge([0], $this->shop()->categories()->pluck('id')->all()))],
            'productType' => ['required', 'in:'.Product::TYPE_PHYSICAL.','.Product::TYPE_DOWNLOADABLE],
            'weight'      => [$this->productType === Product::TYPE_PHYSICAL ? 'nullable' : 'nullable', 'numeric', 'min:0', 'max:10000000'],
            'unit'        => ['nullable', 'string', 'max:20'],
            'status'      => ['required', 'in:draft,published'],
            'photos'      => ['nullable', 'array', 'max:'.max(0, 6 - $existingCount)],
            'photos.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required'  => 'نام محصول را وارد کنید.',
            'name.min'       => 'نام محصول حداقل ۲ کاراکتر است.',
            'photos.max'     => 'حداکثر ۶ تصویر برای هر محصول مجاز است (تصاویر فعلی + جدید).',
            'photos.*.image' => 'فایل انتخاب‌شده تصویر نیست.',
            'photos.*.max'   => 'حجم هر تصویر حداکثر ۲ مگابایت است.',
        ]);
    }

    protected function validateVariants(): void
    {
        $validValueIds = $this->shop()->attributes()->with('values')->get()
            ->flatMap(fn ($a) => $a->values->pluck('id'))
            ->push(0) // 0 = «بدون انتخاب»
            ->all();

        $this->validate([
            'variants'                        => ['required', 'array', 'min:1'],
            'variants.*.price'                => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'variants.*.stock'                => ['required', 'integer', 'min:0', 'max:1000000'],
            'variants.*.discount'             => ['nullable', 'integer', 'min:0', 'max:100'],
            'variants.*.discount_expires_at'  => ['nullable', 'date'],
            'variants.*.max_purchase'         => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'variants.*.min_purchase'         => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'variants.*.status'               => ['required', 'in:active,hidden'],
            'variants.*.values.*.value_id'    => ['nullable', 'integer', Rule::in($validValueIds)],
            'variants.*.values.*.text'        => ['nullable', 'string', 'max:120'],
        ], [
            'variants.required'                       => 'حداقل یک تنوع باید وجود داشته باشد.',
            'variants.min'                            => 'حداقل یک تنوع باید وجود داشته باشد.',
            'variants.*.price.required'               => 'قیمت هر تنوع الزامی است.',
            'variants.*.stock.required'               => 'موجودی انبار هر تنوع الزامی است.',
            'variants.*.discount.max'                 => 'تخفیف باید بین ۰ تا ۱۰۰ درصد باشد.',
            'variants.*.values.*.value_id.in'         => 'مقدار انتخاب‌شده معتبر نیست؛ ویژگی‌ها را به‌روز کنید.',
        ]);

        foreach ($this->variants as $i => $variant) {
            $min = (int) ($variant['min_purchase'] ?? 0);
            $max = (int) ($variant['max_purchase'] ?? 0);

            if ($min > 0 && $max > 0 && $min > $max) {
                $this->addError("variants.{$i}.min_purchase", 'حداقل خرید نمی‌تواند بیشتر از حداکثر خرید باشد.');

                return;
            }
        }
    }

    protected function validateFiles(): void
    {
        $this->validate([
            'files'               => ['required', 'array', 'min:1'],
            'files.*.title'       => ['required', 'string', 'min:1', 'max:150'],
            'files.*.price'       => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'files.*.discount'    => ['nullable', 'integer', 'min:0', 'max:100'],
            'files.*.status'      => ['required', 'in:active,hidden'],
            'files.*.file'        => ['nullable', 'file', 'max:102400'],
        ], [
            'files.required'            => 'حداقل یک فایل دانلودی اضافه کنید.',
            'files.min'                 => 'حداقل یک فایل دانلودی اضافه کنید.',
            'files.*.title.required'    => 'عنوان فایل را وارد کنید.',
            'files.*.price.required'    => 'قیمت فایل را وارد کنید.',
            'files.*.discount.max'      => 'تخفیف باید بین ۰ تا ۱۰۰ درصد باشد.',
            'files.*.file.max'          => 'حجم فایل حداکثر ۱۰۰ مگابایت است.',
        ]);

        $existingCount = $this->productId
            ? $this->existingFiles->whereNotIn('id', $this->removedFileIds)->count()
            : 0;

        if ($this->status === 'published' && ($existingCount + count($this->files)) === 0) {
            $this->addError('files', 'برای انتشار محصول دانلودی، حداقل یک فایل لازم است.');
        }

        foreach ($this->files as $i => $row) {
            if (($row['file'] ?? null) === null && $row['title'] !== '') {
                // new row without a chosen file — only blocks when nothing else is saved
                if ($existingCount === 0 && count($this->files) === 1) {
                    $this->addError("files.{$i}.file", 'فایل را انتخاب کنید.');
                }
            }
        }
    }

    protected function storePhotos(Product $product): void
    {
        $baseSort = $product->images()->count() === 0 ? 0 : ((int) $product->images()->max('sort') + 1);

        foreach ($this->photos as $i => $photo) {
            $path = $photo->store("products/{$product->id}", 'public');

            ProductImage::query()->create([
                'product_id' => $product->id,
                'path'       => $path,
                'sort'       => $baseSort + $i,
            ]);
        }

        $this->photos = [];
    }

    protected function persistVariants(Product $product): void
    {
        $keepIds          = [];
        $validAttributes  = $this->shop()->attributes()->with('values')->get()->keyBy('id');

        foreach (array_values($this->variants) as $i => $row) {
            $variant = $row['id']
                ? ProductVariant::query()->where('product_id', $product->id)->find($row['id'])
                : new ProductVariant;

            if (! $variant) {
                $variant = new ProductVariant;
            }

            $variant->fill([
                'product_id'           => $product->id,
                'price'                => (int) $row['price'],
                'stock'                => (int) $row['stock'],
                'discount_percent'     => (int) ($row['discount'] ?: 0),
                'discount_expires_at'  => $row['discount_expires_at'] !== '' ? $row['discount_expires_at'] : null,
                'max_purchase'         => $row['max_purchase'] !== '' ? (int) $row['max_purchase'] : null,
                'min_purchase'         => $row['min_purchase'] !== '' ? (int) $row['min_purchase'] : null,
                'status'               => $row['status'],
                'sort'                 => $i,
            ]);
            $variant->save();

            $keepIds[] = $variant->id;

            /* ── attribute picks ── */
            foreach ($validAttributes as $attributeId => $attribute) {
                $picked = $row['values'][$attributeId] ?? ['value_id' => 0, 'text' => ''];

                $valueId = (int) ($picked['value_id'] ?? 0);
                $text    = trim((string) ($picked['text'] ?? ''));

                if ($attribute->type === 'text') {
                    $payload = ['value_text' => $text !== '' ? $text : null, 'shop_attribute_value_id' => null];
                } else {
                    $payload = ['shop_attribute_value_id' => $valueId > 0 ? $valueId : null, 'value_text' => null];
                }

                $hasSomething = ($payload['shop_attribute_value_id'] ?? null) !== null
                    || ($payload['value_text'] ?? null) !== null;

                $existing = $variant->values()
                    ->where('shop_attribute_id', $attributeId)
                    ->first();

                if ($hasSomething) {
                    if ($existing) {
                        $existing->update($payload);
                    } else {
                        $variant->values()->create($payload + ['shop_attribute_id' => $attributeId]);
                    }
                } elseif ($existing) {
                    $existing->delete();
                }
            }
        }

        $product->variants()->whereNotIn('id', $keepIds)->get()->each(fn (ProductVariant $v) => $v->delete());
    }

    protected function persistFiles(Product $product): void
    {
        /* removed existing rows */
        if ($this->removedFileIds !== []) {
            $product->files()->whereIn('id', $this->removedFileIds)->get()
                ->each(function (ProductFile $f) {
                    $f->deleteFile();
                    $f->delete();
                });
        }

        $baseSort = $product->files()->count() === 0 ? 0 : ((int) $product->files()->max('sort') + 1);

        foreach (array_values($this->files) as $i => $row) {
            $file = $row['id']
                ? ProductFile::query()->where('product_id', $product->id)->find($row['id'])
                : null;

            if ($row['id'] && ! $file) {
                $file = null; // stale id — treat as new
            }

            if ($file) {
                $file->fill([
                    'title'            => trim($row['title']),
                    'price'            => (int) $row['price'],
                    'discount_percent' => (int) ($row['discount'] ?: 0),
                    'status'           => $row['status'],
                ]);
                $file->save();

                if ($row['file'] !== null) { // replaced file
                    $file->deleteFile();
                    $file->update(['file_path' => $this->storeProductFile($product, $row['file']), 'file_size' => $row['file']->getSize()]);
                }

                continue;
            }

            if ($row['file'] === null) {
                continue; // nothing chosen yet — skip creating an empty row
            }

            ProductFile::query()->create([
                'product_id'       => $product->id,
                'title'            => trim($row['title']),
                'price'            => (int) $row['price'],
                'discount_percent' => (int) ($row['discount'] ?: 0),
                'status'           => $row['status'],
                'file_path'        => $this->storeProductFile($product, $row['file']),
                'file_size'        => $row['file']->getSize(),
                'sort'             => $baseSort + $i,
            ]);
        }
    }

    protected function storeProductFile(Product $product, $uploaded): string
    {
        $ext  = strtolower($uploaded->getClientOriginalExtension() ?: 'bin');
        $name = $product->id.'-'.bin2hex(random_bytes(8)).'.'.$ext;

        return $uploaded->storeAs('product-files/'.$product->id, $name, 'local');
    }

    /* ══════════════════ computed ══════════════════ */

    public function render()
    {
        return view('livewire.seller.product-form', [
            'existingImages' => $this->existingImages,
            'existingFiles'  => $this->existingFiles,
            'shopAttributes' => $this->attributesWithValues,
        ])->title($this->productId ? 'ویرایش محصول' : 'افزودن محصول');
    }

    /** Shop attribute fields with their values (for the variant pickers). */
    public function getAttributesWithValuesProperty(): \Illuminate\Support\Collection
    {
        return $this->shop()->attributes()->with('values')->get();
    }

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

    /** Already-saved downloadable files (edit mode only). */
    public function getExistingFilesProperty(): \Illuminate\Support\Collection
    {
        if (! $this->productId) {
            return collect();
        }

        return ProductFile::query()
            ->where('product_id', $this->productId)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->reject(fn (ProductFile $f) => in_array($f->id, $this->removedFileIds));
    }

    /* ══════════════════ helpers ══════════════════ */

    protected function loadVariants(Product $product): void
    {
        $this->variants = $product->variants->map(function (ProductVariant $variant) {
            $values = [];

            foreach ($this->attributesWithValues as $attribute) {
                $picked = $variant->valueForAttribute($attribute->id);

                $values[$attribute->id] = $attribute->type === 'text'
                    ? ['value_id' => 0, 'text' => (string) ($picked?->value_text ?? '')]
                    : ['value_id' => (int) ($picked?->shop_attribute_value_id ?? 0), 'text' => ''];
            }

            return [
                'id'                  => $variant->id,
                'price'               => (string) $variant->price,
                'stock'               => (string) $variant->stock,
                'discount'            => (string) $variant->discount_percent,
                'discount_expires_at' => $variant->discount_expires_at?->format('Y-m-d') ?? '',
                'max_purchase'        => $variant->max_purchase ? (string) $variant->max_purchase : '',
                'min_purchase'        => $variant->min_purchase ? (string) $variant->min_purchase : '',
                'status'              => $variant->status,
                'values'              => $values,
            ];
        })->all();
    }
}
