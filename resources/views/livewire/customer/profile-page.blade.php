<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                <x-icon name="user" class="size-5" />
            </span>
            <div>
                <div class="text-sm font-extrabold leading-5">پروفایل من</div>
                <div class="text-[11px] text-slate-400">حساب کاربری و تنظیمات</div>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4">
        {{-- ── identity card ── --}}
        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <span class="grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-lg font-extrabold text-white shadow-lg shadow-primary-600/30">
                {{ mb_substr($customer->full_name ?: '۹', 0, 1) }}
            </span>
            <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-extrabold">{{ $customer->full_name ?: 'مشتری InstaShop' }}</div>
                <div class="mt-0.5 flex items-center gap-1 text-[11px] text-slate-400" dir="ltr">
                    <x-icon name="phone" class="size-3" />
                    {{ \App\Support\Fmt::toFa($customer->phone) }}
                </div>
                @if ($joinedAt !== '')
                    <div class="mt-0.5 text-[10px] text-slate-400">عضو از {{ $joinedAt }}</div>
                @endif
            </div>
        </div>

        {{-- ── stats ── --}}
        <div class="mt-4 grid grid-cols-3 gap-3">
            <a href="{{ route('shop.favorites') }}" wire:navigate
               class="flex flex-col items-center gap-1.5 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-rose-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-rose-800">
                <span class="grid size-10 place-items-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                    <x-icon name="heart" class="size-5" />
                </span>
                <div class="text-center">
                    <div class="text-sm font-extrabold">{{ \App\Support\Fmt::toFa($stats['favorites']) }}</div>
                    <div class="text-[9px] text-slate-400">علاقه‌مندی</div>
                </div>
            </a>
            <a href="{{ route('shop.cart') }}" wire:navigate
               class="flex flex-col items-center gap-1.5 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-primary-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-800">
                <span class="grid size-10 place-items-center rounded-xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                    <x-icon name="cart" class="size-5" />
                </span>
                <div class="text-center">
                    <div class="text-sm font-extrabold">{{ \App\Support\Fmt::toFa($stats['cart']) }}</div>
                    <div class="text-[9px] text-slate-400">سبد خرید</div>
                </div>
            </a>
            <a href="{{ route('shop.orders') }}" wire:navigate
               class="flex flex-col items-center gap-1.5 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-emerald-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-emerald-800">
                <span class="grid size-10 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <x-icon name="inbox" class="size-5" />
                </span>
                <div class="text-center">
                    <div class="text-sm font-extrabold">{{ \App\Support\Fmt::toFa($stats['orders']) }}</div>
                    <div class="text-[9px] text-slate-400">سفارش‌ها</div>
                </div>
            </a>
        </div>

        {{-- ── edit name ── --}}
        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-xs font-extrabold">نام و نام خانوادگی</h3>
            <p class="mt-0.5 text-[10px] text-slate-400">برای نمایش در فاکتورهای خرید (اختیاری)</p>
            <form wire:submit="saveName" class="mt-3 flex gap-2">
                <input type="text" wire:model="full_name" placeholder="مثلاً: سارا محمدی" maxlength="60"
                       class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                <button type="submit" wire:loading.attr="disabled" wire:target="saveName"
                        class="rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-50">
                    ذخیره
                </button>
            </form>
            @error('full_name') <p class="mt-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
        </div>

        {{-- ── settings rows ── --}}
        <div class="mt-4 divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
            <a href="{{ route('shop.orders') }}" wire:navigate
               class="flex w-full items-center justify-between p-4 text-right transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <span class="flex items-center gap-2.5 text-xs font-bold">
                    <span class="grid size-9 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <x-icon name="file-text" class="size-4.5" />
                    </span>
                    سفارش‌های من
                </span>
                <span class="flex items-center gap-1 text-[10px] text-slate-400">
                    {{ \App\Support\Fmt::toFa($stats['orders']) }} سفارش
                    <x-icon name="chevron-left" class="size-3.5" />
                </span>
            </a>
            <button type="button" data-theme-toggle
                    class="flex w-full items-center justify-between p-4 text-right transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <span class="flex items-center gap-2.5 text-xs font-bold">
                    <span class="grid size-9 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                        <x-icon name="sun" class="size-4.5 dark:hidden" />
                        <x-icon name="moon" class="hidden size-4.5 dark:block" />
                    </span>
                    حالت نمایش
                </span>
                <span class="text-[10px] text-slate-400">تغییر روشن / تیره</span>
            </button>
        </div>

        {{-- ── logout ── --}}
        <form method="POST" action="{{ route('shop.logout') }}" class="mt-4">
            @csrf
            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 py-3 text-xs font-extrabold text-rose-600 transition hover:bg-rose-100 active:scale-[0.98] dark:border-rose-900 dark:bg-rose-950/30 dark:text-rose-400 dark:hover:bg-rose-950/50">
                <x-icon name="log-out" class="size-4" />
                خروج از حساب
            </button>
        </form>

        {{-- ── about ── --}}
        <div class="mt-4 mb-2 flex items-center justify-between rounded-2xl bg-slate-100/70 px-4 py-3 text-[10px] text-slate-400 dark:bg-slate-900">
            <span>InstaShop — نسخه ۰٫۵</span>
            <span>پرداخت آنلاین فعال ✅</span>
        </div>
    </div>

    {{-- toast --}}
    <div x-data="{ show: false, msg: '' }"
         @profile-toast.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2600)"
         x-show="show" x-transition.opacity
         class="fixed bottom-24 left-1/2 z-40 w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95"
         x-cloak>
        <span x-text="msg"></span>
    </div>
</div>
