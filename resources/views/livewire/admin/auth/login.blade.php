<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-900"
     x-data="{
        a: {{ (int) request()->session()->get('admin_captcha_display_a', 0) }},
        b: {{ (int) request()->session()->get('admin_captcha_display_b', 0) }},
        fa: (v) => String(v).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]),
     }"
     x-on:admin-captcha-refresh.window="a = event.detail.a; b = event.detail.b">

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

        {{-- ── robot check ── ──
             Image captcha: the challenge is a noisy PNG whose answer lives
             only in the session — nothing scriptable in the DOM. On hosts
             without GD it falls back to the legacy math challenge. --}}
        <div>
            <label for="captcha" class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">کد امنیتی (ربات نیستید؟)</label>
            <div class="flex items-center gap-2">
                @if ($captchaImage !== '')
                    <img src="{{ $captchaImage }}" alt="کد امنیتی" width="190" height="60"
                         wire:key="captcha-img"
                         class="h-[52px] w-[164px] shrink-0 select-none rounded-2xl border border-dashed border-primary-300 object-cover dark:border-primary-800"
                         draggable="false">
                @else
                    <span class="flex h-[52px] min-w-[92px] select-none items-center justify-center gap-1.5 rounded-2xl border border-dashed border-primary-300 bg-primary-50 text-base font-black tracking-[0.2em] text-primary-700 dark:border-primary-800 dark:bg-primary-950/40 dark:text-primary-300">
                        <span x-text="fa(a)"></span>
                        <span class="text-primary-400">+</span>
                        <span x-text="fa(b)"></span>
                        <span class="text-primary-400">=</span>
                        <span class="text-primary-300">؟</span>
                    </span>
                @endif
                <input id="captcha" type="text" inputmode="numeric" dir="ltr"
                       placeholder="کد را وارد کنید"
                       maxlength="6" autocomplete="off" autocapitalize="off" spellcheck="false"
                       class="h-[52px] w-full rounded-2xl border border-slate-300 bg-white px-4 text-center text-sm font-bold shadow-sm outline-none transition placeholder:text-slate-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/15 dark:border-slate-700 dark:bg-slate-950 dark:placeholder:text-slate-600"
                       wire:model="captcha" required>
                <button type="button" wire:click="regenerateCaptcha" wire:loading.attr="disabled" wire:target="regenerateCaptcha"
                        class="grid size-[52px] shrink-0 place-items-center rounded-2xl border border-slate-200 text-slate-400 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:hover:text-primary-400"
                        aria-label="تعویض کد امنیتی" title="تعویض کد امنیتی">
                    <x-icon name="rotate" class="size-4.5" />
                </button>
            </div>
            @error('captcha') <p class="mt-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
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
