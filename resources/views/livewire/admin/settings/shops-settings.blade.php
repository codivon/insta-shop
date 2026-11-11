<div class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-extrabold">تنظیمات فروشگاه‌ها</h2>
            <p class="mt-1 text-xs text-slate-400">تایید خودکار فروشندگان، کمیسیون سراسری و کلید نردبان</p>
        </div>
        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
            <x-icon name="check" class="size-4" />
            ذخیره تنظیمات
        </button>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- ── تایید خودکار فروشنده ── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <x-icon name="users" class="size-5" />
                    </span>
                    <div>
                        <h3 class="text-sm font-extrabold">تایید خودکار فروشنده</h3>
                        <p class="mt-1 text-[11px] leading-5 text-slate-400">
                            با روشن‌بودن این گزینه، فروشندگان تازه‌ثبت‌نام بدون صف تایید، مستقیماً فعال می‌شوند و فروشگاهشان بلافاصله برای مشتریان دیده می‌شود.
                        </p>
                    </div>
                </div>
                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                    <input type="checkbox" wire:model.live="autoApprove" class="peer sr-only">
                    <span class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500 dark:bg-slate-700"></span>
                    <span class="absolute start-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:start-[1.375rem]"></span>
                </label>
            </div>

            <div class="mt-4 flex items-start gap-2 rounded-xl border border-dashed border-slate-300 bg-slate-50/70 px-3 py-2.5 text-[10px] font-semibold leading-5 text-slate-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">
                <x-icon name="info" class="mt-0.5 size-3.5 shrink-0 text-primary-500" />
                <span>حالت خاموش (پیش‌فرض): هر فروشنده جدید در صف «در انتظار تأیید» می‌ماند تا مدیر از پنل فروشندگان تأییدش کند.</span>
            </div>
        </div>

        {{-- ── کمیسیون سراسری ── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <x-icon name="coins" class="size-5" />
                </span>
                <div>
                    <h3 class="text-sm font-extrabold">کمیسیون سراسری</h3>
                    <p class="mt-1 text-[11px] leading-5 text-slate-400">
                        از سهم هر فروشگاه در هر سفارش پرداخت‌شده کسر می‌شود. هزینهٔ ارسال و کد تخفیف هرگز مشمول کمیسیون نیستند.
                    </p>
                </div>
            </div>

            <div class="mt-4 space-y-3">
                <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="نوع کمیسیون">
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-3 text-xs font-extrabold transition {{ $commissionType === 'percent'
                        ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                        : 'border-slate-200 hover:border-primary-300 dark:border-slate-700' }}">
                        <input type="radio" value="percent" wire:model.live="commissionType" class="sr-only">
                        درصدی (٪)
                    </label>
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-3 text-xs font-extrabold transition {{ $commissionType === 'fixed'
                        ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                        : 'border-slate-200 hover:border-primary-300 dark:border-slate-700' }}">
                        <input type="radio" value="fixed" wire:model.live="commissionType" class="sr-only">
                        مبلغ ثابت (تومان)
                    </label>
                </div>

                <div>
                    <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400">
                        {{ $commissionType === 'percent' ? 'درصد کمیسیون (۰ تا ۱۰۰)' : 'مبلغ کمیسیون هر سفارش هر فروشگاه (تومان)' }}
                    </label>
                    <input type="text" inputmode="numeric" wire:model.blur="commissionValue" placeholder="0"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    @error('commissionValue') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                <p class="rounded-xl bg-slate-50 px-3 py-2.5 text-[10px] font-semibold leading-5 text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                    پیش‌فرض صفر است. اولویت نهایی: کمیسیون اختصاصی فروشنده ← کمیسیون پلن اشتراک فعال ← همین نرخ سراسری.
                </p>
            </div>
        </div>

        {{-- ── نردبان: فقط کلید روشن/خاموش (v0.24.0) ── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-950/50 dark:text-orange-400">
                        <x-icon name="rocket" class="size-5" />
                    </span>
                    <div>
                        <h3 class="text-sm font-extrabold">نردبان (نمایش در صدر صفحهٔ اول)</h3>
                        <p class="mt-1 text-[11px] leading-5 text-slate-400">
                            با روشن‌بودن این کلید، بخش نردبان در پنل فروشنده ظاهر می‌شود و فروشنده می‌تواند از بین «طرح‌های نردبان» یکی را بخرد (پرداخت از کیف پول). با خاموش‌کردن آن، بخش نردبان در پنل فروشنده کلاً نمایش داده نمی‌شود.
                        </p>
                    </div>
                </div>
                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                    <input type="checkbox" wire:model.live="bumpEnabled" class="peer sr-only">
                    <span class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-orange-500 dark:bg-slate-700"></span>
                    <span class="absolute start-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:start-[1.375rem]"></span>
                </label>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-dashed border-orange-300 bg-orange-50/70 px-3 py-2.5 text-[10px] font-semibold leading-5 text-orange-700 dark:border-orange-800 dark:bg-orange-950/20 dark:text-orange-300">
                <span class="flex items-start gap-2">
                    <x-icon name="info" class="mt-0.5 size-3.5 shrink-0" />
                    <span>قیمت‌ها و مدت‌ها از صفحهٔ «طرح‌های نردبان» خوانده می‌شود — مثلاً «۱۰ روزه، ۱۵۰٬۰۰۰ تومان». آنجا طرح جدید بسازید.</span>
                </span>
                <a href="{{ route('admin.bump.plans') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-orange-600 px-3 py-2 text-[10px] font-extrabold text-white shadow-sm transition hover:bg-orange-700">
                    <x-icon name="rocket" class="size-3.5" />
                    مدیریت طرح‌های نردبان
                </a>
            </div>
        </div>
    </div>
</div>
