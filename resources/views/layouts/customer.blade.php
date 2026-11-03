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
    @include('partials.pwa-head', ['role' => 'customer'])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ip-fit-dvh flex flex-col bg-slate-200 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">

    {{-- App shell: exactly viewport height on every device → the header and
         tab bar stay pinned and ONLY <main> scrolls (also on real phones).
         .ip-app-shell (app.css) sets 100vh WITH a 100dvh override — on the
         rare phone without the dvh unit the vh fallback keeps the shell full
         height instead of letting it collapse to content height.
         max-lg:min-h-dvh is the LAST line of defense: even if a broken
         morph / stale-tab repair rebuilds this subtree without app.css,
         the shell can never shrink under the viewport on phones. --}}
    <div class="ip-app-shell max-lg:min-h-dvh relative mx-auto flex w-full max-w-[430px] flex-col overflow-hidden bg-slate-50 shadow-2xl dark:bg-slate-950 lg:my-6 lg:h-[calc(100vh-3rem)] lg:min-h-0 lg:rounded-[2.5rem] lg:overflow-hidden lg:border lg:border-slate-300 dark:lg:border-slate-800">

        {{-- ───────── App header (pinned top) ───────── --}}
        <header class="relative z-30 shrink-0 border-b border-slate-200/80 bg-white/90 pt-[env(safe-area-inset-top)] backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
            @yield('header')
        </header>

        {{-- ───────── Scrollable content ─────────
              On FOCUSED pages (single product, ticket chat) <main> does
              NOT scroll — the page component owns an inner scroller and
              pins its own bottom bar (buy bar / reply composer), so
              mobile overscroll can never drag the bar up. ───────── --}}
        @php
            $focusedShell = request()->routeIs(['shop.product.show', 'shop.storefront.product', 'shop.support.show']);
            $showTabBar   = ! $focusedShell;
        @endphp
        <main class="min-h-0 flex-1 overscroll-contain {{ $focusedShell ? 'flex flex-col overflow-hidden' : 'scroll-slim overflow-y-auto' }} {{ $showTabBar ? 'max-lg:pb-[calc(80px_+_env(safe-area-inset-bottom))]' : '' }}">
            @yield('content')
        </main>

        {{-- ───────── Bottom tab bar ─────────
              Hidden on focused pages (single product / ticket chat):
              there the pinned buy bar or reply composer takes over.

              DOUBLE ANCHORING (the tab bar can never float mid-page):
              • sticky bottom-0 → while the shell is healthy the bar is the
                last flex child, glued to the shell bottom = viewport bottom.
              • max-lg:fixed bottom-0 on phones → the bar is VIEWPORT-anchored
                (centered like the shell): even if a broken morph, a stale-tab
                repair or any DOM rearrangement moves the node elsewhere in
                the document, CSS still paints it at the bottom of the screen.
                <main> carries a matching safe-area padding so nothing hides
                behind the bar. ───────── --}}
        @php
            $customerTabs = [
                ['route' => 'shop.home',      'icon' => 'home',   'label' => 'خانه'],
                ['route' => 'shop.categories','icon' => 'package','label' => 'دسته‌بندی'],
                ['route' => 'shop.cart',      'icon' => 'cart',   'label' => 'سبد خرید'],
                ['route' => 'shop.favorites', 'icon' => 'heart',  'label' => 'علاقه‌مندی'],
                ['route' => 'shop.profile',   'icon' => 'user',   'label' => 'پروفایل'],
            ];

            $cartBadgeCount = 0;
            $cartBadgeCart  = \App\Models\Cart::forVisitor(auth('customer')->user());

            if ($cartBadgeCart) {
                $cartBadgeCount = $cartBadgeCart->countDistinct();
            }
        @endphp
        @if ($showTabBar)
            <nav class="sticky bottom-0 z-30 shrink-0 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/95 max-lg:fixed max-lg:inset-x-0 max-lg:bottom-0 max-lg:z-40 max-lg:mx-auto max-lg:w-full max-lg:max-w-[430px] max-lg:rounded-t-none"
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
                            <span id="cart-badge" data-cart-badge
                                  class="absolute right-5 top-0.5 grid min-w-4 place-items-center rounded-full bg-primary-600 px-1 text-[9px] font-bold text-white shadow {{ $cartBadgeCount === 0 ? 'hidden' : '' }}"
                                  data-count="{{ $cartBadgeCount }}">{{ \App\Support\Fmt::toFa($cartBadgeCount) }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
            </nav>
        @endif
    </div>

    @include('partials.panel-toast')
    @include('partials.pwa-sheets', ['role' => 'customer'])

    @include('partials.build-check')

    @stack('scripts')
</body>
</html>
