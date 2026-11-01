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
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">

    {{-- ───────── Sidebar (right) ───────── --}}
    <aside id="admin-sidebar"
           class="fixed inset-y-0 right-0 z-50 flex w-72 translate-x-full flex-col border-l border-slate-200 bg-white transition-transform duration-300 lg:translate-x-0 dark:border-slate-800 dark:bg-slate-900"
           aria-label="منوی مدیریت">

        {{-- brand --}}
        <div class="flex h-16 items-center justify-between border-b border-slate-200 px-5 dark:border-slate-800">
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

                // Permission-aware menu: items the current admin cannot open
                // are hidden (super admins always pass).
                $admin = auth('admin')->user();
                $can   = fn (string $route) => $admin?->hasPermission($route) ?? false;

                $navItems = array_values(array_filter([
                    ['route' => 'admin.dashboard',           'icon' => 'chart',       'label' => 'داشبورد',    'match' => 'admin.dashboard',     'badge' => null],
                    ['route' => 'admin.orders',              'icon' => 'bag',         'label' => 'سفارش‌ها',   'match' => 'admin.orders.*',      'badge' => $pendingOrders],
                    ['route' => 'admin.shops',               'icon' => 'store',       'label' => 'فروشگاه‌ها', 'match' => 'admin.shops',         'badge' => null],
                    ['route' => 'admin.products',            'icon' => 'package',     'label' => 'محصولات',    'match' => 'admin.products',      'badge' => null],
                    ['route' => 'admin.users.sellers',       'icon' => 'users',       'label' => 'فروشندگان',  'match' => 'admin.users.sellers', 'badge' => $pendingSellers],
                    ['route' => 'admin.users.customers',     'icon' => 'user',        'label' => 'مشتریان',    'match' => 'admin.users.customers','badge' => null],
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
                    || $can('admin.settings.payment');

                // ── برنامه‌نویس: super admin (سازنده) ONLY — never in roles ──
                $showDeveloper = $admin?->isSuper() ?? false;
            @endphp

            @foreach ($navItems as $item)
                @php $isActive = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate
                   class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm transition
                          {{ $isActive
                              ? 'bg-primary-600 font-bold text-white shadow-md shadow-primary-600/25'
                              : 'font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="{{ $item['icon'] }}" class="size-4.5 {{ $isActive ? 'text-white' : 'text-slate-400' }}" />
                        {{ $item['label'] }}
                    </span>
                    @if ($item['badge'] > 0)
                        <span class="rounded-md px-1.5 py-0.5 text-[10px] font-bold
                                     {{ $isActive ? 'bg-white text-primary-700' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400' }}">
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
                        class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition
                               {{ $managersOpen
                                   ? 'bg-primary-600 font-bold text-white shadow-md shadow-primary-600/25'
                                   : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}"
                        :aria-expanded="open" aria-controls="admin-managers-menu">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="users" class="size-4.5 {{ $managersOpen ? 'text-white' : 'text-slate-400' }}" />
                        مدیران
                    </span>
                    <x-icon name="chevron-down" class="size-3.5 transition-transform duration-300 {{ $managersOpen ? 'text-white' : 'text-slate-400' }}" x-bind:class="open ? 'rotate-180' : ''" />
                </button>

                <div id="admin-managers-menu" x-show="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-cloak class="mt-1 space-y-0.5 border-e-2 border-slate-100 pe-1 dark:border-slate-800">
                    @foreach ($managersItems as $item)
                        @php $isActive = request()->routeIs($item['route']); @endphp
                        <a href="{{ route($item['route']) }}" wire:navigate
                           class="ms-4 flex items-center gap-2 rounded-xl px-3 py-2 text-[12.5px] transition
                                  {{ $isActive
                                      ? 'bg-primary-50 font-extrabold text-primary-700 dark:bg-primary-950/50 dark:text-primary-300'
                                      : 'font-medium text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                            <x-icon name="{{ $item['icon'] }}" class="size-3.5 {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- settings: SINGLE menu item — sections are inside the page --}}
            @if ($showSettings)
                <a href="{{ route('admin.settings') }}" wire:navigate
                   class="mt-5 flex items-center justify-between rounded-xl px-3 py-2.5 text-sm transition
                          {{ request()->routeIs('admin.settings')
                              ? 'bg-primary-600 font-bold text-white shadow-md shadow-primary-600/25'
                              : 'font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="settings" class="size-4.5 {{ request()->routeIs('admin.settings') ? 'text-white' : 'text-slate-400' }}" />
                        تنظیمات
                    </span>
                    <span class="rounded-md px-1.5 py-0.5 text-[10px] font-bold {{ request()->routeIs('admin.settings') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500' }}">
                        بخش‌ها در صفحه
                    </span>
                </a>
            @endif

            {{-- developer group (collapsible) — super admin ONLY --}}
            @if ($showDeveloper)
            <div x-data="{ open: {{ $developerOpen ? 'true' : 'false' }} }">
                <button type="button" x-on:click="open = !open"
                        class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition
                               {{ $developerOpen
                                   ? 'bg-amber-500 font-bold text-white shadow-md shadow-amber-500/25'
                                   : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}"
                        :aria-expanded="open" aria-controls="admin-developer-menu">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="alert" class="size-4.5 {{ $developerOpen ? 'text-white' : 'text-amber-500' }}" />
                        برنامه‌نویس
                    </span>
                    <x-icon name="chevron-down" class="size-3.5 transition-transform duration-300 {{ $developerOpen ? 'text-white' : 'text-slate-400' }}" x-bind:class="open ? 'rotate-180' : ''" />
                </button>

                <div id="admin-developer-menu" x-show="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-cloak class="mt-1 space-y-0.5 border-e-2 border-slate-100 pe-1 dark:border-slate-800">
                    <a href="{{ route('admin.developer.settings') }}" wire:navigate
                       class="ms-4 flex items-center gap-2 rounded-xl px-3 py-2 text-[12.5px] transition
                              {{ request()->routeIs('admin.developer.settings')
                                  ? 'bg-amber-50 font-extrabold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300'
                                  : 'font-medium text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                        <x-icon name="terminal" class="size-3.5 {{ request()->routeIs('admin.developer.settings') ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400' }}" />
                        تنظیمات برنامه‌نویس
                    </a>
                    <a href="{{ route('admin.developer.logs') }}" wire:navigate
                       class="ms-4 flex items-center gap-2 rounded-xl px-3 py-2 text-[12.5px] transition
                              {{ request()->routeIs('admin.developer.logs')
                                  ? 'bg-amber-50 font-extrabold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300'
                                  : 'font-medium text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                        <x-icon name="file-text" class="size-3.5 {{ request()->routeIs('admin.developer.logs') ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400' }}" />
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
                    <span class="grid size-9 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-bold text-white">
                        {{ mb_substr(auth('admin')->user()?->name ?? 'م', 0, 1) }}
                    </span>
                    <span>
                        <span class="block text-xs font-bold">{{ auth('admin')->user()?->name ?? 'مدیر' }}</span>
                        <span class="block text-[11px] text-slate-400" dir="ltr">{{ auth('admin')->user()?->email ?? 'admin@caffeinet.ir' }}</span>
                    </span>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="grid size-8 place-items-center rounded-lg bg-primary-600 text-white shadow-md shadow-primary-600/30 transition hover:bg-primary-700" aria-label="خروج از حساب">
                        <x-icon name="log-out" class="size-4" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- sidebar overlay (mobile) --}}
    <div id="admin-sidebar-overlay" class="fixed inset-0 z-40 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden"></div>

    {{-- ───────── Main area ───────── --}}
    <div class="flex min-h-screen flex-col lg:mr-72">
        {{-- topbar --}}
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
            <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" data-open-sidebar
                            class="grid size-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800"
                            aria-label="باز کردن منو">
                        <x-icon name="menu" class="size-5" />
                    </button>
                    <div>
                        <h1 class="text-base font-extrabold leading-5">@yield('title', 'پنل مدیریت')</h1>
                        <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-400">
                            <a href="/" class="transition hover:text-primary-600">{{ \App\Support\Theme::resolve()['app_name'] }}</a>
                            <x-icon name="chevron-left" class="size-3" />
                            <span>@yield('breadcrumb', 'مدیریت کل — تنظیمات')</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="hidden items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700 sm:flex dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <span class="size-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                        سیستم آنلاین
                    </span>
                    <x-theme-toggle class="size-10 text-slate-500" />
                    <button type="button" class="relative grid size-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="اعلان‌ها">
                        <x-icon name="bell" class="size-5" />
                        <span class="absolute right-2.5 top-2.5 size-2 rounded-full bg-primary-500 ring-2 ring-white dark:ring-slate-900"></span>
                    </button>
                    <button type="button" class="grid size-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="جستجو">
                        <x-icon name="search" class="size-5" />
                    </button>
                </div>
            </div>
        </header>

        {{-- page content --}}
        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>

        <footer class="mt-auto border-t border-slate-200 px-6 py-4 text-center text-xs text-slate-400 dark:border-slate-800">
            © ۱۴۰۵ کافیت آنلاین — پنل مدیریت کل · <span dir="ltr">Laravel 13.39.0</span>
        </footer>
    </div>

    @once
        <script>
            // admin sidebar drawer (mobile)
            document.addEventListener('click', (e) => {
                const sidebar = document.getElementById('admin-sidebar');
                const overlay = document.getElementById('admin-sidebar-overlay');
                if (!sidebar || !overlay) return;

                if (e.target.closest('[data-open-sidebar]')) {
                    sidebar.classList.remove('translate-x-full');
                    overlay.classList.remove('hidden');
                } else if (e.target.closest('[data-close-sidebar]') || e.target === overlay) {
                    sidebar.classList.add('translate-x-full');
                    overlay.classList.add('hidden');
                }
            });
        </script>
    @endonce

    @livewireScripts
    @stack('scripts')
</body>
</html>
