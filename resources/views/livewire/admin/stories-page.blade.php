<div class="ip-fade-up space-y-4" @scroll-to-top.window="window.scrollTo({ top: 0, behavior: 'smooth' })">

    {{-- ── header + create toggle ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-base font-black">استوری‌ها</h1>
            <p class="mt-0.5 text-xs text-slate-400">استوری‌های فروشگاه‌ها — تایید صف، انتشار دستیِ مدیر و حذف</p>
        </div>
        <button type="button" wire:click="toggleCreate"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
            <x-icon name="{{ $creating || $editing ? 'minus' : 'plus' }}" class="size-4" />
            {{ $creating || $editing ? 'بستن فرم' : 'افزودن استوری' }}
        </button>
    </div>

    {{-- ── create / edit form (shared — v0.21.0 adds edit mode) ── --}}
    @if ($creating || $editing)
        <div class="rounded-2xl border border-primary-200 bg-white p-5 shadow-sm dark:border-primary-900/60 dark:bg-slate-900" x-data x-effect="if ($wire.editing) $el.scrollIntoView({ behavior: 'smooth', block: 'start' })">
            <h2 class="text-sm font-extrabold">{{ $editing ? 'ویرایش استوری #'.\App\Support\Fmt::fa((string) $editingId) : 'استوری جدید برای فروشگاه' }}</h2>
            <p class="mt-1 text-[11px] text-slate-400">
                @if ($editing)
                    فروشگاه و وضعیت تأیید ثابت می‌مانند؛ سایر جزئیات و پنجرهٔ نمایش قابل ویرایش است.
                @else
                    استوریِ ثبت‌شده از پنل مدیریت مستقیم منتشر می‌شود (بدون صف تایید).
                @endif
            </p>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                {{-- shop + product --}}
                <div class="space-y-3">
                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">فروشگاه @if ($editing)<span class="text-[9px] font-black text-slate-400">(ثابت)</span>@else * @endif</span>
                        @if ($editing)
                            <span class="mt-1.5 flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-100/70 px-3 py-2.5 text-xs font-extrabold text-slate-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400">
                                <x-icon name="lock" class="size-3.5 shrink-0" />
                                {{ $this->editStory?->shop?->name ?? '—' }}
                            </span>
                        @else
                            <select wire:model.live="shopId" wire:loading.attr="disabled"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                                <option value="">انتخاب فروشگاه…</option>
                                @foreach ($shopOptions as $shopOption)
                                    <option value="{{ $shopOption->id }}">{{ $shopOption->name }}</option>
                                @endforeach
                            </select>
                            @error('shopId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        @endif
                    </label>

                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">محصول متصل (اختیاری)</span>
                        @if ($productId !== '' && ctype_digit($productId))
                            @php $adminPickedProduct = \App\Models\Product::query()->find((int) $productId); @endphp
                            @if ($adminPickedProduct)
                                <div class="mt-1.5 flex items-center justify-between gap-2 rounded-xl border border-primary-200 bg-primary-50/60 px-3 py-2.5 dark:border-primary-900 dark:bg-primary-950/30">
                                    <span class="flex min-w-0 items-center gap-2 text-xs font-extrabold text-primary-700 dark:text-primary-300">
                                        <x-icon name="package" class="size-3.5 shrink-0" />
                                        <span class="truncate">{{ $adminPickedProduct->name }}</span>
                                    </span>
                                    <button type="button" wire:click="$set('productId', '')" aria-label="برداشتن محصول"
                                            class="grid size-6 shrink-0 place-items-center rounded-lg text-primary-500 transition hover:bg-primary-100 active:scale-90 dark:hover:bg-primary-900/50">
                                        <x-icon name="x" class="size-3.5" />
                                    </button>
                                </div>
                            @endif
                        @elseif ($shopId !== '')
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
                                                wire:key="apopt-{{ $productOption->id }}"
                                                class="block w-full truncate rounded-lg px-2.5 py-2 text-start text-xs font-semibold text-slate-600 transition hover:bg-primary-50 hover:text-primary-700 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">
                                            {{ $productOption->name }}
                                        </button>
                                    @empty
                                        <p class="px-2.5 py-2 text-[10px] font-bold text-slate-400">محصولی پیدا نشد.</p>
                                    @endforelse
                                </div>
                            </div>
                        @else
                            <p class="mt-1.5 rounded-xl border border-dashed border-slate-200 px-3 py-2.5 text-[10px] font-bold text-slate-400 dark:border-slate-700">اول فروشگاه را انتخاب کنید.</p>
                        @endif
                        @error('productId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>

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

                {{-- media + caption + window --}}
                <div class="space-y-3">
                    {{-- type picker --}}
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

                    @if ($type === 'image')
                        <div class="flex items-start gap-3">
                            <label class="flex-1">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                    @if ($editing)
                                        تصویر جدید (اختیاری — خالی = نگه‌داشتن تصویر فعلی)
                                    @else
                                        تصویر استوری * (پیشنهادی: عمودی ۹:۱۶)
                                    @endif
                                </span>
                                <input type="file" accept="image/jpeg,image/png,image/webp" wire:model.live="media"
                                       class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs file:me-3 file:rounded-lg file:border-0 file:bg-primary-600 file:px-3 file:py-1.5 file:text-[10px] file:font-bold file:text-white shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                                @error('media') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                <span wire:loading wire:target="media" class="mt-1 block text-[10px] font-semibold text-primary-500">در حال بارگذاری…</span>
                            </label>
                            @if ($media)
                                <img src="{{ $media->temporaryUrl() }}" alt="پیش‌نمایش تصویر جدید"
                                     class="h-24 w-16 rounded-xl border border-slate-200 object-cover dark:border-slate-700">
                            @elseif ($editing && $this->editStory?->media_path)
                                <span class="grid h-24 w-16 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800" title="تصویر فعلی">
                                    <img src="{{ $this->editStory->mediaUrl() }}" alt="تصویر فعلی استوری" class="size-full object-cover">
                                </span>
                            @endif
                        </div>
                    @else
                        <label class="block">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">لینک مستقیم ویدیو * (mp4، webm، ogg یا m3u8 — ویدیو آپلود نمی‌شود)</span>
                            <input type="url" dir="ltr" wire:model="videoUrl" placeholder="https://example.com/story.mp4"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            @error('videoUrl') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </label>
                    @endif

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

                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">متن استوری (اختیاری)</span>
                        <textarea wire:model="caption" rows="2" maxlength="500" placeholder="توضیح کوتاه روی استوری…"
                                  class="mt-1.5 w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800"></textarea>
                        @error('caption') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>

                    {{-- window — in edit mode the radios are hidden: the window
                         is always edited as explicit start/end datetimes --}}
                    <div class="grid grid-cols-2 gap-2">
                        <label class="col-span-2 text-[11px] font-bold text-slate-500 dark:text-slate-400">شروع</span>
                        @if (! $editing)
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
                        @endif
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
                        @if (! $editing)
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
                        @endif
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
                @if ($editing)
                    <button type="button" wire:click="cancelEdit"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        انصراف
                    </button>
                    <button type="button" wire:click="updateStory" wire:loading.attr="disabled" wire:target="updateStory"
                            class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                        <x-icon name="check" class="size-4" />
                        ذخیرهٔ تغییرات
                    </button>
                @else
                    <button type="button" wire:click="toggleCreate"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        انصراف
                    </button>
                    <button type="button" wire:click="createStory" wire:loading.attr="disabled" wire:target="createStory"
                            class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                        <x-icon name="zap" class="size-4" />
                        انتشار استوری
                    </button>
                @endif
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

    {{-- ── shop filter (searchable) — client v0.17.15: «مشخص شود کدام
         فروشگاه استوری را گزاشته و فیلتر فروشگاه با سرچ فروشگاه هم باشه»
         (same picker pattern as the create-form product search) --}}
    <div class="flex flex-wrap items-center gap-2">
        <div class="relative w-full max-w-xs" x-data="{ open: false }" @click.outside="open = false">
            @if ($filterShop)
                <div class="flex items-center justify-between gap-2 rounded-xl border border-primary-200 bg-primary-50/60 px-3 py-2.5 shadow-sm dark:border-primary-900 dark:bg-primary-950/30">
                    <span class="flex min-w-0 items-center gap-2 text-xs font-extrabold text-primary-700 dark:text-primary-300">
                        @if ($filterShop->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($filterShop->logo_path) }}" alt="" class="size-6 shrink-0 rounded-full object-cover">
                        @else
                            <span class="grid size-6 shrink-0 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-[9px] font-black text-white">{{ mb_substr($filterShop->name, 0, 1) }}</span>
                        @endif
                        <span class="truncate">استوری‌های {{ $filterShop->name }}</span>
                    </span>
                    <button type="button" wire:click="$set('shopFilter', '')" aria-label="حذف فیلتر فروشگاه"
                            class="grid size-6 shrink-0 place-items-center rounded-lg text-primary-500 transition hover:bg-primary-100 active:scale-90 dark:hover:bg-primary-900/50">
                        <x-icon name="x" class="size-3.5" />
                    </button>
                </div>
            @else
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm focus-within:border-primary-400 dark:border-slate-700 dark:bg-slate-900">
                    <x-icon name="search" class="size-3.5 shrink-0 text-slate-400" />
                    <input type="text" wire:model.live.debounce.300ms="shopSearch" x-on:focus="open = true"
                           placeholder="فیلتر بر اساس فروشگاه (جستجو)…"
                           class="w-full bg-transparent text-xs outline-none placeholder:text-slate-400"
                           aria-label="جستجوی فروشگاه برای فیلتر">
                </label>
                <div x-show="open" x-cloak x-transition.opacity
                     class="scroll-slim absolute inset-x-0 top-full z-30 mt-1 max-h-52 space-y-0.5 overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                    @forelse ($shopFilterOptions as $filterOption)
                        <button type="button" wire:click="$set('shopFilter', {{ $filterOption->id }})"
                                wire:key="sfopt-{{ $filterOption->id }}"
                                class="block w-full rounded-lg px-2.5 py-2 text-start text-xs font-semibold text-slate-600 transition hover:bg-primary-50 hover:text-primary-700 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">
                            <span class="block truncate">{{ $filterOption->name }}</span>
                            @if ($filterOption->instagram_username)
                                <span class="block truncate text-[10px] font-semibold text-slate-400">{{ $filterOption->instagram_username }}</span>
                            @endif
                        </button>
                    @empty
                        <p class="px-2.5 py-2 text-[10px] font-bold text-slate-400">فروشگاهی پیدا نشد.</p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    {{-- ── list ── --}}
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($stories as $story)
            <div wire:key="story-{{ $story->id }}"
                 class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <a href="{{ $story->type === \App\Models\Story::TYPE_VIDEO && $story->videoUrl() ? $story->videoUrl() : ($story->media_path ? $story->mediaUrl() : '#') }}" target="_blank" rel="noopener"
                   class="relative block h-32 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                    @if ($story->type === \App\Models\Story::TYPE_VIDEO)
                        <span class="grid size-full place-items-center bg-gradient-to-br from-slate-700 to-slate-900">
                            <x-icon name="play" class="size-6 text-white/85" />
                        </span>
                        <span class="absolute bottom-1 left-1/2 -translate-x-1/2 rounded-full bg-black/60 px-1.5 py-0.5 text-[8px] font-black text-white">ویدیو</span>
                    @elseif ($story->media_path)
                        <img src="{{ $story->mediaUrl() }}" alt="استوری {{ $story->shop->name }}" loading="lazy" class="h-full w-full object-cover">
                    @else
                        <span class="grid size-full place-items-center"><x-icon name="zap" class="size-5 text-slate-300 dark:text-slate-600" /></span>
                    @endif
                </a>

                <div class="flex min-w-0 flex-1 flex-col">
                    <div class="flex items-center justify-between gap-2">
                        {{-- shop identity (client v0.17.15: «مشخص شود کدام فروشگاه
                             استوری را گزاشته») — avatar + name + @handle --}}
                        <span class="flex min-w-0 items-center gap-1.5">
                            @if ($story->shop->logo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($story->shop->logo_path) }}" alt="" class="size-6 shrink-0 rounded-full object-cover">
                            @else
                                <span class="grid size-6 shrink-0 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-[9px] font-black text-white">{{ mb_substr($story->shop->name, 0, 1) }}</span>
                            @endif
                            <span class="min-w-0">
                                <span class="block truncate text-xs font-extrabold">{{ $story->shop->name }}</span>
                                @if ($story->shop->instagram_username)
                                    <span class="block truncate text-[9px] font-semibold text-slate-400" dir="ltr">{{ $story->shop->instagram_username }}</span>
                                @endif
                            </span>
                        </span>
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
                            <a href="{{ $story->link_url }}" target="_blank" rel="noopener" class="block truncate text-primary-500 hover:underline"><x-icon name="external-link" class="me-1 inline size-3" />{{ $story->link_title ?: $story->link_url }}</a>
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
                        <span class="flex-1"></span>
                        {{-- edit (v0.21.0) — the shared form switches into edit mode --}}
                        <button type="button" wire:click="openEdit({{ $story->id }})"
                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1.5 text-[10px] font-extrabold text-slate-600 transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700 active:scale-95 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                            <x-icon name="edit" class="size-3" /> ویرایش
                        </button>
                        <button type="button" wire:click="destroy({{ $story->id }})"
                                wire:confirm="این استوری برای همیشه حذف شود؟"
                                class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2.5 py-1.5 text-[10px] font-extrabold text-rose-600 transition hover:bg-rose-50 active:scale-95 dark:border-rose-900 dark:hover:bg-rose-950/40">
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
