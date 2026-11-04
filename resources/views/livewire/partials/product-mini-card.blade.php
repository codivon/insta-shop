{{-- ───────── Small product card (category page sections) ─────────
    Expects: $product, $key (unique wire:key) — $favoriteIds rides the
    parent scope so the heart state renders without extra queries. --}}
@php
    $isFav = in_array($product->id, $favoriteIds);
    $discount = $product->compare_at_price > 0 && $product->compare_at_price > $product->price
        ? (int) round(100 - ($product->price / $product->compare_at_price * 100))
        : 0;
    $sold = (int) ($product->paid_sales ?? 0);
@endphp
<div wire:key="{{ $key }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
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
