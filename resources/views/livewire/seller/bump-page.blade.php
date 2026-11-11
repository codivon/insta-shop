<div class="ip-fade-up">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 text-white shadow-lg shadow-orange-500/30">
                    <x-icon name="rocket" class="size-5" />
                </span>
                <div>
                    <div class="text-sm font-extrabold leading-5">نردبان</div>
                    <div class="text-[11px] text-slate-400">فروشگاه و محصولت را در صدر صفحهٔ اول ببر</div>
                </div>
            </div>
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">
        {{-- flash toast --}}
        @if (session('bump-flash'))
            <div class="mb-3 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-bold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <x-icon name="circle-check" class="size-4" />
                {{ session('bump-flash') }}
            </div>
        @endif

        {{-- ───────── hero card ───────── --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-bl from-orange-600 via-orange-500 to-amber-400 p-5 text-white shadow-xl shadow-orange-500/25">
            <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
                <div class="absolute -top-8 left-6 size-36 rounded-full bg-white/30 blur-2xl"></div>
                <div class="absolute -bottom-12 right-10 size-40 rounded-full bg-white/20 blur-2xl"></div>
            </div>

            <div class="relative">
                <p class="flex items-center gap-1.5 text-[11px] font-bold text-orange-100">
                    <x-icon name="rocket" class="size-3.5" />
                    مثل دیوار — برای مدت مشخص، در صدر صفحهٔ اول
                </p>
                <div class="mt-1.5 flex items-baseline gap-1.5">
                    <span class="text-2xl font-black tracking-tight">{{ \App\Support\Fmt::toFa($activeCount) }}</span>
                    <span class="text-xs font-bold text-orange-100">نردبان فعال</span>
                </div>
                <p class="mt-1 text-[10px] text-orange-100/90">نردبان‌شده‌ها در صدر صفحهٔ اول همهٔ مشتری‌ها دیده می‌شوند.</p>

                <div class="mt-4 rounded-2xl bg-white/15 px-3 py-2.5 backdrop-blur">
                    <div class="flex items-center justify-between text-[11px] font-bold">
                        <span class="text-orange-100">موجودی کیف پول شما</span>
                        <span>{{ \App\Support\Fmt::money($walletBalance) }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if (! $enabled)
            <div class="mt-3 flex items-start gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-3.5 py-3 text-[11px] font-semibold leading-5 text-amber-700 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300">
                <x-icon name="info" class="mt-0.5 size-4 shrink-0" />
                نردبان فعلاً توسط مدیر فعال نشده است. بعداً سر بزنید.
            </div>
        @endif

        {{-- ───────── نردبانِ خودِ فروشگاه (v0.25.0) ───────── --}}
        @if ($enabled)
            <h2 class="mt-5 mb-2 flex items-center gap-2 text-xs font-extrabold text-slate-500 dark:text-slate-400">
                <x-icon name="store" class="size-4 text-orange-500" />
                نردبان فروشگاه
            </h2>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-3 p-3.5">
                    <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-2xl bg-slate-100 dark:bg-slate-800">
                        @if ($shop->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->logo_path) }}" alt="لوگوی {{ $shop->name }}" loading="lazy" class="size-full object-cover">
                        @else
                            <span class="text-base font-black text-primary-600 dark:text-primary-400">{{ mb_substr($shop->name, 0, 1) }}</span>
                        @endif
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-1.5">
                            <span class="truncate text-xs font-extrabold">{{ $shop->name }}</span>
                            @if ($shopBump)
                                <span class="shrink-0 rounded-full bg-orange-100 px-2 py-0.5 text-[9px] font-black text-orange-700 dark:bg-orange-950/60 dark:text-orange-300">نردبان فعال</span>
                            @endif
                        </span>
                        <span class="mt-0.5 block text-[10px] leading-5 text-slate-400">
                            @if ($shopBump)
                                تا {{ \App\Support\JDate::date($shopBump->ends_at) }} در صدر فروشگاه‌های صفحهٔ اول — {{ \App\Support\Fmt::toFa($shopBump->daysLeft()) }} روز مانده
                            @else
                                فروشگاهت را مدتی در صدر لیست فروشگاه‌های صفحهٔ اول نشان بده
                            @endif
                        </span>
                    </span>
                </div>

                <div class="border-t border-slate-100 px-3.5 py-2.5 dark:border-slate-800">
                    <button type="button" wire:click="openShopBuy"
                            class="flex w-full items-center justify-center gap-2 rounded-2xl py-2.5 text-xs font-extrabold text-white shadow-md transition active:scale-[.98] {{ $shopBump ? 'bg-gradient-to-l from-orange-500 to-amber-500 shadow-orange-500/25 hover:from-orange-600 hover:to-amber-600' : 'bg-gradient-to-l from-orange-600 to-amber-500 shadow-orange-500/30 hover:from-orange-700 hover:to-amber-600' }}">
                        <x-icon name="rocket" class="size-4" />
                        {{ $shopBump ? 'تمدید / افزایش نردبان فروشگاه' : 'خرید طرح نردبان برای فروشگاه' }}
                    </button>
                </div>
            </div>
        @endif

        {{-- ───────── نردبان‌شده‌ها (جستجو + lazy load) — فقط وقتی نردبان فعال است ───────── --}}
        @if ($enabled)
            <h2 class="mt-6 mb-2 flex items-center justify-between gap-2 text-xs font-extrabold text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-2">
                    <x-icon name="rocket" class="size-4 text-orange-500" />
                    نردبان‌شده‌ها
                </span>
                <span class="text-[10px] font-bold text-slate-400">خرید نردبان جدید از کارت محصول در «فروشگاه من»</span>
            </h2>

            <input type="text" wire:model.live.debounce.300ms="bumpSearch" placeholder="جستجوی محصول در نردبان‌ها…"
                   class="w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs shadow-sm focus:border-orange-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900">

            <div class="mt-2 transition" wire:loading.class="opacity-40 pointer-events-none" wire:target="bumpSearch">
                @if (count($bumpRows) > 0)
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        @foreach ($bumpRows as $row)
                            <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-3.5 py-3 last:border-0 dark:border-slate-800" wire:key="bumprow-{{ $row->id }}">
                                <span class="flex min-w-0 items-center gap-2.5">
                                    <span class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                        @if ($row->product?->mainImage)
                                            <img src="{{ $row->product->mainImage->url() }}" alt="{{ $row->product->name }}" loading="lazy" class="size-full object-cover">
                                        @else
                                            <x-icon name="package" class="size-4 text-slate-300" />
                                        @endif
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-[11px] font-extrabold">{{ $row->product?->name ?? 'محصول حذف‌شده' }}</span>
                                        <span class="block text-[9px] text-slate-400">{{ \App\Support\JDate::date($row->starts_at) }} تا {{ \App\Support\JDate::date($row->ends_at) }} · {{ $row->sourceLabel() }}</span>
                                    </span>
                                </span>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-black {{ $row->isActive()
                                    ? 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300'
                                    : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $row->isActive() ? \App\Support\Fmt::toFa($row->daysLeft()).' روز مانده' : 'پایان یافته' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center dark:border-slate-700 dark:bg-slate-900">
                        @if (trim($bumpSearch) !== '')
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-300">نردبانی با این جستجو پیدا نشد.</p>
                        @else
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-300">هنوز نردبانی نداری.</p>
                            <p class="mt-1 text-[10px] leading-5 text-slate-400">از صفحهٔ «فروشگاه من»، روی کارت هر محصول دکمهٔ «نردبان» را بزن.</p>
                        @endif
                    </div>
                @endif

                {{-- lazy-load sentinel (IntersectionObserver → bump-load-more) --}}
                <div
                    x-data="{}"
                    x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
                    class="{{ $hasMore ? 'mt-4 flex justify-center py-2' : 'hidden' }}"
                    wire:loading.delay.class="opacity-0" wire:loading.delay.remove.class="opacity-100" wire:target="loadMore"
                    aria-hidden="true">
                    <span class="inline-block size-6 animate-spin rounded-full border-2 border-orange-500 border-t-transparent"></span>
                </div>
            </div>
        @endif
    </div>

    {{-- ───────── shop purchase confirm modal ───────── --}}
    <x-modal-panel open="$wire.buyShopOpen" max-width="sm" labelledby="buy-shop-bump-title" in-frame
                   close-js="__ipOpen = false; $wire.closeShopBuy()">
        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 id="buy-shop-bump-title" class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-orange-600/10 text-orange-600 dark:bg-orange-500/15 dark:text-orange-400">
                        <x-icon name="store" class="size-4" />
                    </span>
                    خرید نردبان فروشگاه
                </h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeShopBuy()"
                        class="grid size-8 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="p-5">
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                <span class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                    @if ($shop->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->logo_path) }}" alt="لوگوی {{ $shop->name }}" class="size-full object-cover">
                    @else
                        <span class="text-lg font-black text-primary-600 dark:text-primary-400">{{ mb_substr($shop->name, 0, 1) }}</span>
                    @endif
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-xs font-extrabold">{{ $shop->name }}</span>
                    <span class="mt-0.5 block text-[10px] text-slate-400">فروشگاه شما در صدر لیست فروشگاه‌های صفحهٔ اول</span>
                </span>
            </div>

            <div class="mt-4">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">طرح نردبان را انتخاب کنید</span>
                <div class="mt-1.5 grid gap-2">
                    @foreach ($plans as $plan)
                        <button type="button" wire:click="setShopPlan({{ $plan->id }})"
                                class="flex items-center justify-between gap-2 rounded-xl border px-3 py-2.5 transition {{ $buyShopPlan?->id === $plan->id
                                    ? 'border-orange-500 bg-orange-50 shadow-sm dark:border-orange-700 dark:bg-orange-950/30'
                                    : 'border-slate-200 hover:border-orange-300 dark:border-slate-700' }}">
                            <span class="flex min-w-0 items-center gap-2 text-start">
                                <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $buyShopPlan?->id === $plan->id ? 'bg-orange-500 text-white' : 'bg-orange-100 text-orange-600 dark:bg-orange-950/50 dark:text-orange-400' }}">
                                    <x-icon name="rocket" class="size-4" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-[11px] font-extrabold">{{ $plan->title }}</span>
                                    <span class="block text-[9px] text-slate-400">{{ $plan->daysLabel() }} در صدر فروشگاه‌های صفحهٔ اول</span>
                                </span>
                            </span>
                            <span class="shrink-0 text-[11px] font-black text-orange-600 dark:text-orange-400">{{ \App\Support\Fmt::money($plan->price) }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs dark:border-slate-800 dark:bg-slate-800/50">
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">هزینهٔ طرح انتخابی</span>
                    <span class="font-extrabold text-orange-600 dark:text-orange-400">{{ \App\Support\Fmt::money($buyShopPlan?->price ?? 0) }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">موجودی فعلی</span>
                    <span class="font-bold">{{ \App\Support\Fmt::money($walletBalance) }}</span>
                </div>
                <div class="mt-1 flex justify-between border-t border-dashed border-slate-200 pt-2 dark:border-slate-700">
                    <span class="text-slate-400">پس از خرید</span>
                    <span class="font-bold">{{ \App\Support\Fmt::money(max(0, $walletBalance - (int) ($buyShopPlan?->price ?? 0))) }}</span>
                </div>
            </div>

            <p class="mt-3 rounded-xl bg-orange-50 px-3 py-2 text-[10px] leading-5 text-orange-700 dark:bg-orange-950/30 dark:text-orange-300">
                اگر همین حالا نردبان فعال داشته باشید، روزهای جدید به انتهای آن اضافه می‌شوند (تمدید انباشتی).
            </p>

            @if ($errors->has('buyShop'))
                <p class="mt-2 rounded-xl bg-rose-50 px-3 py-2 text-[10px] font-bold text-rose-600 dark:bg-rose-950/30 dark:text-rose-300">{{ $errors->first('buyShop') }}</p>
            @endif

            <div class="mt-4 grid grid-cols-2 gap-2">
                <button type="button" x-on:click="__ipOpen = false; $wire.closeShopBuy()"
                        class="rounded-2xl border border-slate-200 py-3 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                    انصراف
                </button>
                <button type="button" wire:click="confirmShopBuy" wire:loading.attr="disabled" wire:target="confirmShopBuy" {{ ($buyShopPlan === null || $walletBalance < (int) ($buyShopPlan?->price ?? 0)) ? 'disabled' : '' }}
                        class="rounded-2xl py-3 text-xs font-extrabold transition active:scale-[.98] disabled:opacity-50 {{ $buyShopPlan !== null && $walletBalance >= (int) ($buyShopPlan?->price ?? 0) ? 'bg-gradient-to-l from-orange-500 to-amber-500 text-white shadow-lg shadow-orange-500/25' : 'bg-slate-200 text-slate-400 dark:bg-slate-800' }}">
                    پرداخت و فعال‌سازی
                </button>
            </div>
            @if ($buyShopPlan !== null && $walletBalance < (int) $buyShopPlan->price)
                <p class="mt-2 text-center text-[10px] font-bold text-amber-600 dark:text-amber-400">موجودی کافی نیست — از کیف پول شارژ کنید یا طرح ارزان‌تری انتخاب کنید.</p>
            @endif
        </div>
    </x-modal-panel>
</div>
