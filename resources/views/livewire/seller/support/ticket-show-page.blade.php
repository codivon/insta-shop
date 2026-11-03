<div class="ip-fade-up flex h-full min-h-0 flex-col">
    {{-- ───────── Inner scroller: header + conversation ─────────
          The page owns its own scroller (focused shell, tab bar hidden)
          so the reply composer below stays GLUED to the bottom —
          exactly like a chat app; overscroll can never drag it up. --}}
    <div class="scroll-slim min-h-0 flex-1 overflow-y-auto overscroll-contain">
        {{-- ───────── Sticky page header ───────── --}}
        <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
            <div class="flex items-center gap-2.5 px-4 py-3">
                <a href="{{ route('seller.support') }}" wire:navigate
                   class="grid size-10 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-rose-300 hover:text-rose-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                   aria-label="بازگشت به پشتیبانی">
                    <x-icon name="arrow-right" class="size-5" />
                </a>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-extrabold leading-5">{{ $ticket->subject }}</div>
                    <div class="text-[11px] text-slate-400">{{ $ticket->categoryLabel() }}</div>
                </div>
                <span class="shrink-0 rounded-full px-2 py-1 text-[9px] font-black {{ $ticket->statusClasses() }}">{{ $ticket->statusLabel() }}</span>
            </div>
        </div>

        {{-- ── conversation ── --}}
        <div class="space-y-3 px-4 pt-4 pb-4">
            @forelse ($ticket->messages as $message)
                <div class="flex {{ $message->isStaff() ? 'justify-start' : 'justify-end' }}" wire:key="msg-{{ $message->id }}">
                    <div class="max-w-[85%] rounded-2xl border p-3.5 {{ $message->isStaff()
                        ? 'border-primary-200 bg-primary-50 dark:border-primary-900 dark:bg-primary-950/40'
                        : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800' }}">
                        <div class="mb-1 flex items-center gap-2 text-[9px] font-black {{ $message->isStaff() ? 'text-primary-600 dark:text-primary-300' : 'text-slate-400' }}">
                            <x-icon name="{{ $message->isStaff() ? 'headset' : 'user' }}" class="size-3" />
                            {{ $message->isStaff() ? 'پشتیبانی' : 'شما' }}
                            <span class="font-semibold">· {{ \App\Support\JDate::dateTime($message->created_at) }}</span>
                        </div>
                        <p class="whitespace-pre-wrap text-[11.5px] font-semibold leading-6">{{ $message->body }}</p>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-[11px] font-bold text-slate-400">پیامی وجود ندارد.</p>
            @endforelse

            <a href="{{ route('seller.support') }}" wire:navigate
               class="mt-2 flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white py-3 text-xs font-bold text-slate-500 transition hover:border-rose-300 hover:text-rose-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                <x-icon name="headset" class="size-4" />
                بازگشت به لیست تیکت‌ها
            </a>
        </div>
    </div>

    {{-- ───────── Reply composer — pinned to the bottom ───────── --}}
    <div class="shrink-0 border-t border-slate-200 bg-white px-4 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-[0_-4px_16px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900">
        @if ($ticket->status !== \App\Models\SupportTicket::STATUS_CLOSED)
            <label class="block">
                <span class="sr-only">پاسخ شما</span>
                <textarea wire:model="replyBody" rows="3"
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold outline-none transition focus:border-rose-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800"
                          placeholder="پیام خود را بنویسید…"></textarea>
                @error('replyBody') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </label>

            <div class="mt-2.5 flex gap-2">
                <button type="button" wire:click="submitReply" wire:loading.attr="disabled" wire:target="submitReply"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-xs font-bold text-white shadow-md shadow-rose-600/25 transition hover:bg-rose-700 disabled:opacity-50">
                    <span wire:loading wire:target="submitReply" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    ارسال پاسخ
                </button>
                <button type="button" wire:click="closeTicket" wire:loading.attr="disabled" wire:target="closeTicket"
                        class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-bold text-slate-500 transition hover:bg-slate-100 disabled:opacity-50 dark:border-slate-700 dark:hover:bg-slate-800">
                    بستن تیکت
                </button>
            </div>
        @else
            <p class="text-[11px] font-bold leading-6 text-slate-400">این تیکت بسته شده است. اگر مشکل ادامه دارد، از صفحه پشتیبانی تیکت جدیدی ثبت کنید.</p>
        @endif
    </div>
</div>
