<div class="ip-fade-up space-y-5">
    {{-- ═══════════ back + title ═══════════ --}}
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
            <a href="{{ route('admin.tickets') }}" wire:navigate
               class="mb-1 inline-flex items-center gap-1 text-[10.5px] font-bold text-slate-400 transition hover:text-primary-600">
                <x-icon name="chevron-right" class="size-3.5" />
                بازگشت به تیکت‌ها
            </a>
            <h1 class="truncate text-base font-extrabold">{{ $ticket->subject }}</h1>
        </div>
        <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-black {{ $ticket->statusClasses() }}">{{ $ticket->statusLabel() }}</span>
    </div>

    {{-- ═══════════ meta card ═══════════ --}}
    <div class="grid gap-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-2">
        <div class="flex items-center justify-between gap-2 text-[11px]">
            <span class="font-bold text-slate-400">ارسال‌کننده</span>
            <span class="inline-flex items-center gap-1 font-extrabold">
                <x-icon name="{{ $ticket->ticketable_type === \App\Models\Seller::class ? 'store' : 'user' }}" class="size-3.5 text-slate-400" />
                {{ $this->authorName($ticket) }} ({{ $ticket->authorLabel() }})
            </span>
        </div>
        <div class="flex items-center justify-between gap-2 text-[11px]">
            <span class="font-bold text-slate-400">دسته‌بندی</span>
            <span class="font-extrabold">{{ $ticket->categoryLabel() }}</span>
        </div>
        <div class="flex items-center justify-between gap-2 text-[11px]">
            <span class="font-bold text-slate-400">تاریخ ثبت</span>
            <span class="font-extrabold">{{ \App\Support\JDate::dateTime($ticket->created_at) }}</span>
        </div>
        <div class="flex items-center justify-between gap-2 text-[11px]">
            <span class="font-bold text-slate-400">آخرین فعالیت</span>
            <span class="font-extrabold">{{ \App\Support\JDate::dateTime($ticket->last_reply_at ?? $ticket->created_at) }}</span>
        </div>
    </div>

    {{-- ═══════════ conversation ═══════════ --}}
    <div class="scroll-slim max-h-[52vh] space-y-3 overflow-y-auto rounded-2xl border border-slate-200 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-900/60">
        @forelse ($ticket->messages as $message)
            <div class="flex {{ $message->isStaff() ? 'justify-start' : 'justify-end' }}" wire:key="msg-{{ $message->id }}">
                <div class="max-w-[85%] rounded-2xl border p-3.5 {{ $message->isStaff()
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
            <p class="py-6 text-center text-[11px] font-bold text-slate-400">پیامی وجود ندارد.</p>
        @endforelse
    </div>

    {{-- ═══════════ reply box ═══════════ --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <label class="block">
            <span class="mb-1.5 block text-[10px] font-bold text-slate-400">پاسخ پشتیبانی</span>
            <textarea wire:model="replyBody" rows="4"
                      class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-[11.5px] font-semibold shadow-sm focus:border-primary-400 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                      placeholder="پاسخ خود را برای {{ $ticket->authorLabel() }} بنویسید…"></textarea>
            @error('replyBody') <span class="mt-1 block text-[10px] font-bold text-rose-500">{{ $message }}</span> @enderror
        </label>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <button type="button" wire:click="submitReply" wire:loading.attr="disabled" wire:target="submitReply"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-[11px] font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                <span wire:loading wire:target="submitReply" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                ارسال پاسخ
            </button>

            @if ($ticket->status !== \App\Models\SupportTicket::STATUS_CLOSED)
                <button type="button" wire:click="closeTicket" wire:loading.attr="disabled" wire:target="closeTicket"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 px-4 py-2.5 text-[11px] font-bold text-rose-500 transition hover:bg-rose-50 disabled:opacity-50 dark:border-rose-900 dark:hover:bg-rose-950/40">
                    <x-icon name="circle-check" class="size-3.5" />
                    بستن تیکت
                </button>
            @else
                <button type="button" wire:click="reopenTicket" wire:loading.attr="disabled" wire:target="reopenTicket"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-amber-200 px-4 py-2.5 text-[11px] font-bold text-amber-600 transition hover:bg-amber-50 disabled:opacity-50 dark:border-amber-900 dark:hover:bg-amber-950/40">
                    <x-icon name="rotate" class="size-3.5" />
                    بازگشایی تیکت
                </button>
            @endif

            @if (session('admin-ticket-flash'))
                <span class="text-[10.5px] font-bold text-emerald-600 dark:text-emerald-400">{{ session('admin-ticket-flash') }}</span>
            @endif
        </div>
    </div>
</div>
