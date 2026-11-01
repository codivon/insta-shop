<div class="ip-fade-up">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                    <x-icon name="wallet" class="size-5" />
                </span>
                <div>
                    <div class="text-sm font-extrabold leading-5">کیف پول</div>
                    <div class="text-[11px] text-slate-400">درآمد فروش و برداشت‌ها</div>
                </div>
            </div>
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">
        {{-- flash toast --}}
        @if (session('wallet-flash'))
            <div class="mb-3 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-bold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <x-icon name="circle-check" class="size-4" />
                {{ session('wallet-flash') }}
            </div>
        @endif

        {{-- ───────── Balance hero card ───────── --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-bl from-primary-700 via-primary-600 to-primary-500 p-5 text-white shadow-xl shadow-primary-600/25">
            <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
                <div class="absolute -top-8 left-6 size-36 rounded-full bg-white/30 blur-2xl"></div>
                <div class="absolute -bottom-12 right-10 size-40 rounded-full bg-white/20 blur-2xl"></div>
            </div>

            <div class="relative flex items-start justify-between">
                <div>
                    <p class="flex items-center gap-1.5 text-[11px] text-primary-100">
                        <x-icon name="coins" class="size-3.5" />
                        موجودی قابل برداشت
                    </p>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black tracking-tight">{{ \App\Support\Fmt::fa($wallet->balance) }}</span>
                        <span class="text-xs font-bold text-primary-100">تومان</span>
                    </div>
                </div>
                <span class="rounded-full bg-white/15 px-2.5 py-1 text-[9px] font-bold backdrop-blur">
                    در انتظار تأیید: {{ \App\Support\Fmt::fa($stats['pending']) }}
                </span>
            </div>

            <button type="button" wire:click="openWithdraw" @click="true"
                    class="relative mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-white py-3 text-xs font-extrabold text-primary-700 shadow-lg transition hover:bg-primary-50 active:scale-[.98]">
                <x-icon name="arrow-up-left" class="size-4" />
                درخواست برداشت
            </button>
        </div>

        {{-- ───────── stats ───────── --}}
        <div class="mt-3 grid grid-cols-3 gap-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="mx-auto grid size-8 place-items-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <x-icon name="arrow-down-left" class="size-4" />
                </span>
                <div class="mt-1.5 text-[11px] font-black">{{ \App\Support\Fmt::fa($stats['income']) }}</div>
                <div class="text-[9px] font-semibold text-slate-400">مجموع درآمد (تومان)</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="mx-auto grid size-8 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-950/50 dark:text-primary-400">
                    <x-icon name="arrow-up-right" class="size-4" />
                </span>
                <div class="mt-1.5 text-[11px] font-black">{{ \App\Support\Fmt::fa($stats['withdrawn']) }}</div>
                <div class="text-[9px] font-semibold text-slate-400">برداشت‌شده (تومان)</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="mx-auto grid size-8 place-items-center rounded-xl bg-amber-50 text-amber-500 dark:bg-amber-950/50 dark:text-amber-400">
                    <x-icon name="clock" class="size-4" />
                </span>
                <div class="mt-1.5 text-[11px] font-black">{{ \App\Support\Fmt::fa($stats['pending']) }}</div>
                <div class="text-[9px] font-semibold text-slate-400">در انتظار تأیید (تومان)</div>
            </div>
        </div>

        {{-- ───────── transactions ───────── --}}
        <div class="mt-5 flex items-center justify-between">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="file-text" class="size-4 text-primary-500" />
                گردش حساب
            </h3>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                {{ \App\Support\Fmt::fa($transactions->count()) }} تراکنش
            </span>
        </div>

        <div class="mt-3 space-y-2 transition" wire:loading.class="opacity-50 pointer-events-none" wire:target="loadMore">
            @forelse ($transactions as $tx)
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="tx-{{ $tx->id }}">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $tx->isDeposit()
                            ? 'bg-emerald-50 text-emerald-500 dark:bg-emerald-950/50 dark:text-emerald-400'
                            : 'bg-amber-50 text-amber-500 dark:bg-amber-950/50 dark:text-amber-400' }}">
                        <x-icon name="{{ $tx->isDeposit() ? 'arrow-down-left' : 'arrow-up-right' }}" class="size-4.5" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <span class="truncate text-[11px] font-extrabold">{{ $tx->isDeposit() ? 'واریز فروش' : 'درخواست برداشت' }}</span>
                            @unless ($tx->isDeposit())
                                <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[8.5px] font-black {{ $tx->statusClasses() }}">{{ $tx->statusLabel() }}</span>
                            @endunless
                        </div>
                        <div class="mt-0.5 truncate text-[9.5px] font-semibold text-slate-400">
                            {{ \App\Support\JDate::dateTime($tx->created_at) }}
                            @if ($tx->order_id && $tx->order)
                                · سفارش #{{ \App\Support\Fmt::fa($tx->order->id) }}
                            @endif
                        </div>
                    </div>

                    <div class="shrink-0 text-left">
                        <div class="text-[11.5px] font-black {{ $tx->isDeposit() ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}" dir="ltr">
                            {{ $tx->amountLabel() }}
                        </div>
                        <div class="text-[9px] font-semibold text-slate-400">تومان</div>
                    </div>
                </div>
            @empty
                <div class="mt-4 flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-300 py-12 text-center dark:border-slate-700">
                    <span class="grid size-16 place-items-center rounded-2xl bg-primary-50 text-primary-400 dark:bg-primary-950">
                        <x-icon name="wallet" class="size-8" />
                    </span>
                    <p class="text-sm font-extrabold">هنوز تراکنشی ندارید</p>
                    <p class="max-w-[260px] text-xs leading-6 text-slate-400">
                        وقتی مشتریان سفارش را پرداخت کنند، سهم فروش شما به‌صورت خودکار در همین‌جا واریز می‌شود.
                    </p>
                </div>
            @endforelse

            {{-- lazy-load sentinel --}}
            @if ($txHasMore)
                <div
                    x-data="{}"
                    x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
                    class="flex justify-center py-2"
                    wire:loading.delay.class="opacity-0" wire:loading.delay.remove.class="opacity-100" wire:target="loadMore"
                    aria-hidden="true">
                    <span class="inline-block size-6 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"></span>
                </div>
            @endif
        </div>

        {{-- hint --}}
        <div class="mt-4 flex items-start gap-2.5 rounded-2xl border border-primary-200 bg-primary-50/70 p-3.5 text-[11px] leading-6 text-primary-800 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
            <x-icon name="info" class="mt-0.5 size-4 shrink-0" />
            <span>
                درخواست برداشت ابتدا «در انتظار تأیید» می‌شود و مبلغ همان لحظه از موجودی کسر می‌گردد؛ اگر مدیر درخواست را رد کند، مبلغ به‌صورت خودکار به کیف پول بازمی‌گردد.
            </span>
        </div>
    </div>

    {{-- ═══════════════════ WITHDRAW MODAL ═══════════════════ --}}
    <div x-data="{ wdOpen: $wire.withdrawOpen }" x-on:wallet-close-withdraw.window="wdOpen = false">
        <x-modal-panel open="$wire.withdrawOpen" max-width="sm" labelledby="withdraw-modal-title" in-frame
                       close-js="__ipOpen = false; $wire.closeWithdraw()">

            <x-slot:header>
                <div class="flex items-center justify-between px-5 py-4">
                    <h3 id="withdraw-modal-title" class="flex items-center gap-2 text-sm font-extrabold">
                        <span class="grid size-8 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                            <x-icon name="arrow-up-left" class="size-4" />
                        </span>
                        درخواست برداشت
                    </h3>
                    <button type="button" x-on:click="__ipOpen = false; $wire.closeWithdraw()"
                            class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>
            </x-slot:header>

            <form wire:submit="submitWithdraw" class="space-y-4 px-5 py-5">
                <div class="rounded-2xl bg-primary-50 px-4 py-3 text-center dark:bg-primary-950/40">
                    <p class="text-[10px] font-bold text-primary-600 dark:text-primary-300">موجودی قابل برداشت</p>
                    <p class="mt-0.5 text-lg font-black text-primary-700 dark:text-primary-200">{{ \App\Support\Fmt::fa($wallet->balance) }} <span class="text-[10px] font-bold">تومان</span></p>
                </div>

                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">مبلغ برداشت (تومان) <span class="text-rose-500">*</span></span>
                    <input type="text" inputmode="numeric" dir="ltr" wire:model="withdrawAmount" placeholder="500000"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                        @foreach ([50000 => '۵ هزار', 100000 => '۱۰ هزار', 500000 => '۵۰ هزار'] as $quickAmount => $quickLabel)
                            <button type="button" wire:click="$set('withdrawAmount', '{{ $quickAmount }}')"
                                    class="rounded-full border border-slate-200 px-2.5 py-1 text-[9.5px] font-bold text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300">
                                {{ $quickLabel }} تومان
                            </button>
                        @endforeach
                        <button type="button" wire:click="$set('withdrawAmount', '{{ $wallet->balance }}')"
                                class="rounded-full border border-primary-300 bg-primary-50 px-2.5 py-1 text-[9.5px] font-bold text-primary-600 transition hover:bg-primary-100 dark:border-primary-800 dark:bg-primary-950/40 dark:text-primary-300">
                            کل موجودی
                        </button>
                    </div>
                    @error('withdrawAmount') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">شماره شبا <span class="text-rose-500">*</span></span>
                    <input type="text" dir="ltr" wire:model="withdrawSheba" placeholder="IR062980000000123456789001" maxlength="26"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-left text-xs font-extrabold tracking-wide outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                    @error('withdrawSheba') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </label>

                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                    <span wire:loading wire:target="submitWithdraw" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    ثبت درخواست برداشت
                </button>

                <p class="text-center text-[9.5px] leading-5 text-slate-400">
                    مبلغ درخواستی بلافاصله از موجودی کسر و پس از تأیید مدیر به شبا واریز می‌شود.
                </p>
            </form>
        </x-modal-panel>
    </div>
</div>
