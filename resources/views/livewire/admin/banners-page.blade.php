<div class="ip-fade-up space-y-4">

    {{-- ── header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-base font-black">بنرها و اسلایدها</h1>
            <p class="mt-0.5 text-xs text-slate-400">اسلایدر بالای صفحهٔ اصلی اپ مشتری — ترتیبِ اینجا = ترتیبِ اسلایدر</p>
        </div>
        <button type="button" wire:click="toggleForm(null)"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
            <x-icon name="{{ $formOpen && $editingId === null ? 'minus' : 'plus' }}" class="size-4" />
            {{ $formOpen && $editingId === null ? 'بستن فرم' : 'افزودن بنر' }}
        </button>
    </div>

    {{-- ── form (create + edit) ── --}}
    @if ($formOpen)
        <div class="rounded-2xl border border-primary-200 bg-white p-5 shadow-sm dark:border-primary-900/60 dark:bg-slate-900">
            <h2 class="text-sm font-extrabold">{{ $editingId !== null ? 'ویرایش بنر' : 'بنر جدید' }}</h2>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <label class="flex-1">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                تصویر بنر {{ $editingId === null ? '*' : '(برای تغییر، فایل جدید بدهید)' }} — افقی ~۱۲۰۰×۵۰۰
                            </span>
                            <input type="file" accept="image/jpeg,image/png,image/webp" wire:model.live="image"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs file:me-3 file:rounded-lg file:border-0 file:bg-primary-600 file:px-3 file:py-1.5 file:text-[10px] file:font-bold file:text-white shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            @error('image') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            <span wire:loading wire:target="image" class="mt-1 block text-[10px] font-semibold text-primary-500">در حال بارگذاری…</span>
                        </label>
                        @if ($image)
                            <img src="{{ $image->temporaryUrl() }}" alt="پیش‌نمایش بنر"
                                 class="h-20 w-32 rounded-xl border border-slate-200 object-cover dark:border-slate-700">
                        @endif
                    </div>

                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">عنوان (اختیاری — برای مدیریت)</span>
                        <input type="text" wire:model="title" maxlength="150" placeholder="مثلاً: بنر تخفیف پاییزی"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('title') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="space-y-3">
                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">لینک اسلاید (اختیاری)</span>
                        <input type="url" dir="ltr" wire:model="linkUrl" placeholder="https://… یا آدرس محصول"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('linkUrl') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">ترتیب نمایش</span>
                            <input type="number" min="0" max="9999" wire:model="sort"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            @error('sort') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </label>
                        <label class="mt-6 flex cursor-pointer items-center justify-between gap-2 rounded-xl border border-slate-200 px-3 py-2.5 dark:border-slate-700">
                            <span class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">فعال</span>
                            <input type="checkbox" wire:model="isActive" class="size-4 accent-primary-600">
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                <button type="button" wire:click="cancelForm"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    انصراف
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                    <x-icon name="check" class="size-4" />
                    {{ $editingId !== null ? 'ذخیره تغییرات' : 'افزودن بنر' }}
                </button>
            </div>
        </div>
    @endif

    {{-- ── list ── --}}
    <div class="space-y-3">
        @forelse ($banners as $banner)
            <div wire:key="banner-{{ $banner->id }}"
                 class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <img src="{{ $banner->imageUrl() }}" alt="بنر {{ $banner->title ?? $banner->id }}" loading="lazy"
                     class="h-16 w-28 shrink-0 rounded-xl border border-slate-200 object-cover dark:border-slate-700">

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="truncate text-xs font-extrabold">{{ $banner->title ?: 'بنر #'.\App\Support\Fmt::fa($banner->id) }}</span>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-black {{ $banner->is_active
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                            : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $banner->is_active ? 'فعال' : 'غیرفعال' }}
                        </span>
                    </div>
                    @if ($banner->link_url)
                        <a href="{{ $banner->link_url }}" target="_blank" rel="noopener" dir="ltr"
                           class="mt-0.5 block truncate text-[10px] font-semibold text-primary-500 hover:underline">{{ $banner->link_url }}</a>
                    @endif
                    <div class="mt-1 text-[10px] font-semibold text-slate-400">ترتیب: {{ \App\Support\Fmt::fa($banner->sort) }} · ثبت: {{ \App\Support\JDate::date($banner->created_at) }}</div>
                </div>

                <div class="flex shrink-0 items-center gap-1.5">
                    <button type="button" wire:click="moveUp({{ $banner->id }})" wire:loading.attr="disabled" wire:target="moveUp({{ $banner->id }})"
                            class="grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-400 transition hover:border-primary-300 hover:text-primary-600 active:scale-95 disabled:opacity-40 dark:border-slate-700"
                            aria-label="انتقال به بالا" title="انتقال به بالا">
                        <x-icon name="chevron-up" class="size-3.5" />
                    </button>
                    <button type="button" wire:click="moveDown({{ $banner->id }})" wire:loading.attr="disabled" wire:target="moveDown({{ $banner->id }})"
                            class="grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-400 transition hover:border-primary-300 hover:text-primary-600 active:scale-95 disabled:opacity-40 dark:border-slate-700"
                            aria-label="انتقال به پایین" title="انتقال به پایین">
                        <x-icon name="chevron-down" class="size-3.5" />
                    </button>
                    <button type="button" wire:click="toggleForm({{ $banner->id }})"
                            class="grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-400 transition hover:border-primary-300 hover:text-primary-600 active:scale-95 dark:border-slate-700"
                            aria-label="ویرایش بنر" title="ویرایش">
                        <x-icon name="edit" class="size-3.5" />
                    </button>
                    <button type="button" wire:click="toggleActive({{ $banner->id }})"
                            class="grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-400 transition hover:border-amber-300 hover:text-amber-500 active:scale-95 dark:border-slate-700"
                            aria-label="{{ $banner->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}" title="{{ $banner->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}">
                        <x-icon name="{{ $banner->is_active ? 'eye-off' : 'eye' }}" class="size-3.5" />
                    </button>
                    <button type="button" wire:click="destroy({{ $banner->id }})" wire:confirm="این بنر برای همیشه حذف شود؟"
                            class="grid size-8 place-items-center rounded-lg border border-rose-200 text-rose-500 transition hover:bg-rose-50 active:scale-95 dark:border-rose-900 dark:hover:bg-rose-950/40"
                            aria-label="حذف بنر" title="حذف">
                        <x-icon name="trash" class="size-3.5" />
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                <x-icon name="image" class="mx-auto size-8 text-slate-300 dark:text-slate-600" />
                <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">هنوز بنری ساخته نشده — صفحهٔ اصلی فعلاً بنر پیش‌فرض را نشان می‌دهد.</p>
            </div>
        @endforelse
    </div>
</div>
