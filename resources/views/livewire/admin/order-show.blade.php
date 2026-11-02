<div class="ip-fade-up space-y-5">

    {{-- ── back + header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.orders') }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
            <x-icon name="arrow-right" class="size-3.5" />
            بازگشت به سفارش‌ها
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
            @if ($order->status === 'paid')
                <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $order->shipmentStatusClasses() }}">ارسال کل: {{ $order->shipmentStatusLabel() }}</span>
            @endif
        </div>
    </div>

    {{-- ── summary strip ── --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">شماره سفارش</span>
                <x-icon name="bag" class="size-4 text-primary-500" />
            </div>
            <div class="mt-3 text-2xl font-extrabold">#{{ \App\Support\Fmt::fa($order->id) }}</div>
            <div class="mt-1 text-[11px] text-slate-400">{{ $order->dateFa() }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400">مبلغ کل</span>
                <x-icon name="wallet" class="size-4 text-emerald-500" />
            </div>
            <div class="mt-3 text-xl font-extrabold">{{ \App\Support\Fmt::money($order->total_amount) }}</div>
            <div class="mt-1 text-[11px] text-slate-400">{{ \App\Support\Fmt::fa($order->items_count) }} کالا</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
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

        {{-- ── per-shop sections ── --}}
        <div class="space-y-5 xl:col-span-2">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="store" class="size-4 text-primary-500" />
                فروشگاه‌های این سفارش ({{ \App\Support\Fmt::fa($shopGroups->count()) }})
            </h3>

            @foreach ($shopGroups as $group)
                @php
                    $gSlice = $group['slice'];
                    $gCanceled = $gSlice && $gSlice->isCanceled();
                    $gItemsTotal = (int) $group['items']->sum(fn ($i) => $i->lineTotal());
                    $isShipOpen = $shipShopId === $group['shopId'];
                @endphp
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="shopsec-{{ $group['shopId'] }}">
                    {{-- shop header --}}
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/25">
                                <x-icon name="store" class="size-4.5" />
                            </span>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-extrabold">{{ $group['shop']?->name ?? 'فروشگاه حذف‌شده' }}</div>
                                @if ($group['carrierTitle'] !== '')
                                    <div class="mt-0.5 flex items-center gap-1 text-[10px] text-slate-400">
                                        <x-icon name="truck" class="size-3" />
                                        روش ارسال: {{ $group['carrierTitle'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if ($order->status === 'paid' && $gSlice)
                            @if ($gCanceled)
                                <span class="rounded-full bg-rose-50 px-3 py-1.5 text-[11px] font-bold text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">لغو شده</span>
                            @else
                                <span class="rounded-full px-3 py-1.5 text-[11px] font-bold {{ $gSlice->statusClasses() }}">{{ $gSlice->statusLabel() }}</span>
                            @endif
                        @endif
                    </div>

                    {{-- items of this shop --}}
                    <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800/60">
                        @foreach ($group['items'] as $item)
                            <div class="flex items-center gap-3 py-2.5">
                                <span class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                                    @if ($item->product?->mainImage)
                                        <img src="{{ $item->product->mainImage->url() }}" alt="{{ $item->product_name }}" class="size-full object-cover" loading="lazy">
                                    @else
                                        <x-icon name="image" class="size-4" />
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <span class="block truncate text-xs font-bold">{{ $item->product_name }}</span>
                                    <span class="mt-0.5 block text-[10px] text-slate-400">
                                        @if ($item->variant_label) {{ $item->variant_label }} · @endif
                                        @if (! $item->product) محصول حذف‌شده (فاکتور حفظ شده) @endif
                                    </span>
                                </div>
                                <div class="text-left">
                                    <span class="block text-[11px] text-slate-400">{{ \App\Support\Fmt::fa($item->quantity) }} × {{ \App\Support\Fmt::money($item->unit_price) }}</span>
                                    <span class="mt-0.5 block text-xs font-extrabold">{{ \App\Support\Fmt::money($item->lineTotal()) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- slice financials --}}
                    <div class="mt-3 grid gap-2 rounded-2xl bg-slate-50 p-3.5 sm:grid-cols-4 dark:bg-slate-800/60">
                        <div>
                            <div class="text-[10px] text-slate-400">جمع کالاها</div>
                            <div class="mt-0.5 text-xs font-extrabold">{{ \App\Support\Fmt::money($gItemsTotal) }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400">هزینه ارسال</div>
                            <div class="mt-0.5 text-xs font-extrabold">{{ ($gSlice?->shipping_cost ?? 0) > 0 ? \App\Support\Fmt::money($gSlice->shipping_cost) : 'پس‌کرایه / رایگان' }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400">سهم تخفیف</div>
                            <div class="mt-0.5 text-xs font-extrabold text-emerald-600 dark:text-emerald-400">{{ ($gSlice?->discount_amount ?? 0) > 0 ? '− '.\App\Support\Fmt::money($gSlice->discount_amount) : '—' }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400">مبلغ این فروشگاه</div>
                            <div class="mt-0.5 text-xs font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::money($gSlice?->total ?? $gItemsTotal) }}</div>
                        </div>
                    </div>

                    {{-- canceled slice details --}}
                    @if ($gCanceled)
                        <div class="mt-3 rounded-2xl bg-rose-50 p-3.5 text-[11px] font-bold leading-5 text-rose-600 dark:bg-rose-950/30 dark:text-rose-300">
                            <p>لغو توسط {{ $gSlice->canceled_by === 'admin' ? 'مدیر' : 'فروشنده' }} — {{ \App\Support\JDate::dateTime($gSlice->canceled_at) }}</p>
                            @if ($gSlice->cancel_reason)
                                <p class="mt-0.5 font-semibold">دلیل: {{ $gSlice->cancel_reason }}</p>
                            @endif
                            @if ($gSlice->refund_amount > 0)
                                <p class="mt-0.5">مبلغ بازگشتی به مشتری: {{ \App\Support\Fmt::money($gSlice->refund_amount) }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- per-shop shipment control + cancel (paid, active slice) --}}
                    @if ($order->status === 'paid' && $gSlice && ! $gCanceled)
                        <div class="mt-4 rounded-2xl border border-slate-200 p-4 dark:border-slate-700/60" x-data="{ open: {{ $isShipOpen ? 'true' : 'false' }} }">
                            @if ($isShipOpen)
                                {{-- edit mode --}}
                                <div class="grid gap-2 sm:grid-cols-3">
                                    @foreach ([['processing', 'در حال پردازش', 'clock'], ['shipped', 'در حال ارسال', 'truck'], ['delivered', 'تحویل شد', 'circle-check']] as [$value, $label, $icon])
                                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-[11px] font-bold transition {{ $shipStatus === $value
                                                ? 'border-primary-400 bg-primary-50 text-primary-700 dark:border-primary-600 dark:bg-primary-950/40 dark:text-primary-300'
                                                : 'border-slate-200 text-slate-500 hover:border-primary-300 dark:border-slate-700 dark:text-slate-400' }}"
                                               wire:key="admnship-{{ $group['shopId'] }}-{{ $value }}">
                                            <input type="radio" value="{{ $value }}" wire:model="shipStatus" class="sr-only">
                                            <x-icon name="{{ $icon }}" class="size-4" />
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    <label class="block">
                                        <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                            کد رهگیری پستی <span class="text-rose-500">*</span>
                                            <span class="font-normal text-slate-400">(الزامی برای «در حال ارسال»)</span>
                                        </span>
                                        <input type="text" wire:model="shipTracking" dir="ltr"
                                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                                               placeholder="کد رهگیری مرسوله پستی">
                                        @error('shipTracking') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                                    </label>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="button" wire:click="saveShipment" wire:loading.attr="disabled" wire:target="saveShipment"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                                        <span wire:loading wire:target="saveShipment" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                        ثبت وضعیت ارسال
                                    </button>
                                    <button type="button" wire:click="closeShipment"
                                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                        انصراف
                                    </button>
                                </div>
                            @else
                                {{-- summary mode --}}
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                        @if ($gSlice->tracking_code)
                                            کد رهگیری فعلی: <span dir="ltr" class="text-violet-600 dark:text-violet-400">{{ \App\Support\Fmt::toFa($gSlice->tracking_code) }}</span>
                                        @else
                                            کد رهگیری ثبت نشده است.
                                        @endif
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="openShipment({{ $group['shopId'] }})"
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-3.5 py-2 text-[11px] font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                                            <x-icon name="edit" class="size-3.5" />
                                            تغییر وضعیت ارسال
                                        </button>
                                        @if ($gSlice->shipment_status !== 'delivered')
                                            <button type="button" wire:click="openCancel({{ $group['shopId'] }})"
                                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 px-3.5 py-2 text-[11px] font-bold text-rose-500 transition hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950/40">
                                                <x-icon name="circle-x" class="size-3.5" />
                                                لغو سهم فروشگاه
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- ── order totals (all shops) ── --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-sm font-extrabold">
                    <x-icon name="file-text" class="size-4 text-primary-500" />
                    جمع کل سفارش
                </h3>
                <dl class="mt-3 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-400">جمع کالاها</dt>
                        <dd class="font-bold">{{ \App\Support\Fmt::money($order->items->sum(fn ($i) => $i->lineTotal())) }}</dd>
                    </div>
                    @if ($order->shipping_cost > 0)
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-400">هزینه ارسال</dt>
                            <dd class="font-bold">{{ \App\Support\Fmt::money($order->shipping_cost) }}</dd>
                        </div>
                    @endif
                    @if ($order->discount_amount > 0)
                        <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400">
                            <dt>تخفیف @if ($order->discount_code)({{ $order->discount_code }}) @endif</dt>
                            <dd class="font-extrabold">− {{ \App\Support\Fmt::money($order->discount_amount) }}</dd>
                        </div>
                    @endif
                    <div class="flex items-center justify-between border-t border-dashed border-slate-200 pt-2 dark:border-slate-700">
                        <dt class="font-bold text-slate-500">مبلغ کل</dt>
                        <dd class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::money($order->total_amount) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="space-y-5">
            {{-- ── customer & delivery ── --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
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
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
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

    {{-- ═══════════ cancel slice modal (with reason) ═══════════ --}}
    <x-modal-panel open="$wire.cancelShopId !== null" max-width="sm" close-js="__ipOpen = false; $wire.closeCancel()">
        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-rose-600/10 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                        <x-icon name="circle-x" class="size-4" />
                    </span>
                    لغو سهم فروشگاه از سفارش
                </h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeCancel()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="space-y-4 px-5 py-5">
            <p class="rounded-xl bg-amber-50 px-3.5 py-3 text-[11px] font-bold leading-5 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300">
                با لغو، کالاها به موجودی فروشگاه برمی‌گردند، سهم کسرشده از کیف پول فروشنده برای بازگشت به مشتری کنار گذاشته می‌شود و این اقدام قابل بازگشت نیست.
            </p>

            <label class="block">
                <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                    دلیل لغو <span class="text-rose-500">*</span>
                </span>
                <textarea wire:model="cancelReason" rows="3"
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                          placeholder="مثلاً: کالا موجود نیست / تقلب در سفارش / درخواست مشتری"></textarea>
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
