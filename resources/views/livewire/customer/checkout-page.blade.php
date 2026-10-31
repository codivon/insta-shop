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
                <div class="text-[11px] text-slate-400">اطلاعات تحویل و پرداخت آنلاین</div>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4">
        @if ($validItems->isEmpty())
            {{-- ── nothing purchasable ── --}}
            <div class="mt-10 rounded-3xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
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

            {{-- ── delivery info form ── --}}
            <form wire:submit="placeOrder">
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2">
                        <span class="grid size-9 place-items-center rounded-xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                            <x-icon name="package" class="size-4.5" />
                        </span>
                        <h3 class="text-xs font-extrabold">اطلاعات تحویل سفارش</h3>
                    </div>

                    <div class="mt-3 space-y-3">
                        <div>
                            <label for="receiver_name" class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">نام گیرنده</label>
                            <input id="receiver_name" type="text" wire:model="receiver_name" maxlength="80" placeholder="مثلاً: سارا محمدی"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            @error('receiver_name') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="delivery_phone" class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">شماره تماس گیرنده</label>
                            <input id="delivery_phone" type="tel" wire:model="delivery_phone" inputmode="tel" dir="ltr" placeholder="09123456789"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-right text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            @error('delivery_phone') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="delivery_address" class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">نشانی کامل</label>
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

                {{-- ── order summary ── --}}
                <div class="mt-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2">
                        <span class="grid size-9 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                            <x-icon name="file-text" class="size-4.5" />
                        </span>
                        <h3 class="text-xs font-extrabold">خلاصه سفارش</h3>
                        <span class="mr-auto text-[10px] text-slate-400">{{ \App\Support\Fmt::toFa($units) }} کالا</span>
                    </div>

                    <div class="mt-3 space-y-2">
                        @foreach ($validItems as $item)
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                                <div class="min-w-0">
                                    <div class="truncate text-[11px] font-bold">{{ $item->product->name }}</div>
                                    <div class="text-[9px] text-slate-400">{{ \App\Support\Fmt::moneyShort($item->product->price) }} × {{ \App\Support\Fmt::toFa($item->quantity) }}</div>
                                </div>
                                <div class="shrink-0 text-[11px] font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::moneyShort($item->lineTotal()) }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 space-y-1.5 border-t border-dashed border-slate-200 pt-3 text-xs dark:border-slate-700">
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>هزینه ارسال</span>
                            <span>پس‌کرایه — هنگام تحویل</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-500 dark:text-slate-400">مبلغ قابل پرداخت</span>
                            <span class="text-sm font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::money($total) }}</span>
                        </div>
                    </div>

                    {{-- ── pay button ── --}}
                    <button type="submit" wire:loading.attr="disabled" wire:target="placeOrder"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-600 py-3.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-[0.98] disabled:opacity-60">
                        <span wire:loading.remove wire:target="placeOrder" class="flex items-center gap-2">
                            <x-icon name="credit-card" class="size-4" />
                            پرداخت {{ \App\Support\Fmt::money($total) }}
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
</div>
