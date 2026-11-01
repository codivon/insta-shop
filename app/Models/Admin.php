<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Support\SchemaReady;

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

    /* ------------------------------------------------------------------
     |  Roles & permissions
     *----------------------------------------------------------------- */

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role', 'admin_id', 'role_id');
    }

    /**
     * Super admins (is_super flag — set for the very first admin) skip
     * every permission check.
     */
    public function isSuper(): bool
    {
        return (bool) $this->is_super;
    }

    /**
     * Can this admin open the page behind $permission (an admin route
     * name)? Super admins always can; everyone else needs the permission
     * granted through one of their roles.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuper()) {
            return true;
        }

        if (! SchemaReady::check('admin_roles')) {
            // Roles tables not migrated yet — fail closed except super.
            return false;
        }

        return $this->roles()
            ->get()
            ->contains(fn (AdminRole $role) => $role->grants($permission));
    }
}
