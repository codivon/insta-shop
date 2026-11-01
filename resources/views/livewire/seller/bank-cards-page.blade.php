<div class="ip-fade-up">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                    <x-icon name="credit-card" class="size-5" />
                </span>
                <div>
                    <div class="text-sm font-extrabold leading-5">کارت‌های بانکی</div>
                    <div class="text-[11px] text-slate-400">حساب‌های واریز و برداشت شما</div>
                </div>
            </div>
            <a href="{{ route('seller.profile') }}" wire:navigate
               class="grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بازگشت">
                <x-icon name="arrow-right" class="size-5" />
            </a>
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">
        {{-- flash toast --}}
        @if (session('cards-flash'))
            <div class="mb-3 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-bold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <x-icon name="circle-check" class="size-4" />
                {{ session('cards-flash') }}
            </div>
        @endif

        {{-- ───────── add CTA ───────── --}}
        <button type="button" wire:click="openAdd"
                class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-primary-300 bg-primary-50/60 py-3.5 text-xs font-extrabold text-primary-600 transition hover:border-primary-400 hover:bg-primary-50 active:scale-[.98] dark:border-primary-800 dark:bg-primary-950/30 dark:text-primary-300 dark:hover:bg-primary-950/50">
            <x-icon name="plus" class="size-4" />
            افزودن کارت جدید
            <span class="text-[10px] font-bold text-primary-400">({{ \App\Support\Fmt::fa($cards->count()) }} از {{ \App\Support\Fmt::fa(\App\Livewire\Seller\BankCardsPage::MAX_CARDS) }})</span>
        </button>

        {{-- ───────── cards ───────── --}}
        <div class="mt-4 space-y-3">
            @forelse ($cards as $card)
                <div wire:key="card-{{ $card->id }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    {{-- visual card --}}
                    <div class="relative overflow-hidden bg-gradient-to-bl p-4 text-white {{ $card->cardTheme() }}">
                        <div class="pointer-events-none absolute inset-0 opacity-25" aria-hidden="true">
                            <div class="absolute -top-6 left-8 size-28 rounded-full bg-white/30 blur-2xl"></div>
                            <div class="absolute -bottom-10 right-6 size-32 rounded-full bg-white/20 blur-2xl"></div>
                        </div>

                        <div class="relative flex items-start justify-between">
                            <div class="flex items-center gap-2">
                                <span class="grid size-8 place-items-center rounded-xl bg-white/20 backdrop-blur">
                                    <x-icon name="credit-card" class="size-4" />
                                </span>
                                <span class="text-[11px] font-extrabold">{{ $card->bankName() }}</span>
                            </div>
                            @if ($card->is_default)
                                <span class="rounded-full bg-white/25 px-2 py-0.5 text-[9px] font-black backdrop-blur">پیش‌فرض برداشت</span>
                            @endif
                        </div>

                        {{-- number: masked ↔ revealed --}}
                        <div class="relative mt-3 flex items-center justify-between gap-2" x-data="{ show: false }">
                            <span class="font-mono text-[15px] font-extrabold tracking-[0.14em]" x-show="!show">{{ $card->maskedCard() }}</span>
                            <span class="font-mono text-[15px] font-extrabold tracking-[0.14em]" x-show="show" x-cloak>{{ $card->formattedCard() }}</span>
                            <button type="button" @click="show = !show"
                                    class="grid size-7 shrink-0 place-items-center rounded-lg bg-white/15 transition hover:bg-white/25"
                                    :aria-pressed="show" aria-label="نمایش/مخفی کردن شماره کارت">
                                <x-icon name="eye" class="size-3.5" />
                            </button>
                        </div>

                        <div class="relative mt-2.5 flex items-center justify-between">
                            <span class="truncate text-[11px] font-bold">{{ $card->full_name }}</span>
                            <span class="flex items-center gap-1 text-[10px] font-semibold opacity-80">
                                <x-icon name="user" class="size-3" />
                                صاحب کارت
                            </span>
                        </div>
                    </div>

                    {{-- sheba row --}}
                    <div class="flex items-center justify-between gap-2 px-4 py-2.5" x-data="{ copied: false }">
                        <div class="flex min-w-0 items-center gap-1.5">
                            <span class="shrink-0 rounded-lg bg-slate-100 px-1.5 py-0.5 text-[9px] font-black text-slate-500 dark:bg-slate-800 dark:text-slate-300">شبا</span>
                            <span class="truncate font-mono text-[10.5px] font-bold text-slate-500 dark:text-slate-300" dir="ltr">{{ $card->formattedSheba() }}</span>
                        </div>
                        <button type="button"
                                @click="navigator.clipboard && navigator.clipboard.writeText('{{ $card->sheba }}'); copied = true; setTimeout(() => copied = false, 1600)"
                                class="flex shrink-0 items-center gap-1 rounded-lg px-2 py-1 text-[9.5px] font-bold text-primary-600 transition hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-950/50">
                            <span x-show="!copied">کپی</span>
                            <span x-show="copied" x-cloak class="text-emerald-500">کپی شد ✓</span>
                            <x-icon name="copy" class="size-3" />
                        </button>
                    </div>

                    {{-- delete confirm --}}
                    @if ($confirmDeleteId === $card->id)
                        <div class="flex items-center justify-between gap-2 border-t border-rose-100 bg-rose-50 px-4 py-2.5 dark:border-rose-900/50 dark:bg-rose-950/30">
                            <p class="text-[10px] font-bold text-rose-600 dark:text-rose-300">این کارت حذف شود؟</p>
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="cancelDelete"
                                        class="rounded-lg px-2.5 py-1.5 text-[10px] font-bold text-slate-500 transition hover:bg-slate-200/60 dark:hover:bg-slate-800">
                                    انصراف
                                </button>
                                <button type="button" wire:click="deleteCard({{ $card->id }})" wire:loading.attr="disabled" wire:target="deleteCard"
                                        class="rounded-lg bg-rose-600 px-2.5 py-1.5 text-[10px] font-extrabold text-white transition hover:bg-rose-700">
                                    بله، حذف کن
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center justify-end gap-1 border-t border-slate-100 px-3 py-1.5 dark:border-slate-800">
                            @unless ($card->is_default)
                                <button type="button" wire:click="makeDefault({{ $card->id }})" wire:loading.attr="disabled" wire:target="makeDefault"
                                        class="flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[10px] font-bold text-primary-600 transition hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-950/50">
                                    <x-icon name="star" class="size-3.5" />
                                    پیش‌فرض برداشت
                                </button>
                            @endunless
                            <button type="button" wire:click="askDelete({{ $card->id }})"
                                    class="flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[10px] font-bold text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-950/40">
                                <x-icon name="trash" class="size-3.5" />
                                حذف
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-300 py-12 text-center dark:border-slate-700">
                    <span class="grid size-16 place-items-center rounded-2xl bg-primary-50 text-primary-400 dark:bg-primary-950">
                        <x-icon name="credit-card" class="size-8" />
                    </span>
                    <p class="text-sm font-extrabold">هنوز کارتی ثبت نکرده‌اید</p>
                    <p class="max-w-[270px] text-xs leading-6 text-slate-400">
                        با افزودن کارت، موقع درخواست برداشت از کیف پول شماره شبا به‌صورت خودکار پر می‌شود.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- hint --}}
        <div class="mt-4 flex items-start gap-2.5 rounded-2xl border border-primary-200 bg-primary-50/70 p-3.5 text-[11px] leading-6 text-primary-800 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
            <x-icon name="shield" class="mt-0.5 size-4 shrink-0" />
            <span>
                اطلاعات کارت فقط برای واریز برداشت‌ها استفاده می‌شود و نزد InstaShop محفوظ است. صحت شماره کارت و شبا هنگام ثبت به‌صورت خودکار بررسی می‌شود.
            </span>
        </div>
    </div>

    {{-- ═══════════════════ ADD CARD MODAL ═══════════════════ --}}
    <div x-data="{ acOpen: $wire.addOpen }" x-on:cards-close-add.window="acOpen = false">
        <x-modal-panel open="$wire.addOpen" max-width="sm" labelledby="add-card-modal-title" in-frame
                       close-js="__ipOpen = false; $wire.closeAdd()">

            <x-slot:header>
                <div class="flex items-center justify-between px-5 py-4">
                    <h3 id="add-card-modal-title" class="flex items-center gap-2 text-sm font-extrabold">
                        <span class="grid size-8 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                            <x-icon name="credit-card" class="size-4" />
                        </span>
                        افزودن کارت بانکی
                    </h3>
                    <button type="button" x-on:click="__ipOpen = false; $wire.closeAdd()"
                            class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>
            </x-slot:header>

            {{-- All formatting / live checks run 100% in the browser (Alpine) —
                 zero network roundtrips while typing, so the input stays instant.
                 wire:model is DEFERRED on purpose: the values reach the server
                 only when the form is submitted, and submitCard() normalizes
                 (Persian digits, spacing, Luhn, mod-97) again server-side. --}}
            <form wire:submit="submitCard" x-data="{
                        cardRaw: '',
                        shebaRaw: '',
                        bins: @js(\App\Support\Banking::bins()),
                        toLatin(v) {
                            return String(v).replace(/[۰-۹٠-٩]/g, (ch) => {
                                const f = '۰۱۲۳۴۵۶۷۸۹'.indexOf(ch);
                                return String(f > -1 ? f : '٠١٢٣٤٥٦٧٨٩'.indexOf(ch));
                            });
                        },
                        fmtCard(e) {
                            const d = this.toLatin(e.target.value).replace(/\D/g, '').slice(0, 16);
                            e.target.value = d.replace(/(\d{4})(?=\d)/g, '$1 ');
                            this.cardRaw = d;
                        },
                        fmtSheba(e) {
                            const d = this.toLatin(e.target.value).replace(/\D/g, '').slice(0, 24);
                            e.target.value = d;
                            this.shebaRaw = d;
                        },
                        luhn(d) {
                            if (d.length !== 16) return null;
                            let sum = 0;
                            for (let i = 0; i < 16; i++) {
                                let n = +d[15 - i];
                                if (i % 2 === 1) { n *= 2; if (n > 9) n -= 9; }
                                sum += n;
                            }
                            return sum % 10 === 0;
                        },
                        get cardValid() { return this.luhn(this.cardRaw); },
                        get bankName() {
                            for (const [bin, name] of Object.entries(this.bins)) {
                                if (this.cardRaw.startsWith(bin)) return name;
                            }
                            return 'کارت بانکی';
                        },
                        get shebaValid() {
                            if (this.shebaRaw.length !== 24) return null;
                            const rearranged = this.shebaRaw.slice(2) + 'IR' + this.shebaRaw.slice(0, 2);
                            let num = '';
                            for (const ch of rearranged) {
                                num += /[A-Z]/.test(ch) ? String(ch.charCodeAt(0) - 55) : ch;
                            }
                            let mod = 0;
                            for (const chunk of num.match(/\d{1,9}/g) || []) {
                                mod = Number(String(mod) + chunk) % 97;
                            }
                            return mod === 1;
                        },
                        fa(n) { return String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]); },
                    }"
                     x-effect="if ($wire.addOpen) { cardRaw = ''; shebaRaw = ''; }"
                     class="space-y-4 px-5 py-5">
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">نام و نام خانوادگی صاحب کارت <span class="text-rose-500">*</span></span>
                    <input type="text" wire:model="cardName" placeholder="مثلاً: مهدی رضایی" maxlength="60"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                    @error('cardName') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">شماره کارت ۱۶ رقمی <span class="text-rose-500">*</span></span>
                    <div class="relative">
                        <input type="text" inputmode="numeric" dir="ltr" wire:model="cardNumber" x-on:input="fmtCard($event)"
                               placeholder="6104 3378 1234 5678" maxlength="19" autocomplete="off"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-left font-mono text-xs font-extrabold tracking-[0.14em] outline-none transition placeholder:tracking-normal placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 rounded-md bg-slate-200/80 px-1.5 py-0.5 font-mono text-[9px] font-black text-slate-500 dark:bg-slate-700 dark:text-slate-300"
                              x-show="cardRaw.length > 0" x-cloak x-text="fa(cardRaw.length) + '/۱۶'"></span>
                    </div>
                    <p class="mt-1.5 flex items-center gap-1 text-[10px] font-bold" x-show="cardRaw.length === 16" x-cloak
                       :class="cardValid ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'">
                        <x-icon name="circle-check" class="size-3" x-show="cardValid" />
                        <x-icon name="alert" class="size-3" x-show="!cardValid" x-cloak />
                        <span x-text="bankName + (cardValid ? ' — شماره کارت معتبر است' : ' — رقم کنترل کارت اشتباه است')"></span>
                    </p>
                    @error('cardNumber') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    <p class="mt-1.5 text-[9.5px] text-slate-400">رقم‌ها هنگام تایپ خودکار ۴ رقم ۴ رقم جدا می‌شوند و در سیستم یکپارچه (بدون فاصله) ذخیره می‌شوند.</p>
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">شماره شبا <span class="text-rose-500">*</span></span>
                    {{-- dir="ltr" on the group → the fixed «IR» prefix sits on the LEFT,
                        exactly where it appears on the printed cheque / bank app. --}}
                    <div dir="ltr" class="flex items-stretch overflow-hidden rounded-xl border border-slate-200 bg-slate-50 transition focus-within:border-primary-400 focus-within:bg-white focus-within:ring-2 focus-within:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus-within:bg-slate-800">
                        <span class="grid shrink-0 place-items-center border-r border-slate-200 bg-slate-100 px-3.5 font-mono text-xs font-black tracking-wider text-slate-500 dark:border-slate-600 dark:bg-slate-700/70 dark:text-slate-300">IR</span>
                        <input type="text" inputmode="numeric" wire:model="shebaDigits" x-on:input="fmtSheba($event)"
                               placeholder="062980000000123456789001" maxlength="24" autocomplete="off"
                               class="w-full min-w-0 border-0 bg-transparent px-3 py-2.5 text-left font-mono text-xs font-extrabold tracking-[0.1em] outline-none placeholder:text-slate-400">
                        <span class="grid shrink-0 place-items-center pl-3 pr-1 font-mono text-[9px] font-black text-slate-400"
                              x-show="shebaRaw.length > 0" x-cloak x-text="fa(shebaRaw.length) + '/۲۴'"></span>
                    </div>
                    <p class="mt-1.5 flex items-center gap-1 text-[10px] font-bold" x-show="shebaRaw.length === 24" x-cloak
                       :class="shebaValid ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'">
                        <x-icon name="circle-check" class="size-3" x-show="shebaValid" />
                        <x-icon name="alert" class="size-3" x-show="!shebaValid" x-cloak />
                        <span x-text="shebaValid ? 'شماره شبا معتبر است' : 'رقم‌های کنترل شبا اشتباه است'"></span>
                    </p>
                    @error('shebaDigits') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    <p class="mt-1.5 text-[9.5px] leading-5 text-slate-400">
                        پیشوند IR ثابت است؛ فقط ۲۴ رقم بعد از آن را وارد کنید (شبا را از اپ بانک کپی کنید و بدون IR بچسبانید).
                    </p>
                </label>

                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                    <span wire:loading wire:target="submitCard" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    ثبت کارت
                </button>
            </form>
        </x-modal-panel>
    </div>
</div>
