<div class="ip-fade-up space-y-4">

    {{-- ── flash ── --}}
    @if (session('bnpl-admin-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4500)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('bnpl-admin-flash') }}
        </div>
    @endif

    {{-- ── header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="flex items-center gap-2 text-lg font-black">
                <span class="grid size-9 place-items-center rounded-xl bg-violet-600/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                    <x-icon name="calendar-clock" class="size-5" />
                </span>
                تسویه اقساطی
            </h1>
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                تراکنش‌های درگاه‌های اقساطی (دیجی‌پی / اسنپ‌پی / ترب‌پی) — سهم هر فروشگاه از سفارش‌های اقساطی، سررسید و وضعیت پرداخت. هر درگاه دورهٔ تسویهٔ خودش را دارد (هر ماه / هر ۲ ماه / هر ۳ ماه) و اگر حالت تسویه‌اش «خودکار» باشد، کران روزانه (۰۶:۰۰) موارد سررسیدشده را واریز می‌کند — درگاه‌های «دستی» فقط با واریز مدیر جابه‌جا می‌شوند.
            </p>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <a href="{{ route('admin.bnpl.settings') }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-xl border border-violet-300 bg-white px-3 py-2 text-[11px] font-bold text-violet-600 shadow-sm transition hover:bg-violet-50 dark:border-violet-800 dark:bg-slate-900 dark:text-violet-300 dark:hover:bg-violet-950/40">
                <x-icon name="settings" class="size-3.5" />
                تنظیمات اقساط
            </a>
            @if ($stats['due_now_count'] > 0)
                <button type="button" wire:click="depositDueNow"
                        wire:confirm="همهٔ {{ \App\Support\Fmt::fa($stats['due_now_count']) }} تسویهٔ سررسیدشده همین حالا به کیف پول فروشندگان واریز شود؟"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-[11px] font-bold text-white shadow-sm transition hover:bg-emerald-700">
                    <x-icon name="wallet" class="size-3.5" />
                    واریز گروهی سررسیدشده‌ها ({{ \App\Support\Fmt::fa($stats['due_now_count']) }})
                </button>
            @endif
        </div>
    </div>

    {{-- ── stats chips ── --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900 dark:bg-amber-950/30">
            <div class="text-[10px] font-bold text-amber-600 dark:text-amber-300">در انتظار تسویه</div>
            <div class="mt-1 text-lg font-black text-amber-700 dark:text-amber-200">{{ \App\Support\Fmt::money($stats['pending_amount']) }}</div>
            <div class="text-[9.5px] font-bold text-amber-500/80">{{ \App\Support\Fmt::fa($stats['pending_count']) }} مورد</div>
        </div>
        <div class="rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3 dark:border-violet-900 dark:bg-violet-950/30">
            <div class="text-[10px] font-bold text-violet-600 dark:text-violet-300">سررسیدشده (کران واریز می‌کند)</div>
            <div class="mt-1 text-lg font-black text-violet-700 dark:text-violet-200">{{ \App\Support\Fmt::fa($stats['due_now_count']) }} مورد</div>
            <div class="text-[9.5px] font-bold text-violet-500/80">فقط درگاه‌های «خودکار» — دستی‌ها با مدیر</div>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 dark:border-rose-900 dark:bg-rose-950/30">
            <div class="text-[10px] font-bold text-rose-600 dark:text-rose-300">معوق (گذشته از سررسید)</div>
            <div class="mt-1 text-lg font-black text-rose-700 dark:text-rose-200">{{ \App\Support\Fmt::money($stats['overdue_amount']) }}</div>
            <div class="text-[9.5px] font-bold text-rose-500/80">{{ \App\Support\Fmt::fa($stats['overdue_count']) }} مورد — با فیلتر «معوق» ببینید</div>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-900 dark:bg-emerald-950/30">
            <div class="text-[10px] font-bold text-emerald-600 dark:text-emerald-300">جمع واریز شده</div>
            <div class="mt-1 text-lg font-black text-emerald-700 dark:text-emerald-200">{{ \App\Support\Fmt::money($stats['paid_amount']) }}</div>
            <div class="text-[9.5px] font-bold text-emerald-500/80">از ابتدای فعال‌سازی</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-800 dark:bg-slate-900">
            <div class="text-[10px] font-bold text-slate-400">آخرین اجرای کران</div>
            <div class="mt-1 text-sm font-black text-slate-600 dark:text-slate-300" dir="ltr">
                {{ \App\Support\JDate::dateTime(\Illuminate\Support\Carbon::parse((string) (\App\Models\Setting::get('payment.bnpl_last_settle_run') ?? now()->toDateTimeString()))) }}
            </div>
        </div>
    </div>

    {{-- ── filters ── --}}
    <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <label class="inline-flex items-center gap-2 text-[11px] font-bold text-slate-500 dark:text-slate-400">
            <span class="sr-only">درگاه</span>
            <select wire:model.live="gateway"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-bold shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                <option value="all">همهٔ درگاه‌ها</option>
                @foreach ($bnplGateways as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="inline-flex items-center gap-2 text-[11px] font-bold text-slate-500 dark:text-slate-400">
            <span class="sr-only">وضعیت</span>
            <select wire:model.live="status"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-bold shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                <option value="all">همهٔ وضعیت‌ها</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="inline-flex min-w-52 flex-1 items-center gap-2 text-[11px] font-bold text-slate-500 dark:text-slate-400">
            <x-icon name="search" class="size-3.5 shrink-0" />
            <span class="sr-only">جستجو</span>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="جستجو: شماره سفارش، نام/شهر فروشنده…"
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-bold shadow-sm transition focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
        </label>
        @if ($gateway !== 'all' || $status !== 'all' || trim($search) !== '')
            <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-2 text-[10.5px] font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                <x-icon name="x" class="size-3" />
                حذف فیلترها
            </button>
        @endif
    </div>

    {{-- ── settlement rows ── --}}
    <div class="space-y-2.5">
        @forelse ($settlements as $row)
            @php
                $dueToday = $row->status === 'pending' && $row->due_date->lte(today());
            @endphp
            <div wire:key="bnpl-set-{{ $row->id }}"
                 class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-slate-900 {{ $row->status === 'canceled' ? 'opacity-70' : '' }}">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl border border-slate-100 bg-white p-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-950/40">
                        @include('partials.gateway-logo', ['gateway' => $row->gateway])
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="text-xs font-extrabold">سفارش #{{ \App\Support\Fmt::fa($row->order?->id ?? $row->order_id) }}</span>
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $row->seller?->shop?->name ?? ($row->seller?->full_name ?: $row->seller?->instagram_username ?: 'فروشنده #'.\App\Support\Fmt::fa($row->seller_id)) }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[9.5px] font-extrabold {{ $row->statusClasses() }}">{{ $row->statusLabel() }}</span>
                            @if ($row->manual && $row->status === 'paid')
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">دستی</span>
                            @endif
                            @if ($row->status === 'pending' && in_array($row->gateway, $manualGateways, true))
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[9px] font-extrabold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300" title="حالت تسویهٔ این درگاه «دستی» است — کران واریز نمی‌کند">
                                    <x-icon name="edit" class="size-2.5" /> تسویهٔ دستی
                                </span>
                            @endif
                            @if ($dueToday)
                                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[9px] font-extrabold text-rose-600 dark:bg-rose-950/60 dark:text-rose-300">سررسید گذشته</span>
                            @endif
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[9.5px] font-bold text-slate-400">
                            <span>کالا: {{ \App\Support\Fmt::money($row->items_total) }}</span>
                            @if ($row->fee > 0)
                                <span>· کارمزد اقساط: {{ \App\Support\Fmt::money($row->fee) }}</span>
                            @endif
                            @if ($row->commission > 0)
                                <span>· کمیسیون: {{ \App\Support\Fmt::money($row->commission) }}</span>
                            @endif
                            <span>· سررسید: {{ $row->dueDateFa() }}</span>
                            @if ($row->status === 'paid' && $row->paid_at)
                                <span>· واریز: {{ \App\Support\JDate::dateTime($row->paid_at) }}</span>
                            @endif
                            @if ($row->status === 'canceled' && $row->note)
                                <span class="text-rose-400">· {{ $row->note }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="shrink-0 text-left">
                        <div class="text-sm font-black text-violet-600 dark:text-violet-300" dir="ltr">
                            {{ \App\Support\Fmt::money($row->amount) }}
                        </div>
                    </div>

                    @if ($row->status === 'pending')
                        <div class="flex shrink-0 items-center gap-2">
                            <button type="button" wire:click="depositNow({{ $row->id }})"
                                    wire:confirm="مبلغ {{ \App\Support\Fmt::money($row->amount) }} همین حالا به کیف پول فروشنده واریز شود؟"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2 text-[10.5px] font-bold text-white shadow-sm transition hover:bg-emerald-700"
                                    title="واریز دستی به کیف پول فروشنده">
                                <x-icon name="wallet" class="size-3.5" />
                                واریز دستی
                            </button>
                            <button type="button" wire:click="cancelSettlement({{ $row->id }})"
                                    wire:confirm="این تسویه لغو شود و دیگر واریز نشود؟"
                                    class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40"
                                    title="لغو تسویه">
                                <x-icon name="x" class="size-4" />
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center dark:border-slate-700 dark:bg-slate-900/60">
                <x-icon name="calendar-clock" class="mx-auto size-8 text-slate-300 dark:text-slate-600" />
                <p class="mt-2 text-xs font-bold text-slate-400">هیچ تسویهٔ اقساطی ثبت نشده — با اولین خرید اقساطی، سهم هر فروشگاه اینجا ظاهر می‌شود.</p>
            </div>
        @endforelse
    </div>

    {{-- pagination --}}
    @if ($settlements->hasPages())
        <div class="flex items-center justify-center gap-1">
            {{ $settlements->links('pagination::tailwind') }}
        </div>
    @endif
</div>
