<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                <x-icon name="package" class="size-5" />
            </span>
            <div>
                <div class="text-sm font-extrabold leading-5">دسته‌بندی‌ها</div>
                <div class="text-[11px] text-slate-400">مرور محصولات بر اساس دسته</div>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4">
        @if ($openRoot === 0)
            {{-- ── main categories as image cards ── --}}
            <div class="grid grid-cols-3 gap-2.5 sm:grid-cols-4">
                <button type="button" wire:click="select(0)"
                        class="flex flex-col items-center gap-1.5 rounded-2xl border-2 p-2 transition {{ $selected === 0 ? 'border-primary-500 bg-primary-50/60 dark:bg-primary-950/30' : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900' }}">
                    <span class="grid size-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                        <x-icon name="layout-grid" class="size-5" />
                    </span>
                    <span class="text-center text-[10px] font-extrabold leading-4 text-slate-600 dark:text-slate-300">همه محصولات</span>
                </button>
                @foreach ($roots as $root)
                    <button type="button" wire:click="select({{ $root->id }})" wire:key="root-{{ $root->id }}"
                            class="flex flex-col items-center gap-1.5 rounded-2xl border-2 p-2 transition {{ $selected === $root->id ? 'border-primary-500 bg-primary-50/60 dark:bg-primary-950/30' : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900' }}">
                        <span class="grid size-12 place-items-center overflow-hidden rounded-2xl bg-slate-100 dark:bg-slate-800">
                            @if ($root->image)
                                <img src="{{ $root->image }}" alt="{{ $root->name }}" loading="lazy" class="size-12 object-contain p-1">
                            @else
                                <x-icon name="tag" class="size-5 text-slate-400" />
                            @endif
                        </span>
                        <span class="text-center text-[10px] font-extrabold leading-4 text-slate-600 dark:text-slate-300">{{ $root->name }}</span>
                        <span class="text-[9px] font-bold text-slate-400">{{ \App\Support\Fmt::toFa($root->products_total) }} محصول</span>
                    </button>
                @endforeach
            </div>
        @else
            {{-- ── inside a main category: breadcrumb + sub chips ── --}}
            <button type="button" wire:click="backToRoots"
                    class="mb-3 inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[10.5px] font-bold text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                <x-icon name="chevron-right" class="size-3.5" />
                همه دسته‌ها
            </button>

            <div class="scroll-slim flex gap-2 overflow-x-auto pb-1">
                <button type="button" wire:click="select({{ $openRoot }})"
                        class="shrink-0 rounded-full px-3.5 py-2 text-[11px] font-extrabold transition {{ $selected === $openRoot ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'border border-slate-200 bg-white text-slate-600 hover:border-primary-300 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300' }}">
                    همه
                </button>
                @foreach ($subcategories as $sub)
                    <button type="button" wire:click="select({{ $sub->id }})" wire:key="sub-{{ $sub->id }}"
                            class="shrink-0 rounded-full px-3.5 py-2 text-[11px] font-bold transition {{ $selected === $sub->id ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'border border-slate-200 bg-white text-slate-600 hover:border-primary-300 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300' }}">
                        {{ $sub->name }}
                        <span class="mr-1 text-[9px] opacity-70">({{ \App\Support\Fmt::toFa($sub->products_total) }})</span>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ── products ── --}}
        <div class="mt-5 flex items-center justify-between">
            <h3 class="text-sm font-extrabold">{{ $selectedName }}</h3>
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
                    <a href="{{ $product->publicUrl() }}" wire:navigate class="block">
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
                    <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">محصولی در این دسته وجود ندارد.</p>
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

        <div class="pb-2"></div>
    </div>
</div>
