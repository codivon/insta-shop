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
                    <div class="text-[11px] text-slate-400">محصولت را در صدر صفحهٔ اول ببر</div>
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
                    <span class="text-2xl font-black tracking-tight">{{ \App\Support\Fmt::toFa($active->count()) }}</span>
                    <span class="text-xs font-bold text-orange-100">نردبان فعال</span>
                </div>
                <p class="mt-1 text-[10px] text-orange-100/90">محصولِ نردبان‌شده در نوار «نردبان‌ها» بالای صفحهٔ اول همه می‌بینند.</p>

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

        {{-- ───────── pick a product ───────── --}}
        @if ($enabled)
            <h2 class="mt-5 mb-2 flex items-center gap-2 text-xs font-extrabold text-slate-500 dark:text-slate-400">
                <x-icon name="sparkles" class="size-4 text-orange-500" />
                نردبان کردن یک محصول
            </h2>

            <input type="text" wire:model.live.debounce.300ms="productQuery" placeholder="جستجوی محصول از فروشگاه شما…"
                   class="w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs shadow-sm focus:border-orange-400 focus:outline-none dark:border-slate-800 dark:bg-slate-900">

            <div class="mt-2 grid grid-cols-2 gap-2">
                @forelse ($products as $product)
                    <button type="button" wire:click="openBuy({{ $product->id }})"
                            class="group flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-2.5 text-start shadow-sm transition hover:-translate-y-0.5 hover:border-orange-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-orange-800">
                        <span class="block aspect-square w-full overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                            @if ($product->mainImage)
                                <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy"
                                     class="size-full object-cover transition group-hover:scale-105">
                            @else
                                <span class="grid size-full place-items-center text-slate-300"><x-icon name="image" class="size-6" /></span>
                            @endif
                        </span>
                        <span>
                            <span class="block truncate text-[11px] font-extrabold">{{ $product->name }}</span>
                            <span class="mt-0.5 block text-[10px] font-black text-orange-600 dark:text-orange-400">{{ \App\Support\Fmt::money($product->price) }}</span>
                        </span>
                    </button>
                @empty
                    <div class="col-span-2 rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-xs text-slate-400 dark:border-slate-700 dark:bg-slate-900">
                        محصول منتشرشده‌ای پیدا نشد.
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ───────── active bumps ───────── --}}
        @if ($active->isNotEmpty())
            <h2 class="mt-6 mb-2 flex items-center gap-2 text-xs font-extrabold text-slate-500 dark:text-slate-400">
                <x-icon name="rocket" class="size-4 text-orange-500" />
                نردبان‌های فعال
            </h2>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @foreach ($active as $bump)
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-3.5 py-3 last:border-0 dark:border-slate-800">
                        <span class="flex min-w-0 items-center gap-2">
                            <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-950/50 dark:text-orange-400">
                                <x-icon name="rocket" class="size-4" />
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-[11px] font-extrabold">{{ $bump->product?->name ?? 'محصول حذف‌شده' }}</span>
                                <span class="block text-[9px] text-slate-400">تا {{ \App\Support\JDate::date($bump->ends_at) }} · {{ $bump->sourceLabel() }}</span>
                            </span>
                        </span>
                        <span class="shrink-0 rounded-full bg-orange-100 px-2 py-0.5 text-[9px] font-black text-orange-700 dark:bg-orange-950/60 dark:text-orange-300">
                            {{ \App\Support\Fmt::toFa($bump->daysLeft()) }} روز مانده
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ───────── history ───────── --}}
        @if ($history->isNotEmpty())
            <h2 class="mt-6 mb-2 flex items-center gap-2 text-xs font-extrabold text-slate-500 dark:text-slate-400">
                <x-icon name="clock" class="size-4" />
                سابقهٔ نردبان‌ها
            </h2>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @foreach ($history as $row)
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-3.5 py-2.5 last:border-0 dark:border-slate-800">
                        <span class="flex min-w-0 items-center gap-2">
                            <x-icon name="{{ $row->isActive() ? 'rocket' : 'clock' }}" class="size-4 shrink-0 {{ $row->isActive() ? 'text-orange-500' : 'text-slate-400' }}" />
                            <span class="min-w-0">
                                <span class="block truncate text-[11px] font-extrabold">{{ $row->product?->name ?? 'محصول حذف‌شده' }}</span>
                                <span class="block text-[9px] text-slate-400">{{ \App\Support\JDate::date($row->starts_at) }} تا {{ \App\Support\JDate::date($row->ends_at) }} · {{ $row->sourceLabel() }}</span>
                            </span>
                        </span>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-black {{ $row->isActive() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $row->isActive() ? 'فعال' : 'پایان یافته' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ───────── purchase confirm modal ───────── --}}
    <x-modal-panel open="$wire.buyOpen" max-width="sm" labelledby="buy-bump-title" in-frame
                   close-js="__ipOpen = false; $wire.closeBuy()">
        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 id="buy-bump-title" class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-orange-600/10 text-orange-600 dark:bg-orange-500/15 dark:text-orange-400">
                        <x-icon name="rocket" class="size-4" />
                    </span>
                    خرید نردبان
                </h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeBuy()"
                        class="grid size-8 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="p-5">
            @if ($buyProduct)
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                    <span class="block size-14 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                        @if ($buyProduct->mainImage)
                            <img src="{{ $buyProduct->mainImage->url() }}" alt="{{ $buyProduct->name }}" class="size-full object-cover">
                        @else
                            <span class="grid size-full place-items-center text-slate-300"><x-icon name="image" class="size-5" /></span>
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-xs font-extrabold">{{ $buyProduct->name }}</span>
                        <span class="mt-0.5 block text-[10px] text-slate-400">{{ \App\Support\Fmt::money($buyProduct->price) }} تومان</span>
                    </span>
                </div>

                <div class="mt-4">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">طرح نردبان را انتخاب کنید</span>
                    <div class="mt-1.5 grid gap-2">
                        @foreach ($plans as $plan)
                            <button type="button" wire:click="setBuyPlan({{ $plan->id }})"
                                    class="flex items-center justify-between gap-2 rounded-xl border px-3 py-2.5 transition {{ $buyPlan?->id === $plan->id
                                        ? 'border-orange-500 bg-orange-50 shadow-sm dark:border-orange-700 dark:bg-orange-950/30'
                                        : 'border-slate-200 hover:border-orange-300 dark:border-slate-700' }}">
                                <span class="flex min-w-0 items-center gap-2 text-start">
                                    <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $buyPlan?->id === $plan->id ? 'bg-orange-500 text-white' : 'bg-orange-100 text-orange-600 dark:bg-orange-950/50 dark:text-orange-400' }}">
                                        <x-icon name="rocket" class="size-4" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-[11px] font-extrabold">{{ $plan->title }}</span>
                                        <span class="block text-[9px] text-slate-400">{{ $plan->daysLabel() }} در صدر صفحهٔ اول</span>
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
                        <span class="font-extrabold text-orange-600 dark:text-orange-400">{{ \App\Support\Fmt::money($buyPlan?->price ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">موجودی فعلی</span>
                        <span class="font-bold">{{ \App\Support\Fmt::money($walletBalance) }}</span>
                    </div>
                    <div class="mt-1 flex justify-between border-t border-dashed border-slate-200 pt-2 dark:border-slate-700">
                        <span class="text-slate-400">پس از خرید</span>
                        <span class="font-bold">{{ \App\Support\Fmt::money(max(0, $walletBalance - (int) ($buyPlan?->price ?? 0))) }}</span>
                    </div>
                </div>

                <p class="mt-3 rounded-xl bg-orange-50 px-3 py-2 text-[10px] leading-5 text-orange-700 dark:bg-orange-950/30 dark:text-orange-300">
                    محصول در نوار «نردبان‌ها» صدر صفحهٔ اول نمایش داده می‌شود. اگر همین محصول نردبان فعال داشته باشد، روزهای جدید به انتهای آن اضافه می‌شوند.
                </p>

                @if ($errors->has('buy'))
                    <p class="mt-2 rounded-xl bg-rose-50 px-3 py-2 text-[10px] font-bold text-rose-600 dark:bg-rose-950/30 dark:text-rose-300">{{ $errors->first('buy') }}</p>
                @endif

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button type="button" x-on:click="__ipOpen = false; $wire.closeBuy()"
                            class="rounded-2xl border border-slate-200 py-3 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                        انصراف
                    </button>
                    <button type="button" wire:click="confirmBuy" wire:loading.attr="disabled" wire:target="confirmBuy" {{ ($buyPlan === null || $walletBalance < (int) ($buyPlan?->price ?? 0)) ? 'disabled' : '' }}
                            class="rounded-2xl py-3 text-xs font-extrabold transition active:scale-[.98] disabled:opacity-50 {{ $buyPlan !== null && $walletBalance >= (int) ($buyPlan?->price ?? 0) ? 'bg-gradient-to-l from-orange-500 to-amber-500 text-white shadow-lg shadow-orange-500/25' : 'bg-slate-200 text-slate-400 dark:bg-slate-800' }}">
                        پرداخت و فعال‌سازی
                    </button>
                </div>
                @if ($buyPlan !== null && $walletBalance < (int) $buyPlan->price)
                    <p class="mt-2 text-center text-[10px] font-bold text-amber-600 dark:text-amber-400">موجودی کافی نیست — از کیف پول شارژ کنید یا طرح ارزان‌تری انتخاب کنید.</p>
                @endif
            @endif
        </div>
    </x-modal-panel>
</div>
