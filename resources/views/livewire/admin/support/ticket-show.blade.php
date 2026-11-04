{{-- ═══════════════════════════════════════════════════════════
  Admin — ONE ticket as a CHAT page (layout chat mode: no page
  scroll). Header + composer are pinned; ONLY the message list
  scrolls and it always rests on the newest message (ipChatThread).
═════════════════════════════════════════════════════════════ --}}
<div class="ip-fade-up flex h-full min-h-0 flex-1 flex-col" x-data="ipChatThread">
    {{-- ───────── Compact pinned header ───────── --}}
    <div class="shrink-0 border-b border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.tickets') }}" wire:navigate
               class="grid size-9 shrink-0 place-items-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300"
               aria-label="بازگشت به تیکت‌ها">
                <x-icon name="arrow-right" class="size-4.5" />
            </a>
            <div class="min-w-0 flex-1 leading-tight">
                <div class="truncate text-[13px] font-extrabold">{{ $ticket->subject }}</div>
                <div class="flex items-center gap-1 text-[10px] text-slate-400">
                    <x-icon name="{{ $ticket->ticketable_type === \App\Models\Seller::class ? 'store' : 'user' }}" class="size-3 shrink-0" />
                    <span class="truncate">{{ $this->authorName($ticket) }} · {{ $ticket->categoryLabel() }}</span>
                </div>
            </div>
            <span class="shrink-0 rounded-full px-2 py-1 text-[9px] font-black {{ $ticket->statusClasses() }}">{{ $ticket->statusLabel() }}</span>
        </div>
    </div>

    {{-- ───────── Messages — the ONLY scroller ───────── --}}
    <div x-ref="msgs" class="scroll-slim min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain bg-slate-50/60 px-3 py-4 dark:bg-slate-950/40">
        @forelse ($ticket->messages as $message)
            <div class="flex {{ $message->isStaff() ? 'justify-start' : 'justify-end' }}" wire:key="msg-{{ $message->id }}">
                <div class="max-w-[85%] rounded-2xl border p-3 {{ $message->isStaff()
                    ? 'border-primary-200 bg-primary-50 dark:border-primary-900 dark:bg-primary-950/40'
                    : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800' }}">
                    <div class="mb-1 flex items-center gap-2 text-[9.5px] font-black {{ $message->isStaff() ? 'text-primary-600 dark:text-primary-300' : 'text-slate-400' }}">
                        <x-icon name="{{ $message->isStaff() ? 'headset' : 'user' }}" class="size-3" />
                        {{ $message->authorLabel() }}
                        <span class="font-semibold">· {{ \App\Support\JDate::dateTime($message->created_at) }}</span>
                    </div>
                    <p class="whitespace-pre-wrap text-[11.5px] font-semibold leading-6">{{ $message->body }}</p>
                </div>
            </div>
        @empty
            <p class="py-6 text-center text-[11px] font-bold text-slate-400">پیامی وجود ندارد. اولین پاسخ را بنویسید.</p>
        @endforelse
    </div>

    {{-- ───────── Composer — pinned to the bottom ───────── --}}
    <div class="shrink-0 border-t border-slate-200 bg-white px-3 py-2.5 pb-[max(0.625rem,env(safe-area-inset-bottom))] dark:border-slate-800 dark:bg-slate-900">
        @if ($ticket->status !== \App\Models\SupportTicket::STATUS_CLOSED)
            <label class="block">
                <span class="sr-only">پاسخ پشتیبانی</span>
                <textarea wire:model="replyBody" rows="1" x-on:input="grow" x-init="grow"
                          class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-[11.5px] font-semibold leading-5 outline-none transition focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800"
                          placeholder="پاسخ خود را برای {{ $ticket->authorLabel() }} بنویسید…"></textarea>
                @error('replyBody') <span class="mt-1 block text-[10px] font-bold text-rose-500">{{ $message }}</span> @enderror
            </label>

            <div class="mt-2 flex items-center gap-2">
                <button type="button" wire:click="submitReply" wire:loading.attr="disabled" wire:target="submitReply"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-[11px] font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                    <span wire:loading wire:target="submitReply" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    ارسال پاسخ
                </button>

                <button type="button" wire:click="closeTicket" wire:loading.attr="disabled" wire:target="closeTicket"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 px-3 py-2.5 text-[11px] font-bold text-rose-500 transition hover:bg-rose-50 disabled:opacity-50 dark:border-rose-900 dark:hover:bg-rose-950/40"
                        title="بستن تیکت">
                    <x-icon name="circle-check" class="size-3.5" />
                    بستن
                </button>

                @if (session('admin-ticket-flash'))
                    <span class="text-[10.5px] font-bold text-emerald-600 dark:text-emerald-400">{{ session('admin-ticket-flash') }}</span>
                @endif
            </div>
        @else
            <div class="flex items-center gap-2">
                <p class="min-w-0 flex-1 text-[10.5px] font-bold leading-5 text-slate-400">این تیکت بسته شده است؛ برای ادامه گفتگو آن را بازگشایی کنید.</p>
                <button type="button" wire:click="reopenTicket" wire:loading.attr="disabled" wire:target="reopenTicket"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-amber-200 px-3 py-2.5 text-[11px] font-bold text-amber-600 transition hover:bg-amber-50 disabled:opacity-50 dark:border-amber-900 dark:hover:bg-amber-950/40">
                    <x-icon name="rotate" class="size-3.5" />
                    بازگشایی
                </button>
            </div>
        @endif
    </div>
</div>
