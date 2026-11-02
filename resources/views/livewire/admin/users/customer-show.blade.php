<div class="ip-fade-up space-y-4">

    {{-- ── header ── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.customers') }}" wire:navigate
               class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-400 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
               aria-label="بازگشت به مشتریان">
                <x-icon name="chevron-right" class="size-4.5" />
            </a>
            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-600 text-lg font-black text-white shadow-lg shadow-sky-600/25">
                {{ mb_substr($customer->full_name ?: '۹', 0, 1) }}
            </span>
            <div class="min-w-0">
                <h1 class="truncate text-base font-black leading-6">{{ $customer->full_name ?: 'بدون نام' }}</h1>
                <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-slate-400">
                    <span dir="ltr">{{ \App\Support\Fmt::toFa($customer->phone) }}</span>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $customer->statusClasses() }}">{{ $customer->statusLabel() }}</span>
                </div>
            </div>
        </div>

        <span class="inline-flex items-center gap-1.5 self-start rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-bold text-slate-500 sm:self-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
            <x-icon name="calendar" class="size-3.5" />
            عضو از {{ \App\Support\JDate::date($customer->created_at) }}
        </span>
    </div>

    {{-- ── tabs ── --}}
    <div class="scrollbar-hide -mx-1 flex gap-1.5 overflow-x-auto px-1" role="tablist" aria-label="بخش‌های صفحه مشتری">
        @foreach ([
            'overview'  => ['نمای کلی', 'chart'],
            'orders'    => ['سفارش‌ها', 'bag'],
            'favorites' => ['علاقه‌مندی‌ها', 'heart'],
        ] as $key => [$label, $icon])
            <button type="button" wire:click="$set('tab', '{{ $key }}')" role="tab"
                    :aria-selected="$wire.tab === '{{ $key }}'" wire:key="tab-{{ $key }}"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl px-3.5 py-2 text-[11.5px] font-extrabold transition {{ $tab === $key
                        ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                        : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                <x-icon name="{{ $icon }}" class="size-3.5" />
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ══════════════ overview ══════════════ --}}
    @if ($tab === 'overview')
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['label' => 'کل سفارش‌ها', 'value' => \App\Support\Fmt::fa($stats['orders']), 'icon' => 'bag', 'tone' => 'from-sky-500 to-cyan-600'],
                ['label' => 'پرداخت‌شده', 'value' => \App\Support\Fmt::fa($stats['paid_orders']), 'icon' => 'circle-check', 'tone' => 'from-emerald-500 to-teal-600'],
                ['label' => 'مجموع خرید', 'value' => \App\Support\Fmt::moneyShort($stats['spent']), 'icon' => 'wallet', 'tone' => 'from-amber-500 to-orange-600'],
                ['label' => 'علاقه‌مندی / آدرس', 'value' => \App\Support\Fmt::fa($stats['favorites']).' / '.\App\Support\Fmt::fa($stats['addresses']), 'icon' => 'heart', 'tone' => 'from-rose-500 to-pink-600'],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-[10.5px] font-bold text-slate-400">{{ $card['label'] }}</span>
                        <span class="grid size-8 place-items-center rounded-lg bg-gradient-to-bl {{ $card['tone'] }} text-white shadow-sm">
                            <x-icon name="{{ $card['icon'] }}" class="size-4" />
                        </span>
                    </div>
                    <div class="mt-2 text-lg font-black">{{ $card['value'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <x-icon name="trending-up" class="size-4 text-primary-500" />
                    خرید ۱۴ روز اخیر
                </h3>
                <span class="text-[10px] font-bold text-slate-400">مجموع: {{ \App\Support\Fmt::money($chart->sum('total')) }}</span>
            </div>

            @php $maxTotal = max($chart->max('total'), 1); @endphp
            <div class="mt-4 flex h-40 items-end gap-1.5" dir="ltr">
                @foreach ($chart as $bar)
                    <div class="group flex flex-1 flex-col items-center gap-1">
                        <div class="w-full rounded-t-md bg-gradient-to-t from-primary-600 to-primary-400 transition-all group-hover:from-primary-700"
                             style="height: {{ max(4, round($bar['total'] / $maxTotal * 130)) }}px"
                             title="{{ $bar['label'] }} — {{ \App\Support\Fmt::money($bar['total']) }}"></div>
                        <span class="text-[8.5px] font-bold text-slate-400">{{ $bar['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ══════════════ orders ══════════════ --}}
    @if ($tab === 'orders')
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap gap-1.5">
                @foreach (array_merge(['all' => 'همه'], collect(Order::STATUSES)->mapWithKeys(fn ($s) => [$s => ['pending' => 'در انتظار', 'paid' => 'پرداخت‌شده', 'failed' => 'ناموفق', 'canceled' => 'لغوشده'][$s]])->all()) as $key => $label)
                    <button type="button" wire:click="$set('orderStatus', '{{ $key }}')"
                            class="rounded-xl px-3 py-2 text-[11px] font-bold transition {{ $orderStatus === $key
                                ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[520px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10.5px] text-slate-400 dark:border-slate-800">
                            <th class="px-3 py-2.5 font-semibold">سفارش</th>
                            <th class="px-3 py-2.5 font-semibold">اقلام</th>
                            <th class="px-3 py-2.5 font-semibold">مبلغ</th>
                            <th class="px-3 py-2.5 font-semibold">تاریخ</th>
                            <th class="px-3 py-2.5 font-semibold">وضعیت</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @forelse ($orders as $order)
                            <tr wire:key="o-{{ $order->id }}" class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-3 py-3 text-xs font-extrabold">#{{ \App\Support\Fmt::toFa(str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)) }}</td>
                                <td class="px-3 py-3 text-[11px]">{{ \App\Support\Fmt::fa($order->items_count) }} کالا</td>
                                <td class="px-3 py-3 text-xs font-bold">{{ \App\Support\Fmt::moneyShort($order->total_amount) }}</td>
                                <td class="px-3 py-3 text-[10px] text-slate-400">{{ \App\Support\JDate::date($order->created_at) }}</td>
                                <td class="px-3 py-3">
                                    @php
                                        $tone = match ($order->status) {
                                            'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
                                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
                                            'failed', 'canceled' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
                                        };
                                        $lbl = ['pending' => 'در انتظار', 'paid' => 'پرداخت‌شده', 'failed' => 'ناموفق', 'canceled' => 'لغوشده'][$order->status];
                                    @endphp
                                    <span class="rounded-full px-2 py-0.5 text-[9.5px] font-bold {{ $tone }}">{{ $lbl }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-xs text-slate-400">سفارشی یافت نشد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ══════════════ favorites ══════════════ --}}
    @if ($tab === 'favorites')
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($favorites as $product)
                <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="fav-{{ $product->id }}">
                    <span class="grid aspect-square w-full place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                        @if ($product->mainImage)
                            <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" class="size-full object-cover" loading="lazy">
                        @else
                            <x-icon name="package" class="size-7 text-slate-300" />
                        @endif
                    </span>
                    <p class="mt-2 truncate text-[11px] font-bold">{{ $product->name }}</p>
                    <p class="text-[9.5px] text-slate-400">{{ $product->shop?->name ?? '—' }}</p>
                    <p class="mt-1 text-[11px] font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::moneyShort($product->price) }}</p>
                </div>
            @empty
                <div class="col-span-2 rounded-2xl border border-dashed border-slate-300 p-8 text-center text-xs text-slate-400 sm:col-span-3 lg:col-span-4 dark:border-slate-700">
                    این مشتری هنوز محصولی را لایک نکرده است.
                </div>
            @endforelse
        </div>
    @endif
</div>
