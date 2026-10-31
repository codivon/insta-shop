<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center justify-between gap-2">
            <a href="{{ url()->previous() ?: route('shop.home') }}"
               class="grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800"
               aria-label="بازگشت">
                <x-icon name="arrow-left" class="size-5" />
            </a>
            <div class="truncate text-xs font-bold text-slate-500 dark:text-slate-400">{{ $product->shop?->name }}</div>
            <button type="button" wire:click="toggleFavorite"
                    class="grid size-10 place-items-center rounded-xl transition hover:bg-slate-100 dark:hover:bg-slate-800 {{ $isFavorite ? 'text-rose-500' : 'text-slate-400 hover:text-rose-400' }}"
                    aria-label="{{ $isFavorite ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}">
                <x-icon name="heart" class="size-5 {{ $isFavorite ? 'fill-current' : '' }}" />
            </button>
        </div>
    </div>

    {{-- ── gallery ── --}}
    <div class="px-4 pt-4">
        <div class="grid h-64 place-items-center overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @if ($activeImg)
                <img src="{{ $activeImg['url'] }}" alt="{{ $product->name }}" class="size-full object-contain">
            @else
                <x-icon name="package" class="size-16 text-slate-300 dark:text-slate-600" />
            @endif
        </div>

        @if (count($gallery) > 1)
            <div class="scroll-slim mt-3 flex gap-2 overflow-x-auto pb-1">
                @foreach ($gallery as $img)
                    <button type="button" wire:click="selectImage({{ $img['id'] }})"
                            class="size-16 shrink-0 overflow-hidden rounded-xl border-2 transition {{ $activeImg && $activeImg['id'] === $img['id'] ? 'border-primary-500' : 'border-transparent opacity-70 hover:opacity-100' }}"
                            aria-label="تصویر {{ \App\Support\Fmt::toFa($loop->iteration) }}">
                        <img src="{{ $img['url'] }}" alt="" class="size-full object-cover">
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── info ── --}}
    <div class="px-4 pt-4">
        <h1 class="text-base font-extrabold leading-7">{{ $product->name }}</h1>

        <div class="mt-2 flex flex-wrap items-center gap-2 text-[10px]">
            @if ($product->category)
                <span class="rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">{{ $product->category->name }}</span>
            @endif
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                <x-icon name="eye" class="size-3" /> {{ \App\Support\Fmt::toFa($product->views) }} بازدید
            </span>
            <span class="rounded-full px-2.5 py-1 font-bold {{ $product->stock > 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                {{ $product->stock > 0 ? \App\Support\Fmt::toFa($product->stock).' عدد موجود' : 'ناموجود' }}
            </span>
        </div>

        <a href="{{ route('shop.store.show', $product->shop->slug) }}" wire:navigate
           class="mt-3 flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-primary-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-800">
            <div class="flex items-center gap-2.5">
                <div class="grid size-9 place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                    @if ($product->shop->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->shop->logo_path) }}" alt="" class="size-full object-cover">
                    @else
                        <span class="text-sm font-extrabold text-primary-600 dark:text-primary-400">{{ mb_substr($product->shop->name, 0, 1) }}</span>
                    @endif
                </div>
                <div>
                    <div class="text-xs font-extrabold">{{ $product->shop->name }}</div>
                    <div class="text-[10px] text-slate-400">مشاهده فروشگاه</div>
                </div>
            </div>
            <x-icon name="chevron-left" class="size-4 text-slate-400" />
        </a>

        {{-- price + add to cart --}}
        <div class="mt-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-end justify-between">
                <div>
                    @if ($discount > 0)
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[9px] font-bold text-white">٪{{ \App\Support\Fmt::toFa($discount) }} تخفیف</span>
                            <span class="text-[11px] text-slate-400 line-through">{{ \App\Support\Fmt::fa($product->compare_at_price) }}</span>
                        </div>
                    @endif
                    <div class="mt-1 text-lg font-extrabold text-primary-600 dark:text-primary-400">
                        {{ \App\Support\Fmt::fa($product->price) }}
                        <span class="text-[10px] font-semibold text-slate-400">تومان</span>
                    </div>
                </div>

                @if ($product->stock > 0)
                    <div class="flex items-center gap-1 rounded-xl border border-slate-200 p-1 dark:border-slate-700">
                        <button type="button" wire:click="incrementQty" @disabled($qty >= $product->stock)
                                class="grid size-7 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 disabled:opacity-30 dark:hover:bg-slate-800"
                                aria-label="افزایش تعداد"><x-icon name="plus" class="size-3.5" /></button>
                        <span class="w-8 text-center text-xs font-extrabold">{{ \App\Support\Fmt::toFa($qty) }}</span>
                        <button type="button" wire:click="decrementQty" @disabled($qty <= 1)
                                class="grid size-7 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 disabled:opacity-30 dark:hover:bg-slate-800"
                                aria-label="کاهش تعداد"><x-icon name="x" class="size-3.5" /></button>
                    </div>
                @endif
            </div>

            <button type="button" wire:click="addToCart" @disabled($product->stock < 1) wire:loading.attr="disabled" wire:target="addToCart"
                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-600 py-3 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none">
                <x-icon name="cart" class="size-4" />
                {{ $product->stock > 0 ? 'افزودن به سبد خرید' : 'ناموجود' }}
            </button>
        </div>

        @if ($product->description)
            <div class="mt-4 rounded-3xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-xs font-extrabold">توضیحات</h3>
                <p class="mt-2 whitespace-pre-line text-xs leading-6 text-slate-500 dark:text-slate-400">{{ $product->description }}</p>
            </div>
        @endif

        <div class="pb-2"></div>
    </div>

    {{-- toast --}}
    <div x-data="{ show: false, msg: '' }"
         @product-toast.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2600)"
         x-show="show" x-transition.opacity
         class="fixed bottom-24 left-1/2 z-40 w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95"
         x-cloak>
        <span x-text="msg"></span>
    </div>
</div>
