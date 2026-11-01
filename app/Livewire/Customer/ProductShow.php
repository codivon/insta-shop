<?php

namespace App\Livewire\Customer;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class ProductShow extends Component
{
    public Product $product;

    /** Selected gallery image id (null = main image). */
    public ?int $selectedImageId = null;

    /**
     * Attribute picks of the variant picker: shop_attribute_id => option key
     * («v7» = library value #7, «t:متن» = free text). Every pickable
     * attribute is always selected — the cheapest in-stock combination is
     * preselected on mount, and clicking an option re-resolves instantly.
     */
    public array $picks = [];

    /** Legacy fallback: direct variant id when NO pickable attribute exists. */
    public ?int $legacyVariantId = null;

    public function mount(int $productId): void
    {
        // Public page — only published products of publicly visible shops
        // are reachable (guests included).
        $product = Product::query()
            ->where('status', 'published')
            ->whereHas('shop', fn ($q) => $q->publiclyVisible())
            ->with(['shop.seller', 'category', 'images'])
            ->find($productId);

        abort_if(! $product, 404);

        $this->product = $product;

        // Default = the cheapest IN-STOCK combination (fallback: cheapest
        // overall, then the very first variant).
        $initial = $this->variants
            ->filter(fn (ProductVariant $v) => $v->stock > 0)
            ->sortBy(fn (ProductVariant $v) => $v->finalPrice())
            ->first()
            ?? $this->variants->sortBy(fn (ProductVariant $v) => $v->finalPrice())->first()
            ?? $this->variants->first();

        $this->legacyVariantId = $initial?->id;
        $this->picks           = $this->picksFor($initial);

        // Count a view once per component mount (= per page visit):
        // cumulative counter on the product + daily log for reports.
        \App\Models\ProductView::recordView($product);
    }

    /* ─────────────────────── interactions ─────────────────────── */

    public function selectImage(?int $imageId): void
    {
        $this->selectedImageId = $imageId;
    }

    /**
     * Attribute-option click: $optionIndex is the position of the option
     * inside the rendered attribute row (numeric → no quoting hazards).
     * Unavailable (dimmed) options are rejected server-side too.
     */
    public function selectOption(int $attributeId, int $optionIndex): void
    {
        $attribute = $this->variantMatrix->firstWhere('id', $attributeId);

        $option = $attribute['options'][$optionIndex] ?? null;

        if ($option && ($option['available'] ?? false)) {
            $this->picks[$attributeId] = $option['key'];
        }
    }

    /** Legacy combo-grid click (products without pickable attributes). */
    public function selectLegacyVariant(int $variantId): void
    {
        if ($this->variants->contains('id', $variantId)) {
            $this->legacyVariantId = $variantId;
        }
    }

    public function toggleFavorite(): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            $this->dispatch('product-toast', message: 'برای ذخیره در علاقه‌مندی‌ها ابتدا وارد حساب شوید.');

