<?php

namespace App\Support;

/**
 * Registry helper for admin permissions (config/admin-permissions.php).
 * Permission slugs == admin route names; enforcement fails closed for
 * unregistered routes (only super admins may open them).
 */
class AdminPermissions
{
    /** All permissions grouped: ['group' => ['label' => ..., 'permissions' => [slug => label]]] */
    public static function groups(): array
    {
        return config('admin-permissions.groups', []);
    }

    /** Flat [slug => fa label] map of every registered permission. */
    public static function all(): array
    {
        $flat = [];

        foreach (self::groups() as $group) {
            foreach ($group['permissions'] as $slug => $label) {
                $flat[$slug] = $label;
            }
        }

        return $flat;
    }

    /** Persian label of a permission slug (falls back to the slug itself). */
    public static function label(string $slug): string
    {
        return self::all()[$slug] ?? $slug;
    }

    /** Is this route name registered as a permission? */
    public static function exists(string $slug): bool
    {
        return array_key_exists($slug, self::all());
    }
}
