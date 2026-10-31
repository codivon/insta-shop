<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['phone', 'full_name', 'status', 'phone_verified_at'])]
#[Hidden(['remember_token'])]
class Customer extends Authenticatable
{
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest('id');
    }

    /** Favorite product ids (cheap lookup for heart toggles). */
    public function favoriteProductIds(): array
    {
        return $this->favorites()->pluck('product_id')->all();
    }

    public function routeNotificationForSms(): string
    {
        return $this->phone;
    }

    /** Human-friendly status label for the admin panel. */
    public function statusLabel(): string
    {
        return $this->status === 'blocked' ? 'مسدود' : 'فعال';
    }

    public function statusClasses(): string
    {
        return $this->status === 'blocked'
            ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400'
            : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400';
    }
}
