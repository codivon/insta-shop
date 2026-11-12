<div class="ip-fade-up"
     x-data="{ toast: '' }"
     @shop-image-saved.window="toast = ($event.detail.label ?? '') + ' با موفقیت آپلود شد ✅'; setTimeout(() => toast = '', 2800)">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('seller.shop') }}" wire:navigate
                   class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                   aria-label="بازگشت به فروشگاه من">
                    <x-icon name="arrow-right" class="size-5" />
                </a>
                <div>
                    <div class="text-sm font-extrabold leading-5">ویرایش فروشگاه</div>
                    <div class="text-[11px] text-slate-400">هویت و اطلاعات نمایشی فروشگاه</div>
                </div>
            </div>
            <x-seller-notif-bell class="ms-auto" />
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    <form wire:submit="save" class="px-4 pt-4">
        {{-- ───────── Identity card (logo + banner) ───────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="flex items-center gap-2 text-xs font-extrabold">
                <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="palette" class="size-3.5" /></span>
                هویت بصری
                <span class="text-[10px] font-semibold text-slate-400">— با انتخاب فایل، بلافاصله آپلود و ذخیره می‌شود</span>
            </h3>

            {{-- banner preview --}}
            <div class="relative mt-3 h-28 overflow-hidden rounded-2xl bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500">
                @if ($shop->banner_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->banner_path) }}?v={{ \Illuminate\Support\Facades\Storage::disk('public')->lastModified($shop->banner_path) }}" alt="بنر فروشگاه" class="absolute inset-0 size-full object-cover">
                @endif
                <div class="absolute inset-x-2 bottom-2 flex items-center gap-1.5">
                    <label class="flex flex-1 cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-white/90 px-3 py-2 text-[10px] font-bold text-slate-700 shadow backdrop-blur transition hover:bg-white dark:bg-slate-900/90 dark:text-slate-200">
                        <x-icon name="camera" class="size-3.5" />
                        {{ $shop->banner_path ? 'تغییر بنر' : 'افزودن بنر' }}
                        <input type="file" wire:model="banner" accept="image/png,image/jpeg,image/webp,.jpg,.jpeg,.png,.webp" class="sr-only">
                    </label>
                    @if ($shop->banner_path)
                        <button type="button" wire:click="removeBanner" aria-label="حذف بنر"
                                class="grid size-9 place-items-center rounded-xl bg-rose-500/90 text-white shadow backdrop-blur transition hover:bg-rose-600">
                            <x-icon name="trash" class="size-3.5" />
                        </button>
                    @endif
                </div>
                <div wire:loading wire:target="banner" class="absolute inset-0 grid place-items-center bg-black/30">
                    <span class="inline-block size-6 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                </div>
                <div class="absolute inset-x-2 top-2">
                    <x-upload-progress target="banner" label="در حال آپلود بنر" />
                </div>
            </div>

            {{-- logo preview --}}
            <div class="mt-3 flex items-center gap-3">
                <div class="relative size-16 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                    @if ($shop->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($shop->logo_path) }}?v={{ \Illuminate\Support\Facades\Storage::disk('public')->lastModified($shop->logo_path) }}" alt="لوگوی فروشگاه" class="size-full object-cover">
                    @else
                        <span class="grid size-full place-items-center text-xl font-black text-primary-500">{{ mb_substr($name ?: $shop->name, 0, 1) }}</span>
                    @endif
                    <div wire:loading wire:target="logo" class="absolute inset-0 grid place-items-center bg-black/30">
                        <span class="inline-block size-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    </div>
                </div>
                <div class="flex min-w-0 flex-col gap-1.5">
                    <label class="flex w-fit cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-1.5 text-[10px] font-bold text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300">
                        <x-icon name="image" class="size-3.5" />
                        {{ $shop->logo_path ? 'تغییر لوگو' : 'افزودن لوگو' }}
                        <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/webp,.jpg,.jpeg,.png,.webp" class="sr-only">
                    </label>
                    <x-upload-progress target="logo" label="در حال آپلود لوگو" />
                    @if ($shop->logo_path)
                        <button type="button" wire:click="removeLogo" class="flex w-fit items-center gap-1.5 text-[10px] font-bold text-rose-500 hover:text-rose-600">
                            <x-icon name="trash" class="size-3" />
                            حذف لوگو
                        </button>
                    @endif
                </div>
            </div>
            @error('logo') <p class="mt-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            @error('banner') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
        </div>

        {{-- ───────── Basic info card ───────── --}}
        <div class="mt-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="flex items-center gap-2 text-xs font-extrabold">
                <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="store" class="size-3.5" /></span>
                اطلاعات فروشگاه
            </h3>

            <div class="mt-3 space-y-3">
                {{-- ── نام و نام خانوادگی فروشنده (از پروفایل به اینجا منتقل شد) ── --}}
                <div>
                    <label for="se-fullname" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">
                        نام و نام خانوادگی شما <span class="text-[9px] font-semibold text-slate-400">— نام نمایشی در پنل و ارتباط با مشتریان</span>
                    </label>
                    <input id="se-fullname" type="text" wire:model="fullName" maxlength="60" placeholder="مثلاً: مهدی رضایی"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                    @error('fullName') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="se-name" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">نام فروشگاه <span class="text-rose-500">*</span></label>
                    <input id="se-name" type="text" wire:model="name" maxlength="120" placeholder="مثال: فروشگاه زعفران قائنات"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                    @error('name') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- ── شناسه اینستاگرام: قفل‌شده (هندل حساب + لینک فروشگاه) ── --}}
                <div>
                    <label for="se-insta" class="mb-1.5 flex items-center gap-1.5 text-[11px] font-bold text-slate-500 dark:text-slate-300">
                        شناسه اینستاگرام
                        <span class="flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            <x-icon name="lock" class="size-2.5" />
                            غیرقابل تغییر
                        </span>
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400" dir="ltr">@</span>
                        <input id="se-insta" type="text" dir="ltr" value="{{ $instagramUsername }}" readonly tabindex="-1"
                               class="w-full cursor-not-allowed rounded-2xl border border-slate-200 bg-slate-100 py-3 pl-4 pr-8 text-left text-xs font-bold text-slate-500 opacity-90 outline-none dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400">
                    </div>
                    <div class="mt-1.5 flex items-start gap-1.5 rounded-xl bg-amber-50 px-3 py-2 text-[10px] font-semibold leading-5 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300">
                        <x-icon name="info" class="mt-0.5 size-3.5 shrink-0" />
                        <span>
                            شناسه اینستاگرام پس از ساخت فروشگاه قابل ویرایش نیست. برای تغییر آن باید از
                            <b>پروفایل → پشتیبانی</b> تیکت ارسال کنید؛ در نظر داشته باشید با تغییر شناسه،
                            <b>لینک صفحه فروشگاه شما نیز تغییر می‌کند</b>.
                        </span>
                    </div>
                </div>

                <div>
                    <label for="se-about" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">درباره فروشگاه</label>
                    <textarea id="se-about" wire:model="about" rows="3" maxlength="2000"
                              placeholder="داستان فروشگاه، محصولات شاخص، سیاست ارسال..."
                              class="scroll-slim w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold leading-6 outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800"></textarea>
                    @error('about') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="se-phone" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">شماره تماس</label>
                        <input id="se-phone" type="tel" inputmode="numeric" dir="ltr" wire:model="phone" placeholder="09123456789"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-bold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                        @error('phone') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="se-status" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">وضعیت فروشگاه</label>
                        <select id="se-status" wire:model="status"
                                class="w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                            <option value="active">فعال (قابل نمایش)</option>
                            <option value="paused">مکث‌شده (نمایش عمومی خاموش)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="se-address" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">آدرس</label>
                    <textarea id="se-address" wire:model="address" rows="2" maxlength="300"
                              placeholder="استان، شهر، آدرس دقیق"
                              class="scroll-slim w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold leading-6 outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800"></textarea>
                    @error('address') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- ── نمایش موجودی (با تأیید فروشنده) ── --}}
                <label for="se-show-stock" class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800">
                    <span class="min-w-0">
                        <span class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">نمایش موجودی محصولات</span>
                        <span class="mt-0.5 block text-[9.5px] leading-4 text-slate-400">با فعال‌سازی، تعداد موجودی روی صفحهٔ فروشگاه و صفحهٔ محصول نمایش داده می‌شود.</span>
                    </span>
                    <span class="relative shrink-0">
                        <input id="se-show-stock" type="checkbox" wire:model="showStock" class="peer sr-only">
                        <span class="block h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-primary-600 dark:bg-slate-600"></span>
                        <span class="absolute right-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:-translate-x-5"></span>
                    </span>
                </label>
            </div>

        </div>

        {{-- ── حمل و نقل — shortcut to the shipping methods manager ── --}}
        <a href="{{ route('seller.shipping') }}" wire:navigate
           class="mt-3 flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-emerald-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-emerald-800">
            <span class="flex items-center gap-2.5 text-xs font-bold">
                <span class="grid size-9 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <x-icon name="truck" class="size-4.5" />
                </span>
                حمل و نقل
            </span>
            <span class="flex items-center gap-1 text-[10px] text-slate-400">
                مدیریت روش‌های ارسال و هزینه‌ها
                <x-icon name="chevron-left" class="size-3.5" />
            </span>
        </a>

        {{-- ── کدهای تخفیف — shortcut to the discount codes manager ── --}}
        <a href="{{ route('seller.discounts') }}" wire:navigate
           class="mt-2 flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-rose-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-rose-800">
            <span class="flex items-center gap-2.5 text-xs font-bold">
                <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-fuchsia-100 to-rose-100 text-rose-600 dark:from-fuchsia-950/50 dark:to-rose-950/50 dark:text-rose-400">
                    <x-icon name="tag" class="size-4.5" />
                </span>
                کدهای تخفیف
            </span>
            <span class="flex items-center gap-1 text-[10px] text-slate-400">
                ساخت و مدیریت کدهای خرید
                <x-icon name="chevron-left" class="size-3.5" />
            </span>
        </a>

        {{-- ───────── پرداخت اقساطی (v0.30.0 — درگاه‌های اقساطی) ───────── --}}
        @if (count($bnplGateways) > 0)
            <div class="mt-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-950 dark:text-violet-400"><x-icon name="calendar-clock" class="size-3.5" /></span>
                    پرداخت اقساطی — خرید حالا، پرداخت بعداً
                </h3>
                <p class="mt-1.5 rounded-xl bg-violet-50 px-3 py-2 text-[10px] font-semibold leading-5 text-violet-600 dark:bg-violet-950/30 dark:text-violet-300">
                    با فعال‌سازی هر روش، مشتری می‌تواند محصولات شما را اقساطی بخرد (قسط اول هنگام خرید از درگاه پرداخت می‌شود و باقی اقساط را در پنل خودِ درگاه می‌پردازد). چون تسویهٔ این درگاه‌ها <b>سرِ ماه</b> انجام می‌شود، مبلغ فروش شما پس از تسویهٔ درگاه به کیف پول واریز می‌شود. اگر برای این روش کارمزد تعیین کنید، هنگام پرداخت اقساطی به جمع کالاها اضافه می‌شود و شامل هزینهٔ ارسال نیست.
                </p>

                <div class="mt-3 space-y-3">
                    @foreach ($bnplGateways as $bnplKey => $bnplLabel)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5 dark:border-slate-700 dark:bg-slate-800/60">
                            <div class="flex items-center gap-3">
                                <span class="grid size-11 shrink-0 place-items-center rounded-xl border border-slate-100 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                    @include('partials.gateway-logo', ['gateway' => $bnplKey])
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="text-xs font-extrabold">{{ $bnplLabel }}</span>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2 py-0.5 text-[8.5px] font-extrabold text-violet-700 dark:bg-violet-950/60 dark:text-violet-300">
                                            {{ \App\Support\Fmt::fa(\App\Support\Payments\PaymentService::bnplInstallments($bnplKey)) }} قسط
                                        </span>
                                    </div>
                                    <div class="mt-0.5 text-[9.5px] font-semibold text-slate-400">تسویه‌های این روش سرِ ماه به کیف پول شما واریز می‌شود</div>
                                </div>
                                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                    <input type="checkbox" wire:model="bnplEnabled.{{ $bnplKey }}" class="peer sr-only">
                                    <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-violet-600 dark:bg-slate-600"></span>
                                    <span class="absolute right-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:-translate-x-5"></span>
                                    <span class="sr-only">فعال‌سازی خرید اقساطی با {{ $bnplLabel }}</span>
                                </label>
                            </div>

                            {{-- جزئیات — فقط وقتی روش فعال است --}}
                            @if ((bool) ($bnplEnabled[$bnplKey] ?? false))
                                <div class="mt-3 space-y-2.5 border-t border-dashed border-slate-200 pt-3 dark:border-slate-700">
                                    {{-- نمایش در صفحهٔ محصول --}}
                                    <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl bg-white px-3 py-2.5 dark:bg-slate-900">
                                        <span class="min-w-0">
                                            <span class="block text-[10.5px] font-bold text-slate-600 dark:text-slate-300">نمایش در صفحهٔ محصول</span>
                                            <span class="mt-0.5 block text-[9px] leading-4 text-slate-400">بج کوچک «امکان خرید اقساطی با {{ $bnplLabel }}» زیر قیمتِ هر محصول شما نمایش داده می‌شود.</span>
                                        </span>
                                        <span class="flex shrink-0 items-center gap-2">
                                            <input type="checkbox" wire:model="bnplShowProduct.{{ $bnplKey }}" class="size-4 rounded-md border-slate-300 text-violet-600 transition focus:ring-violet-500/30 dark:border-slate-600 dark:bg-slate-800">
                                        </span>
                                    </label>

                                    {{-- کارمزد (درصدی یا مبلغ ثابت) --}}
                                    <div class="rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <span class="block text-[10.5px] font-bold text-slate-600 dark:text-slate-300">کارمزد خرید اقساطی <span class="text-[9px] font-semibold text-slate-400">— هنگام پرداخت اقساطی به جمع کالاها اضافه می‌شود</span></span>
                                        <div class="mt-2 grid grid-cols-3 gap-2">
                                            <label class="col-span-1">
                                                <span class="sr-only">نوع کارمزد</span>
                                                <select wire:model="bnplFeeType.{{ $bnplKey }}"
                                                        class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-2 py-2.5 text-[10.5px] font-bold outline-none transition focus:border-violet-500 dark:border-slate-700 dark:bg-slate-800">
                                                    <option value="none">بدون کارمزد</option>
                                                    <option value="percent">درصدی (٪)</option>
                                                    <option value="fixed">مبلغ ثابت</option>
                                                </select>
                                            </label>
                                            <label class="col-span-2 @if(($bnplFeeType[$bnplKey] ?? 'none') === 'none') hidden @endif">
                                                <span class="sr-only">مقدار کارمزد</span>
                                                <input type="text" inputmode="numeric" dir="ltr" wire:model="bnplFeeValue.{{ $bnplKey }}"
                                                       placeholder="{{ ($bnplFeeType[$bnplKey] ?? 'none') === 'percent' ? 'مثلاً ۵' : 'مثلاً 20000' }}"
                                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-center text-[11px] font-bold outline-none transition placeholder:text-slate-400 focus:border-violet-500 dark:border-slate-700 dark:bg-slate-800">
                                            </label>
                                        </div>
                                        <div class="mt-1.5 text-[9px] font-semibold leading-4 text-slate-400">
                                            @if (($bnplFeeType[$bnplKey] ?? 'none') === 'percent')
                                                درصد کارمزد (۱ تا ۱۰۰) — مثلاً ۵ یعنی ۵٪ از کالاهای این سفارش
                                            @elseif (($bnplFeeType[$bnplKey] ?? 'none') === 'fixed')
                                                مبلغ ثابت به تومان — یک‌بار به‌ازای هر سفارش اقساطیِ شما اضافه می‌شود
                                            @endif
                                        </div>
                                        @error('bnplFeeValue.'.$bnplKey) <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ───────── Pinned actions (fixed at the bottom of the viewport on phones) ───────── --}}
        <div class="sticky bottom-0 z-20 -mx-4 mt-4 flex gap-2 border-t border-slate-200/80 bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/95">
            <button type="submit"
                    class="flex-1 rounded-2xl bg-primary-600 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-[.98] disabled:opacity-60"
                    wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">ذخیره تغییرات</span>
                <span wire:loading class="flex items-center justify-center gap-2" wire:target="save">
                    <span class="inline-block size-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    در حال ذخیره...
                </span>
            </button>
            <a href="{{ route('seller.shop') }}" wire:navigate
               class="grid place-items-center rounded-2xl border border-slate-200 bg-white px-5 text-xs font-bold text-slate-500 transition hover:border-slate-300 hover:text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                انصراف
            </a>
        </div>
    </form>

    {{-- upload toast --}}
    <div x-cloak x-show="toast" x-transition.opacity
         class="fixed bottom-24 left-1/2 z-40 w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95"
         role="status">
        <span x-text="toast"></span>
    </div>
</div>
