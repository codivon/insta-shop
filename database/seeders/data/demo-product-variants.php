<?php

/**
 * Hand-written variation specs for the 43 flagship demo products
 * (v0.17.12) — consumed by DemoProductSeeder::seedVariants().
 *
 * Shape: slug => [ variant, ... ] where each variant is:
 *   picks    → [attribute name => value name]  (names MUST exist on the
 *              demo shop: رنگ/سایز/گارانتی defaults + the extra fields the
 *              seeder registers — حافظه داخلی/حجم/وزن/جلد/تعداد قطعه/قطر/طعم/سال ضرب)
 *   price    → Toman (the FIRST row equals the product's base price, so
 *              syncAggregates() keeps the storefront card price stable;
 *              larger/better combos sit slightly above)
 *   stock    → per-variant warehouse count (product stock = Σ variants)
 *   discount → optional percent, expires a few days out (flash flavor)
 *
 * Every dimension is product-specific: تیتانیوم‌ها for phones، سایز کفش
 * for sneakers، حجم برای عطر، جلد برای کتاب، وزن برای خشکبار، طعم برای
 * پروتئین و سال ضرب برای سکه — exactly like Basalam/Digikala listings.
 */
return [
    'apple-iphone-15-pro-max-256' => [
        ['picks' => ['رنگ' => 'تیتانیوم طبیعی', 'حافظه داخلی' => '۲۵۶ گیگابایت'], 'price' => 89500000, 'stock' => 2],
        ['picks' => ['رنگ' => 'تیتانیوم طبیعی', 'حافظه داخلی' => '۵۱۲ گیگابایت'], 'price' => 104500000, 'stock' => 1],
        ['picks' => ['رنگ' => 'تیتانیوم آبی', 'حافظه داخلی' => '۲۵۶ گیگابایت'], 'price' => 89900000, 'stock' => 1],
        ['picks' => ['رنگ' => 'تیتانیوم مشکی', 'حافظه داخلی' => '۲۵۶ گیگابایت'], 'price' => 91200000, 'stock' => 1, 'discount' => 3],
    ],

    'samsung-galaxy-s24-ultra-512' => [
        ['picks' => ['رنگ' => 'تیتانیوم خاکستری', 'حافظه داخلی' => '۵۱۲ گیگابایت'], 'price' => 72800000, 'stock' => 3],
        ['picks' => ['رنگ' => 'تیتانیوم خاکستری', 'حافظه داخلی' => '۱ ترابایت'], 'price' => 87900000, 'stock' => 1],
        ['picks' => ['رنگ' => 'تیتانیوم بنفش', 'حافظه داخلی' => '۵۱۲ گیگابایت'], 'price' => 73200000, 'stock' => 3, 'discount' => 5],
        ['picks' => ['رنگ' => 'تیتانیوم مشکی', 'حافظه داخلی' => '۵۱۲ گیگابایت'], 'price' => 72800000, 'stock' => 2],
    ],

    'asus-vivobook-15-x1504' => [
        ['picks' => ['رنگ' => 'نقره‌ای'], 'price' => 38400000, 'stock' => 2],
        ['picks' => ['رنگ' => 'مشکی'], 'price' => 38400000, 'stock' => 1, 'discount' => 4],
        ['picks' => ['رنگ' => 'طوسی'], 'price' => 38900000, 'stock' => 1],
    ],

    'apple-airpods-pro-2-usb-c' => [
        ['picks' => ['رنگ' => 'سفید', 'گارانتی' => '۱۲ ماه گارانتی'], 'price' => 11300000, 'stock' => 8],
        ['picks' => ['رنگ' => 'سفید', 'گارانتی' => '۱۸ ماه گارانتی'], 'price' => 11850000, 'stock' => 5],
        ['picks' => ['رنگ' => 'سفید', 'گارانتی' => '۲۴ ماه گارانتی'], 'price' => 12400000, 'stock' => 2, 'discount' => 4],
    ],

    'granite-pot-set-9-artaa' => [
        ['picks' => ['رنگ' => 'کرم'], 'price' => 4850000, 'stock' => 4],
        ['picks' => ['رنگ' => 'سبز'], 'price' => 4950000, 'stock' => 4, 'discount' => 5],
        ['picks' => ['رنگ' => 'مشکی'], 'price' => 4850000, 'stock' => 4],
    ],

    'machine-rug-500-abrang-3x2' => [
        ['picks' => ['رنگ' => 'سرمه‌ای'], 'price' => 8900000, 'stock' => 2],
        ['picks' => ['رنگ' => 'کرم'], 'price' => 8900000, 'stock' => 2],
        ['picks' => ['رنگ' => 'قرمز'], 'price' => 9150000, 'stock' => 2, 'discount' => 3],
    ],

    'wooden-table-lamp-sayeh' => [
        ['picks' => ['رنگ' => 'چوبی'], 'price' => 1250000, 'stock' => 8],
        ['picks' => ['رنگ' => 'مسی'], 'price' => 1390000, 'stock' => 6, 'discount' => 5],
        ['picks' => ['رنگ' => 'مشکی'], 'price' => 1290000, 'stock' => 6],
    ],

    'philips-food-processor-hr7320' => [
        ['picks' => ['رنگ' => 'سفید', 'گارانتی' => '۱۸ ماه گارانتی'], 'price' => 6450000, 'stock' => 3],
        ['picks' => ['رنگ' => 'استیل', 'گارانتی' => '۱۸ ماه گارانتی'], 'price' => 6850000, 'stock' => 3],
        ['picks' => ['رنگ' => 'سفید', 'گارانتی' => '۲۴ ماه گارانتی'], 'price' => 6950000, 'stock' => 2, 'discount' => 6],
    ],

    'samsung-smart-tv-55-cu7000' => [
        ['picks' => ['رنگ' => 'مشکی', 'گارانتی' => '۱۸ ماه گارانتی'], 'price' => 32900000, 'stock' => 2],
        ['picks' => ['رنگ' => 'مشکی', 'گارانتی' => '۲۴ ماه گارانتی'], 'price' => 34200000, 'stock' => 1],
    ],

    'emersun-twin-fridge-tc520' => [
        ['picks' => ['رنگ' => 'سفید', 'گارانتی' => '۱۸ ماه گارانتی'], 'price' => 28500000, 'stock' => 2],
        ['picks' => ['رنگ' => 'سفید', 'گارانتی' => '۲۴ ماه گارانتی'], 'price' => 29500000, 'stock' => 2, 'discount' => 4],
    ],

    'nike-air-zoom-sneakers' => [
        ['picks' => ['رنگ' => 'مشکی', 'سایز' => '۴۱'], 'price' => 3890000, 'stock' => 3],
        ['picks' => ['رنگ' => 'مشکی', 'سایز' => '۴۲'], 'price' => 3890000, 'stock' => 3],
        ['picks' => ['رنگ' => 'مشکی', 'سایز' => '۴۳'], 'price' => 3890000, 'stock' => 3],
        ['picks' => ['رنگ' => 'سفید', 'سایز' => '۴۱'], 'price' => 3990000, 'stock' => 4],
        ['picks' => ['رنگ' => 'سفید', 'سایز' => '۴۴'], 'price' => 3990000, 'stock' => 4, 'discount' => 10],
        ['picks' => ['رنگ' => 'سفید', 'سایز' => '۴۵'], 'price' => 3990000, 'stock' => 4],
    ],

    'linen-manteau-bahar' => [
        ['picks' => ['رنگ' => 'کرم', 'سایز' => 'M'], 'price' => 1680000, 'stock' => 6],
        ['picks' => ['رنگ' => 'کرم', 'سایز' => 'L'], 'price' => 1680000, 'stock' => 6],
        ['picks' => ['رنگ' => 'کرم', 'سایز' => 'XL'], 'price' => 1730000, 'stock' => 5],
        ['picks' => ['رنگ' => 'سرمه‌ای', 'سایز' => 'M'], 'price' => 1680000, 'stock' => 5],
        ['picks' => ['رنگ' => 'سرمه‌ای', 'سایز' => 'L'], 'price' => 1680000, 'stock' => 4],
        ['picks' => ['رنگ' => 'سرمه‌ای', 'سایز' => 'XXL'], 'price' => 1760000, 'stock' => 4, 'discount' => 7],
    ],

    'casio-mtp-1303-watch' => [
        ['picks' => ['رنگ' => 'نقره‌ای'], 'price' => 2450000, 'stock' => 5],
        ['picks' => ['رنگ' => 'طلایی'], 'price' => 2590000, 'stock' => 4],
        ['picks' => ['رنگ' => 'مشکی'], 'price' => 2450000, 'stock' => 5, 'discount' => 5],
    ],

    'maybelline-superstay-matte-ink' => [
        ['picks' => ['رنگ' => 'قرمز'], 'price' => 690000, 'stock' => 14],
        ['picks' => ['رنگ' => 'صورتی'], 'price' => 690000, 'stock' => 13],
        ['picks' => ['رنگ' => 'نارنجی'], 'price' => 690000, 'stock' => 13, 'discount' => 8],
    ],

    'dior-sauvage-edp-100' => [
        ['picks' => ['حجم' => '۱۰۰ میلی‌لیتر'], 'price' => 8900000, 'stock' => 6],
        ['picks' => ['حجم' => '۲۰۰ میلی‌لیتر'], 'price' => 12900000, 'stock' => 3, 'discount' => 6],
    ],

    'dermatica-sunscreen-spf50' => [
        ['picks' => ['حجم' => '۶۰ میلی‌لیتر'], 'price' => 520000, 'stock' => 25],
        ['picks' => ['حجم' => '۱۰۰ میلی‌لیتر'], 'price' => 790000, 'stock' => 25, 'discount' => 8],
    ],

    'akbari-pistachio-500g' => [
        ['picks' => ['وزن' => '۵۰۰ گرم'], 'price' => 1850000, 'stock' => 15],
        ['picks' => ['وزن' => '۹۰۰ گرم'], 'price' => 3250000, 'stock' => 10],
        ['picks' => ['وزن' => '۱ کیلوگرم'], 'price' => 3600000, 'stock' => 10, 'discount' => 5],
    ],

    'sabalan-natural-honey-1kg' => [
        ['picks' => ['وزن' => '۱ کیلوگرم'], 'price' => 980000, 'stock' => 8],
        ['picks' => ['وزن' => '۱.۸ کیلوگرم'], 'price' => 1750000, 'stock' => 6, 'discount' => 4],
    ],

    'lahijan-black-tea-500g' => [
        ['picks' => ['وزن' => '۵۰۰ گرم'], 'price' => 420000, 'stock' => 20],
        ['picks' => ['وزن' => '۹۰۰ گرم'], 'price' => 730000, 'stock' => 20, 'discount' => 6],
    ],

    'bosch-gsr-120-li-drill' => [
        ['picks' => ['رنگ' => 'آبی', 'گارانتی' => '۱۲ ماه گارانتی'], 'price' => 5890000, 'stock' => 3],
        ['picks' => ['رنگ' => 'آبی', 'گارانتی' => '۱۸ ماه گارانتی'], 'price' => 6250000, 'stock' => 2],
        ['picks' => ['رنگ' => 'مشکی', 'گارانتی' => '۱۲ ماه گارانتی'], 'price' => 5890000, 'stock' => 2, 'discount' => 8],
    ],

    'ronix-tool-kit-108' => [
        ['picks' => ['گارانتی' => '۶ ماه گارانتی'], 'price' => 3450000, 'stock' => 5],
        ['picks' => ['گارانتی' => '۱۲ ماه گارانتی'], 'price' => 3750000, 'stock' => 4, 'discount' => 5],
        ['picks' => ['گارانتی' => '۱۸ ماه گارانتی'], 'price' => 3980000, 'stock' => 2],
    ],

    'bosch-glm-40-laser-meter' => [
        ['picks' => ['گارانتی' => '۱۲ ماه گارانتی'], 'price' => 2980000, 'stock' => 5],
        ['picks' => ['گارانتی' => '۱۸ ماه گارانتی'], 'price' => 3190000, 'stock' => 2],
        ['picks' => ['گارانتی' => '۲۴ ماه گارانتی'], 'price' => 3350000, 'stock' => 2, 'discount' => 4],
    ],

    'copper-engraved-plate-eslimi' => [
        ['picks' => ['قطر' => '۳۰ سانتی‌متر'], 'price' => 2350000, 'stock' => 2],
        ['picks' => ['قطر' => '۳۵ سانتی‌متر'], 'price' => 3150000, 'stock' => 1],
        ['picks' => ['قطر' => '۴۰ سانتی‌متر'], 'price' => 4100000, 'stock' => 1],
    ],

    'kilometrico-pen-10pack' => [
        ['picks' => ['رنگ' => 'آبی'], 'price' => 185000, 'stock' => 40],
        ['picks' => ['رنگ' => 'مشکی'], 'price' => 185000, 'stock' => 40],
        ['picks' => ['رنگ' => 'قرمز'], 'price' => 185000, 'stock' => 40, 'discount' => 5],
    ],

    'vangogh-oil-color-24' => [
        ['picks' => ['تعداد قطعه' => '۲۴ عددی'], 'price' => 890000, 'stock' => 8],
        ['picks' => ['تعداد قطعه' => '۳۶ عددی'], 'price' => 1250000, 'stock' => 6],
        ['picks' => ['تعداد قطعه' => '۴۸ عددی'], 'price' => 1690000, 'stock' => 4, 'discount' => 7],
    ],

    'android-car-player-9inch' => [
        ['picks' => ['حافظه داخلی' => '۳۲ گیگابایت', 'گارانتی' => '۱۲ ماه گارانتی'], 'price' => 7800000, 'stock' => 2],
        ['picks' => ['حافظه داخلی' => '۶۴ گیگابایت', 'گارانتی' => '۱۲ ماه گارانتی'], 'price' => 8600000, 'stock' => 2],
        ['picks' => ['حافظه داخلی' => '۶۴ گیگابایت', 'گارانتی' => '۱۸ ماه گارانتی'], 'price' => 8850000, 'stock' => 2, 'discount' => 5],
    ],

    'behran-rokhsh-engine-oil-4l' => [
        ['picks' => ['گارانتی' => '۱۲ ماه گارانتی'], 'price' => 680000, 'stock' => 40],
        ['picks' => ['گارانتی' => '۱۸ ماه گارانتی'], 'price' => 720000, 'stock' => 25],
        ['picks' => ['گارانتی' => '۲۴ ماه گارانتی'], 'price' => 745000, 'stock' => 15, 'discount' => 5],
    ],

    'agv-motorcycle-helmet' => [
        ['picks' => ['رنگ' => 'مشکی', 'سایز' => 'M'], 'price' => 4650000, 'stock' => 2],
        ['picks' => ['رنگ' => 'مشکی', 'سایز' => 'L'], 'price' => 4650000, 'stock' => 2],
        ['picks' => ['رنگ' => 'مشکی', 'سایز' => 'XL'], 'price' => 4750000, 'stock' => 2],
        ['picks' => ['رنگ' => 'سفید', 'سایز' => 'L'], 'price' => 4750000, 'stock' => 1, 'discount' => 6],
        ['picks' => ['رنگ' => 'سفید', 'سایز' => 'XL'], 'price' => 4750000, 'stock' => 1],
    ],

    'ghodrat-e-adat-book' => [
        ['picks' => ['جلد' => 'شومیز'], 'price' => 285000, 'stock' => 25],
        ['picks' => ['جلد' => 'گالنگان'], 'price' => 395000, 'stock' => 10],
        ['picks' => ['جلد' => 'سخت'], 'price' => 450000, 'stock' => 10, 'discount' => 5],
    ],

    'soo-shoon-novel' => [
        ['picks' => ['جلد' => 'شومیز'], 'price' => 195000, 'stock' => 20],
        ['picks' => ['جلد' => 'سخت'], 'price' => 310000, 'stock' => 12, 'discount' => 8],
    ],

    'shahzade-koochooloo-book' => [
        ['picks' => ['جلد' => 'شومیز'], 'price' => 165000, 'stock' => 35],
        ['picks' => ['جلد' => 'سخت'], 'price' => 240000, 'stock' => 20],
    ],

    'whey-protein-2kg' => [
        ['picks' => ['طعم' => 'شکلاتی'], 'price' => 4850000, 'stock' => 4],
        ['picks' => ['طعم' => 'وانیلی'], 'price' => 4850000, 'stock' => 3],
        ['picks' => ['طعم' => 'توت‌فرنگی'], 'price' => 4850000, 'stock' => 3, 'discount' => 6],
    ],

    'smart-digital-scale' => [
        ['picks' => ['رنگ' => 'مشکی', 'گارانتی' => '۱۲ ماه گارانتی'], 'price' => 1290000, 'stock' => 8],
        ['picks' => ['رنگ' => 'سفید', 'گارانتی' => '۱۲ ماه گارانتی'], 'price' => 1290000, 'stock' => 8],
        ['picks' => ['رنگ' => 'مشکی', 'گارانتی' => '۲۴ ماه گارانتی'], 'price' => 1390000, 'stock' => 4, 'discount' => 4],
    ],

    'natural-castor-oil-100ml' => [
        ['picks' => ['حجم' => '۱۰۰ میلی‌لیتر'], 'price' => 95000, 'stock' => 30],
        ['picks' => ['حجم' => '۲۰۰ میلی‌لیتر'], 'price' => 170000, 'stock' => 30, 'discount' => 10],
    ],

    'lego-classic-480-bricks' => [
        ['picks' => ['تعداد قطعه' => '۴۸۰ عددی'], 'price' => 2890000, 'stock' => 6],
        ['picks' => ['تعداد قطعه' => '۹۰۰ عددی'], 'price' => 4500000, 'stock' => 3, 'discount' => 5],
    ],

    'baby-stroller-reversible' => [
        ['picks' => ['رنگ' => 'کرم'], 'price' => 8500000, 'stock' => 2],
        ['picks' => ['رنگ' => 'مشکی'], 'price' => 8650000, 'stock' => 2],
        ['picks' => ['رنگ' => 'طوسی'], 'price' => 8500000, 'stock' => 1, 'discount' => 6],
    ],

    'nature-jigsaw-puzzle-500' => [
        ['picks' => ['تعداد قطعه' => '۵۰۰ عددی'], 'price' => 320000, 'stock' => 15],
        ['picks' => ['تعداد قطعه' => '۱۰۰۰ عددی'], 'price' => 520000, 'stock' => 13],
    ],

    'dumbbell-set-home-gym' => [
        ['picks' => ['وزن' => '۲۰ کیلوگرم'], 'price' => 1850000, 'stock' => 6],
        ['picks' => ['وزن' => '۳۰ کیلوگرم'], 'price' => 2450000, 'stock' => 5],
        ['picks' => ['وزن' => '۴۰ کیلوگرم'], 'price' => 3150000, 'stock' => 3, 'discount' => 5],
    ],

    'abs-travel-suitcase-large' => [
        ['picks' => ['رنگ' => 'طوسی'], 'price' => 4350000, 'stock' => 4],
        ['picks' => ['رنگ' => 'مشکی'], 'price' => 4350000, 'stock' => 3],
        ['picks' => ['رنگ' => 'سرمه‌ای'], 'price' => 4550000, 'stock' => 2, 'discount' => 5],
    ],

    'family-camping-tent-4' => [
        ['picks' => ['رنگ' => 'سبز'], 'price' => 5900000, 'stock' => 3],
        ['picks' => ['رنگ' => 'آبی'], 'price' => 5900000, 'stock' => 2],
        ['picks' => ['رنگ' => 'کرم'], 'price' => 6200000, 'stock' => 2, 'discount' => 4],
    ],

    'gold-necklace-18k-women' => [
        ['picks' => ['رنگ' => 'طلایی'], 'price' => 45000000, 'stock' => 1],
        ['picks' => ['رنگ' => 'رزگلد'], 'price' => 46200000, 'stock' => 1],
    ],

    'bahar-azadi-full-coin' => [
        ['picks' => ['سال ضرب' => '۱۴۰۴'], 'price' => 68000000, 'stock' => 2],
        ['picks' => ['سال ضرب' => '۱۴۰۳'], 'price' => 68500000, 'stock' => 1],
    ],

    'gold-bar-1gram-999' => [
        ['picks' => ['وزن' => '۱ گرم'], 'price' => 8900000, 'stock' => 5],
        ['picks' => ['وزن' => '۲.۵ گرم'], 'price' => 21500000, 'stock' => 3],
        ['picks' => ['وزن' => '۵ گرم'], 'price' => 42800000, 'stock' => 2, 'discount' => 3],
    ],
];
