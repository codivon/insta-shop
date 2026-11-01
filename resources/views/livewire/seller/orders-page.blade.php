<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                <x-icon name="bag" class="size-5" />
            </span>
            <div>
                <div class="text-sm font-extrabold leading-5">سفارش‌های فروشگاه</div>
                <div class="text-[11px] text-slate-400">سفارش‌هایی که شامل محصولات شما هستند (فقط خواندنی)</div>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4">
        {{-- ── status filter chips ── --}}
        <div class="scroll-slim -mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
            @foreach ([['all', 'همه'], ['paid', 'پرداخت‌شده'], ['pending', 'در انتظار'], ['failed', 'ناموفق'], ['canceled', 'لغو شده']] as [$value, $label])
                <button type="button" wire:click="$set('statusFilter', '{{ $value }}')"
                        class="shrink-0 rounded-full px-4 py-2 text-[11px] font-bold transition {{ $statusFilter === $value
                                ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                : 'border border-slate-200 bg-white text-slate-500 hover:border-primary-300 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if ($orders->isEmpty())
            {{-- ── empty state ── --}}
            <div class="mt-10 rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-slate-100 dark:bg-slate-800">
                    <x-icon name="inbox" class="size-8 text-slate-300 dark:text-slate-600" />
                </span>
                <p class="mt-4 text-sm font-extrabold">سفارشی یافت نشد</p>
                <p class="mt-1 text-[11px] text-slate-400">
                    @if ($statusFilter === 'all')
                        هنوز سفارشی شامل محصولات شما ثبت نشده است.
                    @else
                        هیچ سفارشی با این وضعیت وجود ندارد.
                    @endif
                </p>
            </div>
        @else
            <div class="mt-3 space-y-3">
                @foreach ($orders as $order)
                    @php $items = $this->shopItems($order); @endphp
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        {{-- top row --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-extrabold" dir="ltr">{{ \App\Support\Fmt::toFa('#'.$order->id) }}</span>
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400">{{ $order->dateFa() }}</span>
                        </div>

                        {{-- buyer --}}
                        <div class="mt-2.5 flex items-center gap-2 rounded-2xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                            <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-white text-slate-400 shadow-sm dark:bg-slate-900">
                                <x-icon name="user" class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-[11px] font-bold">{{ $order->receiver_name }}</div>
                                <div class="text-[9px] text-slate-400" dir="ltr">{{ \App\Support\Fmt::toFa(\App\Support\Phone::pretty($order->delivery_phone)) }}</div>
                            </div>
                            @if ($order->paid_at)
                                <span class="shrink-0 text-[9px] text-emerald-600 dark:text-emerald-400">پرداخت: {{ $order->paidAtFa() }}</span>
                            @endif
                        </div>

                        {{-- this shop's items --}}
                        <div class="mt-3 space-y-1.5">
                            @foreach ($items as $item)
                                <div class="flex items-center gap-2.5">
                                    <span class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800">
                                        @if ($item->product?->mainImage)
                                            <img src="{{ $item->product->mainImage->url() }}" alt="{{ $item->product_name }}" loading="lazy" class="size-full object-cover">
                                        @else
                                            <x-icon name="package" class="size-3.5 text-slate-300 dark:text-slate-600" />
                                        @endif
                                    </span>
                                    <div class="min-w-0 flex-1 truncate text-[11px] font-bold">{{ $item->product_name }}</div>
                                    <div class="shrink-0 text-[9px] text-slate-400">× {{ \App\Support\Fmt::toFa($item->quantity) }}</div>
                                    <div class="shrink-0 text-[11px] font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::moneyShort($item->lineTotal()) }}</div>
                                </div>
                            @endforeach
                        </div>

                        {{-- address --}}
                        <div class="mt-3 flex items-start gap-1.5 text-[10px] leading-5 text-slate-400">
                            <x-icon name="package" class="mt-0.5 size-3 shrink-0" />
                            <span class="line-clamp-2">تحویل: {{ $order->delivery_address }}</span>
                        </div>

                        {{-- footer: shop slice total --}}
                        <div class="mt-3 flex items-center justify-between border-t border-dashed border-slate-200 pt-2.5 dark:border-slate-700">
                            <span class="text-[10px] text-slate-400">سهم فروشگاه شما ({{ \App\Support\Fmt::toFa($items->count()) }} قلم)</span>
                            <span class="text-xs font-extrabold">{{ \App\Support\Fmt::money($this->shopTotal($order)) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- lazy-load sentinel --}}
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
