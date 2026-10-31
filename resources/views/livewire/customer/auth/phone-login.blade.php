<div class="mx-auto flex w-full max-w-sm flex-1 flex-col">
    <h1 class="text-xl font-extrabold">
        {{ $step === 'phone' ? 'ورود | ثبت‌نام' : 'کد تأیید' }}
    </h1>
    <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
        {{ $step === 'phone'
            ? 'شماره موبایل خود را وارد کنید؛ کد تأیید برایتان پیامک می‌شود.'
            : 'کد ۶ رقمی ارسال‌شده به شماره '.\App\Support\Phone::pretty($phone).' را وارد کنید.' }}
    </p>

    {{-- ───────── Step 1: phone ───────── --}}
    @if ($step === 'phone')
        <form wire:submit="sendOtp" class="mt-6 space-y-4">
            <div>
                <label for="phone" class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">شماره موبایل</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 grid place-items-center text-slate-400">
                        <x-icon name="phone" class="size-4.5" />
                    </span>
                    <input id="phone" type="tel" inputmode="tel" dir="ltr" autocomplete="tel"
                           placeholder="0912 345 6789"
                           class="w-full rounded-2xl border border-slate-300 bg-white py-3.5 pl-11 pr-4 text-left text-sm font-bold tracking-wider shadow-sm outline-none transition placeholder:text-slate-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/15 dark:border-slate-700 dark:bg-slate-900 dark:placeholder:text-slate-600"
                           wire:model="phone" required autofocus>
                </div>
                @error('phone') <p class="mt-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-600 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-[.98] disabled:opacity-60"
                    wire:loading.attr="disabled" wire:target="sendOtp">
                <span wire:loading wire:target="sendOtp" class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                دریافت کد تأیید
            </button>
        </form>
    @endif

    {{-- ───────── Step 2: code ───────── --}}
    @if ($step === 'code')
        <form wire:submit="verify" class="mt-6 space-y-4">
            <div>
                <label for="code" class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">کد تأیید</label>
                <input id="code" type="text" inputmode="numeric" dir="ltr" maxlength="6" autocomplete="one-time-code"
                       placeholder="······"
                       class="w-full rounded-2xl border border-slate-300 bg-white py-4 text-center text-2xl font-black tracking-[.6em] text-slate-800 shadow-sm outline-none transition placeholder:tracking-[.6em] placeholder:text-slate-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-600"
                       wire:model="code" required autofocus>
                @error('code') <p class="mt-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>

            {{-- dev hint: OTP visible with the log driver in local env --}}
            @if ($devCode)
                <div class="flex items-center gap-2.5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-bold text-amber-800 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-300" dir="ltr">
                    <x-icon name="info" class="size-4 shrink-0" />
                    <span>DEV OTP: <b class="text-base tracking-widest">{{ $devCode }}</b> (driver = log)</span>
                </div>
            @endif

            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-600 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-[.98] disabled:opacity-60"
                    wire:loading.attr="disabled" wire:target="verify">
                <span wire:loading wire:target="verify" class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                ورود به فروشگاه
            </button>

            <div class="flex items-center justify-between text-[11px] font-bold">
                <button type="button" wire:click="backToPhone" class="flex items-center gap-1 text-slate-500 transition hover:text-slate-700 dark:hover:text-slate-300">
                    <x-icon name="arrow-left" class="size-3.5" />
                    تغییر شماره
                </button>
                <div class="text-slate-400">
                    @if ($resendIn > 0)
                        <span wire:poll.1000ms="tick">
                            ارسال مجدد تا <b class="text-primary-600 dark:text-primary-400" dir="ltr">{{ $resendIn }}</b> ثانیه
                        </span>
                    @else
                        <button type="button" wire:click="sendOtp"
                                class="text-primary-600 transition hover:text-primary-700 dark:text-primary-400"
                                wire:loading.attr="disabled" wire:target="sendOtp">
                            ارسال مجدد کد
                        </button>
                    @endif
                </div>
            </div>
        </form>
    @endif
</div>
