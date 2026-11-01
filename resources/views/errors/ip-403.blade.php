<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>دسترسی غیرمجاز | {{ \App\Support\Theme::resolve()['app_name'] }}</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    @include('partials.brand-favicon')
    @include('partials.theme-vars')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-slate-100 p-6 dark:bg-slate-950">
    <main class="ip-fade-up w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xl dark:border-slate-800 dark:bg-slate-900">
        <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">
            <x-icon name="shield" class="size-8" />
        </span>
        <h1 class="mt-5 text-lg font-extrabold">دسترسی به این بخش برای شما مسدود است</h1>
        <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
            پنل مدیریت به آی‌پی‌های مشخصی محدود شده است و آی‌پی فعلی شما (<span class="font-mono" dir="ltr">{{ $ip ?? '-' }}</span>) در لیست مجاز نیست.
        </p>
        <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-[11px] leading-6 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
            اگر مدیر سایت هستید و اشتباهاً قفل شده‌اید، از تنظیمات برنامه‌نویس ← تب امنیت لیست آی‌پی را ویرایش کنید، یا فایل قفل را از مدیر سرور بخواهید.
        </p>
        <p class="mt-6 text-[10px] text-slate-400">{{ \App\Support\Theme::resolve()['app_name'] }}</p>
    </main>
</body>
</html>
