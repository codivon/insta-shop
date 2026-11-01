<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

#[Fillable(['customer_id', 'guest_token'])]
class Cart extends Model
{
    /**
     * Shared domain cookie carrying the guest-cart token. Deliberately NOT
     * a panel session value: the storefront lives on the «landing» session
     * while product/cart/checkout live on the «customer» session, and only
     * a plain cookie is visible to both.
     */
    public const GUEST_COOKIE = 'instashop_cart';

    /** Guest carts older than this are garbage-collected opportunistically. */
    protected const GUEST_TTL_DAYS = 45;

    /** Get-or-create the single cart of the given customer (safe on every request). */
    public static function ensureForCustomer(Customer $customer): self
    {
        return static::query()->firstOrCreate(['customer_id' => $customer->id]);
    }

    /**
     * Get-or-create the cart of the current visitor: the customer's own
     * cart when logged in, otherwise the guest cart behind the shared
     * cookie token. Used by MUTATING actions (add / merge / badge after
     * change) — page views should use forVisitor() to avoid writes.
     */
    public static function ensureForVisitor(?Customer $customer): self
    {
        if ($customer) {
            return static::ensureForCustomer($customer);
        }

        static::pruneStaleGuests();

        $token = static::currentGuestToken();

        return static::query()->firstOrCreate(['guest_token' => $token]);
    }

    /**
     * Read-only variant of ensureForVisitor — resolves the visitor's cart
     * WITHOUT creating one (badge / cart page rendering).
     */
    public static function forVisitor(?Customer $customer): ?self
    {
        if ($customer) {
            return static::query()->where('customer_id', $customer->id)->first();
        }

        $token = static::guestTokenFromRequest();

        return $token !== ''
            ? static::query()->where('guest_token', $token)->first()
            : null;
    }

    /**
     * Merge the guest cart identified by the cookie token into the given
     * customer's cart and retire the guest cart. Idempotent — safe to run
     * on every request while the cookie is still around.
     */
    public static function mergeGuestIntoCustomer(string $token, Customer $customer): void
    {
        $guest = static::query()->where('guest_token', $token)->first();

        if (! $guest) {
            return;
        }

        $own = static::query()->where('customer_id', $customer->id)->first();

        // No own cart yet → simply adopt the guest cart wholesale.
        if (! $own) {
            $guest->update(['customer_id' => $customer->id, 'guest_token' => null]);

            return;
        }

        foreach ($guest->items as $item) {
            // New line → adopt the guest quantity as-is; existing line → bump it.
            $line = $own->items()->firstOrCreate(
                ['product_id' => $item->product_id, 'variant_id' => $item->variant_id],
                ['quantity' => $item->quantity],
            );

            if (! $line->wasRecentlyCreated) {
                $line->increment('quantity', $item->quantity);
            }
        }

        $guest->delete(); // items cascade
    }

    /** Existing (or new) guest token — creating one queues the shared cookie. */
    public static function currentGuestToken(): string
    {
        $token = static::guestTokenFromRequest();

        if ($token === '') {
            $token = bin2hex(random_bytes(24));

            Cookie::queue(
                static::GUEST_COOKIE,
                $token,
                60 * 24 * 90, // 90 days
                '/',
                config('session.domain'),
                (bool) config('session.secure'),
                true,  // httpOnly
                false,
                (string) config('session.same_site', 'lax'),
            );
        }

        return $token;
    }

    /** Raw guest token from the shared cookie ('' = none). */
    public static function guestTokenFromRequest(): string
    {
        /** @var Request $request */
        $request = request();

        return substr((string) $request->cookie(static::GUEST_COOKIE, ''), 0, 64);
    }

    /** 1-in-100 opportunistic cleanup of long-abandoned guest carts. */
    protected static function pruneStaleGuests(): void
    {
        if (random_int(1, 100) !== 1) {
            return;
        }

        static::query()
            ->whereNotNull('guest_token')
            ->where('updated_at', '<', now()->subDays(static::GUEST_TTL_DAYS))
            ->limit(50)
            ->get()
            ->each(fn (self $cart) => $cart->delete());
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class)->latest('id');
    }

    /** Distinct product count (used for the tab-bar badge). */
    public function countDistinct(): int
    {
        return $this->items()->count();
    }

    /** Total quantity across items. */
    public function countUnits(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    /** Cart total in Toman: Σ (effective unit price × qty) for published products. */
    public function totalToman(): int
    {
        return (int) $this->items()
            ->with(['product:id,price,status', 'variant'])
            ->get()
            ->filter(fn ($item) => $item->product && $item->product->status === 'published')
            ->sum(fn ($item) => $item->unitPrice() * $item->quantity);
    }
}
