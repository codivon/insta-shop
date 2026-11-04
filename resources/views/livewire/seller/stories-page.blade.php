<div class="ip-fade-up space-y-4">

    {{-- ── header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-base font-black">استوری‌های فروشگاه</h1>
            <p class="mt-0.5 text-xs text-slate-400">
                مثل اینستاگرام — تصویر یا ویدیو، محصول متصل، لینک با عنوان و زمان نمایش دلخواه
                <span class="mx-1 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                    {{ \App\Support\Fmt::fa($runningCount) }}/{{ \App\Support\Fmt::fa($activeLimit) }} فعال
                </span>
            </p>
        </div>
        <button type="button" wire:click="openCreate"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
            <x-icon name="plus" class="size-4" />
            استوری جدید
        </button>
    </div>

    {{-- ── limit info ── --}}
    @if ($runningCount >= $activeLimit)
        <div class="flex items-start gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-[11px] font-bold leading-5 text-amber-700 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
            <x-icon name="alert" class="mt-0.5 size-4 shrink-0" />
            <span>سقف استوری فعال شما ({!! \App\Support\Fmt::fa($activeLimit) !!}) پر شده است. تا منقضی‌شدن یکی از استوری‌ها نمی‌توانید استوری جدید بگذارید.</span>
        </div>
    @endif

    {{-- ── my stories ── --}}
    <div>
        <h2 class="text-sm font-extrabold">استوری‌های من</h2>
        <p class="mt-1 text-[11px] text-slate-400">
            @if ($moderation === 'manual')
                استوری جدید بعد از تایید مدیر منتشر می‌شود.
            @else
                استوری بلافاصله بعد از ثبت منتشر می‌شود.
            @endif
        </p>

        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @forelse ($stories as $story)
                <div wire:key="story-{{ $story->id }}"
                     class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="relative block h-36 w-24 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                        @if ($story->type === \App\Models\Story::TYPE_VIDEO)
                            <span class="grid size-full place-items-center bg-gradient-to-br from-slate-700 to-slate-900">
                                <x-icon name="play" class="size-7 text-white/85" />
                            </span>
                            <span class="absolute bottom-1.5 left-1/2 -translate-x-1/2 rounded-full bg-black/60 px-2 py-0.5 text-[8.5px] font-black text-white">ویدیو</span>
                        @elseif ($story->media_path)
                            <img src="{{ $story->mediaUrl() }}" alt="استوری من" loading="lazy" class="h-full w-full object-cover">
                        @else
                            <span class="grid size-full place-items-center"><x-icon name="zap" class="size-6 text-slate-300 dark:text-slate-600" /></span>
                        @endif
                    </div>

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
                                @if (! $story->likes_enabled || ! $story->comments_enabled)
                                    <span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[8.5px] font-black text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                        {{ ! $story->likes_enabled ? 'بدون لایک' : '' }}{{ ! $story->likes_enabled && ! $story->comments_enabled ? ' · ' : '' }}{{ ! $story->comments_enabled ? 'بدون نظر' : '' }}
                                    </span>
                                @endif
                            </div>
                            @if ($story->product)
                                <div class="flex items-center gap-1"><x-icon name="package" class="size-3" />{{ $story->product->name }}</div>
                            @endif
                            @if ($story->link_url)
                                <div class="flex items-center gap-1"><x-icon name="external-link" class="size-3" />{{ $story->link_title ?: $story->link_url }}</div>
                            @endif
                        </div>

                        <div class="mt-auto flex items-center gap-1.5">
                            <button type="button" wire:click="openEdit({{ $story->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1.5 text-[10px] font-extrabold text-slate-600 transition hover:bg-slate-50 active:scale-95 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                <x-icon name="edit" class="size-3" /> ویرایش
                            </button>
                            <button type="button" wire:click="destroy({{ $story->id }})" wire:confirm="این استوری حذف شود؟"
                                    class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2.5 py-1.5 text-[10px] font-extrabold text-rose-600 transition hover:bg-rose-50 active:scale-95 dark:border-rose-900 dark:hover:bg-rose-950/40">
                                <x-icon name="trash" class="size-3" /> حذف
                            </button>
                        </div>
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

    {{-- ── add / edit MODAL ── --}}
    @if ($formOpen)
        <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center" role="dialog" aria-modal="true" aria-label="{{ $editingId !== null ? 'ویرایش استوری' : 'استوری جدید' }}">
            <button type="button" wire:click="closeForm" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" aria-label="بستن"></button>

            <div class="scroll-slim relative max-h-[92dvh] w-full max-w-lg space-y-4 overflow-y-auto rounded-t-3xl bg-white p-5 shadow-2xl sm:rounded-3xl dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-extrabold">{{ $editingId !== null ? 'ویرایش استوری' : 'استوری جدید' }}</h2>
                    <button type="button" wire:click="closeForm" aria-label="بستن"
                            class="grid size-8 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 active:scale-90 dark:hover:bg-slate-800">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>

                <p class="text-[11px] font-semibold text-slate-400">
                    @if ($editingId === null && $moderation === 'manual')
                        استوری شما بعد از تایید مدیر منتشر می‌شود.
                    @endif
                </p>

                {{-- type picker: image | video --}}
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border p-2.5 text-[11px] font-extrabold transition {{ $type === 'image'
                        ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                        : 'border-slate-200 dark:border-slate-700' }}">
                        <input type="radio" wire:model.live="type" value="image" class="sr-only">
                        <x-icon name="image" class="size-3.5" /> تصویر
                    </label>
                    <label class="flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border p-2.5 text-[11px] font-extrabold transition {{ $type === 'video'
                        ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                        : 'border-slate-200 dark:border-slate-700' }}">
                        <input type="radio" wire:model.live="type" value="video" class="sr-only">
                        <x-icon name="play" class="size-3.5" /> ویدیو (از لینک)
                    </label>
                </div>

                {{-- media: upload (image) OR link (video) --}}
                @if ($type === 'image')
                    <div class="flex items-start gap-3">
                        <label class="flex-1">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                تصویر استوری {{ $editingId === null ? '*' : '(برای تغییر، فایل جدید بدهید)' }} — پیشنهادی: عمودی ۹:۱۶
                            </span>
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
                @else
                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">لینک مستقیم ویدیو * (mp4، webm، ogg یا m3u8 — ویدیو آپلود نمی‌شود)</span>
                        <input type="url" dir="ltr" wire:model="videoUrl" placeholder="https://example.com/story.mp4"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        <span class="mt-1 block text-[10px] font-semibold text-slate-400">ویدیو در نمایشگر استوری با دکمهٔ قطع صدا پخش می‌شود.</span>
                        @error('videoUrl') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>
                @endif

                {{-- attached product — searchable picker --}}
                <div>
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">محصول متصل (اختیاری)</span>
                    @if ($selectedProduct)
                        <div class="mt-1.5 flex items-center justify-between gap-2 rounded-xl border border-primary-200 bg-primary-50/60 px-3 py-2.5 dark:border-primary-900 dark:bg-primary-950/30">
                            <span class="flex min-w-0 items-center gap-2 text-xs font-extrabold text-primary-700 dark:text-primary-300">
                                <x-icon name="package" class="size-3.5 shrink-0" />
                                <span class="truncate">{{ $selectedProduct->name }}</span>
                            </span>
                            <button type="button" wire:click="$set('productId', '')" aria-label="برداشتن محصول"
                                    class="grid size-6 shrink-0 place-items-center rounded-lg text-primary-500 transition hover:bg-primary-100 active:scale-90 dark:hover:bg-primary-900/50">
                                <x-icon name="x" class="size-3.5" />
                            </button>
                        </div>
                    @else
                        <div class="mt-1.5" x-data="{ open: false }" @click.outside="open = false">
                            <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 shadow-sm focus-within:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                                <x-icon name="search" class="size-3.5 shrink-0 text-slate-400" />
                                <input type="text" wire:model.live.debounce.300ms="productSearch" x-on:focus="open = true"
                                       placeholder="جستجوی محصول…"
                                       class="w-full bg-transparent text-xs outline-none placeholder:text-slate-400">
                            </label>
                            <div x-show="open" x-cloak x-transition.opacity
                                 class="scroll-slim mt-1 max-h-44 space-y-0.5 overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                                @forelse ($productOptions as $productOption)
                                    <button type="button" wire:click="$set('productId', {{ $productOption->id }})"
                                            wire:key="popt-{{ $productOption->id }}"
                                            class="block w-full truncate rounded-lg px-2.5 py-2 text-start text-xs font-semibold text-slate-600 transition hover:bg-primary-50 hover:text-primary-700 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">
                                        {{ $productOption->name }}
                                    </button>
                                @empty
                                    <p class="px-2.5 py-2 text-[10px] font-bold text-slate-400">
                                        {{ trim($productSearch) !== '' ? 'محصولی با این نام پیدا نشد.' : 'محصول منتشره‌ای ندارید.' }}
                                    </p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                    <span class="mt-1 block text-[10px] font-semibold text-slate-400">با انتخاب محصول، کارت محصول با قیمت و دکمهٔ خرید روی استوری نمایش داده می‌شود.</span>
                    @error('productId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- external link + title --}}
                <div class="grid grid-cols-1 gap-2">
                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">لینک (اختیاری)</span>
                        <input type="url" dir="ltr" wire:model="linkUrl" placeholder="https://…"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('linkUrl') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">عنوان لینک {{ $linkUrl !== '' ? '*' : '(اگر لینک دارید)' }} — روی استوری نمایش داده می‌شود</span>
                        <input type="text" wire:model="linkTitle" maxlength="150" placeholder="مثلاً: تخفیف ویژهٔ امروز"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('linkTitle') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>
                </div>

                {{-- caption --}}
                <label class="block">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">متن استوری (اختیاری)</span>
                    <textarea wire:model="caption" rows="2" maxlength="500" placeholder="توضیح کوتاه روی استوری…"
                              class="mt-1.5 w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800"></textarea>
                    @error('caption') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </label>

                {{-- likes / comments toggles --}}
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex cursor-pointer items-center justify-between gap-2 rounded-xl border border-slate-200 px-3 py-2.5 dark:border-slate-700">
                        <span class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">لایک</span>
                        <input type="checkbox" wire:model="likesEnabled" class="size-4 accent-primary-600">
                    </label>
                    <label class="flex cursor-pointer items-center justify-between gap-2 rounded-xl border border-slate-200 px-3 py-2.5 dark:border-slate-700">
                        <span class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">نظر</span>
                        <input type="checkbox" wire:model="commentsEnabled" class="size-4 accent-primary-600">
                    </label>
                </div>
                <p class="-mt-2 text-[10px] font-semibold text-slate-400">با خاموش‌کردن هر کدام، دکمهٔ آن روی این استوری مخفی می‌شود (پیش‌فرض: هر دو روشن).</p>

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

                <div class="sticky bottom-0 -mx-5 -mb-5 border-t border-slate-100 bg-white/95 px-5 pb-[calc(12px_+_env(safe-area-inset-bottom,0px))] pt-3 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
                    <button type="button" wire:click="{{ $editingId !== null ? 'updateStory' : 'createStory' }}"
                            wire:loading.attr="disabled" wire:target="{{ $editingId !== null ? 'updateStory' : 'createStory' }}"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                        <x-icon name="zap" class="size-4" />
                        @if ($editingId !== null)
                            ذخیرهٔ تغییرات
                        @elseif ($moderation === 'manual')
                            ثبت و ارسال برای تایید
                        @else
                            انتشار استوری
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
