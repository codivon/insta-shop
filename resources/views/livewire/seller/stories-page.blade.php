<div class="ip-fade-up space-y-4">

    {{-- ── header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-base font-black">استوری‌های فروشگاه</h1>
            <p class="mt-0.5 text-xs text-slate-400">
                مثل اینستاگرام — تصویر، محصول متصل، لینک و زمان نمایش دلخواه
                <span class="mx-1 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                    {{ \App\Support\Fmt::fa($runningCount) }}/{{ \App\Support\Fmt::fa($activeLimit) }} فعال
                </span>
            </p>
        </div>
    </div>

    {{-- ── limit info ── --}}
    @if ($runningCount >= $activeLimit)
        <div class="flex items-start gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-[11px] font-bold leading-5 text-amber-700 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
            <x-icon name="alert" class="mt-0.5 size-4 shrink-0" />
            <span>سقف استوری فعال شما ({!! \App\Support\Fmt::fa($activeLimit) !!}) پر شده است. تا منقضی‌شدن یکی از استوری‌ها نمی‌توانید استوری جدید بگذارید.</span>
        </div>
    @endif

    {{-- ── create form ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-extrabold">استوری جدید</h2>
        <p class="mt-1 text-[11px] text-slate-400">
            @if ($moderation === 'manual')
                استوری شما بعد از تایید مدیر منتشر می‌شود.
            @else
                استوری بلافاصله بعد از ثبت منتشر می‌شود.
            @endif
        </p>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
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
                             class="h-28 w-[4.5rem] rounded-xl border border-slate-200 object-cover dark:border-slate-700">
                    @endif
                </div>

                <label class="block">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">محصول متصل (اختیاری)</span>
                    <select wire:model="productId" @disabled($productOptions->isEmpty())
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold shadow-sm focus:border-primary-500 focus:outline-none disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800">
                        <option value="">بدون محصول</option>
                        @foreach ($productOptions as $productOption)
                            <option value="{{ $productOption->id }}">{{ $productOption->name }}</option>
                        @endforeach
                    </select>
                    <span class="mt-1 block text-[10px] font-semibold text-slate-400">با کشیدن بالا، کارت محصول با قیمت و دکمهٔ خرید روی استوری نمایش داده می‌شود.</span>
                    @error('productId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </label>

                <label class="block">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">لینک (اختیاری)</span>
                    <input type="url" dir="ltr" wire:model="linkUrl" placeholder="https://…"
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    @error('linkUrl') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </label>

                <label class="block">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">متن استوری (اختیاری)</span>
                    <textarea wire:model="caption" rows="2" maxlength="500" placeholder="توضیح کوتاه روی استوری…"
                              class="mt-1.5 w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800"></textarea>
                    @error('caption') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </label>
            </div>

            <div class="space-y-3">
                {{-- window --}}
                <div class="grid grid-cols-2 gap-2">
                    <span class="col-span-2 text-[11px] font-bold text-slate-500 dark:text-slate-400">شروع نمایش</span>
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
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">مدت نمایش (پیش‌فرض: ۲۴ ساعت)</span>
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

                <button type="button" wire:click="createStory" wire:loading.attr="disabled" wire:target="createStory"
                        class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                    <x-icon name="zap" class="size-4" />
                    @if ($moderation === 'manual')
                        ثبت و ارسال برای تایید
                    @else
                        انتشار استوری
                    @endif
                </button>
            </div>
        </div>
    </div>

    {{-- ── my stories ── --}}
    <div>
        <h2 class="text-sm font-extrabold">استوری‌های من</h2>

        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @forelse ($stories as $story)
                <div wire:key="story-{{ $story->id }}"
                     class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <a href="{{ $story->mediaUrl() }}" target="_blank" rel="noopener"
                       class="relative block h-36 w-24 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                        <img src="{{ $story->mediaUrl() }}" alt="استوری من" loading="lazy" class="h-full w-full object-cover">
                    </a>

                    <div class="flex min-w-0 flex-1 flex-col">
                        <div class="flex items-center justify-between gap-2">
                            <span class="rounded-full px-2 py-0.5 text-[9px] font-black {{ $story->stateClass() }}">{{ $story->stateLabel() }}</span>
                            <span class="text-[9.5px] font-semibold text-slate-400">{{ \App\Support\JDate::ago($story->created_at) }}</span>
                        </div>

                        <div class="mt-2 space-y-1 text-[10px] font-semibold text-slate-400">
                            <div class="flex items-center gap-1"><x-icon name="clock" class="size-3" />{{ \App\Support\JDate::dateTime($story->starts_at) }} ← {{ \App\Support\JDate::dateTime($story->ends_at) }}</div>
                            <div class="flex items-center gap-3">
                                <span class="flex items-center gap-1"><x-icon name="eye" class="size-3" />{{ \App\Support\Fmt::fa($story->views) }} بازدید</span>
                                <span class="flex items-center gap-1"><x-icon name="heart" class="size-3" />{{ \App\Support\Fmt::fa($story->likes_count) }}</span>
                                <span class="flex items-center gap-1"><x-icon name="message" class="size-3" />{{ \App\Support\Fmt::fa($story->comments_count) }}</span>
                            </div>
                            @if ($story->product)
                                <div class="flex items-center gap-1"><x-icon name="package" class="size-3" />{{ $story->product->name }}</div>
                            @endif
                            @if ($story->link_url)
                                <a href="{{ $story->link_url }}" target="_blank" rel="noopener" class="block truncate text-primary-500 hover:underline" dir="ltr">{{ $story->link_url }}</a>
                            @endif
                        </div>

                        <button type="button" wire:click="destroy({{ $story->id }})" wire:confirm="این استوری حذف شود؟"
                                class="mt-auto inline-flex w-fit items-center gap-1 rounded-lg border border-rose-200 px-2.5 py-1.5 text-[10px] font-extrabold text-rose-600 transition hover:bg-rose-50 active:scale-95 dark:border-rose-900 dark:hover:bg-rose-950/40">
                            <x-icon name="trash" class="size-3" /> حذف
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                    <x-icon name="zap" class="mx-auto size-8 text-slate-300 dark:text-slate-600" />
                    <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">هنوز استوری‌ای ندارید — اولین استوری فروشگاه‌تان را منتشر کنید.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
