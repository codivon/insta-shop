<div class="ip-fade-up space-y-5">
    {{-- ═══════════ header ═══════════ --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-base font-extrabold">
                <span class="grid size-9 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-300">
                    <x-icon name="headset" class="size-4.5" />
                </span>
                تیکت پشتیبانی
            </h1>
            <p class="mt-1 text-[11px] text-slate-400">گفتگوهای فروشندگان و مشتریان با تیم پشتیبانی</p>
        </div>
    </div>

    {{-- ═══════════ status tabs (optimistic pick) ═══════════ --}}
    <div class="flex flex-wrap gap-1.5" role="group" aria-label="فیلتر وضعیت تیکت">
        @foreach ([
            'open'     => ['در انتظار بررسی', $counts['open']],
            'answered' => ['پاسخ داده شده', $counts['answered']],
            'closed'   => ['بسته شده', $counts['closed']],
            'all'      => ['همه', $counts['open'] + $counts['answered'] + $counts['closed']],
        ] as $key => [$label, $count])
            <button type="button" wire:click="$set('statusFilter', '{{ $key }}')"
                    data-ip-pick data-ip-group="ticket-status"
                    data-ip-pick-class="bg-primary-600 text-white shadow-md shadow-primary-600/25"
                    class="flex items-center gap-1.5 rounded-xl border px-3 py-2 text-[11px] font-bold transition {{ $statusFilter === $key
                        ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25 border-primary-600'
                        : 'border-slate-200 text-slate-500 hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300' }}">
                {{ $label }}
                <span class="rounded-md px-1.5 py-0.5 text-[9.5px] font-black {{ $statusFilter === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300' }}">
                    {{ \App\Support\Fmt::fa($count) }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- ═══════════ tickets list ═══════════ --}}
    <div class="space-y-2" wire:loading.class="opacity-50 pointer-events-none" wire:target="loadMore">
        @forelse ($tickets as $ticket)
            <a href="{{ route('admin.tickets.show', $ticket) }}" wire:navigate wire:key="ticket-{{ $ticket->id }}"
               class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-primary-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="truncate text-xs font-extrabold">{{ $ticket->subject }}</span>
                            <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[8.5px] font-black {{ $ticket->statusClasses() }}">{{ $ticket->statusLabel() }}</span>
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[10px] font-semibold text-slate-400">
                            <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-1.5 py-0.5 font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                <x-icon name="{{ $ticket->ticketable_type === \App\Models\Seller::class ? 'store' : 'user' }}" class="size-3" />
                                {{ $this->authorName($ticket) }} ({{ $ticket->authorLabel() }})
                            </span>
                            <span>· {{ $ticket->categoryLabel() }}</span>
                            <span>· آخرین فعالیت: {{ $this->activityLabel($ticket) }}</span>
                        </div>
                    </div>
                    <x-icon name="chevron-left" class="mt-1 size-4 shrink-0 text-slate-300 dark:text-slate-600" />
                </div>
            </a>
        @empty
            <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-300 py-14 text-center dark:border-slate-700">
                <span class="grid size-14 place-items-center rounded-2xl bg-primary-50 text-primary-400 dark:bg-primary-950">
                    <x-icon name="headset" class="size-7" />
                </span>
                <p class="text-sm font-extrabold">تیکتی با این وضعیت وجود ندارد</p>
                <p class="max-w-[320px] text-xs leading-6 text-slate-400">وقتی فروشندگان یا مشتریان تیکت ثبت کنند، همین‌جا نمایش داده می‌شود.</p>
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
                <span class="inline-block size-6 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"></span>
            </div>
        @endif
    </div>
</div>
