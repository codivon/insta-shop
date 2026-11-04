<div class="ip-fade-up space-y-4">

    {{-- ── header + create toggle ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-base font-black">استوری‌ها</h1>
            <p class="mt-0.5 text-xs text-slate-400">استوری‌های فروشگاه‌ها — تایید صف، انتشار دستیِ مدیر و حذف</p>
        </div>
        <button type="button" wire:click="toggleCreate"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
            <x-icon name="{{ $creating ? 'minus' : 'plus' }}" class="size-4" />
            {{ $creating ? 'بستن فرم' : 'افزودن استوری' }}
        </button>
    </div>

    {{-- ── create form ── --}}
    @if ($creating)
        <div class="rounded-2xl border border-primary-200 bg-white p-5 shadow-sm dark:border-primary-900/60 dark:bg-slate-900">
            <h2 class="text-sm font-extrabold">استوری جدید برای فروشگاه</h2>
            <p class="mt-1 text-[11px] text-slate-400">استوریِ ثبت‌شده از پنل مدیریت مستقیم منتشر می‌شود (بدون صف تایید).</p>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                {{-- shop + product --}}
                <div class="space-y-3">
                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">فروشگاه *</span>
                        <select wire:model.live="shopId" wire:loading.attr="disabled"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            <option value="">انتخاب فروشگاه…</option>
                            @foreach ($shopOptions as $shopOption)
                                <option value="{{ $shopOption->id }}">{{ $shopOption->name }}</option>
                            @endforeach
                        </select>
                        @error('shopId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">محصول متصل (اختیاری)</span>
                        <select wire:model="productId" @disabled($productOptions->isEmpty())
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold shadow-sm focus:border-primary-500 focus:outline-none disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800">
                            <option value="">بدون محصول</option>
                            @foreach ($productOptions as $productOption)
                                <option value="{{ $productOption->id }}">{{ $productOption->name }}</option>
                            @endforeach
                        </select>
                        @error('productId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">لینک (اختیاری)</span>
                        <input type="url" dir="ltr" wire:model="linkUrl" placeholder="https://…"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('linkUrl') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>
                </div>

                {{-- media + caption + window --}}
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <label class="flex-1">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">تصویر استوری * (پیشنهادی: عمودی ۹:۱۶)</span>
                            <input type="file" accept="image/jpeg,image/png,image/webp" wire:model.live="media"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs file:me-3 file:rounded-lg file:border-0 file:bg-primary-600 file:px-3 file:py-1.5 file:text-[10px] file:font-bold file:text-white shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            @error('media') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            <span wire:loading wire:target="media" class="mt-1 block text-[10px] font-semibold text-primary-500">در حال بارگذاری…</span>
                        </label>
                        @if ($media)
                            <img src="{{ $media->temporaryUrl() }}" alt="پیش‌نمایش استوری"
                                 class="h-24 w-16 rounded-xl border border-slate-200 object-cover dark:border-slate-700">
                        @endif
                    </div>

                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">متن استوری (اختیاری)</span>
                        <textarea wire:model="caption" rows="2" maxlength="500" placeholder="توضیح کوتاه روی استوری…"
                                  class="mt-1.5 w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800"></textarea>
                        @error('caption') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>

                    {{-- window --}}
                    <div class="grid grid-cols-2 gap-2">
                        <label class="col-span-2 text-[11px] font-bold text-slate-500 dark:text-slate-400">شروع</span>
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border p-2.5 text-[11px] font-extrabold transition {{ $startMode === 'now'
                            ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                            : 'border-slate-200 dark:border-slate-700' }}">
                            <input type="radio" wire:model.live="startMode" value="now" class="size-3.5 accent-primary-600">
                            همین حالا
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border p-2.5 text-[11px] font-extrabold transition {{ $startMode === 'custom'
                            ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                            : 'border-slate-200 dark:border-slate-700' }}">
                            <input type="radio" wire:model.live="startMode" value="custom" class="size-3.5 accent-primary-600">
                            زمان دلخواه
                        </label>
                        @if ($startMode === 'custom')
                            <div class="col-span-2 grid grid-cols-2 gap-2">
                                <x-jdate-picker model="startDate" placeholder="تاریخ شروع…" />
                                <input type="time" wire:model="startTime" aria-label="ساعت شروع"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                                @error('startDate') <p class="col-span-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                @error('startTime') <p class="col-span-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    <div>
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">مدت نمایش</span>
                        <div class="mt-1.5 grid grid-cols-4 gap-2" role="radiogroup" aria-label="مدت نمایش">
                            @foreach ([['12', '۱۲ ساعت'], ['24', '۲۴ ساعت'], ['48', '۴۸ ساعت'], ['custom', 'دلخواه']] as [$value, $label])
                                <label class="flex cursor-pointer items-center justify-center rounded-xl border p-2.5 text-[11px] font-extrabold transition {{ $duration === $value
                                    ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                                    : 'border-slate-200 dark:border-slate-700' }}">
                                    <input type="radio" wire:model.live="duration" value="{{ $value }}" class="sr-only">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @if ($duration === 'custom')
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <x-jdate-picker model="endDate" placeholder="تاریخ پایان…" />
                                <input type="time" wire:model="endTime" aria-label="ساعت پایان"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                                @error('endDate') <p class="col-span-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                @error('endTime') <p class="col-span-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                <button type="button" wire:click="toggleCreate"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    انصراف
                </button>
                <button type="button" wire:click="createStory" wire:loading.attr="disabled" wire:target="createStory"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                    <x-icon name="zap" class="size-4" />
                    انتشار استوری
                </button>
            </div>
        </div>
    @endif

    {{-- ── filter tabs ── --}}
    <div class="scroll-slim flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="فیلتر استوری‌ها">
        @foreach ([
            'all' => ['همه', 'tag'],
            'pending' => ['در انتظار تأیید', 'shield-off'],
            'running' => ['در حال نمایش', 'zap'],
            'scheduled' => ['زمان‌بندی‌شده', 'clock'],
            'expired' => ['منقضی', 'circle-slash'],
            'rejected' => ['رد شده', 'circle-x'],
        ] as $key => [$label, $icon])
            <button type="button" wire:click="$set('filter', '{{ $key }}')" role="tab"
                    :aria-selected="$wire.filter === '{{ $key }}'"
                    class="flex shrink-0 items-center gap-1.5 rounded-xl border px-3.5 py-2 text-[11px] font-extrabold transition {{ $filter === $key
                        ? 'border-primary-600 bg-primary-600 text-white shadow-md shadow-primary-600/25'
                        : 'border-slate-200 bg-white text-slate-500 hover:border-primary-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                <x-icon name="{{ $icon }}" class="size-3.5" />
                {{ $label }}
                <span class="rounded-full px-1.5 text-[9px] font-black {{ $filter === $key ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-800' }}">{{ \App\Support\Fmt::fa($counts[$key]) }}</span>
            </button>
        @endforeach
    </div>

    {{-- ── list ── --}}
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($stories as $story)
            <div wire:key="story-{{ $story->id }}"
                 class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <a href="{{ $story->mediaUrl() }}" target="_blank" rel="noopener"
                   class="relative block h-32 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                    <img src="{{ $story->mediaUrl() }}" alt="استوری {{ $story->shop->name }}" loading="lazy" class="h-full w-full object-cover">
                </a>

                <div class="flex min-w-0 flex-1 flex-col">
                    <div class="flex items-center justify-between gap-2">
                        <span class="truncate text-xs font-extrabold">{{ $story->shop->name }}</span>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-black {{ $story->stateClass() }}">{{ $story->stateLabel() }}</span>
                    </div>

                    @if ($story->caption)
                        <p class="mt-1 line-clamp-2 text-[10.5px] leading-4 text-slate-500 dark:text-slate-400">{{ $story->caption }}</p>
                    @endif

                    <div class="mt-1.5 space-y-1 text-[10px] font-semibold text-slate-400">
                        <div class="flex items-center gap-1"><x-icon name="clock" class="size-3" />{{ \App\Support\JDate::dateTime($story->starts_at) }} ← {{ \App\Support\JDate::dateTime($story->ends_at) }}</div>
                        <div class="flex items-center gap-3">
                            <span class="flex items-center gap-1"><x-icon name="eye" class="size-3" />{{ \App\Support\Fmt::fa($story->views) }}</span>
                            <span class="flex items-center gap-1"><x-icon name="heart" class="size-3" />{{ \App\Support\Fmt::fa($story->likes_count) }}</span>
                            <span class="flex items-center gap-1"><x-icon name="message" class="size-3" />{{ \App\Support\Fmt::fa($story->comments_count) }}</span>
                        </div>
                        @if ($story->product)
                            <div class="flex items-center gap-1"><x-icon name="package" class="size-3" />محصول: {{ $story->product->name }}</div>
                        @endif
                        @if ($story->link_url)
                            <a href="{{ $story->link_url }}" target="_blank" rel="noopener" class="block truncate text-primary-500 hover:underline" dir="ltr">{{ $story->link_url }}</a>
                        @endif
                    </div>

                    <div class="mt-auto flex items-center gap-1.5 pt-2">
                        @if ($story->status === \App\Models\Story::STATUS_PENDING)
                            <button type="button" wire:click="approve({{ $story->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1.5 text-[10px] font-extrabold text-white shadow-sm transition hover:bg-emerald-700 active:scale-95">
                                <x-icon name="check" class="size-3" /> تأیید
                            </button>
                            <button type="button" wire:click="reject({{ $story->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg bg-amber-500 px-2.5 py-1.5 text-[10px] font-extrabold text-white shadow-sm transition hover:bg-amber-600 active:scale-95">
                                <x-icon name="circle-x" class="size-3" /> رد
                            </button>
                        @endif
                        <button type="button" wire:click="destroy({{ $story->id }})"
                                wire:confirm="این استوری برای همیشه حذف شود؟"
                                class="ms-auto inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2.5 py-1.5 text-[10px] font-extrabold text-rose-600 transition hover:bg-rose-50 active:scale-95 dark:border-rose-900 dark:hover:bg-rose-950/40">
                            <x-icon name="trash" class="size-3" /> حذف
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                <x-icon name="zap" class="mx-auto size-8 text-slate-300 dark:text-slate-600" />
                <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">استوری‌ای در این فهرست نیست.</p>
            </div>
        @endforelse
    </div>

    @if ($hasMore && $stories->isNotEmpty())
        <div x-data
             x-init="new IntersectionObserver((entries) => { if (entries[0].isIntersecting) $wire.call('loadMore') }, { rootMargin: '260px' }).observe($el)"
             class="grid place-items-center py-2">
            <span class="inline-block size-6 animate-spin rounded-full border-2 border-primary-600 border-t-transparent"></span>
        </div>
    @endif
</div>
