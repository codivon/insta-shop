<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ \App\Support\Theme::resolve()['app_name'] }}</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.brand-favicon')
    @include('partials.theme-vars')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="grid min-h-screen place-items-center bg-slate-100 p-6 dark:bg-slate-950">
    <main class="ip-fade-up w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-xl dark:border-slate-800 dark:bg-slate-900">
        @if (\App\Support\Branding::logoUrl())
            <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ \App\Support\Theme::resolve()['app_name'] }}" class="mx-auto size-16 object-contain">
        @else
            <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                <x-icon name="settings" class="size-8" />
            </span>
        @endif

        <h1 class="mt-6 text-xl font-extrabold">{{ $title }}</h1>

        @if (trim($description) !== '')
            <div class="mt-3 text-xs leading-7 text-slate-500 dark:text-slate-400">{!! nl2br(e($description)) !!}</div>
        @endif

        <span class="mt-6 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-4 py-1.5 text-[11px] font-bold text-amber-700 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-400">
            <span class="size-1.5 animate-pulse rounded-full bg-amber-500"></span>
            به‌زودی برمی‌گردیم
        </span>

        <p class="mt-8 text-[10px] text-slate-400">{{ \App\Support\Theme::resolve()['app_name'] }}</p>
    </main>
</body>
</html>
