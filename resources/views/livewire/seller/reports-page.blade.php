<div class="ip-fade-up">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                    <x-icon name="chart" class="size-5" />
                </span>
                <div>
                    <div class="text-sm font-extrabold leading-5">گزارش‌ها</div>
                    <div class="text-[11px] text-slate-400">فروش و بازدید فروشگاه</div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <x-seller-notif-bell />
                <a href="{{ route('seller.profile') }}" wire:navigate
                   class="grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بازگشت">
                    <x-icon name="arrow-right" class="size-5" />
                </a>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">
        {{-- ───────── نمودار فروش ───────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900" data-testid="sales-chart-card">
            <div class="flex items-center justify-between gap-2">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"><x-icon name="coins" class="size-3.5" /></span>
                    نمودار فروش
                </h3>
                <div class="flex items-center gap-1.5">
                    {{-- CSV download for the sales report --}}
                    <button type="button" wire:click="exportSalesCsv"
                            class="flex items-center gap-1 rounded-xl bg-slate-100 px-2.5 py-1.5 text-[10px] font-extrabold text-slate-600 transition hover:bg-slate-200 active:scale-95 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                            aria-label="دانلود گزارش فروش">
                        <x-icon name="download" class="size-3.5" />
                        دانلود CSV
                        <span class="hidden" wire:loading wire:target="exportSalesCsv"><x-icon name="rotate" class="size-3.5 animate-spin" /></span>
                    </button>
                </div>
            </div>

            {{-- granularity tabs — optimistic flip via data-ip-pick so the
                 tapped tab reacts INSTANTLY; only the chart itself may load --}}
            <div class="mt-3 flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800" role="group" aria-label="بازه نمودار فروش">
                @foreach ([['key' => 'daily', 'label' => 'روزانه'], ['key' => 'weekly', 'label' => 'هفتگی'], ['key' => 'monthly', 'label' => 'ماهانه'], ['key' => 'yearly', 'label' => 'سالانه']] as $mode)
                    <button type="button" wire:click="setSalesMode('{{ $mode['key'] }}')"
                            data-ip-pick data-ip-group="sales-mode"
                            data-ip-pick-class="bg-white text-emerald-600 shadow-sm dark:bg-slate-900 dark:text-emerald-400"
                            class="flex-1 rounded-lg px-2 py-1 text-[10px] font-bold transition {{ $salesMode === $mode['key']
                                ? 'bg-white text-emerald-600 shadow-sm dark:bg-slate-900 dark:text-emerald-400'
                                : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                        {{ $mode['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- summary --}}
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div class="rounded-2xl bg-gradient-to-l from-emerald-600/10 to-emerald-500/5 p-3 dark:from-emerald-600/20 dark:to-emerald-500/10">
                    <p class="text-[9.5px] font-bold text-slate-400">فروش بازه انتخابی</p>
                    <p class="mt-0.5 text-sm font-black text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::fa($sales['total']) }} <span class="text-[9px] font-bold text-slate-400">تومان</span></p>
                </div>
                <div class="rounded-2xl bg-primary-50 p-3 dark:bg-primary-950/30">
                    <p class="text-[9.5px] font-bold text-slate-400">سفارش‌های پرداخت‌شده</p>
                    <p class="mt-0.5 text-sm font-black text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::fa($sales['orders']) }} <span class="text-[9px] font-bold text-slate-400">سفارش</span></p>
                </div>
            </div>

            {{-- jalali range pickers --}}
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div>
                    <span class="mb-1 block text-[9.5px] font-bold text-slate-400">از تاریخ</span>
                    <x-jdate-picker model="salesFrom" placeholder="از تاریخ" />
                </div>
                <div>
                    <span class="mb-1 block text-[9.5px] font-bold text-slate-400">تا تاریخ</span>
                    <x-jdate-picker model="salesTo" placeholder="تا تاریخ" />
                </div>
            </div>

            {{-- bars — only THIS chart area shows activity while it reloads --}}
            <div class="relative scroll-slim mt-4 overflow-x-auto pb-1">
                <div wire:loading wire:target="setSalesMode, salesFrom, salesTo"
                     class="absolute inset-0 z-10 grid place-items-center rounded-xl bg-white/60 backdrop-blur-[1px] dark:bg-slate-900/60">
                    <span class="size-5 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"></span>
                </div>
                <div class="flex h-28 min-w-full items-end gap-1.5" dir="ltr">
                    @forelse ($sales['series'] as $bar)
                        <div class="flex min-w-7 flex-1 flex-col items-center gap-1" wire:key="sales-{{ $loop->index }}">
                            <div class="flex w-full flex-1 items-end">
                                <div class="w-full rounded-t-lg transition-all duration-300 {{ $bar['today'] ? 'bg-gradient-to-t from-emerald-600 to-emerald-400 shadow-md shadow-emerald-600/30' : 'bg-emerald-200/80 dark:bg-emerald-900' }}"
                                     style="height: {{ max(6, (int) round($bar['value'] / max($sales['max'], 1) * 100)) }}%"
                                     title="{{ $bar['label'] }}: {{ \App\Support\Fmt::money($bar['value']) }} تومان"></div>
                            </div>
                            <span class="whitespace-nowrap text-[8.5px] font-bold {{ $bar['today'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">{{ $bar['label'] }}</span>
                        </div>
                    @empty
                        <div class="grid h-full w-full place-items-center">
                            <p class="text-[10px] font-bold text-slate-400">در این بازه فروشی ثبت نشده است.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ───────── نمودار بازدید ───────── --}}
        <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900" data-testid="views-chart-card">
            <div class="flex items-center justify-between gap-2">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="eye" class="size-3.5" /></span>
                    نمودار بازدید محصولات
                </h3>
                <div class="flex items-center gap-1.5">
                    <button type="button" wire:click="exportViewsCsv"
                            class="flex items-center gap-1 rounded-xl bg-slate-100 px-2.5 py-1.5 text-[10px] font-extrabold text-slate-600 transition hover:bg-slate-200 active:scale-95 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                            aria-label="دانلود گزارش بازدید">
                        <x-icon name="download" class="size-3.5" />
                        دانلود CSV
                        <span class="hidden" wire:loading wire:target="exportViewsCsv"><x-icon name="rotate" class="size-3.5 animate-spin" /></span>
                    </button>
                </div>
            </div>

            <div class="mt-3 flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800" role="group" aria-label="بازه نمودار بازدید">
                @foreach ([['key' => 'daily', 'label' => 'روزانه'], ['key' => 'weekly', 'label' => 'هفتگی'], ['key' => 'monthly', 'label' => 'ماهانه'], ['key' => 'yearly', 'label' => 'سالانه']] as $mode)
                    <button type="button" wire:click="setViewsMode('{{ $mode['key'] }}')"
                            data-ip-pick data-ip-group="views-mode"
                            data-ip-pick-class="bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400"
                            class="flex-1 rounded-lg px-2 py-1 text-[10px] font-bold transition {{ $viewsMode === $mode['key']
                                ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400'
                                : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                        {{ $mode['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- summary --}}
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div class="rounded-2xl bg-gradient-to-l from-primary-600/10 to-primary-500/5 p-3 dark:from-primary-600/20 dark:to-primary-500/10">
                    <p class="text-[9.5px] font-bold text-slate-400">بازدید بازه انتخابی</p>
                    <p class="mt-0.5 text-sm font-black text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::fa($views['total']) }} <span class="text-[9px] font-bold text-slate-400">بازدید</span></p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/60">
                    <p class="text-[9.5px] font-bold text-slate-400">میانگین روزانه</p>
                    <p class="mt-0.5 text-sm font-black">{{ \App\Support\Fmt::fa((int) round($views['total'] / max($viewsDays, 1))) }} <span class="text-[9px] font-bold text-slate-400">بازدید در روز</span></p>
                </div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2">
                <div>
                    <span class="mb-1 block text-[9.5px] font-bold text-slate-400">از تاریخ</span>
                    <x-jdate-picker model="viewsFrom" placeholder="از تاریخ" />
                </div>
                <div>
                    <span class="mb-1 block text-[9.5px] font-bold text-slate-400">تا تاریخ</span>
                    <x-jdate-picker model="viewsTo" placeholder="تا تاریخ" />
                </div>
            </div>

            <div class="relative scroll-slim mt-4 overflow-x-auto pb-1">
                <div wire:loading wire:target="setViewsMode, viewsFrom, viewsTo"
                     class="absolute inset-0 z-10 grid place-items-center rounded-xl bg-white/60 backdrop-blur-[1px] dark:bg-slate-900/60">
                    <span class="size-5 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"></span>
                </div>
                <div class="flex h-24 min-w-full items-end gap-1.5" dir="ltr">
                    @forelse ($views['series'] as $bar)
                        <div class="flex min-w-7 flex-1 flex-col items-center gap-1" wire:key="views-{{ $loop->index }}">
                            <div class="flex w-full flex-1 items-end">
                                <div class="w-full rounded-t-lg transition-all duration-300 {{ $bar['today'] ? 'bg-gradient-to-t from-primary-600 to-primary-400 shadow-md shadow-primary-600/30' : 'bg-primary-200/80 dark:bg-primary-900' }}"
                                     style="height: {{ max(6, (int) round($bar['value'] / max($views['max'], 1) * 100)) }}%"
                                     title="{{ $bar['label'] }}: {{ \App\Support\Fmt::fa($bar['value']) }} بازدید"></div>
                            </div>
                            <span class="whitespace-nowrap text-[8.5px] font-bold {{ $bar['today'] ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}">{{ $bar['label'] }}</span>
                        </div>
                    @empty
                        <div class="grid h-full w-full place-items-center">
                            <p class="text-[10px] font-bold text-slate-400">هنوز بازدیدی ثبت نشده است.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ───────── آمار کلی بازدید ───────── --}}
        <div class="mt-5 flex items-center gap-2">
            <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="eye" class="size-3.5" /></span>
            <h3 class="text-sm font-extrabold">بازدید محصولات</h3>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[9.5px] font-bold text-slate-400">مجموع بازدید کل</p>
                <p class="mt-0.5 flex items-baseline gap-1 text-lg font-black text-primary-600 dark:text-primary-400">
                    {{ \App\Support\Fmt::fa($viewStats['total']) }}
                    <span class="text-[9px] font-bold text-slate-400">بازدید</span>
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[9.5px] font-bold text-slate-400">بازدید امروز</p>
                <p class="mt-0.5 flex items-baseline gap-1 text-lg font-black text-emerald-600 dark:text-emerald-400">
                    {{ \App\Support\Fmt::fa($viewStats['today']) }}
                    <span class="text-[9px] font-bold text-slate-400">بازدید</span>
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[9.5px] font-bold text-slate-400">۷ روز گذشته</p>
                <p class="mt-0.5 flex items-baseline gap-1 text-sm font-black">
                    {{ \App\Support\Fmt::fa($viewStats['d7']) }}
                    <span class="text-[9px] font-bold text-slate-400">بازدید</span>
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[9.5px] font-bold text-slate-400">۳۰ روز گذشته</p>
                <p class="mt-0.5 flex items-baseline gap-1 text-sm font-black">
                    {{ \App\Support\Fmt::fa($viewStats['d30']) }}
                    <span class="text-[9px] font-bold text-slate-400">بازدید</span>
                </p>
            </div>
        </div>

        {{-- ───────── top viewed products ───────── --}}
        @if ($topProducts->isNotEmpty())
            <div class="mt-5 flex items-center justify-between">
                <h3 class="flex items-center gap-2 text-sm font-extrabold">
                    <x-icon name="star" class="size-4 text-amber-500" />
                    پربازدیدترین محصولات
                </h3>
                <a href="{{ route('seller.reports.views') }}" wire:navigate class="flex items-center gap-1 text-[11px] font-bold text-primary-600 hover:text-primary-700 dark:text-primary-400">
                    گزارش کامل
                    <x-icon name="chevron-left" class="size-3.5" />
                </a>
            </div>

            <div class="mt-3 space-y-2">
                @foreach ($topProducts as $product)
                    <div wire:key="top-{{ $product->id }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-2.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <span class="w-5 shrink-0 text-center text-xs font-black {{ $loop->index < 3 ? 'text-amber-500' : 'text-slate-300 dark:text-slate-600' }}">{{ \App\Support\Fmt::fa($loop->iteration) }}</span>

                        @if ($product->mainImage)
                            <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy"
                                 class="size-11 shrink-0 rounded-xl border border-slate-200 object-cover dark:border-slate-700">
                        @else
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                                <x-icon name="image" class="size-5" />
                            </span>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="truncate text-xs font-extrabold">{{ $product->name }}</div>
                            <div class="mt-0.5 text-[10px] font-semibold text-slate-400">
                                {{ $product->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }} · {{ \App\Support\Fmt::fa($product->price) }} تومان
                            </div>
                        </div>

                        <span class="flex shrink-0 items-center gap-1 rounded-xl bg-primary-50 px-2.5 py-1.5 text-[10px] font-black text-primary-600 dark:bg-primary-950/50 dark:text-primary-300">
                            <x-icon name="eye" class="size-3.5" />
                            {{ \App\Support\Fmt::fa($product->views) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ───────── orders (سفارش‌ها) ───────── --}}
        <div class="mt-6 flex items-center gap-2">
            <span class="grid size-7 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"><x-icon name="bag" class="size-3.5" /></span>
            <h3 class="text-sm font-extrabold">سفارش‌ها</h3>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[9.5px] font-bold text-slate-400">فروش کل (پرداخت‌شده)</p>
                <p class="mt-0.5 text-sm font-black text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::money($orderStats['revenue']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[9.5px] font-bold text-slate-400">مشتریان خریدار</p>
                <p class="mt-0.5 flex items-baseline gap-1 text-sm font-black">
                    {{ \App\Support\Fmt::fa($orderStats['buyers']) }}
                    <span class="text-[9px] font-bold text-slate-400">نفر</span>
                </p>
            </div>
        </div>

        <div class="mt-2 grid grid-cols-4 gap-2">
            @foreach ([
                'paid'     => ['پرداخت‌شده', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400', 'circle-check'],
                'pending'  => ['در انتظار پرداخت', 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400', 'clock'],
                'failed'   => ['ناموفق', 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400', 'alert'],
                'canceled' => ['لغو شده', 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400', 'circle-slash'],
            ] as $status => [$label, $classes, $icon])
                <a href="{{ route('seller.orders') }}" wire:navigate
                   class="flex flex-col items-center gap-1 rounded-2xl border border-slate-200 bg-white p-2.5 text-center shadow-sm transition hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-600">
                    <span class="grid size-8 place-items-center rounded-xl {{ $classes }}">
                        <x-icon name="{{ $icon }}" class="size-4" />
                    </span>
                    <span class="text-xs font-black">{{ \App\Support\Fmt::fa($orderStats['statuses'][$status] ?? 0) }}</span>
                    <span class="text-[8.5px] font-semibold text-slate-400">{{ $label }}</span>
                </a>
            @endforeach
        </div>

        @if ($orderStats['lastPaidAt'])
            <p class="mt-3 flex items-center justify-center gap-1.5 text-[10px] font-semibold text-slate-400">
                <x-icon name="clock" class="size-3" />
                آخرین فروش: {{ $orderStats['lastPaidAt'] }}
            </p>
        @endif

        {{-- hint --}}
        <div class="mt-5 flex items-start gap-2.5 rounded-2xl border border-primary-200 bg-primary-50/70 p-3.5 text-[11px] leading-6 text-primary-800 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
            <x-icon name="info" class="mt-0.5 size-4 shrink-0" />
            <span>
                بازه هر نمودار را جداگانه انتخاب کنید؛ «دانلود CSV» همان بازه را به‌صورت فایل اکسل‌پذیر ذخیره می‌کند. برای جزئیات بازدید هر محصول، «گزارش کامل» را باز کنید.
            </span>
        </div>
    </div>
</div>
