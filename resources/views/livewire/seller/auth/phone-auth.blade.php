<div class="mx-auto flex w-full max-w-sm flex-1 flex-col">

    {{-- ───────── mode tabs (sliding thumb) ───────── --}}
    <div class="relative grid grid-cols-2 rounded-2xl border border-slate-200 bg-slate-100 p-1 dark:border-slate-800 dark:bg-slate-900" role="tablist">
        <span aria-hidden="true"
              class="absolute inset-y-1 right-1 w-[calc(50%-0.25rem)] rounded-xl bg-white shadow-md ring-1 ring-slate-200/60 transition-transform duration-300 ease-out dark:bg-slate-800 dark:ring-slate-700/60"
              style="transform: translateX({{ $mode === 'register' ? '-100%' : '0' }})"></span>

        <button type="button" wire:click="setMode('login')" role="tab" aria-selected="{{ $mode === 'login' }}"
                class="relative z-10 rounded-xl py-2.5 text-xs font-extrabold transition-colors duration-300 {{ $mode === 'login' ? 'text-primary-600 dark:text-primary-400' : 'text-slate-500' }}">
            ورود
        </button>
        <button type="button" wire:click="setMode('register')" role="tab" aria-selected="{{ $mode === 'register' }}"
                class="relative z-10 rounded-xl py-2.5 text-xs font-extrabold transition-colors duration-300 {{ $mode === 'register' ? 'text-primary-600 dark:text-primary-400' : 'text-slate-500' }}">
            ثبت‌نام فروشنده
        </button>
    </div>

    @if ($step === 'phone')
        <div wire:key="step-phone-{{ $mode }}" class="ip-fade-up">
            <div class="mt-7 flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-fuchsia-500 via-primary-500 to-orange-400 text-white shadow-lg shadow-primary-600/30 motion-safe:animate-[ip-float_4s_ease-in-out_infinite]">
                    <x-icon name="{{ $mode === 'login' ? 'headset' : 'sparkles' }}" class="size-6" />
                </span>
                <div>
                    <h1 class="text-xl font-black leading-7">
                        {{ $mode === 'login' ? 'خوش برگشتید! 👋' : 'فروشگاهت را بساز' }}
                    </h1>
                    <p class="mt-0.5 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                        {{ $mode === 'login'
                            ? 'با شماره موبایل و کد تأیید وارد شوید.'
                            : 'کمتر از یک دقیقه طول می‌کشد.' }}
                    </p>
                </div>
            </div>

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

                @if ($mode === 'register')
                    <div>
                        <label for="instagram" class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">شناسه اینستاگرام فروشگاه</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3 grid place-items-center text-slate-400">
                                <x-icon name="instagram" class="size-4.5" />
                            </span>
                            <input id="instagram" type="text" dir="ltr" autocomplete="username"
                                   placeholder="@my_shop"
                                   class="w-full rounded-2xl border border-slate-300 bg-white py-3.5 pl-11 pr-4 text-left text-sm font-bold shadow-sm outline-none transition placeholder:text-slate-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/15 dark:border-slate-700 dark:bg-slate-900 dark:placeholder:text-slate-600"
                                   wire:model.live.debounce.450ms="instagram" required>
                            <span class="absolute inset-y-0 right-3 grid place-items-center">
                                @if ($instagramTaken === false)
                                    <span class="grid size-5 place-items-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400" title="قابل استفاده">
                                        <x-icon name="check" class="size-3" />
                                    </span>
                                @elseif ($instagramTaken === true)
                                    <span class="grid size-5 place-items-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-400" title="قبلاً استفاده شده">
                                        <x-icon name="x" class="size-3" />
                                    </span>
                                @endif
                            </span>
                        </div>
                        @error('instagram') <p class="mt-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        @if ($instagramTaken === true)
                            <p class="mt-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400">این شناسه اینستاگرام قبلاً ثبت شده؛ دیگری را امتحان کنید.</p>
                        @elseif ($instagramTaken === false && ! $errors->has('instagram'))
                            <p class="mt-1.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">این شناسه آزاد است و می‌توانید ثبتش کنید.</p>
                        @endif
                        <p class="mt-1.5 text-[10px] leading-5 text-slate-400">آدرس فروشگاه شما: <span dir="ltr">instashop.ir/s/{{ $instagram ?: 'shop' }}</span></p>
                    </div>
                @endif

                <button type="submit"
                        class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-l from-fuchsia-600 via-primary-600 to-orange-500 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:brightness-105 active:scale-[.98] disabled:opacity-60"
                        wire:loading.attr="disabled" wire:target="sendOtp">
                    <span aria-hidden="true" class="pointer-events-none absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/25 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                    <span wire:loading wire:target="sendOtp" class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    <span wire:loading.remove wire:target="sendOtp">{{ $mode === 'login' ? 'دریافت کد ورود' : 'ادامه و دریافت کد' }}</span>
                    <span wire:loading wire:target="sendOtp">در حال ارسال...</span>
                </button>
            </form>
        </div>
    @endif

    @if ($step === 'code')
        <div wire:key="step-code" class="ip-fade-up">
            <div class="mt-7 flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-fuchsia-500 via-primary-500 to-orange-400 text-white shadow-lg shadow-primary-600/30 motion-safe:animate-[ip-float_4s_ease-in-out_infinite]">
                    <x-icon name="shield" class="size-6" />
                </span>
                <div>
                    <h1 class="text-xl font-black leading-7">کد تأیید</h1>
                    <p class="mt-0.5 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                        کد ۶ رقمی به شماره <b dir="ltr">{{ \App\Support\Phone::pretty($phone) }}</b> ارسال شد.
                    </p>
                </div>
            </div>

            {{-- ───────── test-mode code (SMS driver = log) ───────── --}}
            @if ($devCode)
                <button type="button" wire:click="autofillTestCode"
                        class="ip-fade-up mt-5 flex w-full items-center justify-between gap-3 rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50/80 px-4 py-3 text-right transition hover:border-amber-400 hover:bg-amber-50 active:scale-[.99] dark:border-amber-700 dark:bg-amber-950/40"
                        aria-label="پر کردن خودکار کد تست">
                    <div>
                        <div class="flex items-center gap-1.5 text-[10px] font-black text-amber-700 dark:text-amber-300">
                            <x-icon name="info" class="size-3.5" />
                            حالت آزمایشی پیامک — کد شما:
                        </div>
                        <div class="mt-1.5 flex gap-1" dir="ltr">
                            @foreach (str_split($devCode) as $digit)
                                <span class="grid size-7 place-items-center rounded-lg bg-white text-sm font-black text-primary-700 shadow-sm dark:bg-slate-900 dark:text-primary-300">{{ $digit }}</span>
                            @endforeach
                        </div>
                        <div class="mt-1.5 text-[9px] font-bold text-amber-600/80 dark:text-amber-400/80">روی این کادر بزنید تا خودکار پر شود</div>
                    </div>
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-amber-400/90 text-white shadow-md shadow-amber-400/30">
                        <x-icon name="chevron-left" class="size-4" />
                    </span>
                </button>
            @endif

            <form wire:submit="verify" class="mt-5 space-y-4">
                <div>
                    <label for="code" class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">کد تأیید</label>
                    <input id="code" type="text" inputmode="numeric" dir="ltr" maxlength="6" autocomplete="one-time-code"
                           placeholder="······"
                           class="w-full rounded-2xl border border-slate-300 bg-white py-4 text-center text-2xl font-black tracking-[.6em] text-slate-800 shadow-sm outline-none transition placeholder:tracking-[.6em] placeholder:text-slate-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-600"
                           wire:model="code" required autofocus>
                    @error('code') <p class="mt-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-l from-fuchsia-600 via-primary-600 to-orange-500 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:brightness-105 active:scale-[.98] disabled:opacity-60"
                        wire:loading.attr="disabled" wire:target="verify">
                    <span aria-hidden="true" class="pointer-events-none absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/25 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                    <span wire:loading wire:target="verify" class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    <span wire:loading.remove wire:target="verify">{{ $mode === 'login' ? 'ورود به پنل فروشنده' : 'ثبت‌نام و ورود' }}</span>
                    <span wire:loading wire:target="verify">در حال بررسی...</span>
                </button>

                <div class="flex items-center justify-between text-[11px] font-bold">
                    <button type="button" wire:click="backToPhone" class="flex items-center gap-1 text-slate-500 transition hover:text-slate-700 dark:hover:text-slate-300">
                        <x-icon name="arrow-left" class="size-3.5" />
                        تغییر اطلاعات
                    </button>
                    {{-- client-side countdown (no wire:poll — a concurrent poll request can
                         revert the rotated session cookie right after login) --}}
                    <div class="text-slate-400">
                        <span class="otp-wait">ارسال مجدد تا <b class="otp-cd text-primary-600 dark:text-primary-400" dir="ltr">۶۰</b> ثانیه</span>
                        <button type="button" wire:click="sendOtp"
                                class="otp-resend hidden text-primary-600 transition hover:text-primary-700 dark:text-primary-400"
                                wire:loading.attr="disabled" wire:target="sendOtp">
                            ارسال مجدد کد
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('otp-countdown', (event) => {
                let seconds = event.detail?.seconds ?? 0;
                const wait   = document.querySelector('.otp-wait');
                const resend = document.querySelector('.otp-resend');
                const num    = document.querySelector('.otp-cd');
                if (!wait || !resend) return;

                const fa = (n) => String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
                wait.classList.remove('hidden');
                resend.classList.add('hidden');
                num && (num.textContent = fa(seconds));

                clearInterval(window.__otpCd);
                window.__otpCd = setInterval(() => {
                    seconds--;
                    if (seconds <= 0) {
                        clearInterval(window.__otpCd);
                        wait.classList.add('hidden');
                        resend.classList.remove('hidden');
                    } else {
                        num && (num.textContent = fa(seconds));
                    }
                }, 1000);
            });

            // gentle shake on the code box when the test code gets auto-filled
            document.addEventListener('otp-autofilled', () => {
                const box = document.getElementById('code');
                if (! box) return;
                box.classList.remove('otp-pop');
                void box.offsetWidth;
                box.classList.add('otp-pop');
                box.focus();
            });
        </script>
    @endpush
@endonce
