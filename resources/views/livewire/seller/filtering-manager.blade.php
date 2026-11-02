<div class="ip-fade-up"
     x-data="{ toast: '' }"
     @filters-toast.window="toast = $event.detail.message; setTimeout(() => toast = '', 2800)">
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
                    <div class="text-sm font-extrabold leading-5">فیلترینگ</div>
                    <div class="text-[11px] text-slate-400">فیلترهای صفحهٔ فروشگاه برای خریداران</div>
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
                    <x-icon name="filter" class="size-5" />
                </span>
                <div>
                    <div class="text-xs font-extrabold">فیلترهای فروشگاه</div>
                    <div class="mt-0.5 text-[10px] leading-4 text-slate-400">
                        هر فیلتر = یک عنوان + چند گروه مشخصه از محصولات خودت ({{ \App\Support\Fmt::toFa($productsCount) }} محصول فعال)
                    </div>
                </div>
            </div>
            <button type="button" wire:click="openCreateModal"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-2xl bg-primary-600 px-3.5 py-2.5 text-[11px] font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
                <x-icon name="plus" class="size-3.5" />
                فیلتر جدید
            </button>
        </div>

        {{-- ── list ── --}}
        @if ($filters->isEmpty())
            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                    <x-icon name="filter" class="size-7 text-slate-300 dark:text-slate-600" />
                </span>
                <p class="mt-3 text-xs font-extrabold">هنوز فیلتری نساخته‌اید</p>
                <p class="mt-1 text-[10px] text-slate-400">مثال: فیلتر «رنگ» از مشخصهٔ رنگ محصولات، یا «گارانتی» از مشخصهٔ گارانتی.</p>
            </div>
        @else
            <div class="mt-4 space-y-3">
                @foreach ($filters as $filter)
                    <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="filter-{{ $filter->id }}">
                        <div class="flex items-start gap-3">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                                <x-icon name="filter" class="size-5" />
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="truncate text-xs font-extrabold">{{ $filter->title }}</span>
                                    <button type="button" wire:click="togglePublished({{ $filter->id }})"
                                            class="shrink-0 rounded-full px-2 py-0.5 text-[8.5px] font-black transition {{ $filter->published
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $filter->published ? 'نمایش در فروشگاه' : 'پنهان' }}
                                    </button>
                                </div>

                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    @forelse ($filter->items as $item)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[9.5px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            {{ $item->spec_name }}
                                        </span>
                                    @empty
                                        <span class="text-[9.5px] font-bold text-amber-500">بدون مشخصه — ویرایش کنید</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" wire:click="openEditModal({{ $filter->id }})"
                                        class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-primary-950/40"
                                        aria-label="ویرایش">
                                    <x-icon name="edit" class="size-4" />
                                </button>
                                <button type="button" wire:click="askDelete({{ $filter->id }})"
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

        {{-- ── hint ── --}}
        <div class="mb-2 mt-4 flex items-start gap-2 rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-3 text-[10px] font-semibold leading-5 text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
            <x-icon name="info" class="mt-0.5 size-4 shrink-0 text-primary-500" />
            <span>
                مشخصه‌ها فقط از <b>محصولات خود شما</b> خوانده می‌شوند (رنگ، گارانتی، اندازه صفحه نمایش و…) — فیلترهای فروشندگان دیگر با فروشگاه شما تداخلی ندارند.
            </span>
        </div>

        <div class="pb-4"></div>
    </div>

    {{-- ══════════ CREATE / EDIT FILTER MODAL ══════════ --}}
    <x-modal-panel open="$wire.showModal" maxWidth="md" inFrame
                   labelledby="filter-modal-title"
                   closeJs="__ipOpen = false; $wire.closeFilterModal()">

        <x-slot:header>
            <div class="flex items-center justify-between px-4 py-3">
                <h2 id="filter-modal-title" class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                        <x-icon name="filter" class="size-4" />
                    </span>
                    {{ $editId ? 'ویرایش فیلتر' : 'فیلتر جدید' }}
                </h2>
                <button type="button" class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-800"
                        onclick="__ipOpen = false; $wire.closeFilterModal()" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <form wire:submit="saveFilter" class="space-y-3.5 p-4">
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">عنوان فیلتر <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="title" maxlength="80" placeholder="مثلاً: برند، رنگ، محدودهٔ قیمت…"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('title') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400">مشخصه‌های این فیلتر <span class="text-rose-500">*</span></label>
                    <button type="button" wire:click="openSpecPicker"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3 py-2 text-[10.5px] font-extrabold text-white transition hover:bg-slate-800 active:scale-95 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                        <x-icon name="plus" class="size-3.5" />
                        افزودن مشخصات
                    </button>
                </div>

                @if (count($specNames) === 0)
                    <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50/70 px-3 py-2.5 text-[10px] font-bold leading-5 text-amber-700 dark:border-amber-800 dark:bg-amber-950/20 dark:text-amber-300">
                        هنوز مشخصه‌ای اضافه نکرده‌اید — با «افزودن مشخصات» از میان مشخصه‌های محصولات خودتان انتخاب کنید.
                    </div>
                @else
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($specNames as $name)
                            <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2.5 py-1 text-[10px] font-extrabold text-primary-600 dark:bg-primary-950/40 dark:text-primary-300" wire:key="spec-{{ $loop->index }}">
                                {{ $name }}
                                <button type="button" wire:click="removeSpec('{{ $name }}')" class="text-primary-400 transition hover:text-rose-500" aria-label="حذف {{ $name }}">
                                    <x-icon name="x" class="size-3" />
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif
                @error('specNames') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
                <input type="checkbox" wire:model="published" class="size-4 border-slate-300 text-primary-600 focus:ring-primary-500/30 dark:border-slate-600">
                <span class="text-[11px] font-bold">نمایش این فیلتر در صفحهٔ فروشگاه</span>
            </label>

            <div class="flex gap-2 pt-1">
                <button type="button" onclick="__ipOpen = false; $wire.closeFilterModal()"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-3 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                    انصراف
                </button>
                <button type="submit" wire:loading.attr="disabled" wire:target="saveFilter"
                        class="flex-1 rounded-xl bg-primary-600 py-3 text-xs font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                    ذخیره فیلتر
                </button>
            </div>
        </form>
    </x-modal-panel>

    {{-- ══════════ SPEC PICKER (searchable) ══════════ --}}
    <x-modal-panel open="$wire.showSpecPicker" maxWidth="md" inFrame zClass="z-[80]"
                   labelledby="spec-picker-title"
                   closeJs="__ipOpen = false; $wire.closeSpecPicker()">

        <x-slot:header>
            <div class="flex items-center justify-between px-4 py-3">
                <h2 id="spec-picker-title" class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
                        <x-icon name="search" class="size-4" />
                    </span>
                    انتخاب مشخصات
                </h2>
                <button type="button" class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-800"
                        onclick="__ipOpen = false; $wire.closeSpecPicker()" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="p-4">
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute end-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="text" wire:model.live.debounce.300ms="specSearch" placeholder="جستجوی مشخصه… (رنگ، گارانتی، اندازه…)"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 pe-10 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
            </div>

            <div class="scroll-slim mt-3 max-h-72 space-y-1.5 overflow-y-auto">
                @if ($availableSpecs->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center dark:border-slate-700">
                        <p class="text-[11px] font-extrabold">مشخصه‌ای پیدا نشد</p>
                        <p class="mt-1 text-[10px] leading-5 text-slate-400">مشخصه‌ها از بخش «مشخصات» محصولات فروشگاه شما خوانده می‌شوند؛ اول در محصولات، مشخصه ثبت کنید.</p>
                    </div>
                @else
                    @foreach ($availableSpecs as $spec)
                        @php $picked = in_array($spec, $specNames, true); @endphp
                        <button type="button" wire:click="toggleSpec('{{ $spec }}')" wire:key="avail-{{ $loop->index }}"
                                class="flex w-full items-center justify-between rounded-xl border p-2.5 text-right transition {{ $picked
                                    ? 'border-primary-500 bg-primary-50/70 dark:border-primary-600 dark:bg-primary-950/30'
                                    : 'border-slate-200 bg-white hover:border-primary-300 dark:border-slate-700 dark:bg-slate-900' }}">
                            <span class="text-[11px] font-extrabold">{{ $spec }}</span>
                            <span class="grid size-5 place-items-center rounded-md border transition {{ $picked
                                ? 'border-primary-600 bg-primary-600 text-white'
                                : 'border-slate-300 dark:border-slate-600' }}">
                                @if ($picked)
                                    <x-icon name="check" class="size-3" />
                                @endif
                            </span>
                        </button>
                    @endforeach
                @endif
            </div>

            <button type="button" wire:click="closeSpecPicker"
                    class="mt-3 w-full rounded-xl bg-slate-900 py-2.5 text-[11px] font-extrabold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                تمام — {{ \App\Support\Fmt::toFa(count($specNames)) }} مشخصه انتخاب شد
            </button>
        </div>
    </x-modal-panel>

    {{-- ══════════ DELETE CONFIRM ══════════ --}}
    <x-modal-panel open="$wire.confirmDeleteId !== null" maxWidth="sm" inFrame zClass="z-[80]"
                   closeJs="__ipOpen = false; $wire.confirmDeleteId = null">
        <div class="p-5 text-center">
            <span class="mx-auto grid size-12 place-items-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                <x-icon name="trash" class="size-5" />
            </span>
            <p class="mt-3 text-xs font-extrabold">حذف این فیلتر؟</p>
            <p class="mt-1 text-[10px] leading-5 text-slate-400">این فیلتر از صفحهٔ فروشگاه شما برداشته می‌شود.</p>

            <div class="mt-4 flex gap-2">
                <button type="button" onclick="__ipOpen = false; $wire.confirmDeleteId = null"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    انصراف
                </button>
                <button type="button" wire:click="deleteFilter" wire:loading.attr="disabled" wire:target="deleteFilter"
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
