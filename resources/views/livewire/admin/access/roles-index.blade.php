<div class="ip-fade-up space-y-5">

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

    {{-- ── tabs ── --}}
    <div x-data="{}">
        @php $tab = $tab; @endphp
        <div class="flex gap-2" role="tablist" aria-label="مدیریت دسترسی‌ها">
            <button type="button" wire:click="$set('tab', 'roles')" role="tab" :aria-selected="$wire.tab === 'roles'"
                    class="flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-xs font-bold transition {{ $tab === 'roles'
                        ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <x-icon name="shield" class="size-4" />
                نقش‌ها
            </button>
            <button type="button" wire:click="$set('tab', 'admins')" role="tab" :aria-selected="$wire.tab === 'admins'"
                    class="flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-xs font-bold transition {{ $tab === 'admins'
                        ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <x-icon name="users" class="size-4" />
                مدیران
            </button>
        </div>
    </div>

    @if ($tab === 'roles')
        {{-- ═══════════════════ TAB: ROLES ═══════════════════ --}}
        <div class="grid gap-5 xl:grid-cols-3">

            {{-- roles list --}}
            <div class="space-y-3 xl:col-span-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-extrabold">نقش‌های تعریف‌شده</h2>
                    <button type="button" wire:click="createRole"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                        <x-icon name="plus" class="size-4" />
                        نقش جدید
                    </button>
                </div>

                <div class="scroll-slim max-h-[32rem] space-y-3 overflow-y-auto pe-1">
                    @forelse ($roles as $role)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="flex items-center gap-2 text-sm font-extrabold">
                                        <span class="grid size-8 place-items-center rounded-lg bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                                            <x-icon name="shield" class="size-4" />
                                        </span>
                                        {{ $role->name }}
                                        @if ($role->is_system)
                                            <span class="rounded-md bg-sky-100 px-1.5 py-0.5 text-[9px] font-black text-sky-700 dark:bg-sky-950 dark:text-sky-300">سیستمی</span>
                                        @endif
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-400">
                                        {{ \App\Support\Fmt::fa(count($role->permissions ?? [])) }} دسترسی
                                        @if ($role->description) · {{ $role->description }} @endif
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-1.5">
                                    <button type="button" wire:click="editRole({{ $role->id }})"
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
                            @if (count($role->permissions ?? []))
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach (array_slice($role->permissions ?? [], 0, 6) as $slug)
                                        <code class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400" dir="ltr">{{ \App\Support\AdminPermissions::label($slug) }}</code>
                                    @endforeach
                                    @if (count($role->permissions ?? []) > 6)
                                        <span class="rounded-md bg-primary-100 px-1.5 py-0.5 text-[9px] font-black text-primary-700 dark:bg-primary-950 dark:text-primary-300">+ {{ \App\Support\Fmt::fa(count($role->permissions) - 6) }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center text-xs text-slate-400 dark:border-slate-700">
                            هنوز نقشی تعریف نشده است. با دکمه‌ی «نقش جدید» اولین نقش را بسازید.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- role form --}}
            <div class="scroll-slim max-h-[36rem] overflow-y-auto rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-9 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="{{ $roleId ? 'edit' : 'plus' }}" class="size-4.5" />
                    </span>
                    {{ $roleId ? 'ویرایش نقش' : 'افزودن نقش جدید' }}
                </h2>

                <div class="mt-4 space-y-3">
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
                <div class="mt-5">
                    <p class="mb-2 text-[11px] font-extrabold text-slate-600 dark:text-slate-300">دسترسی‌ها — صفحاتی که این نقش می‌تواند باز کند:</p>
                    <div class="space-y-3">
                        @foreach ($this->groups() as $groupKey => $group)
                            <div class="rounded-2xl border border-slate-200 p-3 dark:border-slate-800">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-extrabold">{{ $group['label'] }}</span>
                                    <label class="inline-flex cursor-pointer items-center gap-1 text-[10px] font-bold text-slate-400">
                                        <input type="checkbox" class="size-3.5" style="accent-color: var(--ip-primary)"
                                               {{ $this->groupChecked($groupKey) ? 'checked' : '' }}
                                               wire:click="{{ $this->groupChecked($groupKey) ? 'toggleGroup(\''.$groupKey.'\', false)' : 'toggleGroup(\''.$groupKey.'\', true)' }}">
                                        همه
                                    </label>
                                </div>
                                <div class="mt-2.5 grid gap-1.5">
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

                <div class="mt-5 flex gap-2">
                    <button type="button" wire:click="saveRole" wire:loading.attr="disabled"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                        <span wire:loading wire:target="saveRole" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        ذخیره نقش
                    </button>
                    @if ($roleId)
                        <button type="button" wire:click="createRole"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                            انصراف
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @else
        {{-- ═══════════════════ TAB: ADMINS ═══════════════════ --}}
        <form wire:submit="saveAssignments" class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-extrabold">تخصیص نقش به مدیران</h2>
                <p class="mt-1 text-[11px] leading-6 text-slate-400">
                    مدیران دارای برچسب «سوپر» به همه‌ی صفحات دسترسی کامل دارند و از این لیست استثنا هستند. بقیه فقط صفحاتی را می‌بینند که نقش‌هایشان اجازه دهد.
                </p>

                <div class="mt-4 space-y-3">
                    @foreach ($admins as $admin)
                        <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="grid size-9 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-xs font-bold text-white">
                                        {{ mb_substr($admin->name, 0, 1) }}
                                    </span>
                                    <div>
                                        <p class="text-xs font-extrabold">{{ $admin->name }}</p>
                                        <p class="text-[10px] text-slate-400" dir="ltr">{{ $admin->email }}</p>
                                    </div>
                                </div>
                                @if ($admin->isSuper())
                                    <span class="rounded-lg bg-amber-100 px-2 py-1 text-[10px] font-black text-amber-700 dark:bg-amber-950 dark:text-amber-400">سوپر — دسترسی کامل</span>
                                @endif
                            </div>

                            @unless ($admin->isSuper())
                                <div class="mt-3 flex flex-wrap gap-2 border-t border-dashed border-slate-200 pt-3 dark:border-slate-700">
                                    @forelse ($allRoles as $role)
                                        <label class="flex cursor-pointer items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[11px] font-bold transition {{ ($assignments[$admin->id][$role->id] ?? false)
                                            ? 'border-primary-400 bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300'
                                            : 'border-slate-200 text-slate-500 dark:border-slate-700 dark:text-slate-400' }}">
                                            <input type="checkbox" wire:model="assignments.{{ $admin->id }}.{{ $role->id }}" class="size-3.5 rounded" style="accent-color: var(--ip-primary)">
                                            {{ $role->name }}
                                        </label>
                                    @empty
                                        <p class="text-[11px] text-slate-400">هنوز نقشی تعریف نشده — ابتدا در تب «نقش‌ها» یک نقش بسازید.</p>
                                    @endforelse
                                </div>
                            @endunless
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                <span wire:loading wire:target="saveAssignments" class="inline-block size-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                ذخیره دسترسی مدیران
            </button>
        </form>
    @endif
</div>
