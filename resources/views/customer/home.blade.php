@extends('layouts.customer')

@section('title', 'فروشگاه InstaShop')

@section('header')
    <div class="flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                <x-icon name="store" class="size-5" />
            </span>
            <div>
                <div class="text-sm font-extrabold leading-5">InstaShop</div>
                <div class="text-[11px] text-slate-400">فروشگاه‌های اینستاگرامی</div>
            </div>
        </div>
        <div class="flex items-center gap-1">
            <x-theme-toggle class="size-10 text-slate-500" />
            <button type="button" class="grid size-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="جستجو">
                <x-icon name="search" class="size-5" />
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="ip-fade-up px-4 pt-4">
        {{-- ── search ── --}}
        <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3.5 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <x-icon name="search" class="size-4.5 text-slate-400" />
            <span class="text-xs text-slate-400">جستجوی محصول، فروشگاه، دسته‌بندی…</span>
        </div>

        {{-- ── banner ── --}}
        <div class="relative mt-4 overflow-hidden rounded-3xl bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500 p-4 text-white shadow-xl shadow-primary-600/25">
            <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
                <div class="absolute -top-6 left-4 size-32 rounded-full bg-white/30 blur-2xl"></div>
            </div>
            <p class="relative text-[11px] text-primary-100">🎁 پیشنهاد ویژه این هفته</p>
            <h2 class="relative mt-1 text-base font-extrabold leading-7">تا ۳۰٪ تخفیف<br>روی محصولات منتخب</h2>
            <button type="button" class="relative mt-3 rounded-xl bg-white px-4 py-2 text-[11px] font-bold text-primary-700 shadow-md transition active:scale-95">
                مشاهده تخفیف‌ها
            </button>
            <div class="absolute -left-4 bottom-0 grid size-24 place-items-center opacity-90" aria-hidden="true">
                <x-icon name="bag" class="size-20 text-white/25" />
            </div>
        </div>

        {{-- ── categories ── --}}
        <h3 class="mt-6 text-sm font-extrabold">دسته‌بندی‌ها</h3>
        <div class="scroll-slim mt-3 flex gap-2.5 overflow-x-auto pb-1">
            @foreach ([
                ['icon' => 'bag',      'label' => 'پوشاک'],
                ['icon' => 'sparkles', 'label' => 'زیبایی'],
                ['icon' => 'package',  'label' => 'دیجیتال'],
                ['icon' => 'star',     'label' => 'سوغات'],
                ['icon' => 'heart',    'label' => 'هنری'],
            ] as $cat)
                <button type="button" class="flex shrink-0 flex-col items-center gap-1.5">
                    <span class="grid size-14 place-items-center rounded-2xl border border-slate-200 bg-white text-primary-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-primary-400">
                        <x-icon name="{{ $cat['icon'] }}" class="size-5" />
                    </span>
                    <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">{{ $cat['label'] }}</span>
                </button>
            @endforeach
        </div>

        {{-- ── product grid (skeleton demo) ── --}}
        <div class="mt-6 flex items-center justify-between">
            <h3 class="text-sm font-extrabold">محصولات داغ</h3>
            <span class="text-[11px] font-semibold text-primary-600 dark:text-primary-400">مشاهده همه</span>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-3">
            @foreach (range(1, 4) as $i)
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="relative grid h-28 place-items-center bg-slate-100 dark:bg-slate-800">
                        <x-icon name="package" class="size-8 text-slate-300 dark:text-slate-600" />
                        <span class="absolute right-2 top-2 rounded-full bg-primary-600 px-2 py-0.5 text-[9px] font-bold text-white">٪{{ ['۱۵', '۳۰', '۱۰', '۲۵'][$i - 1] }}</span>
                    </div>
                    <div class="space-y-2 p-2.5">
                        <div class="h-2.5 w-4/5 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                        <div class="h-2 w-3/5 rounded-full bg-slate-100 dark:bg-slate-800"></div>
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs font-extrabold text-primary-600 dark:text-primary-400">{{ number_format([450000, 890000, 260000, 1200000][$i - 1]) }}</span>
                            <span class="grid size-7 place-items-center rounded-lg bg-primary-600 text-white shadow-md shadow-primary-600/30">
                                <x-icon name="plus" class="size-3.5" />
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── phase notice ── --}}
        <div class="mt-6 mb-4 flex items-start gap-2.5 rounded-2xl border border-primary-200 bg-primary-50/70 p-3.5 text-xs leading-6 text-primary-800 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
            <x-icon name="info" class="mt-0.5 size-4 shrink-0" />
            <span>
                <b>حالت پیش‌نمایش فاز ۱:</b> در فاز ۲ ورود با شماره موبایل + کد OTP فعال و در
                فاز ۴ فروشگاه کامل (محصولات واقعی، سبد خرید و پرداخت) اجرا می‌شود.
            </span>
        </div>
    </div>
@endsection
