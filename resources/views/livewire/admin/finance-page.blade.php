<div class="ip-fade-up space-y-5" x-data="{ tab: 'chart' }">

    {{-- ═══════════ headline cards ═══════════ --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="fin-c1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">حجم سفارش‌های پرداخت‌شده</span>
                <span class="grid size-9 place-items-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-950/40"><x-icon name="circle-check" class="size-4.5" /></span>
            </div>
            <div class="mt-3 text-lg font-extrabold">{{ \App\Support\Fmt::money($metrics['paid_total']) }}</div>
            <div class="mt-1 text-[10.5px] text-slate-400">جمع مبلغ سفارش‌های پرداخت‌شده فعّال — {{ \App\Support\Fmt::fa($metrics['paid_count']) }} سفارش</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="fin-c2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">مبالغ مشمول کمیسیون</span>
                <span class="grid size-9 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-950/40"><x-icon name="coins" class="size-4.5" /></span>
            </div>
            <div class="mt-3 text-lg font-extrabold">{{ \App\Support\Fmt::money($metrics['goods_total']) }}</div>
            <div class="mt-1 text-[10.5px] text-slate-400">پایهٔ محاسبهٔ کمیسیون سهم‌ها</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="fin-c3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">جمع تسویه‌شده</span>
                <span class="grid size-9 place-items-center rounded-xl bg-sky-50 text-sky-500 dark:bg-sky-950/40"><x-icon name="wallet" class="size-4.5" /></span>
            </div>
            <div class="mt-3 text-lg font-extrabold">{{ \App\Support\Fmt::money($metrics['settled']) }}</div>
            <div class="mt-1 text-[10.5px] text-slate-400">واریزی به کیف پول فروشنده‌های شبکه</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="fin-c4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">موجودی کیف پول فروشنده‌ها</span>
                <span class="grid size-9 place-items-center rounded-xl bg-amber-50 text-amber-500 dark:bg-amber-950/40"><x-icon name="credit-card" class="size-4.5" /></span>
            </div>
            <div class="mt-3 text-lg font-extrabold">{{ \App\Support\Fmt::money($metrics['wallet_float']) }}</div>
            <div class="mt-1 text-[10.5px] text-slate-400">جمع موجودی فعلی کیف‌ها (در انتظار تسویه + باقیمانده)</div>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-3">

        {{-- ═══════════ settlement share composition ═══════════ --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="fin-comp">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="chart" class="size-4 text-primary-500" />
                ترکیب سهم‌های تسویه‌شده
            </h3>
            @php
                $shareBase = max(1, $metrics['commission'] + $metrics['settled'] + $metrics['pending_sum']);
                $sharePct  = fn (int $v) => (int) round($v / $shareBase * 100);
            @endphp
            <div class="mt-4 flex h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <span class="bg-primary-500" style="width: {{ $sharePct($metrics['commission']) }}%"></span>
                <span class="bg-emerald-500" style="width: {{ $sharePct($metrics['settled']) }}%"></span>
                <span class="bg-amber-400" style="width: {{ $sharePct($metrics['pending_sum']) }}%"></span>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2.5">
                <div class="rounded-xl border border-primary-100 bg-primary-50/60 p-3 dark:border-primary-900/60 dark:bg-primary-950/30">
                    <p class="text-[10px] font-bold text-slate-400">سهم پلتفرم (کمیسیون)</p>
                    <p class="mt-1 text-xs font-extrabold text-primary-700 dark:text-primary-300">{{ \App\Support\Fmt::money($metrics['commission']) }}</p>
                </div>
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3 dark:border-emerald-900/60 dark:bg-emerald-950/30">
                    <p class="text-[10px] font-bold text-slate-400">سهم فروشنده‌ها (تسویه‌شده)</p>
                    <p class="mt-1 text-xs font-extrabold text-emerald-700 dark:text-emerald-300">{{ \App\Support\Fmt::money($metrics['settled']) }}</p>
                </div>
                <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-3 dark:border-amber-900/60 dark:bg-amber-950/30">
                    <p class="text-[10px] font-bold text-slate-400">در انتظار تسویه</p>
                    <p class="mt-1 text-xs font-extrabold text-amber-700 dark:text-amber-300">{{ \App\Support\Fmt::money($metrics['pending_sum']) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3 dark:border-slate-800 dark:bg-slate-800/40">
                    <p class="text-[10px] font-bold text-slate-400">باقیماندهٔ کیف‌ها</p>
                    <p class="mt-1 text-xs font-extrabold">{{ \App\Support\Fmt::money($metrics['wallet_float']) }}</p>
                </div>
            </div>
        </div>

        {{-- ═══════════ payments & wallets status panel ═══════════ --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="fin-status">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="sliders" class="size-4 text-primary-500" />
                وضعیت پرداخت‌ها و کیف‌پول‌ها
            </h3>
            <div class="mt-4 space-y-2.5">
                <a href="{{ route('admin.withdrawals') }}" wire:navigate
                   class="flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50/70 px-3.5 py-3 transition hover:border-amber-300 dark:border-amber-900/60 dark:bg-amber-950/20">
                    <span class="flex items-center gap-2 text-[11.5px] font-bold text-amber-700 dark:text-amber-300">
                        <x-icon name="clock" class="size-4" />
                        برداشت‌های در انتظار
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-black text-amber-700 dark:bg-amber-950 dark:text-amber-300">{{ \App\Support\Fmt::fa($metrics['pending_count']) }} مورد</span>
                        <span class="text-xs font-extrabold">{{ \App\Support\Fmt::money($metrics['pending_sum']) }}</span>
                    </span>
                </a>
                <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3.5 py-3 dark:bg-slate-800/50">
                    <span class="flex items-center gap-2 text-[11.5px] font-bold text-slate-600 dark:text-slate-300">
                        <x-icon name="circle-check" class="size-4 text-emerald-500" />
                        جمع برداشت‌های پرداخت‌شده
                    </span>
                    <span class="text-xs font-extrabold">{{ \App\Support\Fmt::money($metrics['settled']) }}</span>
                </div>
                <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3.5 py-3 dark:bg-slate-800/50">
                    <span class="flex items-center gap-2 text-[11.5px] font-bold text-slate-600 dark:text-slate-300">
                        <x-icon name="wallet" class="size-4 text-primary-500" />
                        موجودی کیف فروشنده‌ها
                    </span>
                    <span class="text-xs font-extrabold">{{ \App\Support\Fmt::money($metrics['wallet_float']) }}</span>
                </div>
                <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3.5 py-3 dark:bg-slate-800/50">
                    <span class="flex items-center gap-2 text-[11.5px] font-bold text-slate-600 dark:text-slate-300">
                        <x-icon name="coins" class="size-4 text-primary-500" />
                        درآمد پلتفرم از کمیسیون
                    </span>
                    <span class="text-xs font-extrabold">{{ \App\Support\Fmt::money($metrics['commission']) }}</span>
                </div>
            </div>
        </div>

        {{-- ═══════════ 6-month chart ═══════════ --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="fin-chart">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="trending-up" class="size-4 text-primary-500" />
                روند ۶ ماه اخیر
                <span class="ms-auto text-[9.5px] font-bold text-slate-400">بر حسب ماه شمسی</span>
            </h3>
            @php $maxBar = max($monthly->max('paid'), $monthly->max('settled'), 1); @endphp
            <div class="mt-4 flex h-40 items-end gap-2">
                @foreach ($monthly as $bar)
                    <div class="group flex h-full flex-1 flex-col items-center justify-end gap-1">
                        <div class="flex w-full flex-1 items-end justify-center gap-0.5">
                            <div class="w-1/2 rounded-t-lg bg-gradient-to-t from-primary-600 to-primary-400 transition-all group-hover:from-primary-700 group-hover:to-primary-500"
                                 style="height: {{ max(4, (int) round($bar['paid'] / $maxBar * 100)) }}%"
                                 title="پرداخت‌شده: {{ \App\Support\Fmt::money($bar['paid']) }}"></div>
                            <div class="w-1/2 rounded-t-lg bg-emerald-300 transition-all group-hover:bg-emerald-400 dark:bg-emerald-700"
                                 style="height: {{ max(4, (int) round($bar['settled'] / $maxBar * 100)) }}%"
                                 title="تسویه‌شده: {{ \App\Support\Fmt::money($bar['settled']) }}"></div>
                        </div>
                        <span class="truncate text-[9px] font-bold text-slate-400">{{ explode(' ', $bar['label'])[0] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 flex items-center justify-center gap-4 border-t border-dashed border-slate-200 pt-2.5 text-[10px] font-bold text-slate-500 dark:border-slate-700">
                <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-sm bg-primary-500"></span> سفارش‌های پرداخت‌شده</span>
                <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-sm bg-emerald-400 dark:bg-emerald-700"></span> تسویه‌شده</span>
            </div>
        </div>
    </div>

    {{-- ═══════════ monthly breakdown table ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="fin-monthly">
        <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <h3 class="text-sm font-extrabold">روند ۶ ماه اخیر (تسویه بر حسب ماه شمسی)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-right text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60 text-[10.5px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                        <th class="px-5 py-3 font-semibold">ماه</th>
                        <th class="px-3 py-3 font-semibold">سفارش‌های پرداخت‌شده</th>
                        <th class="px-3 py-3 font-semibold">مبالغ مشمول کمیسیون</th>
                        <th class="px-3 py-3 font-semibold">سهم پلتفرم</th>
                        <th class="px-3 py-3 font-semibold">تسویه‌شده</th>
                        <th class="px-5 py-3 font-semibold">حجم پرداخت‌ها</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    @foreach ($monthly->reverse()->values() as $row)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="finm-{{ $row['key'] }}">
                            <td class="px-5 py-3.5 font-extrabold">{{ $row['label'] }}</td>
                            <td class="px-3 py-3.5">{{ \App\Support\Fmt::fa($row['orders']) }}</td>
                            <td class="px-3 py-3.5">{{ \App\Support\Fmt::money($row['goods']) }}</td>
                            <td class="px-3 py-3.5 font-bold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::money($row['commission']) }}</td>
                            <td class="px-3 py-3.5 font-bold text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::money($row['settled']) }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                        <div class="h-full rounded-full bg-primary-500" style="width: {{ max(2, (int) round($row['paid'] / max($monthly->max('paid'), 1) * 100)) }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-bold">{{ \App\Support\Fmt::money($row['paid']) }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══════════ transactions ledger ═══════════ --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="fin-ledger">
        <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <h3 class="text-sm font-extrabold">تراکنش‌های کیف پول</h3>
        </div>

        {{-- filters --}}
        <div class="grid gap-3 border-b border-slate-100 px-5 py-4 sm:grid-cols-2 lg:grid-cols-5 dark:border-slate-800">
            <label class="block lg:col-span-2">
                <span class="mb-1.5 block text-[10px] font-bold text-slate-400">جستجو در توضیح تراکنش…</span>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute end-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                    <input type="text" wire:model.live.debounce.400ms="search"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 pe-9 text-[11px] shadow-sm focus:border-primary-400 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                           placeholder="توضیح، شبا یا شماره سفارش…">
                </div>
            </label>
            <label class="block">
                <span class="mb-1.5 block text-[10px] font-bold text-slate-400">از تاریخ</span>
                <x-jdate-picker model="fromDate" placeholder="۱۴۰۵/۰۶/۰۱" />
            </label>
            <label class="block">
                <span class="mb-1.5 block text-[10px] font-bold text-slate-400">تا تاریخ</span>
                <x-jdate-picker model="toDate" placeholder="۱۴۰۵/۰۶/۳۱" />
            </label>
            <div class="grid grid-cols-2 gap-2">
                <label class="block">
                    <span class="mb-1.5 block text-[10px] font-bold text-slate-400">نوع</span>
                    <select wire:model.live="typeFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-2.5 text-[11px] font-bold shadow-sm focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        <option value="all">همه</option>
                        <option value="deposit">واریز فروش</option>
                        <option value="withdrawal">برداشت</option>
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-[10px] font-bold text-slate-400">وضعیت</span>
                    <select wire:model.live="statusFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-2.5 text-[11px] font-bold shadow-sm focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        <option value="all">همه</option>
                        <option value="done">تسویه شد</option>
                        <option value="pending">در انتظار</option>
                        <option value="rejected">رد شد</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 px-5 py-3">
            <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-[11px] font-bold text-white shadow-md shadow-emerald-600/25 transition hover:bg-emerald-700 disabled:opacity-50">
                <span wire:loading wire:target="exportCsv" class="inline-block size-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                <x-icon name="download" class="size-3.5" />
                خروجی CSV
            </button>
            <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3.5 py-2 text-[11px] font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800">
                <x-icon name="rotate" class="size-3.5" />
                حذف فیلترها
            </button>
            <span class="ms-auto text-[10.5px] font-bold text-slate-400">{{ \App\Support\Fmt::fa($ledgerTotal) }} تراکنش</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-right text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60 text-[10.5px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                        <th class="px-5 py-3 font-semibold">نوع</th>
                        <th class="px-3 py-3 font-semibold">تاریخ</th>
                        <th class="px-3 py-3 font-semibold">مبلغ</th>
                        <th class="px-3 py-3 font-semibold">فروشگاه</th>
                        <th class="px-3 py-3 font-semibold">مرجع</th>
                        <th class="px-3 py-3 font-semibold">وضعیت</th>
                        <th class="px-5 py-3 font-semibold">توضیح</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    @forelse ($ledger as $tx)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="fintx-{{ $tx->id }}">
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 font-bold {{ $tx->isDeposit() ? 'text-emerald-600 dark:text-emerald-400' : 'text-violet-600 dark:text-violet-400' }}">
                                    <x-icon name="{{ $tx->isDeposit() ? 'arrow-down-left' : 'arrow-up-left' }}" class="size-3.5" />
                                    {{ $tx->typeLabel() }}
                                </span>
                            </td>
                            <td class="px-3 py-3.5 text-[10.5px] text-slate-500">{{ $tx->created_at ? \App\Support\JDate::dateTime($tx->created_at) : '—' }}</td>
                            <td class="px-3 py-3.5 font-extrabold {{ $tx->isDeposit() ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $tx->amountLabel() }}</td>
                            <td class="px-3 py-3.5">{{ $tx->wallet?->seller?->full_name ?? '—' }}</td>
                            <td class="px-3 py-3.5">
                                @if ($tx->order_id)
                                    <a href="{{ route('admin.orders.show', $tx->order_id) }}" wire:navigate class="font-bold text-primary-600 hover:underline dark:text-primary-400" dir="ltr">#{{ \App\Support\Fmt::toFa(str_pad((string) $tx->order_id, 5, '0', STR_PAD_LEFT)) }}</a>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3.5"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $tx->statusClasses() }}">{{ $tx->statusLabel() }}</span></td>
                            <td class="max-w-[280px] px-5 py-3.5"><span class="line-clamp-2 text-[10.5px] text-slate-500 dark:text-slate-400">{{ $tx->description }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                                    <x-icon name="clock" class="size-6" />
                                </span>
                                <p class="mt-3 text-xs font-extrabold">تراکنشی یافت نشد.</p>
                                <p class="mt-1 text-[10.5px] text-slate-400">فیلترها را تغییر دهید یا بازهٔ زمانی دیگری انتخاب کنید.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- lazy load --}}
        @if ($hasMore && $ledger->isNotEmpty())
            <div class="flex justify-center border-t border-slate-100 py-3 dark:border-slate-800">
                <div x-data="{}" x-init="new IntersectionObserver((e) => { if (e[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '240px' }).observe($el)"
                     class="inline-block size-6 animate-spin rounded-full border-2 border-primary-500 border-t-transparent" wire:loading.delay.class="opacity-0" wire:target="loadMore"
                     aria-hidden="true"></div>
            </div>
        @endif
    </div>
</div>
