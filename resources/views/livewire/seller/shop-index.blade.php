<div class="ip-fade-up">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                    <x-icon name="store" class="size-5" />
                </span>
                <div>
                    <div class="text-sm font-extrabold leading-5">فروشگاه من</div>
                    <div class="text-[11px] text-slate-400">مدیریت محصولات و دسته‌بندی‌ها</div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <x-theme-toggle class="size-10 text-slate-500" />
                <a href="{{ route('seller.shop.edit') }}" wire:navigate
                   class="grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-800"
                   aria-label="ویرایش فروشگاه">
                    <x-icon name="settings" class="size-5" />
                </a>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4">
        {{-- flash toast --}}
        @if (session('shop-flash'))
            <div class="mb-3 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-bold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 3500)">
                <x-icon name="circle-check" class="size-4" />
                {{ session('shop-flash') }}
            </div>
        @endif

        {{-- ───────── Shop summary card ───────── --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="relative z-0 h-24 overflow-hidden bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500">
                @if ($shop->banner_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->banner_path) }}" alt="بنر فروشگاه"
                         class="absolute inset-0 size-full object-cover">
                @else
                    <div class="absolute inset-0 opacity-20" aria-hidden="true">
                        <div class="absolute -top-6 left-6 size-28 rounded-full bg-white/30 blur-2xl"></div>
                        <div class="absolute -bottom-10 right-8 size-32 rounded-full bg-white/20 blur-2xl"></div>
                    </div>
                @endif
            </div>

            <div class="px-4 pb-4">
                <div class="relative z-10 -mt-7 flex items-end justify-between">
                    <div class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-2xl border-4 border-white bg-white shadow-lg dark:border-slate-900 dark:bg-slate-800">
                        @if ($shop->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->logo_path) }}" alt="لوگوی فروشگاه" class="size-full object-cover">
                        @else
                            <span class="text-lg font-black text-primary-600 dark:text-primary-400">{{ mb_substr($shop->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <a href="{{ route('seller.shop.edit') }}" wire:navigate
                       class="mb-1 flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold text-slate-600 shadow-sm transition hover:border-primary-300 hover:text-primary-600 active:scale-95 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-primary-400">
                        <x-icon name="edit" class="size-3.5" />
                        ویرایش فروشگاه
                    </a>
                </div>

                <h3 class="mt-2 text-sm font-extrabold">{{ $shop->name }}</h3>

                <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[10px] font-bold">
                    @if ($shop->instagram_username)
                        <span class="flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-slate-500 dark:bg-slate-800 dark:text-slate-300" dir="ltr">
                            <x-icon name="instagram" class="size-3" />
                            {{ '@'.$shop->instagram_username }}
                        </span>
                    @endif
                    <span class="rounded-full px-2 py-1 {{ $shop->seller->status === 'approved'
                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                        : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' }}">
                        {{ $shop->seller->status === 'approved' ? 'فروشگاه تأییدشده' : 'در انتظار تأیید مدیر' }}
                    </span>
                    @if ($shop->status === 'paused')
                        <span class="rounded-full bg-rose-100 px-2 py-1 text-rose-700 dark:bg-rose-950 dark:text-rose-300">مکث‌شده (نمایش عمومی خاموش)</span>
                    @endif
                </div>

                {{-- mini stats --}}
                <div class="mt-3 grid grid-cols-4 gap-2">
                    @foreach ([
                        ['value' => \App\Support\Fmt::fa($stats['total']),      'label' => 'محصول'],
                        ['value' => \App\Support\Fmt::fa($stats['published']),  'label' => 'منتشرشده'],
                        ['value' => \App\Support\Fmt::fa($stats['outOfStock']), 'label' => 'ناموجود'],
                        ['value' => \App\Support\Fmt::fa($stats['categories']), 'label' => 'دسته'],
                    ] as $st)
                        <div class="rounded-2xl bg-slate-50 p-2 text-center dark:bg-slate-800/60">
                            <div class="text-sm font-black">{{ $st['value'] }}</div>
                            <div class="mt-0.5 text-[9px] font-semibold text-slate-400">{{ $st['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ───────── Category management entry ───────── --}}
        <a href="{{ route('seller.categories') }}" wire:navigate
           class="mt-3 flex items-center justify-between rounded-2xl border border-dashed border-primary-300 bg-primary-50/50 px-4 py-2.5 text-xs font-bold text-primary-700 transition hover:border-primary-400 hover:bg-primary-50 active:scale-[.99] dark:border-primary-800 dark:bg-primary-950/30 dark:text-primary-300">
            <span class="flex items-center gap-2">
                <x-icon name="tag" class="size-4" />
                مدیریت دسته‌بندی‌ها
            </span>
            <span class="flex items-center gap-1 text-[11px] font-semibold text-primary-500 dark:text-primary-400">
                {{ \App\Support\Fmt::fa($categories->count()) }} دسته
                <x-icon name="chevron-left" class="size-3.5" />
            </span>
        </a>

        {{-- ───────── Variation attributes entry ───────── --}}
        <a href="{{ route('seller.shop.attributes') }}" wire:navigate
           class="mt-2 flex items-center justify-between rounded-2xl border border-dashed border-fuchsia-300 bg-gradient-to-l from-fuchsia-50/70 to-orange-50/50 px-4 py-2.5 text-xs font-bold text-fuchsia-700 transition hover:border-fuchsia-400 active:scale-[.99] dark:border-fuchsia-800 dark:from-fuchsia-950/30 dark:to-orange-950/20 dark:text-fuchsia-300">
            <span class="flex items-center gap-2">
                <x-icon name="layers" class="size-4" />
                ویژگی‌ها و تنوع‌ها (رنگ، سایز، گارانتی و ...)
            </span>
            <x-icon name="chevron-left" class="size-3.5 opacity-60" />
        </a>

        {{-- ───────── Filters ───────── --}}
        <div class="sticky top-[65px] z-10 -mx-4 mt-3 bg-slate-50/95 px-4 py-3 backdrop-blur dark:bg-slate-950/95">
            {{-- search --}}
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجو در محصولات..."
                       class="w-full rounded-2xl border border-slate-200 bg-white py-2.5 pl-4 pr-10 text-xs font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-900">
                @if ($search)
                    <button type="button" wire:click="$set('search', '')" aria-label="پاک کردن جستجو"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <x-icon name="x" class="size-4" />
                    </button>
                @endif
            </div>

            {{-- category chips (horizontal scroll) --}}
            <div class="scroll-slim -mx-4 mt-2.5 flex gap-2 overflow-x-auto px-4 pb-1">
                <button type="button" wire:click="$set('categoryFilter', 0)"
                        class="shrink-0 rounded-full px-3 py-1.5 text-[11px] font-bold transition {{ $categoryFilter === 0 ? 'bg-primary-600 text-white shadow shadow-primary-600/30' : 'border border-slate-200 bg-white text-slate-500 hover:border-primary-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                    همه دسته‌ها
                </button>
                @foreach ($categories as $cat)
                    <button type="button" wire:click="$set('categoryFilter', {{ $cat->id }})"
                            class="shrink-0 rounded-full px-3 py-1.5 text-[11px] font-bold transition {{ $categoryFilter === $cat->id ? 'bg-primary-600 text-white shadow shadow-primary-600/30' : 'border border-slate-200 bg-white text-slate-500 hover:border-primary-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                        {{ $cat->name }}
                        <span class="opacity-60">{{ \App\Support\Fmt::fa($cat->products_count) }}</span>
                    </button>
                @endforeach
            </div>

            {{-- status + stock segmented controls --}}
            <div class="mt-1.5 grid grid-cols-2 gap-2">
                <div class="flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800" role="group" aria-label="فیلتر وضعیت انتشار">
                    @foreach (['all' => 'همه', 'published' => 'منتشرشده', 'draft' => 'پیش‌نویس'] as $key => $label)
                        <button type="button" wire:click="$set('statusFilter', '{{ $key }}')"
                                class="flex-1 rounded-lg py-1.5 text-[10px] font-bold transition {{ $statusFilter === $key ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                <div class="flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800" role="group" aria-label="فیلتر موجودی">
                    @foreach (['all' => 'همه', 'in' => 'موجود', 'out' => 'ناموجود'] as $key => $label)
                        <button type="button" wire:click="$set('stockFilter', '{{ $key }}')"
                                class="flex-1 rounded-lg py-1.5 text-[10px] font-bold transition {{ $stockFilter === $key ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ───────── Product grid (lazy loaded) ───────── --}}
        <div class="mt-1 transition" wire:loading.class="opacity-40 pointer-events-none" wire:target="search, categoryFilter, statusFilter, stockFilter">
            @forelse ($products as $product)
                <div class="group relative mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <a href="{{ route('seller.products.edit', $product) }}" wire:navigate class="block">
                        <div class="flex gap-3 p-3">
                            {{-- thumbnail --}}
                            <div class="relative size-20 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                @if ($product->mainImage)
                                    <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy"
                                         class="size-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <span class="grid size-full place-items-center text-slate-300 dark:text-slate-600">
                                        <x-icon name="image" class="size-7" />
                                    </span>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="truncate text-xs font-extrabold">{{ $product->name }}</div>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-bold {{ $product->status === 'published'
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $product->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}
                                    </span>
                                </div>

                                <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[10px] font-semibold text-slate-400">
                                    <span class="flex items-center gap-1">
                                        <x-icon name="tag" class="size-3" />
                                        {{ $product->category?->name ?? 'بدون دسته' }}
                                    </span>
                                    <span>·</span>
                                    <span class="{{ $product->stock === 0 ? 'font-bold text-rose-500' : ($product->stock <= 3 ? 'font-bold text-amber-500' : '') }}">
                                        {{ $product->stock === 0 ? 'ناموجود' : 'موجودی: '.\App\Support\Fmt::fa($product->stock) }}
                                    </span>
                                    <span>·</span>
                                    <span>{{ \App\Support\Fmt::fa($product->views) }} بازدید</span>
                                </div>

                                <div class="mt-1.5 flex items-baseline gap-1.5">
                                    <span class="text-sm font-black text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::fa($product->price) }}</span>
                                    <span class="text-[10px] font-semibold text-slate-400">تومان</span>
                                    @if ($product->compare_at_price > 0)
                                        <del class="text-[10px] text-slate-400">{{ \App\Support\Fmt::fa($product->compare_at_price) }}</del>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>

                    {{-- quick actions --}}
                    <div class="flex items-center justify-end gap-1 border-t border-slate-100 px-3 py-1.5 dark:border-slate-800">
                        <button type="button" wire:click="togglePublish({{ $product->id }})"
                                class="flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[10px] font-bold transition {{ $product->status === 'published'
                                    ? 'text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800'
                                    : 'text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-950' }}"
                                title="{{ $product->status === 'published' ? 'بردن به پیش‌نویس' : 'انتشار محصول' }}">
                            <x-icon name="{{ $product->status === 'published' ? 'eye-off' : 'eye' }}" class="size-3.5" />
                            {{ $product->status === 'published' ? 'لغو انتشار' : 'انتشار' }}
                        </button>
                        <a href="{{ route('seller.products.edit', $product) }}" wire:navigate
                           class="flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[10px] font-bold text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800">
                            <x-icon name="edit" class="size-3.5" />
                            ویرایش
                        </a>
                        <button type="button" wire:click="removeProduct({{ $product->id }})" wire:confirm="محصول «{{ $product->name }}» حذف شود؟ این عمل قابل بازگشت نیست."
                                class="flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[10px] font-bold text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-950/40">
                            <x-icon name="trash" class="size-3.5" />
                            حذف
                        </button>
                    </div>
                </div>
            @empty
                {{-- empty state --}}
                <div class="mt-4 flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-300 py-12 text-center dark:border-slate-700">
                    <span class="grid size-16 place-items-center rounded-2xl bg-primary-50 text-primary-400 dark:bg-primary-950">
                        <x-icon name="{{ ($search !== '' || $categoryFilter !== 0 || $statusFilter !== 'all' || $stockFilter !== 'all') ? 'search' : 'package' }}" class="size-8" />
                    </span>
                    <p class="text-sm font-extrabold">
                        {{ ($search !== '' || $categoryFilter !== 0 || $statusFilter !== 'all' || $stockFilter !== 'all') ? 'محصولی با این فیلترها پیدا نشد' : 'هنوز محصولی ندارید' }}
                    </p>
                    @if ($search !== '' || $categoryFilter !== 0 || $statusFilter !== 'all' || $stockFilter !== 'all')
                        <button type="button" wire:click="resetFilters"
                                class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                            <x-icon name="rotate" class="size-3.5" />
                            حذف فیلترها
                        </button>
                    @else
                        <p class="max-w-[240px] text-xs leading-6 text-slate-400">اولین محصولت را اضافه کن تا فروشگاهت زنده شود 🚀</p>
                    @endif
                </div>
            @endforelse

            {{-- lazy-load sentinel (IntersectionObserver → shop-load-more) --}}
            <div
                x-data="{}"
                x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
                class="{{ $hasMore ? 'mt-4 flex justify-center py-2' : 'hidden' }}"
                wire:loading.delay.class="opacity-0" wire:loading.delay.remove.class="opacity-100" wire:target="loadMore"
                aria-hidden="true">
                <span class="inline-block size-6 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"></span>
            </div>
        </div>

        <div class="h-24"></div>
    </div>

    {{-- ───────── Floating "Add product" FAB ───────── --}}
    <div class="pointer-events-none fixed inset-x-0 bottom-[5.5rem] z-40 mx-auto w-full max-w-[430px] px-5">
        <div class="flex justify-end">
            <a href="{{ route('seller.products.create') }}" wire:navigate
               class="pointer-events-auto flex items-center gap-2 rounded-2xl bg-primary-600 px-4 py-3 text-xs font-extrabold text-white shadow-xl shadow-primary-600/40 transition hover:bg-primary-700 active:scale-95"
               aria-label="افزودن محصول">
                <x-icon name="plus" class="size-4.5" />
                افزودن محصول
            </a>
        </div>
    </div>
</div>
