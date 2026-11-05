<div class="ip-fade-up">
    {{-- ───────── Sticky header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <span class="relative grid size-10 place-items-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                <x-icon name="bell" class="size-5" />
                @if ($unreadCount > 0)
                    <span class="absolute -top-1 -end-1 grid min-w-[18px] place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-black text-white ring-2 ring-white dark:ring-slate-950">
                        {{ \App\Support\Fmt::fa($unreadCount) }}
                    </span>
                @endif
            </span>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-extrabold leading-5">نوتیفیکیشن‌ها</div>
                <div class="text-[11px] text-slate-400">اعلان‌های فروشگاه شما — سفارش‌ها، کیف پول و محصولات</div>
            </div>
            @if ($items->isNotEmpty())
                <button type="button" wire:click="markAllRead"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[10px] font-bold text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                    خواندن همه
                </button>
            @endif
        </div>
    </div>

    <div class="px-4 pt-4">
        {{-- ── Web Push device card ── --}}
        <div class="rounded-2xl border border-primary-200 bg-gradient-to-br from-primary-50 to-white p-4 shadow-sm dark:border-primary-900/60 dark:from-primary-950/40 dark:to-slate-900">
            <div class="flex items-start gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                    <x-icon name="smartphone" class="size-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-extrabold">نوتیفیکیشن روی گوشی / سیستم</p>
                    <p class="mt-0.5 text-[10.5px] leading-5 text-slate-500 dark:text-slate-400">
                        حتی وقتی مرورگر بسته است، اعلان سفارش جدید، پرداخت و واریز کیف پول را روی گوشی یا دسکتاپ دریافت کنید.
                    </p>
                </div>
            </div>
            <div class="mt-3">
                @if ($pushState === 'subscribed')
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-2 text-[11px] font-extrabold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                        <x-icon name="circle-check" class="size-4" />
                        نوتیفیکیشن دستگاه فعال است ✓
                    </span>
                @elseif ($pushState === 'unsupported')
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-[11px] font-extrabold text-slate-500 dark:bg-slate-800">
                        مرورگر شما از نوتیفیکیشن پشتیبانی نمی‌کند
                    </span>
                @elseif ($pushState === 'denied')
                    <p class="text-[10.5px] leading-5 text-amber-600 dark:text-amber-400">
                        دسترسی نوتیفیکیشن در مرورگر مسدود شده — از تنظیمات مرورگر برای این سایت اجازه دهید.
                    </p>
                @else
                    <button type="button" x-data @click="window.__ipEnablePush && window.__ipEnablePush()"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2.5 text-[11px] font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                        <x-icon name="bell" class="size-4" />
                        فعال‌سازی نوتیفیکیشن دستگاه
                    </button>
                @endif
            </div>
        </div>

        {{-- ── filter chips ── --}}
        <div class="mt-4 flex gap-2">
            @foreach ([['all', 'همه'], ['unread', 'خوانده‌نشده']] as [$value, $label])
                <button type="button" wire:click="$set('filter', '{{ $value }}')"
                        class="rounded-full px-4 py-2 text-[11px] font-bold transition {{ $filter === $value
                                ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                : 'border border-slate-200 bg-white text-slate-500 hover:border-primary-300 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400' }}">
                    {{ $label }}
                </button>
            @endforeach
            @if ($filter === 'all' && $items->isNotEmpty())
                <button type="button" wire:click="clearRead" wire:confirm="همه اعلان‌های خوانده‌شده حذف شوند؟"
                        class="ms-auto rounded-full px-3 py-2 text-[10px] font-bold text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-950/40">
                    پاک‌سازی خوانده‌شده‌ها
                </button>
            @endif
        </div>

        {{-- ── list ── --}}
        <div class="mt-3 space-y-2 pb-6">
            @forelse ($items as $item)
                <div class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:border-primary-200 dark:border-slate-800 dark:bg-slate-900 {{ $item->is_read ? '' : 'border-primary-300 bg-primary-50/30 dark:border-primary-800/60 dark:bg-primary-950/20' }}"
                     wire:key="seller-notif-{{ $item->id }}">
                    <span class="mt-0.5 grid size-9 shrink-0 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="{{ $item->icon }}" class="size-4.5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-extrabold leading-5">
                            {{ $item->title }}
                            @unless ($item->is_read)
                                <span class="ms-1 inline-block size-1.5 rounded-full bg-primary-500 align-middle"></span>
                            @endunless
                        </p>
                        @if ($item->body !== '')
                            <p class="mt-0.5 text-[11px] leading-5 text-slate-500 dark:text-slate-400">{{ $item->body }}</p>
                        @endif
                        <div class="mt-1.5 flex items-center gap-3">
                            <span class="text-[10px] text-slate-400">{{ \App\Support\JDate::ago($item->created_at) }}</span>
                            @if ($item->link)
                                {{-- open() marks read first, then navigates (client v0.17.15:
                                     the old wire:navigate + wire:click anchor raced — the
                                     navigation cancelled markRead so items stayed unread) --}}
                                <button type="button" wire:click="openNotification({{ $item->id }})"
                                        class="text-[10px] font-bold text-primary-600 hover:underline dark:text-primary-400">مشاهده</button>
                            @endif
                            @unless ($item->is_read)
                                <button type="button" wire:click="markRead({{ $item->id }})"
                                        class="text-[10px] font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">خواندم</button>
                            @endunless
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                    <span class="mx-auto grid size-16 place-items-center rounded-full bg-slate-100 dark:bg-slate-800">
                        <x-icon name="inbox" class="size-8 text-slate-300 dark:text-slate-600" />
                    </span>
                    <p class="mt-4 text-sm font-extrabold">{{ $filter === 'unread' ? 'اعلان خوانده‌نشده‌ای ندارید' : 'هنوز اعلانی ندارید' }}</p>
                    <p class="mt-1 text-[11px] text-slate-400">اعلان‌های سفارش، پرداخت و کیف پول اینجا نمایش داده می‌شوند.</p>
                </div>
            @endforelse

            @if ($items->hasPages())
                <div class="pt-1">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
