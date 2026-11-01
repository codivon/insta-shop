<div class="ip-fade-up" x-data="{ tab: 'info' }" x-on:goto-tab.window="tab = $event.detail.tab">
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

        {{-- ───────── Tabs ───────── --}}
        <div class="flex gap-1 px-4 pb-2" role="tablist" aria-label="بخش‌های فرم محصول">
            <button type="button" @click="tab = 'info'" :aria-selected="tab === 'info'" role="tab"
                    class="flex-1 rounded-xl px-2 py-2 text-[11px] font-extrabold transition"
                    :class="tab === 'info'
                        ? 'bg-primary-50 text-primary-700 shadow-sm dark:bg-primary-950/60 dark:text-primary-300'
                        : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                اطلاعات محصول
            </button>
            <button type="button" @click="tab = 'pricing'" :aria-selected="tab === 'pricing'" role="tab"
                    class="flex-1 rounded-xl px-2 py-2 text-[11px] font-extrabold transition"
                    :class="tab === 'pricing'
                        ? 'bg-primary-50 text-primary-700 shadow-sm dark:bg-primary-950/60 dark:text-primary-300'
                        : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                {{ $productType === 'downloadable' ? 'قیمت‌گذاری' : 'قیمت و تنوع‌ها' }}
            </button>
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

    <form wire:submit="save" class="px-4 pt-4 pb-8">

        {{-- ═══════════════ TAB 1 · اطلاعات محصول ═══════════════ --}}
        <div x-show="tab === 'info'" x-cloak class="space-y-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="package" class="size-3.5" /></span>
                    مشخصات محصول
                </h3>

                <div class="mt-3 space-y-3">
                    <div>
                        <label for="pf-name" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">نام محصول <span class="text-rose-500">*</span></label>
                        <input id="pf-name" type="text" wire:model="name" maxlength="150" placeholder="مثال: زعفران سرگل قائنات — ۱۰ گرم"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                        @error('name') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="pf-desc" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">توضیحات</label>
                        <textarea id="pf-desc" wire:model="description" rows="4" maxlength="5000"
                                  placeholder="ویژگی‌ها، جنس، ابعاد، نحوه ارسال..."
                                  class="scroll-slim w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold leading-6 outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800"></textarea>
                        @error('description') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- type + specs --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="settings" class="size-3.5" /></span>
                    نوع و مشخصات تکمیلی
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

                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label for="pf-cat" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">دسته‌بندی</label>
                        <select id="pf-cat" wire:model="categoryId"
                                class="w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                            <option value="0">بدون دسته</option>
                            @foreach (\App\Models\ShopCategory::query()->where('shop_id', $shopId)->orderBy('sort')->get() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('categoryId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    @if ($productType === 'physical')
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
                    @endif
                </div>
            </div>

            {{-- images --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="camera" class="size-3.5" /></span>
                    تصاویر محصول
                    <span class="text-[10px] font-semibold text-slate-400">(حداکثر ۶ تصویر)</span>
                </h3>

                @if ($existingImages->isNotEmpty())
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach ($existingImages as $img)
                            <div class="relative overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700" wire:key="img-{{ $img->id }}">
                                <img src="{{ $img->url() }}" alt="تصویر محصول" class="aspect-square w-full object-cover">
                                @if ($loop->first)
                                    <span class="absolute right-1.5 top-1.5 rounded-full bg-primary-600 px-2 py-0.5 text-[9px] font-bold text-white shadow">اصلی</span>
                                @endif
                                <div class="absolute inset-x-1 bottom-1 flex gap-1">
                                    @unless ($loop->first)
                                        <button type="button" wire:click="makeMain({{ $img->id }})"
                                                class="flex-1 rounded-lg bg-white/90 py-1 text-[9px] font-bold text-slate-700 shadow backdrop-blur transition hover:bg-white">
                                            اصلی شود
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

                <label class="mt-3 flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-slate-300 py-6 text-center transition hover:border-primary-400 hover:bg-primary-50/50 dark:border-slate-700 dark:hover:border-primary-600 dark:hover:bg-primary-950/20">
                    <x-icon name="image-plus" class="size-8 text-primary-400" />
                    <span class="text-xs font-extrabold text-slate-600 dark:text-slate-200">انتخاب تصویر از گالری</span>
                    <span class="text-[10px] font-semibold text-slate-400">JPG / PNG / WebP — حداکثر ۲ مگابایت</span>
                    <input type="file" wire:model="photos" accept="image/png,image/jpeg,image/webp" multiple class="sr-only">
                </label>
                <x-upload-progress target="photos" label="در حال آپلود تصاویر" />

                @if ($photos)
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach ($photos as $key => $photo)
                            <div class="relative overflow-hidden rounded-xl border-2 border-primary-300 dark:border-primary-700" wire:key="new-{{ $key }}">
                                <img src="{{ $photo->temporaryUrl() }}" alt="پیش‌نمایش تصویر" class="aspect-square w-full object-cover">
                                <button type="button" wire:click="removeNewPhoto({{ $key }})" aria-label="حذف تصویر انتخابی"
                                        class="absolute left-1.5 top-1.5 grid size-6 place-items-center rounded-full bg-rose-500 text-white shadow transition hover:bg-rose-600">
                                    <x-icon name="x" class="size-3" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                @error('photos') <p class="mt-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                @foreach (range(0, 5) as $photoKey)
                    @error('photos.'.$photoKey) <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                @endforeach
            </div>
        </div>

        {{-- ═══════════════ TAB 2 · قیمت و تنوع‌ها (فیزیکی) / قیمت‌گذاری (دانلودی) ═══════════════ --}}
        <div x-show="tab === 'pricing'" x-cloak>
            @if ($productType === 'physical')
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="flex items-center gap-2 text-xs font-extrabold">
                            <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="layers" class="size-3.5" /></span>
                            تنوع‌های محصول
                            <span class="rounded-full bg-primary-50 px-2 py-0.5 text-[9px] font-black text-primary-600 dark:bg-primary-950 dark:text-primary-300">{{ count($variants) }}</span>
                        </h3>
                        <a href="{{ route('seller.shop.attributes') }}" wire:navigate
                           class="flex items-center gap-1 rounded-xl border border-slate-200 px-2.5 py-1.5 text-[10px] font-bold text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300">
                            <x-icon name="settings" class="size-3.5" />
                            مدیریت ویژگی‌ها
                        </a>
                    </div>
                    <p class="mt-1.5 text-[10px] leading-5 text-slate-400">برای هر ترکیب (رنگ/سایز/گارانتی و ...) یک ردیف با قیمت و موجودی جدا بسازید. حداقل یک تنوع لازم است.</p>

                    @error('variants') <p class="mt-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[10px] font-bold text-rose-600 dark:border-rose-900 dark:bg-rose-950/40">{{ $message }}</p> @enderror

                    <div class="mt-3 space-y-3">
                        @foreach ($variants as $i => $variant)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-3 dark:border-slate-700 dark:bg-slate-800/40" wire:key="variant-{{ $i }}" x-data="{ open: {{ $i === 0 ? 'true' : 'false' }} }">
                                <div class="flex items-center justify-between gap-2">
                                    <button type="button" @click="open = !open" class="flex min-w-0 items-center gap-2" :aria-expanded="open">
                                        <span class="grid size-6 shrink-0 place-items-center rounded-lg bg-primary-600 text-[10px] font-black text-white">{{ \App\Support\Fmt::fa($i + 1) }}</span>
                                        <span class="truncate text-[11px] font-extrabold">
                                            {{ $variant['price'] !== '' ? \App\Support\Fmt::money((int) $variant['price']).' تومان' : 'تنوع جدید' }}
                                            @if (is_numeric($variant['discount']) && (int) $variant['discount'] > 0)
                                                <span class="mr-1 rounded-full bg-rose-100 px-1.5 py-0.5 text-[9px] font-black text-rose-600 dark:bg-rose-950 dark:text-rose-400">{{ \App\Support\Fmt::fa((int) $variant['discount']) }}٪ تخفیف</span>
                                            @endif
                                        </span>
                                    </button>
                                    <div class="flex shrink-0 items-center gap-1">
                                        <button type="button" @click="open = !open" class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-200/60 dark:hover:bg-slate-700" aria-label="باز و بسته کردن">
                                            <span class="inline-block transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                                                <x-icon name="chevron-down" class="size-4" />
                                            </span>
                                        </button>
                                        @if (count($variants) > 1)
                                            <button type="button" wire:click="removeVariant({{ $i }})" wire:loading.attr="disabled"
                                                    class="grid size-8 place-items-center rounded-lg text-rose-500 transition hover:bg-rose-100 dark:hover:bg-rose-950/60" aria-label="حذف تنوع">
                                                <x-icon name="trash" class="size-4" />
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div x-show="open" x-cloak>
                                    {{-- attribute picks --}}
                                    @if ($shopAttributes->isNotEmpty())
                                        <div class="mt-3 grid grid-cols-2 gap-2">
                                            @foreach ($shopAttributes as $attribute)
                                                @php
                                                    $picked = $variant['values'][$attribute->id] ?? ['value_id' => 0, 'text' => ''];
                                                    $pickedValue = $attribute->values->firstWhere('id', (int) ($picked['value_id'] ?? 0));
                                                @endphp
                                                <div wire:key="va-{{ $i }}-{{ $attribute->id }}">
                                                    <label class="mb-1 flex items-center gap-1 text-[10px] font-bold text-slate-500 dark:text-slate-300">
                                                        {{ $attribute->name }}
                                                        @if ($attribute->type === 'color' && $pickedValue?->color_hex)
                                                            <span class="size-3 rounded-full border border-slate-300" style="background: {{ $pickedValue->color_hex }}"></span>
                                                        @endif
                                                    </label>
                                                    @if ($attribute->type === 'text')
                                                        <input type="text" wire:model="variants.{{ $i }}.values.{{ $attribute->id }}.text" maxlength="120"
                                                               placeholder="مقدار (اختیاری)"
                                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                                    @else
                                                        <select wire:model="variants.{{ $i }}.values.{{ $attribute->id }}.value_id"
                                                                class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold outline-none transition focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                                            <option value="0">— بدون انتخاب —</option>
                                                            @foreach ($attribute->values as $value)
                                                                <option value="{{ $value->id }}">{{ $value->value }}</option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                    @error("variants.{$i}.values.{$attribute->id}.value_id") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                                    @error("variants.{$i}.values.{$attribute->id}.text") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="mt-3 rounded-xl border border-dashed border-slate-300 px-3 py-2 text-center text-[10px] font-bold text-slate-400 dark:border-slate-600">
                                            هنوز ویژگی‌ای تعریف نکرده‌اید؛ تنوع با قیمت و موجودی ساده ساخته می‌شود.
                                        </p>
                                    @endif

                                    {{-- fixed fields --}}
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">قیمت (تومان) <span class="text-rose-500">*</span></label>
                                            <input type="text" inputmode="numeric" dir="ltr" wire:model="variants.{{ $i }}.price" placeholder="125000"
                                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                            @if (is_numeric($variant['price']) && (int) $variant['price'] > 0)
                                                <p class="mt-1 text-[9px] font-bold text-primary-500">
                                                    {{ \App\Support\Fmt::money((int) $variant['price']) }}
                                                    @if (is_numeric($variant['discount']) && (int) $variant['discount'] > 0)
                                                        → پس از تخفیف: {{ \App\Support\Fmt::money((int) floor($variant['price'] * (100 - min(100, (int) $variant['discount'])) / 100)) }}
                                                    @endif
                                                </p>
                                            @endif
                                            @error("variants.{$i}.price") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">موجودی انبار <span class="text-rose-500">*</span></label>
                                            <input type="text" inputmode="numeric" dir="ltr" wire:model="variants.{{ $i }}.stock" placeholder="10"
                                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                            @error("variants.{$i}.stock") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">تخفیف (٪)</label>
                                            <input type="text" inputmode="numeric" dir="ltr" wire:model="variants.{{ $i }}.discount" placeholder="0"
                                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                            @error("variants.{$i}.discount") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">انقضای تخفیف</label>
                                            <input type="date" wire:model="variants.{{ $i }}.discount_expires_at"
                                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold outline-none transition focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                            @error("variants.{$i}.discount_expires_at") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">حداقل خرید</label>
                                            <input type="text" inputmode="numeric" dir="ltr" wire:model="variants.{{ $i }}.min_purchase" placeholder="1"
                                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                            @error("variants.{$i}.min_purchase") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">حداکثر خرید</label>
                                            <input type="text" inputmode="numeric" dir="ltr" wire:model="variants.{{ $i }}.max_purchase" placeholder="5"
                                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-[11px] font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-600 dark:bg-slate-900 dark:focus:bg-slate-900">
                                            @error("variants.{$i}.max_purchase") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div class="mt-2.5">
                                        <span class="mb-1 block text-[10px] font-bold text-slate-500 dark:text-slate-300">وضعیت</span>
                                        <div class="grid grid-cols-2 gap-1.5" role="group">
                                            <button type="button" wire:click="$set('variants.{{ $i }}.status', 'active')"
                                                    class="rounded-xl border px-2 py-1.5 text-[10px] font-bold transition {{ $variant['status'] === 'active'
                                                        ? 'border-emerald-400 bg-emerald-50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'
                                                        : 'border-slate-200 bg-white text-slate-400 dark:border-slate-600 dark:bg-slate-900' }}">
                                                نمایش
                                            </button>
                                            <button type="button" wire:click="$set('variants.{{ $i }}.status', 'hidden')"
                                                    class="rounded-xl border px-2 py-1.5 text-[10px] font-bold transition {{ $variant['status'] === 'hidden'
                                                        ? 'border-slate-400 bg-slate-200/70 text-slate-700 dark:border-slate-500 dark:bg-slate-700 dark:text-slate-200'
                                                        : 'border-slate-200 bg-white text-slate-400 dark:border-slate-600 dark:bg-slate-900' }}">
                                                عدم نمایش
                                            </button>
                                        </div>
                                        @error("variants.{$i}.status") <p class="mt-1 text-[9px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" wire:click="addVariant" wire:loading.attr="disabled"
                            class="mt-3 flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-primary-300 py-3 text-[11px] font-extrabold text-primary-600 transition hover:border-primary-500 hover:bg-primary-50/60 dark:border-primary-700 dark:text-primary-400 dark:hover:bg-primary-950/30">
                        <span wire:loading wire:target="addVariant" class="size-4 animate-spin rounded-full border-2 border-primary-300 border-t-primary-600"></span>
                        <span wire:loading.remove wire:target="addVariant"><x-icon name="plus" class="size-4" /></span>
                        افزودن تنوع
                    </button>
                </div>
            @else
                <div class="rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <x-icon name="download" class="mx-auto size-10 text-primary-400" />
                    <p class="mt-3 text-xs font-extrabold">این محصول دانلودی است</p>
                    <p class="mt-1.5 text-[10px] leading-5 text-slate-400">به‌جای تنوع، فایل‌های دانلودی را در تب «فایل‌های دانلودی» با قیمت و وضعیت جداگانه وارد کنید.</p>
                </div>
            @endif
        </div>

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

        {{-- ───────── Actions ───────── --}}
        <div class="sticky bottom-0 mt-4 border-t border-slate-200/80 bg-white/90 px-0 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
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
</div>
