<div class="space-y-5">

    {{-- ── section header ── --}}
    <div class="flex items-center gap-3">
        <span class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-md shadow-primary-600/25">
            <x-icon name="bell" class="size-5" />
        </span>
        <div>
            <h2 class="text-sm font-black">نوتیفیکیشن‌ها</h2>
            <p class="text-[11px] text-slate-400">هر اتفاق سایت برای مدیر اعلان بیاید — حتی وقتی مرورگر بسته است (پوش روی گوشی/سیستم).</p>
        </div>
    </div>

    {{-- ── push master switch ── --}}
    <section class="space-y-3 rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="flex items-center gap-1.5 text-[13px] font-bold">
                    <x-icon name="smartphone" class="size-4 text-primary-600 dark:text-primary-400" />
                    نوتیفیکیشن پوش (گوشی و سیستم)
                </p>
                <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                    وقتی روشن باشد، اعلان‌ها به‌صورت پوش واقعی روی دستگاه‌های فعال‌شده می‌آیند — حتی اگر مرورگر بسته باشد.
                    برای فعال‌سازی، از زنگ اعلان بالای پنل روی «فعال‌سازی نوتیف موبایل/سیستم» بزنید و اجازه مرورگر را بدهید.
                </p>
            </div>
            <label class="inline-flex shrink-0 cursor-pointer items-center">
                <input type="checkbox" class="peer sr-only" wire:model="pushEnabled">
                <span class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-300 transition-colors duration-300 after:absolute after:start-0.5 after:top-1/2 after:size-5 after:-translate-y-1/2 after:rounded-full after:bg-white after:shadow-md after:transition-all after:duration-300 peer-checked:bg-primary-600 peer-checked:after:start-[calc(100%-1.375rem)] dark:bg-slate-700"></span>
            </label>
        </div>
        <p class="flex items-start gap-1.5 text-[11px] font-semibold leading-5 text-slate-500 dark:text-slate-400">
            <x-icon name="info" class="me-1 mt-0.5 inline size-3.5 shrink-0" aria-hidden="true" />
            دستگاه‌های فعال‌شده: {{ \App\Support\Fmt::fa($this->devices->count()) }} — فعال‌سازی هر دستگاه از زنگ اعلان انجام می‌شود.
        </p>
    </section>

    {{-- ── per-event switches ── --}}
    <section class="space-y-3 rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <p class="text-[13px] font-bold">کدام اتفاق‌ها اعلان بسازند؟</p>
        <div class="grid gap-2.5 sm:grid-cols-2">
            @foreach (App\Support\AdminNotifier::EVENTS as $key => [$label, $icon])
                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-3 transition hover:border-primary-300 dark:border-slate-700 dark:bg-slate-800/40"
                       wire:key="evt-{{ $key }}">
                    <span class="flex items-center gap-2 text-[12px] font-bold text-slate-700 dark:text-slate-200">
                        <x-icon name="{{ $icon }}" class="size-4 text-slate-400" />
                        {{ $label }}
                    </span>
                    {{-- NOTE: dotted event keys can't be used inside wire:model —
                         Livewire would treat them as nested paths. The component
                         exposes underscore keys (order_new) and maps them back. --}}
                    <input type="checkbox" class="size-4.5 accent-primary-600" wire:model="events.{{ str_replace('.', '__', $key) }}">
                </label>
            @endforeach
        </div>
    </section>

    {{-- ── seller-panel event switches ── --}}
    <section class="space-y-3 rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <p class="text-[13px] font-bold">اعلان‌های پنل فروشنده</p>
        <p class="text-[11px] leading-5 text-slate-400">
            فروشنده فقط اعلان‌های مربوط به فروشگاه خودش را می‌بیند (سفارش‌های شامل محصولاتش، کیف پولش، محصولاتش). با این کلیدها مشخص کنید کدام اتفاق‌ها برای فروشنده اعلان بسازند — پوش گوشی/سیستم هم برای دستگاه‌های فعال‌شدهٔ فروشنده ارسال می‌شود.
        </p>
        <div class="grid gap-2.5 sm:grid-cols-2">
            @foreach (App\Support\SellerNotifier::EVENTS as $key => [$label, $icon])
                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-3 transition hover:border-primary-300 dark:border-slate-700 dark:bg-slate-800/40"
                       wire:key="sevt-{{ $key }}">
                    <span class="flex items-center gap-2 text-[12px] font-bold text-slate-700 dark:text-slate-200">
                        <x-icon name="{{ $icon }}" class="size-4 text-slate-400" />
                        {{ $label }}
                    </span>
                    <input type="checkbox" class="size-4.5 accent-primary-600" wire:model="sellerEvents.{{ str_replace('.', '__', $key) }}">
                </label>
            @endforeach
        </div>
    </section>

    {{-- ── actions ── --}}
    <section class="flex flex-wrap items-center gap-2.5 rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-[12px] font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-60">
            <span wire:loading wire:target="save" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
            ذخیره تنظیمات
        </button>
        <button type="button" wire:click="sendTest" wire:loading.attr="disabled" wire:target="sendTest"
                class="inline-flex items-center gap-2 rounded-xl border border-primary-200 bg-primary-50 px-4 py-2.5 text-[12px] font-extrabold text-primary-700 transition hover:bg-primary-100 disabled:opacity-60 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
            <x-icon name="sparkles" class="size-3.5" />
            ارسال نوتیفیکیشن تستی
        </button>
        @if ($testBody !== '')
            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">{{ $testBody }}</span>
        @endif
    </section>

    {{-- ── devices ── --}}
    <section class="space-y-3 rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <div class="flex items-center justify-between gap-3">
            <p class="text-[13px] font-bold">دستگاه‌های پوش</p>
            <button type="button" wire:click="regenerateKeys"
                    onclick="if(!confirm('کلیدهای VAPID بازتولید شوند؟ همهٔ دستگاه‌ها باید دوباره فعال‌سازی کنند.')) return false"
                    class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-[10.5px] font-bold text-amber-700 transition hover:bg-amber-100 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
                بازتولید کلیدها
            </button>
        </div>

        <div class="scroll-slim max-h-64 space-y-2 overflow-y-auto pe-1">
            @forelse ($this->devices as $device)
                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 dark:border-slate-700 dark:bg-slate-800/40"
                     wire:key="dev-{{ $device->id }}">
                    <div class="min-w-0">
                        <p class="truncate text-[11.5px] font-bold text-slate-700 dark:text-slate-200" dir="ltr">
                            {{ $device->device_label ?: $device->user_agent ?: 'دستگاه' }}
                        </p>
                        <p class="mt-0.5 text-[10px] text-slate-400">
                            ثبت: {{ \App\Support\JDate::date($device->created_at) }}
                            @if ($device->last_used_at) — آخرین ارسال: {{ \App\Support\JDate::ago($device->last_used_at) }} @endif
                        </p>
                    </div>
                    <button type="button" wire:click="removeDevice({{ $device->id }})"
                            class="grid size-8 shrink-0 place-items-center rounded-lg text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-950/40"
                            aria-label="حذف دستگاه">
                        <x-icon name="trash" class="size-3.5" />
                    </button>
                </div>
            @empty
                <p class="rounded-xl bg-slate-50 px-4 py-6 text-center text-[11.5px] font-semibold text-slate-400 dark:bg-slate-800/40">
                    هنوز دستگاهی فعال‌سازی نشده — از زنگ اعلان بالای پنل، «فعال‌سازی نوتیف موبایل/سیستم» را بزنید.
                </p>
            @endforelse
        </div>

        @if ($vapidPublic !== '')
            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/40">
                <p class="text-[10.5px] font-bold text-slate-500 dark:text-slate-400">کلید عمومی VAPID (برای عیب‌یابی):</p>
                <p class="mt-1 break-all text-[10px] text-slate-400" dir="ltr">{{ $vapidPublic }}</p>
            </div>
        @endif
    </section>
</div>
