<div class="ip-fade-up space-y-6">

    {{-- ── welcome banner ── --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500 p-6 text-white shadow-xl shadow-primary-600/25 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
            <div class="absolute -top-10 left-10 size-56 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 right-24 size-64 rounded-full bg-white/20 blur-3xl"></div>
        </div>
        <div class="relative flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-xl font-extrabold sm:text-2xl">سلام مدیر عزیز 👋</h2>
                <p class="mt-2 max-w-lg text-sm leading-7 text-primary-100">
                    امروز {{ \App\Support\Fmt::fa($orderStats['todayCount']) }} سفارش ثبت شده و
                    {{ \App\Support\Fmt::money($orderStats['todayPaid']) }} فروش موفق داشتی.
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <a href="{{ route('admin.orders') }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-bold text-primary-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-primary-50">
                    <x-icon name="bag" class="size-4" />
                    سفارش‌ها
                </a>
                <a href="{{ route('admin.settings.appearance') }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-white/40 bg-white/10 px-4 py-2.5 text-sm font-bold text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/20">
                    <x-icon name="palette" class="size-4" />
                    رنگ‌بندی
                </a>
            </div>
        </div>
    </div>

    {{-- ── pending sellers alert ── --}}
    @if ($sellers['pending'] > 0)
        <a href="{{ route('admin.users.sellers', ['status' => 'pending']) }}"
           class="flex items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm font-semibold text-amber-800 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
            <span class="flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-xl bg-amber-100 dark:bg-amber-900/60">
                    <x-icon name="alert" class="size-4.5" />
                </span>
                {{ \App\Support\Fmt::fa($sellers['pending']) }} فروشنده در انتظار بررسی و تأیید است.
            </span>
            <span class="flex items-center gap-1 text-xs font-bold">
                بررسی کن
                <x-icon name="chevron-left" class="size-3.5" />
            </span>
        </a>
    @endif

    {{-- ── stat cards ── --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @php
            $cards = [
                ['icon' => 'store',       'label' => 'فروشندگان',  'value' => \App\Support\Fmt::fa($sellers['total']),
                 'hint' => $sellers['pending'] > 0 ? \App\Support\Fmt::fa($sellers['pending']).' در انتظار تأیید' : 'همه تأیید شده‌اند',
                 'href' => route('admin.users.sellers'),
                 'tone' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                 'hintTone' => $sellers['pending'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400'],
                ['icon' => 'users',       'label' => 'مشتریان',    'value' => \App\Support\Fmt::fa($customers['total']),
                 'hint' => $customers['blocked'] > 0 ? \App\Support\Fmt::fa($customers['blocked']).' مسدود' : 'بدون مسدود',
                 'href' => route('admin.users.customers'),
                 'tone' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
                 'hintTone' => 'text-slate-400'],
                ['icon' => 'store',        'label' => 'فروشگاه‌ها',  'value' => \App\Support\Fmt::fa($shops['total']),
                 'hint' => \App\Support\Fmt::fa($shops['active']).' فروشگاه فعال',
                 'href' => route('admin.shops'),
                 'tone' => 'bg-violet-500/10 text-violet-600 dark:text-violet-400',
                 'hintTone' => 'text-slate-400'],
                ['icon' => 'package',     'label' => 'محصولات',    'value' => \App\Support\Fmt::fa($products['total']),
                 'hint' => \App\Support\Fmt::fa($products['published']).' منتشرشده / '.\App\Support\Fmt::fa($products['draft']).' پیش‌نویس',
                 'href' => route('admin.products'),
                 'tone' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                 'hintTone' => 'text-slate-400'],
                ['icon' => 'bag',         'label' => 'سفارش‌ها',   'value' => \App\Support\Fmt::fa($orderStats['total']),
                 'hint' => \App\Support\Fmt::fa($orderStats['paid']).' پرداخت‌شده / '.\App\Support\Fmt::fa($orderStats['pending']).' در انتظار',
                 'href' => route('admin.orders'),
                 'tone' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                 'hintTone' => 'text-slate-400'],
                ['icon' => 'wallet',      'label' => 'حجم فروش موفق', 'value' => \App\Support\Fmt::money($orderStats['paidAmount']),
                 'hint' => 'مجموع سفارش‌های پرداخت‌شده',
                 'href' => route('admin.orders', ['status' => 'paid']),
                 'tone' => 'bg-teal-500/10 text-teal-600 dark:text-teal-400',
                 'hintTone' => 'text-slate-400'],
            ];
        @endphp
        @foreach ($cards as $card)
            <a href="{{ $card['href'] }}"
               class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="grid size-11 place-items-center rounded-2xl {{ $card['tone'] }}">
                        <x-icon name="{{ $card['icon'] }}" class="size-5" />
                    </span>
                    <x-icon name="chevron-left" class="size-4 text-slate-300 transition group-hover:text-primary-500" />
                </div>
                <div class="mt-4 text-2xl font-extrabold">{{ $card['value'] }}</div>
                <div class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ $card['label'] }}</div>
                <div class="mt-0.5 text-[11px] {{ $card['hintTone'] }}">{{ $card['hint'] }}</div>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-5">

        {{-- ── 7-day orders chart ── --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h3 class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                        <x-icon name="chart" class="size-4" />
                    </span>
                    سفارش‌های ۷ روز اخیر
                </h3>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                    مجموع: {{ \App\Support\Fmt::fa($chart->sum('orders')) }}
                </span>
            </div>

            <div class="mt-5 flex h-32 items-end justify-between gap-2" dir="ltr">
                @php $maxOrders = max($chart->max('orders'), 1); @endphp
                @foreach ($chart as $bar)
                    <div class="flex flex-1 flex-col items-center gap-1.5">
                        <div class="flex w-full flex-1 items-end">
                            <div class="w-full rounded-t-lg transition-all {{ $bar['today'] ? 'bg-gradient-to-t from-primary-600 to-primary-400 shadow-md shadow-primary-600/30' : 'bg-primary-200 dark:bg-primary-900' }}"
                                 style="height: {{ max(8, (int) round($bar['orders'] / $maxOrders * 100)) }}%"
                                 title="{{ $bar['label'] }}: {{ $bar['orders'] }} سفارش — {{ $bar['amount'] }} تومان موفق"></div>
                        </div>
                        <span class="text-[9px] font-bold {{ $bar['today'] ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}">{{ $bar['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                <div class="rounded-2xl bg-slate-50 p-3 text-center dark:bg-slate-800/60">
                    <div class="text-lg font-extrabold">{{ \App\Support\Fmt::fa($orderStats['todayCount']) }}</div>
                    <div class="mt-0.5 text-[10px] text-slate-400">سفارش امروز</div>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3 text-center dark:bg-slate-800/60">
                    <div class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::moneyShort($orderStats['todayPaid']) }}</div>
                    <div class="mt-0.5 text-[10px] text-slate-400">فروش موفق امروز (تومان)</div>
                </div>
            </div>
        </div>

        {{-- ── latest orders ── --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-3 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h3 class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                        <x-icon name="inbox" class="size-4" />
                    </span>
                    آخرین سفارش‌ها
                </h3>
                <a href="{{ route('admin.orders') }}" wire:navigate
                   class="flex items-center gap-1 text-[11px] font-bold text-primary-600 hover:text-primary-700 dark:text-primary-400">
                    همه سفارش‌ها
                    <x-icon name="chevron-left" class="size-3.5" />
                </a>
            </div>

            @if ($recentOrders->isEmpty())
                <div class="mt-4 flex flex-col items-center gap-2 rounded-2xl border border-dashed border-slate-300 py-10 text-center dark:border-slate-700">
                    <span class="grid size-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                        <x-icon name="inbox" class="size-6" />
                    </span>
                    <p class="text-sm font-semibold text-slate-500">هنوز سفارشی ثبت نشده است</p>
                </div>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[540px] text-right text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] text-slate-400 dark:border-slate-800">
                                <th class="pb-2.5 font-semibold">شماره</th>
                                <th class="pb-2.5 font-semibold">مشتری</th>
                                <th class="pb-2.5 font-semibold">مبلغ</th>
                                <th class="pb-2.5 font-semibold">تاریخ</th>
                                <th class="pb-2.5 font-semibold">وضعیت</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                            @foreach ($recentOrders as $order)
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="py-2.5 font-extrabold">#{{ \App\Support\Fmt::fa($order->id) }}</td>
                                    <td class="py-2.5">
                                        <span class="block text-xs font-semibold">{{ $order->customer?->full_name ?: 'بدون نام' }}</span>
                                        <span class="block text-[10px] text-slate-400" dir="ltr">{{ \App\Support\Fmt::toFa($order->customer?->phone ?? '—') }}</span>
                                    </td>
                                    <td class="py-2.5 text-xs font-bold">{{ \App\Support\Fmt::money($order->total_amount) }}</td>
                                    <td class="py-2.5 text-[11px] text-slate-400">{{ $order->dateFa() }}</td>
                                    <td class="py-2.5">
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ── quick actions ── --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['icon' => 'shield',    'label' => 'مدیریت فروشندگان', 'desc' => 'تأیید و مسدودسازی',   'href' => route('admin.users.sellers')],
            ['icon' => 'store',      'label' => 'فروشگاه‌ها',       'desc' => 'فعال / توقف فروشگاه', 'href' => route('admin.shops')],
            ['icon' => 'message',   'label' => 'تنظیمات پیامک',    'desc' => 'پروایدر و تست ارسال', 'href' => route('admin.settings.sms')],
            ['icon' => 'credit-card','label' => 'درگاه پرداخت',    'desc' => 'انتخاب و اعتبارنامه', 'href' => route('admin.settings.payment')],
        ] as $action)
            <a href="{{ $action['href'] }}" wire:navigate
               class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-primary-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-800">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-primary-600/10 text-primary-600 transition group-hover:bg-primary-600 group-hover:text-white dark:bg-primary-500/15 dark:text-primary-400">
                    <x-icon name="{{ $action['icon'] }}" class="size-4.5" />
                </span>
                <span>
                    <span class="block text-xs font-extrabold">{{ $action['label'] }}</span>
                    <span class="mt-0.5 block text-[10px] text-slate-400">{{ $action['desc'] }}</span>
                </span>
            </a>
        @endforeach
    </div>
</div>
