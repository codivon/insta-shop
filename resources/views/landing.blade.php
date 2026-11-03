<!DOCTYPE html>
<html lang="fa" dir="rtl" data-panel="landing">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ \App\Models\Setting::get('seo.meta_description', 'InstaShop — از پیج اینستاگرام تا فروشگاه آنلاین کامل؛ ثبت‌نام با شماره موبایل، پرداخت امن ایرانی، کیف پول و تسویه، ارسال با پست پیشتاز و دو اپ مستقل قابل نصب روی گوشی.') }}">
    @php($ipAppName = \App\Support\Theme::resolve()['app_name'])
<title>{{ $ipAppName }} — از پیج اینستاگرام تا فروشگاه آنلاین</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.brand-favicon')
    @include('partials.theme-vars')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>document.documentElement.classList.add('js');</script>
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">

    {{-- ═══════════════════ HEADER ═══════════════════ --}}
    <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/80 backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/80">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-3 px-4 sm:px-6">
            <a href="/" class="flex shrink-0 items-center gap-2.5" aria-label="{{ $ipAppName }}">
                @if (\App\Support\Branding::logoUrl())
                    <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ $ipAppName }}" class="size-9 rounded-xl object-contain">
                @else
                    <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                        <x-icon name="store" class="size-5" />
                    </span>
                @endif
                <span class="text-lg font-extrabold tracking-tight">
                    @if (str_starts_with($ipAppName, 'Insta') && mb_strlen($ipAppName) > 5)
                        Insta<span class="text-primary-600 dark:text-primary-400">{{ mb_substr($ipAppName, 5) }}</span>
                    @else
                        <span class="text-primary-600 dark:text-primary-400">{{ $ipAppName }}</span>
                    @endif
                </span>
            </a>

            <nav class="hidden items-center gap-7 text-sm font-medium text-slate-600 dark:text-slate-300 md:flex" aria-label="منوی اصلی">
                <a href="#features" class="transition hover:text-primary-600 dark:hover:text-primary-400">امکانات</a>
                <a href="#panels" class="transition hover:text-primary-600 dark:hover:text-primary-400">پنل‌ها</a>
                <a href="#how" class="transition hover:text-primary-600 dark:hover:text-primary-400">شروع فروش</a>
                <a href="#faq" class="transition hover:text-primary-600 dark:hover:text-primary-400">پرسش‌ها</a>
            </nav>

            <div class="flex items-center gap-2">
                <x-theme-toggle />
                <a href="{{ route('shop.home') }}"
                   class="hidden rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 sm:inline-flex">
                    ورود به فروشگاه
                </a>

                {{-- mobile menu (no-JS: details/summary) --}}
                <details class="relative md:hidden">
                    <summary class="grid size-10 cursor-pointer list-none place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 [&::-webkit-details-marker]:hidden" aria-label="باز کردن منو">
                        <x-icon name="menu" class="size-5" />
                    </summary>
                    <div class="absolute left-0 top-12 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                        <a href="#features" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-primary-600 dark:text-slate-300 dark:hover:bg-slate-800">امکانات</a>
                        <a href="#panels" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-primary-600 dark:text-slate-300 dark:hover:bg-slate-800">پنل‌ها</a>
                        <a href="#how" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-primary-600 dark:text-slate-300 dark:hover:bg-slate-800">شروع فروش</a>
                        <a href="#faq" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-primary-600 dark:text-slate-300 dark:hover:bg-slate-800">پرسش‌ها</a>
                        <a href="{{ route('shop.home') }}" class="mt-1 block rounded-xl bg-primary-600 px-3 py-2.5 text-center text-sm font-bold text-white">ورود به فروشگاه</a>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <main>
        {{-- ═══════════════════ HERO ═══════════════════ --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(100,116,139,0.16)_1px,transparent_0)] [background-size:22px_22px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_35%,black,transparent)] dark:bg-[radial-gradient(circle_at_1px_1px,rgba(148,163,184,0.13)_1px,transparent_0)]"></div>
                <div class="absolute -top-36 right-[12%] size-[30rem] rounded-full bg-primary-400/25 blur-3xl dark:bg-primary-600/20"></div>
                <div class="absolute -bottom-44 left-[8%] size-[26rem] rounded-full bg-pink-400/15 blur-3xl dark:bg-pink-600/10"></div>
            </div>

            <div class="mx-auto grid max-w-6xl items-center gap-14 px-4 pb-16 pt-14 sm:px-6 md:pt-20 lg:grid-cols-2 lg:gap-8 lg:pb-24">
                <div class="ip-fade-up text-center lg:text-right">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-primary-200 bg-primary-50 px-3.5 py-1.5 text-xs font-bold text-primary-700 shadow-sm dark:border-primary-800 dark:bg-primary-950 dark:text-primary-300">
                        <x-icon name="sparkles" class="size-3.5" />
                        پلتفرم فروش اینستاگرامی، همه‌چیز آماده
                    </span>

                    <h1 class="mt-6 text-4xl font-black leading-[1.25] tracking-tight sm:text-5xl lg:text-[3.3rem]">
                        پیج اینستاگرامت را به یک
                        <span class="relative inline-block bg-gradient-to-l from-primary-500 via-primary-600 to-pink-600 bg-clip-text text-transparent dark:from-primary-400 dark:via-primary-500 dark:to-pink-500">
                            فروشگاه آنلاین
                        </span>
                        تبدیل کن
                    </h1>

                    <p class="mx-auto mt-6 max-w-xl text-base leading-8 text-slate-600 dark:text-slate-300 lg:mx-0 md:text-lg md:leading-9">
                        ثبت‌نام فقط با شماره موبایل، مدیریت محصول از گوشی، پرداخت امن ایرانی،
                        کیف پول و تسویه شفاف، ارسال با پست پیشتاز و انتخاب روز تحویل —
                        همه‌چیز آماده است تا فقط به فروش فکر کنی.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                        <a href="{{ route('seller.home') }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-primary-600 px-7 py-4 text-sm font-extrabold text-white shadow-xl shadow-primary-600/30 transition hover:-translate-y-0.5 hover:bg-primary-700 active:scale-[0.98]">
                            <x-icon name="rocket" class="size-5" />
                            شروع فروشندگی
                        </a>
                        <a href="{{ route('shop.home') }}"
                           class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-7 py-4 text-sm font-extrabold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-primary-300 hover:text-primary-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-primary-700 dark:hover:text-primary-300 active:scale-[0.98]">
                            <x-icon name="cart" class="size-5" />
                            ورود به فروشگاه
                        </a>
                    </div>

                    <div class="mt-9 flex flex-wrap items-center justify-center gap-x-7 gap-y-3 text-[13px] font-medium text-slate-500 dark:text-slate-400 lg:justify-start">
                        <span class="flex items-center gap-1.5"><x-icon name="circle-check" class="size-4 text-primary-600" /> ثبت‌نام با کد پیامکی</span>
                        <span class="flex items-center gap-1.5"><x-icon name="circle-check" class="size-4 text-primary-600" /> خرید مهمان بدون ثبت‌نام</span>
                        <span class="flex items-center gap-1.5"><x-icon name="circle-check" class="size-4 text-primary-600" /> نصب اپ روی گوشی</span>
                    </div>
                </div>

                {{-- app mockups --}}
                <div class="relative mx-auto w-fit" aria-hidden="true">
                    <div class="absolute -inset-10 -z-10 rounded-[3.5rem] bg-gradient-to-br from-primary-500/20 via-pink-500/10 to-orange-400/10 blur-2xl"></div>

                    <div class="flex items-end justify-center gap-4 sm:gap-6">
                        {{-- customer app phone --}}
                        <div class="hidden w-44 rotate-[5deg] rounded-[2rem] border-4 border-slate-900 bg-slate-900 p-1.5 shadow-2xl transition duration-500 hover:rotate-2 sm:block dark:border-slate-700 dark:bg-slate-800">
                            <div class="overflow-hidden rounded-[1.55rem] bg-white dark:bg-slate-950">
                                <div class="bg-gradient-to-br from-primary-500 to-primary-700 px-3 pb-7 pt-3 text-white">
                                    <div class="flex items-center justify-between text-[8px] opacity-90" dir="ltr">
                                        <span>۱۲:۳۰</span><span>{{ $ipAppName }}</span>
                                    </div>
                                    <div class="mt-2.5 text-[10px] font-extrabold">فروشگاه شما</div>
                                    <div class="text-[7.5px] opacity-85" dir="ltr">@your_shop</div>
                                </div>
                                <div class="-mt-3.5 rounded-t-2xl bg-white p-2.5 dark:bg-slate-950">
                                    <div class="h-5 rounded-lg bg-slate-100 dark:bg-slate-900"></div>
                                    <div class="mt-2 grid grid-cols-2 gap-1.5">
                                        @foreach ([1, 2, 3, 4] as $i)
                                            <div class="rounded-lg border border-slate-100 p-1 dark:border-slate-800">
                                                <div class="h-9 rounded-md bg-slate-100 dark:bg-slate-900"></div>
                                                <div class="mt-1 h-1 w-3/4 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                                                <div class="mt-0.5 h-1 w-1/2 rounded-full bg-primary-300 dark:bg-primary-800"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2 flex items-center justify-around rounded-xl bg-slate-50 py-1.5 dark:bg-slate-900">
                                        <x-icon name="home" class="size-3 text-primary-600" />
                                        <x-icon name="list" class="size-3 text-slate-300 dark:text-slate-600" />
                                        <x-icon name="cart" class="size-3 text-slate-300 dark:text-slate-600" />
                                        <x-icon name="user" class="size-3 text-slate-300 dark:text-slate-600" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- seller app phone (main) --}}
                        <div class="w-48 rounded-[2.2rem] border-4 border-slate-900 bg-slate-900 p-2 shadow-2xl transition duration-500 hover:-rotate-1 sm:w-52 dark:border-slate-700 dark:bg-slate-800">
                            <div class="overflow-hidden rounded-[1.8rem] bg-white dark:bg-slate-950">
                                <div class="bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 px-3.5 pb-8 pt-3.5 text-white">
                                    <div class="flex items-center justify-between text-[8px] opacity-90" dir="ltr">
                                        <span>۱۲:۳۰</span><span>اپ فروشنده</span>
                                    </div>
                                    <div class="mt-3 text-[10px] opacity-80">فروش امروز</div>
                                    <div class="text-lg font-black tracking-tight">۲٬۴۵۰٬۰۰۰ <span class="text-[8px] font-normal opacity-80">تومان</span></div>
                                    <div class="mt-2 flex items-end gap-1" dir="ltr">
                                        @foreach ([35, 55, 40, 70, 52, 88, 64] as $h)
                                            <span class="w-2.5 rounded-t-sm bg-white/40" style="height: {{ $h * 0.32 }}px"></span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="-mt-4 space-y-1.5 rounded-t-2xl bg-white p-2.5 dark:bg-slate-950">
                                    <div class="flex items-center gap-2 rounded-xl border border-slate-100 p-2 dark:border-slate-800">
                                        <span class="size-6 shrink-0 rounded-lg bg-primary-100 dark:bg-primary-950"></span>
                                        <span class="h-1.5 flex-1 rounded-full bg-slate-200 dark:bg-slate-800"></span>
                                        <span class="h-1.5 w-7 rounded-full bg-primary-300 dark:bg-primary-800"></span>
                                    </div>
                                    <div class="flex items-center gap-2 rounded-xl border border-slate-100 p-2 dark:border-slate-800">
                                        <span class="size-6 shrink-0 rounded-lg bg-slate-100 dark:bg-slate-900"></span>
                                        <span class="h-1.5 flex-1 rounded-full bg-slate-200 dark:bg-slate-800"></span>
                                        <span class="h-1.5 w-7 rounded-full bg-slate-200 dark:bg-slate-800"></span>
                                    </div>
                                    <div class="grid grid-cols-4 gap-1.5 pt-1">
                                        @foreach (['package', 'wallet', 'chart', 'bell'] as $ic)
                                            <span class="grid place-items-center rounded-xl bg-slate-50 py-2 text-slate-400 dark:bg-slate-900">
                                                <x-icon name="{{ $ic }}" class="size-3.5" />
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- floating glass chips --}}
                    <div class="ip-float absolute -top-4 right-0 flex items-center gap-2 rounded-2xl border border-white/70 bg-white/85 px-3 py-2 shadow-xl backdrop-blur sm:-right-6 dark:border-slate-700/70 dark:bg-slate-900/85">
                        <span class="grid size-7 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15">
                            <x-icon name="circle-check" class="size-4" />
                        </span>
                        <div class="text-[11px] font-extrabold leading-4 text-slate-800 dark:text-slate-100">پرداخت موفق<div class="text-[9px] font-medium text-slate-400">۲ دقیقه پیش</div></div>
                    </div>
                    <div class="ip-float-slow absolute -bottom-4 left-0 flex items-center gap-2 rounded-2xl border border-white/70 bg-white/85 px-3 py-2 shadow-xl backdrop-blur sm:-left-4 dark:border-slate-700/70 dark:bg-slate-900/85" style="animation-delay: 1.2s">
                        <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950">
                            <x-icon name="bell" class="size-4" />
                        </span>
                        <div class="text-[11px] font-extrabold leading-4 text-slate-800 dark:text-slate-100">سفارش جدید ثبت شد<div class="text-[9px] font-medium text-slate-400">همین حالا</div></div>
                    </div>
                    <div class="ip-float absolute bottom-16 right-2 hidden items-center gap-2 rounded-2xl border border-white/70 bg-white/85 px-3 py-2 shadow-xl backdrop-blur md:flex dark:border-slate-700/70 dark:bg-slate-900/85" style="animation-delay: 2.1s">
                        <span class="grid size-7 place-items-center rounded-xl bg-pink-100 text-pink-600 dark:bg-pink-500/15">
                            <x-icon name="download" class="size-4" />
                        </span>
                        <div class="text-[11px] font-extrabold text-slate-800 dark:text-slate-100">قابل نصب روی گوشی</div>
                    </div>
                </div>
            </div>

            {{-- stats band --}}
            <div class="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:pb-20">
                <dl class="ip-reveal grid grid-cols-2 gap-3 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-4 sm:gap-4 sm:p-7 dark:border-slate-800 dark:bg-slate-900">
                    @foreach ([
                        ['n' => '۵',  'l' => 'درگاه پرداخت ایرانی'],
                        ['n' => '۵',  'l' => 'پیامک‌رسان آماده اتصال'],
                        ['n' => '۱۱', 'l' => 'پالت رنگی قابل انتخاب'],
                        ['n' => '۲',  'l' => 'اپ مستقل قابل نصب'],
                    ] as $s)
                        <div class="rounded-2xl bg-slate-50 p-4 text-center dark:bg-slate-950/60">
                            <dt class="sr-only">{{ $s['l'] }}</dt>
                            <dd class="bg-gradient-to-l from-primary-500 to-primary-700 bg-clip-text text-3xl font-black text-transparent">{{ $s['n'] }}</dd>
                            <dd class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">{{ $s['l'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>

        {{-- ═══════════════════ INFRASTRUCTURE MARQUEE ═══════════════════ --}}
        <section class="border-y border-slate-200 bg-white py-7 dark:border-slate-800 dark:bg-slate-900/50" aria-label="زیرساخت‌های متصل">
            <p class="mb-5 text-center text-xs font-bold tracking-wide text-slate-400 dark:text-slate-500">زیرساخت‌های آماده اتصال — از پنل مدیریت، در چند دقیقه</p>
            <div class="ip-marquee relative overflow-hidden [mask-image:linear-gradient(to_left,transparent,black_12%,black_88%,transparent)]">
                <div class="ip-marquee-track flex w-max items-center gap-3 px-3" dir="ltr">
                    @foreach (array_merge([
                        'زرین‌پال', 'زیبال', 'بانک ملت', 'سپ', 'آیدی‌پی',
                        'کاوه‌نگار', 'فراز اس‌ام‌اس', 'ملی‌پیامک', 'پست پیشتاز', 'تیپاکس',
                        'پرداخت امن', 'نصب PWA', 'اعلان پوش',
                    ], [
                        'زرین‌پال', 'زیبال', 'بانک ملت', 'سپ', 'آیدی‌پی',
                        'کاوه‌نگار', 'فراز اس‌ام‌اس', 'ملی‌پیامک', 'پست پیشتاز', 'تیپاکس',
                        'پرداخت امن', 'نصب PWA', 'اعلان پوش',
                    ]) as $chip)
                        <span class="flex items-center gap-1.5 whitespace-nowrap rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-[13px] font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                            <x-icon name="circle-check" class="size-3.5 text-primary-500" />
                            {{ $chip }}
                        </span>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══════════════════ FEATURES BENTO ═══════════════════ --}}
        <section id="features" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-20 sm:px-6">
            <div class="ip-reveal mx-auto max-w-2xl text-center">
                <span class="text-xs font-black tracking-widest text-primary-600 dark:text-primary-400">امکانات</span>
                <h2 class="mt-2 text-2xl font-extrabold sm:text-4xl">هر چیزی که برای فروش لازم داری، همین حالا آماده است</h2>
                <p class="mt-4 text-sm leading-7 text-slate-500 dark:text-slate-400">
                    نه افزونه، نه نصب اضافه — همهٔ امکانات زیر همین حالا روی پلتفرم فعال‌اند و از پنل مدیریت قابل تنظیم‌اند.
                </p>
            </div>

            <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                {{-- storefront address --}}
                <div class="ip-reveal group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl md:col-span-2 lg:col-span-4 dark:border-slate-800 dark:bg-slate-900">
                    <div class="pointer-events-none absolute -left-16 -top-16 size-48 rounded-full bg-primary-500/10 blur-2xl transition group-hover:bg-primary-500/20"></div>
                    <div class="relative flex h-full flex-col justify-between gap-5 sm:flex-row-reverse sm:items-center">
                        <div class="sm:max-w-[55%]">
                            <span class="grid size-12 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                                <x-icon name="instagram" class="size-6" />
                            </span>
                            <h3 class="mt-4 text-lg font-extrabold">فروشگاه با آدرس آیدی اینستاگرام</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                                هر فروشنده یک فروشگاه مستقل با آدرس اختصاصی می‌گیرد؛ لینک را در بیو اینستاگرام بگذار و
                                مشتری مستقیم وارد فروشگاهت شود.
                            </p>
                        </div>
                        <div class="w-full shrink-0 sm:w-[42%]">
                            <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-3 shadow-inner dark:border-slate-700 dark:bg-slate-950">
                                <x-icon name="lock" class="size-3.5 shrink-0 text-emerald-500" />
                                <span class="truncate text-xs font-bold text-slate-600 dark:text-slate-300" dir="ltr">shop.ir/<span class="text-primary-600 dark:text-primary-400">@your_shop</span></span>
                            </div>
                            <div class="mt-2.5 space-y-2">
                                <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-[11px] font-bold text-slate-500 dark:bg-slate-950 dark:text-slate-400"><x-icon name="external-link" class="size-3.5 text-primary-500" /> لینک در بیو یا استوری</div>
                                <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-[11px] font-bold text-slate-500 dark:bg-slate-950 dark:text-slate-400"><x-icon name="search" class="size-3.5 text-primary-500" /> مرور و خرید بدون ثبت‌نام</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- OTP login --}}
                <div class="ip-reveal group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl md:col-span-1 lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                    <span class="grid size-12 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="phone" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-lg font-extrabold">ورود با کد یکبارمصرف</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">بدون رمز، بدون ایمیل — فقط شماره موبایل و کد پیامکی.</p>
                    <div class="mt-4 flex justify-center gap-2" dir="ltr" aria-hidden="true">
                        @foreach (['۷', '۲', '۵', '۱'] as $d)
                            <span class="grid size-10 place-items-center rounded-xl border-2 border-slate-200 bg-slate-50 text-base font-black text-slate-700 transition group-hover:border-primary-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">{{ $d }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- wallet --}}
                <div class="ip-reveal group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                    <span class="grid size-12 place-items-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                        <x-icon name="wallet" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-lg font-extrabold">کیف پول و تسویه</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">موجودی لحظه‌ای، برداشت به کارت بانکی و تاریخچهٔ کامل تراکنش‌ها.</p>
                    <div class="mt-4 rounded-2xl border border-emerald-200/70 bg-emerald-50 px-4 py-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                        <div class="text-[10px] font-bold text-emerald-700/70 dark:text-emerald-400/80">موجودی قابل برداشت</div>
                        <div class="mt-0.5 text-sm font-black text-emerald-700 dark:text-emerald-300">۲٬۴۵۰٬۰۰۰ تومان</div>
                    </div>
                </div>

                {{-- shipping --}}
                <div class="ip-reveal group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                    <span class="grid size-12 place-items-center rounded-2xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                        <x-icon name="truck" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-lg font-extrabold">ارسال و انتخاب روز تحویل</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">پست پیشتاز، تیپاکس یا ارسال خودِ فروشنده — با هزینهٔ شهر/استان جداگانه.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full bg-amber-50 px-3 py-1.5 text-[11px] font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">پست پیشتاز</span>
                        <span class="rounded-full bg-slate-50 px-3 py-1.5 text-[11px] font-bold text-slate-500 dark:bg-slate-900 dark:text-slate-400">تیپاکس</span>
                        <span class="rounded-full bg-slate-50 px-3 py-1.5 text-[11px] font-bold text-slate-500 dark:bg-slate-900 dark:text-slate-400">ارسال شخصی</span>
                    </div>
                </div>

                {{-- discounts --}}
                <div class="ip-reveal group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                    <span class="grid size-12 place-items-center rounded-2xl bg-pink-500/10 text-pink-600 dark:bg-pink-500/15 dark:text-pink-400">
                        <x-icon name="tag" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-lg font-extrabold">کد تخفیف و جشنوارهٔ فروش</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">تخفیف درصدی یا مبلغی با سقف و تاریخ انقضا؛ کد از هر فروشگاه.</p>
                    <div class="mt-4 flex items-center justify-between rounded-2xl border-2 border-dashed border-pink-300/70 bg-pink-50/50 px-4 py-3 dark:border-pink-500/30 dark:bg-pink-500/10">
                        <span class="text-sm font-black tracking-wider text-pink-600 dark:text-pink-400" dir="ltr">WELCOME</span>
                        <span class="rounded-lg bg-pink-600 px-2 py-1 text-[10px] font-black text-white">۱۵٪ تخفیف</span>
                    </div>
                </div>

                {{-- PWA apps --}}
                <div class="ip-reveal group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl lg:col-span-3 dark:border-slate-800 dark:bg-slate-900">
                    <div class="pointer-events-none absolute -bottom-20 -right-20 size-56 rounded-full bg-orange-400/10 blur-2xl"></div>
                    <span class="grid size-12 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="smartphone" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-lg font-extrabold">دو اپ مستقل، نصب‌شدنی روی گوشی</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                        اپ فروشنده و اپ مشتری، هر کدام با نام و آیکون جداگانه؛ بدون استور، مستقیم از مرورگر نصب می‌شوند
                        و مثل اپ بومی باز می‌مانند.
                    </p>
                    <div class="mt-4 flex items-center gap-3">
                        <span class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-950">
                            <span class="grid size-8 place-items-center rounded-xl bg-gradient-to-br from-fuchsia-600 via-pink-500 to-orange-500 text-white"><x-icon name="bag" class="size-4" /></span>
                            <span class="text-xs font-extrabold text-slate-700 dark:text-slate-200">اپ فروشنده</span>
                        </span>
                        <span class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-950">
                            <span class="grid size-8 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white"><x-icon name="cart" class="size-4" /></span>
                            <span class="text-xs font-extrabold text-slate-700 dark:text-slate-200">اپ مشتری</span>
                        </span>
                        <span class="hidden items-center gap-1 text-[11px] font-bold text-emerald-600 sm:flex dark:text-emerald-400"><x-icon name="circle-check" class="size-3.5" /> نصب با یک لمس</span>
                    </div>
                </div>

                {{-- payment gateways --}}
                <div class="ip-reveal group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl lg:col-span-3 dark:border-slate-800 dark:bg-slate-900">
                    <div class="pointer-events-none absolute -bottom-20 -left-20 size-56 rounded-full bg-primary-500/10 blur-2xl"></div>
                    <span class="grid size-12 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="credit-card" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-lg font-extrabold">پرداخت امن با درگاه‌های ایرانی</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                        زرین‌پال، زیبال، بانک ملت، سپ و آیدی‌پی — درگاه از پنل مدیریت انتخاب و در چند دقیقه وصل می‌شود.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach (['زرین‌پال', 'زیبال', 'بانک ملت', 'سپ', 'آیدی‌پی'] as $g)
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-extrabold text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">{{ $g }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- small features --}}
            <div class="ip-reveal mt-12">
                <h3 class="text-center text-sm font-black tracking-widest text-slate-400 dark:text-slate-500">و جزئیاتی که تجربه را کامل می‌کنند</h3>
                <ul class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ([
                        ['icon' => 'megaphone',  't' => 'پیامک چندپروایدره'],
                        ['icon' => 'bell',       't' => 'اعلان پوش روی گوشی'],
                        ['icon' => 'headset',    't' => 'پشتیبانی تیکتی'],
                        ['icon' => 'chart',      't' => 'گزارش فروش و بازدید'],
                        ['icon' => 'filter',     't' => 'دسته‌بندی، ویژگی و فیلتر'],
                        ['icon' => 'heart',      't' => 'علاقه‌مندی و سبد خرید'],
                        ['icon' => 'palette',    't' => 'تم روشن/تیره و ۱۱ پالت'],
                        ['icon' => 'image-plus', 't' => 'آپلود چندتصویری محصول'],
                        ['icon' => 'database',   't' => 'پشتیبان‌گیری از دیتابیس'],
                        ['icon' => 'map-pin',    't' => 'مدیریت استان و شهر'],
                        ['icon' => 'lock',       't' => 'نقش‌ها و سطوح دسترسی'],
                        ['icon' => 'file-text',  't' => 'ثبت فعالیت کاربران'],
                    ] as $f)
                        <li class="flex items-center gap-2.5 rounded-2xl border border-slate-100 bg-white px-3.5 py-3 text-[13px] font-bold text-slate-600 transition hover:border-primary-200 hover:text-primary-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-primary-800 dark:hover:text-primary-300">
                            <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                                <x-icon name="{{ $f['icon'] }}" class="size-4" />
                            </span>
                            {{ $f['t'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- ═══════════════════ PANELS ═══════════════════ --}}
        <section id="panels" class="scroll-mt-20 border-y border-slate-200 bg-white py-20 dark:border-slate-800 dark:bg-slate-900/50">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="ip-reveal mx-auto max-w-2xl text-center">
                    <span class="text-xs font-black tracking-widest text-primary-600 dark:text-primary-400">سه پنل</span>
                    <h2 class="mt-2 text-2xl font-extrabold sm:text-4xl">یک اکوسیستم کامل برای همه</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-500 dark:text-slate-400">هر پنل برای کاربر خودش طراحی شده و با نشست کاملاً مستقل کار می‌کند.</p>
                </div>

                <div class="mt-12 grid gap-5 md:grid-cols-3">
                    {{-- admin --}}
                    <div class="ip-reveal group flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-950">
                        <div class="p-5 pb-0">
                            <div class="overflow-hidden rounded-t-2xl border border-b-0 border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex items-center gap-1.5 border-b border-slate-100 px-3 py-2 dark:border-slate-800" dir="ltr">
                                    <span class="size-2 rounded-full bg-rose-400"></span>
                                    <span class="size-2 rounded-full bg-amber-400"></span>
                                    <span class="size-2 rounded-full bg-emerald-400"></span>
                                </div>
                                <div class="flex gap-2 p-3">
                                    <div class="w-10 space-y-1.5">
                                        @foreach ([1, 2, 3, 4] as $i)
                                            <div class="h-2.5 rounded-md {{ $i === 1 ? 'bg-primary-200 dark:bg-primary-800' : 'bg-slate-100 dark:bg-slate-800' }}"></div>
                                        @endforeach
                                    </div>
                                    <div class="flex-1 space-y-1.5">
                                        <div class="grid grid-cols-3 gap-1.5">
                                            <div class="h-7 rounded-lg bg-slate-100 dark:bg-slate-800"></div>
                                            <div class="h-7 rounded-lg bg-slate-100 dark:bg-slate-800"></div>
                                            <div class="h-7 rounded-lg bg-primary-100 dark:bg-primary-900"></div>
                                        </div>
                                        <div class="h-12 rounded-lg bg-slate-100 dark:bg-slate-800"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <span class="grid size-11 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                                <x-icon name="shield" class="size-5" />
                            </span>
                            <h3 class="mt-3.5 text-lg font-extrabold">پنل مدیریت</h3>
                            <ul class="mt-3 flex-1 space-y-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 shrink-0 text-primary-500" /> داشبورد آماری و مدیریت کاربران</li>
                                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 shrink-0 text-primary-500" /> تنظیم پیامک، درگاه و رنگ‌بندی</li>
                                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 shrink-0 text-primary-500" /> تسویه‌ها، پشتیبان‌گیری و دسترسی‌ها</li>
                            </ul>
                            <a href="{{ route('admin.dashboard') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-extrabold text-primary-600 transition hover:gap-2.5 dark:text-primary-400">
                                ورود مدیر <x-icon name="arrow-left" class="size-4" />
                            </a>
                        </div>
                    </div>

                    {{-- seller --}}
                    <div class="ip-reveal group relative flex flex-col overflow-hidden rounded-3xl border-2 border-primary-500/40 bg-gradient-to-b from-primary-50/60 to-white shadow-lg shadow-primary-600/5 transition hover:-translate-y-1 hover:shadow-xl dark:border-primary-500/30 dark:from-primary-950/40 dark:to-slate-950">
                        <span class="absolute left-4 top-4 rounded-full bg-primary-600 px-2.5 py-1 text-[10px] font-black text-white shadow-md">محبوب‌ترین</span>
                        <div class="p-5 pb-0">
                            <div class="mx-auto w-36 rounded-[1.6rem] border-4 border-slate-900 bg-slate-900 p-1 shadow-xl dark:border-slate-700 dark:bg-slate-800">
                                <div class="overflow-hidden rounded-[1.15rem] bg-white dark:bg-slate-950">
                                    <div class="bg-gradient-to-br from-primary-600 to-primary-800 px-2.5 pb-5 pt-2.5 text-white">
                                        <div class="text-[8px] opacity-80">فروش امروز</div>
                                        <div class="text-[13px] font-black">۱٬۸۵۰٬۰۰۰</div>
                                    </div>
                                    <div class="-mt-2.5 space-y-1 rounded-t-xl bg-white p-2 dark:bg-slate-950">
                                        <div class="h-5 rounded-md bg-primary-100 dark:bg-primary-950"></div>
                                        <div class="h-5 rounded-md bg-slate-100 dark:bg-slate-900"></div>
                                        <div class="h-5 rounded-md bg-slate-100 dark:bg-slate-900"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <span class="grid size-11 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                                <x-icon name="bag" class="size-5" />
                            </span>
                            <h3 class="mt-3.5 text-lg font-extrabold">اپ فروشنده</h3>
                            <ul class="mt-3 flex-1 space-y-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 shrink-0 text-primary-500" /> محصول، دسته‌بندی و ویژگی از گوشی</li>
                                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 shrink-0 text-primary-500" /> سفارش‌ها، کیف پول و کارت بانکی</li>
                                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 shrink-0 text-primary-500" /> گزارش فروش و بازدید محصولات</li>
                            </ul>
                            <a href="{{ route('seller.home') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-extrabold text-primary-600 transition hover:gap-2.5 dark:text-primary-400">
                                ورود فروشنده <x-icon name="arrow-left" class="size-4" />
                            </a>
                        </div>
                    </div>

                    {{-- customer --}}
                    <div class="ip-reveal group flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-950">
                        <div class="p-5 pb-0">
                            <div class="mx-auto w-36 rounded-[1.6rem] border-4 border-slate-900 bg-slate-900 p-1 shadow-xl dark:border-slate-700 dark:bg-slate-800">
                                <div class="overflow-hidden rounded-[1.15rem] bg-white dark:bg-slate-950">
                                    <div class="bg-gradient-to-br from-primary-500 to-primary-700 px-2.5 pb-5 pt-2.5 text-white">
                                        <div class="text-[8px] opacity-80">فروشگاه شما</div>
                                        <div class="text-[9px] opacity-75" dir="ltr">@your_shop</div>
                                    </div>
                                    <div class="-mt-2.5 grid grid-cols-2 gap-1 rounded-t-xl bg-white p-2 dark:bg-slate-950">
                                        @foreach ([1, 2, 3, 4] as $i)
                                            <div class="h-7 rounded-md bg-slate-100 dark:bg-slate-900"></div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <span class="grid size-11 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                                <x-icon name="cart" class="size-5" />
                            </span>
                            <h3 class="mt-3.5 text-lg font-extrabold">اپ مشتری</h3>
                            <ul class="mt-3 flex-1 space-y-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 shrink-0 text-primary-500" /> خرید مهمان، بدون ثبت‌نام اجباری</li>
                                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 shrink-0 text-primary-500" /> انتخاب روز تحویل و پرداخت امن</li>
                                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 shrink-0 text-primary-500" /> پیگیری سفارش تا لحظه تحویل</li>
                            </ul>
                            <a href="{{ route('shop.home') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-extrabold text-primary-600 transition hover:gap-2.5 dark:text-primary-400">
                                ورود مشتری <x-icon name="arrow-left" class="size-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ═══════════════════ HOW IT WORKS ═══════════════════ --}}
        <section id="how" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-20 sm:px-6">
            <div class="ip-reveal mx-auto max-w-2xl text-center">
                <span class="text-xs font-black tracking-widest text-primary-600 dark:text-primary-400">شروع فروش</span>
                <h2 class="mt-2 text-2xl font-extrabold sm:text-4xl">در ۴ قدم بفروش</h2>
                <p class="mt-4 text-sm leading-7 text-slate-500 dark:text-slate-400">اولین محصولت را کمتر از ۵ دقیقه منتشر کن.</p>
            </div>

            <ol class="relative mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
                <div class="absolute inset-x-16 top-7 hidden border-t-2 border-dashed border-slate-200 lg:block dark:border-slate-700" aria-hidden="true"></div>
                @foreach ([
                    ['n' => '۱', 'icon' => 'user',    'title' => 'ثبت‌نام فروشنده', 'desc' => 'شماره موبایل و شناسه اینستاگرام؛ تأیید با کد پیامکی.'],
                    ['n' => '۲', 'icon' => 'package', 'title' => 'ساخت فروشگاه',    'desc' => 'دسته‌بندی‌ها و محصولات را با چند عکس از گوشی بساز.'],
                    ['n' => '۳', 'icon' => 'external-link',   'title' => 'اشتراک‌گذاری',     'desc' => 'لینک فروشگاه را در بیو اینستاگرامت بگذار.'],
                    ['n' => '۴', 'icon' => 'coins',   'title' => 'فروش و تسویه',     'desc' => 'سفارش ثبت می‌شود و مبلغ به کارت تو واریز می‌شود.'],
                ] as $step)
                    <li class="ip-reveal relative text-center lg:text-right">
                        <div class="relative mx-auto grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-xl shadow-primary-600/25 lg:mx-0">
                            <x-icon name="{{ $step['icon'] }}" class="size-6" />
                            <span class="absolute -left-2 -top-2 grid size-6 place-items-center rounded-full border-2 border-white bg-slate-900 text-[10px] font-black text-white dark:border-slate-950">{{ $step['n'] }}</span>
                        </div>
                        <h3 class="mt-4 font-extrabold">{{ $step['title'] }}</h3>
                        <p class="mx-auto mt-1.5 max-w-[16rem] text-sm leading-7 text-slate-500 lg:mx-0 dark:text-slate-400">{{ $step['desc'] }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- ═══════════════════ FAQ ═══════════════════ --}}
        <section id="faq" class="scroll-mt-20 border-t border-slate-200 bg-white py-20 dark:border-slate-800 dark:bg-slate-900/50">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">
                <div class="ip-reveal text-center">
                    <span class="text-xs font-black tracking-widest text-primary-600 dark:text-primary-400">پرسش‌های پرتکرار</span>
                    <h2 class="mt-2 text-2xl font-extrabold sm:text-3xl">سوالی که ممکن است داشته باشی</h2>
                </div>

                <div class="mt-10 space-y-3">
                    @foreach ([
                        ['q' => 'برای شروع فروش به دانش فنی نیاز دارم؟', 'a' => 'نه. همه‌چیز از پنل گوشی انجام می‌شود: ساخت فروشگاه، افزودن محصول با عکس، تعیین قیمت و موجودی. هیچ نصب یا کدنویسی‌ای در کار نیست.'],
                        ['q' => 'پول فروش‌ها چطور به دستم می‌رسد؟', 'a' => 'پس از پرداخت مشتری، مبلغ به کیف پول فروشگاهت اضافه می‌شود و از پنل، درخواست برداشت به کارت بانکی خودت را ثبت می‌کنی؛ تاریخچهٔ کامل هم در کیف پول دیده می‌شود.'],
                        ['q' => 'اپ‌ها چطور روی گوشی نصب می‌شوند؟', 'a' => 'اپ فروشنده و اپ مشتری هر دو از مرورگر و بدون استور نصب می‌شوند (PWA). فقط توجه کن که چون آدرس سرویس در ایران فیلتر است، برای نصب باید فیلترشکن روشن باشد؛ بعد از نصب، اپ مستقل روی صفحهٔ گوشی می‌نشیند.'],
                        ['q' => 'مشتری بدون ثبت‌نام می‌تواند خرید کند؟', 'a' => 'بله؛ مرور محصولات، سبد خرید و حتی ادامه تا پیش از پرداخت برای مهمان باز است. فقط برای ثبت نهایی سفارش، شماره موبایل برای پیگیری سفارش گرفته می‌شود.'],
                    ] as $item)
                        <details class="ip-reveal group rounded-2xl border border-slate-200 bg-slate-50 transition open:border-primary-200 open:bg-white open:shadow-lg dark:border-slate-800 dark:bg-slate-950 dark:open:border-primary-800">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 text-sm font-extrabold text-slate-700 [&::-webkit-details-marker]:hidden dark:text-slate-200">
                                {{ $item['q'] }}
                                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-white text-slate-400 shadow-sm transition group-open:rotate-180 group-open:text-primary-600 dark:bg-slate-900">
                                    <x-icon name="chevron-down" class="size-4" />
                                </span>
                            </summary>
                            <p class="border-t border-slate-100 px-5 py-4 text-sm leading-8 text-slate-500 dark:border-slate-800 dark:text-slate-400">{{ $item['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══════════════════ FINAL CTA ═══════════════════ --}}
        <section class="px-4 py-20 sm:px-6">
            <div class="ip-reveal relative mx-auto max-w-4xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 p-10 text-center text-white shadow-2xl shadow-primary-700/30 md:p-16">
                <div class="pointer-events-none absolute inset-0 opacity-25" aria-hidden="true">
                    <div class="absolute -top-16 right-10 size-64 rounded-full bg-white/30 blur-3xl"></div>
                    <div class="absolute -bottom-20 left-10 size-72 rounded-full bg-white/20 blur-3xl"></div>
                </div>
                <span class="relative inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold backdrop-blur">
                    <x-icon name="sparkles" class="size-3.5" />
                    شروع رایگان — بدون هزینهٔ راه‌اندازی
                </span>
                <h2 class="relative mt-5 text-2xl font-black leading-relaxed sm:text-4xl sm:leading-relaxed">فروش اینستاگرامی‌ات را همین امروز جدی بگیر</h2>
                <p class="relative mx-auto mt-4 max-w-md text-sm leading-7 text-primary-100">
                    فروشگاهت را بساز، اولین محصول را منتشر کن و لینک را در بیو بگذار —
                    بقیه‌اش با ما.
                </p>
                <div class="relative mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('seller.home') }}"
                       class="inline-flex items-center gap-2 rounded-2xl bg-white px-8 py-4 text-sm font-black text-primary-700 shadow-xl transition hover:-translate-y-0.5 hover:bg-primary-50 active:scale-[0.98]">
                        <x-icon name="rocket" class="size-5" />
                        شروع فروشندگی
                    </a>
                    <a href="{{ route('shop.home') }}"
                       class="inline-flex items-center gap-2 rounded-2xl border border-white/30 bg-white/10 px-8 py-4 text-sm font-black text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/20 active:scale-[0.98]">
                        <x-icon name="cart" class="size-5" />
                        دیدن فروشگاه
                    </a>
                </div>
            </div>
        </section>
    </main>

    {{-- ═══════════════════ FOOTER (sticky via mt-auto) ═══════════════════ --}}
    <footer class="mt-auto border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr]">
            <div>
                <div class="flex items-center gap-2.5">
                    @if (\App\Support\Branding::logoUrl())
                        <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ $ipAppName }}" class="size-9 rounded-xl object-contain">
                    @else
                        <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white">
                            <x-icon name="store" class="size-5" />
                        </span>
                    @endif
                    <span class="text-lg font-extrabold">{{ $ipAppName }}</span>
                </div>
                <p class="mt-4 max-w-sm text-sm leading-7 text-slate-500 dark:text-slate-400">
                    پلتفرم فروشگاهی مبتنی بر اینستاگرام؛ از پیج تا فروشگاه آنلاین کامل،
                    با اپ موبایلی فروشنده و مشتری، پرداخت امن ایرانی و تسویه شفاف.
                </p>
            </div>
            <nav aria-label="پیوندهای پنل‌ها">
                <h3 class="text-sm font-extrabold text-slate-700 dark:text-slate-200">پنل‌ها</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-500 dark:text-slate-400">
                    <li><a href="{{ route('admin.dashboard') }}" class="transition hover:text-primary-600">پنل مدیریت</a></li>
                    <li><a href="{{ route('seller.home') }}" class="transition hover:text-primary-600">اپ فروشنده</a></li>
                    <li><a href="{{ route('shop.home') }}" class="transition hover:text-primary-600">اپ مشتری</a></li>
                </ul>
            </nav>
            <nav aria-label="پیوندهای صفحه">
                <h3 class="text-sm font-extrabold text-slate-700 dark:text-slate-200">دربارهٔ محصول</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-500 dark:text-slate-400">
                    <li><a href="#features" class="transition hover:text-primary-600">امکانات</a></li>
                    <li><a href="#panels" class="transition hover:text-primary-600">پنل‌ها</a></li>
                    <li><a href="#how" class="transition hover:text-primary-600">شروع فروش</a></li>
                    <li><a href="#faq" class="transition hover:text-primary-600">پرسش‌های پرتکرار</a></li>
                </ul>
            </nav>
        </div>
        <div class="border-t border-slate-100 dark:border-slate-800/70">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-slate-400 sm:flex-row sm:px-6 dark:text-slate-500">
                <span>© ۱۴۰۵ {{ $ipAppName }} — تمام حقوق محفوظ است.</span>
                <span class="flex items-center gap-1.5">ساخته‌شده برای فروشندگان اینستاگرام ایران <x-icon name="heart" class="size-3.5 text-pink-500" /></span>
            </div>
        </div>
    </footer>

    {{-- reveal-on-scroll (progressive enhancement) --}}
    <script>
    (function () {
        var els = document.querySelectorAll('.ip-reveal');
        if (!('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('ip-reveal-in'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('ip-reveal-in');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
        els.forEach(function (el) { io.observe(el); });
    })();
    </script>
</body>
</html>
