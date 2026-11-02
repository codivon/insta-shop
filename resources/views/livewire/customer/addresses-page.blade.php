<div class="ip-fade-up"
     x-data="{ toast: '' }"
     @addresses-toast.window="toast = $event.detail.message; setTimeout(() => toast = '', 2800)">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('shop.profile') }}" wire:navigate
                   class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                   aria-label="بازگشت به پروفایل">
                    <x-icon name="arrow-right" class="size-5" />
                </a>
                <div>
                    <div class="text-sm font-extrabold leading-5">آدرس‌های من</div>
                    <div class="text-[11px] text-slate-400">دفترچه نشانی برای ارسال سفارش‌ها</div>
                </div>
            </div>
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    <div class="px-4 pt-4">
        {{-- ── intro + create ── --}}
        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-primary-100 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400">
                    <x-icon name="map-pin" class="size-5" />
                </span>
                <div>
                    <div class="text-xs font-extrabold">نشانی‌های تحویل</div>
                    <div class="mt-0.5 text-[10px] leading-4 text-slate-400">آدرس پیش‌فرض به‌صورت خودکار در تکمیل خرید انتخاب می‌شود</div>
                </div>
            </div>
            <button type="button" wire:click="openCreateModal"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-2xl bg-primary-600 px-3.5 py-2.5 text-[11px] font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
                <x-icon name="plus" class="size-3.5" />
                آدرس جدید
            </button>
        </div>

        {{-- ── list ── --}}
        @if ($addresses->isEmpty())
            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                    <x-icon name="map-pin" class="size-7 text-slate-300 dark:text-slate-600" />
                </span>
                <p class="mt-3 text-xs font-extrabold">هنوز آدرسی ثبت نکرده‌اید</p>
                <p class="mt-1 text-[10px] text-slate-400">با دکمه «آدرس جدید» اولین نشانی تحویل را بسازید.</p>
            </div>
        @else
            <div class="mt-4 space-y-3">
                @foreach ($addresses as $address)
                    <div class="rounded-2xl border p-3.5 shadow-sm transition {{ $address->is_default
                        ? 'border-primary-300 bg-primary-50/40 dark:border-primary-800 dark:bg-primary-950/20'
                        : 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' }}"
                        wire:key="addr-{{ $address->id }}">

                        <div class="flex items-start gap-2.5">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $address->is_default
                                ? 'bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400'
                                : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                <x-icon name="{{ $address->title === 'محل کار' ? 'store' : 'home' }}" class="size-4.5" />
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="truncate text-xs font-extrabold">{{ $address->title ?: 'بدون عنوان' }}</span>
                                    @if ($address->is_default)
                                        <span class="rounded-full bg-primary-100 px-2 py-0.5 text-[9px] font-extrabold text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">پیش‌فرض</span>
                                    @endif
                                </div>

                                <p class="mt-1 text-[11px] font-bold leading-5 text-slate-600 dark:text-slate-300">{{ $address->fullLabel() }}</p>

                                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[9.5px] text-slate-400">
                                    @if ($address->recipient_name)
                                        <span class="flex items-center gap-1"><x-icon name="user" class="size-3" /> {{ $address->recipient_name }}</span>
                                    @endif
                                    @if ($address->phone)
                                        <span class="flex items-center gap-1" dir="ltr"><x-icon name="phone" class="size-3" /> {{ \App\Support\Fmt::toFa($address->phone) }}</span>
                                    @endif
                                    @if ($address->postal_code)
                                        <span class="flex items-center gap-1" dir="ltr"><x-icon name="file-text" class="size-3" /> {{ \App\Support\Fmt::toFa($address->postal_code) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- actions --}}
                        <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-2.5 dark:border-slate-800">
                            @if (! $address->is_default)
                                <button type="button" wire:click="makeDefault({{ $address->id }})"
                                        class="flex items-center gap-1 rounded-xl bg-slate-100 px-3 py-2 text-[10px] font-extrabold text-slate-600 transition hover:bg-primary-100 hover:text-primary-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-primary-950/60 dark:hover:text-primary-300">
                                    <x-icon name="check" class="size-3.5" />
                                    پیش‌فرض کن
                                </button>
                            @endif
                            <button type="button" wire:click="openEditModal({{ $address->id }})"
                                    class="flex items-center gap-1 rounded-xl bg-slate-100 px-3 py-2 text-[10px] font-extrabold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                <x-icon name="edit" class="size-3.5" />
                                ویرایش
                            </button>
                            <button type="button" wire:click="askDelete({{ $address->id }})"
                                    class="mr-auto flex items-center gap-1 rounded-xl bg-rose-50 px-3 py-2 text-[10px] font-extrabold text-rose-600 transition hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-950/70">
                                <x-icon name="trash" class="size-3.5" />
                                حذف
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="pb-4"></div>
    </div>

    {{-- ══════════ CREATE / EDIT MODAL ══════════ --}}
    <x-modal-panel open="$wire.showModal" maxWidth="md" inFrame
                   labelledby="address-modal-title"
                   closeJs="__ipOpen = false; $wire.closeAddModal()">

        <x-slot:header>
            <div class="flex items-center justify-between px-4 py-3">
                <h2 id="address-modal-title" class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                        <x-icon name="map-pin" class="size-4" />
                    </span>
                    {{ $editId ? 'ویرایش آدرس' : 'آدرس جدید' }}
                </h2>
                <button type="button" class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-800"
                        x-on:click="__ipOpen = false; $wire.closeAddModal()" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <form wire:submit="saveAddress" class="space-y-3 p-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">عنوان (اختیاری)</label>
                    <input type="text" wire:model="title" maxlength="60" placeholder="خانه / محل کار"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">نام گیرنده <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="recipient_name" maxlength="80" placeholder="مثلاً: سارا محمدی"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    @error('recipient_name') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">شماره همراه <span class="text-rose-500">*</span></label>
                <input type="tel" wire:model="phone" inputmode="tel" dir="ltr" maxlength="14" placeholder="09123456789"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-right text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('phone') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">استان <span class="text-rose-500">*</span></label>
                    <select wire:model.live="provinceId"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                        <option value="0">انتخاب استان…</option>
                        @foreach ($provinces as $province)
                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                    @error('provinceId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">شهر <span class="text-rose-500">*</span></label>
                    <select wire:model.live="cityId"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                        <option value="0">انتخاب شهر…</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                    @error('cityId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">نشانی کامل <span class="text-rose-500">*</span></label>
                <textarea wire:model="address" rows="3" maxlength="500" placeholder="خیابان، کوچه، نام ساختمان…"
                          class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs leading-5 outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800"></textarea>
                @error('address') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">کد پستی</label>
                    <input type="text" wire:model="postal_code" inputmode="numeric" dir="ltr" maxlength="10" placeholder="—"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-right text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    @error('postal_code') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">پلاک</label>
                    <input type="text" wire:model="plaque" inputmode="numeric" maxlength="20" placeholder="—"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">واحد</label>
                    <input type="text" wire:model="unit" inputmode="numeric" maxlength="20" placeholder="—"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                </div>
            </div>

            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
                <input type="checkbox" wire:model="is_default" class="size-4 border-slate-300 text-primary-600 focus:ring-primary-500/30 dark:border-slate-600">
                <span class="text-[11px] font-bold">این آدرس، پیش‌فرض من باشد</span>
            </label>

            <div class="flex gap-2 pt-1">
                <button type="button" x-on:click="__ipOpen = false; $wire.closeAddModal()"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-3 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                    انصراف
                </button>
                <button type="submit" wire:loading.attr="disabled" wire:target="saveAddress"
                        class="flex-1 rounded-xl bg-primary-600 py-3 text-xs font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                    ذخیره آدرس
                </button>
            </div>
        </form>
    </x-modal-panel>

    {{-- ══════════ DELETE CONFIRM ══════════ --}}
    <x-modal-panel open="$wire.confirmDeleteId !== null" maxWidth="sm" inFrame zClass="z-[80]"
                   closeJs="__ipOpen = false; $wire.confirmDeleteId = null">
        <div class="p-5 text-center">
            <span class="mx-auto grid size-12 place-items-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                <x-icon name="trash" class="size-5" />
            </span>
            <p class="mt-3 text-xs font-extrabold">حذف این آدرس؟</p>
            <p class="mt-1 text-[10px] leading-5 text-slate-400">این نشانی از دفترچه آدرس‌های شما برداشته می‌شود.</p>

            <div class="mt-4 flex gap-2">
                <button type="button" x-on:click="__ipOpen = false; $wire.confirmDeleteId = null"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    انصراف
                </button>
                <button type="button" wire:click="deleteAddress" wire:loading.attr="disabled" wire:target="deleteAddress"
                        class="flex-1 rounded-xl bg-rose-600 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-rose-600/25 transition hover:bg-rose-700 active:scale-95 disabled:opacity-60">
                    حذف کن
                </button>
            </div>
        </div>
    </x-modal-panel>

    {{-- toast --}}
    <div x-show="toast" x-transition.opacity x-cloak
         class="fixed bottom-24 left-1/2 z-40 w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95">
        <span x-text="toast"></span>
    </div>
</div>
