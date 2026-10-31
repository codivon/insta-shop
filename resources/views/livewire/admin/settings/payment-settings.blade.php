<div class="ip-fade-up space-y-5">

    {{-- ── flash ── --}}
    @if (session('admin-payment-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-payment-flash') }}
        </div>
    @endif
    @error('gateway') <p class="text-xs font-bold text-rose-500">{{ $message }}</p> @enderror

    <div class="grid gap-5 xl:grid-cols-3">

        {{-- ── gateway selection ── --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="flex items-center gap-2 text-sm font-extrabold">
                <span class="grid size-9 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                    <x-icon name="credit-card" class="size-4.5" />
                </span>
                درگاه پرداخت
            </h2>
            <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                درگاه فعال برای همه پرداخت‌های اپ مشتری. اعتبارنامه‌ها از این جدول تنظیمات خوانده می‌شوند؛ اگر فیلدی خالی بماند، مقدار فایل env (در صورت وجود) استفاده می‌شود.
            </p>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($gateways as $name => $label)
                    @php $credCount = count($fields[$name] ?? []); @endphp
                    <button type="button" wire:click="$set('gateway', '{{ $name }}')"
                            class="relative flex flex-col items-start gap-2 rounded-2xl border p-4 text-right transition {{ $gateway === $name
                                ? 'border-primary-500 bg-primary-50/60 ring-2 ring-primary-500/30 dark:bg-primary-950/40'
                                : 'border-slate-200 hover:border-primary-300 dark:border-slate-800 dark:hover:border-primary-800' }}">
                        <span class="flex items-center gap-2 text-xs font-extrabold {{ $gateway === $name ? 'text-primary-700 dark:text-primary-300' : '' }}">
                            <x-icon name="credit-card" class="size-4 {{ $gateway === $name ? 'text-primary-600' : 'text-slate-400' }}" />
                            {{ $label }}
                        </span>
                        <span class="text-[10px] text-slate-400">
                            @if ($name === 'local')
                                بدون نیاز به حساب بانکی (تست)
                            @elseif ($credCount > 0)
                                {{ \App\Support\Fmt::fa($credCount) }} فیلد اعتبارنامه
                            @endif
                        </span>
                        @if ($gateway === $name)
                            <span class="absolute -left-1.5 -top-1.5 grid size-5 place-items-center rounded-full bg-primary-600 text-white shadow">
                                <x-icon name="check" class="size-3" />
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- ── credentials of selected gateway ── --}}
            @if (count($fields[$gateway] ?? []) > 0)
                <div class="mt-6 rounded-2xl border border-dashed border-slate-300 p-4 dark:border-slate-700">
                    <h3 class="text-xs font-extrabold">اعتبارنامه «{{ $gateways[$gateway] }}»</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ($fields[$gateway] as $fieldKey => $fieldLabel)
                            <label class="block">
                                <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                    {{ $fieldLabel }}
                                    <span class="text-[9px] font-normal text-slate-400">(خالی = استفاده از env)</span>
                                </span>
                                <input type="{{ str_contains($fieldKey, 'password') ? 'password' : 'text' }}"
                                       wire:model="fields.{{ $gateway }}.{{ $fieldKey }}"
                                       dir="ltr" autocomplete="off"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            </label>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-6 rounded-2xl border border-dashed border-emerald-300 bg-emerald-50/50 p-4 text-xs font-semibold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
                    درگاه آزمایشی: صفحه پرداخت ساختگی با دکمه‌های «پرداخت موفق» و «انصراف» — هیچ پول واقعی جابه‌جا نمی‌شود. برای راه‌اندازی واقعی، یکی از درگاه‌های بانکی را انتخاب کنید.
                </div>
            @endif

            <div class="mt-5 flex items-center gap-3">
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-2xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-60">
                    <span wire:loading wire:target="save"><x-icon name="rotate" class="size-4 animate-spin" /></span>
                    ذخیره تنظیمات
                </button>
                <span class="text-[11px] text-slate-400">درگاه فعال فعلی: <b class="text-slate-600 dark:text-slate-300">{{ $gateways[$gateway] }}</b></span>
            </div>
        </div>

        {{-- ── help card ── --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="flex items-center gap-2 text-sm font-extrabold">
                <span class="grid size-9 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                    <x-icon name="info" class="size-4.5" />
                </span>
                راهنما
            </h2>
            <ul class="mt-4 space-y-3 text-[11px] leading-6 text-slate-500 dark:text-slate-400">
                <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-3.5 shrink-0 text-emerald-500" /> درگاه‌ها با پکیج shetabit/payment وصل شده‌اند؛ تغییر درگاه فوراً روی پرداخت‌های جدید اعمال می‌شود.</li>
                <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-3.5 shrink-0 text-emerald-500" /> سفارش‌های در جریان (pending) با همان درگاهی که از آن رفته‌اند verify می‌شوند.</li>
                <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-3.5 shrink-0 text-emerald-500" /> تاریخچه کامل تلاش‌های پرداخت در جدول پایین و در جزئیات هر سفارش قابل بررسی است.</li>
                <li class="flex gap-2"><x-icon name="alert" class="mt-0.5 size-3.5 shrink-0 text-amber-500" /> پیش از سوییچ به درگاه واقعی، با مبلغ کم تست بگیرید.</li>
            </ul>
        </div>
    </div>

    {{-- ── payment logs ── --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="file-text" class="size-4 text-primary-500" />
                آخرین رخدادهای پرداخت
            </h3>
            <span class="text-[11px] text-slate-400">{{ \App\Support\Fmt::fa($logs->count()) }} ردیف اخیر</span>
        </div>

        @if ($logs->isEmpty())
            <p class="px-5 py-10 text-center text-xs text-slate-400">هنوز پرداختی ثبت نشده است.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                            <th class="px-5 py-3 font-semibold">سفارش</th>
                            <th class="px-3 py-3 font-semibold">درگاه</th>
                            <th class="px-3 py-3 font-semibold">رخداد</th>
                            <th class="px-3 py-3 font-semibold">مبلغ</th>
                            <th class="px-3 py-3 font-semibold">زمان</th>
                            <th class="px-5 py-3 font-semibold">نتیجه</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach ($logs as $log)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="pl-{{ $log->id }}">
                                <td class="px-5 py-3">
                                    @if ($log->order)
                                        <a href="{{ route('admin.orders.show', $log->order) }}" wire:navigate class="text-xs font-extrabold text-primary-600 hover:text-primary-700">#{{ \App\Support\Fmt::fa($log->order->id) }}</a>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-[11px] text-slate-500">{{ $log->gateway }}</td>
                                <td class="px-3 py-3 text-[11px] font-semibold">{{ $this->eventLabel($log->event) }}</td>
                                <td class="px-3 py-3 text-xs font-bold">{{ $log->amount ? \App\Support\Fmt::money($log->amount) : '—' }}</td>
                                <td class="px-3 py-3 text-[10px] text-slate-400">{{ \App\Support\JDate::dateTime($log->created_at) }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $log->ok
                                        ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                                        : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                                        {{ $log->ok ? 'موفق' : 'ناموفق' }}
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
