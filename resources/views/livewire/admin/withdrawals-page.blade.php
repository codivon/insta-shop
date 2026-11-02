<div class="ip-fade-up space-y-5">

    {{-- ═══════════ cards ═══════════ --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm dark:border-amber-900/60 dark:from-amber-950/30 dark:to-slate-900" wire:key="wd-c1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-700/80 dark:text-amber-300/80">در انتظار تأیید</span>
                <span class="grid size-9 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-300"><x-icon name="clock" class="size-4.5" /></span>
            </div>
            <div class="mt-3 text-lg font-extrabold text-amber-700 dark:text-amber-300">{{ \App\Support\Fmt::money($metrics['pending_sum']) }}</div>
            <div class="mt-1 text-[10.5px] text-slate-400">{{ \App\Support\Fmt::fa($metrics['pending_count']) }} درخواست باز</div>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm dark:border-emerald-900/60 dark:from-emerald-950/30 dark:to-slate-900" wire:key="wd-c2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700/80 dark:text-emerald-300/80">پرداخت‌شده</span>
                <span class="grid size-9 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300"><x-icon name="circle-check" class="size-4.5" /></span>
            </div>
            <div class="mt-3 text-lg font-extrabold text-emerald-700 dark:text-emerald-300">{{ \App\Support\Fmt::money($metrics['done_sum']) }}</div>
            <div class="mt-1 text-[10.5px] text-slate-400">جمع برداشت‌های تأییدشده</div>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-gradient-to-br from-rose-50 to-white p-5 shadow-sm dark:border-rose-900/60 dark:from-rose-950/30 dark:to-slate-900" wire:key="wd-c3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-700/80 dark:text-rose-300/80">ردشده</span>
                <span class="grid size-9 place-items-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-300"><x-icon name="circle-slash" class="size-4.5" /></span>
            </div>
            <div class="mt-3 text-lg font-extrabold text-rose-700 dark:text-rose-300">{{ \App\Support\Fmt::money($metrics['rejected_sum']) }}</div>
            <div class="mt-1 text-[10.5px] text-slate-400">{{ \App\Support\Fmt::fa($metrics['rejected_count']) }} درخواست — مبلغ به کیف بازگشت</div>
        </div>
    </div>

    {{-- ═══════════ filters ═══════════ --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <label class="block">
                <span class="mb-1.5 block text-[10px] font-bold text-slate-400">وضعیت</span>
                <select wire:model.live="statusFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-[11px] font-bold shadow-sm focus:border-primary-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    <option value="pending">در انتظار تأیید</option>
                    <option value="done">تأییدشده</option>
                    <option value="rejected">ردشده</option>
                    <option value="all">همه</option>
                </select>
            </label>
            <label class="block">
                <span class="mb-1.5 block text-[10px] font-bold text-slate-400">از تاریخ</span>
                <x-jdate-picker model="fromDate" placeholder="از ابتدا" />
            </label>
            <label class="block">
                <span class="mb-1.5 block text-[10px] font-bold text-slate-400">تا تاریخ</span>
                <x-jdate-picker model="toDate" placeholder="تا امروز" />
            </label>
            <div class="flex items-end">
                <button type="button" wire:click="$set('statusFilter', 'pending')"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3.5 py-2.5 text-[11px] font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800">
                    <x-icon name="rotate" class="size-3.5" />
                    بازنشانی
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════ rows ═══════════ --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <h3 class="text-sm font-extrabold">درخواست‌های برداشت فروشنده‌ها</h3>
            <span class="text-[10.5px] font-bold text-slate-400">{{ \App\Support\Fmt::fa($total) }} مورد</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-right text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60 text-[10.5px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                        <th class="px-5 py-3 font-semibold">فروشنده</th>
                        <th class="px-3 py-3 font-semibold">مبلغ</th>
                        <th class="px-3 py-3 font-semibold">شبا</th>
                        <th class="px-3 py-3 font-semibold">تاریخ درخواست</th>
                        <th class="px-3 py-3 font-semibold">وضعیت</th>
                        <th class="px-5 py-3 font-semibold">اقدام</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    @forelse ($rows as $tx)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="wd-{{ $tx->id }}">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-xs font-bold text-white">
                                        {{ mb_substr($tx->wallet?->seller?->full_name ?: '؟', 0, 1) }}
                                    </span>
                                    <span class="font-bold">{{ $tx->wallet?->seller?->full_name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-3.5 font-extrabold">{{ \App\Support\Fmt::money($tx->amount) }}</td>
                            <td class="px-3 py-3.5 text-[10.5px] text-slate-500" dir="ltr">{{ $tx->sheba ?: '—' }}</td>
                            <td class="px-3 py-3.5 text-[10.5px] text-slate-500">{{ $tx->created_at ? \App\Support\JDate::dateTime($tx->created_at) : '—' }}</td>
                            <td class="px-3 py-3.5"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $tx->statusClasses() }}">{{ $tx->statusLabel() }}</span></td>
                            <td class="px-5 py-3.5">
                                @if ($tx->status === 'pending')
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" wire:click="askApprove({{ $tx->id }})"
                                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-[10.5px] font-bold text-white shadow-md shadow-emerald-600/25 transition hover:bg-emerald-700">
                                            <x-icon name="check" class="size-3.5" />
                                            تأیید
                                        </button>
                                        <button type="button" wire:click="askReject({{ $tx->id }})"
                                                class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-3 py-1.5 text-[10.5px] font-bold text-rose-500 transition hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950/40">
                                            <x-icon name="x" class="size-3.5" />
                                            رد
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[10px] text-slate-300 dark:text-slate-600">پردازش شده</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                                    <x-icon name="wallet" class="size-6" />
                                </span>
                                <p class="mt-3 text-xs font-extrabold">درخواست برداشتی با این فیلترها یافت نشد.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($hasMore && $rows->isNotEmpty())
            <div class="flex justify-center border-t border-slate-100 py-3 dark:border-slate-800">
                <div x-data="{}" x-init="new IntersectionObserver((e) => { if (e[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '240px' }).observe($el)"
                     class="inline-block size-6 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"
                     aria-hidden="true"></div>
            </div>
        @endif
    </div>

    {{-- ═══════════ confirm modal ═══════════ --}}
    <x-modal-panel open="$wire.confirmId !== null" max-width="sm" close-js="__ipOpen = false; $wire.cancelConfirm()" z-class="z-[75]">
        <div class="p-6 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl {{ $confirmAction === 'approve' ? 'bg-emerald-50 text-emerald-500 dark:bg-emerald-950/40' : 'bg-rose-50 text-rose-500 dark:bg-rose-950/40' }}">
                <x-icon name="{{ $confirmAction === 'approve' ? 'check' : 'circle-slash' }}" class="size-6" />
            </span>
            <h3 class="mt-4 text-sm font-extrabold">{{ $confirmAction === 'approve' ? 'تأیید برداشت' : 'رد درخواست برداشت' }}</h3>
            <p class="mt-2 text-[11px] leading-6 text-slate-400">
                @if ($confirmAction === 'approve')
                    مبلغ برداشت به حساب اعلام‌شدهٔ فروشنده واریز می‌شود و وضعیت به «تسویه شد» تغییر می‌کند.
                @else
                    مبلغ رزروشده به کیف پول فروشنده بازگردانده می‌شود و فروشنده از رد درخواست باخبر خواهد شد.
                @endif
            </p>
            <div class="mt-5 flex gap-2">
                <button type="button" wire:click="execute" wire:loading.attr="disabled" wire:target="execute"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-md transition disabled:opacity-50 {{ $confirmAction === 'approve' ? 'bg-emerald-600 shadow-emerald-600/25 hover:bg-emerald-700' : 'bg-rose-600 shadow-rose-600/25 hover:bg-rose-700' }}">
                    <span wire:loading wire:target="execute" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    {{ $confirmAction === 'approve' ? 'تأیید و واریز' : 'رد و بازگشت مبلغ' }}
                </button>
                <button type="button" x-on:click="__ipOpen = false; $wire.cancelConfirm()"
                        class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    انصراف
                </button>
            </div>
        </div>
    </x-modal-panel>
</div>
