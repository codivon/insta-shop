<div class="ip-fade-up space-y-5">

    {{-- ═══════════ date + shop filters ═══════════ --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <label class="block">
                <span class="mb-1.5 block text-[10px] font-bold text-slate-400">از تاریخ</span>
                <x-jdate-picker model="fromDate" placeholder="از ابتدا" />
            </label>
            <label class="block">
                <span class="mb-1.5 block text-[10px] font-bold text-slate-400">تا تاریخ</span>
                <x-jdate-picker model="toDate" placeholder="تا امروز" />
            </label>
            <label class="block">
                <span class="mb-1.5 block text-[10px] font-bold text-slate-400">فروشگاه</span>
                <select wire:model.live="shopId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-[11px] font-bold shadow-sm focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    <option value="0">همهٔ فروشگاه‌ها</option>
                    @foreach ($shopOptions as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button type="button" wire:click="resetFilters"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3.5 py-2.5 text-[11px] font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800">
                    <x-icon name="rotate" class="size-3.5" />
                    حذف فیلترها
                </button>
                <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2.5 text-[11px] font-bold text-white shadow-md shadow-emerald-600/25 transition hover:bg-emerald-700 disabled:opacity-50">
                    <span wire:loading wire:target="exportCsv" class="inline-block size-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <x-icon name="download" class="size-3.5" />
                    CSV
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════ cards ═══════════ --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="stl-c1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">سهم فروشنده‌ها (تسویه‌پذیر)</span>
                <span class="grid size-9 place-items-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-950/40"><x-icon name="coins" class="size-4.5" /></span>
            </div>
            <div class="mt-3 text-lg font-extrabold">{{ \App\Support\Fmt::money($metrics['payout']) }}</div>
            <div class="mt-1 text-[10.5px] text-slate-400">از {{ \App\Support\Fmt::fa($metrics['orders_count']) }} سفارش پرداخت‌شده</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="stl-c2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">تسویه‌شده (برداشت تأییدشده)</span>
                <span class="grid size-9 place-items-center rounded-xl bg-sky-50 text-sky-500 dark:bg-sky-950/40"><x-icon name="circle-check" class="size-4.5" /></span>
            </div>
            <div class="mt-3 text-lg font-extrabold">{{ \App\Support\Fmt::money($metrics['settled']) }}</div>
            <div class="mt-1 text-[10.5px] text-slate-400">واریز شده به حساب فروشنده‌ها</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="stl-c3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">در انتظار تسویه</span>
                <span class="grid size-9 place-items-center rounded-xl bg-amber-50 text-amber-500 dark:bg-amber-950/40"><x-icon name="clock" class="size-4.5" /></span>
            </div>
            <div class="mt-3 text-lg font-extrabold">{{ \App\Support\Fmt::money($metrics['pending_sum']) }}</div>
            <div class="mt-1 flex items-center justify-between text-[10.5px]">
                <span class="text-slate-400">{{ \App\Support\Fmt::fa($metrics['pending_count']) }} درخواست برداشت</span>
                <a href="{{ route('admin.withdrawals') }}" wire:navigate class="font-bold text-primary-600 hover:underline dark:text-primary-400">مدیریت برداشت‌ها</a>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="stl-c4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">کمیسیون پلتفرم</span>
                <span class="grid size-9 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-950/40"><x-icon name="wallet" class="size-4.5" /></span>
            </div>
            <div class="mt-3 text-lg font-extrabold">{{ \App\Support\Fmt::money($metrics['commission']) }}</div>
            <div class="mt-1 text-[10.5px] text-slate-400">پایه: {{ \App\Support\Fmt::money($metrics['goods']) }}</div>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-3">
        {{-- ═══════════ weekly settled chart ═══════════ --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="chart" class="size-4 text-primary-500" />
                روند تسویه‌ها
                <span class="ms-auto text-[9.5px] font-bold text-slate-400">۸ هفتهٔ اخیر (برداشت‌های تأییدشده)</span>
            </h3>
            @php $maxSettled = max($weekly->max('settled'), 1); @endphp
            <div class="mt-4 flex h-44 items-end gap-2">
                @foreach ($weekly as $bar)
                    <div class="group flex h-full flex-1 flex-col items-center justify-end gap-1">
                        <span class="text-[9px] font-bold text-slate-400 opacity-0 transition group-hover:opacity-100">{{ \App\Support\Fmt::moneyShort($bar['settled']) }}</span>
                        <div class="w-full rounded-t-lg bg-gradient-to-t from-emerald-600 to-emerald-400 transition-all group-hover:from-emerald-700 group-hover:to-emerald-500"
                             style="height: {{ max(4, (int) round($bar['settled'] / $maxSettled * 100)) }}%"></div>
                        <span class="truncate text-[9px] font-bold text-slate-400" title="{{ $bar['label'] }}">{{ $bar['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ═══════════ settlement share ═══════════ --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="sliders" class="size-4 text-primary-500" />
                تقسیم پول سفارش‌ها
            </h3>
            @php
                $base = max(1, $metrics['goods']);
                $pct  = fn (int $v) => (int) round($v / $base * 100);
            @endphp
            <div class="mt-4 flex h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <span class="bg-emerald-500" style="width: {{ $pct($metrics['payout']) }}%"></span>
                <span class="bg-primary-500" style="width: {{ $pct($metrics['commission']) }}%"></span>
            </div>
            <div class="mt-4 space-y-2.5">
                <div class="flex items-center justify-between rounded-xl bg-emerald-50/70 px-3.5 py-3 dark:bg-emerald-950/20">
                    <span class="flex items-center gap-2 text-[11.5px] font-bold text-emerald-700 dark:text-emerald-300">
                        <span class="inline-block size-2.5 rounded-sm bg-emerald-500"></span>
                        سهم فروشنده‌ها
                    </span>
                    <span class="text-xs font-extrabold">{{ \App\Support\Fmt::fa($pct($metrics['payout'])) }}٪ — {{ \App\Support\Fmt::money($metrics['payout']) }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-primary-50/70 px-3.5 py-3 dark:bg-primary-950/20">
                    <span class="flex items-center gap-2 text-[11.5px] font-bold text-primary-700 dark:text-primary-300">
                        <span class="inline-block size-2.5 rounded-sm bg-primary-500"></span>
                        کمیسیون پلتفرم
                    </span>
                    <span class="text-xs font-extrabold">{{ \App\Support\Fmt::fa($pct($metrics['commission'])) }}٪ — {{ \App\Support\Fmt::money($metrics['commission']) }}</span>
                </div>
                <p class="text-[10px] leading-5 text-slate-400">تقسیم بر اساس اسنپ‌شات کمیسیونِ سفارش‌های پرداخت‌شده در بازهٔ انتخابی است.</p>
            </div>
        </div>
    </div>

    {{-- ═══════════ per-shop settlement table ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <h3 class="text-sm font-extrabold">تسویه بر اساس فروشگاه</h3>
            <span class="text-[10.5px] font-bold text-slate-400">{{ \App\Support\Fmt::fa($shops->count()) }} فروشگاه</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-right text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60 text-[10.5px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                        <th class="px-5 py-3 font-semibold">فروشگاه</th>
                        <th class="px-3 py-3 font-semibold">سفارش‌ها</th>
                        <th class="px-3 py-3 font-semibold">مبالغ مشمول</th>
                        <th class="px-3 py-3 font-semibold">کمیسیون</th>
                        <th class="px-5 py-3 font-semibold">سهم فروشنده</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    @forelse ($shops as $shop)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="stls-{{ $shop->shop_id }}">
                            <td class="px-5 py-3.5 font-extrabold">{{ $shop->shop_name }}</td>
                            <td class="px-3 py-3.5">{{ \App\Support\Fmt::fa($shop->orders) }}</td>
                            <td class="px-3 py-3.5">{{ \App\Support\Fmt::money($shop->goods) }}</td>
                            <td class="px-3 py-3.5 font-bold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::money($shop->commission) }}</td>
                            <td class="px-5 py-3.5 font-extrabold text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::money($shop->payout) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                                    <x-icon name="store" class="size-6" />
                                </span>
                                <p class="mt-3 text-xs font-extrabold">تسویه‌ای در این بازه ثبت نشده است.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
