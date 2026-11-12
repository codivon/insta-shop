<div class="ip-fade-up space-y-4">

    {{-- ── header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="flex items-center gap-2 text-lg font-black">
                <span class="grid size-9 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                    <x-icon name="list" class="size-5" />
                </span>
                تراکنش‌های بانکی
            </h1>
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                آخرین رخدادهای پرداخت — هر تلاش خرید، هدایت به درگاه، تصمیم تسهیم و تأیید پرداخت اینجا ثبت می‌شود.
            </p>
        </div>
        <a href="{{ route('admin.settings', ['tab' => 'payment']) }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold text-slate-600 shadow-sm transition hover:border-primary-300 hover:text-primary-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-primary-800">
            <x-icon name="credit-card" class="size-3.5" />
            تنظیمات درگاه‌ها
        </a>
    </div>

    {{-- ── filters ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <label class="block">
                <span class="mb-1.5 block text-[10.5px] font-bold text-slate-500 dark:text-slate-400">درگاه</span>
                <select wire:model.live="gateway"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    <option value="all">همهٔ درگاه‌ها</option>
                    @foreach ($gateways as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-1.5 block text-[10.5px] font-bold text-slate-500 dark:text-slate-400">رخداد</span>
                <select wire:model.live="event"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    <option value="all">همهٔ رخدادها</option>
                    @foreach ($events as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-1.5 block text-[10.5px] font-bold text-slate-500 dark:text-slate-400">نتیجه</span>
                <select wire:model.live="result"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    <option value="all">همه</option>
                    <option value="1">موفق</option>
                    <option value="0">ناموفق</option>
                </select>
            </label>
            <label class="block">
                <span class="mb-1.5 block text-[10.5px] font-bold text-slate-500 dark:text-slate-400">جستجو (سفارش / کد تراکنش / پیام)</span>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.400ms="search" placeholder="مثلاً ۱۲ یا REF..."
                           dir="auto"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    <span wire:loading wire:target="search" class="absolute end-3 top-1/2 -translate-y-1/2">
                        <x-icon name="rotate" class="size-3.5 animate-spin text-slate-400" />
                    </span>
                </div>
            </label>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <p class="text-[10.5px] text-slate-400">{{ \App\Support\Fmt::fa($logs->total()) }} رخداد ثبت شده</p>
            <button type="button" wire:click="resetFilters"
                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-[10.5px] font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                پاک کردن فیلترها
            </button>
        </div>
    </div>

    {{-- ── table ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($logs->isEmpty())
            <div class="px-6 py-16 text-center">
                <x-icon name="file-text" class="mx-auto size-10 text-slate-300 dark:text-slate-600" />
                <p class="mt-4 text-sm font-bold text-slate-400">رخدادی با این فیلترها پیدا نشد</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                            <th class="px-5 py-3 font-semibold">سفارش</th>
                            <th class="px-3 py-3 font-semibold">درگاه</th>
                            <th class="px-3 py-3 font-semibold">رخداد</th>
                            <th class="px-3 py-3 font-semibold">کد تراکنش</th>
                            <th class="px-3 py-3 font-semibold">مبلغ</th>
                            <th class="px-3 py-3 font-semibold">پیام</th>
                            <th class="px-3 py-3 font-semibold">زمان</th>
                            <th class="px-5 py-3 font-semibold">نتیجه</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach ($logs as $log)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="pl-{{ $log->id }}">
                                <td class="px-5 py-3">
                                    @if ($log->order)
                                        <a href="{{ route('admin.orders.show', $log->order) }}" wire:navigate
                                           class="text-xs font-extrabold text-primary-600 hover:text-primary-700">
                                            #{{ \App\Support\Fmt::fa($log->order->id) }}
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-[11px] text-slate-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] dark:bg-slate-800">{{ $log->gateway }}</code>
                                        {{ $gateways[$log->gateway] ?? '' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $this->eventTone($log->event, (bool) $log->ok) }}">
                                        {{ $this->eventLabel($log->event) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($log->transaction_id)
                                        <code class="font-mono text-[10px] text-slate-500" dir="ltr">{{ \Illuminate\Support\Str::limit($log->transaction_id, 24) }}</code>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs font-bold">{{ $log->amount ? \App\Support\Fmt::money($log->amount) : '—' }}</td>
                                <td class="max-w-[280px] px-3 py-3">
                                    <span class="block truncate text-[10.5px] text-slate-500 dark:text-slate-400" title="{{ $log->message }}">
                                        {{ $log->message ?? '—' }}
                                    </span>
                                </td>
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

    @if ($logs->hasPages())
        <div class="px-2">
            {{ $logs->links() }}
        </div>
    @endif
</div>
