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
        <label class="mt-2.5 flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-sm focus-within:border-primary-400 dark:border-slate-800 dark:bg-slate-900">
            <x-icon name="search" class="size-4 shrink-0 text-slate-400" />
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجو در {{ $category->name }}…"
                   class="w-full bg-transparent text-xs outline-none placeholder:text-slate-400"
                   aria-label="جستجوی محصول در این دسته‌بندی">
            @if (filled($search))
                <button type="button" wire:click="$set('search', '')" aria-label="پاک کردن جستجو"
                        class="shrink-0 text-slate-400 transition hover:text-rose-500">
                    <x-icon name="x" class="size-4" />
                </button>
            @endif
        </label>
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

        @if (filled($search))
            {{-- ───────── Search results replace the showcases ───────── --}}
            <div class="mt-5 flex items-center justify-between">
                <h2 class="flex items-center gap-1.5 text-sm font-extrabold">
                    <x-icon name="search" class="size-4 text-primary-600 dark:text-primary-400" />
                    نتایج جستجو
                </h2>
                <span class="text-[11px] font-semibold text-slate-400">{{ \App\Support\Fmt::toFa($total) }} محصول</span>
            </div>

            @if ($total > 0)
                <div class="mt-3 grid grid-cols-2 gap-3">
                    @foreach ($products as $product)
                        @include('livewire.partials.product-mini-card', ['key' => 'found-'.$product->id])
                    @endforeach
                </div>

                @if ($hasMore && $products->isNotEmpty())
                    <div x-data
                         x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
                         class="mt-6 grid place-items-center py-2"
                         wire:loading.delay.class="opacity-0" wire:loading.delay.remove.class="opacity-100" wire:target="loadMore">
                        <span class="inline-block size-6 animate-spin rounded-full border-2 border-primary-600 border-t-transparent"></span>
                    </div>
                @endif
            @else
                <div class="mt-3 rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                    <x-icon name="search" class="mx-auto size-8 text-slate-300 dark:text-slate-600" />
                    <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">چیزی مطابق «{{ trim($search) }}» در این دسته‌بندی پیدا نشد.</p>
                </div>
            @endif
        @elseif ($total > 8)
            {{-- ───────── Showcase ۱: best-sellers of the branch ───────── --}}
            <div class="mt-5 flex items-center justify-between">
                <h2 class="flex items-center gap-1.5 text-sm font-extrabold">
                    <x-icon name="flame" class="size-4 text-rose-500" />
                    پرفروش‌ترین‌ها
                </h2>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3">
                @foreach ($bestSellers as $product)
                    @include('livewire.partials.product-mini-card', ['key' => 'best-'.$product->id])
                @endforeach
            </div>

            {{-- ───────── Showcase ۲: freshest of the branch ───────── --}}
            <div class="mt-6 flex items-center justify-between">
                <h2 class="flex items-center gap-1.5 text-sm font-extrabold">
                    <x-icon name="clock" class="size-4 text-primary-600 dark:text-primary-400" />
                    جدیدترین‌ها
                </h2>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3">
                @foreach ($newest as $product)
                    @include('livewire.partials.product-mini-card', ['key' => 'new-'.$product->id])
                @endforeach
            </div>

            {{-- ───────── The whole branch feed, infinite scroll ───────── --}}
            <div class="mt-6 flex items-center justify-between">
                <h2 class="flex items-center gap-1.5 text-sm font-extrabold">
                    <x-icon name="layout-grid" class="size-4 text-slate-500 dark:text-slate-400" />
                    همهٔ محصولات
                </h2>
                <span class="text-[11px] font-semibold text-slate-400">{{ \App\Support\Fmt::toFa($total) }} محصول</span>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3">
                @foreach ($products as $product)
                    @include('livewire.partials.product-mini-card', ['key' => 'prod-'.$product->id])
                @endforeach
            </div>

            @if ($hasMore && $products->isNotEmpty())
                <div x-data
                     x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
                     class="mt-6 grid place-items-center py-2"
                     wire:loading.delay.class="opacity-0" wire:loading.delay.remove.class="opacity-100" wire:target="loadMore">
                    <span class="inline-block size-6 animate-spin rounded-full border-2 border-primary-600 border-t-transparent"></span>
                </div>
            @endif
        @elseif ($total > 0)
            {{-- ───────── Small branch: one sales-ordered section ───────── --}}
            <div class="mt-5 flex items-center justify-between">
                <h2 class="flex items-center gap-1.5 text-sm font-extrabold">
                    <x-icon name="flame" class="size-4 text-rose-500" />
                    پرفروش‌ترین‌ها
                </h2>
                <span class="text-[11px] font-semibold text-slate-400">{{ \App\Support\Fmt::toFa($total) }} محصول</span>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3">
                @foreach ($bestSellers as $product)
                    @include('livewire.partials.product-mini-card', ['key' => 'best-'.$product->id])
                @endforeach
            </div>
        @else
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                <x-icon name="package" class="mx-auto size-8 text-slate-300 dark:text-slate-600" />
                <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">هنوز محصولی در این دسته‌بندی عرضه نشده است.</p>
            </div>
        @endif
    </div>
</div>
