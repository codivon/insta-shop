<div class="ip-fade-up space-y-4"
     x-data="{ toast: '' }"
     @locations-toast.window="toast = $event.detail.message; setTimeout(() => toast = '', 2800)">

    {{-- ── page head ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-2xl bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                <x-icon name="map-pin" class="size-5" />
            </span>
            <div>
                <h1 class="text-base font-extrabold leading-6">استان‌ها و شهرها</h1>
                <p class="text-[11px] text-slate-400">موقعیت‌های پیش‌فرض سیستم — فقط از همین صفحه قابل افزودن یا حذف است</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="openCreate('province')"
                    class="inline-flex items-center gap-1.5 rounded-2xl bg-primary-600 px-3.5 py-2.5 text-[11px] font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
                <x-icon name="plus" class="size-3.5" />
                استان جدید
            </button>
            <button type="button" wire:click="openCreate('city')"
                    class="inline-flex items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-[11px] font-extrabold text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                <x-icon name="plus" class="size-3.5" />
                شهر جدید
            </button>
        </div>
    </div>

    {{-- ── stats + filters ── --}}
    <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex gap-1 rounded-xl bg-slate-100 p-1 dark:bg-slate-800" role="tablist" aria-label="نوع موقعیت">
            <button type="button" wire:click="$set('tab', 'provinces')" role="tab" :aria-selected="true"
                    class="rounded-lg px-3.5 py-2 text-[11px] font-extrabold transition {{ $tab === 'provinces' ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400' : 'text-slate-500 dark:text-slate-400' }}">
                استان‌ها ({{ \App\Support\Fmt::toFa($stats['provinces']) }})
            </button>
            <button type="button" wire:click="$set('tab', 'cities')" role="tab"
                    class="rounded-lg px-3.5 py-2 text-[11px] font-extrabold transition {{ $tab === 'cities' ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400' : 'text-slate-500 dark:text-slate-400' }}">
                شهرها ({{ \App\Support\Fmt::toFa($stats['cities']) }})
            </button>
        </div>

        <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجو بر اساس نام..."
               class="min-w-48 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">

        @if ($tab === 'cities')
            <select wire:model.live="provinceFilter"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                <option value="">همه استان‌ها</option>
                @foreach ($provinceOptions as $province)
                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- ── list card ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($tab === 'provinces')
            <div class="scroll-slim max-h-[62vh] overflow-y-auto">
                <table class="w-full text-right text-xs">
                    <thead class="sticky top-0 bg-slate-50 text-[10px] font-extrabold text-slate-500 dark:bg-slate-800/80 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3">استان</th>
                            <th class="px-4 py-3">تعداد شهرها</th>
                            <th class="px-4 py-3">ترتیب</th>
                            <th class="px-4 py-3">وضعیت</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($provinces as $province)
                            <tr wire:key="prov-{{ $province->id }}" class="transition hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-2.5 font-bold">{{ $province->name }}</td>
                                <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ \App\Support\Fmt::toFa($province->cities_count) }}</td>
                                <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ \App\Support\Fmt::toFa($province->ordering) }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="rounded-full px-2 py-0.5 text-[9px] font-black {{ $province->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $province->is_active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" wire:click="openEdit('province', {{ $province->id }})"
                                                class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-primary-950/40" aria-label="ویرایش">
                                            <x-icon name="edit" class="size-3.5" />
                                        </button>
                                        <button type="button" wire:click="deleteLocation('province', {{ $province->id }})"
                                                wire:confirm="حذف «{{ $province->name }}» همه شهرهایش را هم حذف می‌کند. مطمئنید؟"
                                                class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40" aria-label="حذف">
                                            <x-icon name="trash" class="size-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-[11px] font-bold text-slate-400">استانی یافت نشد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="scroll-slim max-h-[62vh] overflow-y-auto">
                <table class="w-full text-right text-xs">
                    <thead class="sticky top-0 bg-slate-50 text-[10px] font-extrabold text-slate-500 dark:bg-slate-800/80 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3">شهر</th>
                            <th class="px-4 py-3">استان</th>
                            <th class="px-4 py-3">ترتیب</th>
                            <th class="px-4 py-3">وضعیت</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($cities as $city)
                            <tr wire:key="city-{{ $city->id }}" class="transition hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-2.5 font-bold">{{ $city->name }}</td>
                                <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ $city->province_name }}</td>
                                <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ \App\Support\Fmt::toFa($city->ordering) }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="rounded-full px-2 py-0.5 text-[9px] font-black {{ $city->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $city->is_active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" wire:click="openEdit('city', {{ $city->id }})"
                                                class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-primary-950/40" aria-label="ویرایش">
                                            <x-icon name="edit" class="size-3.5" />
                                        </button>
                                        <button type="button" wire:click="deleteLocation('city', {{ $city->id }})"
                                                wire:confirm="شهر «{{ $city->name }}» حذف شود؟"
                                                class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40" aria-label="حذف">
                                            <x-icon name="trash" class="size-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-[11px] font-bold text-slate-400">شهری یافت نشد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ═══════════ create/edit modal ═══════════ --}}
    <div x-show="$wire.modalOpen" x-cloak
         class="fixed inset-0 z-50 grid place-items-center bg-slate-950/60 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <h3 class="text-sm font-extrabold">
                    {{ $editingId ? 'ویرایش '.($kind === 'city' ? 'شهر' : 'استان') : ($kind === 'city' ? 'شهر جدید' : 'استان جدید') }}
                </h3>
                <button type="button" wire:click="closeModal" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>

            <form wire:submit="save" class="space-y-3.5 px-5 py-4">
                @if ($kind === 'city')
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">استان <span class="text-rose-500">*</span></label>
                        <select wire:model="parentProvinceId"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            <option value="">انتخاب کنید</option>
                            @foreach ($provinceOptions as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                            @endforeach
                        </select>
                        @error('parentProvinceId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">نام <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model.blur="name" maxlength="80"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    @error('name') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">ترتیب نمایش</label>
                        <input type="number" wire:model.blur="ordering" min="0" max="99999"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    </div>
                    <label class="flex cursor-pointer items-center gap-2 self-end text-[11px] font-bold text-slate-600 dark:text-slate-300">
                        <input type="checkbox" wire:model="isActive" class="size-4 accent-primary-600">
                        فعال باشد
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3.5 dark:border-slate-800">
                    <button type="button" wire:click="closeModal"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-[11px] font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        انصراف
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="rounded-xl bg-primary-600 px-4 py-2.5 text-[11px] font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                        ذخیره
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- toast --}}
    <div x-show="toast" x-transition.opacity x-cloak
         class="fixed bottom-8 left-1/2 z-[60] w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-xs font-bold text-white shadow-2xl dark:bg-slate-800/95">
        <span x-text="toast"></span>
    </div>
</div>
