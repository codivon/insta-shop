{{-- ═══════════════════════════════════════════════════════════
  Admin layout — desktop-first RTL with right sidebar, responsive.
  Sidebar collapses into a drawer under lg breakpoint.
═════════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'پنل مدیریت') | InstaShop</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
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
                <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                    <x-icon name="store" class="size-5" />
                </span>
                <span>
                    <span class="block text-sm font-extrabold leading-4">InstaShop</span>
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
                $navItems = [
                    ['route' => 'admin.dashboard',           'icon' => 'chart',       'label' => 'عمومی',            'badge' => '۲'],
                    ['route' => 'admin.dashboard',           'icon' => 'info',        'label' => 'صفحه درباره ما',   'badge' => 'بزودی'],
                    ['route' => 'admin.dashboard',           'icon' => 'download',    'label' => 'مرکز خروجی‌ها',    'badge' => 'فعال'],
                    ['route' => 'admin.dashboard',           'icon' => 'headset',     'label' => 'تیکت‌های پشتیبانی','badge' => 'فعال'],
                    ['route' => 'admin.settings.appearance', 'icon' => 'settings',    'label' => 'تنظیمات',         'badge' => null, 'active' => true],
                    ['route' => 'admin.dashboard',           'icon' => 'megaphone',   'label' => 'اطلاعیه‌ها',       'badge' => 'فعال'],
                    ['route' => 'admin.dashboard',           'icon' => 'bell',        'label' => 'تنبیهات',          'badge' => null],
                    ['route' => 'admin.dashboard',           'icon' => 'message',     'label' => 'قالب‌های پیامک',   'badge' => 'فعال'],
                    ['route' => 'admin.dashboard',           'icon' => 'clock',       'label' => 'لاگ پیامک‌ها',     'badge' => 'پیامک'],
                    ['route' => 'admin.dashboard',           'icon' => 'star',        'label' => 'نظرسنجی و امتیاز', 'badge' => 'پیش'],
                    ['route' => 'admin.dashboard',           'icon' => 'package',     'label' => 'مجوز سفارش‌ها',    'badge' => 'پیش'],
                    ['route' => 'admin.dashboard',           'icon' => 'message',     'label' => 'چت و پاسخ‌دهی',    'badge' => 'KPI'],
                    ['route' => 'admin.dashboard',           'icon' => 'clock',       'label' => 'ساعت کاری',       'badge' => 'خانه'],
                    ['route' => 'admin.dashboard',           'icon' => 'credit-card', 'label' => 'درگاه پرداخت',    'badge' => 'درگاه'],
                    ['route' => 'admin.dashboard',           'icon' => 'users',       'label' => 'کاربران گرفتنی‌ها', 'badge' => 'خبر'],
                    ['route' => 'admin.dashboard',           'icon' => 'trending-up', 'label' => 'Realtime',         'badge' => 'پویا'],
                    ['route' => 'admin.dashboard',           'icon' => 'bell',        'label' => 'اعلان‌ها و پوش',   'badge' => 'مکا'],
                    ['route' => 'admin.dashboard',           'icon' => 'users',       'label' => 'بازاریابی معرفی',  'badge' => null],
                    ['route' => 'admin.dashboard',           'icon' => 'upload',      'label' => 'آپلود و فضای‌دسازی','badge' => null],
                    ['route' => 'admin.dashboard',           'icon' => 'qr-code',     'label' => 'QR شبکه‌ها',       'badge' => null],
                    ['route' => 'admin.dashboard',           'icon' => 'megaphone',   'label' => 'بات تلگرام',       'badge' => null],
                    ['route' => 'admin.dashboard',           'icon' => 'sparkles',    'label' => 'قابلیت‌ها و امنیت','badge' => null],
                ];
            @endphp

            @foreach ($navItems as $item)
                @php
                    $isActive = ($item['active'] ?? false) && request()->routeIs($item['route']);
                @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm transition
                          {{ $isActive
                              ? 'bg-primary-600 font-bold text-white shadow-md shadow-primary-600/25'
                              : 'font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="{{ $item['icon'] }}" class="size-4.5 {{ $isActive ? 'text-white' : 'text-slate-400' }}" />
                        {{ $item['label'] }}
                    </span>
                    @if ($item['badge'] ?? null)
                        <span class="rounded-md px-1.5 py-0.5 text-[10px] font-semibold
                                     {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach

            {{-- sub sections --}}
            <div class="pt-3">
                @foreach ([['icon' => 'file-text', 'label' => 'مرکز پیامک (بتن‌ها)'], ['icon' => 'sparkles', 'label' => 'نمایش لاگ سیستمی'], ['icon' => 'globe', 'label' => 'کلیدهای وب‌سرویس'], ['icon' => 'settings', 'label' => 'سنسورهای فعال'], ['icon' => 'settings', 'label' => 'ساخت سیستم']] as $sub)
                    <button type="button" class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300">
                        <span class="flex items-center gap-2.5">
                            <x-icon name="{{ $sub['icon'] }}" class="size-4.5" />
                            {{ $sub['label'] }}
                        </span>
                        <x-icon name="chevron-down" class="size-3.5" />
                    </button>
                @endforeach
            </div>
        </nav>

        {{-- manager card --}}
        <div class="border-t border-slate-200 p-4 dark:border-slate-800">
            <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/60">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-9 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-bold text-white">م</span>
                    <span>
                        <span class="block text-xs font-bold">مدیر</span>
                        <span class="block text-[11px] text-slate-400" dir="ltr">admin@caffeinet.ir</span>
                    </span>
                </div>
                <button type="button" class="grid size-8 place-items-center rounded-lg bg-primary-600 text-white shadow-md shadow-primary-600/30" aria-label="خروج">
                    <x-icon name="log-out" class="size-4" />
                </button>
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
                            <a href="/" class="transition hover:text-primary-600">InstaShop</a>
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
