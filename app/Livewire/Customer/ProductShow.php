<?php

namespace App\Livewire\Customer;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Models\Shop;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class ProductShow extends Component
{
    public Product $product;

    /**
     * Attribute picks of the variant picker: shop_attribute_id => option key
     * («v7» = library value #7, «t:متن» = free text). Every pickable
     * attribute is always selected — the cheapest in-stock combination is
     * preselected on mount, and clicking an option re-resolves instantly.
     */
    public array $picks = [];

    /** Legacy fallback: direct variant id when NO pickable attribute exists. */
    public ?int $legacyVariantId = null;

    public function mount(string $instagram, string $slug): void
    {
        // Public page — only published products of publicly visible shops
        // are reachable (guests included).
        $handle = mb_strtolower(trim($instagram));

        $shop = Shop::query()
            ->publiclyVisible()
            ->where(function ($query) use ($handle) {
                $query
                    ->whereHas('seller', fn ($s) => $s->whereRaw('lower(instagram_username) = ?', [$handle]))
                    ->orWhereRaw('lower(instagram_username) = ?', [$handle]);
            })
            ->first();

        abort_if(! $shop, 404);

        // Slug first; legacy rows without a slug answer to «p{id}».
        $product = Product::query()
            ->where('shop_id', $shop->id)
            ->where('status', 'published')
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug);

