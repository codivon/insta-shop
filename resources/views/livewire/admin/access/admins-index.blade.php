<div class="ip-fade-up space-y-5"
     x-data="{ open: $wire.modalOpen, deleteOpen: false }"
     x-on:keydown.escape.window="open = false; deleteOpen = false; $wire.closeModal()"
     x-on:admins-close-modal.window="open = false">

    {{-- ── flash ── --}}
    @if (session('admin-admins-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-admins-flash') }}
        </div>
    @endif
    @if (session('admin-admins-error'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" x-data x-init="setTimeout(() => $el.remove(), 6000)">
            <x-icon name="alert" class="size-4.5 shrink-0" />
            {{ session('admin-admins-error') }}
        </div>
    @endif

    {{-- ── header ── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-extrabold">مدیران پنل</h2>
            <p class="mt-1 text-[11px] leading-5 text-slate-400">
                حساب‌های ورود به پنل مدیریت کل. مدیر اصلی (سوپر) دسترسی کامل دارد؛ بقیه فقط صفحاتی را می‌بینند که نقش‌هایشان اجازه دهد.
            </p>
        </div>
        <button type="button" wire:click="openCreate" x-on:click="open = true"
                class="inline-flex items-center gap-1.5 self-start rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
            <x-icon name="plus" class="size-4" />
            افزودن مدیر
        </button>
    </div>

    {{-- ── admins table ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-right text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                        <th class="px-5 py-3.5 font-semibold">مدیر</th>
                        <th class="px-3 py-3.5 font-semibold">نقش‌ها</th>
                        <th class="px-3 py-3.5 font-semibold">آخرین ورود</th>
                        <th class="px-3 py-3.5 font-semibold">عضویت</th>
                        <th class="px-5 py-3.5 font-semibold">اقدامات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    @foreach ($admins as $admin)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="adm-{{ $admin->id }}">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-bold text-white">
                                        {{ mb_substr($admin->name, 0, 1) }}
                                    </span>
                                    <div>
                                        <p class="flex items-center gap-1.5 text-xs font-extrabold">
                                            {{ $admin->name }}
                                            @if ($admin->id === $me->id)
                                                <span class="rounded-md bg-primary-100 px-1.5 py-0.5 text-[9px] font-black text-primary-700 dark:bg-primary-950 dark:text-primary-300">شما</span>
                                            @endif
                                        </p>
                                        <p class="text-[11px] text-slate-400" dir="ltr">{{ $admin->email }}</p>
                                    </div>
                                    @if ($admin->isSuper())
                                        <span class="rounded-lg bg-amber-100 px-2 py-1 text-[10px] font-black text-amber-700 dark:bg-amber-950 dark:text-amber-400">مدیر اصلی</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-4">
                                @if ($admin->isSuper())
                                    <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400">دسترسی کامل</span>
                                @elseif ($admin->roles->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($admin->roles as $role)
                                            <span class="rounded-lg bg-primary-50 px-2 py-1 text-[10px] font-bold text-primary-700 dark:bg-primary-950/50 dark:text-primary-300">{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-400 dark:bg-slate-800">بدون نقش</span>
                                @endif
                            </td>
                            <td class="px-3 py-4 text-[11px] text-slate-500">{{ $admin->last_login_at ? \App\Support\JDate::dateTime($admin->last_login_at) : '—' }}</td>
                            <td class="px-3 py-4 text-[11px] text-slate-500">{{ \App\Support\JDate::date($admin->created_at) }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5">
                                    <button type="button" wire:click="openEdit({{ $admin->id }})" x-on:click="open = true"
                                            class="grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800" aria-label="ویرایش مدیر">
                                        <x-icon name="edit" class="size-3.5" />
                                    </button>
                                    @unless ($admin->isSuper() || $admin->id === $me->id)
                                        <button type="button" wire:click="requestDelete({{ $admin->id }})" x-on:click="deleteOpen = true"
                                                class="grid size-8 place-items-center rounded-lg border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950/40" aria-label="حذف مدیر">
                                            <x-icon name="trash" class="size-3.5" />
                                        </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══════════ add/edit modal ═══════════ --}}
    <x-modal-panel open="$wire.modalOpen" max-width="lg" labelledby="admin-modal-title"
                   close-js="__ipOpen = false; $wire.closeModal()">

        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 id="admin-modal-title" class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="{{ $editingId ? 'edit' : 'plus' }}" class="size-4" />
                    </span>
                    {{ $editingId ? 'ویرایش مدیر' : 'افزودن مدیر جدید' }}
                </h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeModal()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <form wire:submit="saveAdmin" class="space-y-4 px-5 py-5">
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">نام و نام خانوادگی <span class="text-rose-500">*</span></span>
                    <input type="text" wire:model="adminName"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                           placeholder="مثلاً: علی محمدی">
                    @error('adminName') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">ایمیل (نام کاربری ورود) <span class="text-rose-500">*</span></span>
                    <input type="email" wire:model="adminEmail" dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                           placeholder="admin@example.com">
                    @error('adminEmail') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                        رمز عبور
                        @if ($editingId) <span class="font-normal text-slate-400">(برای تغییر، پر کنید)</span> @else <span class="text-rose-500">*</span> @endif
                    </span>
                    <input type="password" wire:model="adminPassword" dir="ltr" autocomplete="new-password"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                           placeholder="••••••••">
                    @error('adminPassword') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>

                {{-- roles --}}
                <div class="rounded-2xl border border-slate-200 p-3 dark:border-slate-800">
                    <p class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">نقش‌ها</p>
                    <p class="mt-0.5 text-[10px] text-slate-400">مدیران دارای نقش فقط صفحات مجاز را می‌بینند. مدیر اصلی نیازی به نقش ندارد.</p>
                    <div class="mt-2.5 flex flex-wrap gap-2">
                        @forelse ($allRoles as $role)
                            <label class="flex cursor-pointer items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[11px] font-bold transition {{ ($roleSelection[$role->id] ?? false)
                                ? 'border-primary-400 bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300'
                                : 'border-slate-200 text-slate-500 hover:border-primary-300 dark:border-slate-700 dark:text-slate-400' }}">
                                <input type="checkbox" wire:model="roleSelection.{{ $role->id }}" class="size-3.5 rounded" style="accent-color: var(--ip-primary)">
                                {{ $role->name }}
                            </label>
                        @empty
                            <p class="text-[11px] text-slate-400">هنوز نقشی تعریف نشده — از منوی «مدیران ← نقش‌ها» نقش بسازید.</p>
                        @endforelse
                    </div>
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                        <span wire:loading wire:target="saveAdmin" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        {{ $editingId ? 'ذخیره تغییرات' : 'افزودن مدیر' }}
                    </button>
                    <button type="button" x-on:click="__ipOpen = false; $wire.closeModal()"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                        انصراف
                    </button>
                </div>
            </form>

    </x-modal-panel>

    {{-- ═══════════ delete confirm modal ═══════════ --}}
    <x-modal-panel open="$wire.deleteOpen" max-width="sm" close-js="__ipOpen = false" z-class="z-[75]">
        <div class="p-6 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-rose-50 text-rose-500 dark:bg-rose-950/40">
                <x-icon name="trash" class="size-6" />
            </span>
            <h3 class="mt-4 text-sm font-extrabold">حذف مدیر</h3>
            <p class="mt-2 text-[11px] leading-6 text-slate-400">
                این حساب دیگر به پنل مدیریت دسترسی نخواهد داشت. اقدام قابل بازگشت نیست.
            </p>
            <div class="mt-5 flex gap-2">
                <button type="button" wire:click="deleteAdmin" x-on:click="__ipOpen = false" wire:loading.attr="disabled"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-rose-600/25 transition hover:bg-rose-700 disabled:opacity-50">
                    <span wire:loading wire:target="deleteAdmin" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    حذف قطعی
                </button>
                <button type="button" x-on:click="__ipOpen = false"
                        class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    انصراف
                </button>
            </div>
        </div>
    </x-modal-panel>
</div>
