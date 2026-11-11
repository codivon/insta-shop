<div class="ip-fade-up space-y-6">

    {{-- ── welcome banner ── --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500 p-6 text-white shadow-xl shadow-primary-600/25 sm:p-8">
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
                <a href="{{ route('admin.settings', ['tab' => 'appearance']) }}"
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

    {{-- ── stat cards — 2 compact columns on mobile so nothing looks cut-off/empty ── --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-3">
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
               class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="grid size-10 place-items-center rounded-2xl sm:size-11 {{ $card['tone'] }}">
                        <x-icon name="{{ $card['icon'] }}" class="size-5" />
                    </span>
                    <x-icon name="chevron-left" class="size-4 shrink-0 text-slate-300 transition group-hover:text-primary-500" />
                </div>
                <div class="mt-3 text-xl font-extrabold sm:mt-4 sm:text-2xl">{{ $card['value'] }}</div>
                <div class="mt-1 text-[13px] font-semibold text-slate-600 sm:text-sm dark:text-slate-300">{{ $card['label'] }}</div>
                <div class="mt-0.5 text-[11px] {{ $card['hintTone'] }}">{{ $card['hint'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- ── v0.23.0 — platform revenue: hero number + 14-day stacked chart ── --}}
    <div class="min-w-0 rounded-2xl border border-amber-200 bg-white p-5 shadow-sm dark:border-amber-900/60 dark:bg-slate-900" data-testid="revenue-card">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <span class="grid size-12 place-items-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-xl shadow-amber-600/25">
                    <x-icon name="coins" class="size-6" />
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2 text-xs font-extrabold text-amber-700 dark:text-amber-300">
                        درآمد پلتفرم (کمیسیون + اشتراک)
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[9px] font-black text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">از ابتدا</span>
                    </div>
                    <div class="mt-1 text-2xl font-black tracking-tight text-amber-600 dark:text-amber-400">{{ \App\Support\Fmt::money($revenue['total']) }}</div>
                    <div class="mt-0.5 text-[10px] font-semibold text-slate-400">
                        تومان — ۳۰ روز اخیر: {{ \App\Support\Fmt::moneyShort($revenue['total30']) }}
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-[11px] font-bold">
                <span class="flex items-center gap-1.5 text-slate-500 dark:text-slate-300">
                    <span class="size-2.5 rounded-sm bg-gradient-to-t from-amber-500 to-amber-300"></span>
                    کمیسیون: {{ \App\Support\Fmt::moneyShort($revenue['commission']) }}
                </span>
                <span class="flex items-center gap-1.5 text-slate-500 dark:text-slate-300">
                    <span class="size-2.5 rounded-sm bg-gradient-to-t from-violet-600 to-violet-400"></span>
                    اشتراک: {{ \App\Support\Fmt::moneyShort($revenue['subscription']) }}
                </span>
                <a href="{{ route('admin.income') }}" wire:navigate class="flex items-center gap-1 text-primary-600 hover:text-primary-700 dark:text-primary-400">
                    گزارش کامل
                    <x-icon name="chevron-left" class="size-3.5" />
                </a>
            </div>
        </div>

        {{-- stacked bars: violet subscription + amber commission --}}
        <div class="mt-4 flex h-28 items-end gap-1.5" dir="ltr">
            @php $maxRev = max($revenueChart->max('total'), 1); @endphp
            @foreach ($revenueChart as $bar)
                <div class="group flex min-w-0 flex-1 flex-col items-center gap-1.5">
                    <div class="flex w-full min-w-0 flex-1 flex-col justify-end" title="{{ $bar['label'] }} — جمع {{ \App\Support\Fmt::moneyShort($bar['total']) }} تومان (کمیسیون {{ \App\Support\Fmt::moneyShort($bar['commission']) }} / اشتراک {{ \App\Support\Fmt::moneyShort($bar['subscription']) }})">
                        @if ($bar['commission'] > 0)
                            <div class="w-full rounded-t-md bg-gradient-to-t from-amber-500 to-amber-300 transition-all group-hover:from-amber-600"
                                 style="height: {{ max(3, (int) round($bar['commission'] / $maxRev * 92)) }}px"></div>
                        @endif
                        @if ($bar['subscription'] > 0)
                            <div class="w-full {{ $bar['commission'] > 0 ? '' : 'rounded-t-md' }} bg-gradient-to-t from-violet-600 to-violet-400 transition-all group-hover:from-violet-700"
                                 style="height: {{ max(3, (int) round($bar['subscription'] / $maxRev * 92)) }}px"></div>
                        @endif
                        @if ($bar['total'] === 0)
                            <div class="w-full rounded-t-md bg-slate-100 dark:bg-slate-800" style="height: 3px"></div>
                        @endif
                    </div>
                    <span class="w-full min-w-0 truncate text-center text-[9px] font-bold {{ $bar['today'] ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400' }}">
                        <span class="sm:hidden">{{ $bar['short'] }}</span>
                        <span class="hidden sm:inline">{{ $bar['label'] }}</span>
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-5">

        {{-- ── 14-day story views chart ── min-w-0: a grid item must be
             allowed to shrink or the 14-label chart blows the whole
             track (and the page) wider than the viewport on phones. --}}
        <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-3 dark:border-slate-800 dark:bg-slate-900" data-testid="story-chart-card">
            <div class="flex items-center justify-between">
                <h3 class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                        <x-icon name="zap" class="size-4" />
                    </span>
                    بازدید استوری‌ها — ۱۴ روز اخیر
                </h3>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                    مجموع: {{ \App\Support\Fmt::fa($storyChart->sum('views')) }}
                </span>
            </div>

            <div class="mt-5 flex h-32 justify-between gap-1 sm:gap-1.5" dir="ltr">
                @php $maxViews = max($storyChart->max('views'), 1); @endphp
                @foreach ($storyChart as $bar)
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-1.5">
                        <div class="flex w-full min-w-0 flex-1 items-end">
                            <div class="w-full rounded-t-lg transition-all {{ $bar['today'] ? 'bg-gradient-to-t from-primary-600 to-primary-400 shadow-md shadow-primary-600/30' : 'bg-primary-200 dark:bg-primary-900' }}"
                                 style="height: {{ max(6, (int) round($bar['views'] / $maxViews * 100)) }}%"
                                 title="{{ $bar['label'] }}: {{ \App\Support\Fmt::fa($bar['views']) }} بازدید استوری"></div>
                        </div>
                        {{-- full weekday name on ≥sm, single letter on phones —
                             min-w-0 + truncate keep the column shrinkable --}}
                        <span class="w-full min-w-0 truncate text-center text-[9px] font-bold {{ $bar['today'] ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}">
                            <span class="sm:hidden">{{ $bar['short'] }}</span>
                            <span class="hidden sm:inline">{{ $bar['label'] }}</span>
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                <a href="{{ route('admin.stories') }}" wire:navigate class="rounded-2xl bg-slate-50 p-3 text-center transition hover:bg-slate-100 dark:bg-slate-800/60 dark:hover:bg-slate-800">
                    <div class="text-lg font-extrabold">{{ \App\Support\Fmt::fa($storyStats['running']) }}</div>
                    <div class="mt-0.5 text-[10px] text-slate-400">استوری در حال نمایش</div>
                </a>
                <div class="rounded-2xl bg-slate-50 p-3 text-center dark:bg-slate-800/60">
                    <div class="text-lg font-extrabold">{{ \App\Support\Fmt::fa($storyStats['views']) }}</div>
                    <div class="mt-0.5 text-[10px] text-slate-400">مجموع بازدید استوری‌ها</div>
                </div>
                <a href="{{ route('admin.slider') }}" wire:navigate class="rounded-2xl bg-slate-50 p-3 text-center transition hover:bg-slate-100 dark:bg-slate-800/60 dark:hover:bg-slate-800">
                    <div class="text-lg font-extrabold">{{ \App\Support\Fmt::fa($banners) }}</div>
                    <div class="mt-0.5 text-[10px] text-slate-400">اسلاید و بنر فعال</div>
                </a>
            </div>
        </div>

        {{-- ── 7-day orders chart ── --}}
        <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">
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

            <div class="mt-5 flex h-32 justify-between gap-1.5 sm:gap-2" dir="ltr">
                @php $maxOrders = max($chart->max('orders'), 1); @endphp
                @foreach ($chart as $bar)
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-1.5">
                        <div class="flex w-full min-w-0 flex-1 items-end">
                            <div class="w-full rounded-t-lg transition-all {{ $bar['today'] ? 'bg-gradient-to-t from-primary-600 to-primary-400 shadow-md shadow-primary-600/30' : 'bg-primary-200 dark:bg-primary-900' }}"
                                 style="height: {{ max(8, (int) round($bar['orders'] / $maxOrders * 100)) }}%"
                                 title="{{ $bar['label'] }}: {{ $bar['orders'] }} سفارش — {{ $bar['amount'] }} تومان موفق"></div>
                        </div>
                        <span class="w-full min-w-0 truncate text-center text-[9px] font-bold {{ $bar['today'] ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}">
                            <span class="sm:hidden">{{ $bar['short'] }}</span>
                            <span class="hidden sm:inline">{{ $bar['label'] }}</span>
                        </span>
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
        <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-3 dark:border-slate-800 dark:bg-slate-900">
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
                    {{-- min-w only from sm up — on phones the compacted
                         columns fit the card instead of overflowing it --}}
                    <table class="w-full min-w-0 text-right text-sm sm:min-w-[540px]">
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
                                    <td class="whitespace-nowrap py-2.5 font-extrabold">#{{ \App\Support\Fmt::fa($order->id) }}</td>
                                    <td class="py-2.5">
                                        <span class="block max-w-[86px] truncate text-xs font-semibold sm:max-w-none">{{ $order->customer?->full_name ?: 'بدون نام' }}</span>
                                        <span class="block text-[10px] text-slate-400" dir="ltr">{{ \App\Support\Fmt::toFa($order->customer?->phone ?? '—') }}</span>
                                    </td>
                                    <td class="whitespace-nowrap py-2.5 text-xs font-bold">
                                        {{-- «تومان» dropped on phones so every column fits the card --}}
                                        <span class="hidden sm:inline">{{ \App\Support\Fmt::money($order->total_amount) }}</span>
                                        <span class="sm:hidden">{{ \App\Support\Fmt::moneyShort($order->total_amount) }}</span>
                                    </td>
                                    <td class="whitespace-nowrap py-2.5 text-[11px] text-slate-400">
                                        {{-- time hidden on phones so all five columns fit the card --}}
                                        <span class="hidden sm:inline">{{ $order->dateFa() }}</span>
                                        <span class="sm:hidden">{{ \App\Support\JDate::date($order->created_at) }}</span>
                                    </td>
                                    <td class="whitespace-nowrap py-2.5">
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

    {{-- ── recent activities (فعالیت‌های اخیر) ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    <x-icon name="clock" class="size-4.5" />
                </span>
                <div>
                    <p class="text-sm font-extrabold">فعالیت‌های اخیر</p>
                    <p class="text-[10.5px] text-slate-400">هر اتفاقی که در سایت می‌افتد ثبت می‌شود</p>
                </div>
            </div>
            <a href="{{ route('admin.activity') }}" wire:navigate
               class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-500 transition hover:border-primary-300 hover:text-primary-700 dark:border-slate-700 dark:text-slate-300">
                مشاهده همه
                <x-icon name="arrow-left" class="size-3" />
            </a>
        </div>

        @forelse ($recentActivities as $act)
            <div class="flex items-center gap-3 border-b border-slate-50 px-5 py-2.5 last:border-0 dark:border-slate-800/60" wire:key="dash-act-{{ $act->id }}">
                <span class="size-1.5 shrink-0 rounded-full {{ $act->actor_type === 'admin' ? 'bg-primary-500' : ($act->actor_type === 'seller' ? 'bg-emerald-500' : ($act->actor_type === 'customer' ? 'bg-sky-500' : 'bg-slate-300')) }}"></span>
                <p class="min-w-0 flex-1 truncate text-[12px] font-semibold text-slate-600 dark:text-slate-300">{{ $act->title }}</p>
                <span class="shrink-0 rounded-md bg-slate-100 px-1.5 py-0.5 text-[9.5px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $act->actorLabel() }}</span>
                <span class="w-20 shrink-0 text-end text-[10px] text-slate-400">{{ \App\Support\JDate::ago($act->created_at) }}</span>
            </div>
        @empty
            <p class="px-5 py-8 text-center text-xs font-semibold text-slate-400">هنوز فعالیتی ثبت نشده است</p>
        @endforelse
    </div>
</div>
