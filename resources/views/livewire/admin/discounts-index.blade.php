<div class="ip-fade-up space-y-4">

    {{-- ── flash toast ── --}}
    @if (session('admin-catalog-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-catalog-flash') }}
        </div>
    @endif

    {{-- ── stats ── --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['label' => 'کل کدها', 'value' => \App\Support\Fmt::fa($stats['all']), 'icon' => 'tag', 'tone' => 'from-rose-500 to-pink-600'],
            ['label' => 'فعال', 'value' => \App\Support\Fmt::fa($stats['published']), 'icon' => 'check', 'tone' => 'from-emerald-500 to-teal-600'],
            ['label' => 'فروشگاه‌های دارای کد', 'value' => \App\Support\Fmt::fa($stats['shops']), 'icon' => 'store', 'tone' => 'from-amber-500 to-orange-600'],
            ['label' => 'دفعات مصرف', 'value' => \App\Support\Fmt::fa($stats['used']), 'icon' => 'trending-up', 'tone' => 'from-sky-500 to-cyan-600'],
        ] as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-[10.5px] font-bold text-slate-400">{{ $card['label'] }}</span>
                    <span class="grid size-8 place-items-center rounded-lg bg-gradient-to-bl {{ $card['tone'] }} text-white shadow-sm">
                        <x-icon name="{{ $card['icon'] }}" class="size-4" />
                    </span>
                </div>
                <div class="mt-2 text-lg font-black">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ── toolbar ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="relative w-full lg:max-w-xs">
                <x-icon name="search" class="absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="search" wire:model.live.debounce.350ms="search" placeholder="کد یا عنوان تخفیف…"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-4 pr-10 text-sm shadow-sm transition focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                       aria-label="جستجوی کد تخفیف">
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="shopId" aria-label="فیلتر فروشگاه"
                        class="max-w-44 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    <option value="0">همه فروشگاه‌ها</option>
                    @foreach ($shopOptions as $shopOption)
                        <option value="{{ $shopOption->id }}">{{ $shopOption->name }}</option>
                    @endforeach
                </select>

                <button type="button" wire:click="openCreateModal"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
                    <x-icon name="plus" class="size-4" />
                    کد تخفیف جدید
                </button>
            </div>
        </div>
    </div>

    {{-- ── table ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($discounts->isEmpty())
            <div class="flex flex-col items-center gap-3 py-16 text-center">
                <span class="grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <x-icon name="tag" class="size-7" />
                </span>
                <p class="text-sm font-bold text-slate-500">کد تخفیفی یافت نشد</p>
                <p class="text-xs text-slate-400">با دکمه «کد تخفیف جدید» اولین کد را بسازید.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                            <th class="px-5 py-3 font-semibold">کد</th>
                            <th class="px-3 py-3 font-semibold">عنوان</th>
                            <th class="px-3 py-3 font-semibold">فروشگاه مالک</th>
                            <th class="px-3 py-3 font-semibold">مقدار</th>
                            <th class="px-3 py-3 font-semibold">مصرف</th>
                            <th class="px-3 py-3 font-semibold">اعتبار</th>
                            <th class="px-3 py-3 font-semibold">وضعیت</th>
                            <th class="px-5 py-3 font-semibold">اقدام</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach ($discounts as $discount)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="d-{{ $discount->id }}">
                                <td class="px-5 py-3.5">
                                    <span class="rounded-lg bg-slate-900 px-2.5 py-1 font-mono text-[11px] font-extrabold text-white dark:bg-white dark:text-slate-900" dir="ltr">{{ $discount->code }}</span>
                                </td>
                                <td class="px-3 py-3.5 max-w-40">
                                    <span class="block truncate text-xs font-bold">{{ $discount->title }}</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[10.5px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        <x-icon name="store" class="size-3" />
                                        {{ $discount->shop?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-xs font-extrabold text-primary-600 dark:text-primary-400">
                                    {{ $discount->type === 'percent' ? \App\Support\Fmt::toFa($discount->percent).'٪' : \App\Support\Fmt::moneyShort($discount->price) }}
                                </td>
                                <td class="px-3 py-3.5 text-xs">{{ \App\Support\Fmt::fa($discount->used_count) }}@if ($discount->quantity > 0)<span class="text-slate-400"> / {{ \App\Support\Fmt::fa($discount->quantity) }}</span>@endif</td>
                                <td class="px-3 py-3.5 text-[10.5px] text-slate-400">
                                    @if ($discount->ends_at)
                                        تا {{ \App\Support\JDate::date($discount->ends_at) }}
                                    @else
                                        بدون محدودیت
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $discount->published
                                        ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $discount->published ? 'فعال' : 'پنهان' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" wire:click="openEditModal({{ $discount->id }})"
                                                class="grid size-8 place-items-center rounded-lg bg-primary-50 text-primary-600 transition hover:bg-primary-100 dark:bg-primary-950/40 dark:text-primary-400"
                                                aria-label="ویرایش">
                                            <x-icon name="edit" class="size-3.5" />
                                        </button>
                                        <button type="button" wire:click="togglePublished({{ $discount->id }})"
                                                class="grid size-8 place-items-center rounded-lg bg-slate-100 text-slate-500 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300"
                                                aria-label="{{ $discount->published ? 'پنهان کردن' : 'فعال کردن' }}">
                                            <x-icon name="{{ $discount->published ? 'eye-off' : 'eye' }}" class="size-3.5" />
                                        </button>
                                        <button type="button" wire:click="deleteDiscount({{ $discount->id }})"
                                                wire:confirm="کد «{{ $discount->code }}» حذف شود؟"
                                                class="grid size-8 place-items-center rounded-lg bg-rose-50 text-rose-600 transition hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-400"
                                                aria-label="حذف">
                                            <x-icon name="trash" class="size-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($hasMore)
                <div class="border-t border-slate-100 p-4 text-center dark:border-slate-800">
                    <button type="button" wire:click="loadMore" wire:loading.attr="disabled"
                            class="rounded-xl border border-slate-200 px-5 py-2 text-xs font-bold text-slate-600 transition hover:border-primary-400 hover:text-primary-600 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300">
                        نمایش بیشتر
                    </button>
                </div>
            @endif
        @endif
    </div>

    <p class="text-center text-[11px] text-slate-400">
        نمایش {{ \App\Support\Fmt::fa(min($discounts->count(), $perPage)) }} از {{ \App\Support\Fmt::fa($stats['all']) }} کد تخفیف
    </p>

    {{-- ══════════ CREATE / EDIT DISCOUNT MODAL (75 + 78) ══════════ --}}
    <x-modal-panel open="$wire.showModal" maxWidth="xl" labelledby="discount-modal-title"
                   closeJs="__ipOpen = false; $wire.closeModal()">
        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h2 id="discount-modal-title" class="flex items-center gap-2.5 text-sm font-extrabold">
                    <span class="grid size-9 place-items-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">
                        <x-icon name="tag" class="size-4" />
                    </span>
                    {{ $editId ? 'ویرایش کد تخفیف' : 'کد تخفیف جدید' }}
                </h2>
                <button type="button" onclick="__ipOpen = false; $wire.closeModal()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <form wire:submit="saveDiscount" class="grid gap-4 p-5 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">فروشگاه مالک <span class="text-rose-500">*</span></label>
                <select wire:model="formShopId"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    <option value="0">انتخاب فروشگاه…</option>
                    @foreach ($shopOptions as $shopOption)
                        <option value="{{ $shopOption->id }}">{{ $shopOption->name }}</option>
                    @endforeach
                </select>
                @error('formShopId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">عنوان <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="formTitle" maxlength="120" placeholder="مثلاً: جشنواره زمستانی"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('formTitle') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">کد <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="formCode" dir="ltr" maxlength="60" placeholder="SALE2025"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-right font-mono text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('formCode') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">نوع تخفیف</label>
                <select wire:model.live="formType"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    <option value="amount">مبلغ ثابت (تومان)</option>
                    <option value="percent">درصدی (٪)</option>
                </select>
            </div>

            @if ($formType === 'amount')
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">مبلغ تخفیف (تومان) <span class="text-rose-500">*</span></label>
                    <input type="text" inputmode="numeric" wire:model.blur="formPrice" placeholder="مثلاً: 50000"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    @error('formPrice') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>
            @else
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">درصد تخفیف <span class="text-rose-500">*</span></label>
                    <input type="text" inputmode="numeric" wire:model.blur="formPercent" placeholder="مثلاً: 20"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    @error('formPercent') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">سقف تخفیف (تومان)</label>
                    <input type="text" inputmode="numeric" wire:model.blur="formCeiling" placeholder="اختیاری"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    @error('formCeiling') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>
            @endif

            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">تاریخ شروع</label>
                <input type="date" wire:model="formStartsAt" dir="ltr"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('formStartsAt') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">تاریخ پایان</label>
                <input type="date" wire:model="formEndsAt" dir="ltr"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('formEndsAt') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">حداقل مبلغ سفارش (تومان)</label>
                <input type="text" inputmode="numeric" wire:model.blur="formLeastPrice" placeholder="0 = بدون محدودیت"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('formLeastPrice') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">سقف تعداد مصرف (کل)</label>
                <input type="text" inputmode="numeric" wire:model.blur="formQuantity" placeholder="0 = بی‌نهایت"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('formQuantity') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">توضیحات</label>
                <textarea wire:model="formDescription" rows="2" maxlength="2000" placeholder="اختیاری"
                          class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm leading-5 outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800"></textarea>
            </div>

            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-3 sm:col-span-2 dark:border-slate-700 dark:bg-slate-900">
                <input type="checkbox" wire:model="formPublished" class="size-4 border-slate-300 text-primary-600 focus:ring-primary-500/30 dark:border-slate-600">
                <span class="text-[11px] font-bold">این کد فعال باشد (در تکمیل خرید قابل استفاده است)</span>
            </label>

            <div class="flex gap-2 sm:col-span-2">
                <button type="button" onclick="__ipOpen = false; $wire.closeModal()"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    انصراف
                </button>
                <button type="submit" wire:loading.attr="disabled" wire:target="saveDiscount"
                        class="flex-1 rounded-xl bg-primary-600 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                    ذخیره کد تخفیف
                </button>
            </div>
        </form>
    </x-modal-panel>
</div>
