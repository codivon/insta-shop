<div class="ip-fade-up space-y-4">

    {{-- ── header — gradient hero card with live counter ── --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500 p-4 text-white shadow-xl shadow-primary-600/25">
        <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
            <div class="absolute -top-8 left-6 size-36 rounded-full bg-white/25 blur-2xl"></div>
        </div>
        <div class="relative flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-white/15 ring-1 ring-white/30 backdrop-blur">
                    <x-icon name="zap" class="size-5" />
                </span>
                <div>
                    <h1 class="text-sm font-black">استوری‌های فروشگاه</h1>
                    <p class="mt-0.5 text-[10.5px] leading-4 text-primary-100">مثل اینستاگرام — تصویر یا ویدیو، محصول و لینک</p>
                </div>
            </div>
            <div class="text-center">
                <div class="text-lg font-black leading-6">{{ \App\Support\Fmt::fa($runningCount) }}<span class="text-xs font-bold text-primary-200">/{{ \App\Support\Fmt::fa($activeLimit) }}</span></div>
                <div class="text-[9px] font-bold text-primary-200">استوری فعال</div>
            </div>
        </div>
        <div class="relative mt-3 h-1.5 overflow-hidden rounded-full bg-white/20" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $activeLimit }}" aria-valuenow="{{ $runningCount }}">
            <div class="h-full rounded-full bg-white transition-all" style="width: {{ min(100, (int) ($runningCount / max(1, $activeLimit) * 100)) }}%"></div>
        </div>
        <button type="button" wire:click="openCreate"
                class="relative mt-3 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-xs font-black text-primary-700 shadow-md transition hover:bg-primary-50 active:scale-[0.98]">
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
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-sm font-extrabold">استوری‌های من</h2>
            <span class="text-[10px] font-semibold text-slate-400">
                @if ($moderation === 'manual')
                    انتشار پس از تایید مدیر
                @else
                    انتشار خودکار
                @endif
            </span>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($stories as $story)
                <article wire:key="story-{{ $story->id }}"
                         class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
                    {{-- media — Instagram 4:5 preview with overlays --}}
                    <div class="relative aspect-[4/5] overflow-hidden bg-slate-100 dark:bg-slate-800">
                        @if ($story->type === \App\Models\Story::TYPE_VIDEO)
                            <span class="grid size-full place-items-center bg-gradient-to-br from-slate-700 to-slate-900">
                                <span class="grid size-12 place-items-center rounded-full bg-white/15 ring-1 ring-white/30 backdrop-blur">
                                    <x-icon name="play" class="size-6 text-white/90" />
                                </span>
                            </span>
                        @elseif ($story->media_path)
                            <img src="{{ $story->mediaUrl() }}" alt="استوری من" loading="lazy"
                                 class="size-full object-cover transition duration-300 group-hover:scale-[1.04]">
                        @else
                            <span class="grid size-full place-items-center"><x-icon name="zap" class="size-7 text-slate-300 dark:text-slate-600" /></span>
                        @endif

                        {{-- status + age — glass pills over media --}}
                        <span class="absolute start-2 top-2 rounded-full px-2 py-0.5 text-[8.5px] font-black shadow {{ $story->stateClass() }}">{{ $story->stateLabel() }}</span>
                        <span class="absolute end-2 top-2 rounded-full bg-slate-950/55 px-2 py-0.5 text-[8px] font-bold text-white backdrop-blur-sm">{{ \App\Support\JDate::ago($story->created_at) }}</span>

                        @if ($story->type === \App\Models\Story::TYPE_VIDEO)
                            <span class="absolute bottom-2 end-2 rounded-full bg-slate-950/55 px-1.5 py-0.5 text-[8px] font-black text-white backdrop-blur-sm">ویدیو</span>
                        @endif

                        {{-- stats bar on media bottom --}}
                        <div class="absolute inset-x-0 bottom-0 flex items-center gap-1.5 bg-gradient-to-t from-slate-950/75 via-slate-950/35 to-transparent px-2.5 pb-2 pt-8">
                            <span class="inline-flex items-center gap-0.5 rounded-full bg-white/15 px-1.5 py-0.5 text-[8.5px] font-bold text-white backdrop-blur-sm" title="بازدید">
                                <x-icon name="eye" class="size-2.5" />{{ \App\Support\Fmt::fa($story->views) }}
                            </span>
                            <span class="inline-flex items-center gap-0.5 rounded-full bg-white/15 px-1.5 py-0.5 text-[8.5px] font-bold text-white backdrop-blur-sm" title="لایک">
                                <x-icon name="heart" class="size-2.5" />{{ \App\Support\Fmt::fa($story->likes_count) }}
                            </span>
                            <span class="inline-flex items-center gap-0.5 rounded-full bg-white/15 px-1.5 py-0.5 text-[8.5px] font-bold text-white backdrop-blur-sm" title="نظر">
                                <x-icon name="message" class="size-2.5" />{{ \App\Support\Fmt::fa($story->comments_count) }}
                            </span>
                            @if (! $story->likes_enabled || ! $story->comments_enabled)
                                <span class="ms-auto rounded-full bg-white/15 px-1.5 py-0.5 text-[8px] font-black text-white/90 backdrop-blur-sm">
                                    {{ trim((! $story->likes_enabled ? 'بدون لایک' : '').' '.(! $story->likes_enabled && ! $story->comments_enabled ? '·' : '').' '.(! $story->comments_enabled ? 'بدون نظر' : '')) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- body: attachment chips + window + actions --}}
                    <div class="space-y-2 p-2.5">
                        @if ($story->caption)
                            <p class="line-clamp-1 text-[10px] font-bold text-slate-600 dark:text-slate-300" title="{{ $story->caption }}">{{ $story->caption }}</p>
                        @endif
                        @if ($story->product)
                            <span class="flex items-center gap-1 rounded-lg bg-slate-50 px-2 py-1 text-[9px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300" title="{{ $story->product->name }}">
                                <x-icon name="package" class="size-3 shrink-0 text-primary-500" /><span class="truncate">{{ $story->product->name }}</span>
                            </span>
                        @endif
                        @if ($story->link_url)
                            <span class="flex items-center gap-1 rounded-lg bg-primary-50 px-2 py-1 text-[9px] font-bold text-primary-600 dark:bg-primary-950/40 dark:text-primary-300" title="{{ $story->link_title ?: $story->link_url }}">
                                <x-icon name="external-link" class="size-3 shrink-0" /><span class="truncate">{{ $story->link_title ?: $story->link_url }}</span>
                            </span>
                        @endif
                        <span class="flex items-center gap-1 text-[8.5px] font-semibold text-slate-400">
                            <x-icon name="clock" class="size-3 shrink-0" />
                            <span class="truncate">{{ \App\Support\JDate::dateTime($story->starts_at) }} ← {{ \App\Support\JDate::dateTime($story->ends_at) }}</span>
                        </span>
                        <div class="flex items-center gap-1.5 pt-0.5">
                            <button type="button" wire:click="openEdit({{ $story->id }})"
                                    class="inline-flex flex-1 items-center justify-center gap-1 rounded-xl border border-slate-200 py-1.5 text-[10px] font-extrabold text-slate-600 transition hover:bg-slate-50 active:scale-95 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                <x-icon name="edit" class="size-3" /> ویرایش
                            </button>
                            <button type="button" wire:click="destroy({{ $story->id }})" wire:confirm="این استوری حذف شود؟"
                                    class="grid size-7 place-items-center rounded-xl border border-rose-200 text-rose-500 transition hover:bg-rose-50 active:scale-95 dark:border-rose-900 dark:hover:bg-rose-950/40"
                                    aria-label="حذف استوری">
                                <x-icon name="trash" class="size-3.5" />
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-3xl border-2 border-dashed border-slate-200 bg-gradient-to-b from-slate-50 to-white p-8 text-center dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
                    <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                        <x-icon name="zap" class="size-6" />
                    </span>
                    <p class="mt-4 text-xs font-extrabold text-slate-600 dark:text-slate-300">هنوز استوری‌ای ندارید</p>
                    <p class="mt-1 text-[10.5px] font-semibold text-slate-400">اولین استوری فروشگاه‌تان را منتشر کنید و فروش بیشتری ببینید.</p>
                    <button type="button" wire:click="openCreate"
                            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-[11px] font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
                        <x-icon name="plus" class="size-3.5" />
                        انتشار اولین استوری
                    </button>
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
