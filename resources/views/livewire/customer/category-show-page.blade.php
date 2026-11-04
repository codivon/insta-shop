<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('shop.categories') }}" wire:navigate
               class="grid size-10 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-rose-300 hover:text-rose-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
               aria-label="بازگشت به دسته‌بندی‌ها">
                <x-icon name="arrow-right" class="size-4.5" />
            </a>
            <div class="min-w-0">
                <div class="truncate text-sm font-extrabold leading-5">{{ $category->name }}</div>
                <div class="text-[11px] text-slate-400">{{ $category->pathLabel() }}</div>
            </div>
        </div>
    </div>

    <div class="px-4 pb-6 pt-3">
        {{-- ───────── Breadcrumb ───────── --}}
        <nav class="flex flex-wrap items-center gap-x-1 gap-y-0.5 text-[10.5px] font-semibold text-slate-400" aria-label="مسیر دسته‌بندی">
            <a href="{{ route('shop.home') }}" wire:navigate class="transition hover:text-rose-500">خانه</a>
            @foreach ($trail as $node)
                <x-icon name="chevron-left" class="size-3 shrink-0" />
                @if ($loop->last)
                    <span class="font-extrabold text-slate-600 dark:text-slate-300">{{ $node->name }}</span>
                @else
                    <a href="{{ route('shop.categories.show', $node->id) }}" wire:navigate class="transition hover:text-rose-500">{{ $node->name }}</a>
                @endif
            @endforeach
        </nav>

        {{-- ───────── Child categories as image cards ───────── --}}
        @if ($children->isNotEmpty())
            <div class="scroll-slim mt-3 flex gap-2.5 overflow-x-auto overscroll-x-contain pb-1.5">
                @foreach ($children as $child)
                    <a href="{{ route('shop.categories.show', $child->id) }}" wire:navigate wire:key="cat-{{ $child->id }}"
                       class="group w-[88px] shrink-0 text-center">
                        <span class="grid aspect-square w-full place-items-center overflow-hidden rounded-2xl bg-slate-100 p-2 transition group-active:scale-95 dark:bg-slate-800">
                            <img src="{{ $child->image }}" alt="{{ $child->name }}" loading="lazy"
                                 class="max-h-full max-w-full object-contain drop-shadow-sm">
                        </span>
                        <span class="mt-1.5 line-clamp-2 text-[10px] font-bold leading-4 text-slate-600 dark:text-slate-300">{{ $child->name }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- ───────── Best-sellers of the branch ───────── --}}
        <div class="mt-5 flex items-center justify-between">
            <h2 class="flex items-center gap-1.5 text-sm font-extrabold">
                <x-icon name="flame" class="size-4 text-rose-500" />
                پرفروش‌ترین‌ها
            </h2>
            <span class="text-[11px] font-semibold text-slate-400">{{ \App\Support\Fmt::toFa($products->count()) }} محصول</span>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-3">
            @forelse ($products as $product)
                @php
                    $isFav = in_array($product->id, $favoriteIds);
                    $discount = $product->compare_at_price > 0 && $product->compare_at_price > $product->price
                        ? (int) round(100 - ($product->price / $product->compare_at_price * 100))
                        : 0;
                    $sold = (int) ($product->paid_sales ?? 0);
                @endphp
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <a href="{{ $product->publicUrl() }}" wire:navigate class="block">
                        <div class="relative grid h-32 place-items-center overflow-hidden bg-slate-100 dark:bg-slate-800">
                            @if ($product->mainImage)
                                <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover">
                            @else
                                <x-icon name="package" class="size-9 text-slate-300 dark:text-slate-600" />
                            @endif
                            @if ($discount > 0)
                                <span class="absolute right-2 top-2 rounded-full bg-rose-500 px-2 py-0.5 text-[9px] font-bold text-white shadow">٪{{ \App\Support\Fmt::toFa($discount) }}</span>
                            @elseif ($sold > 0)
                                <span class="absolute right-2 top-2 rounded-full bg-white/90 px-2 py-0.5 text-[9px] font-extrabold text-rose-600 shadow">پرفروش</span>
                            @endif
                            @if ($product->stock < 1)
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
                            <div class="text-xs font-extrabold text-primary-600 dark:text-primary-400">
                                {{ \App\Support\Fmt::moneyShort($product->price) }} <span class="text-[9px] font-bold text-slate-400">تومان</span>
                            </div>
                            <button type="button" wire:click="toggleFavorite({{ $product->id }})"
                                    class="grid size-7 place-items-center rounded-lg border border-slate-200 transition active:scale-90 dark:border-slate-700 {{ $isFav ? 'text-rose-500' : 'text-slate-400 hover:text-rose-400' }}"
                                    aria-label="علاقه‌مندی">
                                <x-icon name="heart" class="size-3.5 {{ $isFav ? 'fill-current' : '' }}" />
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                    <x-icon name="package" class="mx-auto size-8 text-slate-300 dark:text-slate-600" />
                    <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">هنوز محصولی در این دسته‌بندی عرضه نشده است.</p>
                </div>
            @endforelse
        </div>

        @if ($hasMore && $products->isNotEmpty())
            <div x-data
                 x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
                 class="mt-6 grid place-items-center py-2"
                 wire:loading.delay.class="opacity-0" wire:loading.delay.remove.class="opacity-100" wire:target="loadMore">
                <span class="inline-block size-6 animate-spin rounded-full border-2 border-primary-600 border-t-transparent"></span>
            </div>
        @endif
    </div>
</div>
