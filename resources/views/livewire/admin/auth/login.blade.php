<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-900">

    <form wire:submit="login" class="space-y-4">
        <div>
            <label for="email" class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">ایمیل</label>
            <input id="email" type="email" dir="ltr" autocomplete="email"
                   placeholder="admin@example.com"
                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-left text-sm font-bold shadow-sm outline-none transition placeholder:text-slate-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/15 dark:border-slate-700 dark:bg-slate-950 dark:placeholder:text-slate-600"
                   wire:model="email" required autofocus>
            @error('email') <p class="mt-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">رمز عبور</label>
            <input id="password" type="password" dir="ltr" autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-left text-sm font-bold shadow-sm outline-none transition placeholder:text-slate-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/15 dark:border-slate-700 dark:bg-slate-950 dark:placeholder:text-slate-600"
                   wire:model="password" required>
            @error('password') <p class="mt-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <label class="flex cursor-pointer items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
            <input type="checkbox" wire:model="remember"
                   class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500/30 dark:border-slate-600 dark:bg-slate-950">
            مرا به خاطر بسپار
        </label>

        <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-600 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-[.98] disabled:opacity-60"
                wire:loading.attr="disabled" wire:target="login">
            <span wire:loading wire:target="login" class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
            ورود به پنل مدیریت
        </button>
    </form>

    <p class="mt-4 text-center text-[11px] text-slate-400">
        <a href="{{ route('landing') }}" class="font-semibold transition hover:text-primary-600 dark:hover:text-primary-400">بازگشت به صفحه اصلی</a>
    </p>
</div>
