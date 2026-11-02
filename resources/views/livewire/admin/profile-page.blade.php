<div class="ip-fade-up space-y-5">

    {{-- ── header card ── --}}
    <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-lg font-black text-white shadow-lg shadow-primary-600/30">
            {{ mb_substr($me->name ?: 'م', 0, 1) }}
        </span>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-sm font-extrabold">{{ $me->name }}</h2>
                @if ($me->isSuper())
                    <span class="rounded-lg bg-amber-100 px-2 py-1 text-[10px] font-black text-amber-700 dark:bg-amber-950 dark:text-amber-400">مدیر اصلی</span>
                @endif
            </div>
            <p class="mt-0.5 text-[11px] text-slate-400" dir="ltr">{{ $me->email }}</p>
        </div>
        <div class="text-end text-[10.5px] text-slate-400">
            @if ($me->last_login_at)
                آخرین ورود: {{ \App\Support\JDate::dateTime($me->last_login_at) }}
            @endif
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">

        {{-- ═════ edit info ═════ --}}
        <form wire:submit="saveProfile" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="user" class="size-4 text-primary-500" />
                ویرایش اطلاعات
            </h3>
            <div class="mt-4 space-y-4">
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">نام و نام خانوادگی <span class="text-rose-500">*</span></span>
                    <input type="text" wire:model="name"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                           placeholder="مثلاً: علی محمدی">
                    @error('name') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">ایمیل (نام کاربری ورود) <span class="text-rose-500">*</span></span>
                    <input type="email" wire:model="email" dir="ltr"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                           placeholder="admin@example.com">
                    @error('email') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>
            </div>
            <button type="submit" wire:loading.attr="disabled" wire:target="saveProfile"
                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                <span wire:loading wire:target="saveProfile" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                ذخیره تغییرات
            </button>
        </form>

        {{-- ═════ password change ═════ --}}
        <form wire:submit="changePassword" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="lock" class="size-4 text-primary-500" />
                تغییر رمز عبور
            </h3>
            <div class="mt-4 space-y-4">
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">رمز عبور فعلی <span class="text-rose-500">*</span></span>
                    <input type="password" wire:model="currentPassword" dir="ltr" autocomplete="current-password"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                           placeholder="••••••••">
                    @error('currentPassword') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">رمز عبور جدید <span class="text-rose-500">*</span></span>
                    <input type="password" wire:model="newPassword" dir="ltr" autocomplete="new-password"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                           placeholder="حداقل ۸ کاراکتر">
                    @error('newPassword') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">تکرار رمز عبور جدید <span class="text-rose-500">*</span></span>
                    <input type="password" wire:model="newPasswordConfirmation" dir="ltr" autocomplete="new-password"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                           placeholder="••••••••">
                    @error('newPasswordConfirmation') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>
            </div>
            <button type="submit" wire:loading.attr="disabled" wire:target="changePassword"
                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-md transition hover:bg-slate-800 disabled:opacity-50 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                <span wire:loading wire:target="changePassword" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent dark:border-slate-900 dark:border-t-transparent"></span>
                تغییر رمز عبور
            </button>
        </form>
    </div>
</div>
