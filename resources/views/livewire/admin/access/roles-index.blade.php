<div class="ip-fade-up space-y-5"
     x-data="{ open: $wire.modalOpen }"
     x-on:keydown.escape.window="open = false; $wire.closeModal()"
     x-on:roles-close-modal.window="open = false">

    {{-- ── flash ── --}}
    @if (session('admin-roles-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-roles-flash') }}
        </div>
    @endif
    @if (session('admin-roles-error'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" x-data x-init="setTimeout(() => $el.remove(), 6000)">
            <x-icon name="alert" class="size-4.5 shrink-0" />
            {{ session('admin-roles-error') }}
        </div>
    @endif

    {{-- ── header ── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-extrabold">نقش‌های پنل مدیریت</h2>
            <p class="mt-1 text-[11px] leading-5 text-slate-400">
                هر صفحه‌ی پنل یک دسترسی است. با تیک‌زدن دسترسی‌ها مشخص کنید هر نقش کدام صفحات را ببیند. صفحات برنامه‌نویس فقط برای مدیر اصلی است و در این فهرست نیست.
            </p>
        </div>
        <button type="button" wire:click="createRole" x-on:click="open = true"
                class="inline-flex items-center gap-1.5 self-start rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
            <x-icon name="plus" class="size-4" />
            افزودن نقش
        </button>
    </div>

    {{-- ── roles cards ── --}}
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($roles as $role)
            <div class="flex flex-col rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                            <x-icon name="shield" class="size-4.5" />
                        </span>
                        <div>
                            <p class="flex items-center gap-1.5 text-sm font-extrabold">
                                {{ $role->name }}
                                @if ($role->is_system)
                                    <span class="rounded-md bg-sky-100 px-1.5 py-0.5 text-[9px] font-black text-sky-700 dark:bg-sky-950 dark:text-sky-300">سیستمی</span>
                                @endif
                            </p>
                            <p class="mt-0.5 text-[10px] text-slate-400">
                                {{ \App\Support\Fmt::fa($role->admins_count ?? 0) }} مدیر · {{ \App\Support\Fmt::fa(count($role->permissions ?? [])) }} دسترسی
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                        <button type="button" wire:click="editRole({{ $role->id }})" x-on:click="open = true"
                                class="grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800" aria-label="ویرایش نقش">
                            <x-icon name="edit" class="size-3.5" />
                        </button>
                        @unless ($role->is_system)
                            <button type="button" wire:click="deleteRole({{ $role->id }})"
                                    wire:confirm="نقش «{{ $role->name }}» حذف شود؟"
                                    class="grid size-8 place-items-center rounded-lg border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950/40" aria-label="حذف نقش">
                                <x-icon name="trash" class="size-3.5" />
                            </button>
                        @endunless
                    </div>
                </div>

                @if ($role->description)
                    <p class="mt-3 text-[11px] leading-5 text-slate-500 dark:text-slate-400">{{ $role->description }}</p>
                @endif

                @if (count($role->permissions ?? []))
                    <div class="mt-3 flex flex-wrap gap-1.5 border-t border-dashed border-slate-200 pt-3 dark:border-slate-700">
                        @foreach (array_slice($role->permissions ?? [], 0, 6) as $slug)
                            <code class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ \App\Support\AdminPermissions::label($slug) }}</code>
                        @endforeach
                        @if (count($role->permissions ?? []) > 6)
                            <span class="rounded-md bg-primary-100 px-1.5 py-0.5 text-[9px] font-black text-primary-700 dark:bg-primary-950 dark:text-primary-300">+ {{ \App\Support\Fmt::fa(count($role->permissions) - 6) }}</span>
                        @endif
                    </div>
                @else
                    <p class="mt-3 border-t border-dashed border-slate-200 pt-3 text-[11px] text-slate-400 dark:border-slate-700">بدون دسترسی — این نقش فقط به داشبورد پیش‌فرض می‌رسد.</p>
                @endif
            </div>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 p-10 text-center text-xs text-slate-400 md:col-span-2 xl:col-span-3 dark:border-slate-700">
                هنوز نقشی تعریف نشده است. با دکمه‌ی «افزودن نقش» اولین نقش را بسازید.
            </div>
        @endforelse
    </div>

    {{-- ═══════════ add/edit role modal ═══════════ --}}
    <div x-cloak x-show="open" x-on:click.self="open = false; $wire.closeModal()"
         class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-labelledby="role-modal-title"
         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="scroll-slim max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
             x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100">

            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900">
                <h3 id="role-modal-title" class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="{{ $editing ? 'edit' : 'plus' }}" class="size-4" />
                    </span>
                    {{ $editing ? 'ویرایش نقش' : 'افزودن نقش جدید' }}
                </h3>
                <button type="button" x-on:click="open = false; $wire.closeModal()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>

            <form wire:submit="saveRole" class="space-y-4 px-5 py-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">نام نقش <span class="text-rose-500">*</span></span>
                        <input type="text" wire:model="roleName"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                               placeholder="مثلاً: مدیر سفارش‌ها">
                        @error('roleName') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">توضیحات (اختیاری)</span>
                        <input type="text" wire:model="roleDescription"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    </label>
                </div>

                {{-- permission checkboxes --}}
                <div>
                    <p class="mb-2 text-[11px] font-extrabold text-slate-600 dark:text-slate-300">دسترسی‌ها — صفحاتی که این نقش می‌تواند باز کند:</p>
                    <div class="scroll-slim max-h-80 space-y-3 overflow-y-auto rounded-2xl bg-slate-50/60 p-2 dark:bg-slate-800/40">
                        @foreach ($this->groups() as $groupKey => $group)
                            <div class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-extrabold">{{ $group['label'] }}</span>
                                    <label class="inline-flex cursor-pointer items-center gap-1 text-[10px] font-bold text-slate-400">
                                        <input type="checkbox" class="size-3.5" style="accent-color: var(--ip-primary)"
                                               {{ $this->groupChecked($groupKey) ? 'checked' : '' }}
                                               wire:click="{{ $this->groupChecked($groupKey) ? 'toggleGroup(\''.$groupKey.'\', false)' : 'toggleGroup(\''.$groupKey.'\', true)' }}">
                                        همه
                                    </label>
                                </div>
                                <div class="mt-2.5 grid gap-1.5 sm:grid-cols-2">
                                    @foreach ($group['permissions'] as $slug => $label)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-[11px] font-bold text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/60">
                                            <input type="checkbox" wire:model="permissionSelection.{{ $slug }}" class="size-4 rounded" style="accent-color: var(--ip-primary)">
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                        <span wire:loading wire:target="saveRole" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        ذخیره نقش
                    </button>
                    <button type="button" x-on:click="open = false; $wire.closeModal()"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                        انصراف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
