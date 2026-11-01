<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>دسترسی محدود | {{ \App\Support\Theme::resolve()['app_name'] }}</title>
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
        <h1 class="mt-5 text-lg font-extrabold">دسترسی به این بخش مجاز نیست</h1>
        <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
            @if (isset($permission) && $permission)
                برای باز کردن صفحه‌ی «{{ $permission }}» دسترسی لازم را ندارید. اگر فکر می‌کنید این یک اشتباه است، از مدیر ارشد بخواهید این دسترسی را در بخش «دسترسی‌ها» به نقش شما اضافه کند.
            @else
                نقش فعلی شما به این صفحه دسترسی ندارد. از مدیر ارشد بخواهید دسترسی مورد نیاز را به نقش شما اضافه کند.
            @endif
        </p>
        <div class="mt-6 flex justify-center gap-2">
            <a href="{{ route('admin.dashboard') }}"
               class="rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                بازگشت به داشبورد
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="rounded-xl border border-slate-200 px-5 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    خروج از حساب
                </button>
            </form>
        </div>
        <p class="mt-6 text-[10px] text-slate-400">{{ \App\Support\Theme::resolve()['app_name'] }} — پنل مدیریت</p>
    </main>
</body>
</html>
