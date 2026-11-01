<div class="ip-fade-up"
     x-data="{ toast: '' }"
     @shipping-toast.window="toast = $event.detail.message; setTimeout(() => toast = '', 2800)">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('seller.profile') }}" wire:navigate
                   class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                   aria-label="بازگشت به پروفایل">
                    <x-icon name="arrow-right" class="size-5" />
                </a>
                <div>
                    <div class="text-sm font-extrabold leading-5">حمل و نقل</div>
                    <div class="text-[11px] text-slate-400">روش‌های ارسال و هزینه‌های فروشگاه</div>
                </div>
            </div>
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    <div class="px-4 pt-4">
        {{-- ── intro + create ── --}}
        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 shrink-0 place-items-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <x-icon name="truck" class="size-5" />
                </span>
                <div>
                    <div class="text-xs font-extrabold">روش‌های ارسال</div>
                    <div class="mt-0.5 text-[10px] leading-4 text-slate-400">هزینه، مناطق تحت پوشش و زمان ارسال هر روش را اینجا مدیریت کنید</div>
                </div>
            </div>
            <button type="button" wire:click="openCreateModal"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-2xl bg-primary-600 px-3.5 py-2.5 text-[11px] font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
                <x-icon name="plus" class="size-3.5" />
                جدید
            </button>
        </div>

        {{-- ── list ── --}}
        @if ($carriers->isEmpty())
            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                    <x-icon name="truck" class="size-7 text-slate-300 dark:text-slate-600" />
                </span>
                <p class="mt-3 text-xs font-extrabold">هنوز روش ارسالی ثبت نکرده‌اید</p>
                <p class="mt-1 text-[10px] text-slate-400">با دکمه «جدید» اولین روش ارسال فروشگاه را بسازید.</p>
            </div>
        @else
            <div class="mt-4 space-y-3">
                @foreach ($carriers as $carrier)
                    <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="carrier-{{ $carrier->id }}">
                        <div class="flex items-start gap-3">
                            {{-- thumb --}}
                            <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                @if ($carrier->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($carrier->image_path) }}" alt="{{ $carrier->title }}" class="size-full object-cover">
                                @else
                                    <x-icon name="truck" class="size-5 text-slate-400" />
                                @endif
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <div class="truncate text-xs font-extrabold">{{ $carrier->title }}</div>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[8.5px] font-black {{ $carrier->is_active
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $carrier->is_active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </div>

                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[9.5px] font-bold text-slate-500 dark:text-slate-400">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 dark:bg-slate-800">
                                        <x-icon name="map-pin" class="size-2.5" />
                                        {{ $carrier->coverageLabel() }}
                                    </span>
                                    @if ($carrier->waiting_time)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 dark:bg-slate-800">
                                            <x-icon name="clock" class="size-2.5" />
                                            {{ $carrier->waiting_time }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 dark:bg-slate-800">
                                        <x-icon name="calendar" class="size-2.5" />
                                        {{ $carrier->deliveryRangeLabel() }}
                                    </span>
                                    @if ($carrier->carriage_forward)
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">پس کرایه</span>
                                    @elseif ($carrier->base_price > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2 py-0.5 text-primary-600 dark:bg-primary-950/40 dark:text-primary-300">
                                            <x-icon name="coins" class="size-2.5" />
                                            {{ \App\Support\Fmt::moneyShort($carrier->base_price) }} تومان
                                        </span>
                                    @endif
                                    @if ($carrier->free_shipping_price > 0 || $carrier->free_shipping_weight > 0)
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">ارسال رایگان شرطی</span>
                                    @endif
                                </div>
                            </div>

                            {{-- actions --}}
                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" wire:click="toggleCarrier({{ $carrier->id }})"
                                        class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-800"
                                        aria-label="{{ $carrier->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}">
                                    <x-icon name="{{ $carrier->is_active ? 'eye' : 'eye-off' }}" class="size-4" />
                                </button>
                                <button type="button" wire:click="openEditModal({{ $carrier->id }})"
                                        class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-primary-950/40"
                                        aria-label="ویرایش">
                                    <x-icon name="edit" class="size-4" />
                                </button>
                                <button type="button" wire:click="deleteCarrier({{ $carrier->id }})" wire:confirm="این روش ارسال حذف شود؟"
                                        class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40"
                                        aria-label="حذف">
                                    <x-icon name="trash" class="size-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mb-2 mt-4 flex items-start gap-2 rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-3 text-[10px] font-semibold leading-5 text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
            <x-icon name="info" class="mt-0.5 size-4 shrink-0 text-primary-500" />
            <span>
                استان‌ها و شهرها به‌صورت پیش‌فرض روی سیستم نصب هستند و افزودن یا کم‌کردن آن‌ها فقط از <b>پنل مدیریت</b> انجام می‌شود.
            </span>
        </div>
    </div>

    {{-- ═══════════════ CREATE / EDIT MODAL (sample layout — 3 tabs) ═══════════════
         Mobile: full-height bottom sheet (no empty strip at the top).
         Footer buttons are pinned OUTSIDE the scroll area — only the
         tab contents scroll. ═══════════════ --}}
    <div x-show="$wire.showCreateModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/60 p-0 backdrop-blur-sm sm:items-center sm:p-4"
         role="dialog" aria-modal="true" aria-label="روش ارسال">
        <div class="relative flex h-full max-h-full w-full flex-col overflow-hidden rounded-t-2xl border border-slate-200 bg-white shadow-2xl sm:h-auto sm:max-h-[92dvh] sm:rounded-2xl dark:border-slate-800 dark:bg-slate-900"
             x-data="{ tab: 'base' }"
             x-init="
                $wire.on('shipping-modal-open', () => { tab = 'base' });
                $wire.on('shipping-modal-errors', (e) => { tab = e.tab || 'base' });
             ">

            {{-- modal header --}}
            <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-5 py-3 pt-[max(0.75rem,env(safe-area-inset-top))] dark:border-slate-800">
                <h3 class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                        <x-icon name="truck" class="size-4" />
                    </span>
                    {{ $editId ? 'ویرایش روش ارسال' : 'روش ارسال جدید' }}
                </h3>
                <button type="button" wire:click="closeCreateModal" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>

            {{-- tabs --}}
            <div class="shrink-0 px-5 pb-3 pt-3">
                <div class="flex gap-1 rounded-2xl bg-slate-100 p-1 dark:bg-slate-800">
                    <button type="button" @click="tab = 'base'" :aria-selected="tab === 'base'" role="tab"
                            class="flex-1 rounded-xl px-3 py-2 text-[11px] font-extrabold transition"
                            :class="tab === 'base' ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                        اطلاعات کلی
                    </button>
                    <button type="button" @click="tab = 'zones'" :aria-selected="tab === 'zones'" role="tab"
                            class="flex-1 rounded-xl px-3 py-2 text-[11px] font-extrabold transition"
                            :class="tab === 'zones' ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                        مناطق و هزینه‌ها
                    </button>
                    <button type="button" @click="tab = 'ranges'" :aria-selected="tab === 'ranges'" role="tab"
                            class="flex-1 rounded-xl px-3 py-2 text-[11px] font-extrabold transition"
                            :class="tab === 'ranges' ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                        بازه زمانی ارسال
                    </button>
                </div>
            </div>

            {{-- body: the FORM is a flex column — only the middle section
                 scrolls, the footer (دکمه‌ها) stays pinned below it --}}
            <form wire:submit="createCarrier" class="flex min-h-0 flex-1 flex-col">
                <div class="scroll-slim min-h-0 flex-1 overflow-y-auto px-5 pb-4 pt-4">
                {{-- ── tab 1: اطلاعات کلی ── --}}
                <div x-show="tab === 'base'" class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3" role="tabpanel">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">عنوان <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.blur="createTitle" maxlength="120" placeholder="مثال: پست پیشتاز"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800">
                        @error('createTitle') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">تصویر روش ارسال</label>
                        <input type="file" accept="image/*" wire:model="createImage"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-semibold text-slate-500 outline-none transition file:me-2 file:rounded-lg file:border-0 file:bg-slate-200 file:px-2.5 file:py-1 file:text-[10px] file:font-bold focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800 dark:file:bg-slate-700 dark:file:text-slate-200">
                        @error('createImage') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        @if ($editId && $carriers->firstWhere('id', $editId)?->image_path && ! $removeImage)
                            <label class="mt-1.5 flex items-center gap-1.5 text-[10px] font-bold text-rose-500">
                                <input type="checkbox" wire:model="removeImage" class="size-3.5 accent-rose-500">
                                حذف تصویر فعلی
                            </label>
                        @endif
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">وضعیت</label>
                        <select wire:model="createIsActive" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            <option value="1">فعال</option>
                            <option value="0">غیر فعال</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">زمان انتظار</label>
                        <input type="text" wire:model.blur="createWaitingTime" maxlength="120" placeholder="مثال: ۲۴ ساعت بعد از ثبت سفارش"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">حداکثر وزن بسته (گرم)</label>
                        <input type="text" inputmode="numeric" wire:model.blur="createMaxPackageWeight" placeholder="مثال: 2000"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                        @error('createMaxPackageWeight') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">حداقل وزن بسته (گرم)</label>
                        <input type="text" inputmode="numeric" wire:model.blur="createMinPackageWeight" placeholder="مثال: 100"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                        @error('createMinPackageWeight') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">توضیحات</label>
                        <textarea wire:model.blur="createDescription" rows="3" maxlength="2000" placeholder="توضیح دلخواه برای نمایش به خریدار..."
                                  class="scroll-slim w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold leading-6 outline-none transition focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800"></textarea>
                    </div>
                </div>

                {{-- ── tab 2: مناطق و هزینه‌ها ── --}}
                <div x-show="tab === 'zones'" x-cloak class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3" role="tabpanel">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">استان فروشگاه <span class="text-rose-500">*</span></label>
                        <select wire:model.live="createProvinceId" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            <option value="">انتخاب کنید</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                            @endforeach
                        </select>
                        @error('createProvinceId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">شهر فروشگاه</label>
                        <select wire:model="createCityId" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            <option value="">انتخاب کنید</option>
                            @foreach ($originCities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex cursor-pointer items-center gap-2 self-end rounded-2xl bg-slate-50 px-3.5 py-3 text-[11px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        <input type="checkbox" wire:model="createCarrigeForward" class="size-4 accent-primary-600">
                        پس کرایه (کرایه پس از ارسال مشخص شود)
                    </label>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">هزینه ارسال پایه (تومان)</label>
                        <input type="text" inputmode="numeric" wire:model.blur="createBasePrice" placeholder="مثال: 45000"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                        <p class="mt-1 text-[10px] leading-4 text-slate-400 dark:text-slate-500">در تسویهٔ آنلاین از مشتری گرفته می‌شود؛ با فعال‌بودن پس‌کرایه نادیده گرفته می‌شود.</p>
                        @error('createBasePrice') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">ارسال رایگان برای وزن‌های بزرگتر از (گرم)</label>
                        <input type="text" inputmode="numeric" wire:model.blur="createFreeShippingWeight" placeholder="مثال: 1000"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                        @error('createFreeShippingWeight') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">ارسال رایگان برای مبالغ بزرگتر از (تومان)</label>
                        <input type="text" inputmode="numeric" wire:model.blur="createFreeShippingPrice" placeholder="مثال: 5000000"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                        @error('createFreeShippingPrice') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">هزینه اضافی برای بارهای سنگین‌تر (هر کیلوگرم)</label>
                        <input type="text" inputmode="numeric" wire:model.blur="createExtraCost" placeholder="مثال: 25000"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                        @error('createExtraCost') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">نوع شهرهای تحت پوشش</label>
                        <select wire:model.live="createCoveredCities" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            <option value="all">همه</option>
                            <option value="select_city">انتخاب شهر</option>
                        </select>
                    </div>

                    @if ($createCoveredCities === 'select_city')
                        {{-- city picker: province filter + checkbox list + selected chips --}}
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-3 sm:col-span-2 lg:col-span-3 dark:border-slate-700 dark:bg-slate-800/40">
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="text-[10.5px] font-extrabold text-slate-500 dark:text-slate-300">انتخاب شهرهای تحت پوشش:</label>
                                <select wire:model.live="createZoneProvinceId"
                                        class="min-w-44 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                                    <option value="">فیلتر استان</option>
                                    @foreach ($provinces as $province)
                                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-[10px] font-bold text-slate-400">{{ \App\Support\Fmt::toFa(count($createCoveredCityIds)) }} شهر انتخاب شده</span>
                            </div>

                            @if ($zoneCities->isNotEmpty())
                                <div class="scroll-slim mt-2.5 grid max-h-44 grid-cols-2 gap-1.5 overflow-y-auto sm:grid-cols-3 lg:grid-cols-4">
                                    @foreach ($zoneCities as $city)
                                        <label class="flex cursor-pointer items-center gap-1.5 rounded-lg bg-white px-2 py-1.5 text-[10px] font-bold text-slate-600 shadow-sm transition hover:bg-primary-50 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                                            <input type="checkbox" wire:model.live="createCoveredCityIds" value="{{ $city->id }}" class="size-3.5 shrink-0 accent-primary-600">
                                            <span class="truncate">{{ $city->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            @error('createCoveredCityIds') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                            @if ($coveredNames->isNotEmpty())
                                <div class="mt-2.5 border-t border-dashed border-slate-200 pt-2.5 dark:border-slate-700">
                                    <div class="text-[10px] font-bold text-slate-400">{{ $coveredProvinces ?: 'شهرهای انتخابی' }}:</div>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        @foreach ($coveredNames as $city)
                                            <button type="button" @click="$wire.createCoveredCityIds = $wire.createCoveredCityIds.filter(id => id !== {{ $city->id }})"
                                                    class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2.5 py-1 text-[10px] font-extrabold text-primary-600 transition hover:bg-rose-50 hover:text-rose-500 dark:bg-primary-950/40 dark:text-primary-300"
                                                    aria-label="حذف {{ $city->name }}">
                                                {{ $city->name }}
                                                <x-icon name="x" class="size-2.5" />
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- ── tab 3: بازه زمانی ارسال ── --}}
                <div x-show="tab === 'ranges'" x-cloak class="grid grid-cols-1 gap-3.5 sm:grid-cols-2" role="tabpanel">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">نوع بازه زمانی ارسال</label>
                        <select wire:model.live="createDeliveryTimeType" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            <option value="default">متن پیشفرض</option>
                            <option value="user_select">انتخاب توسط کاربر</option>
                        </select>
                        <p class="mt-1 text-[10px] leading-4 text-slate-400 dark:text-slate-500">انتخاب کنید که بازه زمانی ثابت باشد یا کاربر انتخاب کند</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">شروع بازه زمانی از (روز بعد از سفارش)</label>
                        <select wire:model="createStartDaysAfterOrder" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            @for ($d = 1; $d <= 11; $d++)
                                <option value="{{ $d }}">از {{ \App\Support\Fmt::toFa($d) }} روز بعد</option>
                            @endfor
                        </select>
                    </div>
                    @if ($createDeliveryTimeType === 'default')
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">بازه زمانی ارسال (پیشفرض)</label>
                            <input type="text" wire:model.blur="createDefaultDeliveryRange" maxlength="150" placeholder="مثال: ارسال در ۳ الی ۶ روز کاری"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                            <p class="mt-1 text-[10px] leading-4 text-slate-400 dark:text-slate-500">متنی که به کاربر نمایش داده می‌شود</p>
                            @error('createDefaultDeliveryRange') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
                </div><!-- /scrollable modal body -->

                {{-- pinned modal footer — OUTSIDE the scroll area so the
                     دکمه‌ها همیشه پایین و در دسترس می‌مانند --}}
                <div class="flex shrink-0 items-center justify-end gap-2.5 border-t border-slate-100 bg-white px-5 pb-[max(0.875rem,env(safe-area-inset-bottom))] pt-3.5 dark:border-slate-800 dark:bg-slate-900">
                    <button type="button" wire:click="closeCreateModal" class="rounded-2xl border border-slate-200 px-4 py-2.5 text-[11px] font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        انصراف
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="createCarrier"
                            class="inline-flex items-center gap-1.5 rounded-2xl bg-primary-600 px-4 py-2.5 text-[11px] font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                        <x-icon name="plus" class="size-3.5" />
                        {{ $editId ? 'ذخیره تغییرات' : 'ایجاد روش ارسال' }}
                    </button>
                </div>
            </form>

            <div wire:loading wire:target="createCarrier" class="absolute inset-0 grid place-items-center bg-white/60 backdrop-blur-[2px] dark:bg-slate-900/60">
                <span class="inline-block size-8 animate-spin rounded-full border-2 border-primary-600 border-t-transparent"></span>
            </div>
        </div>
    </div>

    {{-- toast --}}
    <div x-show="toast" x-transition.opacity x-cloak
         class="fixed bottom-24 left-1/2 z-[60] w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95">
        <span x-text="toast"></span>
    </div>
</div>
