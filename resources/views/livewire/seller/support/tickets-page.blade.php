<div class="ip-fade-up">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 text-white shadow-lg shadow-rose-500/30">
                    <x-icon name="headset" class="size-5" />
                </span>
                <div>
                    <div class="text-sm font-extrabold leading-5">پشتیبانی</div>
                    <div class="text-[11px] text-slate-400">گفتگو با مدیر سایت</div>
                </div>
            </div>
            <x-seller-notif-bell class="ms-auto" />
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">
        {{-- flash toast --}}
        @if (session('seller-support-flash'))
            <div class="mb-3 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-bold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <x-icon name="circle-check" class="size-4" />
                {{ session('seller-support-flash') }}
            </div>
        @endif

        {{-- new ticket button --}}
        <button type="button" wire:click="openCreate"
                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-600 py-3 text-xs font-extrabold text-white shadow-lg shadow-rose-600/25 transition hover:bg-rose-700 active:scale-[.98]">
            <x-icon name="plus" class="size-4" />
            تیکت جدید به پشتیبانی
        </button>

        {{-- tickets list --}}
        <div class="mt-4 space-y-2 transition" wire:loading.class="opacity-50 pointer-events-none" wire:target="loadMore">
            @forelse ($tickets as $ticket)
                <a href="{{ route('seller.support.show', $ticket) }}" wire:navigate wire:key="ticket-{{ $ticket->id }}"
                   class="block rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:border-rose-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-rose-800">
                    <div class="flex items-start justify-between gap-2.5">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="truncate text-[11.5px] font-extrabold">{{ $ticket->subject }}</span>
                                <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[8.5px] font-black {{ $ticket->statusClasses() }}">{{ $ticket->statusLabel() }}</span>
                            </div>
                            <div class="mt-1 text-[9.5px] font-semibold text-slate-400">
                                {{ $ticket->categoryLabel() }} · {{ \App\Support\Fmt::fa($ticket->messages_count) }} پیام · آخرین فعالیت: {{ \App\Support\JDate::date($ticket->last_reply_at ?? $ticket->created_at) }}
                            </div>
                        </div>
                        <x-icon name="chevron-left" class="mt-1 size-3.5 shrink-0 text-slate-300 dark:text-slate-600" />
                    </div>
                </a>
            @empty
                <div class="mt-2 flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-300 py-12 text-center dark:border-slate-700">
                    <span class="grid size-16 place-items-center rounded-2xl bg-rose-50 text-rose-400 dark:bg-rose-950">
                        <x-icon name="headset" class="size-8" />
                    </span>
                    <p class="text-sm font-extrabold">هنوز تیکتی ندارید</p>
                    <p class="max-w-[280px] text-xs leading-6 text-slate-400">
                        برای هر سؤال یا مشکلی درباره سفارش‌ها، پرداخت یا فروشگاه، دکمه «تیکت جدید» را بزنید.
                    </p>
                </div>
            @endforelse

            {{-- lazy-load sentinel --}}
            @if ($hasMore)
                <div
                    x-data="{}"
                    x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
                    class="flex justify-center py-2"
                    wire:loading.delay.class="opacity-0" wire:loading.delay.remove.class="opacity-100" wire:target="loadMore"
                    aria-hidden="true">
                    <span class="inline-block size-6 animate-spin rounded-full border-2 border-rose-500 border-t-transparent"></span>
                </div>
            @endif
        </div>

        {{-- hint --}}
        <div class="mt-4 flex items-start gap-2.5 rounded-2xl border border-primary-200 bg-primary-50/70 p-3.5 text-[11px] leading-6 text-primary-800 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
            <x-icon name="info" class="mt-0.5 size-4 shrink-0" />
            <span>
                پاسخ پشتیبانی در همین صفحه زیر هر تیکت نمایش داده می‌شود و با نوتیفیکیشن هم به شما اطلاع داده می‌شود.
            </span>
        </div>
    </div>

    {{-- ═══════════════════ CREATE TICKET MODAL ═══════════════════ --}}
    <x-modal-panel open="$wire.createOpen" max-width="sm" labelledby="seller-ticket-create-title" in-frame
                   close-js="__ipOpen = false; $wire.closeCreate()">
        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 id="seller-ticket-create-title" class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-rose-600/10 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                        <x-icon name="headset" class="size-4" />
                    </span>
                    تیکت جدید به پشتیبانی
                </h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeCreate()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <form wire:submit="submitTicket" class="space-y-4 px-5 py-5">
            <label class="block">
                <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">موضوع تیکت <span class="text-rose-500">*</span></span>
                <input type="text" wire:model="subject" maxlength="120"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-bold outline-none transition focus:border-rose-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800"
                       placeholder="مثلاً: مشکل در واریز مبلغ سفارش #۱۲">
                @error('subject') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </label>

            <div>
                <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">دسته‌بندی <span class="text-rose-500">*</span></span>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($categories as $key => $label)
                        <button type="button" wire:click="$set('category', '{{ $key }}')"
                                class="rounded-full border px-3 py-1.5 text-[10px] font-bold transition {{ $category === $key
                                    ? 'border-rose-400 bg-rose-50 text-rose-600 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300'
                                    : 'border-slate-200 text-slate-500 hover:border-rose-300 dark:border-slate-700 dark:text-slate-300' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                @error('category') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <label class="block">
                <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">متن تیکت <span class="text-rose-500">*</span></span>
                <textarea wire:model="body" rows="5"
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold outline-none transition focus:border-rose-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800"
                          placeholder="مشکل یا سؤال خود را کامل توضیح دهید…"></textarea>
                @error('body') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </label>

            <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-xs font-bold text-white shadow-md shadow-rose-600/25 transition hover:bg-rose-700 disabled:opacity-50">
                <span wire:loading wire:target="submitTicket" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                ارسال تیکت
            </button>
        </form>
    </x-modal-panel>
</div>
