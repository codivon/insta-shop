<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                <x-icon name="heart" class="size-5" />
            </span>
            <div>
                <div class="text-sm font-extrabold leading-5">علاقه‌مندی‌ها</div>
                <div class="text-[11px] text-slate-400">{{ \App\Support\Fmt::toFa($favorites->count()) }} محصول ذخیره‌شده</div>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4">
        @if ($favorites->isEmpty())
            <div class="mt-10 rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-slate-100 dark:bg-slate-800">
                    <x-icon name="heart" class="size-8 text-slate-300 dark:text-slate-600" />
                </span>
                <p class="mt-4 text-sm font-extrabold">هنوز علاقه‌مندی‌ای ندارید</p>
                <p class="mt-1 text-[11px] text-slate-400">روی قلب محصولات بزنید تا اینجا ذخیره شوند.</p>
                <a href="{{ route('shop.home') }}" wire:navigate
                   class="mt-5 inline-block rounded-2xl bg-primary-600 px-6 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-95">
                    مشاهده محصولات
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($favorites as $fav)
                    @php $product = $fav->product; @endphp
                    <div class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <a href="{{ route('shop.product.show', $product->id) }}" wire:navigate
                           class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                            @if ($product->mainImage)
                                <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy" class="size-full object-cover">
                            @else
                                <x-icon name="package" class="size-7 text-slate-300 dark:text-slate-600" />
                            @endif
                        </a>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('shop.product.show', $product->id) }}" wire:navigate
                                   class="line-clamp-2 text-xs font-bold leading-5 hover:text-primary-600 dark:hover:text-primary-400">
                                    {{ $product->name }}
                                </a>
                                <button type="button" wire:click="removeFavorite({{ $product->id }})"
                                        class="grid size-7 shrink-0 place-items-center rounded-lg text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-950/40"
                                        aria-label="حذف از علاقه‌مندی‌ها">
                                    <x-icon name="heart" class="size-3.5 fill-current" />
                                </button>
                            </div>

                            <div class="mt-0.5 truncate text-[10px] text-slate-400">از {{ $product->shop?->name }}</div>

                            <div class="mt-2 flex items-center justify-between">
                                <div class="text-xs font-extrabold text-primary-600 dark:text-primary-400">
                                    {{ \App\Support\Fmt::moneyShort($product->price) }}
                                    @if ($product->stock < 1)
                                        <span class="mr-1 rounded-full bg-rose-50 px-2 py-0.5 text-[9px] font-bold text-rose-500 dark:bg-rose-950/40">ناموجود</span>
                                    @endif
                                </div>
                                @if ($product->hasVariants())
                                    <a href="{{ route('shop.product.show', $product->id) }}" wire:navigate
                                       class="grid size-7 place-items-center rounded-lg bg-primary-600 text-white shadow-md shadow-primary-600/30 transition active:scale-90"
                                       aria-label="مشاهده و انتخاب تنوع" title="انتخاب تنوع در صفحه محصول">
                                        <x-icon name="layers" class="size-3.5" />
                                    </a>
                                @else
                                    <button type="button" wire:click="addToCart({{ $product->id }})" @disabled($product->stock < 1)
                                            class="grid size-7 place-items-center rounded-lg bg-primary-600 text-white shadow-md shadow-primary-600/30 transition active:scale-90 disabled:opacity-40 disabled:shadow-none"
                                            aria-label="افزودن به سبد">
                                        <x-icon name="plus" class="size-3.5" />
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pb-2"></div>
        @endif
    </div>

    {{-- toast --}}
    <div x-data="{ show: false, msg: '' }"
         @fav-toast.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2600)"
         x-show="show" x-transition.opacity
         class="fixed bottom-24 left-1/2 z-40 w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95"
         x-cloak>
        <span x-text="msg"></span>
    </div>
</div>
