<div class="ip-fade-up space-y-4">

    {{-- ── flash toast ── --}}
    @if (session('admin-users-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-users-flash') }}
        </div>
    @endif

    {{-- ── toolbar ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="relative w-full lg:max-w-xs">
                <x-icon name="search" class="absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="search" wire:model.live.debounce.350ms="search" placeholder="نام، موبایل یا شناسه اینستاگرام…"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-4 pr-10 text-sm shadow-sm transition focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800"
                       aria-label="جستجوی فروشنده">
                <span wire:loading wire:target="search" class="absolute left-3 top-1/2 -translate-y-1/2">
                    <x-icon name="rotate" class="size-4 animate-spin text-primary-500" />
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2" role="group" aria-label="فیلتر وضعیت">
                @foreach ([
                    ['key' => 'all',       'label' => 'همه ('.\App\Support\Fmt::fa($stats['all']).')'],
                    ['key' => 'pending',   'label' => 'در انتظار ('.\App\Support\Fmt::fa($stats['pending']).')'],
                    ['key' => 'approved',  'label' => 'تأییدشده ('.\App\Support\Fmt::fa($stats['approved']).')'],
                    ['key' => 'suspended', 'label' => 'غیرفعال ('.\App\Support\Fmt::fa($stats['suspended']).')'],
                ] as $tab)
                    <button type="button" wire:click="$set('status', '{{ $tab['key'] }}')"
                            class="rounded-xl px-3.5 py-2 text-xs font-bold transition {{ $status === $tab['key']
                                ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── pending banner ── --}}
    @if ($stats['pending'] > 0)
        <div class="flex items-center gap-2.5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-700 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
            <x-icon name="alert" class="size-4 shrink-0" />
            {{ \App\Support\Fmt::fa($stats['pending']) }} فروشنده منتظر بررسی شماست — پس از تأیید، فروشگاه او در اپ مشتری نمایش داده می‌شود.
        </div>
    @endif

    {{-- ── table ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($sellers->isEmpty())
            <div class="flex flex-col items-center gap-3 py-16 text-center">
                <span class="grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <x-icon name="store" class="size-7" />
                </span>
                <p class="text-sm font-bold text-slate-500">فروشنده‌ای یافت نشد</p>
                <p class="text-xs text-slate-400">{{ $search !== '' ? 'عبارت جستجو یا فیلتر را تغییر دهید.' : 'با اولین ثبت‌نام فروشنده در اپ، اینجا نمایش داده می‌شود.' }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                            <th class="px-5 py-3 font-semibold">فروشنده</th>
                            <th class="px-3 py-3 font-semibold">موبایل</th>
                            <th class="px-3 py-3 font-semibold">فروشگاه</th>
                            <th class="px-3 py-3 font-semibold">محصولات</th>
                            <th class="px-3 py-3 font-semibold">عضویت</th>
                            <th class="px-3 py-3 font-semibold">وضعیت</th>
                            <th class="px-3 py-3 font-semibold">کمیسیون</th>
                            <th class="px-5 py-3 font-semibold">اقدامات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach ($sellers as $seller)
                            @php $displayName = $seller->full_name ?: ($seller->instagram_username ? '@'.$seller->instagram_username : 'بدون نام'); @endphp
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="s-{{ $seller->id }}">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-xs font-bold text-white">
                                            {{ mb_substr($displayName, 1, 1) }}
                                        </span>
                                        <div>
                                            <span class="block text-xs font-bold">{{ $displayName }}</span>
                                            @if ($seller->instagram_username)
                                                <span class="mt-0.5 flex items-center gap-1 text-[10px] text-slate-400" dir="ltr">
                                                    <x-icon name="instagram" class="size-3" />
                                                    {{ $seller->instagram_username }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ \App\Support\Fmt::toFa($seller->phone) }}</td>
                                <td class="px-3 py-3.5">
                                    @if ($seller->shop)
                                        <span class="block text-xs font-semibold">{{ $seller->shop->name }}</span>
                                        <span class="text-[10px] {{ $seller->shop->status === 'active' ? 'text-emerald-500' : 'text-slate-400' }}">
                                            {{ $seller->shop->status === 'active' ? 'فعال' : 'مکث‌شده' }}
                                        </span>
                                    @else
                                        <span class="text-[11px] text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-xs font-bold">{{ \App\Support\Fmt::fa($seller->products_count) }}</td>
                                <td class="px-3 py-3.5 text-[11px] text-slate-400">{{ \App\Support\JDate::date($seller->created_at) }}</td>
                                <td class="px-3 py-3.5">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $seller->statusClasses() }}">{{ $seller->statusLabel() }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-[11px]">
                                    @if ($seller->hasCommissionOverride())
                                        <span class="rounded-full bg-amber-50 px-2 py-1 font-bold text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                                            {{ $seller->commission_type === 'fixed'
                                                ? 'ثابت '.\App\Support\Fmt::money($seller->commission_value)
                                                : \App\Support\Fmt::toFa($seller->commission_value).'٪' }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">پیش‌فرض سراسری</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <a href="{{ route('admin.users.sellers.show', $seller) }}" wire:navigate
                                           class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-[11px] font-bold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                            نمایش
                                        </a>
                                        <button type="button" wire:click="openEditModal({{ $seller->id }})"
                                                class="rounded-lg bg-primary-50 px-2.5 py-1.5 text-[11px] font-bold text-primary-600 transition hover:bg-primary-100 dark:bg-primary-950/40 dark:text-primary-400">
                                            ویرایش
                                        </button>
                                        @if ($seller->status === 'pending')
                                            <button type="button" wire:click="approve({{ $seller->id }})"
                                                    class="rounded-lg bg-emerald-600 px-3 py-1.5 text-[11px] font-bold text-white shadow-sm shadow-emerald-600/25 transition hover:bg-emerald-700">
                                                تأیید
                                            </button>
                                            <button type="button" wire:click="reject({{ $seller->id }})"
                                                    wire:confirm="درخواست «{{ $displayName }}» رد شود؟"
                                                    class="rounded-lg bg-rose-50 px-3 py-1.5 text-[11px] font-bold text-rose-600 transition hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-400">
                                                رد
                                            </button>
                                        @elseif ($seller->status === 'approved')
                                            <button type="button" wire:click="suspend({{ $seller->id }})"
                                                    wire:confirm="فروشنده «{{ $displayName }}» غیرفعال شود؟ فروشگاه او از دید مشتریان خارج می‌شود."
                                                    class="rounded-lg bg-rose-50 px-3 py-1.5 text-[11px] font-bold text-rose-600 transition hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-400">
                                                غیرفعال‌سازی
                                            </button>
                                        @else
                                            <button type="button" wire:click="approve({{ $seller->id }})"
                                                    class="rounded-lg bg-emerald-50 px-3 py-1.5 text-[11px] font-bold text-emerald-600 transition hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                فعال‌سازی مجدد
                                            </button>
                                        @endif

                                        <button type="button" wire:click="askDelete({{ $seller->id }})"
                                                class="rounded-lg bg-rose-50 px-2.5 py-1.5 text-[11px] font-bold text-rose-600 transition hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-400">
                                            حذف
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
        نمایش {{ \App\Support\Fmt::fa(min($sellers->count(), $perPage)) }} از {{ \App\Support\Fmt::fa($stats['all']) }} فروشنده
    </p>

    {{-- ══════════ EDIT MODAL (76 + 79) ══════════ --}}
    <x-modal-panel open="$wire.showEditModal" maxWidth="lg" labelledby="seller-edit-title"
                   closeJs="__ipOpen = false; $wire.closeEditModal()">
        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h2 id="seller-edit-title" class="flex items-center gap-2.5 text-sm font-extrabold">
                    <span class="grid size-9 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                        <x-icon name="edit" class="size-4" />
                    </span>
                    ویرایش فروشنده
                    @if ($editSeller?->shop)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            فروشگاه: {{ $editSeller->shop->name }}
                        </span>
                    @endif
                </h2>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeEditModal()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <form wire:submit="saveSellerEdit" class="grid gap-4 p-5 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">نام و نام خانوادگی</label>
                <input type="text" wire:model="editFullName" maxlength="80" placeholder="مثلاً: علی رضایی"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('editFullName') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">شناسه اینستاگرام</label>
                <input type="text" wire:model="editInstagram" dir="ltr" maxlength="60" placeholder="username"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('editInstagram') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">موبایل <span class="text-rose-500">*</span></label>
                <input type="tel" wire:model="editPhone" inputmode="tel" dir="ltr" maxlength="14" placeholder="09123456789"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-right text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('editPhone') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">وضعیت</label>
                <select wire:model="editStatus"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    <option value="pending">در انتظار تأیید</option>
                    <option value="approved">تأیید شده</option>
                    <option value="suspended">غیرفعال</option>
                </select>
            </div>

            {{-- ── کمیسیون اختصاصی (79) ── --}}
            <div class="rounded-2xl border border-dashed border-amber-300 bg-amber-50/50 p-4 sm:col-span-2 dark:border-amber-800 dark:bg-amber-950/20">
                <p class="flex items-center gap-2 text-xs font-extrabold text-amber-700 dark:text-amber-300">
                    <x-icon name="coins" class="size-4" />
                    کمیسیون اختصاصی این فروشنده
                </p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">نوع کمیسیون</label>
                        <select wire:model.live="editCommissionType"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            <option value="">پیش‌فرض سراسری</option>
                            <option value="percent">درصدی (٪)</option>
                            <option value="fixed">مبلغ ثابت (تومان)</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">مقدار</label>
                        <input type="text" inputmode="numeric" wire:model.blur="editCommissionValue" placeholder="0" @disabled($editCommissionType === '')
                               class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-primary-400 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800">
                        @error('editCommissionValue') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <p class="mt-2 text-[10px] leading-5 text-slate-400">هزینهٔ ارسال و کد تخفیف هرگز مشمول کمیسیون نمی‌شوند.</p>
            </div>

            <div class="flex gap-2 sm:col-span-2">
                <button type="button" x-on:click="__ipOpen = false; $wire.closeEditModal()"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    انصراف
                </button>
                <button type="submit" wire:loading.attr="disabled" wire:target="saveSellerEdit"
                        class="flex-1 rounded-xl bg-primary-600 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                    ذخیره تغییرات
                </button>
            </div>
        </form>
    </x-modal-panel>

    {{-- ══════════ DELETE CONFIRM (75) — type the phone to confirm ══════════ --}}
    <x-modal-panel open="$wire.confirmDeleteId !== null" maxWidth="md" zClass="z-[80]"
                   closeJs="__ipOpen = false; $wire.confirmDeleteId = null">
        <div class="p-5">
            <div class="flex items-start gap-3">
                <span class="grid size-11 shrink-0 place-items-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                    <x-icon name="alert" class="size-5" />
                </span>
                <div>
                    <p class="text-sm font-extrabold">حذف کامل فروشنده «{{ $deleteSeller?->full_name ?: '@'.$deleteSeller?->instagram_username }}»</p>
                    <p class="mt-1.5 text-[11px] leading-5 text-slate-400">
                        فروشگاه، محصولات، دسته‌بندی‌ها، روش‌های ارسال، فیلترها و کدهای تخفیف او نیز حذف می‌شوند و این عمل بازگشت‌پذیر نیست.
                    </p>
                </div>
            </div>

            <label class="mt-4 block text-[11px] font-bold text-slate-500 dark:text-slate-400">
                برای تأیید، شماره موبایل فروشنده را بنویسید: <span dir="ltr" class="font-mono text-rose-500">{{ $deleteSeller?->phone }}</span>
            </label>
            <input type="text" wire:model="deleteConfirmText" dir="ltr" placeholder="09…"
                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-right text-sm font-mono outline-none transition focus:border-rose-400 dark:border-slate-700 dark:bg-slate-800">

            <div class="mt-4 flex gap-2">
                <button type="button" x-on:click="__ipOpen = false; $wire.confirmDeleteId = null"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    انصراف
                </button>
                <button type="button" wire:click="deleteSeller" wire:loading.attr="disabled" wire:target="deleteSeller"
                        @disabled($deleteConfirmText !== (string) ($deleteSeller?->phone ?? ''))
                        class="flex-1 rounded-xl bg-rose-600 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-rose-600/25 transition hover:bg-rose-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40">
                    حذف قطعی
                </button>
            </div>
        </div>
    </x-modal-panel>
</div>
