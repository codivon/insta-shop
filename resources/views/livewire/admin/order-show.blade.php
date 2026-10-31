<div class="ip-fade-up space-y-5">

    {{-- ── back + header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.orders') }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
            <x-icon name="arrow-right" class="size-3.5" />
            بازگشت به سفارش‌ها
        </a>
        <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
    </div>

    {{-- ── summary strip ── --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">شماره سفارش</span>
                <x-icon name="bag" class="size-4 text-primary-500" />
            </div>
            <div class="mt-3 text-2xl font-extrabold">#{{ \App\Support\Fmt::fa($order->id) }}</div>
            <div class="mt-1 text-[11px] text-slate-400">{{ $order->dateFa() }}</div>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">مبلغ کل</span>
                <x-icon name="wallet" class="size-4 text-emerald-500" />
            </div>
            <div class="mt-3 text-xl font-extrabold">{{ \App\Support\Fmt::money($order->total_amount) }}</div>
            <div class="mt-1 text-[11px] text-slate-400">{{ \App\Support\Fmt::fa($order->items_count) }} کالا</div>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">پرداخت</span>
                <x-icon name="credit-card" class="size-4 text-primary-500" />
            </div>
            <div class="mt-3 text-sm font-extrabold">{{ $order->gatewayLabel() }}</div>
            <div class="mt-1 space-y-0.5 text-[10px] text-slate-400" dir="ltr">
                @if ($order->gateway_ref)<div>ref: {{ \App\Support\Fmt::toFa($order->gateway_ref) }}</div>@endif
                @if ($order->receipt_ref)<div>receipt: {{ \App\Support\Fmt::toFa($order->receipt_ref) }}</div>@endif
                @if ($order->paid_at)<div>{{ $order->paidAtFa() }}</div>@endif
            </div>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-3">

        {{-- ── items ── --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="package" class="size-4 text-primary-500" />
                اقلام سفارش
            </h3>
            <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800/60">
                @foreach ($order->items as $item)
                    <div class="flex items-center gap-3 py-3">
                        <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                            @if ($item->product?->mainImage)
                                <img src="{{ $item->product->mainImage->url() }}" alt="{{ $item->product_name }}" class="size-full object-cover" loading="lazy">
                            @else
                                <x-icon name="image" class="size-4" />
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <span class="block truncate text-xs font-bold">{{ $item->product_name }}</span>
                            <span class="mt-0.5 block text-[10px] text-slate-400">
                                {{ $item->shop?->name ?? '—' }}
                                @if (! $item->product) · محصول حذف‌شده (فاکتور حفظ شده) @endif
                            </span>
                        </div>
                        <div class="text-left">
                            <span class="block text-[11px] text-slate-400">{{ \App\Support\Fmt::fa($item->quantity) }} × {{ \App\Support\Fmt::money($item->unit_price) }}</span>
                            <span class="mt-0.5 block text-xs font-extrabold">{{ \App\Support\Fmt::money($item->lineTotal()) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-dashed border-slate-200 pt-3 dark:border-slate-700">
                <span class="text-xs font-bold text-slate-500">جمع کل</span>
                <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::money($order->total_amount) }}</span>
            </div>
        </div>

        <div class="space-y-5">
            {{-- ── customer & delivery ── --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-sm font-extrabold">
                    <x-icon name="user" class="size-4 text-primary-500" />
                    مشتری و تحویل
                </h3>
                <dl class="mt-4 space-y-3 text-xs">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-slate-400">خریدار</dt>
                        <dd class="font-bold">{{ $order->customer?->full_name ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-slate-400">موبایل خریدار</dt>
                        <dd class="font-bold" dir="ltr">{{ \App\Support\Fmt::toFa($order->customer?->phone ?? '—') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-slate-400">گیرنده</dt>
                        <dd class="font-bold">{{ $order->receiver_name }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-slate-400">تلفن گیرنده</dt>
                        <dd class="font-bold" dir="ltr">{{ \App\Support\Fmt::toFa($order->delivery_phone) }}</dd>
                    </div>
                    @if ($order->postal_code)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="shrink-0 text-slate-400">کد پستی</dt>
                            <dd class="font-bold" dir="ltr">{{ \App\Support\Fmt::toFa($order->postal_code) }}</dd>
                        </div>
                    @endif
                    <div class="rounded-2xl bg-slate-50 p-3 leading-6 dark:bg-slate-800/60">
                        <dt class="mb-1 text-slate-400">نشانی</dt>
                        <dd class="font-semibold">{{ $order->delivery_address }}</dd>
                    </div>
                </dl>
            </div>

            {{-- ── payment logs ── --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-sm font-extrabold">
                    <x-icon name="file-text" class="size-4 text-primary-500" />
                    لاگ پرداخت
                </h3>
                @if ($logs->isEmpty())
                    <p class="mt-4 text-xs text-slate-400">لاگی ثبت نشده است.</p>
                @else
                    <ol class="mt-4 space-y-3">
                        @foreach ($logs as $log)
                            <li class="flex items-start gap-2.5 text-xs">
                                <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full {{ $log->ok
                                    ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                                    : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                                    <x-icon name="{{ $log->ok ? 'check' : 'x' }}" class="size-3" />
                                </span>
                                <div class="min-w-0">
                                    <span class="block font-bold">{{ $this->eventLabel($log->event) }} <span class="font-normal text-slate-400">({{ $log->gateway }})</span></span>
                                    @if ($log->message)
                                        <span class="mt-0.5 block break-all text-[10px] leading-5 text-slate-400" dir="auto">{{ \Illuminate\Support\Str::limit($log->message, 120) }}</span>
                                    @endif
                                    <span class="block text-[9px] text-slate-400/70">{{ \App\Support\JDate::dateTime($log->created_at) }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </div>
</div>
