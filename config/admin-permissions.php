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
| NOTE: Developer pages (admin.developer.*) are deliberately NOT
| registered here — they are exclusive to the super admin (سازنده) and
| can never be granted through roles.
|
*/

return [

    'groups' => [

        'overview' => [
            'label' => 'نمای کلی',
            'permissions' => [
                'admin.dashboard' => 'داشبورد',
                'admin.activity' => 'فعالیت‌های اخیر (گزارش کامل رویدادها)',
                'admin.notifications' => 'نوتیفیکیشن‌ها',
                'admin.profile' => 'پروفایل مدیر (ویرایش اطلاعات و رمز عبور)',
            ],
        ],

        'catalog' => [
            'label' => 'مدیریت فروشگاه و سفارش‌ها',
            'permissions' => [
                'admin.orders' => 'سفارش‌ها',
                'admin.orders.show' => 'جزئیات سفارش',
                'admin.shops' => 'فروشگاه‌ها',
                'admin.categories' => 'دسته‌بندی‌های سراسری (درخت دسته‌ها)',
                'admin.products' => 'محصولات',
                'admin.discounts' => 'کدهای تخفیف',
                'admin.stories' => 'استوری‌ها (تأیید و مدیریت استوری فروشگاه‌ها)',
                'admin.banners' => 'بنرها و اسلایدر صفحهٔ اصلی',
                'admin.income' => 'درآمد و کمیسیون‌ها',
                'admin.finance' => 'گزارش مالی',
                'admin.settlements' => 'تسویه‌ها',
                'admin.withdrawals' => 'مدیریت برداشت‌ها',
                'admin.locations' => 'استان‌ها و شهرها',
            ],
        ],

        'users' => [
            'label' => 'کاربران',
            'permissions' => [
                'admin.users.sellers' => 'فروشندگان',
                'admin.users.customers' => 'مشتریان',
            ],
        ],

        'support' => [
            'label' => 'پشتیبانی',
            'permissions' => [
                'admin.tickets' => 'تیکت پشتیبانی (لیست تیکت‌ها)',
                'admin.tickets.show' => 'جزئیات و پاسخ تیکت',
            ],
        ],

        'system' => [
            'label' => 'سیستم',
            'permissions' => [
                'admin.backups' => 'پشتیبان‌گیری و بازگردانی (صفحهٔ مدیریت پشتیبان‌ها)',
                'admin.backups.download' => 'دانلود فایل پشتیبان',
                'admin.backups.upload' => 'بارگذاری فایل پشتیبان (بازگردانی)',
            ],
        ],

        // The settings HUB is one sidebar page; each section inside it is
        // its own permission so roles can grant only some sections.
        'settings' => [
            'label' => 'تنظیمات سیستم',
            'permissions' => [
                'admin.settings' => 'مرکز تنظیمات (دسترسی به صفحه تنظیمات)',
                'admin.settings.general' => 'تنظیمات عمومی (عنوان، لوگو، فاوآیکون)',
                'admin.settings.appearance' => 'ظاهر و رنگ‌بندی',
                'admin.settings.sms' => 'پیامک و پرووایدر',
                'admin.settings.sms.templates' => 'قالب‌های پیامک',
                'admin.settings.payment' => 'درگاه پرداخت',
                'admin.settings.shops' => 'تنظیمات فروشگاه‌ها (تایید خودکار، کمیسیون)',
                'admin.settings.stories' => 'تنظیمات استوری (سقف استوری فعال، حالت تایید)',
                'admin.settings.notifications' => 'نوتیفیکیشن‌ها (پوش و رویدادها)',
                'admin.settings.backups' => 'پشتیبان‌گیری خودکار + ایمیل و SMTP',
            ],
        ],

        'managers' => [
            'label' => 'مدیران و نقش‌ها',
            'permissions' => [
                'admin.admins' => 'لیست مدیران',
                'admin.access.roles' => 'نقش‌ها و دسترسی‌ها',
            ],
        ],
    ],
];
