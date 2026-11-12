<div class="ip-fade-up space-y-4">

    {{-- ── flash ── --}}
    @if (session('admin-payment-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4500)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-payment-flash') }}
        </div>
    @endif
    @error('gateway') <p class="text-xs font-bold text-rose-500">{{ $message }}</p> @enderror

    {{-- ── header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="flex items-center gap-2 text-lg font-black">
                <span class="grid size-9 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                    <x-icon name="credit-card" class="size-5" />
                </span>
                درگاه‌های پرداخت
            </h1>
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                مدیریت و پیکربندی درگاه‌های پرداخت آنلاین سایت — چند درگاه می‌توانند هم‌زمان فعال باشند و مشتری در صفحهٔ پرداخت بین آن‌ها انتخاب کند.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.payment.logs') }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold text-slate-600 shadow-sm transition hover:border-primary-300 hover:text-primary-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-primary-800">
                <x-icon name="list" class="size-3.5" />
                تراکنش‌های بانکی
            </a>
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-2 text-[11px] font-extrabold {{ $this->enabledCount > 0
                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' }}">
                <x-icon name="{{ $this->enabledCount > 0 ? 'check' : 'alert' }}" class="size-3.5" />
                {{ \App\Support\Fmt::fa($this->enabledCount) }} از {{ \App\Support\Fmt::fa(count($gateways)) }} درگاه فعال
            </span>
        </div>
    </div>

    {{-- ── toolbar: sort + bulk actions ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <label class="inline-flex items-center gap-2 text-[11px] font-bold text-slate-500 dark:text-slate-400">
            <x-icon name="arrow-up-left" class="size-3.5" />
            <span class="sr-only">ترتیب نمایش</span>
            <select wire:model.live="sort"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-bold shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                <option value="default">ترتیب پیش‌فرض</option>
                <option value="enabled">فعال‌ها اول</option>
                <option value="disabled">غیرفعال‌ها اول</option>
            </select>
        </label>

        @if (count($selected) > 0)
            <div class="flex flex-wrap items-center gap-2 rounded-xl border border-primary-200 bg-primary-50 px-3 py-2 dark:border-primary-900 dark:bg-primary-950/40">
                <span class="text-[11px] font-extrabold text-primary-700 dark:text-primary-300">
                    {{ \App\Support\Fmt::fa(count($selected)) }} درگاه انتخاب شده
                </span>
                <button type="button" wire:click="bulkEnable"
                        class="rounded-lg bg-emerald-600 px-3 py-1.5 text-[11px] font-bold text-white transition hover:bg-emerald-700">
                    فعال‌سازی
                </button>
                <button type="button" wire:click="bulkDisable"
                        class="rounded-lg bg-slate-600 px-3 py-1.5 text-[11px] font-bold text-white transition hover:bg-slate-700">
                    غیرفعال‌سازی
                </button>
                <button type="button" wire:click="$set('selected', [])"
                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-[11px] font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    لغو انتخاب
                </button>
            </div>
        @endif
    </div>

    {{-- ── no-gateway warning ── --}}
    @if ($this->enabledCount === 0)
        <div class="flex items-start gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
            <x-icon name="alert" class="mt-0.5 size-4 shrink-0" />
            <span>هیچ درگاهی فعال نیست؛ درگاه آزمایشی فقط در محیط توسعه کار می‌کند. حداقل یک درگاه را روشن کنید و مدارک آن را تکمیل نمایید تا مشتری بتواند پرداخت آنلاین انجام دهد.</span>
        </div>
    @endif

    {{-- ── gateway rows ── --}}
    <div class="space-y-3">
        @foreach ($this->sortedGateways as $name => $label)
            @php
                $gwOn = (bool) ($enabled[$name] ?? false);
                $gwCreds = $fields[$name] ?? [];
                $gwCapable = array_key_exists($name, $splitCapable);
                $gwIsDefault = $gateway === $name;
                $gwTest = $name === 'local';
                $gwLockedLocal = $gwTest && ! \App\Support\Payments\PaymentService::localGatewayAllowed();
                $stripe = $gwLockedLocal ? 'border-e-amber-400' : ($gwOn ? 'border-e-emerald-500' : 'border-e-transparent');
            @endphp
            <div wire:key="gw-row-{{ $name }}" x-data="{ open: false }"
                 class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition dark:border-slate-800 dark:bg-slate-900 border-e-4 {{ $stripe }}">
                <div class="flex items-center gap-3 p-3.5">

                    {{-- bulk checkbox --}}
                    <label class="shrink-0 cursor-pointer" title="انتخاب برای عملیات گروهی">
                        <input type="checkbox" wire:model="selected" value="{{ $name }}"
                               class="size-4 rounded-md border-slate-300 text-primary-600 transition focus:ring-primary-500/30 dark:border-slate-600 dark:bg-slate-800">
                        <span class="sr-only">انتخاب {{ $label }}</span>
                    </label>

                    {{-- brand tile --}}
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl border border-slate-100 bg-white p-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-950/40">
                        @include('partials.gateway-logo', ['gateway' => $name])
                    </span>

                    {{-- title + badges --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <h2 class="text-sm font-extrabold">{{ $label }}</h2>
                            @if ($gwIsDefault)
                                <span class="inline-flex items-center gap-1 rounded-full bg-primary-600 px-2 py-0.5 text-[9.5px] font-extrabold text-white">
                                    <x-icon name="star" class="size-3" /> پیش‌فرض
                                </span>
                            @endif
                            @if ($gwOn && ! $gwLockedLocal)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[9.5px] font-extrabold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">فعال</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[9.5px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">غیرفعال</span>
                            @endif
                            @if ($gwTest)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[9.5px] font-extrabold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                    <x-icon name="alert" class="size-2.5" /> درگاه تست
                                </span>
                            @endif
                            @if ($gwCapable)
                                <span class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-amber-50 px-2 py-0.5 text-[9.5px] font-extrabold text-amber-700 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                                    <x-icon name="git-fork" class="size-2.5" /> تسهیم دارد
                                </span>
                            @endif
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-[9.5px] font-bold text-slate-400">
                            <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono dark:bg-slate-800">{{ $name }}</code>
                            @if ($gwTest)
                                <span>بدون نیاز به حساب بانکی</span>
                            @elseif (count($gwCreds) > 0)
                                <span>{{ \App\Support\Fmt::fa(count($gwCreds)) }} فیلد اعتبارنامه</span>
                                @if (! \App\Support\Payments\PaymentService::hasCredentials($name))
                                    <span class="text-amber-500">مدارک ثبت نشده</span>
                                @endif
                            @endif
                            @if ($gwOn && ! $gwLockedLocal && ! $gwTest && ! \App\Support\Payments\PaymentService::hasCredentials($name))
                                <span class="text-amber-600 dark:text-amber-400">تا ثبت مدارک در checkout نمایش داده نمی‌شود</span>
                            @endif
                        </div>
                    </div>

                    {{-- actions: trash + expand + toggle --}}
                    <div class="flex shrink-0 items-center gap-2">
                        @if (! $gwTest)
                            <button type="button" wire:click="deleteCredentials('{{ $name }}')"
                                    wire:confirm="اعتبارنامه‌های ذخیره‌شدهٔ «{{ $label }}» از تنظیمات پاک شود؟ (مقادیر فایل env حفظ می‌شوند)"
                                    class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40"
                                    title="پاک کردن مدارک ذخیره‌شده">
                                <x-icon name="trash" class="size-4" />
                            </button>
                        @endif
                        <button type="button" x-on:click="open = ! open"
                                class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800"
                                :aria-expanded="open ? 'true' : 'false'" aria-label="جزئیات {{ $label }}">
                            <x-icon name="chevron-down" class="size-4 transition-transform duration-300" x-bind:class="open ? 'rotate-180' : ''" />
                        </button>
                        @if ($gwLockedLocal)
                            <span class="w-[46px] rounded-lg bg-slate-100 px-2 py-1.5 text-center text-[9px] font-bold leading-4 text-slate-400 dark:bg-slate-800" title="درگاه آزمایشی روی سرور عملیاتی غیرفعال است">قفل</span>
                        @else
                            <label class="relative inline-flex shrink-0 cursor-pointer items-center" title="{{ $gwOn ? 'غیرفعال کردن' : 'فعال کردن' }} {{ $label }}">
                                <input type="checkbox" wire:model.live="enabled.{{ $name }}" class="peer sr-only">
                                <span class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500 dark:bg-slate-700"></span>
                                <span class="absolute start-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:start-[1.375rem]"></span>
                                <span class="sr-only">فعال بودن {{ $label }}</span>
                            </label>
                        @endif
                    </div>
                </div>

                {{-- ── expanded panel ── --}}
                <div x-show="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-cloak
                     class="border-t border-slate-100 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-950/30">

                    {{-- ── آیکون درگاه (v0.29.0): پیش‌فرض = آیکون رسمی، قابل تغییر با بارگذاری تصویر ── --}}
                    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
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
                            <p class="mt-1 text-[10px] leading-4 text-slate-500 dark:text-slate-400">
                                پیش‌فرض، آیکون رسمی «{{ $label }}» است — می‌توانید تصویر دلخواه بارگذاری کنید تا هم اینجا و هم در صفحهٔ پرداخت (checkout) نمایش داده شود (PNG، JPG یا WebP — حداکثر ۲ مگابایت).
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl bg-primary-600 px-3 py-2 text-[11px] font-bold text-white shadow-sm transition hover:bg-primary-700"
                                   title="بارگذاری تصویر آیکون">
                                <x-icon name="image-plus" class="size-3.5" />
                                {{ \App\Support\Payments\GatewayIcons::hasCustom($name) ? 'تغییر آیکون' : 'بارگذاری آیکون' }}
                                <input type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" class="sr-only"
                                       wire:model="iconFiles.{{ $name }}">
                                <span class="sr-only">انتخاب تصویر آیکون برای {{ $label }}</span>
                            </label>
                            @if (\App\Support\Payments\GatewayIcons::hasCustom($name))
                                <button type="button" wire:click="removeIcon('{{ $name }}')"
                                        wire:confirm="آیکون سفارشی «{{ $label }}» حذف شود و آیکون رسمی درگاه بازگردد؟"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold text-slate-500 transition hover:border-rose-300 hover:text-rose-600 dark:border-slate-700 dark:bg-slate-900"
                                        title="حذف تصویر سفارشی">
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

                    @if ($gwTest)
                        <div class="rounded-2xl border border-dashed border-amber-300 bg-amber-50/60 p-4 text-xs font-semibold leading-6 text-amber-700 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-300">
                            درگاه آزمایشی: صفحهٔ پرداخت ساختگی با دکمه‌های «پرداخت موفق» و «انصراف» — هیچ پول واقعی جابه‌جا نمی‌شود و فقط در محیط توسعه کار می‌کند. برای راه‌اندازی واقعی، یکی از درگاه‌های بانکی را روشن کنید و مدارکش را تکمیل نمایید.
                        </div>
                    @endif

                    @if (count($gwCreds) > 0)
                        <div class="{{ $gwTest ? 'mt-3' : '' }}">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-xs font-extrabold">اعتبارنامه‌های «{{ $label }}»</h3>
                                @if (! $gwIsDefault)
                                    <button type="button" wire:click="$set('gateway', '{{ $name }}')"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-primary-200 bg-white px-2.5 py-1.5 text-[10.5px] font-bold text-primary-600 transition hover:bg-primary-50 dark:border-primary-900 dark:bg-slate-900 dark:hover:bg-primary-950/40">
                                        <x-icon name="star" class="size-3" />
                                        تنظیم به‌عنوان درگاه پیش‌فرض
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-primary-50 px-2.5 py-1.5 text-[10.5px] font-bold text-primary-600 dark:bg-primary-950/40 dark:text-primary-300">
                                        <x-icon name="star" class="size-3" /> این درگاه پیش‌فرض است (پیش‌گزین صفحهٔ پرداخت)
                                    </span>
                                @endif
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($gwCreds as $fieldKey => $fieldLabel)
                                    <label class="block {{ $fieldKey === 'pubKey' ? 'sm:col-span-2' : '' }}">
                                        <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                            {{ $fieldLabel }}
                                            <span class="text-[9px] font-normal text-slate-400">(خالی = استفاده از env)</span>
                                        </span>
                                        @if ($fieldKey === 'pubKey')
                                            {{-- کلید عمومی RSA چندخطی است — input یک‌خطی newlineها را از بین می‌برد --}}
                                            <textarea rows="4" wire:model="fields.{{ $name }}.{{ $fieldKey }}"
                                                      dir="ltr" autocomplete="off" spellcheck="false"
                                                      class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 font-mono text-[11px] leading-5 shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"></textarea>
                                        @else
                                            <input type="{{ str_contains($fieldKey, 'password') ? 'password' : 'text' }}"
                                                   wire:model="fields.{{ $name }}.{{ $fieldKey }}"
                                                   dir="ltr" autocomplete="off"
                                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900">
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- پرداخت تسهیمی (split-capable gateways only) --}}
                    @if ($gwCapable)
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-900 dark:bg-amber-950/20">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                                        <x-icon name="git-fork" class="size-5" />
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-extrabold">پرداخت تسهیمی (واریز مستقیم به فروشنده)</h3>
                                        <p class="mt-1 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                                            {{ $splitCapable[$name] }}
                                        </p>
                                    </div>
                                </div>
                                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                    <input type="checkbox" wire:model.live="split.{{ $name }}" class="peer sr-only">
                                    <span class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-amber-500 dark:bg-slate-700"></span>
                                    <span class="absolute start-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:start-[1.375rem]"></span>
                                    <span class="sr-only">پرداخت تسهیمی {{ $label }}</span>
                                </label>
                            </div>

                            <div class="mt-4 grid gap-2 text-[11px] font-semibold leading-5 sm:grid-cols-2">
                                <div class="flex items-start gap-2 rounded-xl border border-dashed border-amber-300 bg-white/70 px-3 py-2.5 text-amber-700 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-300">
                                    <x-icon name="circle-check" class="mt-0.5 size-3.5 shrink-0" />
                                    <span><b>روشن:</b> سهم هر فروشگاه (کالا منهای کمیسیون) هنگام پرداخت، مستقیماً از درگاه به مقصد بانکی او می‌رود؛ فقط کمیسیون و هزینهٔ ارسال نزد پلتفرم می‌ماند.</span>
                                </div>
                                <div class="flex items-start gap-2 rounded-xl border border-dashed border-slate-300 bg-white/70 px-3 py-2.5 text-slate-600 dark:border-slate-700 dark:bg-slate-950/30 dark:text-slate-300">
                                    <x-icon name="wallet" class="mt-0.5 size-3.5 shrink-0" />
                                    <span><b>خاموش:</b> کل مبلغ به حساب درگاهِ پلتفرم می‌رود و سهم فروشنده پس از تأیید پرداخت به کیف پول او واریز می‌شود (روش پیش‌فرض).</span>
                                </div>
                            </div>

                            <p class="mt-3 flex items-start gap-2 text-[10px] leading-5 text-slate-500 dark:text-slate-400">
                                <x-icon name="info" class="mt-0.5 size-3.5 shrink-0" />
                                <span>
                                    شرط تسهیم: فروشنده باید قسمت «کارت‌های بانکی» پنل خود را تکمیل و حداقل یک کارت ثبت کرده باشد؛ مقصد تسهیم از کارت پیش‌فرض (شبا) یا موبایل فروشنده خوانده می‌شود. اگر هر فروشگاهِ سفارش مقصد معتبر نداشته باشد، سهمِ کل سفارش برای همهٔ فروشگاه‌ها به‌صورت خودکار مسیر کیف پول می‌رود و اگر درگاه پرداخت تسهیم را رد کند، سفارش با روش معمولی ادامه می‌یابد.
                                </span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── v0.31.0 — the installment (BNPL) trio got its own DEDICATED
         section (تنظیمات پرداخت اقساطی) with the full settlement policy
         (دورهٔ هر ۱/۲/۳ ماه + روزِ ماه + خودکار/دستی), the amount window,
         credentials and icons — this page only keeps the pointer card. --}}
    <a href="{{ route('admin.bnpl.settings') }}" wire:navigate
       class="group mt-2 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-violet-200 bg-gradient-to-l from-violet-50/80 via-white to-white px-4 py-3.5 shadow-sm transition hover:border-violet-300 hover:shadow dark:border-violet-900 dark:from-violet-950/30 dark:via-slate-900 dark:to-slate-900 dark:hover:border-violet-700">
        <div class="flex items-center gap-3">
            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-violet-600/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                <x-icon name="calendar-clock" class="size-5.5" />
            </span>
            <div>
                <h2 class="flex flex-wrap items-center gap-2 text-sm font-extrabold">
                    درگاه‌های پرداخت اقساطی (خرید حالا، پرداخت بعداً)
                    <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2 py-0.5 text-[9px] font-extrabold text-violet-700 dark:bg-violet-950/60 dark:text-violet-300">بخش مستقل</span>
                </h2>
                <p class="mt-1 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                    فعال‌سازی دیجی‌پی / اسنپ‌پی / ترب‌پی، تعداد اقساط، دورهٔ تسویه (هر ماه / هر ۲ ماه / هر ۳ ماه)، روزِ واریز، حالت خودکار/دستی و پنجرهٔ مبلغ خرید اقساطی — همه در بخش «تنظیمات پرداخت اقساطی».
                </p>
            </div>
        </div>
        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-violet-300 bg-white px-3 py-2 text-[11px] font-bold text-violet-600 shadow-sm transition group-hover:bg-violet-50 dark:border-violet-800 dark:bg-slate-900 dark:text-violet-300 dark:group-hover:bg-violet-950/40">
            <x-icon name="settings" class="size-3.5" />
            تنظیمات پرداخت اقساطی
        </span>
    </a>

    {{-- ── bottom bar: info + save ── --}}
    <div class="sticky bottom-3 z-10 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 shadow-lg shadow-slate-900/5 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
        <p class="flex items-start gap-2 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
            <x-icon name="info" class="mt-0.5 size-3.5 shrink-0 text-primary-500" />
            <span>
                برای فعال کردن هر درگاه کلیدش را روشن کنید و مدارکش را تکمیل نمایید؛ درگاه ستاره‌دار «پیش‌فرض» است و در صفحهٔ پرداخت پیش‌گزین می‌شود. با فعال بودن چند درگاه، مشتری خودش درگاه را انتخاب می‌کند — درگاه‌های اقساطی هم باید از پنل فروشنده فعال شده باشند تا در صفحهٔ پرداخت ظاهر شوند.
            </span>
        </p>
        <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700 disabled:opacity-60">
            <span wire:loading wire:target="save"><x-icon name="rotate" class="size-4 animate-spin" /></span>
            ذخیره تنظیمات
        </button>
    </div>
</div>
