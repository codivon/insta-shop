{{-- ═══════════════════════════════════════════════════════════
  Customer — ONE support ticket as a CHAT page (focused shell:
  no page scroll). Header + composer are pinned; ONLY the
  message list scrolls and it always rests on the newest
  message (ipChatThread).
═════════════════════════════════════════════════════════════ --}}
<div class="ip-fade-up flex h-full min-h-0 flex-1 flex-col" x-data="ipChatThread">
    {{-- ───────── Compact pinned header ───────── --}}
    <div class="shrink-0 border-b border-slate-200/80 bg-slate-50/95 px-3 py-2 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2">
            <a href="{{ route('shop.support') }}" wire:navigate
               class="grid size-9 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-rose-300 hover:text-rose-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
               aria-label="بازگشت به پشتیبانی">
                <x-icon name="arrow-right" class="size-4.5" />
            </a>
            <div class="min-w-0 flex-1 leading-tight">
                <div class="truncate text-[13px] font-extrabold">{{ $ticket->subject }}</div>
                <div class="truncate text-[10px] text-slate-400">{{ $ticket->categoryLabel() }}</div>
            </div>
            <span class="shrink-0 rounded-full px-2 py-1 text-[9px] font-black {{ $ticket->statusClasses() }}">{{ $ticket->statusLabel() }}</span>
        </div>
    </div>

    {{-- ───────── Messages — the ONLY scroller ───────── --}}
    <div x-ref="msgs" class="scroll-slim min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain px-3 py-4">
        @forelse ($ticket->messages as $message)
            <div class="flex {{ $message->isStaff() ? 'justify-start' : 'justify-end' }}" wire:key="msg-{{ $message->id }}">
                <div class="max-w-[85%] rounded-2xl border p-3 {{ $message->isStaff()
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
    </div>

    {{-- ───────── Composer — pinned to the bottom, one row ───────── --}}
    <div class="shrink-0 border-t border-slate-200 bg-white px-3 py-2.5 pb-[max(0.625rem,env(safe-area-inset-bottom))] shadow-[0_-4px_16px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900">
        @if ($ticket->status !== \App\Models\SupportTicket::STATUS_CLOSED)
            <label class="block">
                <span class="sr-only">پاسخ شما</span>
                <textarea wire:model="replyBody" rows="1" x-on:input="grow" x-init="grow"
                          class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold leading-5 outline-none transition focus:border-rose-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800"
                          placeholder="پیام خود را بنویسید…"></textarea>
                @error('replyBody') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </label>

            <div class="mt-2 flex items-center gap-2">
                <button type="button" wire:click="submitReply" wire:loading.attr="disabled" wire:target="submitReply"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-rose-600/25 transition hover:bg-rose-700 disabled:opacity-50">
                    <span wire:loading wire:target="submitReply" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    ارسال پاسخ
                </button>
                <button type="button" wire:click="closeTicket" wire:loading.attr="disabled" wire:target="closeTicket"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 disabled:opacity-50 dark:border-slate-700 dark:hover:bg-slate-800"
                        title="بستن تیکت">
                    بستن
                </button>
            </div>
        @else
            <p class="text-[10.5px] font-bold leading-6 text-slate-400">این تیکت بسته شده است. اگر مشکل ادامه دارد، از صفحه پشتیبانی تیکت جدیدی ثبت کنید.</p>
        @endif
    </div>
</div>
