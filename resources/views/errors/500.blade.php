@extends('errors.partials.error-shell')

@section('title', 'خطای سرور')

@section('accent', 'from-slate-800 via-slate-700 to-slate-600')

@section('code', '۵۰۰')

@section('icon')
    <x-icon name="alert" class="size-8" />
@endsection

@section('heading', 'مشکلی در سرور پیش آمد')

@section('message')
    <p>خطای غیرمنتظره‌ای رخ داد. نگران نباشید — تیم فنی مطلع شده است.</p>
    <p class="mt-1">چند لحظه بعد دوباره تلاش کنید.</p>
@endsection

@section('actions')
    <div class="mt-5 border-t border-dashed border-slate-200 pt-4 dark:border-slate-700">
        <p class="flex items-center justify-center gap-1.5 text-[11px] font-bold leading-6 text-slate-500 dark:text-slate-400">
            <x-icon name="alert" class="size-3.5 shrink-0 text-amber-500" />
            برای گزارش این مشکل، از یکی از لینک‌های زیر استفاده کنید:
        </p>
        <div class="mt-3 flex flex-wrap items-center justify-center gap-2.5">
            <a href="https://weblak.ir" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 rounded-2xl border border-primary-200 bg-primary-50 px-4 py-2.5 text-[11px] font-extrabold text-primary-700 transition hover:bg-primary-100 active:scale-95 dark:border-primary-800 dark:bg-primary-950/40 dark:text-primary-300 dark:hover:bg-primary-950/70">
                <x-icon name="globe" class="size-3.5" />
                وب‌لاک — weblak.ir
            </a>
            <a href="https://update.opashop.ir" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-[11px] font-extrabold text-slate-600 transition hover:bg-slate-50 active:scale-95 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                <x-icon name="rotate" class="size-3.5" />
                آپدیت اُپاشاپ — update.opashop.ir
            </a>
        </div>
    </div>
@endsection
