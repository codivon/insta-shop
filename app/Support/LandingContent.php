<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Landing page (») content + layout registry.
 *
 * EVERY visible piece of the intro page (هدر → بخش‌ها → فوتر) now comes
 * from here instead of being hardcoded in the blade, so the admin can
 * edit texts/lists AND reorder / hide whole sections from the settings
 * panel (تنظیمات ← صفحهٔ اصلی).
 *
 * Storage (all JSON, via the cached Setting model — group «landing»):
 *   landing.layout                  => ['order' => [key,…], 'enabled' => [key => bool]]
 *   landing.content.{section}       => one merged-over-defaults content array
 *
 * Merge rule: stored values REPLACE the matching top-level keys of the
 * defaults (lists are replaced wholesale, never concatenated) so removing
 * an item in the panel really removes it on the page.
 */
class LandingContent
{
    /** Cache-busting key used by tests/CI only. */
    public const CACHE_GROUP = 'landing';

    /**
     * Section registry — the movable main-page sections in their DEFAULT
     * order. «footer» is deliberately NOT here: it is always the last
     * element of the page (sticky-footer contract) but fully editable.
     *
     * anchor  => optional in-page anchor; anchor sections surface in the
     *            header/footer navigation (auto-hidden when disabled).
     */
    public static function sections(): array
    {
        return [
            'hero' => [
                'label' => 'هدر و معرفی (Hero)',
                'icon' => 'sparkles',
                'description' => 'بج، تیتر، توضیح، دکمه‌ها و تیک‌های اعتماد',
            ],
            'stats' => [
                'label' => 'نوار آمار',
                'icon' => 'chart',
                'description' => 'عدد + برچسب (درگاه‌ها، اپ‌ها و …)',
            ],
            'infra' => [
                'label' => 'زیرساخت‌های متصل (مارکی)',
                'icon' => 'zap',
                'description' => 'نوار متحرک درگاه‌ها و سرویس‌ها',
            ],
            'features' => [
                'label' => 'امکانات',
                'icon' => 'layout-grid',
                'description' => 'کارت‌های بنتو + فهرست جزئیات',
                'anchor' => 'features',
            ],
            'panels' => [
                'label' => 'پنل‌ها',
                'icon' => 'layers',
                'description' => 'کارت‌های مدیریت / فروشنده / مشتری',
                'anchor' => 'panels',
            ],
            'how' => [
                'label' => 'شروع فروش (۴ قدم)',
                'icon' => 'rocket',
                'description' => 'گام‌های راه‌اندازی فروشگاه',
                'anchor' => 'how',
            ],
            'faq' => [
                'label' => 'پرسش‌های پرتکرار',
                'icon' => 'info',
                'description' => 'سوال و جواب‌های آکاردئونی',
                'anchor' => 'faq',
            ],
            'cta' => [
                'label' => 'کارت دعوت پایانی (CTA)',
                'icon' => 'flame',
                'description' => 'کارت گرادیانی آخر صفحه',
            ],
        ];
    }

    /** Ordered + filtered section keys for rendering. */
    public static function orderedEnabled(): array
    {
        $layout = self::layout();
        $defaults = array_keys(self::sections());

        $order = array_values(array_filter(
            $layout['order'],
            fn ($key) => in_array($key, $defaults, true),
        ));

        // Sections registered later (e.g. after an update) append at the end.
        foreach ($defaults as $key) {
            if (! in_array($key, $order, true)) {
                $order[] = $key;
            }
        }

        return array_values(array_filter(
            $order,
            fn ($key) => ($layout['enabled'][$key] ?? true) === true,
        ));
    }

    /** Current layout (order + enabled map) with defaults applied. */
    public static function layout(): array
    {
        $stored = Setting::get('landing.layout');

        $defaults = array_keys(self::sections());

        $order = [];
        $enabled = [];

        if (is_array($stored)) {
            $order = is_array($stored['order'] ?? null)
                ? array_values(array_filter($stored['order'], fn ($key) => in_array($key, $defaults, true)))
                : [];
            $enabled = is_array($stored['enabled'] ?? null) ? $stored['enabled'] : [];
        }

        // Sections registered later (e.g. after an update) append at the end.
        foreach ($defaults as $key) {
            if (! in_array($key, $order, true)) {
                $order[] = $key;
            }
        }

        // Default every section to enabled unless explicitly false.
        $enabledMap = [];
        foreach ($defaults as $key) {
            $enabledMap[$key] = ($enabled[$key] ?? true) === true;
        }

        return ['order' => $order, 'enabled' => $enabledMap];
    }

