@extends('layouts.seller')

@section('title', 'خانه فروشنده')

@section('header')
    @php $me = auth('seller')->user(); @endphp
    <div class="flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                <x-icon name="store" class="size-5" />
            </span>
            <div>
                <div class="text-sm font-extrabold leading-5">فروشگاه شما</div>
                <div class="flex items-center gap-1 text-[11px] text-slate-400">
                    <x-icon name="instagram" class="size-3" />
                    <span dir="ltr">{{ '@'.($me?->instagram_username ?? 'your_shop') }}</span>
                    @if ($me?->status === 'pending')
                        <span class="mx-0.5">·</span>
                        <span class="font-bold text-amber-500">در انتظار تأیید</span>
                    @elseif ($me?->status === 'approved')
                        <span class="mx-0.5">·</span>
                        <span class="text-emerald-500">فعال</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1">
            <x-theme-toggle class="size-10 text-slate-500" />
            <form method="POST" action="{{ route('seller.logout') }}">
                @csrf
                <button type="submit" class="grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-800" aria-label="خروج از حساب">
                    <x-icon name="log-out" class="size-5" />
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')
    <div class="ip-fade-up px-4 pt-4">
        {{-- ── pending approval banner ── --}}
        @if ($me?->status === 'pending')
            <div class="mb-4 flex items-start gap-2.5 rounded-2xl border border-amber-200 bg-amber-50 p-3.5 text-xs leading-6 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
                <x-icon name="clock" class="mt-0.5 size-4 shrink-0" />
                <span>
                    <b>فروشگاه شما در انتظار تأیید است.</b>
                    تا زمان تأیید توسط مدیر می‌توانید فروشگاه و محصولات را بسازید؛ اما نمایش عمومی پس از تأیید فعال می‌شود.
                </span>
            </div>
        @endif

        {{-- ── greeting hero ── --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500 p-5 text-white shadow-xl shadow-primary-600/25">
            <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
                <div class="absolute -top-8 left-6 size-40 rounded-full bg-white/30 blur-2xl"></div>
                <div class="absolute -bottom-12 right-10 size-48 rounded-full bg-white/20 blur-2xl"></div>
            </div>
            <p class="relative text-xs text-primary-100">سلام {{ $me?->full_name ?? 'فروشنده' }} عزیز، امروز چیکار می‌کنیم؟ ☀️</p>
            <h2 class="relative mt-1.5 text-lg font-extrabold">فروشگاهت آماده فروشه!</h2>

            {{-- mini stats --}}
            <div class="relative mt-4 grid grid-cols-3 gap-2">
                @foreach ([
                    ['icon' => 'bag',        'value' => '۱۲', 'label' => 'سفارش امروز'],
                    ['icon' => 'wallet',     'value' => '۴٫۲م', 'label' => 'درآمد ماه'],
                    ['icon' => 'star',       'value' => '۴٫۸', 'label' => 'امتیاز فروشگاه'],
                ] as $s)
                    <div class="rounded-2xl bg-white/15 p-2.5 backdrop-blur">
                        <x-icon name="{{ $s['icon'] }}" class="size-4 opacity-80" />
                        <div class="mt-1 text-base font-extrabold">{{ $s['value'] }}</div>
                        <div class="text-[10px] text-primary-100">{{ $s['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── quick actions ── --}}
        <h3 class="mt-6 flex items-center justify-between text-sm font-extrabold">
            دسترسی سریع
            <span class="text-[11px] font-medium text-slate-400">نمونه فاز ۱</span>
        </h3>
        <div class="mt-3 grid grid-cols-4 gap-2.5">
            @foreach ([
                ['icon' => 'plus',    'label' => 'افزودن محصول', 'tone' => 'bg-primary-600 text-white shadow-lg shadow-primary-600/30'],
                ['icon' => 'package', 'label' => 'محصولات',      'tone' => 'bg-white text-primary-600 border border-slate-200 dark:bg-slate-900 dark:border-slate-800'],
                ['icon' => 'store',   'label' => 'ویرایش فروشگاه','tone' => 'bg-white text-primary-600 border border-slate-200 dark:bg-slate-900 dark:border-slate-800'],
                ['icon' => 'qr-code', 'label' => 'کد تخفیف',     'tone' => 'bg-white text-primary-600 border border-slate-200 dark:bg-slate-900 dark:border-slate-800'],
            ] as $qa)
                <button type="button" class="flex flex-col items-center gap-1.5 rounded-2xl p-2 transition active:scale-95">
                    <span class="grid size-12 w-full place-items-center rounded-2xl {{ $qa['tone'] }}">
                        <x-icon name="{{ $qa['icon'] }}" class="size-5" />
                    </span>
                    <span class="text-[10px] font-semibold leading-3 text-center text-slate-600 dark:text-slate-300">{{ $qa['label'] }}</span>
                </button>
            @endforeach
        </div>

        {{-- ── recent orders ── --}}
        <h3 class="mt-6 text-sm font-extrabold">سفارش‌های اخیر</h3>
        <div class="mt-3 space-y-2.5">
            @foreach ([
                ['name' => 'زعفران سرگل قائنات — ۱۰ گرم', 'buyer' => 'مشتری: علی ر.',  'price' => '۹۸۰٬۰۰۰',   'status' => 'جدید',    'tone' => 'bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300'],
                ['name' => 'پوشاک بافت زمستانی',           'buyer' => 'مشتری: سارا م.',  'price' => '۱٬۲۴۰٬۰۰۰', 'status' => 'در حال ارسال', 'tone' => 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'],
                ['name' => 'کرم مرطوب‌کننده گیاهی',        'buyer' => 'مشتری: رضا ک.',   'price' => '۳۶۰٬۰۰۰',   'status' => 'تحویل شد', 'tone' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'],
            ] as $o)
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                        <x-icon name="package" class="size-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-xs font-bold">{{ $o['name'] }}</div>
                        <div class="mt-1 text-[11px] text-slate-400">{{ $o['buyer'] }} · <span class="font-bold text-slate-600 dark:text-slate-300">{{ $o['price'] }} تومان</span></div>
                    </div>
                    <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-bold {{ $o['tone'] }}">{{ $o['status'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- ── phase notice ── --}}
        <div class="mt-6 mb-4 flex items-start gap-2.5 rounded-2xl border border-primary-200 bg-primary-50/70 p-3.5 text-xs leading-6 text-primary-800 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
            <x-icon name="info" class="mt-0.5 size-4 shrink-0" />
            <span>
                <b>حالت پیش‌نمایش فاز ۲:</b> ورود با OTP فعال شد (پیامک در حالت لاگ). داده‌های سفارش و درآمد نمایشی هستند؛ در فاز ۳ صفحه کامل «فروشگاه من» (کارت محصول، فیلترها، دسته‌بندی‌ها و دکمه شناور افزودن محصول) فعال می‌شود.
            </span>
        </div>
    </div>
@endsection
