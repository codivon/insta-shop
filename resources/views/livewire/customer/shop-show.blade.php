<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center justify-between gap-2">
            <a href="{{ route('shop.home') }}" wire:navigate
               class="grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800"
               aria-label="بازگشت به خانه">
                <x-icon name="arrow-left" class="size-5" />
            </a>
            <div class="truncate text-sm font-extrabold">{{ $shop->name }}</div>
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    {{-- ── shop hero ── --}}
    <div class="relative">
        <div class="h-36 overflow-hidden bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500">
            @if ($shop->banner_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->banner_path) }}" alt="بنر {{ $shop->name }}" class="h-full w-full object-cover">
            @endif
        </div>

        <div class="px-4">
            <div class="-mt-10 flex items-end gap-3">
                <div class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-2xl border-4 border-slate-50 bg-white shadow-lg dark:border-slate-950 dark:bg-slate-900">
                    @if ($shop->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->logo_path) }}" alt="لوگوی {{ $shop->name }}" class="size-full object-cover">
                    @else
                        <span class="text-xl font-extrabold text-primary-600 dark:text-primary-400">{{ mb_substr($shop->name, 0, 1) }}</span>
                    @endif
                </div>
                <div class="min-w-0 flex-1 pb-1">
                    <h1 class="truncate text-base font-extrabold">{{ $shop->name }}</h1>
                    <div class="mt-1 flex items-center gap-2 text-[10px] text-slate-400">
                        @if ($shop->instagram_username)
                            <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-l from-fuchsia-500/10 to-amber-500/10 px-2 py-0.5 font-bold text-fuchsia-600 dark:text-fuchsia-400">
                                <x-icon name="instagram" class="size-3" />
                                {{ $shop->instagram_username }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            فروشگاه فعال و تأییدشده
                        </span>
                    </div>
                </div>
            </div>

            @if ($shop->about)
                <p class="mt-3 text-xs leading-6 text-slate-500 dark:text-slate-400">{{ $shop->about }}</p>
            @endif

            {{-- stats + contact --}}
            <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-2xl border border-slate-200 bg-white p-2.5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-sm font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::toFa($stats['products']) }}</div>
                    <div class="mt-0.5 text-[9px] text-slate-400">محصول</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-2.5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-sm font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::toFa($stats['views']) }}</div>
                    <div class="mt-0.5 text-[9px] text-slate-400">بازدید</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-2.5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-sm font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::toFa($shop->categories->count()) }}</div>
                    <div class="mt-0.5 text-[9px] text-slate-400">دسته‌بندی</div>
                </div>
            </div>

            @if ($shop->address)
                <div class="mt-3 flex items-start gap-2 rounded-2xl bg-slate-100/80 p-3 text-[11px] leading-5 text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                    <x-icon name="store" class="mt-0.5 size-3.5 shrink-0" />
                    <span>{{ $shop->address }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ── search ── --}}
    <div class="px-4 pt-4">
        <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-sm focus-within:border-primary-400 dark:border-slate-800 dark:bg-slate-900">
            <x-icon name="search" class="size-4 shrink-0 text-slate-400" />
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجو در {{ $shop->name }}…"
                   class="w-full bg-transparent text-xs outline-none placeholder:text-slate-400">
        </label>
    </div>

    {{-- ── products ── --}}
    <div class="px-4 pt-4">
        <div class="mt-1 flex items-center justify-between">
            <h3 class="text-sm font-extrabold">محصولات فروشگاه</h3>
            <span class="text-[11px] font-semibold text-slate-400">{{ \App\Support\Fmt::toFa($products->count()) }} محصول</span>
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
                                <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover">
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
                        @if ($product->category)
                            <div class="truncate text-[10px] text-slate-400">{{ $product->category->name }}</div>
                        @endif
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
                    <x-icon name="package" class="mx-auto size-8 text-slate-300 dark:text-slate-600" />
                    <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">
                        @if ($search !== '')
                            محصولی برای «{{ $search }}» در این فروشگاه پیدا نشد.
                        @else
                            این فروشگاه هنوز محصولی منتشر نکرده است.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- lazy-load sentinel --}}
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
         @store-toast.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2600)"
         x-show="show" x-transition.opacity
         class="fixed bottom-24 left-1/2 z-40 w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95"
         x-cloak>
        <span x-text="msg"></span>
    </div>
</div>
