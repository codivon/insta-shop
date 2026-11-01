<div class="ip-fade-up"
     x-data="{
        tab: 'info',
        catOpen: false,
        catQuery: '',
     }"
     x-effect="if (tab === 'pricing' && $wire.productType === 'downloadable') tab = 'files'"
     x-on:scroll-specs-bottom.window="$nextTick(() => { const el = document.getElementById('specs-editor-end'); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }) })">

    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('seller.shop') }}" wire:navigate
                   class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                   aria-label="بازگشت به فروشگاه من">
                    <x-icon name="arrow-right" class="size-5" />
                </a>
                <div>
                    <div class="text-sm font-extrabold leading-5">{{ $productId ? 'ویرایش محصول' : 'افزودن محصول' }}</div>
                    <div class="text-[11px] text-slate-400">{{ $productType === 'downloadable' ? 'محصول دانلودی' : 'محصول فیزیکی' }}</div>
                </div>
            </div>
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>

        {{-- ───────── Tabs (دانلودی → تب قیمت‌گذاری نمایش داده نمی‌شود) ───────── --}}
        <div class="flex gap-1 px-4 pb-2" role="tablist" aria-label="بخش‌های فرم محصول">
            <button type="button" @click="tab = 'info'" :aria-selected="tab === 'info'" role="tab"
                    class="flex-1 rounded-xl px-2 py-2 text-[11px] font-extrabold transition"
                    :class="tab === 'info'
                        ? 'bg-primary-50 text-primary-700 shadow-sm dark:bg-primary-950/60 dark:text-primary-300'
                        : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                اطلاعات محصول
            </button>
            @if ($productType === 'physical')
                <button type="button" @click="tab = 'pricing'" :aria-selected="tab === 'pricing'" role="tab"
                        class="flex-1 rounded-xl px-2 py-2 text-[11px] font-extrabold transition"
                        :class="tab === 'pricing'
                            ? 'bg-primary-50 text-primary-700 shadow-sm dark:bg-primary-950/60 dark:text-primary-300'
                            : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                    قیمت و تنوع‌ها
                    <span class="rounded-full bg-primary-100 px-1.5 text-[9px] font-black dark:bg-primary-950">{{ \App\Support\Fmt::fa(count($variants)) }}</span>
                </button>
            @endif
            @if ($productType === 'downloadable')
                <button type="button" @click="tab = 'files'" :aria-selected="tab === 'files'" role="tab"
                        class="flex-1 rounded-xl px-2 py-2 text-[11px] font-extrabold transition"
                        :class="tab === 'files'
                            ? 'bg-primary-50 text-primary-700 shadow-sm dark:bg-primary-950/60 dark:text-primary-300'
                            : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                    فایل‌های دانلودی
                </button>
            @endif
        </div>
    </div>

    <form wire:submit="save" class="px-4 pt-4">

        {{-- ═══════════════ TAB 1 · اطلاعات محصول ═══════════════ --}}
        <div x-show="tab === 'info'" x-cloak class="space-y-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="package" class="size-3.5" /></span>
                    مشخصات محصول
                </h3>

                <div class="mt-3 space-y-3">
                    <div>
                        <label for="pf-name" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">نام محصول <span class="text-rose-500">*</span></label>
                        <input id="pf-name" type="text" wire:model.blur="name" maxlength="150" placeholder="مثال: زعفران سرگل قائنات — ۱۰ گرم"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                        @error('name') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="pf-desc" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">توضیحات</label>
                        <textarea id="pf-desc" wire:model.blur="description" rows="4" maxlength="5000"
                                  placeholder="ویژگی‌ها، جنس، ابعاد، نحوه ارسال..."
                                  class="scroll-slim w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold leading-6 outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800"></textarea>
                        @error('description') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- type + category --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="settings" class="size-3.5" /></span>
                    نوع و دسته‌بندی
                </h3>

                <div class="mt-3 grid grid-cols-2 gap-2" role="group" aria-label="نوع محصول">
                    <button type="button" wire:click="$set('productType', 'physical')"
                            class="rounded-2xl border-2 p-3 text-right transition {{ $productType === 'physical'
                                ? 'border-primary-500 bg-primary-50/60 shadow-sm shadow-primary-600/10 dark:border-primary-600 dark:bg-primary-950/40'
                                : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900' }}">
                        <div class="flex items-center gap-2">
                            <span class="grid size-8 place-items-center rounded-xl {{ $productType === 'physical' ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                <x-icon name="package" class="size-4" />
                            </span>
                            <span class="text-[11px] font-extrabold">فیزیکی</span>
                        </div>
                        <p class="mt-1.5 text-[9px] leading-4 text-slate-400">کالای ارسالی با وزن و موجودی</p>
                    </button>
                    <button type="button" wire:click="$set('productType', 'downloadable')"
                            class="rounded-2xl border-2 p-3 text-right transition {{ $productType === 'downloadable'
                                ? 'border-primary-500 bg-primary-50/60 shadow-sm shadow-primary-600/10 dark:border-primary-600 dark:bg-primary-950/40'
                                : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900' }}">
                        <div class="flex items-center gap-2">
                            <span class="grid size-8 place-items-center rounded-xl {{ $productType === 'downloadable' ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                <x-icon name="download" class="size-4" />
                            </span>
                            <span class="text-[11px] font-extrabold">دانلودی</span>
                        </div>
                        <p class="mt-1.5 text-[9px] leading-4 text-slate-400">فایل — بدون ارسال فیزیکی</p>
                    </button>
                </div>
                @error('productType') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                {{-- ── دسته‌بندی با جستجو (combobox) ── --}}
                <div class="mt-3">
                    <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">دسته‌بندی</label>
                    <div class="relative" @click.outside="catOpen = false">
                        <button type="button" @click="catOpen = !catOpen" :aria-expanded="catOpen"
                                class="flex w-full items-center justify-between gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition hover:border-slate-300 focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            <span class="truncate" :class="$wire.categoryId > 0 ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400'"
                                  x-text="$wire.categoryId > 0 ? (({{ str_replace('"', "'", $shopCategories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->toJson()) }}).find(c => c.id === $wire.categoryId)?.name ?? 'بدون دسته') : 'بدون دسته — انتخاب کنید'">
                                انتخاب دسته‌بندی
                            </span>
                            <span class="shrink-0 text-slate-400 transition-transform" :class="catOpen ? 'rotate-180' : ''">
                                <x-icon name="chevron-down" class="size-4" />
                            </span>
                        </button>

                        <div x-cloak x-show="catOpen"
                             x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="absolute inset-x-0 top-full z-30 mt-1.5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                            <div class="border-b border-slate-100 p-2 dark:border-slate-800">
                                <div class="relative">
                                    <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                                    <input type="search" x-model="catQuery" placeholder="جستجوی دسته‌بندی..." x-ref="catSearch"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-9 pl-3 text-[11px] font-semibold outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                                </div>
                            </div>
                            <div class="scroll-slim max-h-44 overflow-y-auto p-1.5">
                                <button type="button" x-show="catQuery.trim() === ''"
                                        @click="$wire.set('categoryId', 0); catOpen = false"
                                        class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-[11px] font-bold transition"
                                        :class="$wire.categoryId === 0 ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300' : 'text-slate-500 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'">
                                    <x-icon name="circle-slash" class="size-3.5 opacity-50" />
                                    بدون دسته
                                </button>
                                @foreach ($shopCategories as $cat)
                                    <button type="button" x-show="catQuery.trim() === '' || '{{ e($cat->name) }}'.includes(catQuery.trim())" wire:key="cat-opt-{{ $cat->id }}"
                                            @click="$wire.set('categoryId', {{ $cat->id }}); catOpen = false"
                                            class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-[11px] font-bold transition"
                                            :class="$wire.categoryId === {{ $cat->id }} ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300' : 'text-slate-500 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'">
                                        <x-icon name="tag" class="size-3.5 opacity-50" />
                                        {{ $cat->name }}
                                        @if ($wire.categoryId === $cat->id)
                                            <x-icon name="circle-check" class="mr-auto size-3.5 text-primary-600" />
                                        @endif
                                    </button>
                                @endforeach
                                @if ($shopCategories->isEmpty())
                                    <p class="px-3 py-4 text-center text-[10px] font-bold text-slate-400">دسته‌بندی‌ای نساخته‌اید — از «مدیریت دسته‌بندی‌ها» اضافه کنید.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @error('categoryId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                @if ($productType === 'physical')
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div>
                            <label for="pf-weight" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">وزن (گرم)</label>
                            <input id="pf-weight" type="text" inputmode="numeric" dir="ltr" wire:model="weight" placeholder="500"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                            @error('weight') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="pf-unit" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">واحد</label>
                            <input id="pf-unit" type="text" wire:model="unit" maxlength="20" list="unit-suggestions" placeholder="تعداد"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                            <datalist id="unit-suggestions">
                                <option value="تعداد"><option value="کیلوگرم"><option value="گرم"><option value="متر"><option value="لیتر"><option value="بسته"><option value="جین">
                            </datalist>
                            @error('unit') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif
            </div>

            {{-- ═══ مشخصات محصول — خلاصه + مدیریت با مودال ═══ --}}
            @php
                $specItemsFlat = [];
                foreach ($specifications as $specGroup) {
                    $groupName = trim((string) ($specGroup['name'] ?? ''));
                    foreach (($specGroup['items'] ?? []) as $specItem) {
                        $itemTitle = trim((string) ($specItem['title'] ?? ''));
                        $itemValue = trim((string) ($specItem['value'] ?? ''));
                        if ($itemTitle !== '' || $itemValue !== '') {
                            $specItemsFlat[] = ['g' => $groupName, 't' => $itemTitle, 'v' => $itemValue];
                        }
                    }
                }
                $hasSpecContent = count($specItemsFlat) > 0 || trim($specType) !== '';
            @endphp
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="flex items-center gap-2 text-xs font-extrabold">
                        <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="list" class="size-3.5" /></span>
                        مشخصات محصول
                    </h3>
                    @if (count($specItemsFlat) > 0)
                        <span class="shrink-0 rounded-full bg-primary-50 px-2.5 py-1 text-[9px] font-black text-primary-600 dark:bg-primary-950 dark:text-primary-300">
                            {{ \App\Support\Fmt::fa(count($specItemsFlat)) }} مشخصه
                        </span>
                    @endif
                </div>

                @if ($hasSpecContent)
                    <div class="mt-3 space-y-2">
                        @if (trim($specType) !== '')
                            <p class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                <x-icon name="folder" class="size-3.5 shrink-0 text-primary-500" />
                                نوع مشخصات:
                                <span class="font-extrabold text-slate-700 dark:text-slate-200">{{ $specType }}</span>
                            </p>
                        @endif
                        @if (count($specItemsFlat) > 0)
                            <div class="flex flex-wrap gap-1.5">
                                @foreach (array_slice($specItemsFlat, 0, 8) as $chip)
                                    <span class="inline-flex max-w-full items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[9.5px] font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                                        <span class="max-w-[9rem] truncate">{{ $chip['t'] !== '' ? $chip['t'] : '—' }}</span>
                                        <span class="text-[8px] text-slate-300 dark:text-slate-600">•</span>
                                        <span class="max-w-[9rem] truncate font-semibold text-slate-400">{{ $chip['v'] !== '' ? $chip['v'] : '—' }}</span>
                                    </span>
                                @endforeach
                                @if (count($specItemsFlat) > 8)
                                    <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-1 text-[9.5px] font-black text-primary-600 dark:bg-primary-950/60 dark:text-primary-300">
                                        +{{ \App\Support\Fmt::fa(count($specItemsFlat) - 8) }} مشخصه دیگر
                                    </span>
                                @endif
                            </div>
                        @else
                            <p class="text-[10px] font-bold text-slate-400">نوع انتخاب شده اما هنوز مشخصه‌ای ثبت نشده است.</p>
                        @endif
                    </div>
                @else
                    <div class="mt-3 rounded-2xl border border-dashed border-slate-300 px-4 py-5 text-center dark:border-slate-700">
                        <x-icon name="list" class="mx-auto size-7 text-slate-300" />
                        <p class="mt-2 text-[11px] font-bold text-slate-400">مشخصاتی ثبت نشده است.</p>
                        <p class="mt-1 text-[10px] leading-5 text-slate-400">با «مدیریت مشخصات» یک نوع ذخیره‌شده را انتخاب کنید یا گروه و مشخصه بسازید — مثل «اندازه: ۶.۷ اینچ».</p>
                    </div>
                @endif

                <button type="button" wire:click="openSpecModal" wire:loading.attr="disabled"
                        class="mt-3 flex w-full items-center justify-center gap-2 rounded-2xl border border-primary-200 bg-primary-50/60 py-2.5 text-[11px] font-extrabold text-primary-600 transition hover:border-primary-400 hover:bg-primary-50 dark:border-primary-800/60 dark:bg-primary-950/30 dark:text-primary-400 dark:hover:bg-primary-950/50">
                    <span wire:loading wire:target="openSpecModal" class="size-4 animate-spin rounded-full border-2 border-primary-300 border-t-primary-600"></span>
                    <span wire:loading.remove wire:target="openSpecModal"><x-icon name="sliders" class="size-4" /></span>
                    {{ $hasSpecContent ? 'مدیریت مشخصات' : 'افزودن مشخصات' }}
                </button>
            </div>

            {{-- ── تصاویر محصول ── --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="camera" class="size-3.5" /></span>
                    تصاویر محصول
                    <span class="text-[10px] font-semibold text-slate-400">(حداکثر ۶ تصویر — اولین تصویر، شاخص محصول است)</span>
                </h3>

                @if ($existingImages->isNotEmpty())
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach ($existingImages as $img)
                            <div class="relative overflow-hidden rounded-xl border-2 {{ $loop->first ? 'border-primary-500' : 'border-slate-200 dark:border-slate-700' }}" wire:key="img-{{ $img->id }}">
                                <img src="{{ $img->url() }}" alt="تصویر محصول" class="aspect-square w-full object-cover">
                                @if ($loop->first)
                                    <span class="absolute right-1.5 top-1.5 flex items-center gap-1 rounded-full bg-primary-600 px-2 py-0.5 text-[9px] font-bold text-white shadow">شاخص</span>
                                @endif
                                <div class="absolute inset-x-1 bottom-1 flex gap-1">
                                    @unless ($loop->first)
                                        <button type="button" wire:click="makeMain({{ $img->id }})"
                                                class="flex-1 rounded-lg bg-white/90 py-1 text-[9px] font-bold text-slate-700 shadow backdrop-blur transition hover:bg-white">
                                            شاخص شود
                                        </button>
                                    @endunless
                                    <button type="button" wire:click="removeImage({{ $img->id }})" aria-label="حذف تصویر"
                                            class="grid place-items-center rounded-lg bg-rose-500/90 px-2 py-1 text-white shadow backdrop-blur transition hover:bg-rose-600">
                                        <x-icon name="trash" class="size-3.5" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <label class="mt-3 flex cursor-pointer flex-col items-center gap-1.5 rounded-2xl border-2 border-dashed border-slate-300 py-5 text-center transition hover:border-primary-400 hover:bg-primary-50/50 dark:border-slate-700 dark:hover:border-primary-600 dark:hover:bg-primary-950/20">
                    <x-icon name="image-plus" class="size-7 text-primary-400" />
                    <span class="text-xs font-extrabold text-slate-600 dark:text-slate-200">انتخاب تصویر از گالری</span>
                    <span class="text-[10px] font-semibold text-slate-400">JPG / PNG / WebP — حداکثر ۲ مگابایت</span>
                    <input type="file" wire:model="photos" accept="image/png,image/jpeg,image/webp" multiple class="sr-only">
                </label>
                <x-upload-progress target="photos" label="در حال آپلود تصاویر" />

                @if ($photos)
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach ($photos as $key => $photo)
                            <div class="relative overflow-hidden rounded-xl border-2 {{ $featuredPhotoKey === $key ? 'border-primary-500' : 'border-primary-300 dark:border-primary-700' }}" wire:key="new-{{ $key }}">
                                @if (method_exists($photo, 'isPreviewable') && $photo->isPreviewable())
                                    <img src="{{ $photo->temporaryUrl() }}" alt="پیش‌نمایش تصویر" class="aspect-square w-full object-cover">
                                @else
                                    <span class="grid aspect-square w-full place-items-center bg-slate-100 text-slate-400 dark:bg-slate-800">
                                        <x-icon name="image" class="size-6" />
                                    </span>
                                @endif
                                <button type="button" wire:click="setFeaturedPhoto({{ $key }})"
                                        class="absolute right-1.5 top-1.5 grid size-6 place-items-center rounded-full shadow transition {{ $featuredPhotoKey === $key ? 'bg-primary-600 text-white' : 'bg-white/90 text-slate-400 backdrop-blur hover:text-primary-600' }}"
                                        aria-label="انتخاب به عنوان تصویر شاخص" title="تصویر شاخص">
                                    <x-icon name="star" class="size-3.5 {{ $featuredPhotoKey === $key ? 'fill-white' : '' }}" />
                                </button>
                                <button type="button" wire:click="removeNewPhoto({{ $key }})" aria-label="حذف تصویر انتخابی"
                                        class="absolute left-1.5 top-1.5 grid size-6 place-items-center rounded-full bg-rose-500 text-white shadow transition hover:bg-rose-600">
                                    <x-icon name="x" class="size-3" />
                                </button>
                                @if ($featuredPhotoKey === $key)
                                    <span class="absolute inset-x-1 bottom-1 rounded-lg bg-primary-600/95 py-0.5 text-center text-[8.5px] font-black text-white">تصویر شاخص</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if ($featuredPhotoKey === null)
                        <p class="mt-2 flex items-center gap-1.5 text-[9.5px] font-bold text-slate-400">
                            <x-icon name="star" class="size-3 text-amber-400" />
                            با ستاره روی هر تصویر، آن را به‌عنوان «تصویر شاخص» محصول انتخاب کنید.
                        </p>
                    @endif
                @endif

                @error('photos') <p class="mt-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                @foreach (range(0, 5) as $photoKey)
                    @error('photos.'.$photoKey) <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                @endforeach
            </div>
        </div>

        {{-- ═══════════════ TAB 2 · قیمت و تنوع‌ها (فقط فیزیکی) ═══════════════ --}}
        @if ($productType === 'physical')
            <div x-show="tab === 'pricing'" x-cloak>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="flex items-center gap-2 text-xs font-extrabold">
                            <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="layers" class="size-3.5" /></span>
                            تنوع‌های محصول
                            <span class="rounded-full bg-primary-50 px-2 py-0.5 text-[9px] font-black text-primary-600 dark:bg-primary-950 dark:text-primary-300">{{ \App\Support\Fmt::fa(count($variants)) }}</span>
                        </h3>
                        <a href="{{ route('seller.shop.attributes') }}" wire:navigate
                           class="flex items-center gap-1 rounded-xl border border-slate-200 px-2.5 py-1.5 text-[10px] font-bold text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300">
                            <x-icon name="settings" class="size-3.5" />
                            مدیریت ویژگی‌ها
                        </a>
                    </div>
                    <p class="mt-1.5 text-[10px] leading-5 text-slate-400">برای هر ترکیب (رنگ/سایز/گارانتی و ...) یک تنوع با قیمت و موجودی جدا بسازید. افزودن و ویرایش با مودال انجام می‌شود.</p>

                    @error('variants') <p class="mt-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[10px] font-bold text-rose-600 dark:border-rose-900 dark:bg-rose-950/40">{{ $message }}</p> @enderror

                    {{-- ── لیست تنوع‌ها (نمایش خلاصه + ویرایش با مودال) ── --}}
                    <div class="mt-3 space-y-2">
                        @foreach ($variants as $i => $variant)
                            @php
                                $picks = [];
                                foreach ($shopAttributes as $attribute) {
                                    $picked = $variant['values'][$attribute->id] ?? ['value_id' => 0, 'text' => ''];
                                    if ($attribute->type === 'text') {
                                        if (trim($picked['text'] ?? '') !== '') $picks[] = ['label' => $picked['text'], 'hex' => null];
                                    } else {
                                        $pv = $attribute->values->firstWhere('id', (int) ($picked['value_id'] ?? 0));
                                        if ($pv) $picks[] = ['label' => $pv->value, 'hex' => $pv->color_hex];
                                    }
                                }
                            @endphp
                            <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-slate-50/60 p-3 transition hover:border-primary-200 dark:border-slate-700 dark:bg-slate-800/40" wire:key="variant-{{ $i }}">
                                <span class="grid size-7 shrink-0 place-items-center rounded-xl bg-primary-600 text-[10px] font-black text-white">{{ \App\Support\Fmt::fa($i + 1) }}</span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-1">
                                        @if (count($picks) > 0)
                                            @foreach ($picks as $pick)
                                                <span class="flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[9px] font-bold text-slate-600 shadow-sm dark:bg-slate-900 dark:text-slate-300">
                                                    @if ($pick['hex'])
                                                        <span class="size-2.5 rounded-full border border-slate-300" style="background: {{ $pick['hex'] }}"></span>
                                                    @endif
                                                    {{ $pick['label'] }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-[10px] font-bold text-slate-400">تنوع ساده</span>
                                        @endif
                                        @if ($variant['status'] === 'hidden')
                                            <span class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[8.5px] font-black text-slate-500 dark:bg-slate-700 dark:text-slate-300">مخفی</span>
                                        @endif
                                        @if (is_numeric($variant['discount']) && (int) $variant['discount'] > 0)
                                            <span class="rounded-full bg-rose-100 px-1.5 py-0.5 text-[8.5px] font-black text-rose-600 dark:bg-rose-950 dark:text-rose-400">{{ \App\Support\Fmt::fa((int) $variant['discount']) }}٪ تخفیف</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                        <span class="text-primary-600 dark:text-primary-400">{{ is_numeric($variant['price']) && $variant['price'] !== '' ? \App\Support\Fmt::money((int) $variant['price']).' تومان' : 'قیمت ثبت نشده' }}</span>
                                        <span>·</span>
                                        <span>موجودی: {{ $variant['stock'] !== '' ? \App\Support\Fmt::fa((int) $variant['stock']) : '—' }}</span>
                                        @if ((int) ($variant['min_purchase'] ?? 0) > 0 || (int) ($variant['max_purchase'] ?? 0) > 0)
                                            <span>·</span>
                                            <span>محدودیت خرید: {{ \App\Support\Fmt::fa((int) $variant['min_purchase']) }} تا {{ \App\Support\Fmt::fa((int) $variant['max_purchase']) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-1">
                                    <button type="button" wire:click="openVariantModal({{ $i }})"
                                            class="grid size-8 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300"
                                            aria-label="ویرایش تنوع">
                                        <x-icon name="edit" class="size-3.5" />
                                    </button>
                                    @if (count($variants) > 1)
                                        <button type="button" wire:click="removeVariant({{ $i }})" wire:loading.attr="disabled"
                                                class="grid size-8 place-items-center rounded-xl text-rose-500 transition hover:bg-rose-100 dark:hover:bg-rose-950/60" aria-label="حذف تنوع">
                                            <x-icon name="trash" class="size-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" wire:click="openVariantModal(null)" wire:loading.attr="disabled"
                            class="mt-3 flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-primary-300 py-3 text-[11px] font-extrabold text-primary-600 transition hover:border-primary-500 hover:bg-primary-50/60 dark:border-primary-700 dark:text-primary-400 dark:hover:bg-primary-950/30">
                        <span wire:loading wire:target="openVariantModal" class="size-4 animate-spin rounded-full border-2 border-primary-300 border-t-primary-600"></span>
                        <span wire:loading.remove wire:target="openVariantModal"><x-icon name="plus" class="size-4" /></span>
                        افزودن تنوع جدید
                    </button>
                </div>
            </div>
        @endif

        {{-- ═══════════════ TAB 3 · فایل‌های دانلودی ═══════════════ --}}
        @if ($productType === 'downloadable')
            <div x-show="tab === 'files'" x-cloak class="space-y-3">
                {{-- existing files (edit mode) --}}
                @foreach ($existingFiles as $existingFile)
                    <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="exfile-{{ $existingFile->id }}">
                        <div class="flex items-center gap-2.5">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                                <x-icon name="file-text" class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-[11px] font-extrabold">{{ $existingFile->title }}</div>
                                <div class="text-[9px] font-semibold text-slate-400">
                                    {{ \App\Support\Fmt::money($existingFile->finalPrice()) }} تومان
                                    @if ($existingFile->discount_percent > 0)
                                        <span class="mr-1 rounded-full bg-rose-100 px-1.5 py-0.5 text-[8px] font-black text-rose-600 dark:bg-rose-950 dark:text-rose-400">{{ \App\Support\Fmt::fa($existingFile->discount_percent) }}٪ تخفیف</span>
                                    @endif
                                    · {{ $existingFile->humanSize() }}
                                </div>
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-[9px] font-black {{ $existingFile->status === 'active'
                                    ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400'
                                    : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                {{ $existingFile->status === 'active' ? 'نمایش' : 'عدم نمایش' }}
                            </span>
                            <a href="{{ $existingFile->downloadUrl() }}" target="_blank" rel="noopener"
                               class="grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700"
                               aria-label="دانلود فایل">
                                <x-icon name="download" class="size-3.5" />
                            </a>
                            <button type="button" wire:click="removeExistingFile({{ $existingFile->id }})"
                                    class="grid size-8 place-items-center rounded-lg text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-950/40"
                                    aria-label="حذف فایل">
                                <x-icon name="trash" class="size-3.5" />
                            </button>
                        </div>
                    </div>
                @endforeach

                @if ($removedFileIds !== [])
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($removedFileIds as $removedId)
                            <button type="button" wire:click="undoRemoveExistingFile({{ $removedId }})" wire:key="rmfile-{{ $removedId }}"
                                    class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-600 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400">
                                <x-icon name="rotate" class="size-3" />
                                بازگردانی فایل حذف‌شده #{{ $removedId }}
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- new file rows --}}
                @foreach ($files as $i => $fileRow)
                    <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="file-row-{{ $i }}">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="flex items-center gap-2 text-[11px] font-extrabold">
                                <span class="grid size-6 place-items-center rounded-lg bg-primary-600 text-[10px] font-black text-white">{{ \App\Support\Fmt::fa($i + 1) }}</span>
                                فایل جدید
                            </h4>
                            @if (count($files) > 1 || $existingFiles->isNotEmpty())
                                <button type="button" wire:click="removeNewFile({{ $i }})"
                                        class="grid size-8 place-items-center rounded-lg text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-950/40" aria-label="حذف ردیف">
                                    <x-icon name="trash" class="size-4" />
                                </button>
                            @endif
                        </div>

                        <div class="mt-2.5 space-y-2.5">
                            <div>
                                <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">عنوان فایل <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="files.{{ $i }}.title" maxlength="150" placeholder="مثال: ویدیوی آموزش کامل"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                                @error("files.{$i}.title") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">قیمت (تومان) <span class="text-rose-500">*</span></label>
                                    <input type="text" inputmode="numeric" dir="ltr" wire:model="files.{{ $i }}.price" placeholder="0"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                                    @if (is_numeric($fileRow['price']) && (int) $fileRow['price'] > 0)
                                        <p class="mt-1 text-[9px] font-bold text-primary-500">
                                            {{ \App\Support\Fmt::money((int) $fileRow['price']) }}
                                            @if (is_numeric($fileRow['discount']) && (int) $fileRow['discount'] > 0)
                                                → پس از تخفیف: {{ \App\Support\Fmt::money((int) floor($fileRow['price'] * (100 - min(100, (int) $fileRow['discount'])) / 100)) }}
                                            @endif
                                        </p>
                                    @endif
                                    @error("files.{$i}.price") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">تخفیف (٪)</label>
                                    <input type="text" inputmode="numeric" dir="ltr" wire:model="files.{{ $i }}.discount" placeholder="0"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                                    @error("files.{$i}.discount") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <span class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">وضعیت</span>
                                <div class="grid grid-cols-2 gap-1.5" role="group">
                                    <button type="button" wire:click="$set('files.{{ $i }}.status', 'active')"
                                            class="rounded-xl border px-2 py-1.5 text-[10px] font-bold transition {{ $fileRow['status'] === 'active'
                                                ? 'border-emerald-400 bg-emerald-50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'
                                                : 'border-slate-200 bg-white text-slate-400 dark:border-slate-700 dark:bg-slate-900' }}">
                                        نمایش
                                    </button>
                                    <button type="button" wire:click="$set('files.{{ $i }}.status', 'hidden')"
                                            class="rounded-xl border px-2 py-1.5 text-[10px] font-bold transition {{ $fileRow['status'] === 'hidden'
                                                ? 'border-slate-400 bg-slate-200/70 text-slate-700 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200'
                                                : 'border-slate-200 bg-white text-slate-400 dark:border-slate-700 dark:bg-slate-900' }}">
                                        عدم نمایش
                                    </button>
                                </div>
                                @error("files.{$i}.status") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="flex cursor-pointer flex-col items-center gap-1.5 rounded-2xl border-2 border-dashed border-slate-300 py-4 text-center transition hover:border-primary-400 hover:bg-primary-50/50 dark:border-slate-700 dark:hover:border-primary-600 dark:hover:bg-primary-950/20">
                                    <x-icon name="upload" class="size-6 text-primary-400" />
                                    <span class="text-[11px] font-extrabold text-slate-600 dark:text-slate-200">انتخاب فایل دانلودی</span>
                                    <span class="text-[9px] font-semibold text-slate-400">حداکثر ۱۰۰ مگابایت — فایل روی هاست خصوصی ذخیره می‌شود</span>
                                    <input type="file" wire:model="files.{{ $i }}.file" class="sr-only">
                                </label>
                                <x-upload-progress target="files.{{ $i }}.file" label="در حال آپلود فایل" />
                                @if ($fileRow['file'])
                                    <p class="mt-1.5 flex items-center gap-1.5 rounded-xl bg-slate-50 px-3 py-1.5 text-[10px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300" wire:key="fname-{{ $i }}">
                                        <x-icon name="file-text" class="size-3.5 shrink-0 text-primary-500" />
                                        <span class="truncate">{{ $fileRow['file']->getClientOriginalName() }}</span>
                                    </p>
                                @endif
                                @error("files.{$i}.file") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                @endforeach

                <button type="button" wire:click="addFile"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-primary-300 py-3 text-[11px] font-extrabold text-primary-600 transition hover:border-primary-500 hover:bg-primary-50/60 dark:border-primary-700 dark:text-primary-400 dark:hover:bg-primary-950/30">
                    <x-icon name="plus" class="size-4" />
                    افزودن فایل جدید
                </button>
                @error('files') <p class="mt-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[10px] font-bold text-rose-600 dark:border-rose-900 dark:bg-rose-950/40">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- ───────── Sticky actions — بدون منوی پایین، چسبیده به زیر صفحه ───────── --}}
        <div class="sticky bottom-0 z-20 -mx-4 mt-4 border-t border-slate-200/80 bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/95">
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 rounded-2xl bg-gradient-to-l from-fuchsia-600 via-primary-600 to-orange-500 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:brightness-105 active:scale-[.98] disabled:opacity-60"
                        wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $productId ? 'ذخیره تغییرات' : 'افزودن محصول' }}</span>
                    <span wire:loading class="flex items-center justify-center gap-2" wire:target="save">
                        <span class="inline-block size-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        در حال ذخیره...
                    </span>
                </button>
                <a href="{{ route('seller.shop') }}" wire:navigate
                   class="grid place-items-center rounded-2xl border border-slate-200 bg-white px-5 text-xs font-bold text-slate-500 transition hover:border-slate-300 hover:text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    انصراف
                </a>
            </div>
        </div>
    </form>

    {{-- ═══════════════════ SPECS MODAL ═══════════════════ --}}
    <x-modal-panel open="$wire.specModalOpen" max-width="lg" z-class="z-[75]" labelledby="specs-modal-title" in-frame
                   close-js="__ipOpen = false; $wire.closeSpecModal()">

        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 id="specs-modal-title" class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="list" class="size-4" />
                    </span>
                    مدیریت مشخصات محصول
                </h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeSpecModal()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="px-5 py-4">
            {{-- ── نوع مشخصات (کتابخانه گروه‌های ذخیره‌شده) ── --}}
            <div class="mt-3" x-data="{ stOpen: false, stQuery: '' }" @click.outside="stOpen = false">
                <div class="mb-1.5 flex items-center justify-between gap-2">
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-300">نوع مشخصات</label>
                    @if (trim($specType) !== '')
                        <button type="button" wire:click="$set('specType', '')"
                                class="flex items-center gap-1 rounded-full bg-primary-50 px-2.5 py-1 text-[10px] font-extrabold text-primary-700 transition hover:bg-primary-100 dark:bg-primary-950/60 dark:text-primary-300"
                                title="حذف نوع (مشخصات آزاد می‌ماند)">
                            {{ $specType }}
                            <x-icon name="x" class="size-3" />
                        </button>
                    @endif
                </div>
                <div class="relative">
                    <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <x-icon name="search" class="size-3.5" />
                    </div>
                    <button type="button" @click="stOpen = !stOpen" :aria-expanded="stOpen"
                            class="flex w-full items-center justify-between gap-2 rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pr-9 pl-3 text-[11px] font-semibold outline-none transition hover:border-slate-300 focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                        <span class="truncate {{ trim($specType) !== '' ? 'font-bold text-slate-700 dark:text-slate-200' : 'text-slate-400' }}">
                            @if (trim($specType) !== '')
                                {{ $specType }} — گروه‌های همین نوع بارگذاری شده است
                            @else
                                انتخاب نوع ذخیره‌شده یا تایپ نوع جدید (مثل: گوشی موبایل)
                            @endif
                        </span>
                        <span class="shrink-0 text-slate-400 transition-transform" :class="stOpen ? 'rotate-180' : ''">
                            <x-icon name="chevron-down" class="size-4" />
                        </span>
                    </button>

                    <div x-cloak x-show="stOpen"
                         x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="absolute inset-x-0 top-full z-30 mt-1.5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                        <div class="border-b border-slate-100 p-2 dark:border-slate-800">
                            <input type="search" x-model="stQuery" placeholder="جستجوی نوع مشخصات..."
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-3 pl-3 text-[11px] font-semibold outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                        </div>
                        <div class="scroll-slim max-h-44 overflow-y-auto p-1.5">
                            @forelse ($specTemplates as $template)
                                <button type="button" x-show="stQuery.trim() === '' || '{{ e($template->title) }}'.includes(stQuery.trim())"
                                        wire:key="st-{{ $template->id }}"
                                        @click="stOpen = false; stQuery = ''"
                                        @if (count($specifications) > 0)
                                            wire:click="applySpecTemplate({{ $template->id }})"
                                            wire:confirm="با انتخاب این نوع، مشخصات فعلی جایگزین می‌شود. ادامه می‌دهید؟"
                                        @else
                                            wire:click="applySpecTemplate({{ $template->id }})"
                                        @endif
                                        class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-[11px] font-bold transition"
                                        :class="($wire.specType || '').trim() === '{{ e($template->title) }}' ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'">
                                    <x-icon name="folder" class="size-3.5 opacity-50" />
                                    {{ $template->title }}
                                    @if (trim($specType) === $template->title)
                                        <x-icon name="circle-check" class="mr-auto size-3.5 text-primary-600" />
                                    @endif
                                </button>
                            @empty
                                <p class="px-3 py-3 text-center text-[10px] font-bold text-slate-400">هنوز نوعی ذخیره نکرده‌اید — اولین نوع را با تایپ بسازید.</p>
                            @endforelse
                            @if (trim($specType) !== '')
                                <button type="button" x-show="stQuery.trim() === ''"
                                        @click="stOpen = false; stQuery = ''" wire:click="$set('specType', '')"
                                        class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-[11px] font-bold text-slate-500 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">
                                    <x-icon name="circle-slash" class="size-3.5 opacity-50" />
                                    بدون نوع (مشخصات این محصول آزاد بماند)
                                </button>
                            @endif
                        </div>
                        <div class="border-t border-slate-100 p-2 dark:border-slate-800" x-show="stQuery.trim() !== ''">
                            <button type="button"
                                    @click="$wire.set('specType', stQuery.trim()); stOpen = false; stQuery = ''"
                                    class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-primary-600 px-3 py-2 text-[11px] font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                                <x-icon name="plus" class="size-3.5" />
                                ساخت نوع جدید: «<span x-text="stQuery.trim()"></span>»
                            </button>
                        </div>
                    </div>
                </div>
                <p class="mt-1.5 text-[9.5px] leading-4 text-slate-400">
                    با انتخاب نوع، گروه‌های ذخیره‌شده همان نوع ظاهر می‌شوند؛ فقط مقادیر را پر کنید. مشخصه یا گروه جدید هم به همان نوع اضافه می‌شود تا دفعه بعد دوباره در دسترس باشد.
                </p>
                @error('specType') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            {{-- ── ویرایش درون‌صفحه‌ای گروه‌ها و مشخصه‌ها ── --}}
            @if (count($specifications) > 0)
                <div class="mt-3 space-y-2.5">
                    @foreach ($specifications as $gi => $group)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-700 dark:bg-slate-800/40" wire:key="spec-g-{{ $gi }}">
                            <div class="flex items-center gap-2">
                                <span class="grid size-6 shrink-0 place-items-center rounded-lg bg-primary-600 text-[9.5px] font-black text-white">{{ \App\Support\Fmt::fa($gi + 1) }}</span>
                                <input type="text" wire:model="specifications.{{ $gi }}.name" maxlength="80"
                                       placeholder="نام گروه — مثال: مشخصات کلی"
                                       class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold outline-none transition placeholder:text-slate-400 focus:border-primary-400 dark:border-slate-600 dark:bg-slate-900">
                                <button type="button" wire:click="removeSpecGroup({{ $gi }})" wire:loading.attr="disabled"
                                        class="grid size-8 shrink-0 place-items-center rounded-lg text-rose-500 transition hover:bg-rose-100 dark:hover:bg-rose-950/60" aria-label="حذف گروه">
                                    <x-icon name="trash" class="size-4" />
                                </button>
                            </div>

                            <div class="mt-2 space-y-1.5">
                                @foreach ($group['items'] as $ii => $item)
                                    <div class="flex items-center gap-1.5" wire:key="spec-i-{{ $gi }}-{{ $ii }}">
                                        <input type="text" wire:model="specifications.{{ $gi }}.items.{{ $ii }}.title" maxlength="80"
                                               placeholder="عنوان — مثل: اندازه"
                                               class="w-2/5 min-w-0 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-[10.5px] font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 dark:border-slate-600 dark:bg-slate-900">
                                        <input type="text" wire:model="specifications.{{ $gi }}.items.{{ $ii }}.value" maxlength="200"
                                               placeholder="مقدار — مثل: ۶.۷ اینچ"
                                               class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-[10.5px] font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 dark:border-slate-600 dark:bg-slate-900">
                                        <button type="button" wire:click="removeSpecItem({{ $gi }}, {{ $ii }})"
                                                class="grid size-7 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-100 hover:text-rose-500 dark:hover:bg-rose-950/50"
                                                aria-label="حذف مشخصه">
                                            <x-icon name="x" class="size-3.5" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" wire:click="addSpecItem({{ $gi }})"
                                    class="mt-2 flex w-full items-center justify-center gap-1.5 rounded-xl border border-dashed border-primary-300 py-1.5 text-[10px] font-extrabold text-primary-600 transition hover:border-primary-500 hover:bg-primary-50/50 dark:border-primary-700 dark:text-primary-400">
                                <x-icon name="plus" class="size-3.5" />
                                افزودن مشخصه
                            </button>
                        </div>
                    @endforeach

                    <button type="button" wire:click="addSpecGroup" wire:loading.attr="disabled"
                            class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-primary-300 py-2.5 text-[11px] font-extrabold text-primary-600 transition hover:border-primary-500 hover:bg-primary-50/60 dark:border-primary-700 dark:text-primary-400 dark:hover:bg-primary-950/30">
                        <x-icon name="plus" class="size-4" />
                        افزودن گروه جدید
                    </button>
                </div>
            @else
                <div class="mt-3 rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-center dark:border-slate-700">
                    <x-icon name="list" class="mx-auto size-7 text-slate-300" />
                    <p class="mt-2 text-[11px] font-bold text-slate-400">مشخصاتی ثبت نشده است.</p>
                    <p class="mt-1 text-[10px] leading-5 text-slate-400">یک «نوع مشخصات» انتخاب کنید یا از «افزودن گروه جدید» شروع کنید — مثل گروه «مشخصات کلی» با مشخصه «اندازه: ۶.۷ اینچ».</p>
                </div>
                <button type="button" wire:click="addSpecGroup"
                        class="mt-2 flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-primary-300 py-2.5 text-[11px] font-extrabold text-primary-600 transition hover:border-primary-500 hover:bg-primary-50/60 dark:border-primary-700 dark:text-primary-400 dark:hover:bg-primary-950/30">
                    <x-icon name="plus" class="size-4" />
                    افزودن گروه جدید
                </button>
            @endif
            <div id="specs-editor-end" aria-hidden="true"></div>
        </div>

        <x-slot:footer>
            <div class="px-5 py-4">
                <button type="button" x-on:click="__ipOpen = false; $wire.closeSpecModal()"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                    <x-icon name="circle-check" class="size-4" />
                    تأیید مشخصات
                </button>
                <p class="mt-2 text-center text-[9.5px] font-bold text-slate-400">تغییرات مشخصات با دکمه «ذخیره محصول» نهایی می‌شود.</p>
            </div>
        </x-slot:footer>
    </x-modal-panel>

    {{-- ═══════════════════ VARIANT MODAL ═══════════════════ --}}
    @if ($variantModalIndex !== null)
        @php
            $vi          = $variantModalIndex;
            $variant     = $variants[$vi] ?? null;
            $isNew       = $variant !== null && $variant['id'] === null;
        @endphp
        @if ($variant !== null)
            <div x-data="{ vmOpen: true }"
                 x-on:variant-close.window="vmOpen = false">
                <x-modal-panel open="$wire.variantModalIndex !== null" max-width="md" z-class="z-[75]" labelledby="variant-modal-title" in-frame
                               close-js="__ipOpen = false; $wire.closeVariantModal()">

                    <x-slot:header>
                        <div class="flex items-center justify-between px-5 py-4">
                            <h3 id="variant-modal-title" class="flex items-center gap-2 text-sm font-extrabold">
                                <span class="grid size-8 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                                    <x-icon name="layers" class="size-4" />
                                </span>
                                {{ $isNew ? 'افزودن تنوع جدید' : 'ویرایش تنوع' }}
                            </h3>
                            <button type="button" x-on:click="__ipOpen = false; $wire.closeVariantModal()"
                                    class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                                <x-icon name="x" class="size-4" />
                            </button>
                        </div>
                    </x-slot:header>

                    <div class="space-y-4 px-5 py-5">
                        {{-- ── ویژگی‌ها (رنگ با جستجو و پالت کنار عنوان) ── --}}
                        @if ($shopAttributes->isNotEmpty())
                            <div class="space-y-3">
                                @foreach ($shopAttributes as $attribute)
                                    @php $picked = $variant['values'][$attribute->id] ?? ['value_id' => 0, 'text' => '']; @endphp
                                    <div wire:key="vm-attr-{{ $attribute->id }}">
                                        @if ($attribute->type === 'color')
                                            {{-- رنگ: دراپ‌داون جستجودار + پالت کنار عنوان --}}
                                            <div x-data="{ colorOpen: false, colorQuery: '' }" @click.outside="colorOpen = false" class="relative">
                                                <div class="mb-1 flex items-center gap-1.5">
                                                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-300">{{ $attribute->name }}</span>
                                                    @php
                                                        $pickedValue = $attribute->values->firstWhere('id', (int) ($picked['value_id'] ?? 0));
                                                    @endphp
                                                    @if ($pickedValue?->color_hex)
                                                        <span class="size-3.5 rounded-full border border-slate-300 shadow-inner" style="background: {{ $pickedValue->color_hex }}"></span>
                                                        <span class="text-[9.5px] font-bold text-slate-500 dark:text-slate-400">{{ $pickedValue->value }}</span>
                                                    @endif
                                                </div>
                                                <button type="button" x-on:click="colorOpen = !colorOpen" :aria-expanded="colorOpen"
                                                        class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold outline-none transition hover:border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                                                    <span class="flex items-center gap-2">
                                                        @if ($pickedValue)
                                                            <span class="size-4 rounded-full border border-slate-300 shadow-inner" style="background: {{ $pickedValue->color_hex }}"></span>
                                                            <span class="font-bold">{{ $pickedValue->value }}</span>
                                                        @else
                                                            <span class="text-slate-400">انتخاب {{ $attribute->name }}</span>
                                                        @endif
                                                    </span>
                                                    <span class="shrink-0 text-slate-400 transition-transform" :class="colorOpen ? 'rotate-180' : ''">
                                                        <x-icon name="chevron-down" class="size-3.5" />
                                                    </span>
                                                </button>

                                                <div x-cloak x-show="colorOpen"
                                                     x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                                     class="absolute inset-x-0 top-full z-30 mt-1 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                                                    <div class="border-b border-slate-100 p-2 dark:border-slate-800">
                                                        <div class="relative">
                                                            <x-icon name="search" class="pointer-events-none absolute right-2.5 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                                                            <input type="search" x-model="colorQuery" placeholder="جستجوی {{ $attribute->name }}..."
                                                                   class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pr-8 pl-2.5 text-[10.5px] font-semibold outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                                                        </div>
                                                    </div>
                                                    <div class="scroll-slim max-h-40 overflow-y-auto p-1">
                                                        @foreach ($attribute->values as $value)
                                                            <button type="button" wire:key="color-opt-{{ $value->id }}"
                                                                    x-show="colorQuery.trim() === '' || '{{ e($value->value) }}'.includes(colorQuery.trim())"
                                                                    x-on:click="$wire.set('variants.{{ $vi }}.values.{{ $attribute->id }}.value_id', {{ $value->id }}); colorOpen = false"
                                                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-[10.5px] font-bold transition"
                                                                    :class="$wire.get('variants.{{ $vi }}.values.{{ $attribute->id }}.value_id') === {{ $value->id }} ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'">
                                                                @if ($value->color_hex)
                                                                    <span class="size-4 shrink-0 rounded-full border border-slate-300 shadow-inner" style="background: {{ $value->color_hex }}"></span>
                                                                @endif
                                                                {{ $value->value }}
                                                                @if ((int) ($picked['value_id'] ?? 0) === $value->id)
                                                                    <x-icon name="circle-check" class="mr-auto size-3.5 text-primary-600" />
                                                                @endif
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            @error("variants.{$vi}.values.{$attribute->id}.value_id") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                        @elseif ($attribute->type === 'text')
                                            <label class="block">
                                                <span class="mb-1 flex items-center gap-1 text-[10px] font-bold text-slate-500 dark:text-slate-300">{{ $attribute->name }}</span>
                                                <input type="text" wire:model="variants.{{ $vi }}.values.{{ $attribute->id }}.text" maxlength="120"
                                                       placeholder="مقدار (اختیاری)"
                                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                                @error("variants.{$vi}.values.{$attribute->id}.text") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                            </label>
                                        @else
                                            <label class="block">
                                                <span class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">{{ $attribute->name }}</span>
                                                <select wire:model="variants.{{ $vi }}.values.{{ $attribute->id }}.value_id"
                                                        class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold outline-none transition focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                                    <option value="0">— بدون انتخاب —</option>
                                                    @foreach ($attribute->values as $value)
                                                        <option value="{{ $value->id }}">{{ $value->value }}</option>
                                                    @endforeach
                                                </select>
                                                @error("variants.{$vi}.values.{$attribute->id}.value_id") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                            </label>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="border-t border-dashed border-slate-200 dark:border-slate-700"></div>
                        @else
                            <p class="rounded-xl border border-dashed border-slate-300 px-3 py-2 text-center text-[10px] font-bold text-slate-400 dark:border-slate-600">
                                ویژگی‌ای تعریف نشده؛ این تنوع با قیمت و موجودی ساده ثبت می‌شود.
                            </p>
                        @endif

                        {{-- ── قیمت و موجودی ── --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">قیمت (تومان) <span class="text-rose-500">*</span></label>
                                <input type="text" inputmode="numeric" dir="ltr" wire:model="variants.{{ $vi }}.price" placeholder="125000"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                @if (is_numeric($variant['price']) && (int) $variant['price'] > 0)
                                    <p class="mt-1 text-[9px] font-bold text-primary-500">
                                        {{ \App\Support\Fmt::money((int) $variant['price']) }}
                                        @if (is_numeric($variant['discount']) && (int) $variant['discount'] > 0)
                                            → پس از تخفیف: {{ \App\Support\Fmt::money((int) floor($variant['price'] * (100 - min(100, (int) $variant['discount'])) / 100)) }}
                                        @endif
                                    </p>
                                @endif
                                @error("variants.{$vi}.price") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">موجودی انبار <span class="text-rose-500">*</span></label>
                                <input type="text" inputmode="numeric" dir="ltr" wire:model="variants.{{ $vi }}.stock" placeholder="10"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                @error("variants.{$vi}.stock") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">تخفیف (٪)</label>
                                <input type="text" inputmode="numeric" dir="ltr" wire:model="variants.{{ $vi }}.discount" placeholder="0"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                @error("variants.{$vi}.discount") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <span class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">انقضای تخفیف</span>
                                <x-jdate-picker model="variants.{{ $vi }}.discount_expires_at" placeholder="تاریخ شمسی" />
                                @error("variants.{$vi}.discount_expires_at") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- ── محدودیت‌های خرید (یک دسته) ── --}}
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-3 dark:border-slate-700 dark:bg-slate-800/40">
                            <p class="flex items-center gap-1.5 text-[10px] font-extrabold text-slate-600 dark:text-slate-300">
                                <x-icon name="bag" class="size-3.5 text-primary-500" />
                                محدودیت‌های خرید
                                <span class="font-normal text-slate-400">(اختیاری)</span>
                            </p>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <div>
                                    <label class="mb-1 block text-[9.5px] font-bold text-slate-500 dark:text-slate-400">حداقل خرید</label>
                                    <input type="text" inputmode="numeric" dir="ltr" wire:model="variants.{{ $vi }}.min_purchase" placeholder="1"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                    @error("variants.{$vi}.min_purchase") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-[9.5px] font-bold text-slate-500 dark:text-slate-400">حداکثر خرید</label>
                                    <input type="text" inputmode="numeric" dir="ltr" wire:model="variants.{{ $vi }}.max_purchase" placeholder="5"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                    @error("variants.{$vi}.max_purchase") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ── وضعیت ── --}}
                        <div>
                            <span class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">وضعیت</span>
                            <div class="grid grid-cols-2 gap-1.5" role="group">
                                <button type="button" wire:click="$set('variants.{{ $vi }}.status', 'active')"
                                        class="rounded-xl border px-2 py-1.5 text-[10px] font-bold transition {{ $variant['status'] === 'active'
                                            ? 'border-emerald-400 bg-emerald-50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'
                                            : 'border-slate-200 bg-white text-slate-400 dark:border-slate-600 dark:bg-slate-900' }}">
                                    نمایش
                                </button>
                                <button type="button" wire:click="$set('variants.{{ $vi }}.status', 'hidden')"
                                        class="rounded-xl border px-2 py-1.5 text-[10px] font-bold transition {{ $variant['status'] === 'hidden'
                                            ? 'border-slate-400 bg-slate-200/70 text-slate-700 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200'
                                            : 'border-slate-200 bg-white text-slate-400 dark:border-slate-600 dark:bg-slate-900' }}">
                                    عدم نمایش
                                </button>
                            </div>
                            @error("variants.{$vi}.status") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex gap-2 px-5 py-4">
                            @unless ($isNew)
                                <button type="button" wire:click="removeVariant({{ $vi }})"
                                        class="grid place-items-center rounded-xl border border-rose-200 bg-rose-50 px-3.5 text-rose-500 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40"
                                        aria-label="حذف تنوع">
                                    <x-icon name="trash" class="size-4" />
                                </button>
                            @endunless
                            <button type="button" x-on:click="__ipOpen = false; $wire.closeVariantModal()"
                                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                انصراف
                            </button>
                            <button type="button" wire:click="confirmVariantModal" wire:loading.attr="disabled"
                                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                                <span wire:loading wire:target="confirmVariantModal" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                {{ $isNew ? 'ثبت تنوع' : 'ذخیره تغییرات' }}
                            </button>
                        </div>
                    </x-slot:footer>
                </x-modal-panel>
            </div>
        @endif
    @endif
</div>
