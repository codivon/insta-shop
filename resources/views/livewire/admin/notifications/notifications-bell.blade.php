{{-- ═══════════════════════════════════════════════════════════
  Admin topbar NOTIFICATIONS BELL — unread badge, dropdown list,
  mark-all-read and one-click Web Push registration for this
  device (works even when the browser is closed).
  NOTE: ONE root element — the scripts live inside it so the
  Livewire multiple-root check never trips.
════════════════════════════════════════════════════════════ --}}
<div class="relative" wire:poll.30s>
    <button type="button" wire:click="toggle"
            class="relative grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
            aria-label="اعلان‌ها">
        <x-icon name="bell" class="size-5" />
        @if ($this->unreadCount > 0)
            <span class="absolute -top-0.5 -end-0.5 grid min-w-[18px] place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-black text-white ring-2 ring-white dark:ring-slate-900">
                {{ \App\Support\Fmt::fa($this->unreadCount) }}
            </span>
        @endif
    </button>

    @if ($open)
        <div class="absolute left-0 top-12 z-50 w-[330px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900"
             wire:key="bell-open">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                <p class="text-[13px] font-extrabold">اعلان‌ها</p>
                <div class="flex items-center gap-1">
                    <button type="button" wire:click="markAllRead"
                            class="text-[11px] font-bold text-primary-600 transition hover:text-primary-700 dark:text-primary-400">
                        علامت‌گذاری همه به‌عنوان خوانده‌شده
                    </button>
                    <button type="button" wire:click="toggle" aria-label="بستن"
                            class="grid size-7 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>
            </div>

            <div class="scroll-slim max-h-[330px] overflow-y-auto">
                @forelse ($this->items as $item)
                    <div class="flex gap-3 border-b border-slate-50 px-4 py-3 transition last:border-0 hover:bg-slate-50 dark:border-slate-800/60 dark:hover:bg-slate-800/50 {{ $item->is_read ? '' : 'bg-primary-50/40 dark:bg-primary-950/20' }}"
                         wire:key="notif-{{ $item->id }}">
                        <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-lg bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                            <x-icon name="{{ $item->icon }}" class="size-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12.5px] font-bold leading-5">
                                {{ $item->title }}
                                @unless ($item->is_read)
                                    <span class="ms-1 inline-block size-1.5 rounded-full bg-primary-500 align-middle"></span>
                                @endunless
                            </p>
                            @if ($item->body !== '')
                                <p class="mt-0.5 text-[11px] leading-5 text-slate-500 dark:text-slate-400">{{ $item->body }}</p>
                            @endif
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-[10px] text-slate-400">{{ \App\Support\JDate::ago($item->created_at) }}</span>
                                @if ($item->link)
                                    <a href="{{ $item->link }}" wire:navigate wire:click="markRead({{ $item->id }})"
                                       class="text-[10px] font-bold text-primary-600 hover:underline dark:text-primary-400">مشاهده</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center">
                        <x-icon name="inbox" class="mx-auto size-8 text-slate-300 dark:text-slate-600" />
                        <p class="mt-3 text-xs font-semibold text-slate-400">اعلانی وجود ندارد</p>
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 p-3 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.notifications') }}" wire:navigate wire:click="toggle"
                       class="flex-1 rounded-xl bg-slate-100 px-3 py-2 text-center text-[11px] font-extrabold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        مشاهده همه اعلان‌ها
                    </a>
                    @if ($pushState !== 'subscribed' && $pushState !== 'unsupported')
                        <button type="button" x-data @click="window.__ipEnablePush && window.__ipEnablePush()"
                                class="flex-1 rounded-xl bg-primary-600 px-3 py-2 text-center text-[11px] font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                            فعال‌سازی نوتیف موبایل/سیستم
                        </button>
                    @elseif ($pushState === 'subscribed')
                        <span class="flex-1 rounded-xl bg-emerald-50 px-3 py-2 text-center text-[11px] font-extrabold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                            نوتیفیکیشن دستگاه فعال است ✓
                        </span>
                    @endif
                </div>
                @if ($pushState === 'denied')
                    <p class="mt-2 text-[10px] leading-4 text-amber-600 dark:text-amber-400">
                        دسترسی نوتیفیکیشن در مرورگر شما مسدود شده — از تنظیمات مرورگر برای سایت اجازه دهید.
                    </p>
                @endif
            </div>
        </div>
        <button type="button" class="fixed inset-0 z-40 cursor-default" wire:click="toggle" aria-label="بستن" tabindex="-1"></button>
    @endif

</div>
