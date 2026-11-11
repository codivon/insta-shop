<div class="ip-fade-up" x-data="{
        flashLeft: {{ (int) max(0, now()->diffInSeconds(now()->endOfDay())) }},
        faNum(n) { return String(Math.max(0, n)).padStart(2, '0').replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]) }
     }"
     x-init="setInterval(() => { if (flashLeft > 0) flashLeft-- }, 1000)">
    {{-- ───────── Sticky in-feed header — Basalam-style search pill ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-2.5 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2">
            <label class="flex flex-1 items-center gap-2.5 rounded-full border border-slate-200 bg-white py-2 pe-2 ps-2 shadow-md shadow-slate-200/60 focus-within:border-primary-400 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-md shadow-primary-600/30">
                    <x-icon name="bag" class="size-4" />
                </span>
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجو در اینستاشاپ…"
                       class="w-full bg-transparent text-xs font-semibold outline-none placeholder:text-slate-400">
                <x-theme-toggle class="size-8 shrink-0 rounded-full text-slate-400" />
            </label>
        </div>
    </div>

    <div class="px-4 pt-3">
        @if ($search === '')
            {{-- ── Basalam-style orange promo strip ── --}}
            <a href="{{ route('shop.categories') }}" wire:navigate
               class="mb-4 flex items-center justify-between rounded-2xl bg-gradient-to-l from-orange-600 via-orange-500 to-amber-500 px-3.5 py-2.5 text-white shadow-lg shadow-orange-500/25 transition active:scale-[0.98]">
                <span class="text-[11px] font-extrabold">🛍️ خرید مستقیم از فروشگاه‌های اینستاگرامی — بدون واسطه</span>
                <x-icon name="chevron-left" class="size-4 shrink-0 opacity-90" />
            </a>

            {{-- ── story rings (Instagram-style, one ring per shop) ──
                 pt-2.5 = client v0.17.15: «نمایش استوری‌ها را از بالا ۱۰ پیکسل
                 فاصله بده» — also un-clips the «جدید» badge (-top-1) that the
                 overflow-x container used to cut off at its top edge. --}}
            @if ($storyShops->isNotEmpty())
                <div class="scroll-slim -mx-1 mb-4 flex gap-3 overflow-x-auto px-1 pb-2 pt-2.5" aria-label="استوری فروشگاه‌ها">
                    @foreach ($storyShops as $ring)
                        <button type="button" wire:click="openStories({{ $ring['shop']->id }})"
                                wire:key="sring-{{ $ring['shop']->id }}"
                                class="group relative w-[4.25rem] shrink-0 text-center transition active:scale-95"
                                aria-label="نمایش استوری‌های {{ $ring['shop']->name }}">
                            @if ($ring['hasUnseen'])
                                <span class="absolute -top-1 start-0 z-10 rounded-full bg-gradient-to-l from-orange-500 to-amber-500 px-1.5 py-px text-[8px] font-black text-white shadow">جدید</span>
                            @endif
                            <span class="relative inline-grid place-items-center rounded-full p-[2.5px] {{ $ring['hasUnseen'] ? 'bg-gradient-to-tr from-amber-400 via-rose-500 to-primary-600' : 'bg-slate-300 dark:bg-slate-700' }}">
                                <span class="grid size-14 place-items-center overflow-hidden rounded-full bg-white p-[2.5px] dark:bg-slate-950">
                                    @if ($ring['shop']->logo_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($ring['shop']->logo_path) }}"
                                             alt="{{ $ring['shop']->name }}" loading="lazy" class="size-full rounded-full object-cover">
                                    @else
                                        <span class="grid size-full place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-black text-white">{{ mb_substr($ring['shop']->name, 0, 1) }}</span>
                                    @endif
                                </span>
                                @if ($ring['count'] > 1)
                                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 rounded-full bg-slate-900/85 px-1.5 text-[8.5px] font-black text-white">{{ \App\Support\Fmt::fa($ring['count']) }}</span>
                                @endif
                            </span>
                            <span class="mt-1.5 block truncate text-[10px] font-bold text-slate-600 dark:text-slate-300">{{ $ring['shop']->name }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- ── hero banner slider (admin-managed) or default promo ── --}}
            @if ($banners->isNotEmpty())
                {{-- touch swipe: horizontal pan drags the track live (tx in px),
                     a ≥40px flick commits to the next/prev slide; `touch-pan-y`
                     keeps vertical page scrolling native. Track stays LTR so
                     translateX math is the classic carousel. --}}
                <div x-data="{
                         slide: 0, n: {{ $banners->count() }},
                         go(i) { this.slide = (i + this.n) % this.n },
                         tx: 0, dragging: false, sx: 0,
                         tStart(e) { this.dragging = true; this.sx = e.touches[0].clientX; this.tx = 0 },
                         tMove(e) { if (this.dragging) this.tx = e.touches[0].clientX - this.sx },
                         tEnd() {
                             if (! this.dragging) return;
                             this.dragging = false;
                             if (Math.abs(this.tx) > 40) this.go(this.slide + (this.tx < 0 ? 1 : -1));
                             this.tx = 0;
                         }
                     }"
                     x-init="{{ $banners->count() > 1 ? 'setInterval(() => go(slide + 1), 5000)' : '' }}"
                     class="relative overflow-hidden rounded-2xl shadow-xl"
                     @touchstart="tStart" @touchmove="tMove" @touchend="tEnd"
                     aria-label="اسلایدر بنر">
                    <div dir="ltr"
                         class="flex select-none ease-out"
                         :class="dragging ? 'transition-none' : 'transition-transform duration-500'"
                         :style="'transform: translateX(calc(-' + (slide * 100) + '% + ' + tx + 'px))'"
                         style="touch-action: pan-y">
                        @foreach ($banners as $banner)
                            <div wire:key="banner-slide-{{ $banner->id }}" class="w-full shrink-0">
                                @if ($banner->link_url)
                                    <a href="{{ $banner->link_url }}" class="block" draggable="false">
                                        <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title ?: 'بنر' }}" loading="lazy" draggable="false"
                                             class="aspect-[12/5] w-full object-cover">
                                    </a>
                                @else
                                    <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title ?: 'بنر' }}" loading="lazy" draggable="false"
                                         class="aspect-[12/5] w-full object-cover">
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if ($banners->count() > 1)
                        <div class="absolute inset-x-0 bottom-2.5 flex items-center justify-center gap-1.5">
                            @foreach ($banners as $dot)
                                <button type="button" x-on:click="go({{ $loop->index }})"
                                        class="h-1.5 rounded-full transition-all"
                                        x-bind:class="slide === {{ $loop->index }} ? 'w-4 bg-white' : 'w-1.5 bg-white/50'"
                                        aria-label="اسلاید {{ \App\Support\Fmt::fa($loop->index + 1) }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500 p-4 text-white shadow-xl shadow-primary-600/25">
                    <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
                        <div class="absolute -top-6 left-4 size-32 rounded-full bg-white/30 blur-2xl"></div>
                    </div>
                    <p class="relative text-[11px] text-primary-100">🛍️ خرید مستقیم از فروشگاه‌های اینستاگرامی</p>
                    <h2 class="relative mt-1 text-base font-extrabold leading-7">محصولات فروشنده‌های<br>تأییدشده را همین‌جا بخر</h2>
                    <a href="#shops" class="relative mt-3 inline-block rounded-xl bg-white px-4 py-2 text-[11px] font-bold text-primary-700 shadow-md transition active:scale-95">
                        دیدن فروشگاه‌ها
                    </a>
                    <div class="absolute -left-4 bottom-0 grid size-24 place-items-center opacity-90" aria-hidden="true">
                        <x-icon name="bag" class="size-20 text-white/25" />
                    </div>
                </div>
            @endif

            {{-- ── نردبان‌ها (v0.23.0 — Divar-style top strip) ──
                 products whose seller bought a running bump window;
                 the strip only renders while at least one bump is alive. --}}
            @if ($bumpProducts->isNotEmpty())
                <div class="mt-5 overflow-hidden rounded-2xl border border-orange-200 bg-gradient-to-l from-orange-50 to-amber-50/50 shadow-sm dark:border-orange-900/60 dark:from-orange-950/20 dark:to-amber-950/10" aria-label="نردبان‌ها">
                    <div class="flex items-center justify-between px-3.5 pt-2.5">
                        <span class="flex items-center gap-1.5 text-xs font-black text-orange-700 dark:text-orange-300">
                            <span class="grid size-6 place-items-center rounded-lg bg-gradient-to-br from-orange-500 to-amber-500 text-white shadow-sm">
                                <x-icon name="rocket" class="size-3.5" />
                            </span>
                            نردبان‌ها
                        </span>
                        <span class="text-[9.5px] font-bold text-orange-600/70 dark:text-orange-400/70">نمایش در صدر صفحهٔ اول</span>
                    </div>

                    <div class="scroll-slim flex gap-2.5 overflow-x-auto p-2.5">
                        @foreach ($bumpProducts as $bumpProduct)
                            <a href="{{ $bumpProduct->publicUrl() }}" wire:navigate wire:key="bump-{{ $bumpProduct->id }}"
                               class="group w-28 shrink-0 overflow-hidden rounded-xl border border-orange-200/70 bg-white p-1.5 shadow-sm transition hover:shadow-md active:scale-95 dark:border-orange-900/50 dark:bg-slate-900">
                                <div class="relative grid aspect-square place-items-center overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800">
                                    @if ($bumpProduct->mainImage)
                                        <img src="{{ $bumpProduct->mainImage->url() }}" alt="{{ $bumpProduct->name }}" loading="lazy" class="size-full object-cover transition duration-300 group-hover:scale-105">
                                    @else
                                        <x-icon name="package" class="size-8 text-slate-300 dark:text-slate-600" />
                                    @endif
                                    <span class="absolute end-1 top-1 rounded-full bg-gradient-to-l from-orange-500 to-amber-500 px-1.5 py-0.5 text-[8px] font-black text-white shadow">نردبان</span>
                                </div>
                                <p class="mt-1.5 line-clamp-1 px-0.5 text-[10px] font-bold text-slate-600 dark:text-slate-300">{{ $bumpProduct->name }}</p>
                                <div class="mt-0.5 flex items-baseline gap-1.5 px-0.5 pb-0.5">
                                    <span class="text-[11px] font-black text-orange-600 dark:text-orange-400">{{ \App\Support\Fmt::moneyShort($bumpProduct->price) }}</span>
                                    <span class="text-[8.5px] font-bold text-slate-400">تومان</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── Basalam-style category grid (۷ دستهٔ اصلی + همه) ── --}}
            @if ($topCategories->isNotEmpty())
                <div class="mt-5">
                    <h3 class="text-sm font-extrabold">خرید بر اساس دسته‌بندی</h3>
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach ($topCategories as $topCategory)
                            <a href="{{ route('shop.categories.show', $topCategory->id) }}" wire:navigate wire:key="cat-{{ $topCategory->id }}"
                               class="flex flex-col items-center gap-1.5 rounded-2xl border border-slate-200 bg-white p-2.5 shadow-sm transition hover:shadow-md active:scale-95 dark:border-slate-800 dark:bg-slate-900">
                                <span class="grid size-12 place-items-center overflow-hidden rounded-full bg-slate-50 dark:bg-slate-800">
                                    @if (filled($topCategory->image))
                                        <img src="{{ $topCategory->image }}" alt="{{ $topCategory->name }}" loading="lazy" class="size-9 object-contain">
                                    @else
                                        <x-icon name="layout-grid" class="size-5 text-slate-400" />
                                    @endif
                                </span>
                                <span class="w-full truncate text-center text-[9.5px] font-bold leading-3.5 text-slate-600 dark:text-slate-300">{{ $topCategory->name }}</span>
                            </a>
                        @endforeach
                        <a href="{{ route('shop.categories') }}" wire:navigate
                           class="flex flex-col items-center gap-1.5 rounded-2xl border border-primary-200 bg-primary-50/60 p-2.5 shadow-sm transition hover:shadow-md active:scale-95 dark:border-primary-900 dark:bg-primary-950/30">
                            <span class="grid size-12 place-items-center rounded-full bg-primary-100 text-primary-600 dark:bg-primary-900/60 dark:text-primary-300">
                                <x-icon name="layout-grid" class="size-5" />
                            </span>
                            <span class="w-full truncate text-center text-[9.5px] font-bold leading-3.5 text-primary-700 dark:text-primary-300">همهٔ دسته‌ها</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- ── Basalam-style flash sale strip + countdown + discounted scroller ── --}}
            @if ($flashProducts->isNotEmpty())
                <div class="mt-5" aria-label="پیشنهاد شگفت‌انگیز">
                    <div class="flex items-center justify-between rounded-2xl bg-gradient-to-l from-rose-600 via-orange-500 to-amber-500 px-3.5 py-2.5 shadow-lg shadow-orange-500/20">
                        <span class="flex items-center gap-1.5 text-xs font-black text-white">
                            <x-icon name="zap" class="size-4" />
                            پیشنهاد شگفت‌انگیز
                        </span>
                        <span class="flex items-center gap-1 text-rose-600" dir="ltr" role="timer" aria-label="زمان باقی‌مانده امروز">
                            <span class="rounded-lg bg-white/95 px-1.5 py-0.5 text-[11px] font-black tabular-nums" x-text="faNum(Math.floor(flashLeft / 3600))">…</span>
                            <span class="text-[11px] font-black text-white/90">:</span>
                            <span class="rounded-lg bg-white/95 px-1.5 py-0.5 text-[11px] font-black tabular-nums" x-text="faNum(Math.floor(flashLeft / 60) % 60)">…</span>
                            <span class="text-[11px] font-black text-white/90">:</span>
                            <span class="rounded-lg bg-white/95 px-1.5 py-0.5 text-[11px] font-black tabular-nums" x-text="faNum(flashLeft % 60)">…</span>
                        </span>
                    </div>

                    <div class="scroll-slim mt-3 flex gap-2.5 overflow-x-auto pb-1">
                        @foreach ($flashProducts as $flashProduct)
                            @php
                                $flashDiscount = $flashProduct->compare_at_price > 0 && $flashProduct->compare_at_price > $flashProduct->price
                                    ? (int) round(100 - ($flashProduct->price / $flashProduct->compare_at_price * 100))
                                    : 0;
                            @endphp
                            <a href="{{ $flashProduct->publicUrl() }}" wire:navigate wire:key="flash-{{ $flashProduct->id }}"
                               class="group w-28 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-sm transition hover:shadow-md active:scale-95 dark:border-slate-800 dark:bg-slate-900">
                                <div class="relative grid aspect-square place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                    @if ($flashProduct->mainImage)
                                        <img src="{{ $flashProduct->mainImage->url() }}" alt="{{ $flashProduct->name }}" loading="lazy" class="size-full object-cover transition duration-300 group-hover:scale-105">
                                    @else
                                        <x-icon name="package" class="size-8 text-slate-300 dark:text-slate-600" />
                                    @endif
                                    @if ($flashDiscount > 0)
                                        <span class="absolute start-1.5 top-1.5 rounded-full bg-rose-500 px-1.5 py-0.5 text-[9px] font-black text-white shadow">٪{{ \App\Support\Fmt::toFa($flashDiscount) }}</span>
                                    @endif
                                </div>
                                <p class="mt-1.5 line-clamp-1 text-[10px] font-bold text-slate-600 dark:text-slate-300">{{ $flashProduct->name }}</p>
                                <div class="mt-0.5 flex items-baseline gap-1.5">
                                    <span class="text-[11px] font-black text-rose-600 dark:text-rose-400">{{ \App\Support\Fmt::moneyShort($flashProduct->price) }}</span>
                                    <span class="text-[8.5px] font-bold text-slate-400">تومان</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── banner cards 2×2 grid (admin-managed) ── --}}
            @if ($homeBanners->isNotEmpty())
                <div class="mt-5 grid grid-cols-2 gap-2.5" aria-label="بنرها">
                    @foreach ($homeBanners as $homeBanner)
                        <a href="{{ $homeBanner->link_url ?? '#' }}" @if ($homeBanner->link_url) wire:navigate @endif wire:key="hbanner-{{ $homeBanner->id }}"
                           class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md active:scale-95 dark:border-slate-800 dark:bg-slate-900">
                            <img src="{{ $homeBanner->imageUrl() }}" alt="{{ $homeBanner->title ?: 'بنر' }}" loading="lazy"
                                 class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                            @if ($homeBanner->title)
                                <span class="absolute inset-x-0 bottom-0 truncate bg-gradient-to-t from-black/70 to-transparent px-2.5 pb-2 pt-6 text-[10px] font-extrabold text-white">{{ $homeBanner->title }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- ── Basalam-style shop spotlight («پیشنهادهای امروز بازار») ── --}}
            <div id="shops" class="mt-6 flex items-center justify-between">
                <h3 class="text-sm font-extrabold">فروشگاه‌های فعال</h3>
                <span class="text-[11px] font-semibold text-slate-400">{{ \App\Support\Fmt::toFa($shops->count()) }} فروشگاه</span>
            </div>

            <div class="scroll-slim mt-3 flex gap-3 overflow-x-auto pb-1">
                @forelse ($spotlightShops as $shop)
                    @php $storeUrl = $shop->storefrontHandle() ? $shop->storefrontUrl() : null; @endphp
                    <a href="{{ $storeUrl ?? '#' }}" @if ($storeUrl) wire:navigate @else aria-disabled="true" @endif wire:key="spot-{{ $shop->id }}"
                       class="w-60 shrink-0 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900 {{ $storeUrl ? 'active:scale-[0.98]' : 'pointer-events-none opacity-60' }}">
                        <div class="flex items-center gap-2.5">
                            @if ($shop->logo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->logo_path) }}" alt="لوگوی {{ $shop->name }}" loading="lazy" class="size-10 rounded-full object-cover ring-2 ring-slate-100 dark:ring-slate-800">
                            @else
                                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-black text-white">{{ mb_substr($shop->name, 0, 1) }}</span>
                            @endif
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-xs font-extrabold">{{ $shop->name }}</span>
                                <span class="mt-0.5 flex items-center gap-1 text-[9.5px] text-slate-400">
                                    @if ($shop->instagram_username)
                                        <x-icon name="instagram" class="size-3" />{{ $shop->instagram_username }}
                                    @else
                                        {{ \App\Support\Fmt::toFa($shop->products_count) }} محصول فعال
                                    @endif
                                </span>
                            </span>
                            <x-icon name="chevron-left" class="size-4 shrink-0 text-slate-300" />
                        </div>
                        <div class="mt-2.5 flex gap-1.5">
                            @foreach ($shop->spotlight_products as $spotProduct)
                                <span class="relative grid flex-1 place-items-center overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800" title="{{ $spotProduct->name }}">
                                    @if ($spotProduct->mainImage)
                                        <img src="{{ $spotProduct->mainImage->url() }}" alt="{{ $spotProduct->name }}" loading="lazy" class="aspect-square size-full object-cover">
                                    @else
                                        <x-icon name="package" class="size-5 text-slate-300 dark:text-slate-600" />
                                    @endif
                                </span>
                            @endforeach
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-[10px]">
                            <span class="font-bold text-slate-400">{{ \App\Support\Fmt::toFa($shop->products_count) }} محصول</span>
                            <span class="inline-flex items-center gap-0.5 font-extrabold text-primary-600 dark:text-primary-400">مشاهدهٔ فروشگاه</span>
                        </div>
                    </a>
                @empty
                    <div class="w-full rounded-2xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-400 dark:border-slate-700">
                        فعلاً فروشگاه فعالی وجود ندارد.
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ── shop results — client v0.17.15: «اسم فروشندگان یا آیدی فروشندگان
             یا فروشگاه‌ها را هم در جستجو داشته باشیم و در نتیجه جستجو، فروشگاه
             هم اضافه بشه» — matching shops (name / @handle / seller name)
             appear above the matching products. --}}
        @if ($search !== '' && $searchShops->isNotEmpty())
            <div class="mt-6" aria-label="فروشگاه‌های یافت‌شده">
                <h3 class="text-sm font-extrabold">فروشگاه‌ها</h3>
                <div class="scroll-slim mt-3 flex gap-3 overflow-x-auto pb-1">
                    @foreach ($searchShops as $searchShop)
                        <a href="{{ $searchShop->storefrontUrl() }}" wire:navigate wire:key="sshop-{{ $searchShop->id }}"
                           class="flex w-52 shrink-0 items-center gap-2.5 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-md active:scale-[0.98] dark:border-slate-800 dark:bg-slate-900">
                            @if ($searchShop->logo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($searchShop->logo_path) }}"
                                     alt="لوگوی {{ $searchShop->name }}" loading="lazy"
                                     class="size-11 shrink-0 rounded-full object-cover ring-2 ring-slate-100 dark:ring-slate-800">
                            @else
                                <span class="grid size-11 shrink-0 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-black text-white">{{ mb_substr($searchShop->name, 0, 1) }}</span>
                            @endif
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-xs font-extrabold">{{ $searchShop->name }}</span>
                                <span class="mt-0.5 flex items-center gap-1 text-[9.5px] font-semibold text-slate-400">
                                    @if ($searchShop->storefrontHandle())
                                        <x-icon name="instagram" class="size-3 shrink-0" /><span class="truncate">{{ $searchShop->storefrontHandle() }}</span>
                                    @endif
                                </span>
                                <span class="mt-0.5 block text-[9.5px] font-bold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::toFa($searchShop->products_count) }} محصول</span>
                            </span>
                            <x-icon name="chevron-left" class="size-4 shrink-0 text-slate-300" />
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── product feed (search results replace everything above) ── --}}
        <div class="mt-6 flex items-center justify-between gap-2">
            <h3 class="text-sm font-extrabold">
                {{ $search !== '' ? 'نتیجه جستجو' : 'محصولات' }}
            </h3>
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-semibold text-slate-400">
                    {{ \App\Support\Fmt::toFa($products->count()) }} محصول
                </span>
                <select wire:model.live="sort" aria-label="مرتب‌سازی"
                        class="rounded-xl border border-slate-200 bg-white px-2 py-1.5 text-[10px] font-semibold text-slate-600 shadow-sm outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                    <option value="newest">جدیدترین</option>
                    <option value="popular">پربازدید</option>
                    <option value="cheap">ارزان‌ترین</option>
                    <option value="expensive">گران‌ترین</option>
                </select>
            </div>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-3">
            @forelse ($products as $product)
                @php
                    $isFav = in_array($product->id, $favoriteIds);
                    $discount = $product->compare_at_price > 0 && $product->compare_at_price > $product->price
                        ? (int) round(100 - ($product->price / $product->compare_at_price * 100))
                        : 0;
                @endphp
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <a href="{{ $product->publicUrl() }}" wire:navigate class="block">
                        <div class="relative grid h-32 place-items-center overflow-hidden bg-slate-100 dark:bg-slate-800">
                            @if ($product->mainImage)
                                <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy"
                                     class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <x-icon name="package" class="size-9 text-slate-300 dark:text-slate-600" />
                            @endif
                            @if ($discount > 0)
                                <span class="absolute right-2 top-2 rounded-full bg-rose-500 px-2 py-0.5 text-[9px] font-bold text-white shadow">٪{{ \App\Support\Fmt::toFa($discount) }}</span>
                            @endif
                            @if ($product->type === \App\Models\Product::TYPE_PHYSICAL && $product->stock < 1)
                                <span class="absolute inset-0 grid place-items-center bg-slate-900/50 text-[11px] font-bold text-white">ناموجود</span>
                            @endif
                        </div>
                    </a>
                    <div class="space-y-1.5 p-2.5">
                        <a href="{{ $product->publicUrl() }}" wire:navigate class="line-clamp-2 block min-h-8 text-xs font-bold leading-5 hover:text-primary-600 dark:hover:text-primary-400">
                            {{ $product->name }}
                        </a>
                        <div class="truncate text-[10px] text-slate-400">از {{ $product->shop?->name }}</div>
                        <div class="flex items-end justify-between pt-1">
                            <div>
                                @if ($discount > 0)
                                    <div class="text-[9px] text-slate-400 line-through">{{ \App\Support\Fmt::fa($product->compare_at_price) }}</div>
                                @endif
                                <div class="text-xs font-extrabold text-primary-600 dark:text-primary-400">
                                    {{ \App\Support\Fmt::moneyShort($product->price) }} <span class="text-[9px] font-bold text-slate-400">تومان</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="toggleFavorite({{ $product->id }})"
                                        class="grid size-7 place-items-center rounded-lg border border-slate-200 transition active:scale-90 dark:border-slate-700 {{ $isFav ? 'text-rose-500' : 'text-slate-400 hover:text-rose-400' }}"
                                        aria-label="{{ $isFav ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}">
                                    <x-icon name="heart" class="size-3.5 {{ $isFav ? 'fill-current' : '' }}" />
                                </button>
                                @if ($product->variants_count > 0)
                                    <a href="{{ $product->publicUrl() }}" wire:navigate
                                       class="grid size-7 place-items-center rounded-lg bg-primary-600 text-white shadow-md shadow-primary-600/30 transition active:scale-90"
                                       aria-label="مشاهده و انتخاب تنوع" title="انتخاب تنوع در صفحه محصول">
                                        <x-icon name="layers" class="size-3.5" />
                                    </a>
                                @else
                                    <button type="button" wire:click="addToCart({{ $product->id }})" @disabled($product->type !== \App\Models\Product::TYPE_DOWNLOADABLE && $product->stock < 1)
                                            class="grid size-7 place-items-center rounded-lg bg-primary-600 text-white shadow-md shadow-primary-600/30 transition active:scale-90 disabled:opacity-40 disabled:shadow-none"
                                            aria-label="افزودن به سبد خرید">
                                        <x-icon name="plus" class="size-3.5" />
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                    <x-icon name="search" class="mx-auto size-8 text-slate-300 dark:text-slate-600" />
                    <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">
                        @if ($search !== '')
                            محصولی برای «{{ $search }}» پیدا نشد.
                        @else
                            هنوز محصولی برای نمایش وجود ندارد.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- lazy-load sentinel (IntersectionObserver → home-load-more) —
             client v0.17.15: «وقتی محصول در حال بارگذاری هست بنویسه در حال
             بارگذاری تا کاربر بدونه باید منتظر بمونه» — the old wire:loading
             classes were INVERTED (added opacity-0 while loading → the
             spinner was hidden exactly when needed and visible when idle);             
             now a spinner + «در حال بارگذاری…» pill shows only during the
             loadMore commit, same wording as the seller/admin pages. --}}
        @if ($hasMore && $products->isNotEmpty())
            <div x-data
                 x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
                 class="mt-6 grid place-items-center py-3">
                <div wire:loading wire:target="loadMore"
                     class="flex items-center gap-2 rounded-full bg-primary-50 px-4 py-2 text-primary-600 shadow-sm dark:bg-primary-950/40 dark:text-primary-300">
                    <span class="inline-block size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                    <span class="text-[11px] font-extrabold">در حال بارگذاری محصولات…</span>
                </div>
            </div>
        @endif

        <div class="pb-2"></div>
    </div>

    {{-- ── Instagram-style story viewer (opens from the rings above) ── --}}
    @livewire('customer.story-viewer')

    {{-- toast — z-[130]: must float ABOVE the story viewer (z-[100]) —
         the guest «وارد حساب خود شوید» hint fires from the nested viewer
         and used to render BENEATH its opaque overlay (invisible). --}}
    <div x-data="{ show: false, msg: '' }"
         @home-toast.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2600)"
         @story-toast.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2600)"
         x-show="show" x-transition.opacity
         class="fixed bottom-24 left-1/2 z-[130] w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95"
         x-cloak>
        <span x-text="msg"></span>
    </div>
</div>
