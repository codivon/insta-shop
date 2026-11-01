<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A named bundle of admin-panel permissions. Permission slugs are admin
 * route names (see config/admin-permissions.php) stored as a JSON array.
 */
class AdminRole extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'permissions', 'is_system'];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system'   => 'boolean',
        ];
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_role', 'role_id', 'admin_id');
    }

    /** Does this role grant the given permission slug (route name)? */
    public function grants(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }
}
