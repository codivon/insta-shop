<div class="ip-fade-up space-y-5">

    {{-- ── flash ── --}}
    @if (session('admin-developer-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-developer-flash') }}
        </div>
    @endif
    @if (session('admin-developer-error'))
        <div class="flex items-start gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" x-data x-init="setTimeout(() => $el.remove(), 8000)">
            <x-icon name="alert" class="size-4.5 shrink-0" />
            <span class="break-all">{{ session('admin-developer-error') }}</span>
        </div>
    @endif

    <div x-data="{ tab: 'cron' }" class="space-y-5">

        {{-- ── tabs ── --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="scroll-slim flex gap-1 overflow-x-auto border-b border-slate-100 px-2 pt-2 dark:border-slate-800" role="tablist" aria-label="تنظیمات برنامه‌نویس">
                <button type="button" role="tab" :aria-selected="tab === 'cron'" x-on:click="tab = 'cron'"
                        class="ap-dev-tab relative whitespace-nowrap" :class="tab === 'cron' ? 'is-active' : ''">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="clock" class="size-4" />
                        کرون جاب
                    </span>
                </button>
                <button type="button" role="tab" :aria-selected="tab === 'maintenance'" x-on:click="tab = 'maintenance'"
                        class="ap-dev-tab relative whitespace-nowrap" :class="tab === 'maintenance' ? 'is-active' : ''">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="alert" class="size-4" />
                        حالت بروزرسانی سایت
                    </span>
                </button>
                <button type="button" role="tab" :aria-selected="tab === 'updater'" x-on:click="tab = 'updater'"
                        class="ap-dev-tab relative whitespace-nowrap" :class="tab === 'updater' ? 'is-active' : ''">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="rotate" class="size-4" />
                        بروزرسانی
                    </span>
                </button>
                @if ($hasPendingMig)
                    <button type="button" role="tab" :aria-selected="tab === 'migrations'" x-on:click="tab = 'migrations'"
                            class="ap-dev-tab relative whitespace-nowrap" :class="tab === 'migrations' ? 'is-active' : ''">
                        <span class="flex items-center gap-1.5">
                            <x-icon name="database" class="size-4" />
                            مایگریشن‌های جدید
                            <span class="rounded-md bg-amber-100 px-1.5 py-0.5 text-[10px] font-black text-amber-700 dark:bg-amber-950 dark:text-amber-400">{{ \App\Support\Fmt::fa(count($pendingMig)) }}</span>
                        </span>
                    </button>
                @endif
            </div>

            {{-- ═══════════════════ TAB 1: CRON JOB ═══════════════════ --}}
            <div x-show="tab === 'cron'" class="animate-fade-in space-y-4 p-5 sm:p-6" role="tabpanel">

                @if ($cronActive)
                    <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                        <x-icon name="circle-check" class="size-4 shrink-0" />
                        <div>
                            <p class="text-[13px] font-bold">کرون جاب فعال است</p>
                            <p class="text-xs">زمان‌بندی خودکار لاراول روی سرور در حال اجراست. آخرین اجرا: <span class="font-mono" dir="ltr">{{ $cronLastRun ?? '-' }}</span></p>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-rose-800 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300">
                        <x-icon name="alert" class="size-4 shrink-0" />
                        <div>
                            <p class="text-[13px] font-bold">کرون جاب غیرفعال است</p>
                            <p class="text-xs">زمان‌بند لاراول هنوز اجرا نشده است. دستورهای زیر را در Cron Job هاست خود وارد کنید؛ پس از اولین اجرا، این پیام به «فعال» تغییر می‌کند.</p>
                        </div>
                    </div>
                @endif

                <div class="flex items-start gap-2 rounded-xl border border-sky-200 bg-sky-50 p-3.5 text-sky-800 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                    <x-icon name="info" class="mt-0.5 size-4 shrink-0" />
                    <div class="text-xs leading-6">
                        <p class="font-bold">اجرای صف از طریق Cron Job، مخصوص هاست اشتراکی</p>
                        <p>اگر هاست شما اجازه اجرای دائمی Queue Worker را نمی‌دهد، این حالت را فعال کنید. Queue به‌جای اجرای مداوم، توسط دو Cron Job مدیریت می‌شود.</p>
                    </div>
                </div>

                <form wire:submit="saveCron" class="space-y-3">
                    <div class="flex items-center justify-between gap-2.5 rounded-xl border border-slate-200 bg-slate-50 p-3.5 dark:border-slate-800 dark:bg-slate-800/60">
                        <span class="text-[13px] font-bold">فعال‌سازی اجرای صف با دو Cron Job (هاست اشتراکی)</span>
                        <label class="inline-flex shrink-0 cursor-pointer items-center">
                            <input type="checkbox" class="peer sr-only" wire:model="sharedHostingCronEnabled" aria-label="فعال‌سازی اجرای صف با دو Cron Job">
                            <span class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-300 transition-colors duration-300 after:absolute after:start-0.5 after:top-1/2 after:size-5 after:-translate-y-1/2 after:rounded-full after:bg-white after:shadow-md after:transition-all after:duration-300 peer-checked:bg-primary-600 peer-checked:after:start-[calc(100%-1.375rem)] dark:bg-slate-700"></span>
                        </label>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                        <p class="text-xs font-bold">دستورهایی که باید در پنل Cron Job هاست خود وارد کنید:</p>
                        <p class="mt-1 text-[10px] text-slate-400">⚠️ مقادیر مسیر به‌صورت خودکار از سرور فعلی شناسایی شده‌اند؛ اگر هاست شما متفاوت است، آن‌ها را با مسیرهای صحیح جایگزین کنید.</p>
                        <div class="mt-3 space-y-3">
                            <div>
                                <small class="text-[11px] text-slate-500 dark:text-slate-400">① زمان‌بندی لاراول، هر دقیقه</small>
                                <div class="relative">
                                    <code class="mt-1 block select-all overflow-x-auto rounded-lg bg-slate-100 px-3 py-2 pe-10 font-mono text-[11px] dark:bg-slate-800" dir="ltr">{{ $cronCommands[0] }}</code>
                                    <button type="button" x-data="{ copied: false }" x-on:click="navigator.clipboard.writeText(@js($cronCommands[0])); copied = true; setTimeout(() => copied = false, 1500)"
                                            class="absolute end-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600 dark:hover:bg-slate-700"
                                            :title="copied ? 'کپی شد' : 'کپی دستور'" aria-label="کپی دستور زمان‌بندی">
                                        <span x-show="!copied"><x-icon name="copy" class="size-3.5" /></span>
                                        <span x-show="copied" x-cloak><x-icon name="check" class="size-3.5 text-emerald-500" /></span>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <small class="text-[11px] text-slate-500 dark:text-slate-400">② اجرای صف، یک‌بار خالی‌سازی و توقف</small>
                                <div class="relative">
                                    <code class="mt-1 block select-all overflow-x-auto rounded-lg bg-slate-100 px-3 py-2 pe-10 font-mono text-[11px] dark:bg-slate-800" dir="ltr">{{ $cronCommands[1] }}</code>
                                    <button type="button" x-data="{ copied: false }" x-on:click="navigator.clipboard.writeText(@js($cronCommands[1])); copied = true; setTimeout(() => copied = false, 1500)"
                                            class="absolute end-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600 dark:hover:bg-slate-700"
                                            :title="copied ? 'کپی شد' : 'کپی دستور'" aria-label="کپی دستور صف">
                                        <span x-show="!copied"><x-icon name="copy" class="size-3.5" /></span>
                                        <span x-show="copied" x-cloak><x-icon name="check" class="size-3.5 text-emerald-500" /></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                        <x-icon name="check" class="size-4" />
                        ذخیره تنظیمات صف
                    </button>
                </form>
            </div>

            {{-- ═══════════════════ TAB 2: MAINTENANCE ═══════════════════ --}}
            <div x-show="tab === 'maintenance'" class="animate-fade-in space-y-4 p-5 sm:p-6" role="tabpanel">

                <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sky-800 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                    <p class="text-[13px] font-bold">فعال‌سازی حالت بروزرسانی (سایت برای بازدیدکنندگان بسته می‌شود)</p>
                    <p class="mt-1 text-xs leading-6">
                        پس از فعال کردن حالت بروزرسانی، پنل مدیریت و لینک زیر برای شما باز می‌ماند و بقیه‌ی سایت با پیام بروزرسانی نمایش داده می‌شود:
                        <span class="font-mono" dir="ltr">{{ url('/').'?secret='.$maintenanceSecret }}</span>
                    </p>
                </div>

                @if ($maintenanceEnabled)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
                        <span class="flex items-center gap-2 text-[13px] font-bold">
                            <span class="size-2 animate-pulse rounded-full bg-amber-500"></span>
                            حالت بروزرسانی در حال حاضر فعال است
                        </span>
                        <button type="button" wire:click="disableMaintenance" wire:loading.attr="disabled"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-emerald-700 disabled:opacity-50">
                            غیرفعال کردن
                            <span wire:loading wire:target="disableMaintenance" class="inline-block size-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        </button>
                    </div>
                @endif

                <form wire:submit="enableMaintenance" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold">کد مخفی ورود (Secret) <span class="text-rose-500">*</span></label>
                            <div class="flex gap-2">
                                <input type="text" dir="ltr" wire:model="maintenanceSecret" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 font-mono text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                                <button type="button" wire:click="regenerateSecret" title="ساخت کد جدید"
                                        class="grid size-10 shrink-0 place-items-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                    <x-icon name="rotate" class="size-4" />
                                </button>
                            </div>
                            @error('maintenanceSecret') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold">عنوان</label>
                            <input type="text" wire:model="maintenanceTitle" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            @error('maintenanceTitle') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold">توضیحات</label>
                            <textarea rows="4" wire:model="maintenanceDescription" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"></textarea>
                        </div>
                    </div>

                    @if (! $maintenanceEnabled)
                        <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-rose-700 disabled:opacity-50">
                            <x-icon name="alert" class="size-4" />
                            فعال کردن حالت بروزرسانی
                            <span wire:loading wire:target="enableMaintenance" class="inline-block size-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        </button>
                    @endif
                </form>
            </div>

            {{-- ═══════════════════ TAB 3: UPDATER ═══════════════════ --}}
            <div x-show="tab === 'updater'" class="animate-fade-in space-y-4 p-5 sm:p-6" role="tabpanel">

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                    <div>
                        <p class="text-[13px] font-bold">نسخه نصب‌شده</p>
                        <p class="mt-0.5 font-mono text-xs text-slate-500 dark:text-slate-400" dir="ltr">v{{ $this->currentVersion() }}</p>
                    </div>
                    <button type="button" wire:click="checkUpdate" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-xl border border-primary-300 px-4 py-2 text-xs font-bold text-primary-700 transition hover:bg-primary-50 disabled:opacity-50 dark:border-primary-800 dark:text-primary-300 dark:hover:bg-primary-950/40">
                        <span wire:loading wire:target="checkUpdate" class="inline-block size-3.5 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"></span>
                        <span wire:loading.remove wire:target="checkUpdate"><x-icon name="rotate" class="size-3.5" /></span>
                        بررسی نسخه جدید
                    </button>
                </div>

                @if (is_array($updateInfo) && isset($updateInfo['error']))
                    <div class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs text-rose-800 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300">
                        <x-icon name="alert" class="size-4 shrink-0" />
                        {{ $updateInfo['error'] }}
                    </div>
                @elseif (is_array($updateInfo) && isset($updateInfo['tag']))
                    @if ($updateInfo['newer'])
                        <div class="space-y-2 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
                            <p class="flex items-center gap-2 text-[13px] font-bold">
                                <x-icon name="info" class="size-4" />
                                نسخه جدید «{{ $updateInfo['name'] ?: $updateInfo['tag'] }}» موجود است!
                            </p>
                            <p class="text-xs">نسخه فعلی: <span class="font-mono" dir="ltr">v{{ $this->currentVersion() }}</span> — نسخه جدید: <span class="font-mono" dir="ltr">{{ $updateInfo['tag'] }}</span></p>
                            @if ($updateInfo['notes'])
                                <div class="max-h-52 overflow-y-auto whitespace-pre-line rounded-lg bg-white/60 p-3 text-[11px] leading-6 dark:bg-slate-900/40">{{ $updateInfo['notes'] }}</div>
                            @endif
                            <a href="{{ $updateInfo['url'] }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-amber-700">
                                <x-icon name="external-link" class="size-3.5" />
                                مشاهده و دریافت نسخه جدید
                            </a>
                        </div>
                    @else
                        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                            <x-icon name="circle-check" class="size-4 shrink-0" />
                            <div>
                                <p class="text-[13px] font-bold">به‌روز هستید</p>
                                <p class="text-xs">آخرین نسخه منتشرشده: <span class="font-mono" dir="ltr">{{ $updateInfo['tag'] }}</span></p>
                            </div>
                        </div>
                    @endif
                @endif

                <p class="text-[10px] leading-6 text-slate-400">
                    نتیجه‌ی بررسی تا ۶۰ دقیقه کش می‌شود؛ با دکمه‌ی «بررسی نسخه جدید» هر زمان می‌توانید نتیجه‌ی تازه بگیرید.
                </p>
            </div>

            {{-- ═══════════════════ TAB 4: MIGRATIONS (auto-hidden) ═══════════════════ --}}
            @if ($hasPendingMig)
                <div x-show="tab === 'migrations'" class="animate-fade-in space-y-4 p-5 sm:p-6" role="tabpanel">

                    <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
                        <x-icon name="alert" class="mt-0.5 size-4 shrink-0" />
                        <div class="text-xs leading-6">
                            <p class="font-bold">{{ \App\Support\Fmt::fa(count($pendingMig)) }} مایگریشن جدید هنوز وارد دیتابیس نشده است</p>
                            <p>جدول‌ها و فیلدهای زیر پس از اجرا ساخته می‌شوند. تا زمانی که همه‌ی مایگریشن‌ها اجرا شوند، این تب نمایش داده می‌شود.</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach ($pendingMig as $mig)
                            <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800" x-data="{ open: false }">
                                <button type="button" x-on:click="open = !open" class="flex w-full items-center justify-between gap-3 bg-slate-50 px-4 py-3 text-right transition hover:bg-slate-100 dark:bg-slate-800/60 dark:hover:bg-slate-800">
                                    <span class="flex items-center gap-2 text-xs font-bold">
                                        <x-icon name="file-text" class="size-4 text-slate-400" />
                                        {{ $mig['name'] }}
                                    </span>
                                    <span class="flex items-center gap-2 text-[10px] text-slate-400">
                                        @if (count($mig['tables'])) <span class="rounded-md bg-primary-100 px-1.5 py-0.5 font-bold text-primary-700 dark:bg-primary-950 dark:text-primary-300">{{ \App\Support\Fmt::fa(count($mig['tables'])) }} جدول جدید</span> @endif
                                        @if (count($mig['alters'])) <span class="rounded-md bg-sky-100 px-1.5 py-0.5 font-bold text-sky-700 dark:bg-sky-950 dark:text-sky-300">{{ \App\Support\Fmt::fa(count($mig['alters'])) }} تغییر جدول</span> @endif
                                        <x-icon name="chevron-down" class="size-3.5 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                                    </span>
                                </button>
                                <div x-show="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-cloak class="space-y-3 border-t border-slate-200 px-4 py-3 text-xs dark:border-slate-800">
                                    @foreach ($mig['tables'] as [$table, $columns])
                                        <div>
                                            <p class="flex items-center gap-1.5 font-bold text-primary-700 dark:text-primary-300">
                                                <x-icon name="database" class="size-3.5" />
                                                جدول جدید: <span class="font-mono" dir="ltr">{{ $table }}</span>
                                                <span class="text-[10px] font-normal text-slate-400">({{ \App\Support\Fmt::fa(count($columns)) }} فیلد)</span>
                                            </p>
                                            <p class="mt-1 flex flex-wrap gap-1.5">
                                                @foreach ($columns as $col)
                                                    <code class="rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] dark:bg-slate-800" dir="ltr">{{ $col }}</code>
                                                @endforeach
                                            </p>
                                        </div>
                                    @endforeach
                                    @foreach ($mig['alters'] as [$table, $columns])
                                        <div>
                                            <p class="flex items-center gap-1.5 font-bold text-sky-700 dark:text-sky-300">
                                                <x-icon name="layers" class="size-3.5" />
                                                افزودن فیلد به جدول: <span class="font-mono" dir="ltr">{{ $table }}</span>
                                            </p>
                                            <p class="mt-1 flex flex-wrap gap-1.5">
                                                @foreach ($columns as $col)
                                                    <code class="rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] dark:bg-slate-800" dir="ltr">{{ $col }}</code>
                                                @endforeach
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="runMigrations" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                            <x-icon name="database" class="size-4" />
                            ساخت جداول (اجرای مایگریشن‌ها)
                            <span wire:loading wire:target="runMigrations" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        </button>
                        <p class="text-[10px] text-slate-400">پیش از اجرا از دیتابیس خود پشتیبان بگیرید.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .ap-dev-tab {
        padding: 0.65rem 0.9rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        border-bottom: 2px solid transparent;
        transition: color .2s, border-color .2s, background .2s;
    }
    .ap-dev-tab:hover { color: #334155; }
    .dark .ap-dev-tab { color: #94a3b8; }
    .dark .ap-dev-tab:hover { color: #e2e8f0; }
    .ap-dev-tab.is-active {
        color: var(--ip-primary, #D62976);
        border-color: var(--ip-primary, #D62976);
        background: color-mix(in srgb, var(--ip-primary, #D62976) 7%, transparent);
    }
    [x-cloak] { display: none !important; }
</style>
