{{-- ═══════════════════ FEATURES BENTO ═══════════════════ --}}
<section id="features" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-20 sm:px-6">
    <div class="ip-reveal mx-auto max-w-2xl text-center">
        <span class="text-xs font-black tracking-widest text-primary-600 dark:text-primary-400">{{ $c['eyebrow'] }}</span>
        <h2 class="mt-2 text-2xl font-extrabold sm:text-4xl">{{ $c['title'] }}</h2>
        <p class="mt-4 text-sm leading-7 text-slate-500 dark:text-slate-400">
            {{ $c['lead'] }}
        </p>
    </div>

    <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-6">
        {{-- storefront address --}}
        <div class="ip-reveal group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl md:col-span-2 lg:col-span-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="pointer-events-none absolute -left-16 -top-16 size-48 rounded-full bg-primary-500/10 blur-2xl transition group-hover:bg-primary-500/20"></div>
            <div class="relative flex h-full flex-col justify-between gap-5 sm:flex-row-reverse sm:items-center">
                <div class="sm:max-w-[55%]">
                    <span class="grid size-12 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="instagram" class="size-6" />
                    </span>
                    <h3 class="mt-4 text-lg font-extrabold">فروشگاه با آدرس آیدی اینستاگرام</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                        هر فروشنده یک فروشگاه مستقل با آدرس اختصاصی می‌گیرد؛ لینک را در بیو اینستاگرام بگذار و
                        مشتری مستقیم وارد فروشگاهت شود.
                    </p>
                </div>
                <div class="w-full shrink-0 sm:w-[42%]">
                    <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-3 shadow-inner dark:border-slate-700 dark:bg-slate-950">
                        <x-icon name="lock" class="size-3.5 shrink-0 text-emerald-500" />
                        <span class="truncate text-xs font-bold text-slate-600 dark:text-slate-300" dir="ltr">shop.ir/<span class="text-primary-600 dark:text-primary-400">@your_shop</span></span>
                    </div>
                    <div class="mt-2.5 space-y-2">
                        <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-[11px] font-bold text-slate-500 dark:bg-slate-950 dark:text-slate-400"><x-icon name="external-link" class="size-3.5 text-primary-500" /> لینک در بیو یا استوری</div>
                        <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-[11px] font-bold text-slate-500 dark:bg-slate-950 dark:text-slate-400"><x-icon name="search" class="size-3.5 text-primary-500" /> مرور و خرید بدون ثبت‌نام</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- OTP login --}}
        <div class="ip-reveal group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl md:col-span-1 lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <span class="grid size-12 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                <x-icon name="phone" class="size-6" />
            </span>
            <h3 class="mt-4 text-lg font-extrabold">ورود با کد یکبارمصرف</h3>
            <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">بدون رمز، بدون ایمیل — فقط شماره موبایل و کد پیامکی.</p>
            <div class="mt-4 flex justify-center gap-2" dir="ltr" aria-hidden="true">
                @foreach (['۷', '۲', '۵', '۱'] as $d)
                    <span class="grid size-10 place-items-center rounded-xl border-2 border-slate-200 bg-slate-50 text-base font-black text-slate-700 transition group-hover:border-primary-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">{{ $d }}</span>
                @endforeach
            </div>
        </div>

        {{-- wallet --}}
        <div class="ip-reveal group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <span class="grid size-12 place-items-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                <x-icon name="wallet" class="size-6" />
            </span>
            <h3 class="mt-4 text-lg font-extrabold">کیف پول و تسویه</h3>
            <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">موجودی لحظه‌ای، برداشت به کارت بانکی و تاریخچهٔ کامل تراکنش‌ها.</p>
            <div class="mt-4 rounded-2xl border border-emerald-200/70 bg-emerald-50 px-4 py-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                <div class="text-[10px] font-bold text-emerald-700/70 dark:text-emerald-400/80">موجودی قابل برداشت</div>
                <div class="mt-0.5 text-sm font-black text-emerald-700 dark:text-emerald-300">۲٬۴۵۰٬۰۰۰ تومان</div>
            </div>
        </div>

        {{-- shipping --}}
        <div class="ip-reveal group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <span class="grid size-12 place-items-center rounded-2xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                <x-icon name="truck" class="size-6" />
            </span>
            <h3 class="mt-4 text-lg font-extrabold">ارسال و انتخاب روز تحویل</h3>
            <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">پست پیشتاز، تیپاکس یا ارسال خودِ فروشنده — با هزینهٔ شهر/استان جداگانه.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="rounded-full bg-amber-50 px-3 py-1.5 text-[11px] font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">پست پیشتاز</span>
                <span class="rounded-full bg-slate-50 px-3 py-1.5 text-[11px] font-bold text-slate-500 dark:bg-slate-900 dark:text-slate-400">تیپاکس</span>
                <span class="rounded-full bg-slate-50 px-3 py-1.5 text-[11px] font-bold text-slate-500 dark:bg-slate-900 dark:text-slate-400">ارسال شخصی</span>
            </div>
        </div>

        {{-- discounts --}}
        <div class="ip-reveal group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <span class="grid size-12 place-items-center rounded-2xl bg-pink-500/10 text-pink-600 dark:bg-pink-500/15 dark:text-pink-400">
                <x-icon name="tag" class="size-6" />
            </span>
            <h3 class="mt-4 text-lg font-extrabold">کد تخفیف و جشنوارهٔ فروش</h3>
            <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">تخفیف درصدی یا مبلغی با سقف و تاریخ انقضا؛ کد از هر فروشگاه.</p>
            <div class="mt-4 flex items-center justify-between rounded-2xl border-2 border-dashed border-pink-300/70 bg-pink-50/50 px-4 py-3 dark:border-pink-500/30 dark:bg-pink-500/10">
                <span class="text-sm font-black tracking-wider text-pink-600 dark:text-pink-400" dir="ltr">WELCOME</span>
                <span class="rounded-lg bg-pink-600 px-2 py-1 text-[10px] font-black text-white">۱۵٪ تخفیف</span>
            </div>
        </div>

        {{-- PWA apps --}}
        <div class="ip-reveal group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl lg:col-span-3 dark:border-slate-800 dark:bg-slate-900">
            <div class="pointer-events-none absolute -bottom-20 -right-20 size-56 rounded-full bg-orange-400/10 blur-2xl"></div>
            <span class="grid size-12 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                <x-icon name="smartphone" class="size-6" />
            </span>
            <h3 class="mt-4 text-lg font-extrabold">دو اپ مستقل، نصب‌شدنی روی گوشی</h3>
            <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                اپ فروشنده و اپ مشتری، هر کدام با نام و آیکون جداگانه؛ بدون استور، مستقیم از مرورگر نصب می‌شوند
                و مثل اپ بومی باز می‌مانند.
            </p>
            <div class="mt-4 flex items-center gap-3">
                <span class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-950">
                    <span class="grid size-8 place-items-center rounded-xl bg-gradient-to-br from-fuchsia-600 via-pink-500 to-orange-500 text-white"><x-icon name="bag" class="size-4" /></span>
                    <span class="text-xs font-extrabold text-slate-700 dark:text-slate-200">اپ فروشنده</span>
                </span>
                <span class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-950">
                    <span class="grid size-8 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white"><x-icon name="cart" class="size-4" /></span>
                    <span class="text-xs font-extrabold text-slate-700 dark:text-slate-200">اپ مشتری</span>
                </span>
                <span class="hidden items-center gap-1 text-[11px] font-bold text-emerald-600 sm:flex dark:text-emerald-400"><x-icon name="circle-check" class="size-3.5" /> نصب با یک لمس</span>
            </div>
        </div>

        {{-- payment gateways --}}
        <div class="ip-reveal group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl lg:col-span-3 dark:border-slate-800 dark:bg-slate-900">
            <div class="pointer-events-none absolute -bottom-20 -left-20 size-56 rounded-full bg-primary-500/10 blur-2xl"></div>
            <span class="grid size-12 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                <x-icon name="credit-card" class="size-6" />
            </span>
            <h3 class="mt-4 text-lg font-extrabold">پرداخت امن با درگاه‌های ایرانی</h3>
            <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                زرین‌پال، زیبال، بانک ملت، سپ و آیدی‌پی — درگاه از پنل مدیریت انتخاب و در چند دقیقه وصل می‌شود.
            </p>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach (['زرین‌پال', 'زیبال', 'بانک ملت', 'سپ', 'آیدی‌پی'] as $g)
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-extrabold text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">{{ $g }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- small features --}}
    <div class="ip-reveal mt-12">
        <h3 class="text-center text-sm font-black tracking-widest text-slate-400 dark:text-slate-500">{{ $c['small_title'] }}</h3>
        <ul class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($c['small'] as $f)
                <li class="flex items-center gap-2.5 rounded-2xl border border-slate-100 bg-white px-3.5 py-3 text-[13px] font-bold text-slate-600 transition hover:border-primary-200 hover:text-primary-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-primary-800 dark:hover:text-primary-300">
                    <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="{{ $f['icon'] }}" class="size-4" />
                    </span>
                    {{ $f['t'] }}
                </li>
            @endforeach
        </ul>
    </div>
</section>
