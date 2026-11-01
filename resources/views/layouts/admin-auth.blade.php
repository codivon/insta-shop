{{-- ═══════════════════════════════════════════════════════════
  Admin-auth layout — desktop-centered login card, RTL.
═══════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-panel="admin">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#D62976">
    <title>@yield('title', 'ورود مدیر') | InstaShop</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.brand-favicon')
    @include('partials.theme-vars')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-200 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors">

    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-800 text-white shadow-xl shadow-primary-600/30">
                    <x-icon name="shield" class="size-7" />
                </span>
                <h1 class="mt-4 text-xl font-extrabold">پنل مدیریت InstaShop</h1>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">دسترسی فقط برای مدیران سیستم</p>
            </div>

            {{ $slot }}
        </div>
    </main>

    @stack('scripts')
</body>
</html>
