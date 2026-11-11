<div class="ip-fade-up space-y-4">

    {{-- ── header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-base font-black">اشتراک فروشندگان</h1>
            <p class="mt-0.5 text-xs text-slate-400">اعطای دستی، تمدید، لغو و گزارش اشتراک‌های فعال و درآمد اشتراک</p>
        </div>
        <button type="button" wire:click="openGrant"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-amber-500/25 transition hover:bg-amber-600 active:scale-95">
            <x-icon name="crown" class="size-4" />
            اعطای اشتراک
        </button>
    </div>

    {{-- ── stats ── --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                <span class="grid size-8 place-items-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-950/50 dark:text-emerald-400"><x-icon name="crown" class="size-4" /></span>
                اشتراک‌های فعال
            </div>
            <div class="mt-2 text-xl font-black">{{ \App\Support\Fmt::toFa($stats['active']) }}</div>
            <div class="text-[10px] text-slate-400">متعلق به {{ \App\Support\Fmt::toFa($stats['active_sellers']) }} فروشنده</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                <span class="grid size-8 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"><x-icon name="store" class="size-4" /></span>
                بدون اشتراک فعال
            </div>
            <div class="mt-2 text-xl font-black">{{ \App\Support\Fmt::toFa($stats['without']) }}</div>
            <div class="text-[10px] text-slate-400">فروشندگان روی نرخ سراسری</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                <span class="grid size-8 place-items-center rounded-xl bg-amber-50 text-amber-500 dark:bg-amber-950/50 dark:text-amber-400"><x-icon name="coins" class="size-4" /></span>
                درآمد اشتراک (کل)
            </div>
            <div class="mt-2 text-xl font-black text-amber-600 dark:text-amber-400">{{ \App\Support\Fmt::fa($stats['revenue']) }}</div>
            <div class="text-[10px] text-slate-400">تومان — خرید کیف پول + اعطای پرداخت‌شده</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                <span class="grid size-8 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-950/50 dark:text-primary-400"><x-icon name="timer" class="size-4" /></span>
                دورهٔ آزمایشی خودکار
            </div>
            <div class="mt-2 text-xl font-black">{{ \App\Support\Fmt::toFa($stats['trial_days']) }} <span class="text-xs font-bold text-slate-400">روز</span></div>
            <div class="text-[10px] text-slate-400">در «تنظیمات ← تنظیمات فروشگاه‌ها»</div>
        </div>
    </div>

    {{-- ── filters ── --}}
    <div class="flex flex-wrap items-center gap-2">
        <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 dark:border-slate-800 dark:bg-slate-900">
            @foreach (['all' => 'همه', 'active' => 'فعال', 'expired' => 'منقضی'] as $key => $label)
                <button type="button" wire:click="$set('status', '{{ $key }}')"
                        class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold transition {{ $status === $key ? 'bg-primary-600 text-white shadow' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجوی فروشنده (نام، آیدی اینستا، موبایل)"
               class="w-72 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-800 dark:bg-slate-900">
    </div>

    {{-- ── ledger table ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="hidden grid-cols-12 gap-2 border-b border-slate-100 bg-slate-50 px-4 py-2.5 text-[10px] font-black text-slate-400 lg:grid dark:border-slate-800 dark:bg-slate-800/50">
            <span class="col-span-3">فروشنده</span>
            <span class="col-span-3">پلن / دوره</span>
            <span class="col-span-2">اعتبار</span>
            <span class="col-span-2">مبلغ / منبع</span>
            <span class="col-span-2 text-end">وضعیت / عملیات</span>
        </div>

        @forelse ($rows as $row)
            @php
                $isActive = $row->isActive();
                $seller = $row->seller;
            @endphp
            <div class="grid grid-cols-2 items-center gap-2 border-b border-slate-100 px-4 py-3 last:border-0 lg:grid-cols-12 dark:border-slate-800">
                <div class="col-span-2 lg:col-span-3">
                    <div class="text-xs font-extrabold">{{ $seller?->full_name ?: 'فروشنده #'.\App\Support\Fmt::toFa($row->seller_id) }}</div>
                    <div class="mt-0.5 text-[10px] text-slate-400" dir="ltr">
                        @if ($seller?->instagram_username) &#64;{{ $seller->instagram_username }} · @endif {{ $seller?->phone }}
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <span class="block text-[11px] font-extrabold">{{ $row->title() }}</span>
                    <span class="text-[10px] text-slate-400">
                        {{ \App\Support\JDate::date($row->starts_at) }} ← {{ \App\Support\JDate::date($row->ends_at) }}
                        @if ($row->plan && $row->plan->commission_type)
                            · {{ $row->plan->commission_type === 'fixed' ? 'کمیسیون ثابت '.\App\Support\Fmt::fa($row->plan->commission_value) : 'کمیسیون '.\App\Support\Fmt::toFa($row->plan->commission_value).'٪' }}
                        @endif
                    </span>
                </div>

                <div class="lg:col-span-2">
                    @if ($isActive)
                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-[9px] font-black text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">{{ \App\Support\Fmt::toFa($row->daysLeft()) }} روز مانده</span>
                    @else
                        <span class="rounded-full bg-slate-100 px-2 py-1 text-[9px] font-black text-slate-500 dark:bg-slate-800 dark:text-slate-400">منقضی</span>
                    @endif
                </div>

                <div class="lg:col-span-2">
                    <span class="block text-[11px] font-black {{ $row->price_paid > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400' }}">
                        {{ $row->price_paid > 0 ? \App\Support\Fmt::money($row->price_paid) : 'رایگان' }}
                    </span>
                    <span class="text-[10px] text-slate-400">{{ $row->sourceLabel() }}</span>
                </div>

                <div class="col-span-2 flex items-center justify-end gap-1.5 lg:col-span-2">
                    @if ($isActive)
                        <button type="button" wire:click="revoke({{ $row->id }})"
                                wire:confirm="اشتراک «{{ $row->title() }}» همین حالا لغو شود؟ فروشنده اعلان می‌گیرد و باید پلن تهیه کند."
                                title="لغو اشتراک"
                                class="grid size-8 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40">
                            <x-icon name="circle-x" class="size-4" />
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-xs text-slate-400">رکوردی یافت نشد.</div>
        @endforelse

        @if ($hasMore && $rows->count() > 0)
            <div class="border-t border-slate-100 p-3 text-center dark:border-slate-800">
                <button type="button" wire:click="loadMore" class="rounded-xl border border-slate-200 px-4 py-2 text-[11px] font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                    نمایش بیشتر
                </button>
            </div>
        @endif
    </div>

    {{-- ── grant modal (standard modal-panel — v0.23.0 fix: the hand-rolled
         fixed-inset markup had no reliable height cap/scroll and its close
         buttons were unreachable on some devices) ── --}}
    <x-modal-panel open="$wire.grantOpen" max-width="lg" labelledby="grant-modal-title" z-class="z-[75]"
                   close-js="__ipOpen = false; $wire.closeGrant()">
        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 id="grant-modal-title" class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-amber-600/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                        <x-icon name="crown" class="size-4" />
                    </span>
                    اعطای اشتراک به فروشنده
                </h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeGrant()"
                        class="grid size-8 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="space-y-3 p-5">
            {{-- seller picker --}}
            <div class="rounded-2xl border border-slate-200 p-3 dark:border-slate-700">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">فروشنده *</span>
                @if ($grantSellerId)
                    @php $picked = \App\Models\Seller::find($grantSellerId); @endphp
                    <div class="mt-2 flex items-center justify-between rounded-xl bg-emerald-50 px-3 py-2 dark:bg-emerald-950/30">
                        <span class="text-xs font-extrabold text-emerald-700 dark:text-emerald-300">{{ $picked?->full_name ?: 'فروشنده #'.\App\Support\Fmt::toFa($grantSellerId) }} @if($picked?->instagram_username) (&#64;{{ $picked->instagram_username }}) @endif</span>
                        <button type="button" wire:click="$set('grantSellerId', null)" class="text-[10px] font-bold text-rose-500">تغییر</button>
                    </div>
                @else
                    <input type="text" wire:model.live="grantSellerQuery" placeholder="جستجو: نام، آیدی یا موبایل…"
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    <div class="scroll-slim mt-2 max-h-44 space-y-1 overflow-y-auto">
                        @forelse ($sellerOptions as $option)
                            <button type="button" wire:click="pickSeller({{ $option->id }})"
                                    class="flex w-full items-center justify-between rounded-xl border border-slate-100 px-3 py-2 text-start text-[11px] font-bold transition hover:border-primary-300 hover:bg-primary-50 dark:border-slate-800 dark:hover:border-primary-800 dark:hover:bg-primary-950/20">
                                <span>{{ $option->full_name ?: 'فروشنده #'.\App\Support\Fmt::toFa($option->id) }} @if($option->instagram_username) (&#64;{{ $option->instagram_username }}) @endif</span>
                                <span class="text-[10px] font-semibold text-slate-400" dir="ltr">{{ $option->phone }}</span>
                            </button>
                        @empty
                            <p class="py-2 text-center text-[10px] text-slate-400">فروشنده‌ای یافت نشد.</p>
                        @endforelse
                    </div>
                @endif
                @error('grantSellerId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>

            {{-- plan picker --}}
            <label class="block">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">پلن *</span>
                <select wire:model="grantPlanId"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    <option value="">— انتخاب پلن —</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->title }} — {{ $plan->durationLabel() }} — {{ \App\Support\Fmt::money($plan->price) }}</option>
                    @endforeach
                </select>
                @error('grantPlanId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </label>

            {{-- price --}}
            <label class="block">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">مبلغ دریافتی (تومان) — ۰ یا خالی = هدیه/رایگان</span>
                <input type="number" min="0" wire:model="grantPrice" placeholder="مبلغی که واقعاً دریافت کرده‌اید (مثلاً کارت‌به‌کارت)"
                       class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                @error('grantPrice') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </label>

            <label class="block">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">توضیح (اختیاری)</span>
                <input type="text" wire:model="grantNote" maxlength="200" placeholder="مثلاً: پرداخت کارت‌به‌کارت مورخ…"
                       class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                @error('grantNote') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </label>

            <p class="rounded-xl bg-slate-50 px-3 py-2 text-[10px] leading-5 text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                اگر همین پلن از قبل فعال باشد، {{ \App\Support\Fmt::toFa(optional($plans->firstWhere('id', (int) $grantPlanId))->duration_days ?? 0) }} روز به انتهای آن اضافه می‌شود (تمدید).
                پلن دیگر → اشتراک فعلی بسته و پلن جدید فوراً فعال می‌شود. کمیسیونِ پلن در «کمیسیون اختصاصی» فروشنده ثبت و فروشنده اعلان می‌گیرد.
            </p>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                <button type="button" x-on:click="__ipOpen = false; $wire.closeGrant()"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    انصراف
                </button>
                <button type="button" wire:click="submitGrant" wire:loading.attr="disabled" wire:target="submitGrant"
                        class="rounded-xl bg-amber-500 px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-amber-500/25 transition hover:bg-amber-600 active:scale-95 disabled:opacity-50">
                    فعال‌سازی اشتراک
                </button>
            </div>
        </div>
    </x-modal-panel>
</div>
