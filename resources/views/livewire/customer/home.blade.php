<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                    <x-icon name="store" class="size-5" />
                </span>
                <div>
                    <div class="text-sm font-extrabold leading-5">InstaShop</div>
                    <div class="text-[11px] text-slate-400">{{ \App\Support\Fmt::toFa($shops->count()) }} فروشگاه فعال اینستاگرامی</div>
                </div>
            </div>
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>

        {{-- search + sort --}}
        <div class="mt-3 flex items-center gap-2">
            <label class="flex flex-1 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-sm focus-within:border-primary-400 dark:border-slate-800 dark:bg-slate-900">
                <x-icon name="search" class="size-4 shrink-0 text-slate-400" />
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجوی محصول…"
                       class="w-full bg-transparent text-xs outline-none placeholder:text-slate-400">
                <button type="button" wire:click="$set('search', '')" wire:loading.delay wire:target="search"
                        class="hidden text-slate-400" aria-hidden="true"></button>
            </label>
            <select wire:model.live="sort" aria-label="مرتب‌سازی"
                    class="rounded-2xl border border-slate-200 bg-white px-2.5 py-2.5 text-[11px] font-semibold text-slate-600 shadow-sm outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                <option value="newest">جدیدترین</option>
                <option value="popular">پربازدید</option>
                <option value="cheap">ارزان‌ترین</option>
                <option value="expensive">گران‌ترین</option>
            </select>
        </div>
    </div>

    <div class="px-4 pt-4">
        {{-- ── banner ── --}}
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

        {{-- ── public shops ── --}}
        <div id="shops" class="mt-6 flex items-center justify-between">
            <h3 class="text-sm font-extrabold">فروشگاه‌های فعال</h3>
        </div>

        <div class="scroll-slim mt-3 flex gap-3 overflow-x-auto pb-1">
            @forelse ($shops as $shop)
                <a href="{{ route('shop.store.show', $shop->slug) }}" wire:navigate
                   class="group w-36 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <div class="grid h-16 place-items-center overflow-hidden bg-slate-100 dark:bg-slate-800">
                        @if ($shop->banner_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->banner_path) }}" alt="بنر {{ $shop->name }}" loading="lazy" class="h-full w-full object-cover">
                        @elseif ($shop->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->logo_path) }}" alt="لوگوی {{ $shop->name }}" loading="lazy" class="size-10 rounded-xl object-cover">
                        @else
                            <span class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-extrabold text-white">
                                {{ mb_substr($shop->name, 0, 1) }}
                            </span>
                        @endif
                    </div>
                    <div class="p-2.5">
                        <div class="truncate text-xs font-extrabold">{{ $shop->name }}</div>
                        <div class="mt-1 flex items-center justify-between text-[10px] text-slate-400">
                            <span>{{ \App\Support\Fmt::toFa($shop->products_count) }} محصول</span>
                            @if ($shop->instagram_username)
                                <span class="inline-flex items-center gap-0.5"><x-icon name="instagram" class="size-3" />{{ $shop->instagram_username }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="w-full rounded-2xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-400 dark:border-slate-700">
                    فعلاً فروشگاه فعالی وجود ندارد.
                </div>
            @endforelse
        </div>

        {{-- ── product feed ── --}}
        <div class="mt-6 flex items-center justify-between">
            <h3 class="text-sm font-extrabold">
                {{ $search !== '' ? 'نتیجه جستجو' : 'محصولات' }}
            </h3>
            <span class="text-[11px] font-semibold text-slate-400">
                {{ \App\Support\Fmt::toFa($products->count()) }} محصول
            </span>
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
                    <a href="{{ route('shop.product.show', $product->id) }}" wire:navigate class="block">
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
                            @if ($product->stock < 1)
                                <span class="absolute inset-0 grid place-items-center bg-slate-900/50 text-[11px] font-bold text-white">ناموجود</span>
                            @endif
                        </div>
                    </a>
                    <div class="space-y-1.5 p-2.5">
                        <a href="{{ route('shop.product.show', $product->id) }}" wire:navigate class="line-clamp-2 block min-h-8 text-xs font-bold leading-5 hover:text-primary-600 dark:hover:text-primary-400">
                            {{ $product->name }}
                        </a>
                        <div class="truncate text-[10px] text-slate-400">از {{ $product->shop?->name }}</div>
                        <div class="flex items-end justify-between pt-1">
                            <div>
                                @if ($discount > 0)
                                    <div class="text-[9px] text-slate-400 line-through">{{ \App\Support\Fmt::fa($product->compare_at_price) }}</div>
                                @endif
                                <div class="text-xs font-extrabold text-primary-600 dark:text-primary-400">
                                    {{ \App\Support\Fmt::moneyShort($product->price) }}
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="toggleFavorite({{ $product->id }})"
                                        class="grid size-7 place-items-center rounded-lg border border-slate-200 transition active:scale-90 dark:border-slate-700 {{ $isFav ? 'text-rose-500' : 'text-slate-400 hover:text-rose-400' }}"
                                        aria-label="{{ $isFav ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}">
                                    <x-icon name="heart" class="size-3.5 {{ $isFav ? 'fill-current' : '' }}" />
                                </button>
                                <button type="button" wire:click="addToCart({{ $product->id }})" @disabled($product->stock < 1)
                                        class="grid size-7 place-items-center rounded-lg bg-primary-600 text-white shadow-md shadow-primary-600/30 transition active:scale-90 disabled:opacity-40 disabled:shadow-none"
                                        aria-label="افزودن به سبد خرید">
                                    <x-icon name="plus" class="size-3.5" />
                                </button>
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

        {{-- lazy-load sentinel (IntersectionObserver → home-load-more) --}}
        @if ($hasMore && $products->isNotEmpty())
            <div x-data
                 x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
                 class="mt-6 grid place-items-center py-2"
                 wire:loading.delay.class="opacity-0" wire:loading.delay.remove.class="opacity-100" wire:target="loadMore">
                <span class="inline-block size-6 animate-spin rounded-full border-2 border-primary-600 border-t-transparent"></span>
            </div>
        @endif

        <div class="pb-2"></div>
    </div>

    {{-- toast --}}
    <div x-data="{ show: false, msg: '' }"
         @home-toast.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2600)"
         x-show="show" x-transition.opacity
         class="fixed bottom-24 left-1/2 z-40 w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95"
         x-cloak>
        <span x-text="msg"></span>
    </div>
</div>
