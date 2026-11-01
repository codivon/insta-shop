<div class="ip-fade-up">
    @php
        $handle = $shop->seller?->instagram_username ?: $shop->instagram_username;
        $activeCat = $categories->firstWhere('id', $catId);
        $filtersActive = $catId > 0 || $q !== '' || $sort !== 'newest';
        $sorts = [
            'newest'    => 'جدیدترین',
            'cheap'     => 'ارزان‌ترین',
            'expensive' => 'گران‌ترین',
            'popular'   => 'پربازدیدترین',
            'discount'  => 'تخفیف‌دار',
        ];
    @endphp

    {{-- ═══════════════ Sticky shop header ═══════════════ --}}
    <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/85 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/85">
        <div class="mx-auto flex h-14 max-w-6xl items-center justify-between gap-3 px-4 sm:h-16 sm:px-6">
            <a href="{{ url()->current() }}" class="flex min-w-0 items-center gap-2.5">
                @if ($shop->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->logo_path) }}" alt="لوگوی {{ $shop->name }}"
                         class="size-9 shrink-0 rounded-xl object-cover shadow-sm">
                @else
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                        <x-icon name="store" class="size-4.5" />
                    </span>
                @endif
                <span class="truncate text-sm font-extrabold sm:text-base">{{ $shop->name }}</span>
            </a>

            <div class="flex shrink-0 items-center gap-1.5">
                @if ($handle)
                    <a href="https://instagram.com/{{ $handle }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-gradient-to-l from-[#f9ce34] via-[#ee2a7b] to-[#6228d7] px-3 text-[11px] font-extrabold text-white shadow-md shadow-fuchsia-500/25 transition hover:opacity-90 active:scale-95">
                        <x-icon name="instagram" class="size-4" />
                        <span class="hidden sm:inline">اینستاگرام</span>
                    </a>
                @endif
                <x-theme-toggle class="size-9 text-slate-500" />
            </div>
        </div>
    </header>

    {{-- ═══════════════ HERO ═══════════════ --}}
    <section class="relative">
        <div class="relative h-40 overflow-hidden sm:h-56 lg:h-64">
            @if ($shop->banner_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->banner_path) }}" alt="بنر {{ $shop->name }}"
                     class="size-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent" aria-hidden="true"></div>
            @else
                <div class="relative size-full bg-gradient-to-bl from-primary-700 via-primary-600 to-primary-500">
                    <div class="pointer-events-none absolute inset-0 opacity-30" aria-hidden="true">
                        <div class="absolute -top-10 left-1/4 size-48 rounded-full bg-white/25 blur-3xl"></div>
                        <div class="absolute -bottom-14 right-1/5 size-56 rounded-full bg-fuchsia-400/25 blur-3xl"></div>
                        <div class="absolute top-6 right-1/2 size-32 rounded-full bg-amber-300/25 blur-2xl"></div>
                    </div>
                </div>
            @endif
        </div>

        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            {{-- relative z-10 → the logo block paints ABOVE the banner/gradient hero
                (the banner wrapper is `relative` and would otherwise cover it) --}}
            <div class="relative z-10 -mt-12 flex flex-col items-center text-center sm:-mt-14">
                {{-- logo --}}
                <div class="grid size-24 shrink-0 place-items-center overflow-hidden rounded-3xl border-4 border-white bg-white shadow-xl shadow-slate-900/10 sm:size-28 dark:border-slate-950 dark:bg-slate-900">
                    @if ($shop->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->logo_path) }}" alt="لوگوی {{ $shop->name }}" class="size-full object-cover">
                    @else
                        <span class="text-3xl font-black text-primary-600 dark:text-primary-400">{{ mb_substr($shop->name, 0, 1) }}</span>
                    @endif
                </div>

                {{-- name + badge --}}
                <h1 class="mt-3 flex items-center gap-1.5 text-lg font-black sm:text-2xl">
                    {{ $shop->name }}
                    <span class="grid size-5 place-items-center rounded-full bg-emerald-500 text-white shadow-sm sm:size-6" title="فروشگاه فعال و تأییدشده">
                        <x-icon name="check" class="size-3 sm:size-3.5" />
                    </span>
                </h1>

                {{-- handle + status --}}
                <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                    @if ($handle)
                        <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-l from-fuchsia-500/10 to-amber-500/10 px-2.5 py-1 text-[10px] font-extrabold text-fuchsia-600 sm:text-[11px] dark:text-fuchsia-400" dir="ltr">
                            <x-icon name="instagram" class="size-3" />
                            {{ '@'.$handle }}
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-600 sm:text-[11px] dark:text-emerald-400">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                        </span>
                        فروشگاه فعال و تأییدشده
                    </span>
                </div>

                @if ($shop->about)
                    <p class="mt-3 max-w-2xl text-xs leading-6 text-slate-500 sm:text-sm sm:leading-7 dark:text-slate-400">{{ $shop->about }}</p>
                @endif

                {{-- CTA row --}}
                <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                    @if ($handle)
                        <a href="https://instagram.com/{{ $handle }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 rounded-2xl bg-gradient-to-l from-[#f9ce34] via-[#ee2a7b] to-[#6228d7] px-4 py-2.5 text-[11px] font-extrabold text-white shadow-lg shadow-fuchsia-500/30 transition hover:opacity-90 active:scale-95 sm:text-xs">
                            <x-icon name="instagram" class="size-4" />
                            دنبال کردن در اینستاگرام
                        </a>
                    @endif
                    <button type="button" x-data="{ copied: false }"
                            @click="navigator.clipboard && navigator.clipboard.writeText('{{ $storefrontUrl }}').then(() => { copied = true; setTimeout(() => copied = false, 1800) })"
                            class="inline-flex items-center gap-1.5 rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-[11px] font-extrabold text-slate-600 shadow-sm transition hover:border-primary-300 hover:text-primary-600 active:scale-95 sm:text-xs dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                        <span x-show="!copied" class="flex items-center gap-1.5"><x-icon name="copy" class="size-3.5" /> کپی آدرس فروشگاه</span>
                        <span x-show="copied" x-cloak class="flex items-center gap-1.5 text-emerald-500"><x-icon name="circle-check" class="size-3.5" /> کپی شد</span>
                    </button>
                </div>

                {{-- stats --}}
                <div class="mt-5 grid w-full max-w-xl grid-cols-3 divide-x divide-x-reverse divide-slate-200 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-col items-center gap-0.5 p-3 sm:p-4">
                        <span class="flex items-center gap-1 text-base font-black text-primary-600 sm:text-xl dark:text-primary-400">
                            <x-icon name="package" class="size-4 text-slate-300 dark:text-slate-600" />
                            {{ \App\Support\Fmt::toFa($stats['products']) }}
                        </span>
                        <span class="text-[9px] font-bold text-slate-400 sm:text-[10px]">محصول</span>
                    </div>
                    <div class="flex flex-col items-center gap-0.5 p-3 sm:p-4">
                        <span class="flex items-center gap-1 text-base font-black text-primary-600 sm:text-xl dark:text-primary-400">
                            <x-icon name="eye" class="size-4 text-slate-300 dark:text-slate-600" />
                            {{ \App\Support\Fmt::toFa($stats['views']) }}
                        </span>
                        <span class="text-[9px] font-bold text-slate-400 sm:text-[10px]">بازدید</span>
                    </div>
                    <div class="flex flex-col items-center gap-0.5 p-3 sm:p-4">
                        <span class="flex items-center gap-1 text-base font-black text-primary-600 sm:text-xl dark:text-primary-400">
                            <x-icon name="tag" class="size-4 text-slate-300 dark:text-slate-600" />
                            {{ \App\Support\Fmt::toFa($stats['categories']) }}
                        </span>
                        <span class="text-[9px] font-bold text-slate-400 sm:text-[10px]">دسته‌بندی</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════ CATEGORY CHIPS ═══════════════ --}}
    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pt-7 sm:px-6" aria-label="دسته‌بندی‌های فروشگاه">
            <div class="flex items-center gap-2 pb-3">
                <span class="grid size-7 place-items-center rounded-lg bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                    <x-icon name="tag" class="size-3.5" />
                </span>
                <h2 class="text-sm font-extrabold sm:text-base">خرید بر اساس دسته‌بندی</h2>
            </div>

            <div class="scrollbar-hide -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
                <button type="button" wire:key="chip-all"
                        wire:click="$set('catId', 0)"
                        @click="document.getElementById('sf-products')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        class="flex shrink-0 items-center gap-1.5 rounded-2xl border px-4 py-2.5 text-[11px] font-extrabold transition active:scale-95 {{ $catId === 0 ? 'border-primary-600 bg-primary-600 text-white shadow-lg shadow-primary-600/30' : 'border-slate-200 bg-white text-slate-600 hover:border-primary-300 hover:text-primary-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300' }}">
                    <x-icon name="layers" class="size-3.5" />
                    همه محصولات
                </button>
                @foreach ($categories as $category)
                    <button type="button" wire:key="chip-{{ $category->id }}"
                            wire:click="$set('catId', {{ $category->id }})"
                            @click="document.getElementById('sf-products')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                            class="flex shrink-0 items-center gap-1.5 rounded-2xl border px-4 py-2.5 text-[11px] font-extrabold transition active:scale-95 {{ $catId === $category->id ? 'border-primary-600 bg-primary-600 text-white shadow-lg shadow-primary-600/30' : 'border-slate-200 bg-white text-slate-600 hover:border-primary-300 hover:text-primary-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300' }}">
                        {{ $category->name }}
                        <span class="rounded-full px-1.5 py-0.5 text-[8.5px] font-black {{ $catId === $category->id ? 'bg-white/25' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">{{ \App\Support\Fmt::toFa($category->products_count) }}</span>
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ═══════════════ فروش ویژه — SPECIAL SALE (dedicated, with live countdown) ═══════════════ --}}
    @if ($special->isNotEmpty() && ! $filtersActive)
        <section class="mx-auto max-w-6xl px-4 pt-7 sm:px-6" aria-label="فروش ویژه">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-bl from-rose-600 via-rose-500 to-orange-500 p-4 shadow-xl shadow-rose-500/25 sm:p-5">
                {{-- decorative glow --}}
                <div class="pointer-events-none absolute inset-0 opacity-25" aria-hidden="true">
                    <div class="absolute -top-10 left-10 size-36 rounded-full bg-white/30 blur-3xl"></div>
                    <div class="absolute -bottom-14 right-1/4 size-44 rounded-full bg-amber-300/30 blur-3xl"></div>
                </div>

                <div class="relative flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="grid size-10 place-items-center rounded-2xl bg-white/20 backdrop-blur">
                            <x-icon name="flame" class="size-5" />
                        </span>
                        <div>
                            <h2 class="flex items-center gap-1.5 text-base font-black text-white sm:text-lg">
                                فروش ویژه
                                <span class="relative flex size-2">
                                    <span class="absolute inline-flex size-full animate-ping rounded-full bg-white opacity-70"></span>
                                    <span class="relative inline-flex size-2 rounded-full bg-white"></span>
                                </span>
                            </h2>
                            <p class="text-[10.5px] font-bold text-rose-50/90 sm:text-[11px]">فرصت محدود — تا پایان زمان باقی‌مانده</p>
                        </div>
                    </div>

                    {{-- live countdown to the soonest deadline --}}
                    @if ($specialDeadlineMs)
                        <div x-data="sfCountdown({{ $specialDeadlineMs }})" class="flex items-center gap-1.5" dir="ltr" aria-label="زمان باقی‌مانده فروش ویژه">
                            <span class="grid min-w-9 place-items-center rounded-xl bg-white/20 px-1.5 py-1 text-center backdrop-blur">
                                <b class="font-mono text-sm font-black leading-4 text-white" x-text="pad(d)" :aria-label="d + ' روز'">۰۰</b>
                                <i class="text-[7.5px] font-bold not-italic text-rose-50/80">روز</i>
                            </span>
                            <span class="text-white/60">:</span>
                            <span class="grid min-w-9 place-items-center rounded-xl bg-white/20 px-1.5 py-1 text-center backdrop-blur">
                                <b class="font-mono text-sm font-black leading-4 text-white" x-text="pad(h)">۰۰</b>
                                <i class="text-[7.5px] font-bold not-italic text-rose-50/80">ساعت</i>
                            </span>
                            <span class="text-white/60">:</span>
                            <span class="grid min-w-9 place-items-center rounded-xl bg-white/20 px-1.5 py-1 text-center backdrop-blur">
                                <b class="font-mono text-sm font-black leading-4 text-white" x-text="pad(m)">۰۰</b>
                                <i class="text-[7.5px] font-bold not-italic text-rose-50/80">دقیقه</i>
                            </span>
                            <span class="text-white/60">:</span>
                            <span class="grid min-w-9 place-items-center rounded-xl bg-white px-1.5 py-1 text-center shadow-md">
                                <b class="font-mono text-sm font-black leading-4 text-rose-600" x-text="pad(s)">۰۰</b>
                                <i class="text-[7.5px] font-bold not-italic text-rose-500/80">ثانیه</i>
                            </span>
                        </div>
                    @endif
                </div>

                {{-- special products slider --}}
                <div class="scrollbar-hide relative -mx-4 mt-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-1 sm:-mx-5 sm:px-5" data-slider-track>
                    @foreach ($special as $product)
                        @include('livewire.storefront.partials.product-card', ['product' => $product, 'isFav' => in_array($product->id, $favoriteIds), 'prefix' => 'special', 'variant' => 'special'])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ═══════════════ PRODUCT SLIDERS (only without active filters) ═══════════════ --}}
    @unless ($filtersActive)
        @php($sliders = [
            ['key' => 'newest', 'title' => 'جدیدترین محصولات', 'icon' => 'sparkles',   'items' => $newest,     'accent' => 'from-primary-500 to-primary-700'],
            ['key' => 'discount', 'title' => 'تخفیف‌های ویژه', 'icon' => 'coins',       'items' => $discounted, 'accent' => 'from-rose-500 to-rose-700', 'onlyIf' => $discounted->isNotEmpty()],
            ['key' => 'popular', 'title' => 'پربازدیدترین‌ها',  'icon' => 'trending-up', 'items' => $popular,    'accent' => 'from-amber-500 to-orange-600', 'onlyIf' => $popular->isNotEmpty()],
        ])
        @foreach ($sliders as $slider)
            @if (! isset($slider['onlyIf']) || $slider['onlyIf'])
                @if ($slider['items']->isNotEmpty())
                    <section class="mx-auto max-w-6xl px-4 pt-8 sm:px-6" aria-label="{{ $slider['title'] }}">
                        <div class="flex items-center justify-between pb-3">
                            <div class="flex items-center gap-2">
                                <span class="grid size-7 place-items-center rounded-lg bg-gradient-to-bl text-white shadow-md {{ $slider['accent'] }}">
                                    <x-icon name="{{ $slider['icon'] }}" class="size-3.5" />
                                </span>
                                <h2 class="text-sm font-extrabold sm:text-base">{{ $slider['title'] }}</h2>
                            </div>
                            <div class="hidden items-center gap-1 lg:flex" x-data
                                 aria-hidden="true">
                                <button type="button"
                                        @click="$el.closest('section').querySelector('[data-slider-track]').scrollBy({ left: 320, behavior: 'smooth' })"
                                        class="grid size-8 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                                        aria-label="اسلاید قبلی">
                                    <x-icon name="chevron-right" class="size-4" />
                                </button>
                                <button type="button"
                                        @click="$el.closest('section').querySelector('[data-slider-track]').scrollBy({ left: -320, behavior: 'smooth' })"
                                        class="grid size-8 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                                        aria-label="اسلاید بعدی">
                                    <x-icon name="chevron-left" class="size-4" />
                                </button>
                            </div>
                        </div>

                        <div class="scrollbar-hide -mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-2 sm:mx-0 sm:px-0" data-slider-track>
                            @foreach ($slider['items'] as $product)
                                @include('livewire.storefront.partials.product-card', ['product' => $product, 'isFav' => in_array($product->id, $favoriteIds), 'prefix' => $slider['key'], 'variant' => 'slide'])
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif
        @endforeach
    @endunless

    {{-- ═══════════════ ALL PRODUCTS + FILTERS ═══════════════ --}}
    <section id="sf-products" class="mx-auto max-w-6xl scroll-mt-20 px-4 pb-16 pt-8 sm:px-6">
        <div class="flex items-center justify-between pb-3">
            <div class="flex items-center gap-2">
                <span class="grid size-7 place-items-center rounded-lg bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                    <x-icon name="store" class="size-3.5" />
                </span>
                <h2 class="text-sm font-extrabold sm:text-base">{{ $activeCat?->name ?: 'همه محصولات' }}</h2>
            </div>
            <span class="text-[11px] font-bold text-slate-400">{{ \App\Support\Fmt::toFa($grid->count()) }} محصول</span>
        </div>

        {{-- toolbar: search + sort chips --}}
        <div class="flex flex-col gap-2.5 rounded-3xl border border-slate-200 bg-white p-3 shadow-sm sm:flex-row sm:items-center dark:border-slate-800 dark:bg-slate-900">
            <label class="flex flex-1 items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 transition focus-within:border-primary-400 focus-within:bg-white dark:border-slate-700 dark:bg-slate-800">
                <x-icon name="search" class="size-4 shrink-0 text-slate-400" />
                <input type="search" wire:model.live.debounce.400ms="q" placeholder="جستجو در محصولات…"
                       class="w-full bg-transparent text-xs outline-none placeholder:text-slate-400">
            </label>

            <div class="scrollbar-hide -mx-1 flex gap-1.5 overflow-x-auto px-1" role="group" aria-label="مرتب‌سازی">
                @foreach ($sorts as $key => $label)
                    <button type="button" wire:key="sort-{{ $key }}"
                            wire:click="$set('sort', '{{ $key }}')"
                            class="shrink-0 rounded-xl px-3 py-2 text-[10.5px] font-extrabold transition active:scale-95 {{ $sort === $key ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- grid --}}
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($grid as $product)
                @include('livewire.storefront.partials.product-card', ['product' => $product, 'isFav' => in_array($product->id, $favoriteIds), 'prefix' => 'grid', 'variant' => 'grid'])
            @empty
                <div class="col-span-2 flex flex-col items-center gap-3 rounded-3xl border border-dashed border-slate-300 py-12 text-center sm:col-span-3 lg:col-span-4 dark:border-slate-700">
                    <span class="grid size-16 place-items-center rounded-3xl bg-primary-50 text-primary-400 dark:bg-primary-950">
                        <x-icon name="package" class="size-8" />
                    </span>
                    @if ($q !== '' || $catId > 0 || $sort === 'discount')
                        <p class="text-sm font-extrabold">محصولی پیدا نشد</p>
                        <p class="max-w-[280px] text-xs leading-6 text-slate-400">با این فیلترها محصولی نیست؛ فیلترها را پاک کنید یا عبارت دیگری ببینید.</p>
                        <button type="button" wire:click="$set('catId', 0); $set('q', ''); $set('sort', 'newest')"
                                class="rounded-xl bg-primary-600 px-4 py-2 text-[11px] font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                            پاک کردن فیلترها
                        </button>
                    @else
                        <p class="text-sm font-extrabold">این فروشگاه هنوز محصولی منتشر نکرده است</p>
                        <p class="max-w-[280px] text-xs leading-6 text-slate-400">به‌زودی اولین محصولات اینجا نمایش داده می‌شوند.</p>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- lazy-load sentinel --}}
        @if ($hasMore && $grid->isNotEmpty())
            <div x-data
                 x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '320px' }).observe($el)"
                 class="mt-6 grid place-items-center py-2">
                <span class="inline-block size-6 animate-spin rounded-full border-2 border-primary-600 border-t-transparent"></span>
            </div>
        @endif
    </section>

    {{-- ═══════════════ toast ═══════════════ --}}
    <div x-data="{ show: false, msg: '' }"
         @storefront-toast.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2600)"
         x-show="show" x-transition.opacity
         class="fixed bottom-8 left-1/2 z-50 w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95"
         x-cloak>
        <span x-text="msg"></span>
    </div>
</div>
