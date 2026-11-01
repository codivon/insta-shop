<div class="ip-fade-up">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                    @if ($shop->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->logo_path) }}" alt="لوگوی فروشگاه" class="size-full object-cover">
                    @else
                        <x-icon name="store" class="size-5" />
                    @endif
                </span>
                <div>
                    <div class="max-w-[180px] truncate text-sm font-extrabold leading-5">{{ $shop->name }}</div>
                    <div class="flex items-center gap-1 text-[11px] text-slate-400">
                        @if ($shop->instagram_username)
                            <x-icon name="instagram" class="size-3" />
                            <span dir="ltr">{{ '@'.$shop->instagram_username }}</span>
                        @endif
                        @if (auth('seller')->user()->status === 'pending')
                            <span class="mx-0.5">·</span>
                            <span class="font-bold text-amber-500">در انتظار تأیید</span>
                        @elseif (auth('seller')->user()->status === 'approved')
                            <span class="mx-0.5">·</span>
                            <span class="font-bold text-emerald-500">فعال</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <x-theme-toggle class="size-10 text-slate-500" />
                <form method="POST" action="{{ route('seller.logout') }}">
                    @csrf
                    <button type="submit" class="grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-800" aria-label="خروج از حساب">
                        <x-icon name="log-out" class="size-5" />
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">
        {{-- ── pending approval banner ── --}}
        @if (auth('seller')->user()->status === 'pending')
            <div class="mb-4 flex items-start gap-2.5 rounded-2xl border border-amber-200 bg-amber-50 p-3.5 text-xs leading-6 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
                <x-icon name="clock" class="mt-0.5 size-4 shrink-0" />
                <span>
                    <b>فروشگاه شما در انتظار تأیید است.</b>
                    تا زمان تأیید توسط مدیر می‌توانید فروشگاه و محصولات را بسازید؛ اما نمایش عمومی پس از تأیید فعال می‌شود.
                </span>
            </div>
        @endif

        {{-- ── greeting hero with real stats ── --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500 p-5 text-white shadow-xl shadow-primary-600/25">
            <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
                <div class="absolute -top-8 left-6 size-40 rounded-full bg-white/30 blur-2xl"></div>
                <div class="absolute -bottom-12 right-10 size-48 rounded-full bg-white/20 blur-2xl"></div>
            </div>

            <p class="relative text-xs text-primary-100">سلام{{ auth('seller')->user()->full_name ? ' '.auth('seller')->user()->full_name : '' }} عزیز، امروز چیکار می‌کنیم؟ ☀️</p>
            <h2 class="relative mt-1.5 text-lg font-extrabold">
                @if ($stats['total'] === 0)
                    اولین محصولت را اضافه کن!
                @elseif ($stats['outOfStock'] > 0)
                    {{ \App\Support\Fmt::fa($stats['outOfStock']) }} محصولت ناموجود شده
                @else
                    فروشگاهت آماده فروشه!
                @endif
            </h2>

            {{-- mini stats (real data) --}}
            <div class="relative mt-4 grid grid-cols-3 gap-2">
                @foreach ([
                    ['icon' => 'package', 'value' => \App\Support\Fmt::fa($stats['total']),     'label' => 'محصول',   'href' => route('seller.shop')],
                    ['icon' => 'eye',     'value' => \App\Support\Fmt::fa($stats['published']), 'label' => 'منتشرشده', 'href' => route('seller.shop')],
                    ['icon' => 'tag',     'value' => \App\Support\Fmt::fa($stats['categories']), 'label' => 'دسته',    'href' => route('seller.categories')],
                ] as $st)
                    <a href="{{ $st['href'] }}" wire:navigate class="rounded-2xl bg-white/15 p-2.5 backdrop-blur transition hover:bg-white/25 active:scale-95">
                        <x-icon name="{{ $st['icon'] }}" class="size-4 opacity-80" />
                        <div class="mt-1 text-base font-extrabold">{{ $st['value'] }}</div>
                        <div class="text-[10px] text-primary-100">{{ $st['label'] }}</div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ── quick actions (real links) ── --}}
        <h3 class="mt-6 text-sm font-extrabold">دسترسی سریع</h3>
        <div class="mt-3 grid grid-cols-5 gap-2.5">
            <a href="{{ route('seller.products.create') }}" wire:navigate class="flex flex-col items-center gap-1.5 rounded-2xl p-2 transition active:scale-95">
                <span class="grid size-12 w-full place-items-center rounded-2xl bg-primary-600 text-white shadow-lg shadow-primary-600/30">
                    <x-icon name="plus" class="size-5" />
                </span>
                <span class="text-center text-[10px] font-semibold leading-3 text-slate-600 dark:text-slate-300">افزودن محصول</span>
            </a>
            <a href="{{ route('seller.shop') }}" wire:navigate class="flex flex-col items-center gap-1.5 rounded-2xl p-2 transition active:scale-95">
                <span class="grid size-12 w-full place-items-center rounded-2xl border border-slate-200 bg-white text-primary-600 dark:border-slate-800 dark:bg-slate-900 dark:text-primary-400">
                    <x-icon name="package" class="size-5" />
                </span>
                <span class="text-center text-[10px] font-semibold leading-3 text-slate-600 dark:text-slate-300">محصولات</span>
            </a>
            <a href="{{ route('seller.categories') }}" wire:navigate class="flex flex-col items-center gap-1.5 rounded-2xl p-2 transition active:scale-95">
                <span class="grid size-12 w-full place-items-center rounded-2xl border border-slate-200 bg-white text-primary-600 dark:border-slate-800 dark:bg-slate-900 dark:text-primary-400">
                    <x-icon name="tag" class="size-5" />
                </span>
                <span class="text-center text-[10px] font-semibold leading-3 text-slate-600 dark:text-slate-300">دسته‌بندی‌ها</span>
            </a>
            <a href="{{ route('seller.shop.attributes') }}" wire:navigate class="flex flex-col items-center gap-1.5 rounded-2xl p-2 transition active:scale-95">
                <span class="grid size-12 w-full place-items-center rounded-2xl border border-fuchsia-200 bg-gradient-to-br from-fuchsia-50 to-orange-50 text-fuchsia-600 dark:border-fuchsia-900 dark:from-fuchsia-950/40 dark:to-orange-950/30 dark:text-fuchsia-400">
                    <x-icon name="layers" class="size-5" />
                </span>
                <span class="text-center text-[10px] font-semibold leading-3 text-slate-600 dark:text-slate-300">ویژگی‌ها</span>
            </a>
            <a href="{{ route('seller.shop.edit') }}" wire:navigate class="flex flex-col items-center gap-1.5 rounded-2xl p-2 transition active:scale-95">
                <span class="grid size-12 w-full place-items-center rounded-2xl border border-slate-200 bg-white text-primary-600 dark:border-slate-800 dark:bg-slate-900 dark:text-primary-400">
                    <x-icon name="store" class="size-5" />
                </span>
                <span class="text-center text-[10px] font-semibold leading-3 text-slate-600 dark:text-slate-300">ویرایش فروشگاه</span>
            </a>
        </div>

        {{-- ── نمودار فروش با فیلتر روزانه/ماهانه/سالانه + بازه تاریخی شمسی ── --}}
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-2">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="chart" class="size-3.5" /></span>
                    نمودار فروش
                </h3>
                <div class="flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800" role="group" aria-label="بازه نمودار فروش">
                    @foreach ([['key' => 'daily', 'label' => 'روزانه'], ['key' => 'monthly', 'label' => 'ماهانه'], ['key' => 'yearly', 'label' => 'سالانه']] as $mode)
                        <button type="button" wire:click="setChartMode('{{ $mode['key'] }}')" wire:loading.attr="disabled" wire:target="setChartMode"
                                class="rounded-lg px-2.5 py-1 text-[10px] font-bold transition {{ $chartMode === $mode['key']
                                    ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400'
                                    : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                            {{ $mode['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- summary --}}
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div class="rounded-2xl bg-gradient-to-l from-primary-600/10 to-primary-500/5 p-3 dark:from-primary-600/20 dark:to-primary-500/10">
                    <p class="text-[9.5px] font-bold text-slate-400">فروش بازه انتخابی</p>
                    <p class="mt-0.5 text-sm font-black text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::money($chartTotal) }} <span class="text-[9px] font-bold text-slate-400">تومان</span></p>
                </div>
                <div class="rounded-2xl bg-emerald-50 p-3 dark:bg-emerald-950/30">
                    <p class="text-[9.5px] font-bold text-slate-400">سفارش‌های پرداخت‌شده</p>
                    <p class="mt-0.5 text-sm font-black text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::fa($chartOrders) }} <span class="text-[9px] font-bold text-slate-400">سفارش</span></p>
                </div>
            </div>

            {{-- jalali range pickers --}}
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div>
                    <span class="mb-1 block text-[9.5px] font-bold text-slate-400">از تاریخ</span>
                    <x-jdate-picker model="chartFrom" placeholder="از تاریخ" />
                </div>
                <div>
                    <span class="mb-1 block text-[9.5px] font-bold text-slate-400">تا تاریخ</span>
                    <x-jdate-picker model="chartTo" placeholder="تا تاریخ" />
                </div>
            </div>

            {{-- bars --}}
            <div class="scroll-slim mt-4 overflow-x-auto pb-1">
                <div class="flex h-28 min-w-full items-end gap-1.5" dir="ltr">
                    @forelse ($chart as $bar)
                        <div class="flex min-w-7 flex-1 flex-col items-center gap-1">
                            <div class="flex w-full flex-1 items-end">
                                <div class="w-full rounded-t-lg transition-all duration-300 {{ $bar['today'] ? 'bg-gradient-to-t from-primary-600 to-primary-400 shadow-md shadow-primary-600/30' : 'bg-primary-200/80 dark:bg-primary-900' }}"
                                     style="height: {{ max(6, (int) round($bar['value'] / max($chartMax, 1) * 100)) }}%"
                                     title="{{ $bar['label'] }}: {{ \App\Support\Fmt::money($bar['value']) }} تومان"></div>
                            </div>
                            <span class="whitespace-nowrap text-[8.5px] font-bold {{ $bar['today'] ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400' }}">{{ $bar['label'] }}</span>
                        </div>
                    @empty
                        <div class="grid h-full w-full place-items-center">
                            <p class="text-[10px] font-bold text-slate-400">در این بازه فروشی ثبت نشده است.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── recent products ── --}}
        <div class="mt-6 flex items-center justify-between">
            <h3 class="text-sm font-extrabold">آخرین محصولات</h3>
            @if ($stats['total'] > 0)
                <a href="{{ route('seller.shop') }}" wire:navigate class="flex items-center gap-1 text-[11px] font-bold text-primary-600 hover:text-primary-700 dark:text-primary-400">
                    مشاهده همه
                    <x-icon name="chevron-left" class="size-3.5" />
                </a>
            @endif
        </div>

        @if ($recentProducts->isEmpty())
            <div class="mt-3 flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-300 py-10 text-center dark:border-slate-700">
                <span class="grid size-14 place-items-center rounded-2xl bg-primary-50 text-primary-400 dark:bg-primary-950">
                    <x-icon name="package" class="size-7" />
                </span>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-300">هنوز محصولی ثبت نکرده‌اید</p>
                <a href="{{ route('seller.products.create') }}" wire:navigate
                   class="flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-95">
                    <x-icon name="plus" class="size-4" />
                    افزودن اولین محصول
                </a>
            </div>
        @else
            <div class="mt-3 grid grid-cols-2 gap-3">
                @foreach ($recentProducts as $product)
                    <a href="{{ route('seller.products.edit', $product) }}" wire:navigate
                       class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900" wire:key="rp-{{ $product->id }}">
                        <div class="relative aspect-[4/3] w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                            @if ($product->mainImage)
                                <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy" class="size-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <span class="grid size-full place-items-center text-slate-300 dark:text-slate-600"><x-icon name="image" class="size-8" /></span>
                            @endif
                            @if ($product->status !== 'published')
                                <span class="absolute right-2 top-2 rounded-full bg-slate-900/70 px-2 py-0.5 text-[9px] font-bold text-white backdrop-blur">پیش‌نویس</span>
                            @elseif ($product->stock === 0)
                                <span class="absolute right-2 top-2 rounded-full bg-rose-500/90 px-2 py-0.5 text-[9px] font-bold text-white backdrop-blur">ناموجود</span>
                            @endif
                        </div>
                        <div class="p-2.5">
                            <div class="truncate text-[11px] font-extrabold">{{ $product->name }}</div>
                            <div class="mt-1 text-[11px] font-black text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::fa($product->price) }} <span class="text-[9px] font-semibold text-slate-400">تومان</span></div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- ── orders shortcut (real link — Phase 5) ── --}}
        <a href="{{ route('seller.orders') }}" wire:navigate
           class="mt-6 flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white p-3.5 text-xs leading-6 text-slate-500 shadow-sm transition hover:border-primary-300 active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-primary-800">
            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                <x-icon name="bag" class="size-4" />
            </span>
            <span class="min-w-0 flex-1">
                <b class="text-slate-700 dark:text-slate-200">سفارش‌های فروشگاه</b> — سفارش‌های مشتریان را پیگیری کنید.
            </span>
            <x-icon name="chevron-left" class="size-4 shrink-0 text-slate-400" />
        </a>
    </div>
</div>
