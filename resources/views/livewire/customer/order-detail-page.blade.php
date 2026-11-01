<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('shop.orders') }}" wire:navigate
               class="grid size-10 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800"
               aria-label="بازگشت به سفارش‌ها">
                <x-icon name="arrow-left" class="size-5" />
            </a>
            <div>
                <div class="text-sm font-extrabold leading-5">سفارش <span dir="ltr">{{ \App\Support\Fmt::toFa('#'.$order->id) }}</span></div>
                <div class="text-[11px] text-slate-400">{{ $order->dateFa() }}</div>
            </div>
            <span class="mr-auto rounded-full px-2.5 py-1 text-[10px] font-bold {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
        </div>
    </div>

    <div class="px-4 pt-4">
        {{-- ── pending: continue payment ── --}}
        @if ($order->status === 'pending')
            <div class="mb-4 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 p-4 dark:border-amber-900 dark:bg-amber-950/30">
                <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400">
                    <x-icon name="clock" class="size-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-extrabold">این سفارش هنوز پرداخت نشده است</div>
                    <div class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">برای ثبت نهایی، پرداخت را ادامه دهید.</div>
                </div>
                <a href="{{ route('shop.payment.go', $order->id) }}"
                   class="shrink-0 rounded-xl bg-primary-600 px-4 py-2.5 text-[11px] font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
                    پرداخت
                </a>
            </div>
        @endif

        {{-- ── items ── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-xs font-extrabold">اقلام سفارش ({{ \App\Support\Fmt::toFa($order->items_count) }} عدد)</h3>
            <div class="mt-3 space-y-2">
                @foreach ($items as $item)
                    <div class="flex items-center gap-3">
                        <span class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-2xl bg-slate-100 dark:bg-slate-800">
                            @if ($item->product?->mainImage)
                                <img src="{{ $item->product->mainImage->url() }}" alt="{{ $item->product_name }}" loading="lazy" class="size-full object-cover">
                            @else
                                <x-icon name="package" class="size-6 text-slate-300 dark:text-slate-600" />
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-xs font-bold">{{ $item->product_name }}</div>
                            @if ($item->variant_label)
                                <div class="mt-0.5 inline-flex max-w-full items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                    <x-icon name="layers" class="size-2.5 shrink-0" />
                                    <span class="truncate">{{ $item->variant_label }}</span>
                                </div>
                            @endif
                            <div class="mt-0.5 text-[10px] text-slate-400">
                                @if ($item->product?->shop?->name)
                                    از {{ $item->product->shop->name }} —
                                @endif
                                {{ \App\Support\Fmt::moneyShort($item->unit_price) }} × {{ \App\Support\Fmt::toFa($item->quantity) }}
                            </div>
                        </div>
                        <div class="shrink-0 text-xs font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::moneyShort($item->lineTotal()) }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 space-y-1.5 border-t border-dashed border-slate-200 pt-3 dark:border-slate-700">
                <div class="flex items-center justify-between text-[10.5px] text-slate-400">
                    <span>جمع کالاها</span>
                    <span class="font-bold">{{ \App\Support\Fmt::money($order->items->sum(fn ($i) => $i->lineTotal())) }}</span>
                </div>
                @if ($order->shipping_cost > 0)
                    <div class="flex items-center justify-between text-[10.5px] text-slate-400">
                        <span class="flex items-center gap-1">
                            <x-icon name="truck" class="size-3" />
                            هزینه ارسال
                        </span>
                        <span class="font-bold">{{ \App\Support\Fmt::money($order->shipping_cost) }}</span>
                    </div>
                @endif
                @if ($order->discount_amount > 0)
                    <div class="flex items-center justify-between text-[10.5px] text-emerald-600 dark:text-emerald-400">
                        <span class="flex items-center gap-1">
                            <x-icon name="tag" class="size-3" />
                            تخفیف @if ($order->discount_code)({{ $order->discount_code }}) @endif
                        </span>
                        <span class="font-extrabold">− {{ \App\Support\Fmt::money($order->discount_amount) }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between border-t border-slate-100 pt-2 dark:border-slate-800">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">جمع کل</span>
                    <span class="text-sm font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::money($order->total_amount) }}</span>
                </div>
            </div>

            {{-- ── carriers of each store (shipments snapshot) ── --}}
            @if (is_iterable($order->shipments) && count($order->shipments) > 0)
                <div class="mt-3 space-y-1.5 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50">
                    @foreach ($order->shipments as $shipment)
                        <div class="flex items-center justify-between gap-2 text-[10px] font-bold">
                            <span class="flex min-w-0 items-center gap-1.5 text-slate-500 dark:text-slate-300">
                                <x-icon name="truck" class="size-3 shrink-0 text-slate-400" />
                                <span class="truncate">{{ $shipment['carrier_title'] ?? 'روش ارسال' }}</span>
                            </span>
                            <span class="shrink-0 text-slate-400">
                                {{ (($shipment['cost'] ?? 0) > 0 ? \App\Support\Fmt::moneyShort($shipment['cost']).' تومان' : 'پس‌کرایه / رایگان') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── delivery ── --}}
        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2">
                <span class="grid size-9 place-items-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    <x-icon name="package" class="size-4.5" />
                </span>
                <h3 class="text-xs font-extrabold">اطلاعات تحویل</h3>
            </div>
            <dl class="mt-3 space-y-2 text-[11px] leading-5">
                <div class="flex items-start justify-between gap-3">
                    <dt class="shrink-0 text-slate-400">گیرنده</dt>
                    <dd class="text-left font-bold">{{ $order->receiver_name }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="shrink-0 text-slate-400">تلفن</dt>
                    <dd class="text-left font-bold" dir="ltr">{{ \App\Support\Fmt::toFa(\App\Support\Phone::pretty($order->delivery_phone)) }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="shrink-0 text-slate-400">نشانی</dt>
                    <dd class="text-left font-bold leading-5">{{ $order->delivery_address }}</dd>
                </div>
                @if ($order->postal_code)
                    <div class="flex items-start justify-between gap-3">
                        <dt class="shrink-0 text-slate-400">کد پستی</dt>
                        <dd class="text-left font-bold" dir="ltr">{{ \App\Support\Fmt::toFa($order->postal_code) }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- ── payment info ── --}}
        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2">
                <span class="grid size-9 place-items-center rounded-xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                    <x-icon name="credit-card" class="size-4.5" />
                </span>
                <h3 class="text-xs font-extrabold">اطلاعات پرداخت</h3>
            </div>
            <dl class="mt-3 space-y-2 text-[11px] leading-5">
                <div class="flex items-start justify-between gap-3">
                    <dt class="shrink-0 text-slate-400">درگاه</dt>
                    <dd class="text-left font-bold">{{ $order->gatewayLabel() }}</dd>
                </div>
                @if ($order->receipt_ref)
                    <div class="flex items-start justify-between gap-3">
                        <dt class="shrink-0 text-slate-400">شماره پیگیری</dt>
                        <dd class="text-left font-extrabold" dir="ltr">{{ \App\Support\Fmt::toFa($order->receipt_ref) }}</dd>
                    </div>
                @endif
                @if ($order->paid_at)
                    <div class="flex items-start justify-between gap-3">
                        <dt class="shrink-0 text-slate-400">تاریخ پرداخت</dt>
                        <dd class="text-left font-bold">{{ $order->paidAtFa() }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="mt-4 mb-2">
            <a href="{{ route('shop.home') }}" wire:navigate
               class="flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white py-3 text-xs font-extrabold transition hover:border-primary-300 active:scale-[0.98] dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-800">
                <x-icon name="home" class="size-4" />
                بازگشت به فروشگاه
            </a>
        </div>
    </div>
</div>
