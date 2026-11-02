<div class="ip-fade-up space-y-4">

    {{-- ── header ── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.sellers') }}" wire:navigate
               class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-400 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
               aria-label="بازگشت به فروشندگان">
                <x-icon name="chevron-right" class="size-4.5" />
            </a>
            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-lg font-black text-white shadow-lg shadow-emerald-600/25">
                {{ mb_substr($seller->full_name ?: ($seller->instagram_username ?: '۹'), 0, 1) }}
            </span>
            <div class="min-w-0">
                <h1 class="truncate text-base font-black leading-6">{{ $seller->full_name ?: '@'.$seller->instagram_username }}</h1>
                <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-slate-400">
                    <span dir="ltr">{{ \App\Support\Fmt::toFa($seller->phone) }}</span>
                    @if ($seller->instagram_username)
                        <span class="flex items-center gap-1" dir="ltr"><x-icon name="instagram" class="size-3" /> {{ $seller->instagram_username }}</span>
                    @endif
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $seller->statusClasses() }}">{{ $seller->statusLabel() }}</span>
                </div>
            </div>
        </div>

        <span class="inline-flex items-center gap-1.5 self-start rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-bold text-slate-500 sm:self-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
            <x-icon name="store" class="size-3.5" />
            {{ $shop?->name ?? 'بدون فروشگاه' }}
        </span>
    </div>

    {{-- ── tabs ── --}}
    <div class="scrollbar-hide -mx-1 flex gap-1.5 overflow-x-auto px-1" role="tablist" aria-label="بخش‌های صفحه فروشنده">
        @foreach ([
            'overview'  => ['نمای کلی', 'chart'],
            'products'  => ['محصولات', 'package'],
            'orders'    => ['سفارش‌ها', 'bag'],
            'discounts' => ['کدهای تخفیف', 'tag'],
            'wallet'    => ['کیف پول', 'wallet'],
            'catalog'   => ['دسته، ارسال، فیلتر و کارت', 'layers'],
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
                ['label' => 'محصولات', 'value' => \App\Support\Fmt::fa($stats['products']), 'sub' => \App\Support\Fmt::fa($stats['published']).' منتشرشده', 'icon' => 'package', 'tone' => 'from-sky-500 to-cyan-600'],
                ['label' => 'سفارش پرداخت‌شده', 'value' => \App\Support\Fmt::fa($stats['orders']), 'sub' => \App\Support\Fmt::moneyShort($stats['goods_total']).' فروش کالا', 'icon' => 'bag', 'tone' => 'from-emerald-500 to-teal-600'],
                ['label' => 'موجودی کیف پول', 'value' => \App\Support\Fmt::moneyShort($stats['wallet_balance']), 'sub' => 'تومان', 'icon' => 'wallet', 'tone' => 'from-amber-500 to-orange-600'],
                ['label' => 'کدهای تخفیف', 'value' => \App\Support\Fmt::fa($stats['discounts']), 'sub' => \App\Support\Fmt::fa($stats['views']).' بازدید محصولات', 'icon' => 'tag', 'tone' => 'from-rose-500 to-pink-600'],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-[10.5px] font-bold text-slate-400">{{ $card['label'] }}</span>
                        <span class="grid size-8 place-items-center rounded-lg bg-gradient-to-bl {{ $card['tone'] }} text-white shadow-sm">
                            <x-icon name="{{ $card['icon'] }}" class="size-4" />
                        </span>
                    </div>
                    <div class="mt-2 text-lg font-black">{{ $card['value'] }}</div>
                    <div class="text-[10px] text-slate-400">{{ $card['sub'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- 14-day chart --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <x-icon name="trending-up" class="size-4 text-primary-500" />
                    فروش ۱۴ روز اخیر (سفارش‌های پرداخت‌شده)
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

        {{-- commission + shop info --}}
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold"><x-icon name="coins" class="size-4 text-amber-500" /> کمیسیون</h3>
                @if ($seller->hasCommissionOverride())
                    <p class="mt-2 text-xs font-bold">{{ $seller->commission_type === 'fixed' ? 'مبلغ ثابت: '.\App\Support\Fmt::money($seller->commission_value) : \App\Support\Fmt::toFa($seller->commission_value).'٪ از فروش کالا' }}</p>
                @else
                    <p class="mt-2 text-xs font-bold text-slate-400">پیش‌فرض سراسری ({{ \App\Models\Setting::get('shops.commission_type', 'percent') === 'fixed' ? 'ثابت '.\App\Support\Fmt::money((int) \App\Models\Setting::get('shops.commission_value', 0)) : \App\Support\Fmt::toFa((int) \App\Models\Setting::get('shops.commission_value', 0)).'٪' }})</p>
                @endif
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold"><x-icon name="store" class="size-4 text-primary-500" /> فروشگاه</h3>
                <p class="mt-2 text-xs font-bold">{{ $shop?->name ?? '—' }}</p>
                <p class="mt-0.5 text-[10px] text-slate-400">
                    {{ \App\Support\Fmt::fa($stats['categories']) }} دسته · {{ \App\Support\Fmt::fa($stats['carriers']) }} روش ارسال · {{ \App\Support\Fmt::fa($stats['filters']) }} فیلتر
                </p>
            </div>
        </div>
    @endif

    {{-- ══════════════ products ══════════════ --}}
    @if ($tab === 'products')
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <label class="relative flex-1">
                    <x-icon name="search" class="absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" wire:model.live.debounce.350ms="productSearch" placeholder="جستجوی محصول…"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                </label>
                <div class="flex gap-1.5">
                    @foreach (['all' => 'همه', 'published' => 'منتشرشده', 'draft' => 'پیش‌نویس'] as $key => $label)
                        <button type="button" wire:click="$set('productStatus', '{{ $key }}')"
                                class="rounded-xl px-3 py-2 text-[11px] font-bold transition {{ $productStatus === $key
                                    ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                    : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 space-y-2">
                @forelse ($products as $product)
                    <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-2.5 transition hover:border-primary-200 dark:border-slate-800" wire:key="p-{{ $product->id }}">
                        <span class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800">
                            @if ($product->mainImage)
                                <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" class="size-full object-cover" loading="lazy">
                            @else
                                <x-icon name="package" class="size-4.5 text-slate-300" />
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-xs font-bold">{{ $product->name }}</div>
                            <div class="mt-0.5 flex items-center gap-2 text-[10px] text-slate-400">
                                @if ($product->category)
                                    <span>{{ $product->category->name }}</span>
                                @endif
                                <span>موجودی {{ \App\Support\Fmt::fa($product->stock) }}</span>
                                <span>{{ \App\Support\Fmt::fa($product->views) }} بازدید</span>
                            </div>
                        </div>
                        <div class="shrink-0 text-xs font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::moneyShort($product->price) }}</div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[9.5px] font-bold {{ $product->status === 'published'
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                            : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $product->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}
                        </span>
                    </div>
                @empty
                    <p class="py-8 text-center text-xs text-slate-400">محصولی یافت نشد.</p>
                @endforelse
            </div>

            @if ($hasMore)
                <div class="mt-3 text-center">
                    <button type="button" wire:click="loadMore" class="rounded-xl border border-slate-200 px-4 py-2 text-[11px] font-bold text-slate-500 transition hover:border-primary-400 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300">
                        نمایش بیشتر
                    </button>
                </div>
            @endif
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
                <table class="w-full min-w-[560px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10.5px] text-slate-400 dark:border-slate-800">
                            <th class="px-3 py-2.5 font-semibold">سفارش</th>
                            <th class="px-3 py-2.5 font-semibold">مشتری</th>
                            <th class="px-3 py-2.5 font-semibold">مبلغ کل</th>
                            <th class="px-3 py-2.5 font-semibold">تاریخ</th>
                            <th class="px-3 py-2.5 font-semibold">وضعیت</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @forelse ($orders as $order)
                            <tr wire:key="o-{{ $order->id }}" class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-3 py-3 text-xs font-extrabold">#{{ \App\Support\Fmt::toFa(str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)) }}</td>
                                <td class="px-3 py-3 text-[11px]">{{ $order->customer?->full_name ?: \App\Support\Fmt::toFa($order->customer?->phone ?? '—') }}</td>
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

    {{-- ══════════════ discounts ══════════════ --}}
    @if ($tab === 'discounts')
        <div class="grid gap-3 sm:grid-cols-2">
            @forelse ($discounts as $discount)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="d-{{ $discount->id }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded-lg bg-slate-900 px-2.5 py-1 font-mono text-[11px] font-extrabold text-white dark:bg-white dark:text-slate-900" dir="ltr">{{ $discount->code }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[9.5px] font-bold {{ $discount->published
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                            : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $discount->published ? 'فعال' : 'پنهان' }}
                        </span>
                    </div>
                    <p class="mt-2 truncate text-xs font-bold">{{ $discount->title }}</p>
                    <div class="mt-1 flex flex-wrap gap-x-3 gap-y-0.5 text-[10px] text-slate-400">
                        <span>{{ $discount->type === 'percent' ? \App\Support\Fmt::toFa($discount->percent).'٪' : \App\Support\Fmt::moneyShort($discount->price) }}</span>
                        <span>مصرف {{ \App\Support\Fmt::fa($discount->used_count) }}</span>
                        @if ($discount->ends_at)
                            <span>تا {{ \App\Support\JDate::date($discount->ends_at) }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 rounded-2xl border border-dashed border-slate-300 p-8 text-center text-xs text-slate-400 dark:border-slate-700">
                    کد تخفیفی ثبت نشده است.
                </div>
            @endforelse
        </div>
    @endif

    {{-- ══════════════ wallet ══════════════ --}}
    @if ($tab === 'wallet')
        <div class="grid gap-3 lg:grid-cols-3">
            <div class="rounded-2xl bg-gradient-to-br from-primary-600 to-primary-800 p-5 text-white shadow-lg shadow-primary-600/25 lg:col-span-1">
                <p class="text-[11px] font-bold text-white/70">موجودی فعلی کیف پول</p>
                <p class="mt-2 text-2xl font-black">{{ \App\Support\Fmt::money($wallet?->balance ?? 0) }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold"><x-icon name="list" class="size-4 text-primary-500" /> گردش حساب (۲۰ مورد اخیر)</h3>
                <div class="scroll-slim mt-3 max-h-96 space-y-2 overflow-y-auto">
                    @forelse ($transactions as $tx)
                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-2.5 dark:border-slate-800" wire:key="tx-{{ $tx->id }}">
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $tx->isDeposit()
                                ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400'
                                : 'bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400' }}">
                                <x-icon name="{{ $tx->isDeposit() ? 'arrow-down-left' : 'arrow-up-right' }}" class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-[11px] font-bold">{{ $tx->description ?: $tx->typeLabel() }}</div>
                                <div class="text-[9.5px] text-slate-400">{{ \App\Support\JDate::date($tx->created_at) }} · {{ $tx->statusLabel() }}
                                    @if ($tx->commission > 0)
                                        · کمیسیون {{ \App\Support\Fmt::moneyShort($tx->commission) }}
                                    @endif
                                </div>
                            </div>
                            <span class="shrink-0 text-xs font-extrabold {{ $tx->isDeposit() ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $tx->amountLabel() }}
                            </span>
                        </div>
                    @empty
                        <p class="py-8 text-center text-xs text-slate-400">تراکنشی ثبت نشده است.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════ catalog (categories / carriers / filters / cards) ══════════════ --}}
    @if ($tab === 'catalog')
        <div class="grid gap-3 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold"><x-icon name="layers" class="size-4 text-primary-500" /> دسته‌بندی‌های فروشگاه</h3>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @forelse ($categories as $category)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10.5px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $category->name }}</span>
                    @empty
                        <p class="text-[11px] text-slate-400">دسته‌ای ثبت نشده.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold"><x-icon name="credit-card" class="size-4 text-emerald-500" /> کارت‌های بانکی</h3>
                <div class="mt-3 space-y-2">
                    @forelse ($bankCards as $card)
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 p-2.5 dark:border-slate-800" wire:key="bc-{{ $card->id }}">
                            <div class="min-w-0">
                                <div class="truncate text-[11px] font-bold">{{ $card->bankName() }} — {{ $card->full_name }}</div>
                                <div class="text-[10px] text-slate-400" dir="ltr">{{ $card->maskedCard() }}</div>
                            </div>
                            @if ($card->is_default)
                                <span class="rounded-full bg-primary-50 px-2 py-0.5 text-[9px] font-bold text-primary-600 dark:bg-primary-950/40 dark:text-primary-300">پیش‌فرض</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-[11px] text-slate-400">کارتی ثبت نشده.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold"><x-icon name="truck" class="size-4 text-sky-500" /> روش‌های ارسال و تعرفه‌ها</h3>
                <div class="mt-3 space-y-2">
                    @forelse ($carriers as $carrier)
                        <div class="rounded-xl border border-slate-100 p-2.5 dark:border-slate-800" wire:key="c-{{ $carrier->id }}">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-extrabold">{{ $carrier->title }}</span>
                                <span class="text-[9.5px] font-bold {{ $carrier->tariffs->isNotEmpty() ? 'text-emerald-500' : 'text-rose-500' }}">
                                    {{ $carrier->tariffSummaryLabel() }}
                                </span>
                            </div>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach ($carrier->tariffs as $tariff)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                        {{ $tariff->zoneLabel() }} · {{ $tariff->weightLabel() }} · {{ \App\Support\Fmt::moneyShort($tariff->cost) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-[11px] text-slate-400">روش ارسالی ثبت نشده.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold"><x-icon name="filter" class="size-4 text-sky-500" /> فیلترهای فروشگاه</h3>
                <div class="mt-3 space-y-2">
                    @forelse ($filters as $filter)
                        <div class="rounded-xl border border-slate-100 p-2.5 dark:border-slate-800" wire:key="f-{{ $filter->id }}">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-extrabold">{{ $filter->title }}</span>
                                <span class="text-[9.5px] font-bold {{ $filter->published ? 'text-emerald-500' : 'text-slate-400' }}">{{ $filter->published ? 'نمایش' : 'پنهان' }}</span>
                            </div>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach ($filter->items as $item)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $item->spec_name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-[11px] text-slate-400">فیلتری ثبت نشده.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
