<?php

namespace App\Livewire\Seller;

use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Models\ShopCategory;
use App\Models\SpecificationTemplate;
use App\Support\Fmt;
use App\Livewire\Concerns\NotifiesOnValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.seller-slot', ['hideTabbar' => true])]
class ProductForm extends Component
{
    use WithFileUploads;
    use NotifiesOnValidation;

    public ?int $productId = null;

    public int $shopId = 0;

    /* ── مشخصات محصول ── */
    public string $name = '';

    /**
     * آدرس اختصاصی محصول (slug) — در صورت خالی بودن از عنوان ساخته می‌شود؛
     * فارسی هم پشتیبانی می‌شود و آدرس نهایی:
     *   دامنه/shop/{آیدی اینستا}/{اسلاگ}
     */
    public string $slug = '';

    public string $description = '';

    public int $categoryId = 0;

    public string $productType = Product::TYPE_PHYSICAL;

    public string $weight = '';

    public string $unit = 'تعداد';

    public string $status = Product::STATUS_DRAFT;

    /* ── فروش ویژه (special sale) ── */

    /** Part of the storefront's «فروش ویژه» block (with live countdown). */
    public bool $isSpecial = false;

    /** ISO (Y-m-d) end date picked with the Jalali datepicker; the sale runs to the END of this day. */
    public string $specialEndsAt = '';

    /** Newly picked gallery photos (TemporaryUploadedFile[]) */
    public array $photos = [];

    /** Key of the newly-picked photo chosen as the featured (شاخص) image. */
    public ?int $featuredPhotoKey = null;

    /* ── مشخصات فنی (گروه‌های مشخصه) ── */
    public array $specifications = [];

    /** نوع مشخصات — reusable template title, e.g. «گوشی موبایل» */
    public string $specType = '';

    /** Selected template row id (0 = new/none). */
    public int $specTemplateId = 0;

    /* ── تنوع‌ها (physical) ── */
    public array $variants = [];

    /** Index of the variant currently open in the edit modal (null = closed). */
    public ?int $variantModalIndex = null;

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

            $this->productId      = $product->id;
            $this->name           = $product->name;
            $this->slug           = (string) ($product->slug ?? '');
            $this->description    = (string) $product->description;
            $this->categoryId     = (int) $product->shop_category_id;
            $this->productType    = $product->type ?: Product::TYPE_PHYSICAL;
            $this->weight         = $product->weight_grams ? (string) $product->weight_grams : '';
            $this->unit           = (string) ($product->unit ?: 'تعداد');
            $this->status         = $product->status;
            $this->isSpecial      = $product->is_special;
            $this->specialEndsAt  = $product->special_ends_at?->toDateString() ?? '';
            $this->specifications = $product->specifications ?? [];
            $this->specType       = (string) ($product->spec_type ?? '');

            if ($this->specType !== '') {
                $template = SpecificationTemplate::query()
                    ->where('shop_id', $this->shop()->id)
                    ->where('title', $this->specType)
                    ->first();

                $this->specTemplateId = (int) ($template?->id ?? 0);
            }

