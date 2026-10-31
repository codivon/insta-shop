<div class="ip-fade-up">
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
                    <div class="text-[11px] text-slate-400">{{ $productId ? 'اطلاعات محصول را به‌روز کنید' : 'محصول جدید را به فروشگاه اضافه کنید' }}</div>
                </div>
            </div>
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    <form wire:submit="save" class="px-4 pt-4 pb-8">
        {{-- ───────── Details card ───────── --}}
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

        {{-- ───────── Pricing & stock card ───────── --}}
        <div class="mt-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="flex items-center gap-2 text-xs font-extrabold">
                <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="credit-card" class="size-3.5" /></span>
                قیمت و موجودی
            </h3>

            <div class="mt-3 grid grid-cols-2 gap-3">
                <div>
                    <label for="pf-price" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">قیمت (تومان) <span class="text-rose-500">*</span></label>
                    <input id="pf-price" type="text" inputmode="numeric" dir="ltr" wire:model="price" placeholder="125000"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                    @if (is_numeric($price))
                        <p class="mt-1 text-[10px] font-bold text-primary-500">{{ \App\Support\Fmt::money((int) $price) }}</p>
                    @endif
                    @error('price') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="pf-compare" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">قبل از تخفیف</label>
                    <input id="pf-compare" type="text" inputmode="numeric" dir="ltr" wire:model="compareAtPrice" placeholder="150000"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                    @error('compareAtPrice') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="pf-stock" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">موجودی</label>
                    <input id="pf-stock" type="text" inputmode="numeric" dir="ltr" wire:model="stock" placeholder="10"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                    @error('stock') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
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
            </div>

            {{-- publish status --}}
            <div class="mt-3">
                <span class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">وضعیت انتشار</span>
                <div class="grid grid-cols-2 gap-2" role="group" aria-label="وضعیت انتشار محصول">
                    <button type="button" wire:click="$set('status', 'draft')"
                            class="rounded-2xl border px-3 py-2.5 text-[11px] font-bold transition {{ $status === 'draft'
                                ? 'border-slate-400 bg-slate-100 text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200'
                                : 'border-slate-200 bg-white text-slate-400 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900' }}">
                        پیش‌نویس
                    </button>
                    <button type="button" wire:click="$set('status', 'published')"
                            class="rounded-2xl border px-3 py-2.5 text-[11px] font-bold transition {{ $status === 'published'
                                ? 'border-primary-500 bg-primary-50 text-primary-700 shadow-sm shadow-primary-600/20 dark:border-primary-600 dark:bg-primary-950/50 dark:text-primary-300'
                                : 'border-slate-200 bg-white text-slate-400 hover:border-primary-300 dark:border-slate-700 dark:bg-slate-900' }}">
                        منتشر شود
                    </button>
                </div>
            </div>
        </div>

        {{-- ───────── Images card ───────── --}}
        <div class="mt-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="flex items-center gap-2 text-xs font-extrabold">
                <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="camera" class="size-3.5" /></span>
                تصاویر محصول
                <span class="text-[10px] font-semibold text-slate-400">(حداکثر ۶ تصویر)</span>
            </h3>

            {{-- existing images (edit mode) --}}
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

            {{-- upload zone --}}
            <label class="mt-3 flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-slate-300 py-6 text-center transition hover:border-primary-400 hover:bg-primary-50/50 dark:border-slate-700 dark:hover:border-primary-600 dark:hover:bg-primary-950/20">
                <x-icon name="image-plus" class="size-8 text-primary-400" />
                <span class="text-xs font-extrabold text-slate-600 dark:text-slate-200">انتخاب تصویر از گالری</span>
                <span class="text-[10px] font-semibold text-slate-400">JPG / PNG / WebP — حداکثر ۲ مگابایت</span>
                <input type="file" wire:model="photos" accept="image/png,image/jpeg,image/webp" multiple class="sr-only">
            </label>
            <div wire:loading wire:target="photos" class="mt-2 flex items-center justify-center gap-2 text-[10px] font-bold text-primary-500">
                <span class="inline-block size-4 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"></span>
                در حال آپلود...
            </div>

            {{-- new photos preview --}}
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

        {{-- ───────── Actions ───────── --}}
        <div class="mt-4 flex gap-2">
            <button type="submit"
                    class="flex-1 rounded-2xl bg-primary-600 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-[.98] disabled:opacity-60"
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
    </form>
</div>
