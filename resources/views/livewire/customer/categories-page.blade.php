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
    </div>

    <div class="space-y-7 px-4 pb-6 pt-4">
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
</div>
