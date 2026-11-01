<?php

/*
|--------------------------------------------------------------------------
| Admin permission registry
|--------------------------------------------------------------------------
|
| Every admin PANEL PAGE is a permission. The permission slug is exactly
| the route name of the page, so the enforcement middleware can resolve
| the required permission straight from the matched route.
|
| When you add a new admin page: register its route name here too — the
| middleware FAILS CLOSED, meaning an unregistered admin route is only
| reachable by super admins.
|
*/

return [

    'groups' => [

        'overview' => [
            'label' => 'نمای کلی',
            'permissions' => [
                'admin.dashboard' => 'داشبورد',
            ],
        ],

        'catalog' => [
            'label' => 'مدیریت فروشگاه و سفارش‌ها',
            'permissions' => [
                'admin.orders'      => 'سفارش‌ها',
                'admin.orders.show' => 'جزئیات سفارش',
                'admin.shops'       => 'فروشگاه‌ها',
                'admin.products'    => 'محصولات',
            ],
        ],

        'users' => [
            'label' => 'کاربران',
            'permissions' => [
                'admin.users.sellers'  => 'فروشندگان',
                'admin.users.customers' => 'مشتریان',
            ],
        ],

        'settings' => [
            'label' => 'تنظیمات',
            'permissions' => [
                'admin.settings.general'    => 'تنظیمات عمومی (عنوان، لوگو، فاوآیکون)',
                'admin.settings.appearance' => 'ظاهر و رنگ‌بندی',
                'admin.settings.sms'        => 'پیامک',
                'admin.settings.payment'    => 'درگاه پرداخت',
            ],
        ],

        'access' => [
            'label' => 'دسترسی‌ها',
            'permissions' => [
                'admin.access.roles' => 'نقش‌ها و دسترسی‌ها',
            ],
        ],

        'developer' => [
            'label' => 'برنامه‌نویس',
            'permissions' => [
                'admin.developer.settings' => 'تنظیمات برنامه‌نویس',
            ],
        ],
    ],
];
