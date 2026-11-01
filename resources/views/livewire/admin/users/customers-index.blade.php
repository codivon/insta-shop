<div class="ip-fade-up space-y-4">

    {{-- ── flash toast ── --}}
    @if (session('admin-users-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-users-flash') }}
        </div>
    @endif

    {{-- ── toolbar card ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="relative w-full lg:max-w-xs">
                <x-icon name="search" class="absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="search" wire:model.live.debounce.350ms="search" placeholder="جستجوی نام یا شماره موبایل…"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-4 pr-10 text-sm shadow-sm transition focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800"
                       aria-label="جستجوی مشتری">
                <span wire:loading wire:target="search" class="absolute left-3 top-1/2 -translate-y-1/2">
                    <x-icon name="rotate" class="size-4 animate-spin text-primary-500" />
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2" role="group" aria-label="فیلتر وضعیت">
                @foreach ([['key' => 'all', 'label' => 'همه ('.\App\Support\Fmt::fa($stats['all']).')'], ['key' => 'active', 'label' => 'فعال ('.\App\Support\Fmt::fa($stats['active']).')'], ['key' => 'blocked', 'label' => 'مسدود ('.\App\Support\Fmt::fa($stats['blocked']).')']] as $tab)
                    <button type="button" wire:click="$set('status', '{{ $tab['key'] }}')"
                            class="rounded-xl px-3.5 py-2 text-xs font-bold transition {{ $status === $tab['key']
                                ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── table card ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($customers->isEmpty())
            <div class="flex flex-col items-center gap-3 py-16 text-center">
                <span class="grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <x-icon name="users" class="size-7" />
                </span>
                <p class="text-sm font-bold text-slate-500">مشتری‌ای یافت نشد</p>
                <p class="text-xs text-slate-400">{{ $search !== '' ? 'عبارت جستجو یا فیلتر را تغییر دهید.' : 'با اولین ثبت‌نام در اپ مشتری، اینجا نمایش داده می‌شود.' }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                            <th class="px-5 py-3 font-semibold">مشتری</th>
                            <th class="px-3 py-3 font-semibold">موبایل</th>
                            <th class="px-3 py-3 font-semibold">سفارش‌ها</th>
                            <th class="px-3 py-3 font-semibold">عضویت</th>
                            <th class="px-3 py-3 font-semibold">وضعیت</th>
                            <th class="px-5 py-3 font-semibold">اقدام</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach ($customers as $customer)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="c-{{ $customer->id }}">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-xs font-bold text-white">
                                            {{ mb_substr($customer->full_name ?: 'م', 0, 1) }}
                                        </span>
                                        <span class="text-xs font-bold">{{ $customer->full_name ?: 'بدون نام' }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ \App\Support\Fmt::toFa($customer->phone) }}</td>
                                <td class="px-3 py-3.5 text-xs font-bold">{{ \App\Support\Fmt::fa($customer->orders_count) }}</td>
                                <td class="px-3 py-3.5 text-[11px] text-slate-400">{{ \App\Support\JDate::date($customer->created_at) }}</td>
                                <td class="px-3 py-3.5">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $customer->statusClasses() }}">{{ $customer->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($customer->status === 'blocked')
                                        <button type="button" wire:click="toggleBlock({{ $customer->id }})"
                                                class="rounded-lg bg-emerald-50 px-3 py-1.5 text-[11px] font-bold text-emerald-600 transition hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-950">
                                            رفع مسدودی
                                        </button>
                                    @else
                                        <button type="button" wire:click="toggleBlock({{ $customer->id }})"
                                                wire:confirm="مشتری «{{ $customer->full_name ?: \App\Support\Fmt::toFa($customer->phone) }}» مسدود شود؟ او نمی‌تواند وارد اپ شود."
                                                class="rounded-lg bg-rose-50 px-3 py-1.5 text-[11px] font-bold text-rose-600 transition hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-950">
                                            مسدودسازی
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- lazy load more --}}
            @if ($hasMore)
                <div class="border-t border-slate-100 p-4 text-center dark:border-slate-800">
                    <button type="button" wire:click="loadMore" wire:loading.attr="disabled"
                            class="rounded-xl border border-slate-200 px-5 py-2 text-xs font-bold text-slate-600 transition hover:border-primary-400 hover:text-primary-600 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300">
                        <span wire:loading wire:target="loadMore" class="inline-block animate-spin">⏳</span>
                        نمایش بیشتر
                    </button>
                </div>
            @endif
        @endif
    </div>

    <p class="text-center text-[11px] text-slate-400">
        نمایش {{ \App\Support\Fmt::fa(min($customers->count(), $perPage)) }} از {{ \App\Support\Fmt::fa($stats['all']) }} مشتری
    </p>
</div>
