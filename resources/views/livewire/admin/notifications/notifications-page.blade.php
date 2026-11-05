<div class="ip-fade-up space-y-5">

    {{-- ── header ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/25">
                    <x-icon name="bell" class="size-5" />
                </span>
                <div>
                    <h1 class="text-base font-black">نوتیفیکیشن‌ها</h1>
                    <p class="mt-0.5 text-[11px] leading-5 text-slate-400">
                        همهٔ اتفاق‌های سایت — سفارش‌ها، پرداخت‌ها، فروشندگان و امنیت — همین‌جا جمع می‌شوند.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="$refresh"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-[11px] font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                    <x-icon name="rotate" class="size-3.5" />
                    بروزرسانی
                </button>
                <button type="button" wire:click="markAllRead"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-primary-200 bg-primary-50 px-3.5 py-2 text-[11px] font-bold text-primary-700 transition hover:bg-primary-100 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
                    <x-icon name="check" class="size-3.5" />
                    خواندن همه
                </button>
                <button type="button" wire:click="clearRead"
                        onclick="if(!confirm('همهٔ اعلان‌های خوانده‌شده حذف شوند؟')) return false"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-[11px] font-bold text-rose-700 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
                    <x-icon name="trash" class="size-3.5" />
                    پاک‌سازی خوانده‌شده‌ها
                </button>
            </div>
        </div>
    </div>

    {{-- ── filter tabs ── --}}
    <div class="flex items-center gap-2">
        <button type="button" wire:click="$set('filter', 'all')"
                class="rounded-xl px-4 py-2 text-[12px] font-bold transition {{ $filter === 'all'
                    ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                    : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
            همه
        </button>
        <button type="button" wire:click="$set('filter', 'unread')"
                class="rounded-xl px-4 py-2 text-[12px] font-bold transition {{ $filter === 'unread'
                    ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                    : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
            خوانده‌نشده
            @if ($unreadCount > 0)
                <span class="ms-1 rounded-md bg-rose-500 px-1.5 py-0.5 text-[10px] font-black text-white">{{ \App\Support\Fmt::fa($unreadCount) }}</span>
            @endif
        </button>
    </div>

    {{-- ── list ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @forelse ($items as $item)
            <div class="flex gap-3.5 border-b border-slate-100 px-4 py-4 transition last:border-0 hover:bg-slate-50 sm:px-5 dark:border-slate-800/70 dark:hover:bg-slate-800/40 {{ $item->is_read ? '' : 'bg-primary-50/40 dark:bg-primary-950/20' }}"
                 wire:key="page-notif-{{ $item->id }}">
                <span class="mt-0.5 grid size-10 shrink-0 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                    <x-icon name="{{ $item->icon }}" class="size-4.5" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-[13px] font-extrabold leading-6">{{ $item->title }}</p>
                        @unless ($item->is_read)
                            <span class="rounded-md bg-rose-100 px-1.5 py-0.5 text-[9px] font-black text-rose-700 dark:bg-rose-950 dark:text-rose-300">جدید</span>
                        @endunless
                    </div>
                    @if ($item->body !== '')
                        <p class="mt-1 text-[12px] leading-6 text-slate-500 dark:text-slate-400">{{ $item->body }}</p>
                    @endif
                    <div class="mt-1.5 flex flex-wrap items-center gap-3">
                        <span class="flex items-center gap-1 text-[11px] text-slate-400">
                            <x-icon name="clock" class="size-3" />
                            {{ \App\Support\JDate::ago($item->created_at) }}
                        </span>
                        <span class="text-[10px] text-slate-300 dark:text-slate-600">{{ \App\Support\JDate::dateTime($item->created_at) }}</span>
                        @if ($item->link)
                            {{-- open() marks read first, then navigates (client v0.17.15:
                                 the old wire:navigate + wire:click anchor raced — the
                                 navigation cancelled markRead so items stayed unread) --}}
                            <button type="button" wire:click="openNotification({{ $item->id }})"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-primary-600 transition hover:text-primary-700 dark:text-primary-400">
                                مشاهده
                                <x-icon name="arrow-left" class="size-3" />
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <x-icon name="inbox" class="mx-auto size-10 text-slate-300 dark:text-slate-600" />
                <p class="mt-4 text-sm font-bold text-slate-400">
                    {{ $filter === 'unread' ? 'همهٔ اعلان‌ها خوانده شده‌اند ✓' : 'هنوز اعلانی ثبت نشده است' }}
                </p>
            </div>
        @endforelse
    </div>

    @if ($items->hasPages())
        <div class="px-2">
            {{ $items->links() }}
        </div>
    @endif
</div>