                if (preg_match('/^p(\d+)$/', $slug, $m)) {
                    $query->orWhere('id', (int) $m[1]);
                }
            })
            ->with(['shop.seller', 'category', 'images'])
            ->first();

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

        // هر گروه تنوع همیشه یک گزینهٔ انتخاب‌شده دارد — اگر تنوع اولیه
        // ویژگی‌ای (مثل گارانتی) نداشت، بهترین ترکیبِ ممکن با همان انتخاب‌های
        // بالادستی به‌صورت خودکار برای گروه‌های پایین‌دستی انتخاب می‌شود.
        $this->fillMissingPicks();

        // Count a view once per component mount (= per page visit):
        // cumulative counter on the product + daily log for reports.
        \App\Models\ProductView::recordView($product);
    }

    /* ─────────────────────── interactions ─────────────────────── */

    /**
     * Attribute-option click: $optionIndex is the position of the option
     * inside the rendered attribute row (numeric → no quoting hazards).
     *
     * Attributes have PRIORITY order (the shop's attribute sort — رنگ first,
     * then سایز, گارانتی …). Picking an option keeps every EARLIER (higher
     * priority) pick and auto-corrects the LATER ones to the nearest valid
     * combination — e.g. selecting a color that lacks the currently picked
     * size re-picks a size the color actually offers (or drops the size row
     * entirely when the color has none).
     */
    public function selectOption(int $attributeId, int $optionIndex): void
    {
        // Reading the matrix here caches it against the OLD picks — exactly
        // the snapshot the user clicked on (stable numeric indexes). After
        // mutating the picks the cache MUST be dropped, otherwise the render
        // would repaint the stale matrix (hidden rows would stay hidden).
        $attribute = $this->variantMatrix->firstWhere('id', $attributeId);

        $option = $attribute['options'][$optionIndex] ?? null;

        if ($option && ($option['available'] ?? false)) {
            $this->picks[$attributeId] = $option['key'];

            $this->reconcilePicksAfter($attributeId);

            // هر گروه پایین‌دستی همیشه یک انتخاب معتبر دارد — هیچ ردیفی
            // بدون گزینهٔ انتخاب‌شده نمی‌ماند (مثلاً با انتخاب رنگ مشکی،
            // گارانتیِ ۲۴ ماهِ همان رنگ خودکار انتخاب می‌شود).
            $this->fillMissingPicks();

            unset($this->variantMatrix, $this->selectedVariant, $this->textNotes);
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

    /** Address of the seller's storefront (clickable store chip in the header). */
    public function getStoreUrlProperty(): string
    {
        return $this->product->shop?->storefrontUrl() ?? route('shop.home');
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
     * سایز / گارانتی …) in PRIORITY order, each with its round options
     * annotated for the CURRENT picks:
     *
     *   available → a real combination exists considering the picks of the
     *               HIGHER-priority rows only (later picks adapt to earlier
     *               ones, never the other way around — رنگ همیشه اولویت است)
     *   inStock   → that combination actually has stock
     *
     * Options with no matching combination are HIDDEN (e.g. a size the
     * picked color doesn't offer simply disappears), and a row whose
     * options all vanish (the picked color has no size at all) is not
     * rendered. Text-type attributes are NOT pickable — they surface as
     * read-only notes of the resolved variant (textNotes).
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

        // Annotate availability against the picks of HIGHER-priority rows
        // only, then drop the options that lead nowhere (hidden) and rows
        // left without any option.
        $matrix     = [];
        $upstream   = [];   // picks of rows already processed

        foreach ($ordered as $row) {
            $options = [];

            foreach (array_values($row['options']) as $option) {
                $match = $this->variantWithAttribute($row['id'], $option['key'], $upstream);

                if ($match === null) {
                    continue; // hidden — no combination with the upstream picks
                }

                $option['available'] = true;
                $option['inStock']   = $this->isDownloadable || $match->stock > 0;

                $options[] = $option;
            }

            if ($options === []) {
                continue; // e.g. سایز ندارد for the picked color
            }

            $row['options'] = $options;

            $matrix[] = $row;

            // The pick of this row constrains the rows below it.
            $pick = $this->picks[$row['id']] ?? null;

            if ($pick !== null && in_array($pick, array_column($options, 'key'), true)) {
                $upstream[$row['id']] = $pick;
            }
        }

        return collect($matrix);
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

    /**
     * Related products: same store AND same category only (خواستهٔ فروشنده‌ها —
     * محصولات مرتبط از دسته‌بندیِ همان محصول و فقط از این فروشگاه). When the
     * product has no seller category the same-store feed is the fallback.
     */
    public function getRelatedProperty(): Collection
    {
        return Product::query()
            ->where('shop_id', $this->product->shop_id)
            ->where('status', 'published')
            ->whereKeyNot($this->product->id)
            ->when($this->product->shop_category_id, fn ($q) => $q->where('shop_category_id', $this->product->shop_category_id))
            ->with(['mainImage'])
            ->latest()
            ->limit(12)
            ->get();
    }

    public function render()
    {
        return view('livewire.customer.product-show', [
            'gallery'        => $this->gallery,
            'storeUrl'       => $this->storeUrl,
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

    /**
     * First variant (best score) whose values satisfy every pick — a
     * variant that LACKS an attribute is compatible with any pick of that
     * attribute (wildcard), so partial variants (e.g. a color without any
     * size) still resolve. Among the matches the one satisfying the most
     * picks explicitly wins.
     */
    private function variantMatching(array $picks): ?ProductVariant
    {
        if ($picks === []) {
            return null;
        }

        $best      = null;
        $bestScore = -1;

        foreach ($this->variants as $variant) {
            if (! $this->wildcardMatches($variant, $picks)) {
                continue;
            }

            $score = 0;

            foreach ($picks as $attributeId => $key) {
                $value = $variant->valueForAttribute((int) $attributeId);

                if ($value !== null && $this->optionKeyOf($value) === $key) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $best      = $variant;
                $bestScore = $score;
            }
        }

        return $best;
    }

    /** Whether the variant satisfies every pick (missing attribute = wildcard). */
    private function wildcardMatches(ProductVariant $variant, array $picks): bool
    {
        foreach ($picks as $attributeId => $key) {
            $value = $variant->valueForAttribute((int) $attributeId);

            if ($value === null) {
                continue; // variant doesn't have this attribute at all
            }

            if ($this->optionKeyOf($value) !== $key) {
                return false;
            }
        }

        return true;
    }

    /**
     * First variant that carries «$key» on «$attributeId» AND satisfies the
     * other (higher-priority) picks with wildcards allowed.
     */
    private function variantWithAttribute(int $attributeId, string $key, array $otherPicks): ?ProductVariant
    {
        foreach ($this->variants as $variant) {
            $value = $variant->valueForAttribute($attributeId);

            if ($value === null || $this->optionKeyOf($value) !== $key) {
                continue;
            }

            if ($this->wildcardMatches($variant, $otherPicks)) {
                return $variant;
            }
        }

        return null;
    }

    /**
     * Priority-ordered pickable attribute ids (same ordering the matrix
     * renders: shop attribute sort, then id).
     */
    private function orderedAttributeIds(): array
    {
        $ids    = [];
        $sortBy = [];

        foreach ($this->variants as $variant) {
            foreach ($variant->values as $value) {
                $attribute = $value->attribute;

                if (! $attribute || $attribute->type === 'text') {
                    continue;
                }

                $attrId = (int) $value->shop_attribute_id;

                if (! isset($sortBy[$attrId])) {
                    $ids[$attrId]    = $attrId;
                    $sortBy[$attrId] = [(int) $attribute->sort, $attrId];
                }
            }
        }

        uasort($ids, fn ($a, $b) => $sortBy[$a] <=> $sortBy[$b]);

        return array_values($ids);
    }

    /** All option keys a pickable attribute offers across the variants. */
    private function optionKeysOfAttribute(int $attributeId): array
    {
        $keys = [];

        foreach ($this->variants as $variant) {
            $value = $variant->valueForAttribute($attributeId);

            $key = $this->optionKeyOf($value);

            if ($key !== null && ! in_array($key, $keys, true)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Every pickable attribute row must ALWAYS carry a selection: for any
     * row without a pick, the best option reachable with the picks ABOVE it
     * is chosen (cheapest in-stock first). Keeps the picker in the
     * «همیشه یک مورد انتخاب‌شده» state the seller community asked for.
     */
    private function fillMissingPicks(): void
    {
        $upstream = [];

        foreach ($this->orderedAttributeIds() as $id) {
            if (isset($this->picks[$id])) {
                $upstream[$id] = $this->picks[$id];

                continue;
            }

            $best = collect($this->optionKeysOfAttribute($id))
                ->map(fn ($key) => ['key' => $key, 'variant' => $this->variantWithAttribute($id, $key, $upstream)])
                ->filter(fn ($row) => $row['variant'] !== null)
                ->filter(fn ($row) => $this->isDownloadable || $row['variant']->stock > 0)
                ->sortBy(fn ($row) => $row['variant']->finalPrice())
                ->first();

            if ($best === null) {
                continue; // nothing reachable — row will render empty
            }

            $this->picks[$id] = $best['key'];
            $upstream[$id]    = $best['key'];
        }
    }

    /**
     * After a pick changes on «$changedAttrId», every LOWER-priority pick
     * is re-validated against the new upstream picks (INCLUDING the changed
     * row itself — رنگ همیشه محدودکنندهٔ گروه‌های پایین‌تر است): kept when
     * still reachable, otherwise replaced with the best reachable option
     * (in-stock & cheapest first) or dropped when nothing fits.
     */
    private function reconcilePicksAfter(int $changedAttrId): void
    {
        $ordered = $this->orderedAttributeIds();

        $pos = array_search($changedAttrId, $ordered, true);

        if ($pos === false) {
            return;
        }

        $upstream = [];

        // the changed attribute's OWN new pick must constrain everything
        // below it — previously only the rows ABOVE were collected, which
        // let stale lower picks (e.g. a size the new color lacks) survive.
        foreach (array_slice($ordered, 0, $pos + 1) as $id) {
            if (isset($this->picks[$id])) {
                $upstream[$id] = $this->picks[$id];
            }
        }

        foreach (array_slice($ordered, $pos + 1) as $id) {
            $reachable  = [];   // key => resolving variant
            $firstAvail = null;

            foreach ($this->optionKeysOfAttribute($id) as $key) {
                $match = $this->variantWithAttribute($id, $key, $upstream);

                if ($match === null) {
                    continue;
                }

                $reachable[$key] = $match;

                if ($firstAvail === null) {
                    $firstAvail = $key;
                }
            }

            $current = $this->picks[$id] ?? null;

            if ($current !== null && isset($reachable[$current])) {
                $upstream[$id] = $current;

                continue; // still valid — untouched
            }

            // Best replacement: cheapest IN-STOCK combination, fallback first reachable.
            $replacement = collect($reachable)
                ->filter(fn (ProductVariant $v) => $this->isDownloadable || $v->stock > 0)
                ->sortBy(fn (ProductVariant $v) => $v->finalPrice())
                ->keys()
                ->first()
                ?? $firstAvail;

            if ($replacement !== null) {
                $this->picks[$id] = $replacement;
                $upstream[$id]    = $replacement;
            } else {
                unset($this->picks[$id]);
            }
        }
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
