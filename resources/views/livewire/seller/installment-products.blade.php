<div class="ip-fade-up"
     x-data="{ bulkBusy: false }">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('seller.shop.edit') }}" wire:navigate
                   class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-violet-300 hover:text-violet-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                   aria-label="بازگشت به ویرایش فروشگاه">
                    <x-icon name="arrow-right" class="size-5" />
                </a>
                <div>
                    <div class="text-sm font-extrabold leading-5">محصولات مستثنی اقساطی</div>
                    <div class="text-[11px] text-slate-400">محصولاتی که نمی‌خواهید اقساطی فروخته شوند</div>
                </div>
            </div>
            <x-seller-notif-bell class="ms-auto" />
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    @unless ($hasBnplEnabled)
        {{-- ── روش اقساطی فعال نیست — راهنما ── --}}
        <div class="px-4 pt-6">
            <div class="rounded-3xl border border-dashed border-violet-300 bg-violet-50/50 p-8 text-center dark:border-violet-800 dark:bg-violet-950/20">
                <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-violet-100 text-violet-400 dark:bg-violet-950/60 dark:text-violet-500">
                    <x-icon name="calendar-clock" class="size-8" />
                </span>
                <p class="mt-4 text-sm font-extrabold">پرداخت اقساطی هنوز فعال نیست</p>
                <p class="mt-1.5 text-[11px] leading-6 text-slate-500 dark:text-slate-400">
                    این بخش وقتی معنا دارد که حداقل یکی از درگاه‌های پرداخت اقساطی
                    (دیجی‌پی، اسنپ‌پی، ترب‌پی) را از «ویرایش فروشگاه» فعال کرده باشید.
                    بعد از فعال‌سازی، از همین‌جا محصولات مستثنی را انتخاب می‌کنید.
                </p>
                <a href="{{ route('seller.shop.edit') }}" wire:navigate
                   class="mx-auto mt-5 flex w-full max-w-56 items-center justify-center gap-1.5 rounded-2xl bg-violet-600 py-3 text-xs font-extrabold text-white shadow-lg shadow-violet-600/30 transition hover:bg-violet-700 active:scale-95">
                    <x-icon name="store" class="size-4" />
                    فعال‌سازی پرداخت اقساطی
                </a>
            </div>
        </div>
    @else
        <div class="px-4 pt-4">
            {{-- ───────── توضیح + درگاه‌های فعال ───────── --}}
            <div class="rounded-2xl border border-violet-200 bg-gradient-to-l from-violet-50/80 to-fuchsia-50/50 p-4 shadow-sm dark:border-violet-900 dark:from-violet-950/30 dark:to-fuchsia-950/20">
                <div class="flex items-center gap-2">
                    <span class="grid size-8 place-items-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-950/60 dark:text-violet-300">
                        <x-icon name="shield-off" class="size-4" />
                    </span>
                    <h3 class="text-xs font-extrabold">استثنا کردن محصولات از خرید اقساطی</h3>
                </div>
                <p class="mt-2 text-[10.5px] font-semibold leading-5 text-violet-700/90 dark:text-violet-300/90">
                    هر محصولی که این‌جا مستثنی کنید در پرداخت اقساطی شرکت نمی‌کند: بج «امکان خرید اقساطی» از صفحهٔ آن محصول حذف می‌شود و اگر مشتری روش اقساطی را انتخاب کند، همین محصول در مودال اعلام و از سبد جدا می‌شود. بقیهٔ محصولات شما عادی و اقساطی فروخته می‌شوند.
                </p>
                <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                    <span class="text-[9.5px] font-bold text-violet-500 dark:text-violet-400">روش‌های فعال شما:</span>
                    @forelse ($enabledGateways as $gwKey => $gwLabel)
                        <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[9.5px] font-extrabold text-violet-700 ring-1 ring-violet-200 dark:bg-slate-900 dark:text-violet-300 dark:ring-violet-800" wire:key="igw-{{ $gwKey }}">
                            {{ $gwLabel }}
                        </span>
                    @empty
                        <span class="text-[9.5px] font-bold text-amber-600 dark:text-amber-400">هیچ روشی فعال نیست</span>
                    @endforelse
                </div>
            </div>

            {{-- ───────── آمار ───────── --}}
            <div class="mt-3 grid grid-cols-3 gap-2" wire:key="ip-stats">
                <div class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-base font-black text-slate-700 dark:text-slate-200">{{ \App\Support\Fmt::toFa($stats['total']) }}</div>
                    <div class="mt-0.5 text-[9.5px] font-bold text-slate-400">کل محصولات</div>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-3 text-center shadow-sm dark:border-amber-900/60 dark:bg-amber-950/20">
                    <div class="text-base font-black text-amber-600 dark:text-amber-300">{{ \App\Support\Fmt::toFa($stats['excluded']) }}</div>
                    <div class="mt-0.5 text-[9.5px] font-bold text-amber-500/80 dark:text-amber-400/80">مستثنی‌شده</div>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-3 text-center shadow-sm dark:border-emerald-900/60 dark:bg-emerald-950/20">
                    <div class="text-base font-black text-emerald-600 dark:text-emerald-300">{{ \App\Support\Fmt::toFa($stats['allowed']) }}</div>
                    <div class="mt-0.5 text-[9.5px] font-bold text-emerald-500/80 dark:text-emerald-400/80">مجاز اقساطی</div>
                </div>
            </div>
        </div>

        {{-- ───────── Filters (sticky) ───────── --}}
        <div class="sticky top-[65px] z-10 mt-3 bg-slate-50/95 px-4 py-3 backdrop-blur dark:bg-slate-950/95">
            {{-- search --}}
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجو در محصولات..."
                       class="w-full rounded-2xl border border-slate-200 bg-white py-2.5 pl-4 pr-10 text-xs font-semibold outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:ring-2 focus:ring-violet-500/20 dark:border-slate-700 dark:bg-slate-900">
                @if ($search)
                    <button type="button" wire:click="$set('search', '')" aria-label="پاک کردن جستجو"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <x-icon name="x" class="size-4" />
                    </button>
                @endif
            </div>

            {{-- وضعیت اقساطی + مرتب‌سازی --}}
            <div class="mt-2.5 grid grid-cols-2 gap-2">
                <div class="flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800" role="group" aria-label="فیلتر وضعیت اقساطی">
                    @foreach (['all' => 'همه', 'allowed' => 'مجاز', 'excluded' => 'مستثنی'] as $key => $label)
                        <button type="button" wire:click="$set('filter', '{{ $key }}')" data-ip-pick data-ip-group="ip-filter"
                                data-ip-pick-class="bg-white text-violet-600 shadow-sm dark:bg-slate-900 dark:text-violet-400"
                                class="flex-1 rounded-lg py-1.5 text-[10px] font-bold transition {{ $filter === $key ? 'bg-white text-violet-600 shadow-sm dark:bg-slate-900 dark:text-violet-400' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                <div class="flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800" role="group" aria-label="مرتب‌سازی">
                    @foreach (['new' => 'جدیدترین', 'expensive' => 'گران‌ترین', 'cheap' => 'ارزان‌ترین'] as $key => $label)
                        <button type="button" wire:click="$set('sort', '{{ $key }}')" data-ip-pick data-ip-group="ip-sort"
                                data-ip-pick-class="bg-white text-violet-600 shadow-sm dark:bg-slate-900 dark:text-violet-400"
                                class="flex-1 rounded-lg py-1.5 text-[10px] font-bold transition {{ $sort === $key ? 'bg-white text-violet-600 shadow-sm dark:bg-slate-900 dark:text-violet-400' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- category chips (horizontal scroll) --}}
            <div class="scroll-slim -mx-4 mt-2 flex gap-2 overflow-x-auto px-4 pb-1">
                <button type="button" wire:click="$set('categoryFilter', 0)" data-ip-pick data-ip-group="ip-cat"
                        data-ip-pick-class="bg-violet-600 text-white shadow shadow-violet-600/30"
                        class="shrink-0 rounded-full px-3 py-1.5 text-[11px] font-bold transition {{ $categoryFilter === 0 ? 'bg-violet-600 text-white shadow shadow-violet-600/30' : 'border border-slate-200 bg-white text-slate-500 hover:border-violet-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                    همه دسته‌ها
                </button>
                @foreach ($categories as $cat)
                    <button type="button" wire:click="$set('categoryFilter', {{ $cat->id }})" data-ip-pick data-ip-group="ip-cat"
                            data-ip-pick-class="bg-violet-600 text-white shadow shadow-violet-600/30"
                            class="shrink-0 rounded-full px-3 py-1.5 text-[11px] font-bold transition {{ $categoryFilter === $cat->id ? 'bg-violet-600 text-white shadow shadow-violet-600/30' : 'border border-slate-200 bg-white text-slate-500 hover:border-violet-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                        {{ $cat->name }}
                        <span class="opacity-60">{{ \App\Support\Fmt::fa($cat->products_count) }}</span>
                    </button>
                @endforeach
            </div>

            {{-- اکشن گروهی — روی نتایج فعلی --}}
            <div class="mt-2 flex gap-2">
                <button type="button" wire:click="excludeFiltered" wire:loading.attr="disabled" wire:target="excludeFiltered, restoreFiltered"
                        class="flex flex-1 items-center justify-center gap-1 rounded-xl border border-amber-300 bg-amber-50/70 py-2 text-[10px] font-extrabold text-amber-700 transition hover:bg-amber-100/70 disabled:opacity-50 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300">
                    <x-icon name="shield-off" class="size-3" />
                    استثنا کردن همهٔ نتایج
                </button>
                <button type="button" wire:click="restoreFiltered" wire:loading.attr="disabled" wire:target="excludeFiltered, restoreFiltered"
                        class="flex flex-1 items-center justify-center gap-1 rounded-xl border border-emerald-300 bg-emerald-50/70 py-2 text-[10px] font-extrabold text-emerald-700 transition hover:bg-emerald-100/70 disabled:opacity-50 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                    <x-icon name="check-circle-2" class="size-3" />
                    برگرداندن همهٔ نتایج
                </button>
            </div>
        </div>

        {{-- ───────── Product rows ───────── --}}
        <div class="px-4 transition" wire:loading.class="opacity-40 pointer-events-none" wire:target="search, filter, categoryFilter, sort, excludeFiltered, restoreFiltered">
            @forelse ($products as $product)
                @php $isExcluded = in_array($product->id, $excludedIds); @endphp
                <div class="mt-3 flex items-center gap-3 rounded-2xl border p-3 shadow-sm transition {{ $isExcluded
                    ? 'border-amber-200 bg-amber-50/40 dark:border-amber-900/60 dark:bg-amber-950/10'
                    : 'border-slate-200 bg-white hover:border-violet-200 dark:border-slate-800 dark:bg-slate-900' }}"
                    wire:key="ip-row-{{ $product->id }}">
                    {{-- thumbnail --}}
                    <a href="{{ route('seller.products.edit', $product) }}" wire:navigate
                       class="relative size-16 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800" aria-label="ویرایش {{ $product->name }}">
                        @if ($product->mainImage)
                            <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy" class="size-full object-cover">
                        @else
                            <span class="grid size-full place-items-center text-slate-300 dark:text-slate-600">
                                <x-icon name="image" class="size-6" />
                            </span>
                        @endif
                    </a>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('seller.products.edit', $product) }}" wire:navigate class="block truncate text-xs font-extrabold hover:text-violet-600 dark:hover:text-violet-400">
                            {{ $product->name }}
                        </a>
                        <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[9.5px] font-bold">
                            <span class="text-slate-500 dark:text-slate-400">{{ \App\Support\Fmt::money($product->price) }}</span>
                            @if ($product->category)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $product->category->name }}</span>
                            @endif
                            @if ($isExcluded)
                                <span class="inline-flex items-center gap-0.5 rounded-full bg-amber-100 px-2 py-0.5 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                    <x-icon name="shield-off" class="size-2.5" />
                                    مستثنی از اقساط
                                </span>
                            @else
                                <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    <x-icon name="check-circle-2" class="size-2.5" />
                                    مجاز اقساطی
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- toggle --}}
                    <button type="button" wire:click="toggleExclusion({{ $product->id }})"
                            wire:loading.attr="disabled" wire:target="toggleExclusion({{ $product->id }})"
                            aria-label="{{ $isExcluded ? 'حذف استثنا و بازگشت به خرید اقساطی' : 'استثنا کردن از خرید اقساطی' }}"
                            class="grid size-10 shrink-0 place-items-center rounded-xl border transition active:scale-95 {{ $isExcluded
                                ? 'border-emerald-300 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'
                                : 'border-amber-300 bg-amber-50 text-amber-600 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300' }}"
                            title="{{ $isExcluded ? 'حذف از استثنا (بازگشت به اقساطی)' : 'استثنا کردن از خرید اقساطی' }}">
                        <x-icon name="{{ $isExcluded ? 'rotate' : 'shield-off' }}" class="size-4.5" />
                    </button>
                </div>
            @empty
                <div class="mt-6 rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                    <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                        <x-icon name="{{ ($search !== '' || $categoryFilter !== 0 || $filter !== 'all') ? 'search' : 'package' }}" class="size-7 text-slate-300 dark:text-slate-600" />
                    </span>
                    <p class="mt-3 text-xs font-extrabold">{{ ($search !== '' || $categoryFilter !== 0 || $filter !== 'all') ? 'محصولی با این جستجو/فیلتر پیدا نشد' : 'هنوز محصولی در فروشگاه ندارید' }}</p>
                    <p class="mt-1 text-[10px] text-slate-400">{{ ($search !== '' || $categoryFilter !== 0 || $filter !== 'all') ? 'جستجو یا فیلترها را تغییر دهید.' : 'اول از «فروشگاه من» محصول اضافه کنید.' }}</p>
                </div>
            @endforelse

            {{-- load more (infinite scroll like the catalog page) --}}
            <div x-data x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
                 class="{{ $hasMore ? 'mt-4 flex justify-center py-2' : 'hidden' }}">
                <span class="inline-block size-6 animate-spin rounded-full border-2 border-violet-500 border-t-transparent"></span>
            </div>

            <p class="mt-3 text-center text-[9.5px] font-bold text-slate-400">
                نمایش {{ \App\Support\Fmt::toFa(min(count($products), 60)) }} از {{ \App\Support\Fmt::toFa($stats['total']) }} محصول
            </p>
        </div>

        <div class="pb-4"></div>
    @endunless
</div>
