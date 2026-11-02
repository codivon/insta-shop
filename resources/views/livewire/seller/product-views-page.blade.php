<div class="ip-fade-up">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('seller.reports') }}" wire:navigate
                   class="grid size-10 shrink-0 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بازگشت">
                    <x-icon name="arrow-right" class="size-5" />
                </a>
                <div>
                    <div class="text-sm font-extrabold leading-5">گزارش بازدید محصولات</div>
                    <div class="text-[11px] text-slate-400">رتبه‌بندی کامل، جستجو و فیلتر</div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <x-seller-notif-bell />
            </div>
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">
        {{-- ── summary chips ── --}}
        <div class="grid grid-cols-2 gap-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[9.5px] font-bold text-slate-400">بازدید بازه انتخابی</p>
                <p class="mt-0.5 text-sm font-black text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::fa($periodViewsTotal) }} <span class="text-[9px] font-bold text-slate-400">بازدید</span></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[9.5px] font-bold text-slate-400">محصولات مطابق جستجو</p>
                <p class="mt-0.5 text-sm font-black">{{ \App\Support\Fmt::fa($totalMatching) }} <span class="text-[9px] font-bold text-slate-400">محصول</span></p>
            </div>
        </div>

        {{-- ── range filter ── --}}
        <div class="mt-4 flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800" role="group" aria-label="بازه بازدید">
            @foreach ([['key' => 'all', 'label' => 'همه'], ['key' => 'today', 'label' => 'امروز'], ['key' => 'd7', 'label' => '۷ روز'], ['key' => 'd30', 'label' => '۳۰ روز'], ['key' => 'custom', 'label' => 'بازه']] as $r)
                <button type="button" wire:click="$set('range', '{{ $r['key'] }}')"
                        class="flex-1 rounded-lg px-1.5 py-1.5 text-[10px] font-bold transition {{ $range === $r['key']
                            ? 'bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400'
                            : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                    {{ $r['label'] }}
                </button>
            @endforeach
        </div>

        @if ($range === 'custom')
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div>
                    <span class="mb-1 block text-[9.5px] font-bold text-slate-400">از تاریخ</span>
                    <x-jdate-picker model="from" placeholder="از تاریخ" />
                </div>
                <div>
                    <span class="mb-1 block text-[9.5px] font-bold text-slate-400">تا تاریخ</span>
                    <x-jdate-picker model="to" placeholder="تا تاریخ" />
                </div>
            </div>
        @endif

        {{-- ── search + sort ── --}}
        <div class="mt-3 flex items-center gap-2">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">جستجوی محصول</span>
                <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجوی نام محصول…"
                       class="w-full rounded-2xl border border-slate-200 bg-white py-2.5 pl-3 pr-9 text-xs font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 dark:border-slate-700 dark:bg-slate-900 dark:focus:ring-primary-950">
            </label>
            <button type="button" wire:click="toggleSort"
                    class="flex shrink-0 items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-[10px] font-extrabold text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                    aria-label="تغییر مرتب‌سازی">
                @if ($sort === 'desc')
                    <x-icon name="arrow-down-left" class="size-3.5 text-primary-500" />
                    بیشترین بازدید
                @else
                    <x-icon name="arrow-up-left" class="size-3.5 text-primary-500" />
                    کمترین بازدید
                @endif
            </button>
        </div>

        {{-- ── ranking list (lazy) ── --}}
        <div class="mt-4 space-y-2" data-testid="views-ranking">
            @forelse ($products as $product)
                <div wire:key="pv-{{ $product->id }}"
                     class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-2.5 shadow-sm transition hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-600">
                    <span class="w-5 shrink-0 text-center text-xs font-black {{ $sort === 'desc' && $loop->index < 3 ? 'text-amber-500' : 'text-slate-300 dark:text-slate-600' }}">
                        {{ \App\Support\Fmt::fa($loop->index + 1) }}
                    </span>

                    @if ($product->mainImage)
                        <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" loading="lazy"
                             class="size-11 shrink-0 rounded-xl border border-slate-200 object-cover dark:border-slate-700">
                    @else
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                            <x-icon name="image" class="size-5" />
                        </span>
                    @endif

                    <div class="min-w-0 flex-1">
                        <div class="truncate text-xs font-extrabold">{{ $product->name }}</div>
                        <div class="mt-0.5 text-[10px] font-semibold text-slate-400">
                            {{ $product->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}
                            @if ($product->views !== (int) $product->period_views)
                                · مجموع کل: {{ \App\Support\Fmt::fa($product->views) }}
                            @endif
                        </div>
                    </div>

                    <span class="flex shrink-0 items-center gap-1 rounded-xl bg-primary-50 px-2.5 py-1.5 text-[10px] font-black text-primary-600 dark:bg-primary-950/50 dark:text-primary-300">
                        <x-icon name="eye" class="size-3.5" />
                        {{ \App\Support\Fmt::fa($product->period_views) }}
                    </span>
                </div>
            @empty
                <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-300 py-10 text-center dark:border-slate-700">
                    <span class="grid size-14 place-items-center rounded-2xl bg-primary-50 text-primary-400 dark:bg-primary-950">
                        <x-icon name="eye" class="size-7" />
                    </span>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-300">
                        @if ($search !== '')
                            محصولی با این نام پیدا نشد.
                        @else
                            هنوز بازدیدی در این بازه ثبت نشده است.
                        @endif
                    </p>
                </div>
            @endforelse

            {{-- lazy loading — reveal next chunk --}}
            @if ($hasMore)
                <div class="pt-1">
                    <button type="button" wire:click="loadMore" wire:loading.attr="disabled" wire:target="loadMore"
                            class="flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white py-3 text-xs font-extrabold text-primary-600 transition hover:border-primary-300 hover:bg-primary-50/50 active:scale-[.99] dark:border-slate-700 dark:bg-slate-900 dark:text-primary-400 dark:hover:bg-primary-950/30">
                        <x-icon name="rotate" class="hidden size-4 animate-spin" wire:loading wire:target="loadMore" />
                        <x-icon name="chevron-down" class="size-4" wire:loading.remove wire:target="loadMore" />
                        <span wire:loading.remove wire:target="loadMore">نمایش بیشتر</span>
                        <span wire:loading wire:target="loadMore">در حال بارگذاری…</span>
                    </button>
                    <p class="mt-2 text-center text-[9.5px] font-semibold text-slate-400">
                        {{ \App\Support\Fmt::fa($products->count()) }} از {{ \App\Support\Fmt::fa($totalMatching) }} محصول
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
