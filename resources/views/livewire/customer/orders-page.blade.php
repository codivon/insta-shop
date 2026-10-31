<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('shop.profile') }}" wire:navigate
               class="grid size-10 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800"
               aria-label="بازگشت به پروفایل">
                <x-icon name="arrow-left" class="size-5" />
            </a>
            <div>
                <div class="text-sm font-extrabold leading-5">سفارش‌های من</div>
                <div class="text-[11px] text-slate-400">تاریخچه خرید و پیگیری سفارش‌ها</div>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4">
        @if ($orders->isEmpty())
            {{-- ── empty state ── --}}
            <div class="mt-10 rounded-3xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-slate-100 dark:bg-slate-800">
                    <x-icon name="inbox" class="size-8 text-slate-300 dark:text-slate-600" />
                </span>
                <p class="mt-4 text-sm font-extrabold">هنوز سفارشی ثبت نکرده‌اید</p>
                <p class="mt-1 text-[11px] text-slate-400">اولین خرید خود را از فروشگاه‌های فعال شروع کنید.</p>
                <a href="{{ route('shop.home') }}" wire:navigate
                   class="mt-5 inline-block rounded-2xl bg-primary-600 px-6 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-95">
                    رفتن به فروشگاه‌ها
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($orders as $order)
                    <a href="{{ route('shop.orders.show', $order->id) }}" wire:navigate
                       class="block rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-primary-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-extrabold" dir="ltr">{{ \App\Support\Fmt::toFa('#'.$order->id) }}</span>
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400">{{ $order->dateFa() }}</span>
                        </div>

                        {{-- first two items preview --}}
                        <div class="mt-3 flex items-center gap-3">
                            <div class="flex -space-x-2 space-x-reverse">
                                @foreach ($order->items->take(3) as $item)
                                    <span class="grid size-10 place-items-center overflow-hidden rounded-xl border-2 border-white bg-slate-100 dark:border-slate-900 dark:bg-slate-800">
                                        @if ($item->product?->mainImage)
                                            <img src="{{ $item->product->mainImage->url() }}" alt="{{ $item->product_name }}" loading="lazy" class="size-full object-cover">
                                        @else
                                            <x-icon name="package" class="size-4 text-slate-300 dark:text-slate-600" />
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-[11px] font-bold">{{ $order->items->first()?->product_name }}</div>
                                <div class="text-[9px] text-slate-400">
                                    @if ($order->items->count() > 1)
                                        + {{ \App\Support\Fmt::toFa($order->items->count() - 1) }} کالای دیگر ({{ \App\Support\Fmt::toFa($order->items_count) }} عدد)
                                    @else
                                        {{ \App\Support\Fmt::toFa($order->items_count) }} عدد
                                    @endif
                                </div>
                            </div>
                            <div class="shrink-0 text-left">
                                <div class="text-xs font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::moneyShort($order->total_amount) }}</div>
                                <div class="mt-0.5 flex items-center justify-end text-[9px] text-slate-400">
                                    جزئیات
                                    <x-icon name="chevron-left" class="size-3" />
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- lazy-load sentinel (IntersectionObserver → loadMore) --}}
            <div
                x-data="{}"
                x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
                class="{{ $hasMore ? 'mt-4 flex justify-center py-2' : 'hidden' }}"
                wire:loading.delay.class="opacity-0" wire:loading.delay.remove.class="opacity-100" wire:target="loadMore"
                aria-hidden="true">
                <span class="inline-block size-6 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"></span>
            </div>

            <div class="h-6"></div>
        @endif
    </div>
</div>
