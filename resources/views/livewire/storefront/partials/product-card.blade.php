{{-- ─────────────────────────────────────────────────────────
  Storefront product card
  Variants: «slide» (sliders) · «grid» (main grid) · «special» (فروش ویژه
  section — rose glass card with a live per-product countdown chip).
  Expects: $product, $isFav, $prefix (wire:key prefix), $variant
──────────────────────────────────────────────────────────── --}}
@php
    $discount = $product->compare_at_price > 0 && $product->compare_at_price > $product->price
        ? (int) round(100 - ($product->price / $product->compare_at_price * 100))
        : 0;
    $specialActive = $product->is_special && $product->isSpecialActive();
@endphp

<div wire:key="{{ $prefix }}-card-{{ $product->id }}"
     class="group/card {{ $variant === 'grid' ? '' : 'w-36 shrink-0 snap-start sm:w-44' }}
            {{ $variant === 'special' ? 'rounded-2xl border border-white/25 bg-white/95 shadow-lg backdrop-blur dark:border-white/10 dark:bg-slate-900/95' : 'rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900' }}
            flex flex-col overflow-hidden transition duration-200
            hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-900/5">

    {{-- image --}}
    <a href="{{ route('shop.product.show', $product->id) }}" wire:navigate
       class="relative block {{ $variant === 'grid' ? 'aspect-square' : 'h-28 sm:h-36' }} overflow-hidden {{ $variant === 'special' ? 'bg-rose-50 dark:bg-slate-800' : 'bg-slate-100 dark:bg-slate-800' }}">
        @if ($product->mainImage)
            <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy"
                 class="size-full object-cover transition duration-300 group-hover/card:scale-[1.04]">
        @else
            <span class="grid size-full place-items-center">
                <x-icon name="package" class="size-9 text-slate-300 dark:text-slate-600" />
            </span>
        @endif

        @if ($specialActive)
            <span class="absolute right-2 top-2 flex items-center gap-1 rounded-full bg-gradient-to-l from-rose-600 to-orange-500 px-2 py-0.5 text-[9px] font-black text-white shadow-md shadow-rose-500/40">
                <x-icon name="flame" class="size-2.5" />
                فروش ویژه
            </span>
        @elseif ($discount > 0)
            <span class="absolute right-2 top-2 rounded-full bg-gradient-to-l from-rose-500 to-rose-600 px-2 py-0.5 text-[9px] font-black text-white shadow-md shadow-rose-500/30">
                ٪{{ \App\Support\Fmt::toFa($discount) }}
            </span>
        @endif

        @if ($variant === 'special' && $product->special_ends_at)
            <span x-data="sfCountdown({{ $product->special_ends_at->timestamp * 1000 }})"
                  class="absolute bottom-1.5 left-1.5 flex items-center gap-1 rounded-full bg-slate-950/75 px-2 py-0.5 font-mono text-[8.5px] font-black text-white backdrop-blur" dir="ltr">
                <x-icon name="timer" class="size-2.5 text-amber-300" />
                <span x-show="!over" x-text="d + ':' + pad(h) + ':' + pad(m) + ':' + pad(s)">…</span>
                <span x-show="over" x-cloak>پایان یافت</span>
            </span>
        @endif

        @if ($product->stock < 1)
            <span class="absolute inset-0 grid place-items-center bg-slate-950/55 text-[11px] font-extrabold text-white backdrop-blur-[2px]">
                ناموجود
            </span>
        @endif
    </a>

    {{-- body --}}
    <div class="flex flex-1 flex-col gap-1.5 p-2.5 sm:p-3">
        @if ($product->category)
            <span class="truncate text-[9px] font-bold {{ $variant === 'special' ? 'text-rose-400' : 'text-slate-400' }}">{{ $product->category->name }}</span>
        @endif

        <a href="{{ route('shop.product.show', $product->id) }}" wire:navigate
           class="line-clamp-2 min-h-[2rem] text-[11px] font-bold leading-5 transition hover:text-primary-600 sm:text-xs dark:hover:text-primary-400">
            {{ $product->name }}
        </a>

        <div class="mt-auto flex items-end justify-between gap-1.5 pt-1">
            <div class="min-w-0">
                @if ($discount > 0)
                    <div class="text-[9px] font-semibold text-slate-400 line-through">{{ \App\Support\Fmt::fa($product->compare_at_price) }}</div>
                @endif
                <div class="truncate text-xs font-extrabold text-primary-600 sm:text-sm dark:text-primary-400">
                    {{ \App\Support\Fmt::fa($product->price) }}
                    <span class="text-[8.5px] font-bold text-slate-400">تومان</span>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-1">
                <button type="button" wire:click="toggleFavorite({{ $product->id }})"
                        class="grid size-7 place-items-center rounded-lg border border-slate-200 transition active:scale-90 dark:border-slate-700 {{ $isFav ? 'text-rose-500' : 'text-slate-400 hover:text-rose-400' }}"
                        aria-label="{{ $isFav ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}">
                    <x-icon name="heart" class="size-3.5 {{ $isFav ? 'fill-current' : '' }}" />
                </button>
                <button type="button" wire:click="addToCart({{ $product->id }})" @disabled($product->stock < 1)
                        class="grid size-7 place-items-center rounded-lg bg-primary-600 text-white shadow-md shadow-primary-600/30 transition hover:bg-primary-700 active:scale-90 disabled:opacity-40 disabled:shadow-none"
                        aria-label="افزودن به سبد خرید">
                    <x-icon name="plus" class="size-3.5" />
                </button>
            </div>
        </div>
    </div>
</div>
