@extends('layouts.admin')

@section('title', 'عمومی')
@section('breadcrumb', 'مدیریت کل — صفحه اصلی')

@section('content')
    <div class="ip-fade-up space-y-6">
        {{-- welcome banner --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500 p-6 text-white shadow-xl shadow-primary-600/25 sm:p-8">
            <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
                <div class="absolute -top-10 left-10 size-56 rounded-full bg-white/30 blur-3xl"></div>
                <div class="absolute -bottom-16 right-24 size-64 rounded-full bg-white/20 blur-3xl"></div>
            </div>
            <div class="relative flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-xl font-extrabold sm:text-2xl">سلام مدیر عزیز 👋</h2>
                    <p class="mt-2 max-w-lg text-sm leading-7 text-primary-100">
                        فاز ۱ (هسته سه‌پنله) راه‌اندازی شد. از منوی «تنظیمات» می‌توانید رنگ‌بندی کل
                        سیستم را شخصی‌سازی کنید — همه پنل‌ها بلافاصله تغییر می‌کنند.
                    </p>
                </div>
                <a href="{{ route('admin.settings.appearance') }}"
                   class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-primary-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-primary-50">
                    <x-icon name="palette" class="size-4.5" />
                    شخصی‌سازی رنگ‌بندی
                </a>
            </div>
        </div>

        {{-- stat cards --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['icon' => 'store',       'label' => 'فروشندگان',      'value' => '۰',     'hint' => 'فاز ۲ فعال می‌شود', 'tone' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'],
                ['icon' => 'users',       'label' => 'مشتریان',         'value' => '۰',     'hint' => 'فاز ۲ فعال می‌شود', 'tone' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400'],
                ['icon' => 'package',     'label' => 'محصولات',         'value' => '۰',     'hint' => 'فاز ۳ فعال می‌شود', 'tone' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400'],
                ['icon' => 'credit-card', 'label' => 'تراکنش‌های امروز', 'value' => '۰ تومان', 'hint' => 'فاز ۵ فعال می‌شود', 'tone' => 'bg-violet-500/10 text-violet-600 dark:text-violet-400'],
            ] as $card)
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="grid size-11 place-items-center rounded-2xl {{ $card['tone'] }}">
                            <x-icon name="{{ $card['icon'] }}" class="size-5" />
                        </span>
                        <span class="text-[11px] text-slate-400">{{ $card['hint'] }}</span>
                    </div>
                    <div class="mt-4 text-2xl font-extrabold">{{ $card['value'] }}</div>
                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $card['label'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- roadmap --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h3 class="font-extrabold">نقشه راه اجرای پروژه</h3>
                <span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-bold text-primary-700 dark:bg-primary-950 dark:text-primary-300">فاز ۱ از ۷</span>
            </div>

            <ol class="mt-5 space-y-3">
                @foreach ([
                    ['فاز ۰ — زیرساخت (PHP 8.5 / Laravel 13 / MySQL)', 'done'],
                    ['فاز ۱ — هسته سه‌پنله + سشن‌های مستقل + تم', 'done'],
                    ['فاز ۲ — احراز هویت OTP (مشتری و فروشنده)', 'next'],
                    ['فاز ۳ — اپ فروشنده: فروشگاه من، محصولات، دسته‌بندی‌ها', 'todo'],
                    ['فاز ۴ — اپ مشتری: خانه، محصول، سبد خرید', 'todo'],
                    ['فاز ۵ — پرداخت با shetabit/payment', 'todo'],
                    ['فاز ۶ — پنل مدیریت کامل (پیامک/درگاه/گزارش‌ها)', 'todo'],
                    ['فاز ۷ — PWA و استقرار', 'todo'],
                ] as $row)
                    @php $status = $row[1]; @endphp
                    <li class="flex items-center gap-3 rounded-2xl border p-3 text-sm
                               {{ $status === 'done'
                                   ? 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-900 dark:bg-emerald-950/30'
                                   : ($status === 'next'
                                       ? 'border-primary-200 bg-primary-50/60 dark:border-primary-900 dark:bg-primary-950/30'
                                       : 'border-slate-200 dark:border-slate-800') }}">
                        @if ($status === 'done')
                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-emerald-500 text-white"><x-icon name="check" class="size-4" /></span>
                            <span class="font-semibold text-emerald-800 dark:text-emerald-300">{{ $row[0] }}</span>
                        @elseif ($status === 'next')
                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-primary-600 text-white">۳</span>
                            <span class="font-semibold text-primary-800 dark:text-primary-300">{{ $row[0] }}</span>
                            <span class="mr-auto rounded-full bg-primary-600 px-2 py-0.5 text-[10px] font-bold text-white">قدم بعدی</span>
                        @else
                            <span class="size-7 shrink-0 rounded-full border-2 border-dashed border-slate-300 dark:border-slate-700"></span>
                            <span class="text-slate-500 dark:text-slate-400">{{ $row[0] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
@endsection
