<div class="ip-fade-up space-y-5">

    {{-- ── header ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-2xl bg-gradient-to-br from-slate-600 to-slate-800 text-white shadow-lg shadow-slate-700/25">
                    <x-icon name="clock" class="size-5" />
                </span>
                <div>
                    <h1 class="text-base font-black">فعالیت‌های اخیر</h1>
                    <p class="mt-0.5 text-[11px] leading-5 text-slate-400">
                        هر اتفاقی که در سایت می‌افتد این‌جا ثبت می‌شود — چه کسی، چه کاری، چه چیزی از چه چیزی تغییر کرد.
                    </p>
                </div>
            </div>
            <button type="button" wire:click="$refresh"
                    class="inline-flex items-center gap-1.5 self-start rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-[11px] font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                <x-icon name="rotate" class="size-3.5" />
                بروزرسانی
            </button>
        </div>
    </div>

    {{-- ── actor filter ── --}}
    <div class="flex flex-wrap items-center gap-2">
        @foreach (['all' => 'همه', 'admin' => 'مدیران', 'seller' => 'فروشندگان', 'customer' => 'مشتریان', 'system' => 'سیستم'] as $key => $label)
            <button type="button" wire:click="$set('actor', '{{ $key }}')"
                    class="rounded-xl px-4 py-2 text-[12px] font-bold transition {{ $actor === $key
                        ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ── timeline ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @forelse ($items as $item)
            @php
                $hasDiff = ($item->before !== null && $item->before !== []) || ($item->after !== null && $item->after !== []);
                $isOpen  = in_array($item->id, $expanded, true);
            @endphp
            <div class="border-b border-slate-100 last:border-0 dark:border-slate-800/70" wire:key="act-{{ $item->id }}">
                <div class="flex gap-3.5 px-4 py-4 transition hover:bg-slate-50 sm:px-5 dark:hover:bg-slate-800/40">
                    {{-- actor avatar --}}
                    <span class="mt-0.5 grid size-10 shrink-0 place-items-center rounded-xl text-white {{ match ($item->actor_type) {
                            'admin'    => 'bg-gradient-to-br from-primary-500 to-primary-700',
                            'seller'   => 'bg-gradient-to-br from-emerald-500 to-emerald-700',
                            'customer' => 'bg-gradient-to-br from-sky-500 to-sky-700',
                            default    => 'bg-gradient-to-br from-slate-400 to-slate-600',
                        } }}">
                        <x-icon name="{{ $item->actor_type === 'admin' ? 'shield' : ($item->actor_type === 'seller' ? 'store' : ($item->actor_type === 'customer' ? 'user' : 'database')) }}" class="size-4.5" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-bold leading-6">{{ $item->title }}</p>
                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span class="rounded-md px-1.5 py-0.5 text-[10px] font-black {{ match ($item->actor_type) {
                                'admin'    => 'bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300',
                                'seller'   => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
                                'customer' => 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
                                default    => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                            } }}">{{ $item->actorLabel() }}: {{ $item->actor_name ?: '—' }}</span>
                            <span class="flex items-center gap-1 text-[11px] text-slate-400">
                                <x-icon name="clock" class="size-3" />
                                {{ \App\Support\JDate::ago($item->created_at) }}
                            </span>
                            <span class="text-[10px] text-slate-300 dark:text-slate-600" dir="ltr">{{ $item->ip }}</span>
                        </div>

                        {{-- before / after diff toggle --}}
                        @if ($hasDiff)
                            <button type="button" wire:click="toggleExpand({{ $item->id }})"
                                    class="mt-2 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10.5px] font-bold text-slate-500 transition hover:border-primary-300 hover:text-primary-700 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                                <x-icon name="{{ $isOpen ? 'chevron-up' : 'chevron-down' }}" class="size-3" />
                                جزئیات تغییرات
                            </button>
                        @endif

                        @if ($hasDiff && $isOpen)
                            <div class="mt-2.5 space-y-1.5 rounded-xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-700 dark:bg-slate-800/50"
                                 wire:key="diff-{{ $item->id }}">
                                @php
                                    $keys = array_unique(array_merge(array_keys($item->before ?? []), array_keys($item->after ?? [])));
                                @endphp
                                @foreach ($keys as $key)
                                    @php
                                        $label = \App\Support\ActivityLogger::KEY_LABELS[$key] ?? $key;
                                        $old   = $item->before[$key] ?? null;
                                        $new   = $item->after[$key] ?? null;
                                        $fmt   = fn ($v) => $v === null ? '—' : (is_bool($v) ? ($v ? 'فعال' : 'غیرفعال') : (string) $v);
                                        $changed = $fmt($old) !== $fmt($new);
                                    @endphp
                                    <div class="flex flex-wrap items-center gap-2 text-[11px] leading-5">
                                        <span class="font-extrabold text-slate-600 dark:text-slate-300">{{ $label }}:</span>
                                        <span class="rounded-md bg-rose-100 px-2 py-0.5 font-bold text-rose-700 line-through dark:bg-rose-950/60 dark:text-rose-300">{{ $fmt($old) }}</span>
                                        <x-icon name="arrow-left" class="size-3 text-slate-400" />
                                        <span class="rounded-md bg-emerald-100 px-2 py-0.5 font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">{{ $fmt($new) }}</span>
                                        @unless ($changed)
                                            <span class="text-[9.5px] text-slate-400">(بدون تغییر)</span>
                                        @endunless
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <x-icon name="clock" class="mx-auto size-10 text-slate-300 dark:text-slate-600" />
                <p class="mt-4 text-sm font-bold text-slate-400">هنوز فعالیتی ثبت نشده است</p>
            </div>
        @endforelse
    </div>

    @if ($items->hasPages())
        <div class="px-2">
            {{ $items->links() }}
        </div>
    @endif
</div>
