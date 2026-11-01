<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\Admin;
use App\Support\AdminPermissions;

/**
 * Page-permission guard for admin Livewire components.
 *
 * Route middleware (EnsureAdminPermission) protects the page URL, but
 * Livewire update requests hit the panel-less /livewire/update endpoint —
 * this trait re-checks the permission on EVERY mount/hydrate of the
 * component (Livewire calls boot{TraitName} automatically).
 *
 * Usage: `use GuardsPermissions;` + `protected static string $permission = 'admin.orders';`
 */
trait GuardsPermissions
{
    public function bootGuardsPermissions(): void
    {
        /** @var Admin|null $admin */
        $admin = auth('admin')->user();

        $permission = static::$permission ?? null;

        // Guests cannot talk to admin components at all.
        if ($admin === null) {
            self::denyPage($permission, 401);

            return;
        }

        if ($permission === null || $admin->hasPermission($permission)) {
            return;
        }

        self::denyPage($permission, 403);
    }

    protected static function denyPage(?string $permission, int $status): void
    {
        abort($status, $permission
            ? 'دسترسی لازم برای «'.AdminPermissions::label($permission).'» را ندارید.'
            : 'دسترسی مجاز نیست.');
    }
}