            $this->loadVariants($product);
        }

        if ($this->productType === Product::TYPE_PHYSICAL && $this->variants === []) {
            $this->addVariant();
        }

        if ($this->productType === Product::TYPE_DOWNLOADABLE && $this->files === []) {
            // Editing a product that already has saved files → those render as
            // their own cards in the files tab; don't force an empty new row.
            if (! ($product !== null && $product->files()->exists())) {
                $this->addFile();
            }
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

    /* ══════════════════ specifications (گروه‌های مشخصه + نوع مشخصات) ══════════════════ */

    /**
     * Apply a saved spec type (نوع مشخصات): its groups load as fresh rows
     * (values empty) so the seller only fills values / deletes extras.
     */
    public function applySpecTemplate(int $templateId): void
    {
        $template = SpecificationTemplate::query()
            ->where('shop_id', $this->shop()->id)
            ->find($templateId);

        if (! $template) {
            return;
        }

        $this->resetErrorBag(['specifications']);

        $this->specType        = $template->title;
        $this->specTemplateId  = (int) $template->id;

        $this->loadTemplateIntoSpecifications($template);
    }

    /**
     * Picking/typing a spec type (نوع مشخصات):
     *  - matching saved template → its groups load as fresh rows (values empty)
     *  - unknown title → kept as a new type (template is created on save)
     */
    public function updatedSpecType(string $value): void
    {
        $title = trim($value);

        if ($title === '') {
            $this->specTemplateId = 0;

            return;
        }

        $template = SpecificationTemplate::query()
            ->where('shop_id', $this->shop()->id)
            ->where('title', $title)
            ->first();

        if ($template) {
            $this->specTemplateId = (int) $template->id;
            $this->loadTemplateIntoSpecifications($template);
        } else {
            $this->specTemplateId = 0;
        }
    }

    protected function loadTemplateIntoSpecifications(SpecificationTemplate $template): void
    {
        $groups = [];

        foreach (array_slice($template->groups ?? [], 0, 10) as $group) {
            $items = [];

            foreach (array_slice($group['items'] ?? [], 0, 25) as $item) {
                $items[] = ['title' => (string) ($item['title'] ?? ''), 'value' => ''];
            }

            if ($items === []) {
                $items[] = ['title' => '', 'value' => ''];
            }

            $groups[] = ['name' => (string) ($group['name'] ?? ''), 'items' => $items];
        }

        $this->specifications = $groups;
    }

    public function addSpecGroup(): void
    {
        $this->specifications[] = ['name' => '', 'items' => [['title' => '', 'value' => '']]];

        $this->dispatch('scroll-specs-bottom');
    }

    public function removeSpecGroup(int $gi): void
    {
        unset($this->specifications[$gi]);
        $this->specifications = array_values($this->specifications);
    }

    public function addSpecItem(int $gi): void
    {
        if (! isset($this->specifications[$gi])) {
            return;
        }

        $this->specifications[$gi]['items'][] = ['title' => '', 'value' => ''];
    }

    public function removeSpecItem(int $gi, int $ii): void
    {
        if (! isset($this->specifications[$gi]['items'][$ii])) {
            return;
        }

        unset($this->specifications[$gi]['items'][$ii]);
        $this->specifications[$gi]['items'] = array_values($this->specifications[$gi]['items']);

        if ($this->specifications[$gi]['items'] === []) {
            $this->specifications[$gi]['items'][] = ['title' => '', 'value' => ''];
        }
    }

    /**
     * Persist the spec-type library: current groups (title superset) are
     * merged back into the template so they re-appear on the next product.
     */
    protected function syncSpecTemplate(): void
    {
        $title = trim($this->specType);

        if ($title === '') {
            $this->specTemplateId = 0;

            return;
        }

        $template = SpecificationTemplate::query()->firstOrNew([
            'shop_id' => $this->shop()->id,
            'title'   => $title,
        ]);

        $template->mergeGroups($this->normalizedSpecifications());

        if (($template->groups ?? []) === []) {
            if ($template->exists) {
                $template->delete();
            }

            $this->specTemplateId = 0;

            return;
        }

        $template->save();

        $this->specTemplateId = (int) $template->id;
    }

    /* ══════════════════ specs modal ══════════════════ */

    /** Whether the «مشخصات محصول» editor modal is open. */
    public bool $specModalOpen = false;

    public function openSpecModal(): void
    {
        $this->resetErrorBag(['specifications', 'specType']);
        $this->specModalOpen = true;
    }

    public function closeSpecModal(): void
    {
        $this->specModalOpen = false;
    }

    /* ══════════════════ variant modal ══════════════════ */

    public function openVariantModal(?int $index = null): void
    {
        if ($index === null || ! isset($this->variants[$index])) {
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

            $index = count($this->variants) - 1;
        }

        $this->resetErrorBag();
        $this->variantModalIndex = $index;
    }

    public function closeVariantModal(): void
    {
        // a brand-new row that was never confirmed is dropped again
        $index = $this->variantModalIndex;

        if ($index !== null && isset($this->variants[$index]) && $this->variants[$index]['id'] === null) {
            $price = trim((string) ($this->variants[$index]['price'] ?? ''));

            if ($price === '') {
                unset($this->variants[$index]);
                $this->variants = array_values($this->variants);
            }
        }

        $this->variantModalIndex = null;
        $this->resetErrorBag();
    }

    /** Confirm the row being edited in the modal (inline per-row validation). */
    public function confirmVariantModal(): void
    {
        $index = $this->variantModalIndex;

        if ($index === null || ! isset($this->variants[$index])) {
            $this->closeVariantModal();

            return;
        }

        $row = $this->variants[$index];

        try {
            $this->validate([
                "variants.$index.price"       => ['required', 'numeric', 'min:0', 'max:999999999999'],
                "variants.$index.stock"       => ['required', 'integer', 'min:0', 'max:1000000'],
                "variants.$index.discount"    => ['nullable', 'integer', 'min:0', 'max:100'],
                "variants.$index.max_purchase" => ['nullable', 'integer', 'min:1', 'max:1000000'],
                "variants.$index.min_purchase" => ['nullable', 'integer', 'min:1', 'max:1000000'],
            ], [
                "variants.$index.price.required" => 'قیمت این تنوع را وارد کنید.',
                "variants.$index.stock.required" => 'موجودی انبار این تنوع را وارد کنید.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            // مودال باز می‌ماند و خطاها زیر فیلدها نشان داده می‌شوند — به‌همراه پیام پررنگ
            $this->dispatch('panel-toast', message: collect($exception->errors())->flatten()->filter()->first()
                ?: 'قیمت و موجودی این تنوع را تکمیل کنید.');

            throw $exception;
        }

        $min = (int) ($row['min_purchase'] ?? 0);
        $max = (int) ($row['max_purchase'] ?? 0);

        if ($min > 0 && $max > 0 && $min > $max) {
            $this->addError("variants.$index.min_purchase", 'حداقل خرید نمی‌تواند بیشتر از حداکثر خرید باشد.');

            return;
        }

        $this->variantModalIndex = null;
    }

    public function removeVariant(int $index): void
    {
        if ($this->variantModalIndex === $index) {
            $this->variantModalIndex = null;
        }

        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);

        // close the modal on the client too (Alpine state lives there)
        $this->dispatch('variant-close');

        if ($this->variants === []) {
            $this->addVariant();
        }
    }

    /** Keep a raw empty row so the «افزودن تنوع» button always has backup (never rendered directly). */
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
        $this->photos = array_values($this->photos);

        if ($this->featuredPhotoKey !== null && ! isset($this->photos[$this->featuredPhotoKey])) {
            $this->featuredPhotoKey = null;
        }
    }

    public function setFeaturedPhoto(int $key): void
    {
        if (isset($this->photos[$key])) {
            $this->featuredPhotoKey = $key;
        }
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

        // Resequence deterministically: the picked image gets sort 0 and every
        // other image shifts after it. Never produces negative values (the
        // `sort` column is UNSIGNED → MySQL error 1264 on -1).
        $product = $image->product;
        $ordered = $product->images()->orderBy('sort')->orderBy('id')->get()
            ->reject(fn (ProductImage $row) => $row->id === $image->id)
            ->values()
            ->prepend($image);

        foreach ($ordered as $index => $row) {
            if ((int) $row->sort !== $index) {
                $row->update(['sort' => $index]);
            }
        }
    }

    /* ══════════════════ save ══════════════════ */

    public function save(): void
    {
        try {
            $this->validateBase();
            $this->productType === Product::TYPE_DOWNLOADABLE
                ? $this->validateFiles()
                : $this->validateVariants();
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->announceFormErrors($exception->errors());

            throw $exception;
        }

        // دستی addError شده‌ها (مثل حداقل/حداکثر خرید) هم بی‌صدا نمانند
        if ($this->getErrorBag()->isNotEmpty()) {
            $this->announceFormErrors($this->getErrorBag()->toArray());

            return; // روی فرم بمان — خطاها زیر فیلدها نمایش داده شده‌اند
        }

        $product = $this->productId
            ? Product::query()->findOrFail($this->productId)
            : new Product;

        abort_if($product->exists && $product->shop_id !== $this->shop()->id, 404);

        DB::transaction(function () use ($product) {
            $product->fill([
                'shop_id'          => $this->shop()->id,
                'shop_category_id' => $this->categoryId ?: null,
                'name'             => trim($this->name),
                // خالی → خودکار از عنوان ساخته می‌شود (هوک مدل، فارسی‌پسند)
                'slug'             => trim($this->slug) !== '' ? trim($this->slug) : null,
                'description'      => trim($this->description) ?: null,
                'specifications'   => $this->normalizedSpecifications(),
                'spec_type'        => trim($this->specType) !== '' ? trim($this->specType) : null,
                'type'             => $this->productType,
                'weight_grams'     => $this->productType === Product::TYPE_PHYSICAL && $this->weight !== '' ? (int) $this->weight : null,
                'unit'             => $this->productType === Product::TYPE_PHYSICAL ? (trim($this->unit) ?: 'تعداد') : null,
                'status'           => $this->status,
                'is_special'       => $this->isSpecial,
                // the picked Jalali day counts as a full sale day → 23:59:59
                'special_ends_at'  => $this->isSpecial && $this->specialEndsAt !== ''
                    ? $this->specialEndsAt.' 23:59:59'
                    : null,
            ]);
            $product->save();

            $this->syncSpecTemplate();

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

    /**
     * «هیچ ذخیره‌ای بی‌پیام» — the seller ALWAYS gets a toast with the first
     * error message and the form auto-jumps to the tab holding that error.
     */
    protected function announceFormErrors(array $errors): void
    {
        $firstMsg = collect($errors)->flatten()->filter()->first();
        $firstKey = array_key_first($errors);

        $this->dispatch('panel-toast', message: $firstMsg ?: 'لطفاً خطاهای فرم محصول را برطرف کنید.');
        $this->dispatch('product-form-errors', tab: $this->errorTabOf((string) $firstKey));
    }

    /** Which form tab owns a failing field key (for the auto-jump on errors). */
    protected function errorTabOf(string $key): string
    {
        if (str_starts_with($key, 'variants')) {
            return 'pricing';
        }

        if (str_starts_with($key, 'files')) {
            return 'files';
        }

        return 'info';
    }

    /** Clean specification groups (drop empty titles/values, cap counts). */
    protected function normalizedSpecifications(): array
    {
        $clean = [];

        foreach (array_slice($this->specifications, 0, 10) as $group) {
            $name = trim((string) ($group['name'] ?? ''));
            $items = [];

            foreach (array_slice($group['items'] ?? [], 0, 25) as $item) {
                $title = trim((string) ($item['title'] ?? ''));
                $value = trim((string) ($item['value'] ?? ''));

                if ($title !== '' || $value !== '') {
                    $items[] = ['title' => $title, 'value' => $value];
                }
            }

            if ($name !== '' && $items !== []) {
                $clean[] = ['name' => $name, 'items' => $items];
            }
        }

        return $clean;
    }

    protected function validateBase(): void
    {
        $existingCount = $this->productId
            ? ProductImage::query()->where('product_id', $this->productId)->count()
            : 0;

        $this->validate([
            'name'        => ['required', 'string', 'min:2', 'max:150'],
            'slug'        => ['nullable', 'string', 'max:190', 'regex:/^[a-z0-9\x{0600}-\x{06FF}]+(?:-[a-z0-9\x{0600}-\x{06FF}]+)*$/u'],
            'description' => ['nullable', 'string', 'max:5000'],
            'categoryId'  => ['nullable', 'integer', Rule::in(array_merge([0], $this->shop()->categories()->pluck('id')->all()))],
            'productType' => ['required', 'in:'.Product::TYPE_PHYSICAL.','.Product::TYPE_DOWNLOADABLE],
            // کالای فیزیکی بدون وزن پذیرفته نمی‌شود (حمل‌ونقل بر اساس وزن)
            'weight'      => ['exclude_unless:productType,'.Product::TYPE_PHYSICAL, 'required', 'numeric', 'min:1', 'max:10000000'],
            'unit'        => ['nullable', 'string', 'max:20'],
            'status'      => ['required', 'in:draft,published'],
            'isSpecial'   => ['boolean'],
            'specialEndsAt' => ['nullable', 'exclude_unless:isSpecial,true', 'required', 'date'],
            'photos'      => ['nullable', 'array', 'max:'.max(0, 6 - $existingCount)],
            'photos.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'specifications'              => ['nullable', 'array', 'max:10'],
            'specifications.*.name'       => ['nullable', 'string', 'max:80'],
            'specifications.*.items'      => ['nullable', 'array', 'max:25'],
            'specifications.*.items.*.title' => ['nullable', 'string', 'max:80'],
            'specifications.*.items.*.value' => ['nullable', 'string', 'max:200'],
            'specType'                    => ['nullable', 'string', 'max:120'],
        ], [
            'name.required'  => 'نام محصول را وارد کنید.',
            'name.min'       => 'نام محصول حداقل ۲ کاراکتر است.',
            'slug.regex'     => 'آدرس محصول فقط می‌تواند شامل حروف کوچک انگلیسی، حروف فارسی، عدد و خط تیره باشد (بدون فاصله).',
            'slug.max'       => 'آدرس محصول حداکثر ۱۹۰ کاراکتر است.',
            'photos.max'     => 'حداکثر ۶ تصویر برای هر محصول مجاز است (تصاویر فعلی + جدید).',
            'photos.*.image' => 'فایل انتخاب‌شده تصویر نیست.',
            'photos.*.max'   => 'حجم هر تصویر حداکثر ۲ مگابایت است.',
            'specialEndsAt.required' => 'تاریخ پایان فروش ویژه را انتخاب کنید.',
            'specialEndsAt.date'     => 'تاریخ پایان فروش ویژه معتبر نیست.',
            'weight.required'        => 'برای کالای فیزیکی، وزن محصول (گرم) الزامی است.',
            'weight.numeric'         => 'وزن محصول باید عدد باشد.',
            'weight.min'             => 'وزن محصول باید بیشتر از صفر باشد.',
        ]);

        if ($this->isSpecial && trim($this->specialEndsAt) === '') {
            $this->addError('specialEndsAt', 'تاریخ پایان فروش ویژه را انتخاب کنید.');
        }

        // آدرس محصول باید داخل این فروشگاه یکتا باشد.
        $slugCandidate = trim($this->slug);

        if ($slugCandidate !== '') {
            $taken = Product::query()
                ->where('shop_id', $this->shop()->id)
                ->where('slug', $slugCandidate)
                ->when($this->productId, fn ($q) => $q->whereKeyNot($this->productId))
                ->exists();

            if ($taken) {
                $this->addError('slug', 'این آدرس قبلاً در فروشگاه شما استفاده شده است؛ آدرس دیگری وارد کنید یا خالی بگذارید تا خودکار ساخته شود.');
            }
        }
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
        // Saved files (minus removed ones) already satisfy the «at least one file»
        // rule — the new-row array is only REQUIRED when nothing is saved yet.
        $existingCount = $this->productId
            ? $this->existingFiles->whereNotIn('id', $this->removedFileIds)->count()
            : 0;

        $this->validate([
            'files'               => $existingCount > 0 ? ['nullable', 'array'] : ['required', 'array', 'min:1'],
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
        $photos = $this->photos;

        // the picked featured (شاخص) photo goes first → lowest sort
        if ($this->featuredPhotoKey !== null && isset($photos[$this->featuredPhotoKey])) {
            $featured = $photos[$this->featuredPhotoKey];
            unset($photos[$this->featuredPhotoKey]);
            $photos = array_merge([$this->featuredPhotoKey => $featured], $photos);
        }

        $baseSort = $product->images()->count() === 0 ? 0 : ((int) $product->images()->max('sort') + 1);

        foreach (array_values($photos) as $i => $photo) {
            $path = $photo->store("products/{$product->id}", 'public');

            ProductImage::query()->create([
                'product_id' => $product->id,
                'path'       => $path,
                'sort'       => $baseSort + $i,
            ]);
        }

        $this->photos          = [];
        $this->featuredPhotoKey = null;
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
            'shop'           => $this->shop(),
            'existingImages' => $this->existingImages,
            'existingFiles'  => $this->existingFiles,
            'shopAttributes' => $this->attributesWithValues,
            'shopCategories' => $this->shop()->categories()->orderBy('sort')->orderBy('id')->get(),
            'specTemplates'  => $this->specTemplates,
        ])->title($this->productId ? 'ویرایش محصول' : 'افزودن محصول');
    }

    /** Saved spec-type library of this shop (for the نوع مشخصات selector). */
    public function getSpecTemplatesProperty(): Collection
    {
        return SpecificationTemplate::query()
            ->where('shop_id', $this->shop()->id)
            ->orderBy('title')
            ->get(['id', 'title']);
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
