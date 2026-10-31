<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['phone', 'instagram_username', 'full_name', 'status', 'phone_verified_at'])]
#[Hidden(['remember_token'])]
class Seller extends Authenticatable
{
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(Product::class, Shop::class);
    }

    /** Human-friendly status label for the admin panel. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'approved' => 'تأیید شده',
            'suspended' => 'غیرفعال',
            default => 'در انتظار تأیید',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            'approved' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
            'suspended' => 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400',
            default => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400',
        };
    }

    public function routeNotificationForSms(): string
    {
        return $this->phone;
    }
}
