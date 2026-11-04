<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                <x-icon name="layout-grid" class="size-5" />
            </span>
            <div>
                <div class="text-sm font-extrabold leading-5">دسته‌بندی‌ها</div>
                <div class="text-[11px] text-slate-400">هرچه نیاز داری، دسته‌بندی‌شده</div>
            </div>
        </div>
        <label class="mt-2.5 flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-sm focus-within:border-primary-400 dark:border-slate-800 dark:bg-slate-900">
            <x-icon name="search" class="size-4 shrink-0 text-slate-400" />
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجوی دسته‌بندی…"
                   class="w-full bg-transparent text-xs outline-none placeholder:text-slate-400"
                   aria-label="جستجوی دسته‌بندی">
            @if (filled($search))
                <button type="button" wire:click="$set('search', '')" aria-label="پاک کردن جستجو"
                        class="shrink-0 text-slate-400 transition hover:text-rose-500">
                    <x-icon name="x" class="size-4" />
                </button>
            @endif
        </label>
    </div>

    <div class="px-4 pb-6 pt-4">
        @if (filled($search))
            {{-- ───────── Search results across the whole tree ───────── --}}
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-extrabold">نتایج جستجو</h2>
                <span class="text-[11px] font-semibold text-slate-400">{{ \App\Support\Fmt::toFa($resultsTotal) }} دسته‌بندی</span>
            </div>

            @if ($results->isNotEmpty())
                <div class="mt-3 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                    @foreach ($results as $cat)
                        <a href="{{ route('shop.categories.show', $cat->id) }}" wire:navigate wire:key="sr-{{ $cat->id }}"
                           class="group flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white p-2.5 transition hover:border-rose-300 dark:border-slate-800 dark:bg-slate-900">
                            <span class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                @if ($img = $cat->imageOrParentUrl())
                                    <img src="{{ $img }}" alt="" aria-hidden="true" loading="lazy" class="size-9 object-contain p-0.5">
                                @else
                                    <x-icon name="layout-grid" class="size-5 text-slate-400" />
                                @endif
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-xs font-extrabold text-slate-700 dark:text-slate-200">{{ $cat->name }}</span>
                                <span class="block truncate text-[10px] font-semibold text-slate-400">{{ $cat->parent?->pathLabel() ?? 'دستهٔ اصلی' }}</span>
                            </span>
                            <x-icon name="chevron-left" class="ms-auto size-3.5 shrink-0 text-slate-300 transition group-hover:text-rose-400" />
                        </a>
                    @endforeach
                </div>
                @if ($resultsTotal > $results->count())
                    <p class="mt-3 text-center text-[10.5px] font-semibold text-slate-400">نمایش {{ \App\Support\Fmt::toFa($results->count()) }} نتیجهٔ نخست — برای یافتن دقیق‌تر، عبارت کامل‌تری بنویسید.</p>
                @endif
            @else
                <div class="mt-3 rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                    <x-icon name="search" class="mx-auto size-8 text-slate-300 dark:text-slate-600" />
                    <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">دسته‌بندی‌ای مطابق «{{ trim($search) }}» پیدا نشد.</p>
                </div>
            @endif
        @else
            <div class="space-y-7">
                @foreach ($sections as $root)
            {{-- ───────── One main category = section header + child image row ───────── --}}
            <section wire:key="sec-{{ $root->id }}" aria-label="{{ $root->name }}">
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('shop.categories.show', $root->id) }}" wire:navigate
                       class="flex min-w-0 items-center gap-2.5">
                        <span class="grid size-10 shrink-0 place-items-center overflow-hidden rounded-2xl bg-slate-100 dark:bg-slate-800">
                            <img src="{{ $root->image }}" alt="" aria-hidden="true" loading="lazy" class="size-8 object-contain p-0.5">
                        </span>
                        <span class="truncate text-sm font-extrabold">{{ $root->name }}</span>
                    </a>
                    <a href="{{ route('shop.categories.show', $root->id) }}" wire:navigate
                       class="flex shrink-0 items-center gap-0.5 rounded-full px-1 py-1 text-[11px] font-black text-rose-500 transition hover:text-rose-600"
                       aria-label="همه زیرشاخه‌های {{ $root->name }}">
                        همه
                        <x-icon name="chevron-left" class="size-3.5" />
                    </a>
                </div>

                <div class="scroll-slim mt-3 flex gap-2.5 overflow-x-auto overscroll-x-contain pb-1.5">
                    @foreach ($root->children as $child)
                        <a href="{{ route('shop.categories.show', $child->id) }}" wire:navigate wire:key="cat-{{ $child->id }}"
                           class="group w-[88px] shrink-0 text-center">
                            <span class="grid aspect-square w-full place-items-center overflow-hidden rounded-2xl bg-slate-100 p-2 transition group-active:scale-95 dark:bg-slate-800">
                                <img src="{{ $child->image }}" alt="{{ $child->name }}" loading="lazy"
                                     class="max-h-full max-w-full object-contain drop-shadow-sm">
                            </span>
                            <span class="mt-1.5 line-clamp-2 text-[10px] font-bold leading-4 text-slate-600 dark:text-slate-300">{{ $child->name }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
                @endforeach
            </div>
        @endif
    </div>
</div>
