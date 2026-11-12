{{-- ═══════════════════════════════════════════════════════════
  Admin layout — desktop-first RTL with right sidebar, responsive.
  Sidebar collapses into a drawer under lg breakpoint.
═════════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-panel="admin">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'پنل مدیریت') | {{ \App\Support\Theme::resolve()['app_name'] }}</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.brand-favicon')
    @include('partials.theme-vars')
    {{-- self-contained shell CSS — MUST load BEFORE the bundle so a healthy
         bundle still wins the cascade (identical values, zero visual diff);
         when the bundle half-applies on a device (the client's phone!)
         these plain-CSS rules carry the whole admin shell instead --}}
    @include('partials.admin-shell-css')
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">

    {{-- ───────── Sidebar (right) ───────── --}}
    <aside id="admin-sidebar"
           class="fixed inset-y-0 right-0 z-50 flex w-[260px] translate-x-full flex-col border-l border-slate-200 bg-white transition-transform duration-300 lg:translate-x-0 dark:border-slate-800 dark:bg-slate-900"
           aria-label="منوی مدیریت">

        {{-- brand --}}
        <div id="admin-side-head" class="flex h-16 items-center justify-between border-b border-slate-200 px-5 dark:border-slate-800">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                @if (\App\Support\Branding::logoUrl())
                    <img src="{{ \App\Support\Branding::logoUrl() }}" alt="لوگوی {{ \App\Support\Theme::resolve()['app_name'] }}" class="size-9 rounded-xl object-contain">
                @else
                    <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                        <x-icon name="store" class="size-5" />
                    </span>
                @endif
                <span>
                    <span class="block text-sm font-extrabold leading-4">{{ \App\Support\Theme::resolve()['app_name'] }}</span>
                    <span class="mt-1 block text-[11px] text-slate-400">پنل مدیریت کل</span>
                </span>
            </a>
            <button type="button" data-close-sidebar
                    class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800"
                    aria-label="بستن منو">
                <x-icon name="x" class="size-4" />
            </button>
        </div>

        {{-- system status chip --}}
        <div class="px-5 pt-4">
            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs dark:border-emerald-900 dark:bg-emerald-950/50">
                <span class="flex items-center gap-1.5 font-semibold text-emerald-700 dark:text-emerald-400">
                    <span class="size-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                    سیستم آنلاین
                </span>
                <span class="text-emerald-600/70 dark:text-emerald-500/70">Laravel 13</span>
            </div>
        </div>

        {{-- nav --}}
        <nav class="scroll-slim mt-4 flex-1 space-y-1 overflow-y-auto px-3 pb-4" aria-label="منوی اصلی مدیریت">
            @php
                $pendingSellers = \App\Models\Seller::query()->where('status', 'pending')->count();
                $pendingOrders  = \App\Models\Order::query()->where('status', 'pending')->count();
                $pendingWithdrawals = \App\Models\WalletTransaction::query()->where('type', 'withdrawal')->where('status', 'pending')->count();
                $openTickets = \App\Models\SupportTicket::query()->where('status', 'open')->count();
                $pendingStories = \App\Models\Story::query()->where('status', 'pending')->count();

                // Permission-aware menu: items the current admin cannot open
                // are hidden (super admins always pass).
                $admin = auth('admin')->user();
                $can   = fn (string $route) => $admin?->hasPermission($route) ?? false;

                $navItems = array_values(array_filter([
                    ['route' => 'admin.dashboard',           'icon' => 'chart',       'label' => 'داشبورد',    'match' => 'admin.dashboard',     'badge' => null],
                    ['route' => 'admin.orders',              'icon' => 'bag',         'label' => 'سفارش‌ها',   'match' => 'admin.orders.*',      'badge' => $pendingOrders],
                    ['route' => 'admin.tickets',             'icon' => 'headset',     'label' => 'تیکت پشتیبانی', 'match' => 'admin.tickets.*',  'badge' => $openTickets],
                    ['route' => 'admin.shops',               'icon' => 'store',       'label' => 'فروشگاه‌ها', 'match' => 'admin.shops',         'badge' => null],
                    ['route' => 'admin.categories',          'icon' => 'layers',      'label' => 'دسته‌بندی‌ها', 'match' => 'admin.categories',   'badge' => null],
                    ['route' => 'admin.products',            'icon' => 'package',     'label' => 'محصولات',    'match' => 'admin.products',      'badge' => null],
                    ['route' => 'admin.discounts',           'icon' => 'tag',         'label' => 'کدهای تخفیف','match' => 'admin.discounts',     'badge' => null],
                    ['route' => 'admin.stories',             'icon' => 'zap',         'label' => 'استوری‌ها',  'match' => 'admin.stories',       'badge' => $pendingStories],
                    ['route' => 'admin.slider',              'icon' => 'image',       'label' => 'اسلایدر اصلی', 'match' => 'admin.slider',    'badge' => null],
                    ['route' => 'admin.banners',             'icon' => 'layout-grid', 'label' => 'بنرها',      'match' => 'admin.banners',       'badge' => null],
                    ['route' => 'admin.income',              'icon' => 'coins',       'label' => 'درآمد',      'match' => 'admin.income',        'badge' => null],
                    ['route' => 'admin.payment.logs',        'icon' => 'list',        'label' => 'تراکنش‌های بانکی', 'match' => 'admin.payment.logs', 'badge' => null],
                    ['route' => 'admin.bnpl.settlements',    'icon' => 'calendar-clock', 'label' => 'تسویه اقساطی', 'match' => 'admin.bnpl.settlements', 'badge' => null],
                    ['route' => 'admin.plans',               'icon' => 'crown',       'label' => 'پلن‌های اشتراک', 'match' => 'admin.plans',      'badge' => null],
                    ['route' => 'admin.bump.plans',          'icon' => 'rocket',      'label' => 'طرح‌های نردبان', 'match' => 'admin.bump.plans', 'badge' => null],
                    ['route' => 'admin.subscriptions',       'icon' => 'timer',       'label' => 'اشتراک فروشندگان', 'match' => 'admin.subscriptions', 'badge' => null],
                    ['route' => 'admin.finance',             'icon' => 'file-text',   'label' => 'گزارش مالی', 'match' => 'admin.finance',       'badge' => null],
                    ['route' => 'admin.settlements',         'icon' => 'trending-up', 'label' => 'تسویه‌ها',   'match' => 'admin.settlements',   'badge' => null],
                    ['route' => 'admin.withdrawals',         'icon' => 'wallet',      'label' => 'برداشت‌ها',  'match' => 'admin.withdrawals',   'badge' => $pendingWithdrawals],
                    ['route' => 'admin.locations',           'icon' => 'map-pin',     'label' => 'استان و شهر','match' => 'admin.locations',     'badge' => null],
                    ['route' => 'admin.users.sellers',       'icon' => 'users',       'label' => 'فروشندگان',  'match' => 'admin.users.sellers', 'badge' => $pendingSellers],
                    ['route' => 'admin.users.customers',     'icon' => 'user',        'label' => 'مشتریان',    'match' => 'admin.users.customers','badge' => null],
                    ['route' => 'admin.activity',            'icon' => 'clock',       'label' => 'فعالیت‌های اخیر', 'match' => 'admin.activity',  'badge' => null],
                    ['route' => 'admin.backups',             'icon' => 'database',    'label' => 'پشتیبان‌گیری', 'match' => 'admin.backups',       'badge' => null],
                ], fn ($item) => $can($item['route'])));

                // ── مدیران group (لیست مدیران + نقش‌ها) ──
                $managersItems = array_values(array_filter([
                    ['route' => 'admin.admins',       'icon' => 'user',   'label' => 'لیست مدیران'],
                    ['route' => 'admin.access.roles', 'icon' => 'shield', 'label' => 'نقش‌ها'],
                ], fn ($item) => $can($item['route'])));
                $showManagers = $managersItems !== [];

                // ── تنظیمات: ONE menu item; sections live INSIDE the page ──
                $showSettings = $can('admin.settings')
                    || $can('admin.settings.general')
                    || $can('admin.settings.appearance')
                    || $can('admin.settings.sms')
                    || $can('admin.settings.payment')
                    || $can('admin.settings.stories')
                    || $can('admin.settings.backups');

                // ── برنامه‌نویس: super admin (سازنده) ONLY — never in roles ──
                $showDeveloper = $admin?->isSuper() ?? false;
            @endphp

            @foreach ($navItems as $item)
                @php $isActive = request()->routeIs($item['match']); @endphp
                {{-- State styling lives in .admin-nav-item (app.css) and is driven by
                     data-active — the layout script re-derives it after EVERY
                     wire:navigate, so the highlight can never go stale. --}}
                <a href="{{ route($item['route']) }}" wire:navigate data-close-sidebar
                   data-active="{{ $isActive ? '1' : '0' }}"
                   class="admin-nav-item flex items-center justify-between rounded-xl px-3 py-2.5 text-sm transition">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="{{ $item['icon'] }}" class="admin-nav-icon size-4.5" />
                        {{ $item['label'] }}
                    </span>
                    @if ($item['badge'] > 0)
                        <span class="admin-nav-badge rounded-md px-1.5 py-0.5 text-[10px] font-bold">
                            {{ \App\Support\Fmt::fa($item['badge']) }}
                        </span>
                    @endif
                </a>
            @endforeach

            @php
                $managersOpen  = request()->routeIs('admin.admins') || request()->routeIs('admin.access.*');
                $developerOpen = request()->routeIs('admin.developer.*');
            @endphp

            {{-- managers group (collapsible) — لیست مدیران + نقش‌ها --}}
            @if ($showManagers)
            <div x-data="{ open: {{ $managersOpen ? 'true' : 'false' }} }" class="pt-5">
                <button type="button" x-on:click="open = !open"
                        data-active="{{ $managersOpen ? '1' : '0' }}"
                        class="admin-nav-item flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm transition"
                        :aria-expanded="open" aria-controls="admin-managers-menu">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="users" class="admin-nav-icon size-4.5" />
                        مدیران
                    </span>
                    <x-icon name="chevron-down" class="admin-nav-icon size-3.5 transition-transform duration-300" x-bind:class="open ? 'rotate-180' : ''" />
                </button>

                <div id="admin-managers-menu" x-show="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-cloak class="mt-1 space-y-0.5 border-e-2 border-slate-100 pe-1 dark:border-slate-800">
                    @foreach ($managersItems as $item)
                        @php $isActive = request()->routeIs($item['route']); @endphp
                        <a href="{{ route($item['route']) }}" wire:navigate data-close-sidebar
                           data-active="{{ $isActive ? '1' : '0' }}"
                           class="admin-nav-sub admin-nav-sub--primary ms-4 flex items-center gap-2 rounded-xl px-3 py-2 text-[12.5px] transition">
                            <x-icon name="{{ $item['icon'] }}" class="admin-nav-icon size-3.5" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- settings: SINGLE menu item — sections are inside the page --}}
            @if ($showSettings)
                <a href="{{ route('admin.settings') }}" wire:navigate data-close-sidebar
                   data-active="{{ request()->routeIs('admin.settings') ? '1' : '0' }}"
                   class="admin-nav-item mt-5 flex items-center justify-between rounded-xl px-3 py-2.5 text-sm transition">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="settings" class="admin-nav-icon size-4.5" />
                        تنظیمات
                    </span>
                    <span class="admin-nav-chip rounded-md px-1.5 py-0.5 text-[10px] font-bold">
                        بخش‌ها در صفحه
                    </span>
                </a>
            @endif

            {{-- developer group (collapsible) — super admin ONLY --}}
            @if ($showDeveloper)
            <div x-data="{ open: {{ $developerOpen ? 'true' : 'false' }} }">
                <button type="button" x-on:click="open = !open"
                        data-active="{{ $developerOpen ? '1' : '0' }}"
                        class="admin-nav-item admin-nav-item--amber flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm transition"
                        :aria-expanded="open" aria-controls="admin-developer-menu">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="alert" class="admin-nav-icon size-4.5" />
                        برنامه‌نویس
                    </span>
                    <x-icon name="chevron-down" class="admin-nav-icon size-3.5 transition-transform duration-300" x-bind:class="open ? 'rotate-180' : ''" />
                </button>

                <div id="admin-developer-menu" x-show="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-cloak class="mt-1 space-y-0.5 border-e-2 border-slate-100 pe-1 dark:border-slate-800">
                    <a href="{{ route('admin.developer.settings') }}" wire:navigate data-close-sidebar
                       data-active="{{ request()->routeIs('admin.developer.settings') ? '1' : '0' }}"
                       class="admin-nav-sub admin-nav-sub--amber ms-4 flex items-center gap-2 rounded-xl px-3 py-2 text-[12.5px] transition">
                        <x-icon name="terminal" class="admin-nav-icon size-3.5" />
                        تنظیمات برنامه‌نویس
                    </a>
                    <a href="{{ route('admin.developer.logs') }}" wire:navigate data-close-sidebar
                       data-active="{{ request()->routeIs('admin.developer.logs') ? '1' : '0' }}"
                       class="admin-nav-sub admin-nav-sub--amber ms-4 flex items-center gap-2 rounded-xl px-3 py-2 text-[12.5px] transition">
                        <x-icon name="file-text" class="admin-nav-icon size-3.5" />
                        لاگ سیستم
                    </a>
                </div>
            </div>
            @endif
        </nav>

        {{-- manager card --}}
        <div class="border-t border-slate-200 p-4 dark:border-slate-800">
            <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/60">
                <div class="flex items-center gap-2.5">
                    <span class="ip-avatar grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-bold text-white">
                        {{ mb_substr(auth('admin')->user()?->name ?? 'م', 0, 1) }}
                    </span>
                    <span>
                        <span class="block text-xs font-bold">{{ auth('admin')->user()?->name ?? 'مدیر' }}</span>
                        <span class="block text-[11px] text-slate-400" dir="ltr">{{ auth('admin')->user()?->email ?? 'admin@caffeinet.ir' }}</span>
                    </span>
                </div>
                <div class="flex items-center gap-1.5">
                    @if (\Illuminate\Support\Facades\Route::has('admin.profile'))
                        <a href="{{ route('admin.profile') }}" wire:navigate
                           class="grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-400"
                           aria-label="پروفایل مدیر" title="پروفایل مدیر">
                            <x-icon name="settings" class="size-4" />
                        </a>
                    @endif
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="grid size-8 place-items-center rounded-lg bg-primary-600 text-white shadow-md shadow-primary-600/30 transition hover:bg-primary-700" aria-label="خروج از حساب">
                            <x-icon name="log-out" class="size-4" />
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- sidebar overlay (mobile) — shown while <html> carries .admin-drawer-open --}}
    <div id="admin-sidebar-overlay"
         class="fixed inset-0 z-40 bg-slate-950/40 opacity-0 pointer-events-none transition-opacity duration-300 backdrop-blur-sm lg:hidden"
         aria-hidden="true"></div>

    {{-- ───────── Main area ─────────
         CHAT mode (ticket thread): the page NEVER scrolls — topbar is
         pinned, <main> fills the rest of the viewport and the ticket
         page owns an inner scroller (messages only) + a pinned composer.
         The footer is dropped in this mode so nothing can overflow. ───────── --}}
    @php $chat = $chat ?? false; @endphp
    <div id="admin-shell" class="flex flex-col lg:mr-[260px] {{ $chat ? 'h-dvh overflow-hidden' : 'min-h-screen' }}">
        {{-- topbar --}}
        <header id="admin-topbar" class="sticky top-0 z-30 border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            {{-- NOTE: NO backdrop-blur here on purpose — backdrop-filter makes
                 the header a CONTAINING BLOCK for position:fixed descendants,
                 which would shrink the bell dropdown's full-screen close
                 backdrop (fixed inset-0) to the 64px header strip, so tapping
                 «around» the dropdown on a phone never closed it. --}}
            <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" data-open-sidebar
                            class="grid size-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800"
                            aria-label="باز کردن منو">
                        <x-icon name="menu" class="size-5" />
                    </button>
                    <div>
                        <h1 class="text-base font-extrabold leading-5">@yield('title', 'پنل مدیریت')</h1>
                        @unless ($chat)
                            <div class="ip-crumb mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-400">
                                <a href="/" class="transition hover:text-primary-600">{{ \App\Support\Theme::resolve()['app_name'] }}</a>
                                <x-icon name="chevron-left" class="size-3" />
                                <span>@yield('breadcrumb', 'مدیریت کل — تنظیمات')</span>
                            </div>
                        @endunless
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="hidden items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700 sm:flex dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <span class="size-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                        سیستم آنلاین
                    </span>
                    <x-theme-toggle class="size-10 text-slate-500" />
                    @livewire('admin.notifications.notifications-bell')
                    <button type="button" class="grid size-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="جستجو">
                        <x-icon name="search" class="size-5" />
                    </button>
                </div>
            </div>
        </header>

        {{-- page content — chat mode: <main> is a fixed-height flex column
             (NO scroll here); the ticket page owns the inner scroller. --}}
        <main class="{{ $chat ? 'flex min-h-0 flex-1 flex-col overflow-hidden p-0 sm:p-0' : 'flex-1 p-4 sm:p-6' }}">
            @yield('content')
        </main>

        @unless ($chat)
            <footer class="mt-auto border-t border-slate-200 px-6 py-4 text-center text-xs text-slate-400 dark:border-slate-800">
                © ۱۴۰۵ کافیت آنلاین — پنل مدیریت کل · <span dir="ltr">Laravel 13.39.0</span> · نسخهٔ دیپلوی: <span dir="ltr" class="font-bold text-slate-500 dark:text-slate-400">v{{ \App\Support\Version::code() }}</span>
            </footer>
        @endunless
    </div>

    @once
        {{-- drawer state CSS moved to partials/admin-shell-css (self-contained
             shell — survives any partial Tailwind bundle loss on a device) --}}
        <script>
            // admin sidebar drawer (mobile) — IIFE so a wire:navigate script
            // re-run can never hit a "already declared" SyntaxError
            (function () {
                const open  = () => {
                    document.documentElement.classList.add('admin-drawer-open');
                    // the active item must be in view the moment the drawer opens
                    setTimeout(syncAdminNav, 60);
                };
                const close = () => document.documentElement.classList.remove('admin-drawer-open');

                document.addEventListener('click', (e) => {
                    if (e.target.closest('[data-open-sidebar]')) {
                        open();
                    } else if (e.target.closest('[data-close-sidebar]')) {
                        close();
                    }
                });

                // tapping the dim overlay closes the drawer
                document.addEventListener('pointerdown', (e) => {
                    if (e.target.id === 'admin-sidebar-overlay') {
                        close();
                    }
                });

                /* ── active-item sync (works around stale sidebar states after
                   wire:navigate / back-forward restores): re-derive which menu
                   item matches the CURRENT path, flip data-active, open the
                   parent group, and keep the item inside the scrolled menu. ── */
                function syncAdminNav() {
                    const nav = document.querySelector('#admin-sidebar nav');
                    if (!nav) return;

                    const here = window.location.pathname;
                    let active = null;

                    nav.querySelectorAll('a[data-active]').forEach((a) => {
                        let target;
                        try { target = new URL(a.getAttribute('href'), window.location.origin).pathname; }
                        catch (e) { return; }

                        // exact page, or a detail page INSIDE the section
                        // (/admin/orders vs /admin/orders/5) — never a bare
                        // prefix, and /admin itself matches ONLY the dashboard
                        const on = here === target
                            || (target !== '/admin' && here.startsWith(target.endsWith('/') ? target : target + '/'));

                        a.dataset.active = on ? '1' : '0';
                        if (on) active = a;
                    });

                    if (! active) return;

                    // a collapsed group whose child is active must open up
                    const group = active.closest('[x-data]');
                    if (group && window.Alpine) {
                        try { Alpine.$data(group).open = true; } catch (e) { /* not a group */ }
                    }

                    // keep the active row visible inside the scrolled menu —
                    // scroll ONLY the nav container (scrollIntoView would also
                    // drag the whole page)
                    const box = nav.getBoundingClientRect();
                    const row = active.getBoundingClientRect();
                    if (row.top < box.top || row.bottom > box.bottom) {
                        const target = nav.scrollTop + (row.top + row.bottom) / 2 - (box.top + box.bottom) / 2;
                        nav.scrollTo({ top: Math.max(0, target), behavior: 'smooth' });
                    }
                }

                window.syncAdminNav = syncAdminNav;
                syncAdminNav();
                // Alpine (and the collapsible groups) come alive with the
                // Livewire bundle at the end of <body> — re-run after load
                window.addEventListener('load', syncAdminNav);
                document.addEventListener('livewire:navigated', () => {
                    close();
                    syncAdminNav();
                });
            })();
        </script>
    @endonce

    @livewireScripts

    {{-- Web-Push registration helper for the admin notifications bell —
         lives in the LAYOUT (not the bell component) because Livewire
         components may only have one root element. --}}
    @once
        <script>
            (function () {
                function urlB64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - base64String.length % 4) % 4);
                    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                    const raw = atob(base64);
                    const output = new Uint8Array(raw.length);
                    for (let i = 0; i < output.length; ++i) output[i] = raw.charCodeAt(i);
                    return output;
                }

                window.__ipVapidKey = @js(\App\Support\WebPusher::publicKey());
                window.__ipPushRoute = @js(route('admin.notifications.subscribe'));

                function csrf() {
                    return document.querySelector('meta[name="csrf-token"]')?.content
                        || window.livewireScriptConfig?.csrf
                        || '';
                }

                /* Save the subscription on the server (idempotent upsert) and
                   flip every bell/page to the green «فعال است» state. */
                async function upsertSubscription(sub) {
                    const json = sub.toJSON();

                    const res = await fetch(window.__ipPushRoute, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            endpoint: json.endpoint,
                            keys: json.keys,
                            device_label: navigator.userAgent.slice(0, 100),
                        }),
                    });

                    if (!res.ok) throw new Error('push upsert failed: ' + res.status);

                    window.dispatchEvent(new CustomEvent('ip-push-subscribed'));
                }

                window.__ipEnablePush = async function () {
                    try {
                        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !window.Notification) {
                            return;
                        }

                        const perm = await Notification.requestPermission();
                        if (perm !== 'granted') {
                            if (perm === 'denied') {
                                window.dispatchEvent(new CustomEvent('ip-push-state', { detail: { state: 'denied' } }));
                            }
                            return;
                        }

                        const reg = await navigator.serviceWorker.register('/sw.js');
                        await navigator.serviceWorker.ready;

                        let sub = await reg.pushManager.getSubscription();
                        if (!sub) {
                            sub = await reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: urlB64ToUint8Array(window.__ipVapidKey || ''),
                            });
                        }

                        await upsertSubscription(sub);
                    } catch (e) {
                        console.warn('push subscribe failed', e);
                    }
                };

                /* Restore the push state on every page load / wire:navigate —
                   the bell is a fresh Livewire component each time (always
                   «default»), but the BROWSER remembers the subscription, so
                   ask it and sync the row back to the server (heals lost
                   rows too). Without this the green «نوتیفیکیشن دستگاه
                   فعال است» chip vanished on every refresh. */
                window.__ipSyncPush = async function () {
                    if (Date.now() - (window.__ipLastPushSync || 0) < 1500) return;
                    window.__ipLastPushSync = Date.now();

                    try {
                        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !window.Notification) {
                            window.dispatchEvent(new CustomEvent('ip-push-state', { detail: { state: 'unsupported' } }));
                            return;
                        }

                        if (Notification.permission === 'denied') {
                            window.dispatchEvent(new CustomEvent('ip-push-state', { detail: { state: 'denied' } }));
                            return;
                        }

                        if (Notification.permission !== 'granted') return;

                        let reg = await navigator.serviceWorker.getRegistration();
                        if (!reg) reg = await navigator.serviceWorker.register('/sw.js');
                        if (!reg) return;

                        const sub = await reg.pushManager.getSubscription();
                        if (!sub) return; /* this device never subscribed — keep the enable button */

                        await upsertSubscription(sub);
                    } catch (e) {
                        /* restore is best-effort — never break the panel */
                    }
                };

                /* livewire:navigated also fires on the FIRST load, so one
                   hook covers both full reloads and SPA navigations (the
                   bell re-mounts as «default» on each one). */
                if (!window.__ipPushSyncHooked) {
                    window.__ipPushSyncHooked = true;
                    document.addEventListener('livewire:navigated', () => window.__ipSyncPush && window.__ipSyncPush());
                }
                window.__ipSyncPush();
            })();
        </script>
    @endonce

    @include('partials.panel-toast')

    {{-- ───────── stylesheet-health probe (v0.17.19) ─────────
         The client's phone kept rendering the panel with a PARTIALLY
         applied Tailwind bundle (host assets verified byte-identical;
         the loss happens on that device). The self-contained shell CSS
         above keeps the panel correct either way — this probe makes the
         failure VISIBLE: if a live bundle is not actually applying
         (hidden → display:none fails), an amber banner tells the user
         exactly what to do and the console logs the sheet stats. ───────── --}}
    <div id="ip-css-probe-banner" role="alert">
        ⚠️ بارگذاری کامل استایل‌های پنل در این مرورگر انجام نشد — ظاهر ممکن است ناقص باشد.
        لطفاً یک‌بار «کش مرورگر» را پاک کنید (تنظیمات ← حریم خصوصی ← پاک کردن داده‌های مرورگر)
        یا Chrome را به آخرین نسخه به‌روزرسانی کنید. کد خطا: <span dir="ltr">CSS-SHELL</span>
        <button type="button" onclick="this.parentElement.remove()">متوجه شدم</button>
    </div>
    @once
        <script>
            (function () {
                function probeCssHealth() {
                    try {
                        var probe = document.createElement('div');
                        probe.className = 'hidden';
                        probe.style.cssText = 'position:absolute;left:-9999px;top:0;width:0;height:0;';
                        document.body.appendChild(probe);
                        var ok = getComputedStyle(probe).display === 'none';
                        probe.remove();

                        if (ok) return;

                        document.getElementById('ip-css-probe-banner')
                            ?.classList.add('ip-css-broken');

                        var rules = 0;
                        for (var i = 0; i < document.styleSheets.length; i++) {
                            try { rules += document.styleSheets[i].cssRules.length; } catch (e) {}
                        }
                        console.warn('[InstaShop] admin Tailwind bundle did NOT apply on this device — shell CSS is carrying the panel. stylesheets='
                            + document.styleSheets.length + ' rules=' + rules);
                    } catch (e) { /* never break the panel for a diagnostic */ }
                }

                if (document.readyState === 'complete') {
                    setTimeout(probeCssHealth, 300);
                } else {
                    window.addEventListener('load', function () { setTimeout(probeCssHealth, 300); });
                }
                /* wire:navigate swaps the document — re-check after each one */
                document.addEventListener('livewire:navigated', function () {
                    setTimeout(probeCssHealth, 300);
                });
            })();
        </script>
    @endonce



    @stack('scripts')
</body>
</html>
