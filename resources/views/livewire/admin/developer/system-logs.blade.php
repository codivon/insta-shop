<div class="ip-fade-up space-y-5"
     x-data="{ confirmOpen: false }"
     x-on:keydown.escape.window="confirmOpen = false; $wire.cancelAction()">

    {{-- ── flash ── --}}
    @if (session('admin-devlog-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-devlog-flash') }}
        </div>
    @endif
    @if (session('admin-devlog-error'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" x-data x-init="setTimeout(() => $el.remove(), 6000)">
            <x-icon name="alert" class="size-4.5 shrink-0" />
            {{ session('admin-devlog-error') }}
        </div>
    @endif

    {{-- ── header ── --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-lg shadow-amber-500/25">
                    <x-icon name="file-text" class="size-5" />
                </span>
                <div>
                    <h1 class="text-base font-black">لاگ سیستم</h1>
                    <p class="mt-0.5 text-[11px] leading-5 text-slate-400">
                        خطاها و رویدادهای ثبت‌شده در <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px] dark:bg-slate-800" dir="ltr">storage/logs</code> — برای اشکال‌زدایی سریع.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="$refresh"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-[11px] font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                    <x-icon name="rotate" class="size-3.5" />
                    بروزرسانی
                </button>
                <button type="button" wire:click="askClearCurrent" x-on:click="confirmOpen = true"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 px-3.5 py-2 text-[11px] font-bold text-amber-700 transition hover:bg-amber-100 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
                    <x-icon name="trash" class="size-3.5" />
                    پاک‌سازی فایل فعلی
                </button>
                <button type="button" wire:click="askDeleteOthers" x-on:click="confirmOpen = true"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-[11px] font-bold text-rose-600 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
                    <x-icon name="x" class="size-3.5" />
                    حذف فایل‌های قدیمی
                </button>
            </div>
        </div>

        {{-- ── log files picker ── --}}
        @if (count($files) > 0)
            <div class="scroll-slim mt-4 flex gap-2 overflow-x-auto border-t border-dashed border-slate-200 pt-4 dark:border-slate-700">
                @foreach ($files as $f)
                    <button type="button" wire:click="selectFile('{{ $f['name'] }}')"
                            class="flex shrink-0 items-center gap-2 rounded-xl border px-3 py-2 text-[11px] font-bold transition {{ $f['is_current']
                                ? 'border-primary-500 bg-primary-50 text-primary-700 ring-2 ring-primary-500/25 dark:bg-primary-950/40 dark:text-primary-300'
                                : 'border-slate-200 text-slate-500 hover:border-primary-300 dark:border-slate-700 dark:text-slate-400' }}">
                        <x-icon name="file-text" class="size-3.5 {{ $f['is_current'] ? 'text-primary-600' : 'text-slate-400' }}" />
                        <span dir="ltr">{{ $f['label'] }}</span>
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[9px] font-black text-slate-400 dark:bg-slate-800">{{ number_format($f['size'] / 1024, 0) }}KB</span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── filters ── --}}
    <div class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-900">
        {{-- level chips --}}
        <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="فیلتر سطح خطا">
            @php
                $chips = [
                    'all'     => ['همه', 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'],
                    'error'   => ['خطا', 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-300'],
                    'warning' => ['هشدار', 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300'],
                    'info'    => ['اطلاع', 'bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-300'],
                    'debug'   => ['دیباگ', 'bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-300'],
                    'other'   => ['سایر', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300'],
                ];
            @endphp
            @foreach ($chips as $key => [$label, $tone])
                <button type="button" wire:click="selectLevel('{{ $key }}')"
                        class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold transition {{ $level === $key
                            ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                            : $tone.' hover:opacity-80' }}">
                    {{ $label }}
                    <span class="rounded-full px-1.5 text-[9px] font-black {{ $level === $key ? 'bg-white/25' : 'bg-white/70 dark:bg-slate-900/60' }}">{{ \App\Support\Fmt::fa($stats[$key]) }}</span>
                </button>
            @endforeach
        </div>

        {{-- search --}}
        <div class="relative sm:w-72">
            <x-icon name="search" class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجو در پیام‌ها و خطاها..."
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pe-3 ps-9 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800">
        </div>
    </div>

    {{-- ── entries ── --}}
    @if ($file === '')
        <div class="rounded-3xl border border-dashed border-slate-300 p-12 text-center dark:border-slate-700">
            <x-icon name="file-text" class="mx-auto size-10 text-slate-300" />
            <p class="mt-3 text-xs font-bold text-slate-400">هیچ فایل لاگی یافت نشد.</p>
            <p class="mt-1 text-[11px] text-slate-400">به‌محض اولین خطا یا رویداد، فایل لاگ اینجا نمایش داده می‌شود.</p>
        </div>
    @elseif (count($entries) === 0)
        <div class="rounded-3xl border border-dashed border-emerald-300 bg-emerald-50/50 p-12 text-center dark:border-emerald-800 dark:bg-emerald-950/30">
            <x-icon name="check-circle-2" class="mx-auto size-10 text-emerald-400" />
            <p class="mt-3 text-xs font-bold text-emerald-600 dark:text-emerald-300">ورودی‌ای مطابق فیلترهای فعلی نیست — همه‌چیز تمیز است.</p>
        </div>
    @else
        <div class="space-y-2.5">
            @foreach ($entries as $i => $e)
                @php
                    $tone = match ($e['level']) {
                        'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => ['bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900', 'text-rose-500'],
                        'WARNING', 'NOTICE'                       => ['bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900', 'text-amber-500'],
                        'INFO'                                    => ['bg-sky-50 text-sky-600 border-sky-100 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-900', 'text-sky-500'],
                        'DEBUG'                                   => ['bg-violet-50 text-violet-600 border-violet-100 dark:bg-violet-950/40 dark:text-violet-300 dark:border-violet-900', 'text-violet-500'],
                        default                                   => ['bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700', 'text-slate-500'],
                    };
                @endphp
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow dark:border-slate-800 dark:bg-slate-900" wire:key="log-{{ $i }}-{{ md5($e['ts'].$e['msg']) }}">
                    <details>
                        <summary class="flex cursor-pointer list-none items-start gap-3 p-4 [&::-webkit-details-marker]:hidden">
                            <span class="shrink-0 rounded-lg border px-2 py-1 text-[9px] font-black {{ $tone[0] }}" dir="ltr">{{ $e['level'] }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="line-clamp-2 block text-xs font-bold leading-6 text-slate-700 dark:text-slate-200" dir="auto">{{ $e['msg'] }}</span>
                                <span class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] text-slate-400">
                                    <span class="flex items-center gap-1"><x-icon name="clock" class="size-3" /> {{ \App\Support\JDate::dateTime(\Carbon\Carbon::parse($e['ts'])) }}</span>
                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 font-bold dark:bg-slate-800">{{ $e['env'] }}</span>
                                    @if ($e['stack'] !== '')
                                        <span class="font-bold text-primary-500">جزئیات و ردپای خطا ↓</span>
                                    @endif
                                </span>
                            </span>
                            <x-icon name="chevron-down" class="mt-1 size-3.5 shrink-0 text-slate-300" />
                        </summary>
                        @if ($e['stack'] !== '')
                            <div class="border-t border-dashed border-slate-200 px-4 py-3 dark:border-slate-700">
                                <pre class="scroll-slim max-h-72 overflow-auto rounded-xl bg-slate-950 p-3.5 text-[10.5px] leading-5 text-emerald-200/90" dir="ltr">{{ $e['stack'] }}</pre>
                            </div>
                        @endif
                    </details>
                </div>
            @endforeach
        </div>

        @if ($total > count($entries))
            <div class="text-center">
                <button type="button" wire:click="loadMore" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                    نمایش بیشتر ({{ \App\Support\Fmt::fa($total - count($entries)) }} مورد دیگر)
                </button>
            </div>
        @endif
    @endif

    {{-- ═══════════ destructive confirm modal ═══════════ --}}
    <div x-cloak x-show="confirmOpen" x-on:click.self="confirmOpen = false; $wire.cancelAction()"
         class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
         role="alertdialog" aria-modal="true" aria-label="تأیید عملیات حذف"
         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-rose-50 text-rose-500 dark:bg-rose-950/40">
                <x-icon name="alert" class="size-6" />
            </span>
            <h3 class="mt-4 text-sm font-extrabold">
                {{ $confirmAction === 'clear-current' ? 'پاک‌سازی فایل لاگ فعلی' : 'حذف فایل‌های لاگ قدیمی' }}
            </h3>
            <p class="mt-2 text-[11px] leading-6 text-slate-400">
                @if ($confirmAction === 'clear-current')
                    تمام محتوای فایل «{{ $file }}» پاک می‌شود. خود فایل حفظ می‌شود. این اقدام قابل بازگشت نیست.
                @else
                    همه‌ی فایل‌های لاگ به‌جز فایل بازِ فعلی حذف می‌شوند. این اقدام قابل بازگشت نیست.
                @endif
            </p>
            <div class="mt-5 flex gap-2">
                @if ($confirmAction === 'clear-current')
                    <button type="button" wire:click="clearCurrent" x-on:click="confirmOpen = false" wire:loading.attr="disabled"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-amber-500/25 transition hover:bg-amber-600 disabled:opacity-50">
                        <span wire:loading wire:target="clearCurrent" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        پاک‌سازی
                    </button>
                @else
                    <button type="button" wire:click="deleteOthers" x-on:click="confirmOpen = false" wire:loading.attr="disabled"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-rose-600/25 transition hover:bg-rose-700 disabled:opacity-50">
                        <span wire:loading wire:target="deleteOthers" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        حذف قطعی
                    </button>
                @endif
                <button type="button" x-on:click="confirmOpen = false; $wire.cancelAction()"
                        class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    انصراف
                </button>
            </div>
        </div>
    </div>
</div>
