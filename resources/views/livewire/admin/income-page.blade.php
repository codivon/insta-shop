<div class="ip-fade-up space-y-4">

    {{-- ── header ── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid size-12 place-items-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-600/30">
                <x-icon name="coins" class="size-6" />
            </span>
            <div>
                <h1 class="text-lg font-black leading-6">درآمد پلتفرم</h1>
                <p class="mt-0.5 text-[11px] text-slate-400">کمیسیون‌های دریافتی از سفارش‌های پرداخت‌شده — ارسال و تخفیف مشمول کمیسیون نیستند</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="shopId" aria-label="فیلتر فروشگاه"
                    class="max-w-44 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                <option value="0">همه فروشگاه‌ها</option>
                @foreach ($shopOptions as $shopOption)
                    <option value="{{ $shopOption->id }}">{{ $shopOption->name }}</option>
                @endforeach
            </select>

            <div class="flex gap-1.5" role="group" aria-label="بازه زمانی">
                @foreach (['7' => '۷ روز', '30' => '۳۰ روز', '90' => '۹۰ روز', 'all' => 'همه'] as $key => $label)
                    <button type="button" wire:click="$set('range', '{{ $key }}')"
                            class="rounded-xl px-3 py-2 text-[11px] font-bold transition {{ $range === $key
                                ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── totals ── --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        @foreach ([
            ['label' => 'کمیسیون دریافتی', 'value' => \App\Support\Fmt::money($totals['commission']), 'icon' => 'coins', 'tone' => 'from-amber-500 to-orange-600'],
            ['label' => 'درآمد اشتراک', 'value' => \App\Support\Fmt::money($totals['subscription']), 'icon' => 'crown', 'tone' => 'from-yellow-500 to-amber-600'],
            ['label' => 'مجموع فروش کالا', 'value' => \App\Support\Fmt::money($totals['goods']), 'icon' => 'bag', 'tone' => 'from-emerald-500 to-teal-600'],
            ['label' => 'واریز به فروشندگان', 'value' => \App\Support\Fmt::money($totals['payout']), 'icon' => 'wallet', 'tone' => 'from-sky-500 to-cyan-600'],
            ['label' => 'سفارش پرداخت‌شده', 'value' => \App\Support\Fmt::fa($totals['orders']), 'icon' => 'check', 'tone' => 'from-rose-500 to-pink-600'],
        ] as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-[10.5px] font-bold text-slate-400">{{ $card['label'] }}</span>
                    <span class="grid size-8 place-items-center rounded-lg bg-gradient-to-bl {{ $card['tone'] }} text-white shadow-sm">
                        <x-icon name="{{ $card['icon'] }}" class="size-4" />
                    </span>
                </div>
                <div class="mt-2 text-base font-black sm:text-lg">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ── chart ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between">
            <h3 class="flex items-center gap-2 text-xs font-extrabold">
                <x-icon name="trending-up" class="size-4 text-primary-500" />
                کمیسیون ۱۴ روز اخیر
            </h3>
            <span class="text-[10px] font-bold text-slate-400">مجموع: {{ \App\Support\Fmt::money($chart->sum('commission')) }}</span>
        </div>

        @php $maxTotal = max($chart->max('commission'), 1); @endphp
        <div class="mt-4 flex h-40 items-end gap-1.5" dir="ltr">
            @foreach ($chart as $bar)
                <div class="group flex flex-1 flex-col items-center gap-1">
                    <div class="w-full rounded-t-md bg-gradient-to-t from-amber-500 to-amber-300 transition-all group-hover:from-amber-600"
                         style="height: {{ max(4, round($bar['commission'] / $maxTotal * 130)) }}px"
                         title="{{ $bar['label'] }} — {{ \App\Support\Fmt::money($bar['commission']) }}"></div>
                    <span class="text-[8.5px] font-bold text-slate-400">{{ $bar['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── receipts table ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-100 px-5 py-3.5 dark:border-slate-800">
            <h3 class="flex items-center gap-2 text-xs font-extrabold">
                <x-icon name="file-text" class="size-4 text-primary-500" />
                فهرست کمیسیون‌های دریافتی
            </h3>
        </div>

        @if ($rows->isEmpty())
            <div class="flex flex-col items-center gap-3 py-14 text-center">
                <span class="grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <x-icon name="coins" class="size-7" />
                </span>
                <p class="text-sm font-bold text-slate-500">در این بازه کمیسیونی ثبت نشده</p>
                <p class="text-xs text-slate-400">پس از پرداخت موفق سفارش‌ها، کمیسیون هر فروشگاه اینجا گزارش می‌شود.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                            <th class="px-5 py-3 font-semibold">سفارش</th>
                            <th class="px-3 py-3 font-semibold">فروشگاه</th>
                            <th class="px-3 py-3 font-semibold">مبنا (فروش کالا)</th>
                            <th class="px-3 py-3 font-semibold">نوع کمیسیون</th>
                            <th class="px-3 py-3 font-semibold">کمیسیون (تومان)</th>
                            <th class="px-3 py-3 font-semibold">واریز به فروشنده</th>
                            <th class="px-5 py-3 font-semibold">تاریخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach ($rows as $row)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="inc-{{ $row->order_id }}-{{ $row->shop_id }}">
                                <td class="px-5 py-3.5 text-xs font-extrabold">#{{ \App\Support\Fmt::toFa($row->order_code) }}</td>
                                <td class="px-3 py-3.5">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[10.5px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        <x-icon name="store" class="size-3" />
                                        {{ $row->shop_name }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-xs font-bold">{{ \App\Support\Fmt::money($row->items_total) }}</td>
                                <td class="px-3 py-3.5 text-[11px]">
                                    @if ($row->type === 'fixed')
                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 font-bold text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">مبلغ ثابت</span>
                                    @else
                                        <span class="rounded-full bg-sky-50 px-2 py-0.5 font-bold text-sky-600 dark:bg-sky-950/40 dark:text-sky-400">{{ \App\Support\Fmt::toFa($row->value) }}٪</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-xs font-black text-amber-600 dark:text-amber-400">{{ \App\Support\Fmt::money($row->commission) }}</td>
                                <td class="px-3 py-3.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::money($row->payout) }}</td>
                                <td class="px-5 py-3.5 text-[10px] text-slate-400">{{ \App\Support\JDate::date($row->paid_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($hasMore)
                <div class="border-t border-slate-100 p-4 text-center dark:border-slate-800">
                    <button type="button" wire:click="loadMore" wire:loading.attr="disabled"
                            class="rounded-xl border border-slate-200 px-5 py-2 text-xs font-bold text-slate-600 transition hover:border-primary-400 hover:text-primary-600 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300">
                        نمایش بیشتر
                    </button>
                </div>
            @endif
        @endif
    </div>
</div>
