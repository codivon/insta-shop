{{-- ═══════════════════════════════════════════════════════════
  Seller layout — mobile-app shell (phone width on desktop),
  RTL, sticky header + bottom tab bar + safe-area support.
═════════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-panel="seller">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2563EB">
    <title>@yield('title', 'اپ فروشنده') | InstaShop</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.theme-vars')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-200 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">

    <div class="relative mx-auto flex min-h-screen w-full max-w-[430px] flex-col bg-slate-50 shadow-2xl sm:my-0 dark:bg-slate-950 lg:my-6 lg:min-h-[calc(100vh-3rem)] lg:rounded-[2.5rem] lg:overflow-hidden lg:border lg:border-slate-300 dark:lg:border-slate-800">

        {{-- ───────── App header (sticky) ───────── --}}
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 pt-[env(safe-area-inset-top)] backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
            @yield('header')
        </header>

        {{-- ───────── Scrollable content ───────── --}}
        <main class="scroll-slim flex-1 overflow-y-auto pb-28">
            @yield('content')
        </main>

        {{-- ───────── Bottom tab bar ───────── --}}
        @php
            $sellerTabs = [
                ['route' => 'seller.home',        'icon' => 'home',   'label' => 'خانه'],
                ['route' => 'seller.shop',        'icon' => 'store',  'label' => 'فروشگاه من'],
                ['route' => 'seller.orders',      'icon' => 'bag',    'label' => 'سفارش‌ها'],
                ['route' => 'seller.wallet',      'icon' => 'wallet', 'label' => 'کیف پول'],
                ['route' => 'seller.profile',     'icon' => 'user',   'label' => 'پروفایل'],
            ];
        @endphp
        <nav class="fixed bottom-0 z-30 w-full max-w-[430px] border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/95 lg:absolute lg:bottom-0"
             aria-label="تب‌های اصلی فروشنده">
            <div class="grid grid-cols-5">
                @foreach ($sellerTabs as $tab)
                    @php
                        $active = request()->routeIs($tab['route']);
                        // routes that don't exist yet fall back gracefully
                        $href = in_array($tab['route'], ['seller.home']) ? route($tab['route']) : '#';
                    @endphp
                    <a href="{{ $href }}" aria-current="{{ $active ? 'page' : 'false' }}"
                       class="flex flex-col items-center gap-1 py-2.5 text-[10px] font-semibold transition
                              {{ $active ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}">
                        <span class="grid h-8 w-14 place-items-center rounded-full transition {{ $active ? 'bg-primary-100 dark:bg-primary-950' : '' }}">
                            <x-icon name="{{ $tab['icon'] }}" class="size-5" />
                        </span>
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>
    </div>

    @stack('scripts')
</body>
</html>
