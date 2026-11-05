<?php

/**
 * The DEMO seller ecosystem (v0.17.14) — 9 realistic Persian sellers/shops
 * (the digital flagship «demo_store» + 8 specialty shops added in this
 * version) consumed by DemoSellersSeeder / DemoProductSeeder /
 * DemoShopSetupSeeder / demo:remove.
 *
 * Each shop OWNS whole level-1 category roots: every demo product of a
 * root (flagship + generated) is created inside the owning shop, so the
 * catalog of a storefront stays thematically coherent — like a real
 * marketplace of boutiques around one big store.
 *
 * Keys of every row:
 *   phone      → normalized seller phone (E.164 without +) — the stable
 *                idempotency key AND the demo:remove handle
 *   handle     → seller.instagram_username + shop mirror (storefront is
 *                served at «دامنه/shop/{handle}»)
 *   full_name  → owner name shown in the admin sellers table
 *   shop_name  → storefront brand
 *   shop_phone → contact phone shown on the storefront
 *   address    → origin address (all demo shops ship from Tehran, the
 *                carriers seeder uses Tehran as origin)
 *   about      → storefront «دربارهٔ ما» (mentions demo:remove on purpose)
 *   logo       → file inside data/demo/logos (copied to «shops/demo-…»)
 *   logo_target→ public-disk path of the logo (demo_store keeps its
 *                legacy «shops/demo-logo.jpg» so re-seeding never orphans it)
 *   roots      → level-1 category names owned by this shop (must match
 *                the «root» values of data/demo-products.php)
 */
