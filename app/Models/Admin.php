<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['name', 'email', 'password', 'is_super'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    protected function casts(): array
    {
        return [
            'is_super'      => 'boolean',
            'last_login_at' => 'datetime',
            'password'      => 'hashed',
        ];
    }
}
