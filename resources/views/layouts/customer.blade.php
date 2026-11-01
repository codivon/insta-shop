{{-- ═══════════════════════════════════════════════════════════
  Customer layout — mobile-app shell (phone width on desktop),
  RTL, sticky header + bottom tab bar + safe-area support.
═════════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-panel="customer">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#D62976">
    <title>@yield('title', 'فروشگاه') | {{ \App\Support\Theme::resolve()['app_name'] }}</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.brand-favicon')
    @include('partials.theme-vars')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-200 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">

    {{-- App shell: exactly viewport height on every device → the header and
         tab bar stay pinned and ONLY <main> scrolls (also on real phones). --}}
    <div class="relative mx-auto flex h-dvh w-full max-w-[430px] flex-col overflow-hidden bg-slate-50 shadow-2xl dark:bg-slate-950 lg:my-6 lg:h-[calc(100vh-3rem)] lg:min-h-0 lg:rounded-[2.5rem] lg:overflow-hidden lg:border lg:border-slate-300 dark:lg:border-slate-800">

        {{-- ───────── App header (pinned top) ───────── --}}
        <header class="relative z-30 shrink-0 border-b border-slate-200/80 bg-white/90 pt-[env(safe-area-inset-top)] backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
            @yield('header')
        </header>

        {{-- ───────── Scrollable content (the only scroll container) ───────── --}}
        <main class="scroll-slim min-h-0 flex-1 overflow-y-auto">
            @yield('content')
        </main>

        {{-- ───────── Bottom tab bar ─────────
              Hidden on the single product page: there the fixed
              add-to-cart bar (inside the page component) takes over. ───────── --}}
        @php
            $customerTabs = [
                ['route' => 'shop.home',      'icon' => 'home',   'label' => 'خانه'],
                ['route' => 'shop.categories','icon' => 'package','label' => 'دسته‌بندی'],
                ['route' => 'shop.cart',      'icon' => 'cart',   'label' => 'سبد خرید'],
                ['route' => 'shop.favorites', 'icon' => 'heart',  'label' => 'علاقه‌مندی'],
                ['route' => 'shop.profile',   'icon' => 'user',   'label' => 'پروفایل'],
            ];

            $cartBadgeCount = 0;
            if ($customer = auth('customer')->user()) {
                $cartBadgeCount = \App\Models\CartItem::query()
                    ->whereHas('cart', fn ($q) => $q->where('customer_id', $customer->id))
                    ->count();
            }

            $showTabBar = ! request()->routeIs('shop.product.show');
        @endphp
        @if ($showTabBar)
            <nav class="shrink-0 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/95"
                 aria-label="تب‌های اصلی فروشگاه">
            <div class="grid grid-cols-5">
                @foreach ($customerTabs as $tab)
                    @php
                        $active = request()->routeIs($tab['route'], $tab['route'].'.*');
                        $href = \Illuminate\Support\Facades\Route::has($tab['route']) ? route($tab['route']) : '#';
                    @endphp
                    <a href="{{ $href }}" @if ($href !== '#') wire:navigate @endif
                       aria-current="{{ $active ? 'page' : 'false' }}"
                       class="relative flex flex-col items-center gap-1 py-2.5 text-[10px] font-semibold transition
                              {{ $active ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}">
                        <span class="grid h-8 w-14 place-items-center rounded-full transition {{ $active ? 'bg-primary-100 dark:bg-primary-950' : '' }}">
                            <x-icon name="{{ $tab['icon'] }}" class="size-5" />
                        </span>
                        {{ $tab['label'] }}
                        @if ($tab['route'] === 'shop.cart')
                            <span id="cart-badge"
                                  class="absolute right-5 top-0.5 grid min-w-4 place-items-center rounded-full bg-primary-600 px-1 text-[9px] font-bold text-white shadow {{ $cartBadgeCount === 0 ? 'hidden' : '' }}"
                                  data-count="{{ $cartBadgeCount }}">{{ \App\Support\Fmt::toFa($cartBadgeCount) }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
            </nav>
        @endif
    </div>

    @stack('scripts')
</body>
</html>
