<div class="ip-fade-up">
    <div class="px-4 pt-6">
        @php $paid = $order->status === 'paid'; $pending = $order->status === 'pending'; @endphp

        {{-- ── status hero ── --}}
        <div class="rounded-2xl border p-6 text-center shadow-sm {{ $paid
                ? 'border-emerald-200 bg-gradient-to-b from-emerald-50 to-white dark:border-emerald-900 dark:from-emerald-950/40 dark:to-slate-900'
                : ($pending
                    ? 'border-amber-200 bg-gradient-to-b from-amber-50 to-white dark:border-amber-900 dark:from-amber-950/40 dark:to-slate-900'
                    : 'border-rose-200 bg-gradient-to-b from-rose-50 to-white dark:border-rose-900 dark:from-rose-950/40 dark:to-slate-900') }}">
            <span class="mx-auto grid size-16 place-items-center rounded-full shadow-md {{ $paid
                ? 'bg-emerald-500 text-white shadow-emerald-500/40'
                : ($pending ? 'bg-amber-500 text-white shadow-amber-500/40' : 'bg-rose-500 text-white shadow-rose-500/40') }}">
                @if ($paid)
                    <x-icon name="check" class="size-8" />
                @elseif($pending)
                    <x-icon name="clock" class="size-8" />
                @else
                    <x-icon name="x" class="size-8" />
                @endif
            </span>

            <h2 class="mt-3 text-sm font-extrabold">
                @if ($paid)
                    پرداخت با موفقیت انجام شد 🎉
                @elseif($pending)
                    سفارش در انتظار پرداخت است
                @else
                    پرداخت ناموفق بود
                @endif
            </h2>
            <p class="mt-1 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                @if ($paid)
                    از خرید شما سپاسگزاریم؛ سفارش شما ثبت شد.
                @elseif($pending)
                    در صورت لزوم می‌توانید پرداخت را ادامه دهید.
                @else
                    مبلغی از حساب شما کسر نشده است. می‌توانید دوباره تلاش کنید.
                @endif
            </p>

            <div class="mt-4 grid grid-cols-2 gap-2 text-right">
                <div class="rounded-2xl bg-white/80 p-3 dark:bg-slate-800/80">
                    <div class="text-[9px] text-slate-400">شماره سفارش</div>
                    <div class="mt-0.5 text-xs font-extrabold" dir="ltr">{{ \App\Support\Fmt::toFa('#'.$order->id) }}</div>
                </div>
                <div class="rounded-2xl bg-white/80 p-3 dark:bg-slate-800/80">
                    <div class="text-[9px] text-slate-400">وضعیت</div>
                    <div class="mt-0.5">
                        <span class="inline-block rounded-full px-2.5 py-1 text-[10px] font-bold {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
                    </div>
                </div>
                @if ($paid && $order->receipt_ref)
                    <div class="col-span-2 rounded-2xl bg-white/80 p-3 dark:bg-slate-800/80">
                        <div class="text-[9px] text-slate-400">شماره پیگیری پرداخت</div>
                        <div class="mt-0.5 text-xs font-extrabold" dir="ltr">{{ \App\Support\Fmt::toFa($order->receipt_ref) }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── pending banner: continue payment ── --}}
        @if ($pending)
            <a href="{{ route('shop.payment.go', $order->id) }}"
               class="mt-3 flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-600 py-3 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-[0.98]">
                <x-icon name="credit-card" class="size-4" />
                ادامه پرداخت
            </a>
        @endif

        {{-- ── order lines ── --}}
        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-xs font-extrabold">اقلام سفارش</h3>
            <div class="mt-3 space-y-2">
                @foreach ($items as $item)
                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-2.5 dark:bg-slate-800/60">
                        <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                            @if ($item->product?->mainImage)
                                <img src="{{ $item->product->mainImage->url() }}" alt="{{ $item->product_name }}" loading="lazy" class="size-full object-cover">
                            @else
                                <x-icon name="package" class="size-5 text-slate-300 dark:text-slate-600" />
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-[11px] font-bold">{{ $item->product_name }}</div>
                            <div class="text-[9px] text-slate-400">{{ \App\Support\Fmt::moneyShort($item->unit_price) }} × {{ \App\Support\Fmt::toFa($item->quantity) }}</div>
                        </div>
                        <div class="shrink-0 text-[11px] font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::moneyShort($item->lineTotal()) }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 flex items-center justify-between border-t border-dashed border-slate-200 pt-3 text-xs dark:border-slate-700">
                <span class="font-bold text-slate-500 dark:text-slate-400">جمع کل ({{ \App\Support\Fmt::toFa($order->items_count) }} کالا)</span>
                <span class="text-sm font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::money($order->total_amount) }}</span>
            </div>
        </div>

        {{-- ── actions ── --}}
        <div class="mt-4 mb-2 grid grid-cols-2 gap-3">
            <a href="{{ route('shop.orders.show', $order->id) }}" wire:navigate
               class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white py-3 text-xs font-extrabold transition hover:border-primary-300 active:scale-[0.98] dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-800">
                <x-icon name="file-text" class="size-4" />
                جزئیات سفارش
            </a>
            <a href="{{ route('shop.home') }}" wire:navigate
               class="flex items-center justify-center gap-2 rounded-2xl bg-primary-600 py-3 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-[0.98]">
                <x-icon name="home" class="size-4" />
                بازگشت به فروشگاه
            </a>
        </div>
    </div>
</div>
