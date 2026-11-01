<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                <x-icon name="user" class="size-5" />
            </span>
            <div>
                <div class="text-sm font-extrabold leading-5">پروفایل فروشنده</div>
                <div class="text-[11px] text-slate-400">حساب کاربری و دسترسی سریع</div>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4">
        {{-- ── identity card ── --}}
        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-lg font-extrabold text-white shadow-lg shadow-primary-600/30">
                {{ mb_substr($seller->full_name ?: $shop?->name ?: 'ف', 0, 1) }}
            </span>
            <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-extrabold">{{ $seller->full_name ?: 'فروشنده InstaShop' }}</div>
                <div class="mt-0.5 flex items-center gap-1 text-[11px] text-slate-400" dir="ltr">
                    <x-icon name="phone" class="size-3" />
                    {{ \App\Support\Fmt::toFa($seller->phone) }}
                </div>
                @if ($seller->instagram_username)
                    <div class="mt-0.5 flex items-center gap-1 text-[10px] text-slate-400" dir="ltr">
                        <x-icon name="instagram" class="size-3" />
                        {{ '@'.$seller->instagram_username }}
                    </div>
                @endif
                @if ($joinedAt !== '')
                    <div class="mt-0.5 text-[10px] text-slate-400">عضو از {{ $joinedAt }}</div>
                @endif
            </div>
            @if ($seller->status === 'approved')
                <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-1 text-[9px] font-black text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">تأییدشده</span>
            @elseif ($seller->status === 'pending')
                <span class="shrink-0 rounded-full bg-amber-100 px-2 py-1 text-[9px] font-black text-amber-700 dark:bg-amber-950 dark:text-amber-300">در انتظار تأیید</span>
            @endif
        </div>

        {{-- ── stats ── --}}
        <div class="mt-4 grid grid-cols-3 gap-3">
            <a href="{{ route('seller.shop') }}" wire:navigate
               class="flex flex-col items-center gap-1.5 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-primary-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-800">
                <span class="grid size-10 place-items-center rounded-xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                    <x-icon name="package" class="size-5" />
                </span>
                <div class="text-center">
                    <div class="text-sm font-extrabold">{{ \App\Support\Fmt::toFa($stats['products']) }}</div>
                    <div class="text-[9px] text-slate-400">محصول</div>
                </div>
            </a>
            <a href="{{ route('seller.orders') }}" wire:navigate
               class="flex flex-col items-center gap-1.5 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-emerald-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-emerald-800">
                <span class="grid size-10 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <x-icon name="bag" class="size-5" />
                </span>
                <div class="text-center">
                    <div class="text-sm font-extrabold">{{ \App\Support\Fmt::toFa($stats['orders']) }}</div>
                    <div class="text-[9px] text-slate-400">سفارش</div>
                </div>
            </a>
            <a href="{{ route('seller.wallet') }}" wire:navigate
               class="flex flex-col items-center gap-1.5 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-amber-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-amber-800">
                <span class="grid size-10 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <x-icon name="wallet" class="size-5" />
                </span>
                <div class="text-center">
                    <div class="text-sm font-extrabold">{{ \App\Support\Fmt::toFa($stats['balance']) }}</div>
                    <div class="text-[9px] text-slate-400">موجودی کیف پول (تومان)</div>
                </div>
            </a>
        </div>

        {{-- ── edit name ── --}}
        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-xs font-extrabold">نام و نام خانوادگی</h3>
            <p class="mt-0.5 text-[10px] text-slate-400">نام نمایشی شما در پنل و ارتباط با مشتریان</p>
            <form wire:submit="saveName" class="mt-3 flex gap-2">
                <input type="text" wire:model="full_name" placeholder="مثلاً: مهدی رضایی" maxlength="60"
                       class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                <button type="submit" wire:loading.attr="disabled" wire:target="saveName"
                        class="rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-50">
                    ذخیره
                </button>
            </form>
            @error('full_name') <p class="mt-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
        </div>

        {{-- ── shortcuts & settings rows ── --}}
        <div class="mt-4 divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
            <a href="{{ route('seller.shop') }}" wire:navigate
               class="flex w-full items-center justify-between p-4 text-right transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <span class="flex items-center gap-2.5 text-xs font-bold">
                    <span class="grid size-9 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400">
                        <x-icon name="store" class="size-4.5" />
                    </span>
                    فروشگاه من
                </span>
                <span class="flex items-center gap-1 text-[10px] text-slate-400">
                    مدیریت محصولات و دسته‌ها
                    <x-icon name="chevron-left" class="size-3.5" />
                </span>
            </a>
            <a href="{{ route('seller.wallet') }}" wire:navigate
               class="flex w-full items-center justify-between p-4 text-right transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <span class="flex items-center gap-2.5 text-xs font-bold">
                    <span class="grid size-9 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                        <x-icon name="wallet" class="size-4.5" />
                    </span>
                    کیف پول و برداشت
                </span>
                <span class="flex items-center gap-1 text-[10px] text-slate-400">
                    موجودی: {{ \App\Support\Fmt::toFa($stats['balance']) }} تومان
                    <x-icon name="chevron-left" class="size-3.5" />
                </span>
            </a>
            <a href="{{ route('seller.reports') }}" wire:navigate
               class="flex w-full items-center justify-between p-4 text-right transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <span class="flex items-center gap-2.5 text-xs font-bold">
                    <span class="grid size-9 place-items-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400">
                        <x-icon name="chart" class="size-4.5" />
                    </span>
                    گزارش‌ها
                </span>
                <span class="flex items-center gap-1 text-[10px] text-slate-400">
                    سفارش‌ها و بازدید محصولات
                    <x-icon name="chevron-left" class="size-3.5" />
                </span>
            </a>
            <a href="{{ route('seller.cards') }}" wire:navigate
               class="flex w-full items-center justify-between p-4 text-right transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <span class="flex items-center gap-2.5 text-xs font-bold">
                    <span class="grid size-9 place-items-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-950/50 dark:text-violet-400">
                        <x-icon name="credit-card" class="size-4.5" />
                    </span>
                    کارت‌های بانکی
                </span>
                <span class="flex items-center gap-1 text-[10px] text-slate-400">
                    {{ $stats['cards'] > 0 ? \App\Support\Fmt::fa($stats['cards']).' کارت ثبت‌شده' : 'افزودن کارت و شبا' }}
                    <x-icon name="chevron-left" class="size-3.5" />
                </span>
            </a>
            <a href="{{ route('seller.shop.attributes') }}" wire:navigate
               class="flex w-full items-center justify-between p-4 text-right transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <span class="flex items-center gap-2.5 text-xs font-bold">
                    <span class="grid size-9 place-items-center rounded-xl bg-fuchsia-100 text-fuchsia-600 dark:bg-fuchsia-950/50 dark:text-fuchsia-400">
                        <x-icon name="layers" class="size-4.5" />
                    </span>
                    ویژگی‌ها و تنوع‌ها
                </span>
                <span class="flex items-center gap-1 text-[10px] text-slate-400">
                    رنگ، سایز، گارانتی و ...
                    <x-icon name="chevron-left" class="size-3.5" />
                </span>
            </a>
            <button type="button" data-theme-toggle
                    class="flex w-full items-center justify-between p-4 text-right transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <span class="flex items-center gap-2.5 text-xs font-bold">
                    <span class="grid size-9 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                        <x-icon name="sun" class="size-4.5 dark:hidden" />
                        <x-icon name="moon" class="hidden size-4.5 dark:block" />
                    </span>
                    حالت نمایش
                </span>
                <span class="text-[10px] text-slate-400">تغییر روشن / تیره</span>
            </button>
        </div>

        {{-- ── support note ── --}}
        <div class="mt-4 flex items-start gap-2 rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-3 text-[10px] font-semibold leading-5 text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
            <x-icon name="headset" class="mt-0.5 size-4 shrink-0 text-primary-500" />
            <span>
                برای تغییر <b>شناسه اینستاگرام</b> یا هر مورد دیگر که در پنل قابل ویرایش نیست، با پشتیبانی در تماس باشید —
                توجه کنید تغییر شناسه، لینک صفحه فروشگاه را هم عوض می‌کند.
            </span>
        </div>

        {{-- ── logout ── --}}
        <form method="POST" action="{{ route('seller.logout') }}" class="mt-4">
            @csrf
            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 py-3 text-xs font-extrabold text-rose-600 transition hover:bg-rose-100 active:scale-[0.98] dark:border-rose-900 dark:bg-rose-950/30 dark:text-rose-400 dark:hover:bg-rose-950/50">
                <x-icon name="log-out" class="size-4" />
                خروج از حساب
            </button>
        </form>

        <div class="mb-2 mt-4 flex items-center justify-between rounded-2xl bg-slate-100/70 px-4 py-3 text-[10px] text-slate-400 dark:bg-slate-900">
            <span>InstaShop — پنل فروشنده</span>
            <span>نسخه {{ \App\Support\Fmt::toFa(config('instashop.version', '۱.۰')) }}</span>
        </div>
    </div>

    {{-- toast --}}
    <div x-data="{ show: false, msg: '' }"
         @seller-profile-toast.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2600)"
         x-show="show" x-transition.opacity x-cloak
         class="fixed bottom-24 left-1/2 z-40 w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95">
        <span x-text="msg"></span>
    </div>
</div>