return [
    [
        'phone' => '989120000000',
        'handle' => 'demo_store',
        'full_name' => 'فروشگاه نمونه اینستاشاپ',
        'shop_name' => 'دیجی‌مارکت اینستاشاپ',
        'shop_phone' => '02191000000',
        'address' => 'تهران، خیابان ولیعصر، مرکز کامپیوتر پیروزی، پلاک ۱۰',
        'about' => 'فروشگاه نمونهٔ پلتفرم اینستاشاپ — موبایل، لپ‌تاپ و گجت‌های دیجیتال با قیمت رقابتی و ارسال سریع به سراسر کشور. این فروشگاه و محصولاتش با سیدر نمونه ساخته شده‌اند و می‌توانید هر لحظه با دستور demo:remove حذفشان کنید.',
        'logo' => 'shop-logo.jpg',
        'logo_target' => 'shops/demo-logo.jpg',
        'roots' => ['کالای دیجیتال'],
    ],
    [
        'phone' => '989120000001',
        'handle' => 'demo_mehrjaan',
        'full_name' => 'مریم رستمی',
        'shop_name' => 'خانه و آشپزخانه مهرجان',
        'shop_phone' => '02191000001',
        'address' => 'تهران، خیابان ولیعصر، بازار لوازم خانه، پلاک ۱۴',
        'about' => 'خانه و آشپزخانه مهرجان؛ از سرویس قابلمه گرانیتی تا لوازم برقی آشپزخانه، هر آنچه خانه‌تان را گرم‌تر می‌کند با ضمانت اصالت کالا و ارسال سریع. این فروشگاه همراه با محصولاتش با سیدر نمونهٔ اینستاشاپ ساخته شده و با دستور demo:remove قابل حذف است.',
        'logo' => 'demo_mehrjaan.jpg',
        'logo_target' => 'shops/demo-logo-demo_mehrjaan.jpg',
        'roots' => ['خانه و آشپزخانه', 'لوازم خانگی برقی'],
    ],
    [
        'phone' => '989120000002',
        'handle' => 'demo_aysan',
        'full_name' => 'آیسان کریمی',
        'shop_name' => 'بوتیک مد آیسان',
        'shop_phone' => '02191000002',
        'address' => 'تهران، بازار بزرگ، سرای امیر، طبقهٔ دوم، پلاک ۲۲',
        'about' => 'بوتیک مد آیسان؛ پوشاک، کفش و زیورآلات برگزیده برای استایل روزانه و مهمانی — دوخت تمیز، جنس باکیفیت و رنگ‌بندی متنوع. فروشگاه نمونهٔ دموی اینستاشاپ؛ حذف کامل با دستور demo:remove.',
        'logo' => 'demo_aysan.jpg',
        'logo_target' => 'shops/demo-logo-demo_aysan.jpg',
        'roots' => ['مد و پوشاک', 'طلا و جواهرات'],
    ],
    [
        'phone' => '989120000003',
        'handle' => 'demo_rozeno',
        'full_name' => 'نگار احمدی',
        'shop_name' => 'آرایشی و بهداشتی رزنو',
        'shop_phone' => '02191000003',
        'address' => 'تهران، خیابان ولیعصر، پاساژ نگین، طبقهٔ اول، پلاک ۷',
        'about' => 'رزنو؛ مرجع آرایشی و بهداشتی با کالاهای اصل، تاریخ مصرف تازه و مشاورهٔ خرید پاسخ‌گو در دایرکت. فروشگاه نمونهٔ سیدر اینستاشاپ — حذف با demo:remove.',
        'logo' => 'demo_rozeno.jpg',
        'logo_target' => 'shops/demo-logo-demo_rozeno.jpg',
        'roots' => ['آرایشی و بهداشتی'],
    ],
    [
        'phone' => '989120000004',
        'handle' => 'demo_asa',
        'full_name' => 'امیر رضایی',
        'shop_name' => 'سلامت و پزشکی آسا',
        'shop_phone' => '02191000004',
        'address' => 'تهران، خیابان انقلاب، پاساژ تجهیزات پزشکی نور، پلاک ۳۱',
        'about' => 'آسا؛ تجهیزات پزشکی و مراقبت سلامت خانگی با مجوز رسمی — از فشارسنج و تب‌سنج دیجیتال تا ترازوی هوشمند، با گارانتی و پشتیبانی واقعی. فروشگاه نمونهٔ دموی اینستاشاپ.',
        'logo' => 'demo_asa.jpg',
        'logo_target' => 'shops/demo-logo-demo_asa.jpg',
        'roots' => ['سلامت و پزشکی'],
    ],
    [
        'phone' => '989120000005',
        'handle' => 'demo_sabalan',
        'full_name' => 'حسین قنبری',
        'shop_name' => 'سوپرمارکت سبلان',
        'shop_phone' => '02191000005',
        'address' => 'تهران، خیابان مولوی، پاساژ خشکبار، پلاک ۱۸',
        'about' => 'سوپرمارکت سبلان؛ خشکبار، عسل طبیعی و چای تازه مستقیم از تولیدکننده تا در خانهٔ شما — بسته‌بندی بهداشتی، تاریخ تولید روز و ارسال سراسری. فروشگاه نمونهٔ سیدر اینستاشاپ.',
        'logo' => 'demo_sabalan.jpg',
        'logo_target' => 'shops/demo-logo-demo_sabalan.jpg',
        'roots' => ['مواد غذایی و سوپر مارکت'],
    ],
    [
        'phone' => '989120000006',
        'handle' => 'demo_raahvar',
        'full_name' => 'مهدی صادقی',
        'shop_name' => 'ابزار و خودرو راهوار',
        'shop_phone' => '02191000006',
        'address' => 'تهران، خیابان خیام، بازار ابزارآلات، پلاک ۴۵',
        'about' => 'راهوار؛ ابزارآلات حرفه‌ای و لوازم خودرو با برندهای معتبر جهانی — مشاورهٔ فنی قبل از خرید و گارانتی تعویض کالا. فروشگاه نمونهٔ دموی اینستاشاپ؛ حذف با demo:remove.',
        'logo' => 'demo_raahvar.jpg',
        'logo_target' => 'shops/demo-logo-demo_raahvar.jpg',
        'roots' => ['ابزارآلات و تجهیزات', 'خودرو و موتورسیکلت'],
    ],
    [
        'phone' => '989120000007',
        'handle' => 'demo_ghesseh',
        'full_name' => 'سارا موسوی',
        'shop_name' => 'کتاب و فرهنگ قصه',
        'shop_phone' => '02191000007',
        'address' => 'تهران، خیابان انقلاب، نشریهٔ کتاب، پلاک ۹',
        'about' => 'کتاب و فرهنگ قصه؛ کتاب‌های چاپ تازه، لوازم نقاشی و صنایع دستی ایرانی برای عاشقان فرهنگ و هنر. فروشگاه نمونهٔ سیدر اینستاشاپ — demo:remove.',
        'logo' => 'demo_ghesseh.jpg',
        'logo_target' => 'shops/demo-logo-demo_ghesseh.jpg',
        'roots' => ['کتاب', 'فرهنگی هنری'],
    ],
    [
        'phone' => '989120000008',
        'handle' => 'demo_shaadman',
        'full_name' => 'زهرا حسینی',
        'shop_name' => 'دنیای کودک و سفر شادمان',
        'shop_phone' => '02191000008',
        'address' => 'تهران، سعادت‌آباد، بلوار دریا، مرکز خرید کودک، پلاک ۱۲',
        'about' => 'شادمان؛ دنیای کودک، اسباب‌بازی و تجهیزات سفر و ورزش — از بازی‌های ایمن و بدون سرب تا چادرهای مسافرتی مقاوم. فروشگاه نمونهٔ دموی اینستاشاپ.',
        'logo' => 'demo_shaadman.jpg',
        'logo_target' => 'shops/demo-logo-demo_shaadman.jpg',
        'roots' => ['کودک و نوزاد و اسباب بازی', 'ورزش و سفر'],
    ],
];
