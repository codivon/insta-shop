<!DOCTYPE html>
<html lang="fa" dir="rtl" data-panel="landing">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="InstaShop — پلتفرم فروشگاهی مبتنی بر اینستاگرام؛ فروشگاه اینستاگرامی خود را در چند دقیقه راه‌اندازی کنید.">
    @php($ipAppName = \App\Support\Theme::resolve()['app_name'])
<title>{{ $ipAppName }} — فروشگاه اینستاگرامی</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.theme-vars')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">

    {{-- ═══════════════════ HEADER ═══════════════════ --}}
    <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/80 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/80">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="/" class="flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                    <x-icon name="store" class="size-5" />
                </span>
                <span class="text-lg font-extrabold tracking-tight">
                            @if (str_starts_with($ipAppName, 'Insta') && mb_strlen($ipAppName) > 5)
                                Insta<span class="text-primary-600 dark:text-primary-400">{{ mb_substr($ipAppName, 5) }}</span>
                            @else
                                <span class="text-primary-600 dark:text-primary-400">{{ $ipAppName }}</span>
                            @endif
                        </span>
            </a>

            <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 dark:text-slate-300 md:flex" aria-label="منوی اصلی">
                <a href="#features" class="transition hover:text-primary-600 dark:hover:text-primary-400">ویژگی‌ها</a>
                <a href="#panels" class="transition hover:text-primary-600 dark:hover:text-primary-400">پنل‌ها</a>
                <a href="#how" class="transition hover:text-primary-600 dark:hover:text-primary-400">نحوه کار</a>
            </nav>

            <div class="flex items-center gap-2">
                <x-theme-toggle />
                <a href="{{ route('shop.home') }}"
                   class="hidden rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-primary-600/30 transition hover:bg-primary-700 sm:inline-flex">
                    ورود مشتریان
                </a>
            </div>
        </div>
    </header>

    <main>
        {{-- ═══════════════════ HERO ═══════════════════ --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                <div class="absolute -top-32 right-1/4 size-[28rem] rounded-full bg-primary-400/20 blur-3xl dark:bg-primary-600/15"></div>
                <div class="absolute -bottom-40 left-1/4 size-[26rem] rounded-full bg-primary-600/10 blur-3xl dark:bg-primary-500/10"></div>
            </div>

            <div class="mx-auto grid max-w-6xl items-center gap-12 px-4 py-16 sm:px-6 md:py-24 lg:grid-cols-2">
                <div class="ip-fade-up text-center lg:text-right">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-primary-200 bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 dark:border-primary-800 dark:bg-primary-950 dark:text-primary-300">
                        <x-icon name="sparkles" class="size-3.5" />
                        جدیدترین راه فروش در اینستاگرام
                    </span>

                    <h1 class="mt-5 text-4xl font-extrabold leading-[1.25] tracking-tight sm:text-5xl md:text-[3.4rem]">
                        فروشگاه اینستاگرامی‌ات را
                        <span class="relative inline-block bg-gradient-to-l from-primary-500 to-primary-700 bg-clip-text text-transparent">
                            حرفه‌ای
                        </span>
                        بساز
                    </h1>

                    <p class="mx-auto mt-5 max-w-xl text-base leading-8 text-slate-600 dark:text-slate-300 lg:mx-0 md:text-lg md:leading-9">
                        InstaShop پلی است بین پیج اینستاگرام شما و یک فروشگاه آنلاین کامل؛
                        با ثبت‌نام سریع با شماره موبایل، پنل فروشنده موبایلی، پرداخت امن ایرانی
                        و مدیریت رنگ‌بندی و تنظیمات از پنل مدیریت.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                        <a href="{{ route('seller.home') }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-primary-600 px-6 py-3.5 text-sm font-bold text-white shadow-xl shadow-primary-600/30 transition hover:-translate-y-0.5 hover:bg-primary-700">
                            <x-icon name="bag" class="size-5" />
                            شروع فروشندگی
                        </a>
                        <a href="{{ route('shop.home') }}"
                           class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-6 py-3.5 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-primary-300 hover:text-primary-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-primary-700 dark:hover:text-primary-300">
                            <x-icon name="cart" class="size-5" />
                            ورود به فروشگاه
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-sm text-slate-500 dark:text-slate-400 lg:justify-start">
                        <span class="flex items-center gap-1.5"><x-icon name="check" class="size-4 text-primary-600" /> ثبت‌نام با OTP</span>
                        <span class="flex items-center gap-1.5"><x-icon name="check" class="size-4 text-primary-600" /> نصب روی گوشی (PWA)</span>
                        <span class="flex items-center gap-1.5"><x-icon name="check" class="size-4 text-primary-600" /> درگاه‌های ایرانی</span>
                    </div>
                </div>

                {{-- phone mockups --}}
                <div class="relative mx-auto hidden w-fit md:block" aria-hidden="true">
                    <div class="absolute -inset-8 -z-10 rounded-[3rem] bg-gradient-to-br from-primary-500/15 to-primary-700/5 blur-2xl"></div>

                    <div class="flex items-end gap-6">
                        <div class="w-44 rotate-6 rounded-[2rem] border-4 border-slate-900 bg-slate-900 p-1.5 shadow-2xl transition hover:rotate-3 dark:border-slate-700 dark:bg-slate-800">
                            <div class="overflow-hidden rounded-[1.6rem] bg-white dark:bg-slate-950">
                                <div class="bg-gradient-to-br from-primary-500 to-primary-700 px-3 pb-8 pt-4 text-white">
                                    <div class="flex items-center justify-between text-[9px] opacity-90">
                                        <span>InstaShop</span><span>۱۲:۳۰</span>
                                    </div>
                                    <div class="mt-3 text-[11px] font-bold">فروشگاه شما</div>
                                    <div class="text-[8px] opacity-80">@your_shop</div>
                                </div>
                                <div class="-mt-4 space-y-2 rounded-t-2xl bg-white p-3 dark:bg-slate-950">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="h-10 rounded-xl bg-primary-50 dark:bg-primary-950"></div>
                                        <div class="h-10 rounded-xl bg-slate-100 dark:bg-slate-900"></div>
                                    </div>
                                    <div class="h-14 rounded-xl bg-slate-100 dark:bg-slate-900"></div>
                                    <div class="h-14 rounded-xl bg-slate-100 dark:bg-slate-900"></div>
                                </div>
                            </div>
                        </div>

                        <div class="w-52 rounded-[2.2rem] border-4 border-slate-900 bg-slate-900 p-2 shadow-2xl dark:border-slate-700 dark:bg-slate-800">
                            <div class="overflow-hidden rounded-[1.8rem] bg-white dark:bg-slate-950">
                                <div class="flex items-center gap-2 border-b border-slate-100 p-3 dark:border-slate-800">
                                    <div class="size-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-600"></div>
                                    <div class="flex-1">
                                        <div class="h-2 w-16 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                                        <div class="mt-1.5 h-2 w-10 rounded-full bg-slate-100 dark:bg-slate-800"></div>
                                    </div>
                                    <x-icon name="search" class="size-3.5 text-slate-400" />
                                </div>
                                <div class="p-3">
                                    <div class="h-6 rounded-lg bg-slate-100 dark:bg-slate-900"></div>
                                    <div class="mt-3 flex gap-2">
                                        <div class="size-12 rounded-full bg-primary-100 dark:bg-primary-950"></div>
                                        <div class="size-12 rounded-full bg-slate-100 dark:bg-slate-900"></div>
                                        <div class="size-12 rounded-full bg-slate-100 dark:bg-slate-900"></div>
                                        <div class="size-12 rounded-full bg-slate-100 dark:bg-slate-900"></div>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        @foreach ([1, 2, 3, 4] as $i)
                                            <div class="rounded-xl border border-slate-100 p-1.5 dark:border-slate-800">
                                                <div class="h-12 rounded-lg bg-slate-100 dark:bg-slate-900"></div>
                                                <div class="mt-1.5 h-1.5 w-3/4 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                                                <div class="mt-1 h-1.5 w-1/2 rounded-full bg-primary-200 dark:bg-primary-900"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ═══════════════════ PANELS ═══════════════════ --}}
        <section id="panels" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="text-center">
                <h2 class="text-2xl font-extrabold sm:text-3xl">سه پنل، یک اکوسیستم</h2>
                <p class="mx-auto mt-3 max-w-lg text-sm leading-7 text-slate-500 dark:text-slate-400">
                    هر بخش سیستم، مخصوص کاربر خودش طراحی شده و با Session کاملاً مستقل کار می‌کند
                </p>
            </div>

            <div class="mt-10 grid gap-5 md:grid-cols-3">
                <a href="{{ route('admin.dashboard') }}" class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-l from-primary-500 to-primary-700 opacity-0 transition group-hover:opacity-100"></div>
                    <span class="grid size-12 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="shield" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-lg font-bold">پنل مدیریت</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                        کنترل کامل سیستم، تنظیمات پیامک و درگاه پرداخت، شخصی‌سازی رنگ‌بندی،
                        کاربران و گزارش‌ها — دسکتاپ با ریسپانسیو موبایل.
                    </p>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary-600 dark:text-primary-400">
                        ورود مدیر <x-icon name="arrow-left" class="size-4 transition group-hover:-translate-x-1" />
                    </span>
                </a>

                <a href="{{ route('seller.home') }}" class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-l from-primary-500 to-primary-700 opacity-0 transition group-hover:opacity-100"></div>
                    <span class="grid size-12 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="bag" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-lg font-bold">اپ فروشنده</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                        موبایل‌اپ اختصاصی؛ ورود با شماره موبایل و کد یکبار‌مصرف، مدیریت محصولات
                        با کارت و فیلتر، دسته‌بندی‌ها و دکمه شناور افزودن محصول.
                    </p>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary-600 dark:text-primary-400">
                        ورود فروشنده <x-icon name="arrow-left" class="size-4 transition group-hover:-translate-x-1" />
                    </span>
                </a>

                <a href="{{ route('shop.home') }}" class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-l from-primary-500 to-primary-700 opacity-0 transition group-hover:opacity-100"></div>
                    <span class="grid size-12 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="cart" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-lg font-bold">اپ مشتری</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                        تجربه خرید سریع موبایلی؛ فقط با شماره موبایل وارد شوید، خرید کنید و
                        سفارش‌تان را تا لحظه تحویل دنبال کنید. نصب‌شدنی روی گوشی.
                    </p>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary-600 dark:text-primary-400">
                        ورود مشتری <x-icon name="arrow-left" class="size-4 transition group-hover:-translate-x-1" />
                    </span>
                </a>
            </div>
        </section>

        {{-- ═══════════════════ FEATURES ═══════════════════ --}}
        <section id="features" class="border-y border-slate-200 bg-white py-16 dark:border-slate-800 dark:bg-slate-900/50">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="text-center">
                    <h2 class="text-2xl font-extrabold sm:text-3xl">هر آنچه یک فروشگاه مدرن لازم دارد</h2>
                </div>

                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        ['icon' => 'phone',      'title' => 'ورود با کد یکبارمصرف',  'desc' => 'بدون رمز و ایمیل؛ فقط شماره موبایل و کد پیامکی'],
                        ['icon' => 'instagram',  'title' => 'متصل به اینستاگرام',    'desc' => 'فروشنده با شناسه اینستاگرام ثبت‌نام می‌کند'],
                        ['icon' => 'credit-card','title' => 'پرداخت امن ایرانی',     'desc' => 'زرین‌پال، زیبال، آیدی‌پی، ملت، سپ و درگاه تست'],
                        ['icon' => 'megaphone',  'title' => 'پیامک چندپروایدره',     'desc' => 'کاوه‌نگار، فراز، ملی‌پیامک و ... با انتخاب از پنل'],
                        ['icon' => 'palette',    'title' => 'رنگ‌بندی شخصی‌سازی‌شده',  'desc' => 'تم روشن/تیره + پالت رنگ قابل تغییر از پنل مدیریت'],
                        ['icon' => 'smartphone', 'title' => 'نصب روی گوشی (PWA)',     'desc' => 'اپ مشتری و اپ فروشنده، مستقل و قابل نصب'],
                    ] as $f)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-5 transition hover:border-primary-200 hover:bg-primary-50/50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-800 dark:hover:bg-primary-950/40">
                            <span class="grid size-11 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                                <x-icon name="{{ $f['icon'] }}" class="size-5" />
                            </span>
                            <h3 class="mt-3.5 font-bold">{{ $f['title'] }}</h3>
                            <p class="mt-1.5 text-sm leading-7 text-slate-500 dark:text-slate-400">{{ $f['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══════════════════ HOW IT WORKS ═══════════════════ --}}
        <section id="how" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="text-center">
                <h2 class="text-2xl font-extrabold sm:text-3xl">در ۴ قدم بفروشید</h2>
            </div>

            <ol class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['n' => '۱', 'title' => 'ثبت‌نام فروشنده',  'desc' => 'شماره موبایل + شناسه اینستاگرام، تأیید با OTP'],
                    ['n' => '۲', 'title' => 'ساخت فروشگاه',     'desc' => 'دسته‌بندی‌ها و محصولات را با اپ موبایلی بسازید'],
                    ['n' => '۳', 'title' => 'اشتراک‌گذاری',      'desc' => 'لینک فروشگاه را در بیو اینستاگرام بگذارید'],
                    ['n' => '۴', 'title' => 'فروش و تسویه',      'desc' => 'سفارش‌ها آنلاین ثبت و مبلغ به حساب شما واریز می‌شود'],
                ] as $step)
                    <li class="relative rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        <span class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-extrabold text-white shadow-lg shadow-primary-600/25">{{ $step['n'] }}</span>
                        <h3 class="mt-3 font-bold">{{ $step['title'] }}</h3>
                        <p class="mt-1.5 text-sm leading-7 text-slate-500 dark:text-slate-400">{{ $step['desc'] }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- ═══════════════════ CTA ═══════════════════ --}}
        <section class="px-4 pb-20 sm:px-6">
            <div class="relative mx-auto max-w-4xl overflow-hidden rounded-3xl bg-gradient-to-br from-primary-600 to-primary-800 p-10 text-center text-white shadow-2xl shadow-primary-700/30 md:p-14">
                <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
                    <div class="absolute -top-16 right-10 size-64 rounded-full bg-white/30 blur-3xl"></div>
                    <div class="absolute -bottom-20 left-10 size-72 rounded-full bg-white/20 blur-3xl"></div>
                </div>
                <h2 class="relative text-2xl font-extrabold sm:text-3xl">آماده‌اید فروش اینستاگرامی‌تان را جدی بگیرید؟</h2>
                <p class="relative mx-auto mt-3 max-w-md text-sm leading-7 text-primary-100">
                    همین حالا فروشگاه‌تان را بسازید؛ اولین محصول را در کمتر از ۵ دقیقه منتشر کنید.
                </p>
                <a href="{{ route('seller.home') }}"
                   class="relative mt-7 inline-flex items-center gap-2 rounded-2xl bg-white px-7 py-3.5 text-sm font-bold text-primary-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-primary-50">
                    <x-icon name="sparkles" class="size-5" />
                    شروع رایگان
                </a>
            </div>
        </section>
    </main>

    {{-- ═══════════════════ FOOTER (sticky via mt-auto) ═══════════════════ --}}
    <footer class="mt-auto border-t border-slate-200 bg-white py-8 dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 text-sm text-slate-500 sm:flex-row sm:px-6 dark:text-slate-400">
            <div class="flex items-center gap-2">
                <span class="grid size-7 place-items-center rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 text-white">
                    <x-icon name="store" class="size-3.5" />
                </span>
                <span class="font-bold text-slate-700 dark:text-slate-200">{{ $ipAppName }}</span>
                <span class="text-xs">© ۱۴۰۵</span>
            </div>
            <div class="flex items-center gap-5">
                <a href="{{ route('admin.dashboard') }}" class="transition hover:text-primary-600">پنل مدیریت</a>
                <a href="{{ route('seller.home') }}" class="transition hover:text-primary-600">اپ فروشنده</a>
                <a href="{{ route('shop.home') }}" class="transition hover:text-primary-600">اپ مشتری</a>
            </div>
        </div>
    </footer>
</body>
</html>
