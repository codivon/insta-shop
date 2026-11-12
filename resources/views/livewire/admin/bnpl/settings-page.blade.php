{{-- v0.31.0 — «تنظیمات پرداخت اقساطی»: the DEDICATED admin section of the
     installment (BNPL) trio — per-gateway cards with the full settlement
     policy (دورهٔ هر ۱/۲/۳ ماه، روزِ ماه، خودکار/دستی), the amount window
     (حداقل/حداکثر), installments count, OAuth credentials, mock toggle and
     the v0.29 icon block + a LIVE preview of each policy (سررسیدِ یک خریدِ
     امروز). The engine consumes these from PaymentService/BnplSettlements. --}}
<div class="space-y-4">

    {{-- ── page header: intro + ledger chips + quick links ── --}}
    <div class="rounded-2xl border border-violet-200 bg-gradient-to-l from-violet-50/80 via-white to-white p-4 shadow-sm dark:border-violet-900 dark:from-violet-950/30 dark:via-slate-900 dark:to-slate-900">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex min-w-0 items-start gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-violet-600/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                    <x-icon name="calendar-clock" class="size-6" />
                </span>
                <div class="min-w-0">
                    <h1 class="text-base font-extrabold">تنظیمات پرداخت اقساطی</h1>
                    <p class="mt-1 max-w-3xl text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                        دیجی‌پی، اسنپ‌پی و ترب‌پی واسط‌های پرداخت اقساطی هستند: مشتری <b>قسط اول را هنگام خرید</b> می‌پردازد و باقی اقساط را در پنل خودِ درگاه؛ چون تسویهٔ پذیرندهٔ این درگاه‌ها دوره‌ای انجام می‌شود، سهم فروشنده آنی واریز نمی‌شود و در «تسویه اقساطی» (روزِ سررسید هر درگاه) به کیف پول می‌رود. برای هر درگاه <b>دورهٔ تسویه</b> (هر ماه / هر ۲ ماه / هر ۳ ماه)، <b>روزِ واریز</b> و <b>حالت تسویه</b> (خودکار با کران روزانه یا دستی توسط مدیر) را جداگانه تعیین کنید.
                    </p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('admin.bnpl.settlements') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 rounded-xl border border-violet-300 bg-white px-3 py-2 text-[11px] font-bold text-violet-600 shadow-sm transition hover:bg-violet-50 dark:border-violet-800 dark:bg-slate-900 dark:text-violet-300 dark:hover:bg-violet-950/40">
                    <x-icon name="wallet" class="size-3.5" />
                    تسویه اقساطی
                </a>
                <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400" title="آخرین اجرای کران bnpl:settle">
                    <x-icon name="rotate" class="size-3.5" />
                    @if ($lastRunFa !== '')
                        آخرین کران: {{ $lastRunFa }}
                    @else
                        کران هنوز اجرا نشده
                    @endif
                </span>
            </div>
        </div>

        {{-- ledger chips --}}
        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-5">
            <div class="rounded-xl border border-amber-200 bg-amber-50/70 px-3 py-2 dark:border-amber-900/60 dark:bg-amber-950/20">
                <p class="text-[9px] font-bold text-amber-600 dark:text-amber-400">در انتظار تسویه</p>
                <p class="mt-0.5 text-sm font-extrabold text-amber-700 dark:text-amber-300">{{ \App\Support\Fmt::fa($stats['pending_amount']) }} <span class="text-[9px] font-bold">تومان</span></p>
                <p class="text-[9px] font-bold text-amber-500/80">{{ \App\Support\Fmt::fa($stats['pending_count']) }} مورد</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-800 dark:bg-slate-950/40">
                <p class="text-[9px] font-bold text-slate-500 dark:text-slate-400">سررسید رسیده (کران واریز می‌کند)</p>
                <p class="mt-0.5 text-sm font-extrabold text-slate-700 dark:text-slate-200">{{ \App\Support\Fmt::fa($stats['due_now_count']) }} مورد</p>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50/70 px-3 py-2 dark:border-rose-900/60 dark:bg-rose-950/20">
                <p class="text-[9px] font-bold text-rose-600 dark:text-rose-400">معوق (گذشته از سررسید)</p>
                <p class="mt-0.5 text-sm font-extrabold text-rose-700 dark:text-rose-300">{{ \App\Support\Fmt::fa($stats['overdue_count']) }} مورد</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 px-3 py-2 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                <p class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400">مجموع واریز شده</p>
                <p class="mt-0.5 text-sm font-extrabold text-emerald-700 dark:text-emerald-300">{{ \App\Support\Fmt::fa($stats['paid_amount']) }} <span class="text-[9px] font-bold">تومان</span></p>
            </div>
            <div class="rounded-xl border border-violet-200 bg-violet-50/70 px-3 py-2 dark:border-violet-900/60 dark:bg-violet-950/20">
                <p class="text-[9px] font-bold text-violet-600 dark:text-violet-400">درگاه‌های فعال</p>
                <p class="mt-0.5 text-sm font-extrabold text-violet-700 dark:text-violet-300">
                    {{ \App\Support\Fmt::fa(collect($bnplGateways)->keys()->filter(fn ($g) => ($enabled[$g] ?? false))->count()) }} از {{ \App\Support\Fmt::fa(count($bnplGateways)) }}
                </p>
            </div>
        </div>

        @if (session('admin-bnpl-flash'))
            <div class="mt-3 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-bold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
                <x-icon name="check" class="size-4 shrink-0" />
                {{ session('admin-bnpl-flash') }}
            </div>
        @endif
    </div>

    {{-- ── one rich card per BNPL gateway ── --}}
    <div class="space-y-4">
        @foreach ($bnplGateways as $name => $label)
            @php
                $gwOn = (bool) ($enabled[$name] ?? false);
                $gwCreds = $bnplFields[$name] ?? [];
                $p = $policy[$name];
                $mode = $settlementMode[$name] ?? 'auto';
            @endphp
            <div wire:key="bnpl-card-{{ $name }}"
                 class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 border-e-4 {{ $gwOn ? 'border-e-violet-500' : 'border-e-transparent' }}">

                {{-- header --}}
                <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-4 dark:border-slate-800">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl border border-slate-100 bg-white p-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-950/40">
                        @include('partials.gateway-logo', ['gateway' => $name])
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <h2 class="text-sm font-extrabold">{{ $label }}</h2>
                            <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2 py-0.5 text-[9.5px] font-extrabold text-violet-700 dark:bg-violet-950/60 dark:text-violet-300">
                                <x-icon name="calendar-clock" class="size-2.5" /> اقساطی
                            </span>
                            @if ($gwOn)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[9.5px] font-extrabold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">فعال</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[9.5px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">غیرفعال</span>
                            @endif
                            @if ((bool) ($mock[$name] ?? false))
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[9.5px] font-extrabold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                    <x-icon name="alert" class="size-2.5" /> شبیه‌ساز
                                </span>
                            @endif
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-[9.5px] font-bold text-slate-400">
                            <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono dark:bg-slate-800">{{ $name }}</code>
                            <span>{{ \App\Support\Payments\PaymentService::bnplSettlementPolicyLabel($name) }}</span>
                            @if ($gwOn && ! \App\Support\Payments\PaymentService::hasCredentials($name))
                                <span class="text-amber-500">· مدارک ثبت نشده</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" wire:click="deleteCredentials('{{ $name }}')"
                                wire:confirm="اعتبارنامه‌های ذخیره‌شدهٔ «{{ $label }}» از تنظیمات پاک شود؟"
                                class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40"
                                title="پاک کردن مدارک ذخیره‌شده">
                            <x-icon name="trash" class="size-4" />
                        </button>
                        <label class="relative inline-flex shrink-0 cursor-pointer items-center" title="{{ $gwOn ? 'غیرفعال کردن' : 'فعال کردن' }} {{ $label }}">
                            <input type="checkbox" wire:model="enabled.{{ $name }}" class="peer sr-only">
                            <span class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-violet-500 dark:bg-slate-700"></span>
                            <span class="absolute start-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:start-[1.375rem]"></span>
                            <span class="sr-only">فعال بودن {{ $label }}</span>
                        </label>
                    </div>
                </div>

                {{-- body --}}
                <div class="p-4">
                    <div class="grid gap-4 lg:grid-cols-[1fr_280px]">

                        {{-- ── left: policy form ── --}}
                        <div class="space-y-4">

                            {{-- سیاست تسویه --}}
                            <div>
                                <h3 class="mb-2.5 flex items-center gap-1.5 text-xs font-extrabold">
                                    <x-icon name="calendar-clock" class="size-3.5 text-violet-500" />
                                    سیاست تسویه — واریز سهم فروشنده به کیف پول
                                </h3>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    {{-- دورهٔ تسویه (۱/۲/۳ ماه) --}}
                                    <div class="sm:col-span-2">
                                        <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">دورهٔ تسویه</span>
                                        <div class="grid grid-cols-3 gap-2">
                                            @foreach ([1 => 'هر ماه', 2 => 'هر ۲ ماه یک‌بار', 3 => 'هر ۳ ماه یک‌بار'] as $freqValue => $freqLabel)
                                                <label class="cursor-pointer">
                                                    <input type="radio" value="{{ $freqValue }}" wire:model="settlementFrequency.{{ $name }}" class="peer sr-only">
                                                    <span class="block rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-center text-[11px] font-bold text-slate-500 transition peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-700 dark:border-slate-700 dark:bg-slate-900 dark:peer-checked:border-violet-500 dark:peer-checked:bg-violet-950/40 dark:peer-checked:text-violet-300">
                                                        {{ $freqLabel }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <span class="mt-1 block text-[9px] font-semibold text-slate-400">سررسید هر خرید = نخستین «روزِ تسویه» پس از خرید + (دوره − ۱) ماه؛ تغییر فقط برای خریدهای بعدی اعمال می‌شود.</span>
                                    </div>

                                    {{-- روزِ تسویه --}}
                                    <label class="block">
                                        <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">روزِ واریز تسویه (از ماه)</span>
                                        <input type="number" min="1" max="29" wire:model="settlementDay.{{ $name }}" dir="ltr"
                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900">
                                        <span class="mt-1 block text-[9px] font-semibold text-slate-400">۱ تا ۲۹ — کران خودکار، تسویه‌های سررسیدشده را همین روز واریز می‌کند (مثلاً خرید ۲۵‌ماه → تسویه روزِ ۱ ماه بعد).</span>
                                    </label>

                                    {{-- حالت تسویه --}}
                                    <div>
                                        <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">حالت تسویه</span>
                                        <div class="grid grid-cols-2 gap-2">
                                            <label class="cursor-pointer">
                                                <input type="radio" value="auto" wire:model="settlementMode.{{ $name }}" class="peer sr-only">
                                                <span class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2 py-2.5 text-center text-[10.5px] font-bold text-slate-500 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 dark:border-slate-700 dark:bg-slate-900 dark:peer-checked:border-emerald-500 dark:peer-checked:bg-emerald-950/40 dark:peer-checked:text-emerald-300">
                                                    <x-icon name="check" class="size-3.5 shrink-0" /> خودکار (کران)
                                                </span>
                                            </label>
                                            <label class="cursor-pointer">
                                                <input type="radio" value="manual" wire:model="settlementMode.{{ $name }}" class="peer sr-only">
                                                <span class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2 py-2.5 text-center text-[10.5px] font-bold text-slate-500 transition peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 dark:border-slate-700 dark:bg-slate-900 dark:peer-checked:border-amber-500 dark:peer-checked:bg-amber-950/40 dark:peer-checked:text-amber-300">
                                                    <x-icon name="edit" class="size-3.5 shrink-0" /> دستی (مدیر)
                                                </span>
                                            </label>
                                        </div>
                                        <span class="mt-1 block text-[9px] font-semibold text-slate-400">«دستی» یعنی کران هرگز واریز نمی‌کند و همهٔ ردیف‌ها با «واریز دستی/گروهی» مدیر در تسویه اقساطی جابه‌جا می‌شوند.</span>
                                    </div>
                                </div>
                            </div>

                            {{-- پنجرهٔ مبلغ خرید اقساطی --}}
                            <div>
                                <h3 class="mb-2.5 flex items-center gap-1.5 text-xs font-extrabold">
                                    <x-icon name="wallet" class="size-3.5 text-violet-500" />
                                    پنجرهٔ مبلغ خرید اقساطی
                                </h3>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">حداقل مبلغ کالاها (تومان)</span>
                                        <input type="text" inputmode="numeric" wire:model="minAmount.{{ $name }}" dir="ltr" placeholder="۰ = بدون محدودیت"
                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900">
                                    </label>
                                    <label class="block">
                                        <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">حداکثر مبلغ کالاها (تومان)</span>
                                        <input type="text" inputmode="numeric" wire:model="maxAmount.{{ $name }}" dir="ltr" placeholder="۰ = بی‌نهایت"
                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900">
                                    </label>
                                </div>
                                <span class="mt-1 block text-[9px] font-semibold text-slate-400">سبد خریدی که خارج از این پنجره باشد، این درگاه اقساطی را در صفحهٔ پرداخت نمی‌بیند (سقف اعتباری واقعی درگاه‌ها — کالاها، بدون هزینهٔ ارسال).</span>
                                @error('minAmount.'.$name) <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- تعداد اقساط + شبیه‌ساز --}}
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="block">
                                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">تعداد اقساط (نمایشی)</span>
                                    <input type="number" min="2" max="12" wire:model="installments.{{ $name }}" dir="ltr"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900">
                                    <span class="mt-1 block text-[9px] font-semibold text-slate-400">در بج اقساط صفحهٔ محصول و کارت‌های پرداخت نمایش داده می‌شود (۲ تا ۱۲ — پیش‌فرض ۴).</span>
                                </label>
                                <div>
                                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">شبیه‌ساز محلی (تست)</span>
                                    <label class="flex items-center justify-between gap-3 rounded-xl border border-dashed border-amber-300 bg-amber-50/60 px-3 py-2.5 dark:border-amber-800 dark:bg-amber-950/20">
                                        <span class="text-[10px] font-bold leading-4 text-amber-700 dark:text-amber-300">
                                            {{ $mockAllowed ? 'صفحهٔ پرداخت ساختگی برای تستِ کاملِ جریان اقساط (فقط محیط توسعه اثر دارد).' : 'فقط در محیط توسعه کار می‌کند — روی سرور عملیاتی نادیده گرفته می‌شود.' }}
                                        </span>
                                        <span class="relative shrink-0">
                                            <input type="checkbox" wire:model="mock.{{ $name }}" class="peer sr-only">
                                            <span class="block h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-amber-500 dark:bg-slate-700"></span>
                                            <span class="absolute start-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:start-[1.375rem]"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- ── right: LIVE policy preview ── --}}
                        <aside class="h-fit space-y-2.5 rounded-2xl border border-violet-200 bg-violet-50/60 p-3.5 dark:border-violet-900 dark:bg-violet-950/20">
                            <h3 class="flex items-center gap-1.5 text-[11px] font-extrabold text-violet-700 dark:text-violet-300">
                                <x-icon name="info" class="size-3.5 shrink-0" />
                                پیش‌نمایش سیاست
                            </h3>
                            <div class="rounded-xl bg-white/80 px-3 py-2.5 dark:bg-slate-900/70">
                                <p class="text-[9px] font-bold text-slate-400">سیاست انتخاب‌شده</p>
                                <p class="mt-1 text-xs font-extrabold text-slate-700 dark:text-slate-200">
                                    {{ ['هر ماه', 'هر ۲ ماه یک‌بار', 'هر ۳ ماه یک‌بار'][$p['frequency'] - 1] ?? 'هر ماه' }}
                                    · روزِ {{ \App\Support\Fmt::fa($p['day']) }}
                                    · {{ $p['mode'] === 'manual' ? 'دستی' : 'خودکار' }}
                                </p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-3 py-2.5 dark:bg-slate-900/70">
                                <p class="text-[9px] font-bold text-slate-400">سررسید یک خریدِ همین امروز</p>
                                <p class="mt-1 flex items-center gap-1.5 text-xs font-extrabold text-slate-700 dark:text-slate-200">
                                    <x-icon name="calendar" class="size-3.5 text-violet-500 shrink-0" />
                                    {{ $p['next'] }}
                                    <span class="text-[9px] font-bold text-slate-400">({{ \App\Support\Fmt::fa($p['nextInDays']) }} روز بعد)</span>
                                </p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-3 py-2.5 dark:bg-slate-900/70">
                                <p class="text-[9px] font-bold text-slate-400">ردیف‌های باز این درگاه</p>
                                <p class="mt-1 text-xs font-extrabold text-slate-700 dark:text-slate-200">
                                    {{ \App\Support\Fmt::fa($p['pending']) }} تسویه — {{ \App\Support\Fmt::money($p['pendingAmount']) }}
                                </p>
                            </div>
                            <p class="text-[9px] leading-4 font-semibold text-violet-500/90 dark:text-violet-400/90">
                                «خودکار»: کران روزانهٔ ۰۶:۰۰، تسویه‌های سررسیدشده را واریز می‌کند. «دستی»: فقط مدیر از صفحهٔ تسویه اقساطی واریز می‌کند — ردیف‌های این درگاه با نشان «دستی» مشخص می‌شوند.
                            </p>
                        </aside>
                    </div>

                    {{-- ── credentials + icon ── --}}
                    <div class="mt-4 border-t border-dashed border-slate-200 pt-4 dark:border-slate-800">

                        {{-- آیکون درگاه (همان مدیریت آیکون v0.29.0) --}}
                        <div class="mb-4 flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5 dark:border-slate-800 dark:bg-slate-950/30">
                            <span class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-100 bg-white p-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-950/40">
                                @include('partials.gateway-logo', ['gateway' => $name])
                            </span>
                            <div class="min-w-0 flex-1">
                                <h4 class="flex flex-wrap items-center gap-1.5 text-xs font-extrabold">
                                    <x-icon name="image" class="size-3.5 text-primary-500" />
                                    آیکون درگاه
                                    @if (\App\Support\Payments\GatewayIcons::hasCustom($name))
                                        <span class="rounded-full bg-primary-100 px-2 py-0.5 text-[9px] font-extrabold text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">تصویر سفارشی</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">آیکون رسمی درگاه</span>
                                    @endif
                                </h4>
                                <p class="mt-1 text-[10px] leading-4 text-slate-500 dark:text-slate-400">آیکون اینجا، بج اقساط صفحهٔ محصول و کارت‌های اقساطیِ صفحهٔ پرداخت نمایش داده می‌شود (PNG، JPG یا WebP — حداکثر ۲ مگابایت).</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl bg-primary-600 px-3 py-2 text-[11px] font-bold text-white shadow-sm transition hover:bg-primary-700">
                                    <x-icon name="image-plus" class="size-3.5" />
                                    {{ \App\Support\Payments\GatewayIcons::hasCustom($name) ? 'تغییر آیکون' : 'بارگذاری آیکون' }}
                                    <input type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" class="sr-only" wire:model="iconFiles.{{ $name }}">
                                </label>
                                @if (\App\Support\Payments\GatewayIcons::hasCustom($name))
                                    <button type="button" wire:click="removeIcon('{{ $name }}')"
                                            wire:confirm="آیکون سفارشی «{{ $label }}» حذف شود و آیکون رسمی درگاه بازگردد؟"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold text-slate-500 transition hover:border-rose-300 hover:text-rose-600 dark:border-slate-700 dark:bg-slate-900">
                                        <x-icon name="rotate" class="size-3.5" />
                                        بازگشت به پیش‌فرض
                                    </button>
                                @endif
                                <span wire:loading wire:target="iconFiles.{{ $name }}" class="inline-flex items-center gap-1.5 text-[10px] font-bold text-primary-500">
                                    <x-icon name="rotate" class="size-3.5 animate-spin" />
                                    در حال بارگذاری…
                                </span>
                            </div>
                            @error('iconFiles.'.$name)
                                <p class="w-full text-[10px] font-bold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- اعتبارنامه‌ها --}}
                        @if (count($gwCreds) > 0)
                            <h3 class="mb-3 text-xs font-extrabold">اعتبارنامه‌های «{{ $label }}» <span class="text-[9px] font-normal text-slate-400">(OAuth: Client ID/Secret + نام کاربری/رمز — از پنل پذیرندهٔ درگاه)</span></h3>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($gwCreds as $fieldKey => $fieldLabel)
                                    <label class="block">
                                        <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                            {{ $fieldLabel }}
                                            <span class="text-[9px] font-normal text-slate-400">(خالی = استفاده از env)</span>
                                        </span>
                                        <input type="{{ str_contains($fieldKey, 'password') || str_contains($fieldKey, 'secret') ? 'password' : 'text' }}"
                                               wire:model="fields.{{ $name }}.{{ $fieldKey }}"
                                               dir="ltr" autocomplete="off"
                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900">
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── bottom bar: info + save ── --}}
    <div class="sticky bottom-3 z-10 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 shadow-lg shadow-slate-900/5 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
        <p class="flex items-start gap-2 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
            <x-icon name="info" class="mt-0.5 size-3.5 shrink-0 text-primary-500" />
            <span>
                درگاه‌های اقساطی هرگز درگاه «پیش‌فرض» نمی‌شوند؛ برای ظاهر شدن در صفحهٔ پرداخت باید اینجا فعال باشند، مدارکشان کامل باشد و فروشندهٔ سبد هم در پنل خودش فعال کرده باشد. تغییر دورهٔ تسویه فقط روی خریدهای بعدی اثر دارد.
            </span>
        </p>
        <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700 disabled:opacity-60">
            <span wire:loading wire:target="save"><x-icon name="rotate" class="size-4 animate-spin" /></span>
            ذخیره تنظیمات اقساطی
        </button>
    </div>
</div>
