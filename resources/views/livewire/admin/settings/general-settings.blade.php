<div class="ip-fade-up space-y-5">

    {{-- ── flash ── --}}
    @if (session('admin-general-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-general-flash') }}
        </div>
    @endif

    {{-- ── ONE unified section ── --}}
    <form wire:submit="save" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900">

        <div class="flex items-center gap-2.5">
            <span class="grid size-9 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                <x-icon name="settings" class="size-4.5" />
            </span>
            <div>
                <h2 class="text-sm font-extrabold">تنظیمات عمومی سایت</h2>
                <p class="mt-0.5 text-[11px] text-slate-400">عنوان، لوگو، فاوآیکون، سئو و اطلاعات تماس — همه در یک‌جا</p>
            </div>
        </div>

        <div class="mt-5 space-y-5">

            {{-- site title --}}
            <label class="block">
                <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">عنوان سایت <span class="text-rose-500">*</span></span>
                <input type="text" wire:model="appName"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm shadow-sm transition focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                       placeholder="مثلاً: فروشگاه اینستاگرامی من">
                @error('appName') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
            </label>

            {{-- logo + favicon --}}
            <div class="grid gap-4 lg:grid-cols-2">

                {{-- logo upload --}}
                <div class="rounded-2xl border border-dashed border-slate-300 p-4 dark:border-slate-700">
                    <div class="flex items-start gap-4">
                        <div class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                            @if (is_object($logo))
                                <img src="{{ $logo->temporaryUrl() }}" alt="پیش‌نمایش لوگوی جدید" class="size-full object-contain">
                            @elseif (\App\Support\Branding::logoUrl())
                                <img src="{{ \App\Support\Branding::logoUrl() }}" alt="لوگوی سایت" class="size-full object-contain">
                            @else
                                <x-icon name="image" class="size-8 text-slate-300" />
                            @endif
                        </div>
                        <div class="flex-1">
                            <span class="block text-xs font-bold">لوگوی سایت</span>
                            <span class="mt-0.5 block text-[10px] leading-5 text-slate-400">PNG، JPG، WEBP یا SVG — حداکثر ۲ مگابایت. مربع با پس‌زمینه شفاف بهترین نمایش را دارد.</span>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-primary-300 px-3 py-2 text-[11px] font-bold text-primary-700 transition hover:bg-primary-50 dark:border-primary-800 dark:text-primary-300 dark:hover:bg-primary-950/40">
                                    <x-icon name="upload" class="size-3.5" />
                                    انتخاب لوگو
                                    <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden">
                                </label>
                                @if (\App\Support\Branding::logoUrl() || is_object($logo))
                                    <button type="button" wire:click="removeLogo" wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 px-3 py-2 text-[11px] font-bold text-rose-600 transition hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950/40">
                                        <x-icon name="trash" class="size-3.5" />
                                        حذف
                                    </button>
                                @endif
                            </div>
                            <x-upload-progress target="logo" label="در حال آپلود لوگو" />
                            @error('logo') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- favicon upload --}}
                <div class="rounded-2xl border border-dashed border-slate-300 p-4 dark:border-slate-700">
                    <div class="flex items-start gap-4">
                        <div class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                            @if (is_object($favicon))
                                <img src="{{ $favicon->temporaryUrl() }}" alt="پیش‌نمایش فاوآیکون جدید" class="size-full object-contain">
                            @elseif (\App\Support\Branding::faviconUrl())
                                <img src="{{ \App\Support\Branding::faviconUrl() }}" alt="فاوآیکون سایت" class="size-full object-contain">
                            @else
                                <x-icon name="globe" class="size-8 text-slate-300" />
                            @endif
                        </div>
                        <div class="flex-1">
                            <span class="block text-xs font-bold">فاوآیکون (آیکون تب مرورگر)</span>
                            <span class="mt-0.5 block text-[10px] leading-5 text-slate-400">PNG، ICO یا SVG — مربعی ۵۱۲×۵۱۲ توصیه می‌شود. حداکثر ۱ مگابایت.</span>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-primary-300 px-3 py-2 text-[11px] font-bold text-primary-700 transition hover:bg-primary-50 dark:border-primary-800 dark:text-primary-300 dark:hover:bg-primary-950/40">
                                    <x-icon name="upload" class="size-3.5" />
                                    انتخاب فاوآیکون
                                    <input type="file" wire:model="favicon" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon" class="hidden">
                                </label>
                                @if (\App\Support\Branding::faviconUrl() || is_object($favicon))
                                    <button type="button" wire:click="removeFavicon" wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 px-3 py-2 text-[11px] font-bold text-rose-600 transition hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950/40">
                                        <x-icon name="trash" class="size-3.5" />
                                        حذف
                                    </button>
                                @endif
                            </div>
                            <x-upload-progress target="favicon" label="در حال آپلود فاوآیکون" />
                            @error('favicon') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- meta description --}}
            <label class="block">
                <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">توضیحات متا (SEO)</span>
                <textarea rows="3" wire:model="metaDescription"
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                          placeholder="معرفی کوتاه سایت برای موتورهای جست‌وجو"></textarea>
                @error('metaDescription') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
            </label>

            {{-- contact --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">شماره تماس فروشگاه</span>
                    <input type="text" dir="ltr" wire:model="storePhone"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm transition focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"
                           placeholder="02112345678">
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">آیدی اینستاگرام فروشگاه</span>
                    <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 shadow-sm transition focus-within:border-primary-500 focus-within:bg-white dark:border-slate-700 dark:bg-slate-800">
                        <x-icon name="instagram" class="size-4 shrink-0 text-slate-400" />
                        <input type="text" dir="ltr" wire:model="storeInstagram"
                               class="w-full bg-transparent py-2.5 text-xs focus:outline-none"
                               placeholder="myshop">
                    </div>
                    @error('storeInstagram') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>
            </div>

            {{-- save --}}
            <div class="flex justify-end border-t border-slate-100 pt-4 dark:border-slate-800">
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-6 py-3 text-sm font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                    <span wire:loading wire:target="save" class="inline-block size-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span wire:loading.remove wire:target="save"><x-icon name="check" class="size-4" /></span>
                    ذخیره تنظیمات عمومی
                </button>
            </div>
        </div>
    </form>
</div>
