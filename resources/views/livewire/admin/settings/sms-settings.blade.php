<div class="ip-fade-up space-y-5">

    {{-- ── flash ── --}}
    @if (session('admin-sms-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-sms-flash') }}
        </div>
    @endif
    @if (session('admin-sms-error'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" x-data x-init="setTimeout(() => $el.remove(), 6000)">
            <x-icon name="alert" class="size-4.5 shrink-0" />
            {{ session('admin-sms-error') }}
        </div>
    @endif
    @error('driver') <p class="text-xs font-bold text-rose-500">{{ $message }}</p> @enderror

    <div class="grid gap-5 xl:grid-cols-3">

        {{-- ── provider selection ── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-3">
                <h2 class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-9 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="message" class="size-4.5" />
                    </span>
                    پروایدر پیامک
                </h2>
                <a href="{{ route('admin.settings.sms.templates') }}" wire:navigate
                   class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-primary-600 px-3.5 py-2 text-[11px] font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                    <x-icon name="megaphone" class="size-3.5" />
                    قالب‌های پیامک
                </a>
            </div>
            <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                پروایدر فعال برای همه پیامک‌های سیستم (کد OTP و ...) انتخاب کنید. اعتبارنامه هر پروایدر جداگانه ذخیره می‌شود و با تغییر پروایدر حفظ می‌ماند.
            </p>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($drivers as $d)
                    <button type="button" wire:click="$set('driver', '{{ $d['name'] }}')"
                            class="relative flex flex-col items-start gap-2 rounded-2xl border p-4 text-right transition {{ $driver === $d['name']
                                ? 'border-primary-500 bg-primary-50/60 ring-2 ring-primary-500/30 dark:bg-primary-950/40'
                                : 'border-slate-200 hover:border-primary-300 dark:border-slate-800 dark:hover:border-primary-800' }}">
                        <span class="flex items-center gap-2 text-xs font-extrabold {{ $driver === $d['name'] ? 'text-primary-700 dark:text-primary-300' : '' }}">
                            <x-icon name="message" class="size-4 {{ $driver === $d['name'] ? 'text-primary-600' : 'text-slate-400' }}" />
                            {{ $d['label'] }}
                        </span>
                        <span class="text-[10px] text-slate-400">
                            @if (count($d['fields']) === 0)
                                بدون نیاز به تنظیمات (تست)
                            @else
                                {{ \App\Support\Fmt::fa(count($d['fields'])) }} فیلد اعتبارنامه
                            @endif
                        </span>
                        @if ($driver === $d['name'])
                            <span class="absolute -left-1.5 -top-1.5 grid size-5 place-items-center rounded-full bg-primary-600 text-white shadow">
                                <x-icon name="check" class="size-3" />
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- ── credentials of selected driver ── --}}
            @php $active = $drivers->firstWhere('name', $driver); @endphp
            @if ($active && count($active['fields']) > 0)
                <div class="mt-6 rounded-2xl border border-dashed border-slate-300 p-4 dark:border-slate-700">
                    <h3 class="text-xs font-extrabold">اعتبارنامه «{{ $active['label'] }}»</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ($active['fields'] as $fieldKey => $meta)
                            @php
                                [$fieldLabel, $required] = $meta;
                                $isSecret = (bool) preg_match('/password|token|key/i', $fieldKey);
                            @endphp
                            <label class="block">
                                <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                    {{ $fieldLabel }}
                                    @if ($required) <span class="text-rose-500">*</span> @endif
                                </span>
                                <input type="{{ $isSecret ? 'password' : 'text' }}"
                                       wire:model="fields.{{ $driver }}.{{ $fieldKey }}"
                                       dir="ltr" autocomplete="off"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                                       placeholder="{{ $isSecret ? '••••••••' : '' }}">
                                @error("fields.{$driver}.{$fieldKey}") <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                            </label>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-6 rounded-2xl border border-dashed border-emerald-300 bg-emerald-50/50 p-4 text-xs font-semibold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
                    این پروایدر فقط پیام‌ها را در لاگ سیستم می‌نویسد (کد OTP روی صفحه هم نمایش داده می‌شود) — برای توسعه و تست، بدون هزینه.
                </div>
            @endif

            <div class="mt-5 flex items-center gap-3">
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-2xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-60">
                    <span wire:loading wire:target="save"><x-icon name="rotate" class="size-4 animate-spin" /></span>
                    ذخیره تنظیمات
                </button>
                <span class="text-[11px] text-slate-400">پروایدر فعال فعلی: <b class="text-slate-600 dark:text-slate-300">{{ $drivers->firstWhere('name', $driver)['label'] ?? '—' }}</b></span>
            </div>
        </div>

        {{-- ── test send ── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="flex items-center gap-2 text-sm font-extrabold">
                <span class="grid size-9 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                    <x-icon name="phone" class="size-4.5" />
                </span>
                ارسال پیام تست
            </h2>
            <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">پیش از رفتن به حالت واقعی، پروایدر فعال را با یک پیام آزمایشی چک کنید.</p>

            <div class="mt-4 space-y-3">
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">شماره موبایل</span>
                    <input type="text" wire:model="testPhone" dir="ltr" placeholder="09123456789"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    @error('testPhone') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">متن پیام</span>
                    <textarea wire:model="testText" rows="3" placeholder="پیام آزمایشی InstaShop"
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs leading-6 shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"></textarea>
                    @error('testText') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>
                <button type="button" wire:click="sendTest" wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-primary-600 bg-primary-50 px-5 py-2.5 text-sm font-bold text-primary-700 transition hover:bg-primary-100 disabled:opacity-60 dark:bg-primary-950/40 dark:text-primary-300">
                    <span wire:loading wire:target="sendTest"><x-icon name="rotate" class="size-4 animate-spin" /></span>
                    ارسال تست
                </button>
            </div>
        </div>
    </div>

    {{-- ── sms logs ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="file-text" class="size-4 text-primary-500" />
                آخرین پیامک‌های سیستم
            </h3>
            <span class="text-[11px] text-slate-400">{{ \App\Support\Fmt::fa($logs->count()) }} ردیف اخیر</span>
        </div>

        @if ($logs->isEmpty())
            <p class="px-5 py-10 text-center text-xs text-slate-400">هنوز پیامکی ارسال نشده است.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                            <th class="px-5 py-3 font-semibold">گیرنده</th>
                            <th class="px-3 py-3 font-semibold">پروایدر</th>
                            <th class="px-3 py-3 font-semibold">قالب</th>
                            <th class="px-3 py-3 font-semibold">متن</th>
                            <th class="px-3 py-3 font-semibold">زمان</th>
                            <th class="px-5 py-3 font-semibold">وضعیت</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach ($logs as $log)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="sl-{{ $log->id }}">
                                <td class="px-5 py-3 text-xs font-bold" dir="ltr">{{ \App\Support\Fmt::toFa(\App\Support\Phone::pretty($log->phone)) }}</td>
                                <td class="px-3 py-3 text-[11px] text-slate-500">{{ $log->driver }}</td>
                                <td class="px-3 py-3 text-[11px] text-slate-500">{{ $log->template ?? '—' }}</td>
                                <td class="max-w-64 px-3 py-3 text-[11px] text-slate-500"><span class="line-clamp-1" dir="auto">{{ $log->message }}</span></td>
                                <td class="px-3 py-3 text-[10px] text-slate-400">{{ \App\Support\JDate::dateTime($log->created_at) }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $log->status === 'sent'
                                        ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                                        : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                                        {{ $log->status === 'sent' ? 'ارسال شد' : 'ناموفق' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
