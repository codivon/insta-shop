<div class="ip-fade-up space-y-4">

    {{-- ── toolbar ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="relative w-full lg:max-w-sm">
                <x-icon name="search" class="absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="search" wire:model.live.debounce.350ms="search" placeholder="شماره پیگیری، مرجع درگاه یا موبایل…"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-4 pr-10 text-sm shadow-sm transition focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800"
                       aria-label="جستجوی سفارش">
                <span wire:loading wire:target="search" class="absolute left-3 top-1/2 -translate-y-1/2">
                    <x-icon name="rotate" class="size-4 animate-spin text-primary-500" />
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2" role="group" aria-label="فیلتر وضعیت">
                @foreach ([
                    ['key' => 'all',      'label' => 'همه ('.\App\Support\Fmt::fa($stats['all']).')'],
                    ['key' => 'paid',     'label' => 'پرداخت‌شده ('.\App\Support\Fmt::fa($stats['paid']).')'],
                    ['key' => 'pending',  'label' => 'در انتظار ('.\App\Support\Fmt::fa($stats['pending']).')'],
                    ['key' => 'failed',   'label' => 'ناموفق ('.\App\Support\Fmt::fa($stats['failed']).')'],
                    ['key' => 'canceled', 'label' => 'لغوشده ('.\App\Support\Fmt::fa($stats['canceled']).')'],
                ] as $tab)
                    <button type="button" wire:click="$set('status', '{{ $tab['key'] }}')"
                            class="rounded-xl px-3.5 py-2 text-xs font-bold transition {{ $status === $tab['key']
                                ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── table ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($orders->isEmpty())
            <div class="flex flex-col items-center gap-3 py-16 text-center">
                <span class="grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <x-icon name="bag" class="size-7" />
                </span>
                <p class="text-sm font-bold text-slate-500">سفارشی یافت نشد</p>
                <p class="text-xs text-slate-400">{{ $search !== '' || $status !== 'all' ? 'فیلترها را تغییر دهید.' : 'با اولین خرید مشتریان، اینجا نمایش داده می‌شود.' }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                            <th class="px-5 py-3 font-semibold">شماره</th>
                            <th class="px-3 py-3 font-semibold">مشتری</th>
                            <th class="px-3 py-3 font-semibold">اقلام</th>
                            <th class="px-3 py-3 font-semibold">مبلغ</th>
                            <th class="px-3 py-3 font-semibold">درگاه</th>
                            <th class="px-3 py-3 font-semibold">تاریخ</th>
                            <th class="px-3 py-3 font-semibold">وضعیت</th>
                            <th class="px-5 py-3 font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach ($orders as $order)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="o-{{ $order->id }}">
                                <td class="px-5 py-3.5 font-extrabold">#{{ \App\Support\Fmt::fa($order->id) }}</td>
                                <td class="px-3 py-3.5">
                                    <span class="block text-xs font-semibold">{{ $order->customer?->full_name ?: $order->receiver_name }}</span>
                                    <span class="block text-[10px] text-slate-400" dir="ltr">{{ \App\Support\Fmt::toFa($order->customer?->phone ?? $order->delivery_phone) }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-xs">{{ \App\Support\Fmt::fa($order->items_count) }} کالا</td>
                                <td class="px-3 py-3.5 text-xs font-bold">{{ \App\Support\Fmt::money($order->total_amount) }}</td>
                                <td class="px-3 py-3.5 text-[11px] text-slate-500">{{ $order->gatewayLabel() }}</td>
                                <td class="px-3 py-3.5 text-[11px] text-slate-400">{{ $order->dateFa() }}</td>
                                <td class="px-3 py-3.5">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('admin.orders.show', $order) }}" wire:navigate
                                       class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-3 py-1.5 text-[11px] font-bold text-primary-600 transition hover:bg-primary-100 dark:bg-primary-950/40 dark:text-primary-400">
                                        جزئیات
                                        <x-icon name="chevron-left" class="size-3" />
                                    </a>
                                </td>
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

    <p class="text-center text-[11px] text-slate-400">
        نمایش {{ \App\Support\Fmt::fa(min($orders->count(), $perPage)) }} از {{ \App\Support\Fmt::fa($stats['all']) }} سفارش
    </p>
</div>
