{{-- ═══════════════════════════════════════════════════════════
  Storefront layout — PUBLIC seller shop page (no login).
  Reachable at دامنه/shop/{آیدی اینستا} and directly at
  دامنه/{آیدی اینستا} (web.php fallback). Full-width modern
  e-commerce front, RTL, dark-mode + brand-aware.
  The sticky shop header itself lives inside the Livewire
  component (it needs shop data), same pattern as the panels.
═══════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-panel="storefront">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="{{ $ipMetaDescription ?? 'فروشگاه اینترنتی InstaShop — خرید آسان از فروشگاه‌های اینستاگرامی معتبر' }}">
    <title>@yield('title', 'فروشگاه') | {{ \App\Support\Theme::resolve()['app_name'] }}</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.brand-favicon')
    @include('partials.theme-vars')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">

    {{-- ═══════════════ PAGE ═══════════════ --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- ═══════════════ FOOTER (sticks to bottom on short pages) ═══════════════ --}}
    <footer class="mt-auto border-t border-slate-200/80 bg-white/70 pb-[env(safe-area-inset-bottom)] dark:border-slate-800 dark:bg-slate-950/70">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-5 text-center sm:flex-row sm:px-6 sm:text-right">
            <div class="flex items-center gap-1.5 text-[11px] leading-6 text-slate-400">
                <x-icon name="shield" class="size-3.5" />
                خرید امن با پرداخت آنلاین و ضمانت ارسال
            </div>
            <a href="/" class="inline-flex items-center gap-1.5 text-[11px] font-extrabold text-primary-600 transition hover:text-primary-700 dark:text-primary-400">
                <x-icon name="store" class="size-3.5" />
                فروشگاه اینستاگرامی خودت را بساز
            </a>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
