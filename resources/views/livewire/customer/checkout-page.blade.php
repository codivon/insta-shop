<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('shop.cart') }}" wire:navigate
               class="grid size-10 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800"
               aria-label="بازگشت به سبد خرید">
                <x-icon name="arrow-left" class="size-5" />
            </a>
            <div>
                <div class="text-sm font-extrabold leading-5">تکمیل خرید و پرداخت</div>
                <div class="text-[11px] text-slate-400">روش ارسال، کد تخفیف و درگاه پرداخت</div>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4">
        @if ($validItems->isEmpty())
            {{-- ── nothing purchasable ── --}}
            <div class="mt-10 rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-slate-100 dark:bg-slate-800">
                    <x-icon name="cart" class="size-8 text-slate-300 dark:text-slate-600" />
                </span>
                <p class="mt-4 text-sm font-extrabold">کالای قابل خریدی در سبد نیست</p>
                <p class="mt-1 text-[11px] text-slate-400">برای تکمیل خرید حداقل یک محصول موجود لازم است.</p>
                <a href="{{ route('shop.cart') }}" wire:navigate
                   class="mt-5 inline-block rounded-2xl bg-primary-600 px-6 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-95">
                    بازگشت به سبد خرید
                </a>
            </div>
        @else
            @error('cart')
                <div class="mb-3 rounded-2xl border border-rose-200 bg-rose-50/70 p-3 text-[11px] font-bold leading-5 text-rose-600 dark:border-rose-900 dark:bg-rose-950/30 dark:text-rose-400">
                    {{ $message }}
                </div>
            @enderror

            <form wire:submit="placeOrder">
                {{-- ── delivery: آدرس منتخب از دفترچه (73) ── --}}
                @if ($addresses->isNotEmpty())
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center gap-2">
                            <span class="grid size-9 place-items-center rounded-xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                                <x-icon name="map-pin" class="size-4.5" />
                            </span>
                            <h3 class="text-xs font-extrabold">آدرس تحویل سفارش</h3>
                        </div>

                        @if ($selectedAddress)
                            <div class="mt-3 rounded-2xl border border-primary-200 bg-primary-50/50 p-3.5 dark:border-primary-800 dark:bg-primary-950/20" wire:key="sel-addr-{{ $selectedAddress->id }}">
                                <div class="flex items-start gap-2.5">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400">
                                        <x-icon name="{{ $selectedAddress->title === 'محل کار' ? 'store' : 'home' }}" class="size-4" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="truncate text-[11px] font-extrabold">{{ $selectedAddress->title ?: 'آدرس تحویل' }}</span>
                                            @if ($selectedAddress->is_default)
                                                <span class="rounded-full bg-primary-100 px-1.5 py-0.5 text-[8.5px] font-extrabold text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">پیش‌فرض</span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-[10.5px] font-bold leading-5 text-slate-600 dark:text-slate-300">{{ $selectedAddress->fullLabel() }}</p>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[9px] text-slate-400">
                                            @if ($selectedAddress->recipient_name)
                                                <span class="flex items-center gap-1"><x-icon name="user" class="size-2.5" /> {{ $selectedAddress->recipient_name }}</span>
                                            @endif
                                            @if ($selectedAddress->phone)
                                                <span class="flex items-center gap-1" dir="ltr"><x-icon name="phone" class="size-2.5" /> {{ \App\Support\Fmt::toFa($selectedAddress->phone) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50/80 p-3 text-[10.5px] font-bold text-amber-700 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300">
                                برای ادامه، یکی از آدرس‌های خود را انتخاب کنید.
                            </div>
                        @endif

                        @error('selectedAddressId') <p class="mt-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                        <button type="button" wire:click="openAddressModal"
                                class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border border-dashed border-primary-300 bg-primary-50/50 py-2.5 text-[11px] font-extrabold text-primary-600 transition hover:bg-primary-100/60 dark:border-primary-800 dark:bg-primary-950/20 dark:text-primary-400 dark:hover:bg-primary-950/40">
                            <x-icon name="edit" class="size-3.5" />
                            تغییر آدرس
                        </button>
                    </div>
                @else
                    {{-- ── delivery info form (بدون آدرس ذخیره‌شده) ── --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2">
                        <span class="grid size-9 place-items-center rounded-xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                            <x-icon name="package" class="size-4.5" />
                        </span>
                        <h3 class="text-xs font-extrabold">اطلاعات تحویل سفارش</h3>
                    </div>

                    <div class="mt-3 space-y-3">
                        <div>
                            <label for="receiver_name" class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">نام گیرنده <span class="text-rose-500">*</span></label>
                            <input id="receiver_name" type="text" wire:model="receiver_name" maxlength="80" placeholder="مثلاً: سارا محمدی"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            @error('receiver_name') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="delivery_phone" class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">شماره تماس گیرنده <span class="text-rose-500">*</span></label>
                            <input id="delivery_phone" type="tel" wire:model="delivery_phone" inputmode="tel" dir="ltr" placeholder="09123456789"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-right text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            @error('delivery_phone') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="delivery_address" class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">نشانی کامل <span class="text-rose-500">*</span></label>
                            <textarea id="delivery_address" wire:model="delivery_address" rows="3" maxlength="500"
                                      placeholder="استان، شهر، خیابان، پلاک و واحد…"
                                      class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs leading-5 outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800"></textarea>
                            @error('delivery_address') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="postal_code" class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">کد پستی (اختیاری)</label>
                            <input id="postal_code" type="text" wire:model="postal_code" inputmode="numeric" dir="ltr" maxlength="10" placeholder="—"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-right text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            @error('postal_code') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                @endif

                {{-- ── خلاصه سفارش — گروه‌بندی بر اساس فروشگاه + حامل هر فروشگاه ── --}}
                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2">
                        <span class="grid size-9 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                            <x-icon name="file-text" class="size-4.5" />
                        </span>
                        <h3 class="text-xs font-extrabold">خلاصه سفارش</h3>
                        <span class="mr-auto text-[10px] text-slate-400">{{ \App\Support\Fmt::toFa($units) }} کالا از {{ \App\Support\Fmt::toFa($shopGroups->count()) }} فروشگاه</span>
                    </div>

                    <div class="mt-3 space-y-4">
                        @foreach ($shopGroups as $group)
                            @php $shopId = (int) $group['shop']->id; @endphp
                            <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800" wire:key="ck-group-{{ $shopId }}">

                                {{-- shop header --}}
                                <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/70 px-3 py-2.5 dark:border-slate-800 dark:bg-slate-800/40">
                                    <span class="grid size-7 shrink-0 place-items-center rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                                        <x-icon name="store" class="size-3.5" />
                                    </span>
                                    <span class="min-w-0 truncate text-[11px] font-extrabold">{{ $group['shop']?->name ?? 'کالاهای بدون فروشگاه' }}</span>
                                    <span class="mr-auto shrink-0 text-[9.5px] font-bold text-slate-400">جمع {{ \App\Support\Fmt::moneyShort($group['subtotal']) }} تومان</span>
                                </div>

                                {{-- items with thumbnails --}}
                                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach ($group['items'] as $item)
                                        <div class="flex items-center gap-2.5 px-3 py-2.5" wire:key="ck-item-{{ $item->id }}">
                                            <a href="{{ $item->product->publicUrl() }}" wire:navigate
                                               class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                                @if ($item->product->mainImage)
                                                    <img src="{{ $item->product->mainImage->url() }}" alt="{{ $item->product->name }}" loading="lazy" class="size-full object-cover">
                                                @else
                                                    <x-icon name="package" class="size-5 text-slate-300 dark:text-slate-600" />
                                                @endif
                                            </a>
                                            <div class="min-w-0 flex-1">
                                                <div class="truncate text-[11px] font-bold">{{ $item->product->name }}</div>
                                                @if ($item->variant_id > 0)
                                                    <div class="mt-0.5 truncate text-[9px] font-bold text-slate-500 dark:text-slate-400">{{ $item->variant?->label() ?? 'تنوع نامعتبر' }}</div>
                                                @endif
                                                <div class="text-[9px] text-slate-400">{{ \App\Support\Fmt::moneyShort($item->unitPrice()) }} × {{ \App\Support\Fmt::toFa($item->quantity) }}</div>
                                            </div>
                                            <div class="shrink-0 text-[11px] font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::moneyShort($item->lineTotal()) }}</div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- ── حامل این فروشگاه (اجباری برای کالای فیزیکی) ── --}}
                                @if ($group['needsCarrier'])
                                    <div class="border-t border-slate-100 bg-slate-50/60 p-3 dark:border-slate-800 dark:bg-slate-800/30">
                                        <p class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold">
                                            <x-icon name="truck" class="size-3.5 text-primary-500" />
                                            روش ارسال این فروشگاه
                                            <span class="text-rose-500">*</span>
                                        </p>

                                        @if ($group['carriers']->isEmpty())
                                            <div class="rounded-xl border border-amber-200 bg-amber-50/80 px-3 py-2 text-[10px] font-bold leading-5 text-amber-700 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300">
                                                @if ($group['allCarriers']->isNotEmpty())
                                                    روش‌های ارسال این فروشگاه هنوز تعرفه وزنی ثبت نکرده‌اند؛ تا اطلاع فروشنده امکان خرید ارسالی نیست.
                                                @else
                                                    این فروشگاه هنوز روش ارسالی ثبت نکرده است؛ تا اطلاع فروشنده امکان خرید حضوری‌نیست.
                                                @endif
                                            </div>
                                        @else
                                            <div class="space-y-2" role="radiogroup" aria-label="روش ارسال {{ $group['shop']?->name }}">
                                                @foreach ($group['carriers'] as $carrier)
                                                    @php
                                                        $picked   = (int) ($carrierPicks[$shopId] ?? 0) === (int) $carrier->id;
                                                        $cost     = $carrier->costFor((int) $group['subtotal'], (int) $group['weight'], $this->isInProvince($carrier));
                                                        $postpaid = $carrier->isPayOnDelivery();
                                                        $inProv   = $this->isInProvince($carrier);
                                                    @endphp
                                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border p-2.5 transition {{ $picked
                                                        ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                                                        : 'border-slate-200 bg-white hover:border-primary-300 dark:border-slate-700 dark:bg-slate-900' }}"
                                                        wire:key="carrier-{{ $carrier->id }}">
                                                        <input type="radio" name="carrier-{{ $shopId }}" value="{{ $carrier->id }}"
                                                               wire:model.live="carrierPicks.{{ $shopId }}"
                                                               class="size-4 shrink-0 border-slate-300 text-primary-600 focus:ring-primary-500/30 dark:border-slate-600">
                                                        <span class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800">
                                                            @if ($carrier->image_path)
                                                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($carrier->image_path) }}" alt="{{ $carrier->title }}" class="size-full object-cover">
                                                            @else
                                                                <x-icon name="truck" class="size-4" />
                                                            @endif
                                                        </span>
                                                        <span class="min-w-0 flex-1">
                                                            <span class="block truncate text-[11px] font-extrabold">{{ $carrier->title }}</span>
                                                            <span class="mt-0.5 block truncate text-[9px] font-bold text-slate-400">{{ $carrier->deliveryRangeLabel() }} · {{ $carrier->waiting_time ?: $carrier->coverageLabel() }}</span>
                                                            <span class="mt-0.5 inline-flex items-center gap-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-[8px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                                <x-icon name="map-pin" class="size-2.5" />
                                                                {{ $inProv ? 'درون استانی' : 'بیرون استانی' }}
                                                            </span>
                                                        </span>
                                                        <span class="shrink-0 text-left">
                                                            @if ($postpaid)
                                                                <span class="block rounded-full bg-amber-100 px-2 py-0.5 text-[9px] font-extrabold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">پس‌کرایه</span>
                                                                <span class="mt-0.5 block text-[8.5px] font-bold text-slate-400">پرداخت هنگام تحویل</span>
                                                            @elseif ($cost < 1)
                                                                <span class="block rounded-full bg-emerald-100 px-2 py-0.5 text-[9px] font-extrabold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">رایگان</span>
                                                            @else
                                                                <span class="block text-[11px] font-extrabold text-slate-600 dark:text-slate-300">{{ \App\Support\Fmt::moneyShort($cost) }}</span>
                                                                <span class="block text-[8.5px] font-bold text-slate-400">تومان</span>
                                                            @endif
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('carrierPicks.'.$shopId) <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- ── کد تخفیف ── --}}
                    <div class="mt-4 rounded-2xl border border-dashed border-slate-300 p-3 dark:border-slate-700">
                        <p class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold">
                            <x-icon name="tag" class="size-3.5 text-primary-500" />
                            کد تخفیف
                        </p>

                        @if ($discountApplied)
                            <div class="flex items-center justify-between gap-2 rounded-xl border border-emerald-200 bg-emerald-50/80 px-3 py-2.5 dark:border-emerald-900 dark:bg-emerald-950/30">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="grid size-7 shrink-0 place-items-center rounded-full bg-emerald-500 text-white">
                                        <x-icon name="check" class="size-3.5" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-mono text-[11px] font-extrabold text-emerald-700 dark:text-emerald-300" dir="ltr">{{ mb_strtoupper(trim($discountCode)) }}</span>
                                        <span class="block text-[9.5px] font-bold text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::money($discountAmount) }} تومان تخفیف اعمال شد</span>
                                    </span>
                                </span>
                                <button type="button" wire:click="removeDiscount"
                                        class="grid size-8 shrink-0 place-items-center rounded-lg text-emerald-600 transition hover:bg-emerald-100 hover:text-rose-500 dark:hover:bg-emerald-900/40"
                                        aria-label="حذف کد تخفیف">
                                    <x-icon name="x" class="size-4" />
                                </button>
                            </div>
                        @else
                            <div class="flex gap-2">
                                <input type="text" wire:model="discountCode" dir="ltr" maxlength="60" placeholder="SALE2025"
                                       class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 font-mono text-xs font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                                <button type="button" wire:click="applyDiscount" wire:loading.attr="disabled" wire:target="applyDiscount"
                                        class="shrink-0 rounded-xl bg-slate-900 px-4 py-2.5 text-[11px] font-extrabold text-white transition hover:bg-slate-800 active:scale-95 disabled:opacity-50 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                                    اعمال
                                </button>
                            </div>
                            @error('discountCode') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            @if (! $errors->has('discountCode') && $discountMessage !== '')
                                <p class="mt-1.5 text-[10px] font-bold text-slate-400">{{ $discountMessage }}</p>
                            @endif
                        @endif
                    </div>

                    {{-- ── درگاه پرداخت ── --}}
                    @if (count($gateways) > 0)
                        <div class="mt-4">
                            <p class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold">
                                <x-icon name="credit-card" class="size-3.5 text-primary-500" />
                                درگاه پرداخت
                                <span class="text-rose-500">*</span>
                            </p>

                            <div class="grid grid-cols-2 gap-2 {{ count($gateways) > 3 ? 'sm:grid-cols-3' : '' }}" role="radiogroup" aria-label="انتخاب درگاه پرداخت">
                                @foreach ($gateways as $key => $label)
                                    @php $gwPicked = $gateway === $key; @endphp
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-2xl border p-3 transition {{ $gwPicked
                                        ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                                        : 'border-slate-200 bg-white hover:border-primary-300 dark:border-slate-700 dark:bg-slate-900' }}"
                                        wire:key="gw-{{ $key }}">
                                        <input type="radio" name="gateway" value="{{ $key }}" wire:model.live="gateway" class="peer sr-only">
                                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white shadow-sm dark:bg-slate-800">
                                            @include('partials.gateway-logo', ['gateway' => $key])
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-[11px] font-extrabold">{{ $label }}</span>
                                            <span class="mt-0.5 block text-[8.5px] font-bold text-slate-400">پرداخت امن آنلاین</span>
                                        </span>
                                        <span class="grid size-4 shrink-0 place-items-center rounded-full border-2 transition {{ $gwPicked ? 'border-primary-600' : 'border-slate-300 dark:border-slate-600' }}"
                                              aria-hidden="true">
                                            <span class="size-2 rounded-full bg-primary-600 transition {{ $gwPicked ? 'opacity-100' : 'opacity-0' }}"></span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ── totals ── --}}
                    <div class="mt-4 space-y-1.5 border-t border-dashed border-slate-200 pt-3 text-xs dark:border-slate-700">
                        <div class="flex items-center justify-between text-[10.5px] text-slate-400">
                            <span>جمع کالاها ({{ \App\Support\Fmt::toFa($units) }})</span>
                            <span class="font-bold">{{ \App\Support\Fmt::money($total) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[10.5px] text-slate-400">
                            <span>هزینه ارسال</span>
                            @if ($shippingCost > 0)
                                <span class="font-bold text-slate-600 dark:text-slate-300">{{ \App\Support\Fmt::money($shippingCost) }}</span>
                            @else
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">رایگان</span>
                            @endif
                        </div>
                        @if ($hasPayOnDelivery)
                            <div class="flex items-center justify-between text-[10px] text-amber-600 dark:text-amber-400">
                                <span>یکی از حامل‌ها پس‌کرایه است</span>
                                <span>پرداخت هنگام تحویل</span>
                            </div>
                        @endif
                        @if ($discountApplied && $liveDiscount[0] > 0)
                            <div class="flex items-center justify-between text-[10.5px] text-emerald-600 dark:text-emerald-400">
                                <span class="flex items-center gap-1">
                                    <x-icon name="tag" class="size-3" />
                                    تخفیف ({{ mb_strtoupper(trim($discountCode)) }})
                                </span>
                                <span class="font-extrabold">− {{ \App\Support\Fmt::money($liveDiscount[0]) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between border-t border-slate-100 pt-2 dark:border-slate-800">
                            <span class="font-bold text-slate-500 dark:text-slate-400">مبلغ قابل پرداخت</span>
                            <span class="text-sm font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::money($payable) }}</span>
                        </div>
                    </div>

                    {{-- ── pay button ── --}}
                    <button type="submit" wire:loading.attr="disabled" wire:target="placeOrder"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-600 py-3.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-[0.98] disabled:opacity-60">
                        <span wire:loading.remove wire:target="placeOrder" class="flex items-center gap-2">
                            <x-icon name="credit-card" class="size-4" />
                            پرداخت {{ \App\Support\Fmt::money($payable) }}
                        </span>
                        <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                            <x-icon name="rotate" class="size-4 animate-spin" />
                            در حال اتصال به درگاه…
                        </span>
                    </button>

                    <p class="mt-2.5 flex items-center justify-center gap-1 text-center text-[9px] text-slate-400">
                        <x-icon name="shield" class="size-3" />
                        پرداخت در درگاه امن بانکی انجام می‌شود.
                    </p>
                </div>
            </form>

            <div class="pb-2"></div>
        @endif
    </div>

    {{-- ═══════════════ تغییر آدرس — انتخاب از دفترچه + افزودن آدرس (73) ═══════════════ --}}
    <x-modal-panel open="$wire.showAddressModal" maxWidth="md"
               labelledby="addr-modal-title"
               closeJs="__ipOpen = false; $wire.closeAddressModal()">

    <x-slot:header>
        <div class="flex items-center justify-between px-4 py-3">
            <h2 id="addr-modal-title" class="flex items-center gap-2 text-xs font-extrabold">
                <span class="grid size-8 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                    <x-icon name="map-pin" class="size-4" />
                </span>
                آدرس‌های من
            </h2>
            <button type="button" class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-800"
                    onclick="__ipOpen = false; $wire.closeAddressModal()" aria-label="بستن">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    </x-slot:header>

    <div class="p-4">
        {{-- ── address list ── --}}
        <div class="space-y-2.5">
            @foreach ($addresses as $address)
                <button type="button" wire:click="pickAddress({{ $address->id }})" wire:key="pick-{{ $address->id }}"
                        class="flex w-full items-start gap-2.5 rounded-2xl border p-3 text-right transition {{ (int) $selectedAddressId === (int) $address->id
                            ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                            : 'border-slate-200 bg-white hover:border-primary-300 dark:border-slate-700 dark:bg-slate-900' }}">
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl {{ (int) $selectedAddressId === (int) $address->id
                        ? 'bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400'
                        : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                        <x-icon name="{{ $address->title === 'محل کار' ? 'store' : 'home' }}" class="size-4" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center gap-1.5">
                            <span class="truncate text-[11px] font-extrabold">{{ $address->title ?: 'بدون عنوان' }}</span>
                            @if ($address->is_default)
                                <span class="rounded-full bg-primary-100 px-1.5 py-0.5 text-[8px] font-extrabold text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">پیش‌فرض</span>
                            @endif
                        </span>
                        <span class="mt-0.5 block text-[10px] font-bold leading-5 text-slate-500 dark:text-slate-400">{{ $address->fullLabel() }}</span>
                        @if ($address->recipient_name)
                            <span class="mt-0.5 block text-[9px] text-slate-400">گیرنده: {{ $address->recipient_name }} @if($address->phone) · {{ \App\Support\Fmt::toFa($address->phone) }} @endif</span>
                        @endif
                    </span>
                    @if ((int) $selectedAddressId === (int) $address->id)
                        <span class="grid size-5 shrink-0 place-items-center rounded-full bg-primary-600 text-white">
                            <x-icon name="check" class="size-3" />
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- ── افزودن آدرس (inline) ── --}}
        <div class="mt-4 border-t border-dashed border-slate-200 pt-3 dark:border-slate-700">
            @if (! $showAddressForm)
                <button type="button" wire:click="toggleAddressForm"
                        class="flex w-full items-center justify-center gap-1.5 rounded-xl border border-dashed border-primary-300 bg-primary-50/50 py-2.5 text-[11px] font-extrabold text-primary-600 transition hover:bg-primary-100/60 dark:border-primary-800 dark:bg-primary-950/20 dark:text-primary-400 dark:hover:bg-primary-950/40">
                    <x-icon name="plus" class="size-3.5" />
                    افزودن آدرس
                </button>
            @else
                <form wire:submit="saveNewAddress" class="space-y-2.5 rounded-2xl border border-slate-200 bg-slate-50/60 p-3 dark:border-slate-700 dark:bg-slate-900/60">
                    <p class="text-[11px] font-extrabold">آدرس جدید</p>

                    <div class="grid grid-cols-2 gap-2.5">
                        <input type="text" wire:model="newTitle" maxlength="60" placeholder="عنوان (خانه / محل کار)"
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                        <input type="text" wire:model="newRecipient" maxlength="80" placeholder="نام گیرنده *"
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    </div>
                    @error('newRecipient') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                    <input type="tel" wire:model="newPhone" inputmode="tel" dir="ltr" maxlength="14" placeholder="شماره همراه * (09123456789)"
                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-right text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    @error('newPhone') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                    <div class="grid grid-cols-2 gap-2.5">
                        <select wire:model.live="newProvinceId"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            <option value="0">استان *</option>
                            @foreach ($newProvinces as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="newCityId"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            <option value="0">شهر *</option>
                            @foreach ($newCities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('newProvinceId') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    @error('newCityId') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                    <textarea wire:model="newAddress" rows="2" maxlength="500" placeholder="نشانی کامل * (خیابان، کوچه…)"
                              class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs leading-5 outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800"></textarea>
                    @error('newAddress') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                    <div class="grid grid-cols-3 gap-2.5">
                        <input type="text" wire:model="newPostal" inputmode="numeric" dir="ltr" maxlength="10" placeholder="کد پستی"
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-right text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                        <input type="text" wire:model="newPlaque" inputmode="numeric" maxlength="20" placeholder="پلاک"
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                        <input type="text" wire:model="newUnit" inputmode="numeric" maxlength="20" placeholder="واحد"
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    </div>
                    @error('newPostal') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                    <div class="flex gap-2 pt-0.5">
                        <button type="button" wire:click="toggleAddressForm"
                                class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                            انصراف
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveNewAddress"
                                class="flex-1 rounded-xl bg-primary-600 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                            ذخیره و انتخاب
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-modal-panel>
</div>
