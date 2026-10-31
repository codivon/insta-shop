<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['phone', 'instagram_username', 'full_name', 'status', 'phone_verified_at'])]
#[Hidden(['remember_token'])]
class Seller extends Authenticatable
{
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
        ];
    }

    public function routeNotificationForSms(): string
    {
        return $this->phone;
    }
}
