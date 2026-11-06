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

            {{-- nav is DERIVED from the enabled anchor-sections (auto-hides
                 a section that the admin turned off, follows admin order) --}}
            @php($landingNavLinks = \App\Support\LandingContent::navLinks())
            <nav class="hidden items-center gap-7 text-sm font-medium text-slate-600 dark:text-slate-300 md:flex" aria-label="منوی اصلی">
                @foreach ($landingNavLinks as $navLink)
                    <a href="{{ $navLink['href'] }}" class="transition hover:text-primary-600 dark:hover:text-primary-400">{{ $navLink['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                <x-theme-toggle />
                <a href="{{ \App\Support\LandingContent::section('hero')['cta2_href'] ?? route('shop.home') }}"
                   class="hidden rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 sm:inline-flex">
                    ورود به فروشگاه
                </a>

                {{-- mobile menu (no-JS: details/summary) --}}
                <details class="relative md:hidden">
                    <summary class="grid size-10 cursor-pointer list-none place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 [&::-webkit-details-marker]:hidden" aria-label="باز کردن منو">
                        <x-icon name="menu" class="size-5" />
                    </summary>
                    <div class="absolute left-0 top-12 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                        @foreach ($landingNavLinks as $navLink)
                            <a href="{{ $navLink['href'] }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-primary-600 dark:text-slate-300 dark:hover:bg-slate-800">{{ $navLink['label'] }}</a>
                        @endforeach
                        <a href="{{ \App\Support\LandingContent::section('hero')['cta2_href'] ?? route('shop.home') }}" class="mt-1 block rounded-xl bg-primary-600 px-3 py-2.5 text-center text-sm font-bold text-white">ورود به فروشگاه</a>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <main>
        {{-- every main section renders from the admin-managed ORDER —
             each partial is self-contained and reads landing.content.* --}}
        @foreach (\App\Support\LandingContent::orderedEnabled() as $sectionKey)
            @include('landing.sections.'.$sectionKey, ['c' => \App\Support\LandingContent::section($sectionKey)])
        @endforeach
    </main>

    {{-- ═══════════════════ FOOTER (always last — sticky via mt-auto) ═══════════════════ --}}
    @include('landing.sections.footer', ['c' => \App\Support\LandingContent::section('footer')])

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
