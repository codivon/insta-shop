<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * v0.32.0 — a product the seller pulled OUT of installment (BNPL) selling.
 *
 * The seller enables a BNPL gateway per shop (SellerBnplSetting) and can
 * then, from the new «محصولات مستثنی اقساطی» page, exclude individual
 * products («نمی‌خوام چندتا از محصولاتم اقساطی فروخته بشن»).
 *
 * A single rule drives every consumer:
 *  • InstallmentExclusion::isExcluded(sellerId, productId)
 *    - the product-page badge (ProductShow::bnplShowcase) skips it,
 *    - checkout treats it as ineligible for every installment gateway and
 *      names it in the eligibility modal («محصول X جزو کالاهای اقساطی نیست»),
 *    - BNPL surcharges are only ever computed on eligible lines.
 */
#[Fillable(['seller_id', 'product_id'])]
class InstallmentExclusion extends Model
{
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Product ids THIS seller excluded (list pages, filter queries). */
    public static function idsForSeller(int $sellerId): array
    {
        return static::query()
            ->where('seller_id', $sellerId)
            ->pluck('product_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * THE single exclusion rule — the product-page badge, the checkout
     * eligibility analysis and the seller toggle all ride this method.
     */
    public static function isExcluded(int $sellerId, ?int $productId): bool
    {
        if ($sellerId < 1 || (int) $productId < 1) {
            return false;
        }

        return static::query()
            ->where('seller_id', $sellerId)
            ->where('product_id', (int) $productId)
            ->exists();
    }

    /**
     * The excluded subset of a cart, as «sellerId:productId» pairs.
     *
     * Checkout passes every cart product id once and gets the offending
     * pairs back — one query for the whole cart, no per-item lookups.
     * Pairing by seller too (not just product id) keeps the rule strict
     * even if a product ever changes shops.
     *
     * @param  list<int>  $productIds
     * @return list<string>
     */
    public static function excludedPairs(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));

        if ($productIds === []) {
            return [];
        }

        return static::query()
            ->whereIn('product_id', $productIds)
            ->get(['seller_id', 'product_id'])
            ->map(fn (self $row): string => $row->seller_id.':'.$row->product_id)
            ->all();
    }
}
