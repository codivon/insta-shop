<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="grid size-10 place-items-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                            <x-icon name="cart" class="size-5" />
                        </span>
                        <div>
                            <div class="text-sm font-extrabold leading-5">سبد خرید</div>
                            <div class="text-[11px] text-slate-400">{{ \App\Support\Fmt::toFa($units) }} کالا از {{ \App\Support\Fmt::toFa($validItems->count()) }} محصول</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('shop.orders') }}" wire:navigate
                           class="grid size-10 place-items-center rounded-xl text-slate-400 transition hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-primary-950/40"
                           aria-label="سفارش‌های من" title="سفارش‌های من">
                            <x-icon name="file-text" class="size-4.5" />
                        </a>
                        @if ($items->isNotEmpty())
                            <button type="button" wire:click="clearCart" wire:confirm="کل سبد خرید خالی شود؟"
                                    class="grid size-10 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40"
                                    aria-label="خالی کردن سبد">
                                <x-icon name="trash" class="size-4.5" />
                            </button>
                        @endif
                    </div>
                </div>
    </div>

    <div class="px-4 pt-4">
        @if (session('checkout-error'))
            <div class="mb-3 rounded-2xl border border-rose-200 bg-rose-50/70 p-3 text-[11px] font-bold leading-5 text-rose-600 dark:border-rose-900 dark:bg-rose-950/30 dark:text-rose-400">
                {{ session('checkout-error') }}
            </div>
        @endif

        @if ($items->isEmpty())
            {{-- ── empty cart ── --}}
            <div class="mt-10 rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-slate-100 dark:bg-slate-800">
                    <x-icon name="cart" class="size-8 text-slate-300 dark:text-slate-600" />
                </span>
                <p class="mt-4 text-sm font-extrabold">سبد خرید شما خالی است</p>
                <p class="mt-1 text-[11px] text-slate-400">از فروشگاه‌های فعال دیدن کنید و خرید را شروع کنید.</p>
                <a href="{{ route('shop.home') }}" wire:navigate
                   class="mt-5 inline-block rounded-2xl bg-primary-600 px-6 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-95">
                    رفتن به فروشگاه‌ها
                </a>
            </div>
        @else
            {{-- ── stale (removed/unpublished/out-of-stock) items ── --}}
            @if ($staleItems->isNotEmpty())
                <div class="mb-3 rounded-2xl border border-amber-200 bg-amber-50/70 p-3 text-[11px] leading-5 text-amber-700 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300">
                    <b>{{ \App\Support\Fmt::toFa($staleItems->count()) }} کالا</b> دیگر قابل خرید نیست (حذف‌شده/ناموجود) و در جمع محاسبه نشده است.
                </div>
            @endif

            {{-- ── items ── --}}
            <div class="space-y-3">
                @foreach ($items as $item)
                    @php
                        $product = $item->product;
                        $valid = $product && $product->status === 'published' && $product->stock > 0;
                    @endphp
                    <div class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900 {{ $valid ? '' : 'opacity-60' }}">
                        <a href="{{ $product ? route('shop.product.show', $product->id) : '#' }}" wire:navigate
                           class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                            @if ($product?->mainImage)
                                <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy" class="size-full object-cover">
                            @else
                                <x-icon name="package" class="size-7 text-slate-300 dark:text-slate-600" />
                            @endif
                        </a>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ $product ? route('shop.product.show', $product->id) : '#' }}" wire:navigate
                                   class="line-clamp-2 text-xs font-bold leading-5 hover:text-primary-600 dark:hover:text-primary-400">
                                    {{ $product?->name ?? 'محصول حذف‌شده' }}
                                </a>
                                <button type="button" wire:click="removeItem({{ $item->id }})"
                                        class="grid size-7 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40"
                                        aria-label="حذف از سبد">
                                    <x-icon name="trash" class="size-3.5" />
                                </button>
                            </div>

                            <div class="mt-0.5 truncate text-[10px] text-slate-400">از {{ $product?->shop?->name ?? '—' }}</div>

                            @if ($item->variant_id > 0)
                                <div class="mt-1 inline-flex max-w-full items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                    <x-icon name="layers" class="size-2.5 shrink-0" />
                                    <span class="truncate">{{ $item->variant?->label() ?? 'تنوع نامعتبر' }}</span>
                                </div>
                            @endif

                            @if ($valid)
                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-1 rounded-xl border border-slate-200 p-0.5 dark:border-slate-700">
                                        @php $cap = min(\App\Models\Order::lineStock($item), (int) ($item->variant?->max_purchase ?? 0) ?: \App\Models\Order::lineStock($item)); @endphp
                                        <button type="button" wire:click="incrementQty({{ $item->id }})" @disabled($item->quantity >= $cap)
                                                class="grid size-7 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 disabled:opacity-30 dark:hover:bg-slate-800"
                                                aria-label="افزایش"><x-icon name="plus" class="size-3" /></button>
                                        <span class="w-7 text-center text-xs font-extrabold">{{ \App\Support\Fmt::toFa($item->quantity) }}</span>
                                        <button type="button" wire:click="decrementQty({{ $item->id }})"
                                                class="grid size-7 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800"
                                                aria-label="کاهش"><x-icon name="x" class="size-3" /></button>
                                    </div>
                                    <div class="text-left">
                                        @if ($item->quantity > 1)
                                            <div class="text-[9px] text-slate-400">{{ \App\Support\Fmt::moneyShort($item->unitPrice()) }} × {{ \App\Support\Fmt::toFa($item->quantity) }}</div>
                                        @endif
                                        <div class="text-xs font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::moneyShort($item->lineTotal()) }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="mt-2 inline-block rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                                    ناموجود / غیرفعال
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ── summary + checkout teaser ── --}}
            <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">جمع کل ({{ \App\Support\Fmt::toFa($units) }} کالا)</span>
                    <span class="font-extrabold">{{ \App\Support\Fmt::money($total) }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400">
                    <span>هزینه ارسال</span>
                    <span>در مرحله بعد محاسبه می‌شود</span>
                </div>
                <a href="{{ route('shop.checkout') }}" wire:navigate
                   @disabled($validItems->isEmpty())
                   class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl py-3 text-xs font-extrabold transition active:scale-[0.98] disabled:pointer-events-none disabled:bg-slate-200 disabled:text-slate-500 dark:disabled:bg-slate-800 dark:disabled:text-slate-400
                          {{ $validItems->isNotEmpty() ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/30 hover:bg-primary-700' : '' }}">
                    <x-icon name="credit-card" class="size-4" />
                    ادامه و پرداخت
                </a>
            </div>

            <div class="pb-2"></div>
        @endif
    </div>

    {{-- toast --}}
    <div x-data="{ show: false, msg: '' }"
         @cart-toast.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2600)"
         x-show="show" x-transition.opacity
         class="fixed bottom-24 left-1/2 z-40 w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95"
         x-cloak>
        <span x-text="msg"></span>
    </div>
</div>
