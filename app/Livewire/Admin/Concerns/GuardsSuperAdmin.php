<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\Admin;

/**
 * Super-admin-only guard for DEVELOPER Livewire components.
 *
 * Developer pages (برنامه‌نویس) are exclusive to the creator/main admin
 * (is_super) and are intentionally NOT part of the permission registry —
 * no role can ever grant them. Route middleware already fails closed for
 * unregistered routes, but Livewire update requests bypass route
 * middleware — this trait re-checks on EVERY mount/hydrate.
 *
 * Usage: `use GuardsSuperAdmin;` (instead of GuardsPermissions).
 */
trait GuardsSuperAdmin
{
    public function bootGuardsSuperAdmin(): void
    {
        /** @var Admin|null $admin */
        $admin = auth('admin')->user();

        // Guests cannot talk to admin components at all.
        if ($admin === null) {
            abort(401, 'دسترسی مجاز نیست.');
        }

        if (! $admin->isSuper()) {
            abort(403, 'صفحات برنامه‌نویس فقط برای مدیر اصلی (سازنده) در دسترس است.');
        }
    }
}
