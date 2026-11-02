<div class="ip-fade-up space-y-5">

    {{-- ── header ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/25">
                    <x-icon name="database" class="size-5" />
                </span>
                <div>
                    <h1 class="text-base font-black">پشتیبان‌گیری</h1>
                    <p class="mt-0.5 text-[11px] leading-5 text-slate-400">
                        از کل داده‌های فروشگاه — مشتری‌ها، فروشگاه‌ها، سفارش‌ها، پرداخت‌ها، کیف پول و… — نسخهٔ پشتیبان بگیرید، فایل پشتیبان را بارگذاری و بازگردانی کنید.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="$refresh"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-[11px] font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                    <x-icon name="rotate" class="size-3.5" />
                    بروزرسانی
                </button>
                <button type="button" wire:click="startBackup"
                        wire:loading.attr="disabled" wire:target="startBackup"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2 text-[11px] font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-60">
                    <x-icon name="database" class="size-3.5" />
                    <span wire:loading.remove wire:target="startBackup">پشتیبان‌گیری الان</span>
                    <span wire:loading wire:target="startBackup">در حال ثبت...</span>
                </button>
            </div>
        </div>
    </div>

    <div @if ($running) wire:poll.3s @else wire:poll.30s @endif class="space-y-5">

        {{-- ── stats + auto status ── --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[10.5px] font-bold text-slate-400">فایل‌های پشتیبان</p>
                <p class="mt-1 text-lg font-black">{{ \App\Support\Fmt::fa($stats['count']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[10.5px] font-bold text-slate-400">حجم کل</p>
                <p class="mt-1 text-lg font-black">{{ \App\Support\Fmt::bytes($stats['size']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[10.5px] font-bold text-slate-400">پشتیبان‌گیری خودکار</p>
                <p class="mt-1 flex items-center gap-1.5 text-[12px] font-black {{ $autoEnabled ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                    <span class="size-2 rounded-full {{ $autoEnabled ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                    {{ $autoEnabled ? 'فعال است' : 'غیرفعال' }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[10.5px] font-bold text-slate-400">اجرای بعدی خودکار</p>
                <p class="mt-1 text-[12px] font-black {{ $autoEnabled && $cronAlive ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400' }}">
                    @if (! $autoEnabled)
                        —
                    @elseif (! $cronAlive)
                        کرون غیرفعال
                    @else
                        {{ $nextRun ? \App\Support\JDate::dateTime($nextRun) : '—' }}
                    @endif
                </p>
            </div>
        </div>

        {{-- ── auto-run hint strip ── --}}
        @if (! $autoEnabled || ! $cronAlive)
            <div class="flex items-start gap-2.5 rounded-2xl border p-4 text-[12px] leading-6 {{ $autoEnabled
                ? 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300'
                : 'border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400' }}">
                <x-icon name="info" class="mt-0.5 size-4 shrink-0" />
                <span>
                    @if ($autoEnabled && ! $cronAlive)
                        کرون جاب سرور در حال حاضر غیرفعال به‌نظر می‌رسد؛ پشتیبان‌گیری خودکار تا فعال‌شدن کرون اجرا نمی‌شود. (تنظیمات برنامه‌نویس ← کرون)
                    @else
                        پشتیبان‌گیری خودکار هنوز فعال نیست — از <a href="{{ route('admin.settings', ['tab' => 'backups']) }}" wire:navigate class="font-bold underline underline-offset-4">تنظیمات ← پشتیبان‌گیری</a> روزانه / هفتگی / ماهانه یا بازهٔ دلخواه را فعال کنید.
                    @endif
                </span>
            </div>
        @endif

        {{-- ── upload card ── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:ignore>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="upload" class="size-4.5" />
                    </span>
                    <div>
                        <h2 class="text-[13px] font-black">بارگذاری و بازگردانی فایل پشتیبان</h2>
                        <p class="mt-0.5 text-[11px] leading-5 text-slate-400">
                            آپلود تکه‌تکه انجام می‌شود (حتی فایل‌های حجیم) و بازگردانی در پس‌زمینه اجرا می‌گردد؛ ردیف‌های تکراری با جدول مقایسه و رد می‌شوند.
                        </p>
                    </div>
                </div>
                <div class="shrink-0">
                    <input type="file" id="bk-file" accept=".zip" class="hidden" />
                    <button type="button" id="bk-btn"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-primary-200 bg-primary-50 px-4 py-2.5 text-[11px] font-bold text-primary-700 transition hover:bg-primary-100 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
                        <x-icon name="upload" class="size-3.5" />
                        انتخاب فایل پشتیبان (.zip)
                    </button>
                </div>
            </div>

            <div id="bk-progress-wrap" class="mt-4 hidden">
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div id="bk-progress-bar" class="h-full rounded-full bg-primary-600 transition-all duration-300" style="width: 0%"></div>
                </div>
                <div class="mt-1.5 flex items-center justify-between">
                    <p id="bk-progress-text" class="text-[11px] font-bold text-slate-500 dark:text-slate-400">آماده‌سازی...</p>
                    <p id="bk-progress-err" class="hidden text-[11px] font-bold text-rose-600 dark:text-rose-400"></p>
                </div>
            </div>
        </div>

        {{-- ── backups list ── --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <h2 class="text-[13px] font-black">فایل‌های پشتیبان</h2>
                <p class="text-[10.5px] text-slate-400">جدیدترین در بالا</p>
            </div>

            @if ($running)
                <div class="flex items-center gap-2 border-b border-slate-100 bg-primary-50/60 px-5 py-2.5 text-[11px] font-bold text-primary-700 dark:border-slate-800 dark:bg-primary-950/20 dark:text-primary-300">
                    <span class="inline-block size-2 animate-pulse rounded-full bg-primary-600"></span>
                    یک عملیات در حال اجراست — این صفحه خودش بروز می‌شود...
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full min-w-[780px] text-right text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[10.5px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                            <th class="px-5 py-3 font-bold">فایل</th>
                            <th class="px-4 py-3 font-bold">وضعیت</th>
                            <th class="px-4 py-3 font-bold">آمار</th>
                            <th class="px-4 py-3 font-bold">حجم</th>
                            <th class="px-4 py-3 font-bold">تاریخ</th>
                            <th class="px-5 py-3 font-bold">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($backups as $backup)
                            <tr class="border-b border-slate-50 transition last:border-0 hover:bg-slate-50/70 dark:border-slate-800/60 dark:hover:bg-slate-800/30"
                                wire:key="backup-row-{{ $backup->id }}">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="truncate text-[12px] font-extrabold" dir="ltr">
                                            {{ $backup->original_name ?: $backup->filename ?: '—' }}
                                        </span>
                                        @if ($loop->first && $backup->status === \App\Models\Backup::STATUS_COMPLETED)
                                            <span class="shrink-0 rounded-md bg-primary-600 px-1.5 py-0.5 text-[9px] font-black text-white">جدیدترین</span>
                                        @endif
                                    </div>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                        <span class="rounded-md px-1.5 py-0.5 text-[9.5px] font-bold {{ $backup->typeClasses() }}">{{ $backup->typeLabel() }}</span>
                                        @if ($backup->type === \App\Models\Backup::TYPE_UPLOAD && $backup->original_name)
                                            <span class="text-[10px] text-slate-400" dir="ltr">{{ Str::limit($backup->original_name, 28) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[10px] font-black {{ $backup->statusClasses() }}">
                                        @if ($backup->isRunning())
                                            <span class="inline-block size-1.5 animate-pulse rounded-full bg-current"></span>
                                        @endif
                                        {{ $backup->statusLabel() }}
                                    </span>
                                    @if ($backup->status === \App\Models\Backup::STATUS_PROCESSING && $backup->tables_total > 0 && $backup->type !== \App\Models\Backup::TYPE_UPLOAD)
                                        <div class="mt-2 w-28">
                                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                <div class="h-full rounded-full bg-primary-600 transition-all" style="width: {{ (int) round($backup->tables_done / max(1, $backup->tables_total) * 100) }}%"></div>
                                            </div>
                                            <p class="mt-1 text-[9.5px] text-slate-400">
                                                جدول {{ \App\Support\Fmt::fa($backup->tables_done) }} از {{ \App\Support\Fmt::fa($backup->tables_total) }}
                                            </p>
                                        </div>
                                    @elseif ($backup->status === \App\Models\Backup::STATUS_PROCESSING && $backup->type === \App\Models\Backup::TYPE_UPLOAD && $backup->tables_total > 0)
                                        <p class="mt-1 text-[9.5px] text-slate-400">
                                            جدول {{ \App\Support\Fmt::fa($backup->tables_done) }} از {{ \App\Support\Fmt::fa($backup->tables_total) }}
                                        </p>
                                    @elseif ($backup->status === \App\Models\Backup::STATUS_FAILED && $backup->error)
                                        <p class="mt-1 max-w-[220px] truncate text-[9.5px] text-rose-500" title="{{ $backup->error }}">{{ $backup->error }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-[11px] leading-6 text-slate-500 dark:text-slate-400">
                                    @if ($backup->type === \App\Models\Backup::TYPE_UPLOAD)
                                        <p>{{ \App\Support\Fmt::fa($backup->rows_inserted) }} ردیف افزوده شد</p>
                                        <p class="text-[10px] text-slate-400">{{ \App\Support\Fmt::fa($backup->rows_skipped) }} ردیف تکراری رد شد</p>
                                    @else
                                        <p>{{ \App\Support\Fmt::fa($backup->records_count) }} ردیف</p>
                                        <p class="text-[10px] text-slate-400">{{ \App\Support\Fmt::fa($backup->tables_done ?: $backup->tables_total) }} جدول</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                    {{ $backup->size > 0 ? \App\Support\Fmt::bytes($backup->size) : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-[11px] text-slate-500 dark:text-slate-400">
                                    <p>{{ \App\Support\JDate::date($backup->created_at) }}</p>
                                    <p class="mt-0.5 text-[10px] text-slate-400">{{ \App\Support\JDate::ago($backup->created_at) }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-1.5">
                                        @if ($backup->status === \App\Models\Backup::STATUS_COMPLETED && $backup->path)
                                            <a href="{{ route('admin.backups.download', $backup) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[10px] font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                                               title="دانلود فایل پشتیبان">
                                                <x-icon name="download" class="size-3.5" />
                                                دانلود
                                            </a>
                                        @endif
                                        @unless ($backup->isRunning())
                                            <button type="button" wire:click="confirmDelete({{ $backup->id }})"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-[10px] font-bold text-rose-600 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400"
                                                    title="حذف فایل پشتیبان">
                                                <x-icon name="trash" class="size-3.5" />
                                                حذف
                                            </button>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <x-icon name="database" class="mx-auto size-10 text-slate-300 dark:text-slate-600" />
                                    <p class="mt-4 text-sm font-bold text-slate-400">هنوز فایل پشتیبانی وجود ندارد</p>
                                    <p class="mt-1 text-[11px] text-slate-400">با دکمهٔ «پشتیبان‌گیری الان» اولین نسخهٔ پشتیبان را بسازید.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($backups->hasPages())
                <div class="border-t border-slate-100 px-5 py-3 dark:border-slate-800">
                    {{ $backups->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ── delete confirm modal ── --}}
    <x-modal-panel open="$wire.deleteId !== null" max-width="sm" close-js="__ipOpen = false; $wire.cancelDelete()" z-class="z-[75]" labelledby="bk-delete-title">
        <div class="p-6 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-rose-50 text-rose-500 dark:bg-rose-950/40">
                <x-icon name="trash" class="size-6" />
            </span>
            <h3 id="bk-delete-title" class="mt-4 text-sm font-extrabold">حذف فایل پشتیبان</h3>
            <p class="mt-2 text-[12px] leading-6 text-slate-500 dark:text-slate-400">
                فایل پشتیبان برای همیشه از سرور حذف می‌شود و قابل بازگشت نیست. مطمئنید؟
            </p>
            <div class="mt-5 flex gap-2">
                <button type="button" wire:click="cancelDelete" x-on:click="__ipOpen = false"
                        class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    انصراف
                </button>
                <button type="button" wire:click="deleteBackup" x-on:click="__ipOpen = false" wire:loading.attr="disabled" wire:target="deleteBackup"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-rose-600/25 transition hover:bg-rose-700 disabled:opacity-50">
                    <x-icon name="trash" class="size-4" />
                    حذف کن
                </button>
            </div>
        </div>
    </x-modal-panel>
</div>

@script
<script>
    (function () {
        const routeUrl = @js(route('admin.backups.upload'));
        const csrf = @js(csrf_token());

        const input = document.getElementById('bk-file');
        const button = document.getElementById('bk-btn');
        const wrap = document.getElementById('bk-progress-wrap');
        const bar = document.getElementById('bk-progress-bar');
        const text = document.getElementById('bk-progress-text');
        const errBox = document.getElementById('bk-progress-err');

        if (!input || !button) return;

        button.addEventListener('click', () => input.click());

        const newId = () => (window.crypto && crypto.randomUUID
            ? crypto.randomUUID()
            : 'u' + Date.now() + '-' + Math.random().toString(16).slice(2)).toLowerCase();

        const setProgress = (p, t) => {
            bar.style.width = p + '%';
            if (t) text.textContent = t;
        };

        input.addEventListener('change', async function () {
            const file = input.files && input.files[0];
            if (!file) return;

            wrap.classList.remove('hidden');
            errBox.classList.add('hidden');
            setProgress(0, 'آماده‌سازی آپلود...');

            const id = newId();
            const chunkSize = 4 * 1024 * 1024;
            const total = Math.max(1, Math.ceil(file.size / chunkSize));

            try {
                for (let i = 0; i < total; i++) {
                    const fd = new FormData();
                    fd.append('phase', 'chunk');
                    fd.append('uuid', id);
                    fd.append('index', String(i));
                    fd.append('chunk', file.slice(i * chunkSize, (i + 1) * chunkSize));

                    const res = await fetch(routeUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: fd,
                    });

                    if (!res.ok) {
                        const data = await res.json().catch(() => ({}));
                        throw new Error(data.message || 'خطا در آپلود تکهٔ ' + (i + 1));
                    }

                    setProgress(Math.round(((i + 1) / total) * 90), 'در حال آپلود — ' + Math.round(((i + 1) / total) * 100) + '٪');
                }

                setProgress(95, 'بررسی فایل و ثبت بازگردانی در صف...');

                const fd2 = new FormData();
                fd2.append('phase', 'finish');
                fd2.append('uuid', id);
                fd2.append('name', file.name);

                const res2 = await fetch(routeUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: fd2,
                });
                const data2 = await res2.json().catch(() => ({}));

                if (!res2.ok || !data2.ok) {
                    throw new Error(data2.message || 'ثبت فایل ناموفق بود');
                }

                setProgress(100, 'آپلود کامل شد — بازگردانی در پس‌زمینه آغاز شد.');
                if (window.Livewire) {
                    window.Livewire.dispatch('backups.changed', { message: data2.message, type: 'success' });
                }

                input.value = '';
                setTimeout(() => wrap.classList.add('hidden'), 5000);
            } catch (e) {
                errBox.textContent = e.message || 'آپلود ناموفق بود';
                errBox.classList.remove('hidden');
                if (window.Livewire) {
                    window.Livewire.dispatch('backups.changed', { message: 'آپلود فایل پشتیبان ناموفق بود ❌', type: 'error' });
                }
            }
        });
    })();
</script>
@endscript