            return;
        }

        $existing = Favorite::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $this->product->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return;
        }

        Favorite::query()->firstOrCreate([
            'customer_id' => $customer->id,
            'product_id'  => $this->product->id,
        ]);
    }

    /* ───────────────────── cart (fixed bottom bar) ───────────────────── */

    /** «افزودن به سبد خرید» — adds one unit of the picked combination. */
    public function addToCart(): void
    {
        if ($this->product->hasVariants() && ! $this->selectedVariant) {
            $this->dispatch('product-toast', message: 'این ترکیب از تنوع‌ها موجود نیست؛ ترکیب دیگری را انتخاب کنید.');

            return;
        }

        $cap = $this->purchaseCap;

        if (! $this->isDownloadable && $this->purchaseCap < 1) {
            $this->dispatch('product-toast', message: 'این محصول موجود نیست.');

            return;
        }

        $cart = Cart::ensureForVisitor(auth('customer')->user());
        $item = $cart->items()->firstOrCreate(
            ['product_id' => $this->product->id, 'variant_id' => $this->variantKey],
            ['quantity' => 0],
        );

        $item->refresh();

        if ($item->quantity >= $cap) {
            $this->dispatch('product-toast', message: 'بیش از این مقدار نمی‌توانید خرید کنید.');

            return;
        }

        $item->update(['quantity' => $item->quantity + 1]);

        $this->dispatch('cart-changed', count: $cart->countDistinct());
    }

    /** + on the fixed bar stepper — instant one-unit bump of the cart line. */
    public function cartIncrement(): void
    {
        $item = $this->cartLine;

        if (! $item) {
            $this->addToCart();

            return;
        }

        $cap = $this->purchaseCap;

        if ($item->quantity >= $cap) {
            $this->dispatch('product-toast', message: 'بیش از این مقدار نمی‌توانید خرید کنید.');

            return;
        }

        $item->update(['quantity' => $item->quantity + 1]);

        $this->dispatch('cart-changed', count: $this->cartCount);
    }

    /** − on the fixed bar stepper — instant one-unit drop (below the minimum → line removed). */
    public function cartDecrement(): void
    {
        $item = $this->cartLine;

        if (! $item) {
            return;
        }

        $minPurchasable = max(1, (int) ($this->selectedVariant?->min_purchase ?? 0));

        if ($item->quantity <= $minPurchasable) {
            $item->delete();
        } else {
            $item->decrement('quantity');
        }

        $this->dispatch('cart-changed', count: $this->cartCount);
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getGalleryProperty(): array
    {
        return $this->product->images->map(fn ($img) => [
            'id'  => $img->id,
            'url' => $img->url(),
        ])->all();
    }

    public function getActiveImageProperty(): ?array
    {
        $gallery = $this->gallery;

        if ($gallery === []) {
            return null;
        }

        return collect($gallery)->firstWhere('id', $this->selectedImageId) ?? $gallery[0];
    }

    public function getIsFavoriteProperty(): bool
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return false;
        }

        return Favorite::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $this->product->id)
            ->exists();
    }

    /** Active variants of this product (with their attribute picks). */
    public function getVariantsProperty(): Collection
    {
        if ($this->product->type === Product::TYPE_DOWNLOADABLE) {
            return collect();
        }

        return $this->product->activeVariants()
            ->with(['values.attribute', 'values.value'])
            ->get();
    }

    /**
     * The variant-picker matrix — one row per pickable attribute (رنگ /
     * سایز / گارانتی …), each with its round options annotated for the
     * CURRENT picks:
     *   available → a real combination exists with the other picks kept
     *   inStock   → that combination actually has stock
     * Text-type attributes are NOT pickable — they surface as read-only
     * notes of the resolved variant (textNotes).
     */
    public function getVariantMatrixProperty(): Collection
    {
        $rows   = [];
        $order  = [];   // attribute id → sort position
        $sortBy = [];   // attribute id → ShopAttribute sort column

        foreach ($this->variants as $variant) {
            foreach ($variant->values as $value) {
                $attribute = $value->attribute;

                if (! $attribute || $attribute->type === 'text') {
                    continue;
                }

                $key = $this->optionKeyOf($value);

                if ($key === null) {
                    continue;
                }

                $attrId = (int) $value->shop_attribute_id;

                if (! isset($rows[$attrId])) {
                    $order[$attrId]  = count($order);
                    $sortBy[$attrId] = (int) $attribute->sort;

                    $rows[$attrId] = [
                        'id'      => $attrId,
                        'name'    => $attribute->name,
                        'type'    => $attribute->type,
                        'options' => [],
                    ];
                }

                if (! isset($rows[$attrId]['options'][$key])) {
                    $rows[$attrId]['options'][$key] = [
                        'key'   => $key,
                        'label' => (string) ($value->value?->value ?? $value->value_text ?? ''),
                        'hex'   => $value->value?->color_hex,
                    ];
                }
            }
        }

        if ($rows === []) {
            return collect();
        }

        // Attributes ordered by the shop's attribute sort, options in
        // first-seen (seller variant) order → stable numeric indexes.
        $ordered = collect($rows)->sortBy(fn ($row, $id) => [$sortBy[$id], $id])->values();

        // Annotate availability against the CURRENT picks.
        $ordered->transform(function (array $row) {
            $options = [];

            foreach (array_values($row['options']) as $index => $option) {
                $candidate         = $this->picks;
                $candidate[$row['id']] = $option['key'];

                $match        = $this->variantMatching($candidate);
                $option['available'] = $match !== null;
                $option['inStock']   = $match !== null
                    && ($this->isDownloadable || $match->stock > 0);

                $options[] = $option;
            }

            $row['options'] = $options;

            return $row;
        });

        return $ordered;
    }

    /** Read-only text-attribute notes of the resolved variant (e.g. حکاکی). */
    public function getTextNotesProperty(): array
    {
        $variant = $this->selectedVariant;

        if (! $variant) {
            return [];
        }

        $notes = [];

        foreach ($variant->values as $value) {
            $attribute = $value->attribute;

            if (! $attribute || $attribute->type !== 'text') {
                continue;
            }

            $text = trim((string) $value->value_text);

            if ($text !== '') {
                $notes[] = ['name' => $attribute->name, 'text' => $text];
            }
        }

        return $notes;
    }

    public function getSelectedVariantProperty(): ?ProductVariant
    {
        if ($this->variants->isEmpty()) {
            return null;
        }

        // No pickable attributes → legacy direct selection.
        if ($this->variantMatrix->isEmpty()) {
            return $this->variants->firstWhere('id', $this->legacyVariantId)
                ?? $this->variants->first();
        }

        return $this->variantMatching($this->picks);
    }

    /** 0 = plain product line, otherwise the picked variant id. */
    public function getVariantKeyProperty(): int
    {
        return (int) ($this->selectedVariant?->id ?? 0);
    }

    /** Price on display: picked variant (discount-aware) or the product price. */
    public function getDisplayPriceProperty(): int
    {
        return $this->selectedVariant
            ? $this->selectedVariant->finalPrice()
            : (int) $this->product->price;
    }

    /** Downloadable goods are never stock-limited. */
    public function getIsDownloadableProperty(): bool
    {
        return $this->product->type === Product::TYPE_DOWNLOADABLE;
    }

    public function getDisplayStockProperty(): int
    {
        return $this->selectedVariant
            ? (int) $this->selectedVariant->stock
            : (int) $this->product->stock;
    }

    /** Max units purchasable for the current selection (stock vs max_purchase). */
    public function getPurchaseCapProperty(): int
    {
        if ($this->isDownloadable) {
            return 99;
        }

        $cap = $this->displayStock;
        $maxPerOrder = (int) ($this->selectedVariant?->max_purchase ?? 0);

        return $maxPerOrder > 0 ? min($cap, $maxPerOrder) : $cap;
    }

    /** The current (product, variant) cart line of the visitor (customer OR guest). */
    public function getCartLineProperty(): ?CartItem
    {
        $cart = Cart::forVisitor(auth('customer')->user());

        if (! $cart) {
            return null;
        }

        return CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $this->product->id)
            ->where('variant_id', $this->variantKey)
            ->first();
    }

    /** Quantity of the current selection already in the cart (0 = not added yet). */
    public function getCartQtyProperty(): int
    {
        return (int) ($this->cartLine?->quantity ?? 0);
    }

    /** Distinct cart lines of the current visitor — drives the header badge. */
    public function getCartCountProperty(): int
    {
        return Cart::forVisitor(auth('customer')->user())?->countDistinct() ?? 0;
    }

    public function getDiscountPercentProperty(): int
    {
        $compare = $this->product->compare_at_price;

        if (! $compare || $compare <= $this->product->price) {
            return 0;
        }

        return (int) round(100 - ($this->product->price / $compare * 100));
    }

    /** «فروش ویژه» deadline in ms epoch (null = no active special sale) — drives the countdown. */
    public function getSpecialEndsMsProperty(): ?int
    {
        if (! $this->product->isSpecialActive() || ! $this->product->special_ends_at) {
            return null;
        }

        return $this->product->special_ends_at->timestamp * 1000;
    }

    /** Related products: same shop first, same category prioritized. */
    public function getRelatedProperty(): Collection
    {
        return Product::query()
            ->where('shop_id', $this->product->shop_id)
            ->where('status', 'published')
            ->whereKeyNot($this->product->id)
            ->with(['mainImage'])
            ->when($this->product->shop_category_id, fn ($q) => $q->orderByRaw('shop_category_id = ? desc', [$this->product->shop_category_id]))
            ->latest()
            ->limit(12)
            ->get();
    }

    public function render()
    {
        return view('livewire.customer.product-show', [
            'gallery'        => $this->gallery,
            'activeImg'      => $this->activeImage,
            'isFavorite'     => $this->isFavorite,
            'discount'       => $this->discountPercent,
            'specialEndsMs'  => $this->specialEndsMs,
            'variants'       => $this->variants,
            'variantMatrix'  => $this->variantMatrix,
            'textNotes'      => $this->textNotes,
            'selectedVariant'=> $this->selectedVariant,
            'displayPrice'   => $this->displayPrice,
            'displayStock'   => $this->displayStock,
            'cartQty'        => $this->cartQty,
            'cartCount'      => $this->cartCount,
            'purchaseCap'    => $this->purchaseCap,
            'isDownloadable' => $this->isDownloadable,
            'related'        => $this->related,
        ])->title($this->product->name);
    }

    /* ─────────────────────── picker internals ─────────────────────── */

    /** First active variant whose values satisfy every pick. */
    private function variantMatching(array $picks): ?ProductVariant
    {
        if ($picks === []) {
            return null;
        }

        return $this->variants->first(function (ProductVariant $variant) use ($picks) {
            foreach ($picks as $attributeId => $key) {
                $value = $variant->valueForAttribute((int) $attributeId);

                if ($this->optionKeyOf($value) !== $key) {
                    return false;
                }
            }

            return true;
        });
    }

    /** Picks of a given variant, limited to the pickable (matrix) attributes. */
    private function picksFor(?ProductVariant $variant): array
    {
        if (! $variant) {
            return [];
        }

        $picks = [];

        foreach ($variant->values as $value) {
            $attribute = $value->attribute;

            if (! $attribute || $attribute->type === 'text') {
                continue;
            }

            $key = $this->optionKeyOf($value);

            if ($key !== null) {
                $picks[(int) $value->shop_attribute_id] = $key;
            }
        }

        return $picks;
    }

    /** Stable identity of a variant-value cell: «v5» (library) or «t:متن» (free text). */
    private function optionKeyOf(?ProductVariantValue $value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value->shop_attribute_value_id) {
            return 'v'.$value->shop_attribute_value_id;
        }

        $text = trim((string) $value->value_text);

        return $text !== '' ? 't:'.$text : null;
    }
}
