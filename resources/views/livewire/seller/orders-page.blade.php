<div class="ip-fade-up" x-data="{ shipStatus: '' }" x-effect="shipStatus = $wire.shipStatus">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                <x-icon name="bag" class="size-5" />
            </span>
            <div>
                <div class="text-sm font-extrabold leading-5">سفارش‌های فروشگاه</div>
                <div class="text-[11px] text-slate-400">مدیریت سفارش‌ها، جزئیات و وضعیت ارسال</div>
            </div>
            <x-seller-notif-bell class="ms-auto" />
        </div>
    </div>

    <div class="px-4 pt-4">
        @if (session('seller-orders-flash'))
            <div class="mb-3 flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <x-icon name="circle-check" class="size-4.5 shrink-0" />
                {{ session('seller-orders-flash') }}
            </div>
        @endif

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
                    @php
                        $items = $this->shopItems($order);
                        $slice = $order->shopShipments->firstWhere('shop_id', $shop->id);
                        $isCanceledSlice = $slice && $slice->isCanceled();
                    @endphp
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="sord-{{ $order->id }}">
                        {{-- top row --}}
                        <div class="flex items-center justify-between">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-xs font-extrabold" dir="ltr">{{ \App\Support\Fmt::toFa('#'.$order->id) }}</span>
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
                                @if ($order->status === 'paid' && $slice)
                                    @if ($isCanceledSlice)
                                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">لغو شده</span>
                                    @else
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $slice->statusClasses() }}">{{ $slice->statusLabel() }}</span>
                                    @endif
                                @endif
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

                        {{-- footer: shop slice total + actions --}}
                        <div class="mt-3 flex items-center justify-between gap-2 border-t border-dashed border-slate-200 pt-2.5 dark:border-slate-700">
                            <span class="text-[10px] text-slate-400">سهم فروشگاه شما ({{ \App\Support\Fmt::toFa($items->count()) }} قلم)</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-extrabold">{{ \App\Support\Fmt::money($slice?->total ?? $this->shopTotal($order)) }}</span>
                                <button type="button" wire:click="openDetail({{ $order->id }})"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1.5 text-[10px] font-bold text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-400">
                                    <x-icon name="eye" class="size-3.5" />
                                    جزئیات
                                </button>
                                @if ($order->status === 'paid' && $slice && ! $isCanceledSlice)
                                    <button type="button" wire:click="openShipment({{ $order->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg bg-primary-600 px-2.5 py-1.5 text-[10px] font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                                        <x-icon name="truck" class="size-3.5" />
                                        وضعیت ارسال
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- tracking code line when shipped --}}
                        @if ($order->status === 'paid' && $slice && ! $isCanceledSlice && $slice->shipment_status !== 'none' && $slice->tracking_code)
                            <div class="mt-2 flex items-center gap-1.5 rounded-xl bg-violet-50 px-3 py-2 text-[10px] font-bold text-violet-700 dark:bg-violet-950/30 dark:text-violet-300">
                                <x-icon name="package" class="size-3.5 shrink-0" />
                                کد رهگیری پستی: <span dir="ltr" class="tracking-wider">{{ \App\Support\Fmt::toFa($slice->tracking_code) }}</span>
                            </div>
                        @endif

                        {{-- canceled slice reason line --}}
                        @if ($isCanceledSlice && $slice->cancel_reason)
                            <div class="mt-2 rounded-xl bg-rose-50 px-3 py-2 text-[10px] font-bold leading-4 text-rose-600 dark:bg-rose-950/30 dark:text-rose-300">
                                دلیل لغو: {{ $slice->cancel_reason }}
                                @if ($slice->refund_amount > 0)
                                    — بازگشت {{ \App\Support\Fmt::money($slice->refund_amount) }} به مشتری
                                @endif
                            </div>
                        @endif
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

    {{-- ═══════════ order details modal — this shop's slice ═══════════ --}}
    <x-modal-panel open="$wire.detailId !== null" max-width="md" close-js="__ipOpen = false; $wire.closeDetail()">
        @php
            $detail = $this->detail;
            $dSlice = $detail ? $this->shopSlice($detail) : null;
            $dCanceled = $dSlice && $dSlice->isCanceled();
            $otherShops = $detail ? $detail->items->pluck('shop_id')->unique()->diff([$shop->id]) : collect();
        @endphp
        @if ($detail)
            @php $dItems = $this->shopItems($detail); @endphp
            <x-slot:header>
                <div class="flex items-center justify-between px-5 py-4">
                    <h3 class="flex items-center gap-2 text-sm font-extrabold">
                        <span class="grid size-8 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                            <x-icon name="bag" class="size-4" />
                        </span>
                        جزئیات سفارش <span dir="ltr">{{ \App\Support\Fmt::toFa('#'.$detail->id) }}</span>
                    </h3>
                    <button type="button" x-on:click="__ipOpen = false; $wire.closeDetail()"
                            class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>
            </x-slot:header>

            <div class="space-y-4 px-5 py-5">
                {{-- status row --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1.5 text-[11px] font-bold {{ $detail->statusClasses() }}">{{ $detail->statusLabel() }}</span>
                    @if ($detail->status === 'paid' && $dSlice)
                        @if ($dCanceled)
                            <span class="rounded-full bg-rose-50 px-3 py-1.5 text-[11px] font-bold text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">سهم شما: لغو شده</span>
                        @else
                            <span class="rounded-full px-3 py-1.5 text-[11px] font-bold {{ $dSlice->statusClasses() }}">ارسال سهم شما: {{ $dSlice->statusLabel() }}</span>
                        @endif
                    @endif
                    <span class="ms-auto text-[10px] text-slate-400">{{ $detail->dateFa() }}</span>
                </div>

                @if ($otherShops->isNotEmpty())
                    <p class="flex items-center gap-1.5 rounded-xl bg-amber-50 px-3 py-2 text-[10px] font-bold leading-4 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300">
                        <x-icon name="info" class="size-3.5 shrink-0" />
                        این سفارش شامل کالاهای فروشگاه‌های دیگر هم است — فقط سهم فروشگاه شما در ادامه نمایش داده می‌شود.
                    </p>
                @endif

                {{-- delivery info --}}
                <div class="rounded-2xl border border-slate-200 p-3.5 dark:border-slate-800">
                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                        <x-icon name="package" class="size-3.5 text-slate-400" />
                        اطلاعات تحویل
                    </p>
                    <dl class="space-y-1.5 text-[11px]">
                        <div class="flex justify-between gap-3"><dt class="text-slate-400">گیرنده</dt><dd class="font-bold">{{ $detail->receiver_name }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-400">تلفن</dt><dd class="font-bold" dir="ltr">{{ \App\Support\Fmt::toFa(\App\Support\Phone::pretty($detail->delivery_phone)) }}</dd></div>
                        @if ($detail->postal_code)
                            <div class="flex justify-between gap-3"><dt class="text-slate-400">کد پستی</dt><dd class="font-bold" dir="ltr">{{ \App\Support\Fmt::toFa($detail->postal_code) }}</dd></div>
                        @endif
                        <div class="flex justify-between gap-3"><dt class="shrink-0 text-slate-400">آدرس</dt><dd class="text-left font-bold leading-5">{{ $detail->delivery_address }}</dd></div>
                    </dl>
                </div>

                {{-- payment info (order-level gateway context) --}}
                <div class="rounded-2xl border border-slate-200 p-3.5 dark:border-slate-800">
                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                        <x-icon name="circle-check" class="size-3.5 text-slate-400" />
                        پرداخت
                    </p>
                    <dl class="space-y-1.5 text-[11px]">
                        <div class="flex justify-between gap-3"><dt class="text-slate-400">درگاه</dt><dd class="font-bold">{{ $detail->gatewayLabel() }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-400">زمان پرداخت</dt><dd class="font-bold">{{ $detail->paidAtFa() ?: '—' }}</dd></div>
                    </dl>
                </div>

                {{-- this shop's slice --}}
                <div class="rounded-2xl border border-slate-200 p-3.5 dark:border-slate-800">
                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                        <x-icon name="store" class="size-3.5 text-slate-400" />
                        اقلام فروشگاه شما ({{ \App\Support\Fmt::fa($dItems->count()) }} قلم)
                    </p>
                    <div class="space-y-2">
                        @foreach ($dItems as $item)
                            <div class="flex items-center gap-2.5">
                                <span class="grid size-10 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800">
                                    @if ($item->product?->mainImage)
                                        <img src="{{ $item->product->mainImage->url() }}" alt="{{ $item->product_name }}" loading="lazy" class="size-full object-cover">
                                    @else
                                        <x-icon name="package" class="size-4 text-slate-300 dark:text-slate-600" />
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-[11px] font-bold">{{ $item->product_name }}</div>
                                    <div class="text-[9.5px] text-slate-400">
                                        {{ \App\Support\Fmt::money($item->unit_price) }} × {{ \App\Support\Fmt::fa($item->quantity) }}
                                        @if ($item->variant_label) · {{ $item->variant_label }} @endif
                                    </div>
                                </div>
                                <span class="shrink-0 text-[11px] font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::money($item->lineTotal()) }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if ($dSlice)
                        <div class="mt-3 space-y-1.5 border-t border-dashed border-slate-200 pt-2.5 dark:border-slate-700">
                            <div class="flex items-center justify-between text-[10.5px] text-slate-400">
                                <span>جمع کالاهای فروشگاه شما</span>
                                <span class="font-bold text-slate-600 dark:text-slate-300">{{ \App\Support\Fmt::money($dSlice->items_total) }}</span>
                            </div>
                            @if ($dSlice->shipping_cost > 0)
                                <div class="flex items-center justify-between text-[10.5px] text-slate-400">
                                    <span class="flex items-center gap-1"><x-icon name="truck" class="size-3" /> هزینه ارسال فروشگاه شما</span>
                                    <span class="font-bold text-slate-600 dark:text-slate-300">{{ \App\Support\Fmt::money($dSlice->shipping_cost) }}</span>
                                </div>
                            @endif
                            @if ($dSlice->discount_amount > 0)
                                <div class="flex items-center justify-between text-[10.5px] text-emerald-600 dark:text-emerald-400">
                                    <span class="flex items-center gap-1"><x-icon name="tag" class="size-3" /> سهم شما از تخفیف</span>
                                    <span class="font-extrabold">− {{ \App\Support\Fmt::money($dSlice->discount_amount) }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between border-t border-slate-100 pt-2 dark:border-slate-800">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">مبلغ سهم فروشگاه شما</span>
                                <span class="text-xs font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::money($dSlice->total) }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- canceled slice reason --}}
                @if ($dCanceled)
                    <div class="rounded-xl bg-rose-50 px-3.5 py-3 text-[11px] font-bold leading-5 text-rose-600 dark:bg-rose-950/30 dark:text-rose-300">
                        <p>دلیل لغو: {{ $dSlice->cancel_reason }}</p>
                        @if ($dSlice->refund_amount > 0)
                            <p class="mt-1">مبلغ {{ \App\Support\Fmt::money($dSlice->refund_amount) }} باید به مشتری بازگردانده شود.</p>
                        @endif
                    </div>
                @endif

                {{-- tracking code of this shop's parcel --}}
                @if ($detail->status === 'paid' && $dSlice && ! $dCanceled && $dSlice->tracking_code)
                    <div class="flex items-center gap-2 rounded-xl bg-violet-50 px-3.5 py-3 text-[11px] font-bold text-violet-700 dark:bg-violet-950/30 dark:text-violet-300">
                        <x-icon name="truck" class="size-4 shrink-0" />
                        کد رهگیری پستی: <span dir="ltr" class="tracking-wider">{{ \App\Support\Fmt::toFa($dSlice->tracking_code) }}</span>
                    </div>
                @endif
            </div>
        @endif
    </x-modal-panel>

    {{-- ═══════════ shipment status modal (this shop's slice) ═══════════ --}}
    <x-modal-panel open="$wire.shipOrderId !== null" max-width="sm" close-js="__ipOpen = false; $wire.closeShipment()">
        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="truck" class="size-4" />
                    </span>
                    وضعیت ارسال سفارش <span dir="ltr">{{ \App\Support\Fmt::toFa('#'.$shipOrderId) }}</span>
                </h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeShipment()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="space-y-4 px-5 py-5">
            <div class="space-y-2">
                @foreach ([['processing', 'در حال پردازش', 'clock'], ['shipped', 'در حال ارسال', 'truck'], ['delivered', 'تحویل شد', 'circle-check']] as [$value, $label, $icon])
                    <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border px-3.5 py-3 transition {{ $shipStatus === $value
                            ? 'border-primary-400 bg-primary-50 dark:border-primary-600 dark:bg-primary-950/40'
                            : 'border-slate-200 hover:border-primary-300 dark:border-slate-700' }}"
                           wire:key="shipopt-{{ $value }}">
                        <span class="flex items-center gap-2 text-xs font-bold {{ $shipStatus === $value ? 'text-primary-700 dark:text-primary-300' : 'text-slate-600 dark:text-slate-300' }}">
                            <x-icon name="{{ $icon }}" class="size-4 text-slate-400" />
                            {{ $label }}
                        </span>
                        <input type="radio" value="{{ $value }}" wire:model="shipStatus" class="size-4" style="accent-color: var(--ip-primary)">
                    </label>
                @endforeach

                @php
                    $shipSlice = $this->shipSlice;
                    $canCancelSlice = $shipSlice && ! $shipSlice->isCanceled() && $shipSlice->shipment_status !== 'delivered';
                @endphp
                @if ($canCancelSlice)
                    {{-- لغو — deliberately the LAST option, separated from the send options by a divider line --}}
                    <div class="border-t border-dashed border-slate-200 pt-2.5 dark:border-slate-700">
                        <button type="button" wire:click="openCancelFromShipment"
                                class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl border border-rose-200 px-3.5 py-3 text-xs font-bold text-rose-500 transition hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950/40">
                            <span class="flex items-center gap-2">
                                <x-icon name="circle-x" class="size-4" />
                                لغو سهم فروشگاه از این سفارش
                            </span>
                            <x-icon name="chevron-left" class="size-3.5 opacity-50" />
                        </button>
                        <p class="mt-1.5 px-1 text-[10px] leading-4 text-slate-400">
                            کالاها به موجودی انبار برمی‌گردند و مبلغ سهم فروشگاه به مشتری بازگردانده می‌شود.
                        </p>
                    </div>
                @endif
            </div>

            <div x-show="shipStatus === 'shipped'" x-cloak x-transition.opacity>
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                        کد رهگیری پستی <span class="text-rose-500">*</span>
                    </span>
                    <input type="text" wire:model="shipTracking" dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                           placeholder="مثلاً: ۱۲۳۴۵۶۷۸۹۰۱۲۳۴۵۶۷۸">
                    @error('shipTracking') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>
                <p class="mt-1.5 text-[10px] leading-4 text-slate-400">
                    این کد برای مشتری هم نمایش داده می‌شود تا بتواند مرسوله را پیگیری کند.
                </p>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="button" wire:click="saveShipment" wire:loading.attr="disabled" wire:target="saveShipment"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                    <span wire:loading wire:target="saveShipment" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    ثبت وضعیت
                </button>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeShipment()"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    انصراف
                </button>
            </div>
        </div>
    </x-modal-panel>

    {{-- ═══════════ cancel modal (this shop's slice) ═══════════ --}}
    <x-modal-panel open="$wire.cancelOrderId !== null" max-width="sm" close-js="__ipOpen = false; $wire.closeCancel()">
        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-rose-600/10 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                        <x-icon name="circle-x" class="size-4" />
                    </span>
                    لغو سهم فروشگاه از سفارش <span dir="ltr">{{ \App\Support\Fmt::toFa('#'.$cancelOrderId) }}</span>
                </h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeCancel()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="space-y-4 px-5 py-5">
            <p class="rounded-xl bg-amber-50 px-3.5 py-3 text-[11px] font-bold leading-5 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300">
                با لغو، کالاها به موجودی انبار شما برمی‌گردند و سهم کسرشده از کیف پول شما برای بازگشت به مشتری کنار گذاشته می‌شود. این اقدام قابل بازگشت نیست.
            </p>

            <label class="block">
                <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                    دلیل لغو <span class="text-rose-500">*</span>
                </span>
                <textarea wire:model="cancelReason" rows="3"
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                          placeholder="مثلاً: کالا موجود نیست / امکان ارسال به این منطقه وجود ندارد"></textarea>
                @error('cancelReason') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
            </label>

            <div class="flex gap-2 pt-1">
                <button type="button" wire:click="confirmCancel" wire:loading.attr="disabled" wire:target="confirmCancel"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-rose-600/25 transition hover:bg-rose-700 disabled:opacity-50">
                    <span wire:loading wire:target="confirmCancel" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    تأیید و لغو
                </button>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeCancel()"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    انصراف
                </button>
            </div>
        </div>
    </x-modal-panel>
</div>
