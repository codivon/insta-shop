{{-- v0.23.0 — «اشتراک فروشنده به پایان رسیده»: the friendly full-page
     notice shown instead of the storefront when the seller has NO active
     subscription. The shop itself stays hidden from home/search; this
     page only renders for direct links so already-shared addresses don't
     dead-end into a bare 404. --}}
<div class="mx-auto flex min-h-[70dvh] w-full max-w-md flex-col items-center justify-center px-6 py-14 text-center">
    <div class="relative">
        <span class="grid size-20 place-items-center rounded-3xl bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-xl shadow-amber-500/30">
            <x-icon name="crown" class="size-9" />
        </span>
        <span class="absolute -bottom-1.5 -end-1.5 grid size-7 place-items-center rounded-full border-2 border-white bg-slate-400 text-white dark:border-slate-950">
            <x-icon name="clock" class="size-3.5" />
        </span>
    </div>

    <h1 class="mt-5 text-lg font-black leading-8">اشتراک فروشنده به پایان رسیده است</h1>

    <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
        فروشگاه «{{ $shop->name }}» فعلاً تعطیل است؛ پس از تمدید اشتراک توسط فروشنده،
        دوباره با همان آدرس قبلی در دسترس قرار می‌گیرد.
    </p>

    <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
        <a href="{{ route('shop.home') }}" wire:navigate
           class="inline-flex items-center gap-2 rounded-2xl bg-primary-600 px-5 py-3 text-xs font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
            <x-icon name="store" class="size-4" />
            دیدن فروشگاه‌های دیگر
        </a>
        <a href="{{ url('/') }}"
           class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-xs font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
            صفحهٔ اصلی
        </a>
    </div>

    @if (filled($handle))
        <p class="mt-8 rounded-xl bg-slate-100 px-4 py-2 text-[10px] font-bold text-slate-400 dark:bg-slate-900" dir="ltr">{{ '@'.$handle }}</p>
    @endif
</div>