    /**
     * Content of one section: stored JSON merged over the defaults
     * (top-level replace — lists replaced wholesale).
     */
    public static function section(string $key): array
    {
        $defaults = self::defaults()[$key] ?? [];
        $stored = Setting::get("landing.content.{$key}");

        if (! is_array($stored)) {
            return $defaults;
        }

        return self::merge($defaults, $stored);
    }

    /** Header/footer navigation links derived from the anchor sections. */
    public static function navLinks(): array
    {
        $links = [];

        foreach (self::orderedEnabled() as $key) {
            $meta = self::sections()[$key];

            if (! empty($meta['anchor'])) {
                $links[] = ['href' => '#'.$meta['anchor'], 'label' => $meta['label']];
            }
        }

        return $links;
    }

    /** Deep merge: scalar + list keys replaced, nested maps merged. */
    private static function merge(array $defaults, array $stored): array
    {
        foreach ($stored as $key => $value) {
            if (is_array($value) && is_array($defaults[$key] ?? null)
                && ! array_is_list($value) && ! array_is_list($defaults[$key])) {
                $defaults[$key] = self::merge($defaults[$key], $value);
            } else {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

    /**
     * Current Jalali year in Persian digits — used by the footer
     * copyright line placeholder «{year}».
     */
    public static function jYear(): string
    {
        $gy = (int) date('Y');
        $gm = (int) date('n');
        $gd = (int) date('j');

        $gDaysInMonths = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $jy = 0;
        if ($gy > 1600) {
            $jy = 979;
            $gy -= 1600;
        } else {
            $gy -= 621;
        }

        $gy2 = $gm > 2 ? $gy + 1 : $gy;
        $days = 365 * $gy
            + intdiv($gy2 + 3, 4)
            - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400)
            - 80 + $gd + $gDaysInMonths[$gm - 1];

        $jy += 33 * intdiv($days, 12053);
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        return strtr((string) $jy, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    }

    /**
     * Default content — mirrors the previous hardcoded landing page
     * EXACTLY, so a fresh install renders pixel-identical.
     */
    public static function defaults(): array
    {
        return [
            'hero' => [
                'badge' => 'پلتفرم فروش اینستاگرامی، همه‌چیز آماده',
                'title_pre' => 'پیج اینستاگرامت را به یک',
                'title_highlight' => 'فروشگاه آنلاین',
                'title_post' => 'تبدیل کن',
                'lead' => 'ثبت‌نام فقط با شماره موبایل، مدیریت محصول از گوشی، پرداخت امن ایرانی، کیف پول و تسویه شفاف، ارسال با پست پیشتاز و انتخاب روز تحویل — همه‌چیز آماده است تا فقط به فروش فکر کنی.',
                'cta1_label' => 'شروع فروشندگی',
                'cta1_href' => '/seller',
                'cta2_label' => 'ورود به فروشگاه',
                'cta2_href' => '/shop',
                'ticks' => [
                    'ثبت‌نام با کد پیامکی',
                    'خرید مهمان بدون ثبت‌نام',
                    'نصب اپ روی گوشی',
                ],
                'chip1_title' => 'پرداخت موفق',
                'chip1_sub' => '۲ دقیقه پیش',
                'chip2_title' => 'سفارش جدید ثبت شد',
                'chip2_sub' => 'همین حالا',
                'chip3_title' => 'قابل نصب روی گوشی',
            ],

            'stats' => [
                'items' => [
                    ['n' => '۵', 'l' => 'درگاه پرداخت ایرانی'],
                    ['n' => '۵', 'l' => 'پیامک‌رسان آماده اتصال'],
                    ['n' => '۱۱', 'l' => 'پالت رنگی قابل انتخاب'],
                    ['n' => '۲', 'l' => 'اپ مستقل قابل نصب'],
                ],
            ],

            'infra' => [
                'title' => 'زیرساخت‌های آماده اتصال — از پنل مدیریت، در چند دقیقه',
                'speed' => 32,
                'items' => [
                    'زرین‌پال', 'زیبال', 'سداد (ملی)', 'به‌پرداخت (ملت)', 'پارسیان و ۸ درگاه دیگر',
                    'کاوه‌نگار', 'فراز اس‌ام‌اس', 'ملی‌پیامک', 'پست پیشتاز', 'تیپاکس',
                    'پرداخت امن', 'نصب PWA', 'اعلان پوش',
                ],
            ],

            'features' => [
                'eyebrow' => 'امکانات',
                'title' => 'هر چیزی که برای فروش لازم داری، همین حالا آماده است',
                'lead' => 'نه افزونه، نه نصب اضافه — همهٔ امکانات زیر همین حالا روی پلتفرم فعال‌اند و از پنل مدیریت قابل تنظیم‌اند.',
                'small_title' => 'و جزئیاتی که تجربه را کامل می‌کنند',
                'small' => [
                    ['icon' => 'megaphone', 't' => 'پیامک چندپروایدره'],
                    ['icon' => 'bell', 't' => 'اعلان پوش روی گوشی'],
                    ['icon' => 'headset', 't' => 'پشتیبانی تیکتی'],
                    ['icon' => 'chart', 't' => 'گزارش فروش و بازدید'],
                    ['icon' => 'filter', 't' => 'دسته‌بندی، ویژگی و فیلتر'],
                    ['icon' => 'heart', 't' => 'علاقه‌مندی و سبد خرید'],
                    ['icon' => 'palette', 't' => 'تم روشن/تیره و ۱۱ پالت'],
                    ['icon' => 'image-plus', 't' => 'آپلود چندتصویری محصول'],
                    ['icon' => 'database', 't' => 'پشتیبان‌گیری از دیتابیس'],
                    ['icon' => 'map-pin', 't' => 'مدیریت استان و شهر'],
                    ['icon' => 'lock', 't' => 'نقش‌ها و سطوح دسترسی'],
                    ['icon' => 'file-text', 't' => 'ثبت فعالیت کاربران'],
                ],
            ],

            'panels' => [
                'eyebrow' => 'سه پنل',
                'title' => 'یک اکوسیستم کامل برای همه',
                'lead' => 'هر پنل برای کاربر خودش طراحی شده و با نشست کاملاً مستقل کار می‌کند.',
                'cards' => [
                    [
                        'icon' => 'shield',
                        'title' => 'پنل مدیریت',
                        'badge' => '',
                        'link_label' => 'ورود مدیر',
                        'link_href' => '/admin',
                        'items' => [
                            'داشبورد آماری و مدیریت کاربران',
                            'تنظیم پیامک، درگاه و رنگ‌بندی',
                            'تسویه‌ها، پشتیبان‌گیری و دسترسی‌ها',
                        ],
                    ],
                    [
                        'icon' => 'bag',
                        'title' => 'اپ فروشنده',
                        'badge' => 'محبوب‌ترین',
                        'link_label' => 'ورود فروشنده',
                        'link_href' => '/seller',
                        'items' => [
                            'محصول، دسته‌بندی و ویژگی از گوشی',
                            'سفارش‌ها، کیف پول و کارت بانکی',
                            'گزارش فروش و بازدید محصولات',
                        ],
                    ],
                    [
                        'icon' => 'cart',
                        'title' => 'اپ مشتری',
                        'badge' => '',
                        'link_label' => 'ورود مشتری',
                        'link_href' => '/shop',
                        'items' => [
                            'خرید مهمان، بدون ثبت‌نام اجباری',
                            'انتخاب روز تحویل و پرداخت امن',
                            'پیگیری سفارش تا لحظه تحویل',
                        ],
                    ],
                ],
            ],

            'how' => [
                'eyebrow' => 'شروع فروش',
                'title' => 'در ۴ قدم بفروش',
                'lead' => 'اولین محصولت را کمتر از ۵ دقیقه منتشر کن.',
                'steps' => [
                    ['icon' => 'user', 'title' => 'ثبت‌نام فروشنده', 'desc' => 'شماره موبایل و شناسه اینستاگرام؛ تأیید با کد پیامکی.'],
                    ['icon' => 'package', 'title' => 'ساخت فروشگاه', 'desc' => 'دسته‌بندی‌ها و محصولات را با چند عکس از گوشی بساز.'],
                    ['icon' => 'external-link', 'title' => 'اشتراک‌گذاری', 'desc' => 'لینک فروشگاه را در بیو اینستاگرامت بگذار.'],
                    ['icon' => 'coins', 'title' => 'فروش و تسویه', 'desc' => 'سفارش ثبت می‌شود و مبلغ به کارت تو واریز می‌شود.'],
                ],
            ],

            'faq' => [
                'eyebrow' => 'پرسش‌های پرتکرار',
                'title' => 'سوالی که ممکن است داشته باشی',
                'items' => [
                    ['q' => 'برای شروع فروش به دانش فنی نیاز دارم؟', 'a' => 'نه. همه‌چیز از پنل گوشی انجام می‌شود: ساخت فروشگاه، افزودن محصول با عکس، تعیین قیمت و موجودی. هیچ نصب یا کدنویسی‌ای در کار نیست.'],
                    ['q' => 'پول فروش‌ها چطور به دستم می‌رسد؟', 'a' => 'پس از پرداخت مشتری، مبلغ به کیف پول فروشگاهت اضافه می‌شود و از پنل، درخواست برداشت به کارت بانکی خودت را ثبت می‌کنی؛ تاریخچهٔ کامل هم در کیف پول دیده می‌شود.'],
                    ['q' => 'اپ‌ها چطور روی گوشی نصب می‌شوند؟', 'a' => 'اپ فروشنده و اپ مشتری هر دو از مرورگر و بدون استور نصب می‌شوند (PWA). فقط توجه کن که چون آدرس سرویس در ایران فیلتر است، برای نصب باید فیلترشکن روشن باشد؛ بعد از نصب، اپ مستقل روی صفحهٔ گوشی می‌نشیند.'],
                    ['q' => 'مشتری بدون ثبت‌نام می‌تواند خرید کند؟', 'a' => 'بله؛ مرور محصولات، سبد خرید و حتی ادامه تا پیش از پرداخت برای مهمان باز است. فقط برای ثبت نهایی سفارش، شماره موبایل برای پیگیری سفارش گرفته می‌شود.'],
                ],
            ],

            'cta' => [
                'badge' => 'شروع رایگان — بدون هزینهٔ راه‌اندازی',
                'title' => 'فروش اینستاگرامی‌ات را همین امروز جدی بگیر',
                'lead' => 'فروشگاهت را بساز، اولین محصول را منتشر کن و لینک را در بیو بگذار — بقیه‌اش با ما.',
                'btn1_label' => 'شروع فروشندگی',
                'btn1_href' => '/seller',
                'btn2_label' => 'دیدن فروشگاه',
                'btn2_href' => '/shop',
            ],

            'footer' => [
                'about' => 'پلتفرم فروشگاهی مبتنی بر اینستاگرام؛ از پیج تا فروشگاه آنلاین کامل، با اپ موبایلی فروشنده و مشتری، پرداخت امن ایرانی و تسویه شفاف.',
                'groups' => [
                    [
                        'title' => 'پنل‌ها',
                        'links' => [
                            ['label' => 'پنل مدیریت', 'href' => '/admin'],
                            ['label' => 'اپ فروشنده', 'href' => '/seller'],
                            ['label' => 'اپ مشتری', 'href' => '/shop'],
                        ],
                    ],
                    [
                        'title' => 'دربارهٔ محصول',
                        'links' => [
                            ['label' => 'امکانات', 'href' => '#features'],
                            ['label' => 'پنل‌ها', 'href' => '#panels'],
                            ['label' => 'شروع فروش', 'href' => '#how'],
                            ['label' => 'پرسش‌های پرتکرار', 'href' => '#faq'],
                        ],
                    ],
                ],
                'copyright' => '© {year} {app} — تمام حقوق محفوظ است.',
                'credit' => 'ساخته‌شده برای فروشندگان اینستاگرام ایران',
            ],
        ];
    }
}
