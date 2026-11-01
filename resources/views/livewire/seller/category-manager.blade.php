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
                    <div class="text-sm font-extrabold leading-5">مدیریت دسته‌بندی‌ها</div>
                    <div class="text-[11px] text-slate-400">دسته‌بندی محصولات فروشگاه خودت</div>
                </div>
            </div>
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">
        {{-- ───────── Add form ───────── --}}
        <form wire:submit="add" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <label for="cm-name" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">دسته‌بندی جدید</label>
            <div class="flex gap-2">
                <input id="cm-name" type="text" wire:model="name" maxlength="80" placeholder="مثال: ادویه و خشکبار"
                       class="flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                <button type="submit"
                        class="grid size-11 shrink-0 place-items-center rounded-2xl bg-primary-600 text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60"
                        wire:loading.attr="disabled" wire:target="add"
                        aria-label="افزودن دسته‌بندی">
                    <span wire:loading.remove wire:target="add"><x-icon name="plus" class="size-5" /></span>
                    <span wire:loading wire:target="add" class="inline-block size-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                </button>
            </div>
            @error('name') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
        </form>

        {{-- ───────── Category list ───────── --}}
        <div class="mt-3 space-y-2" wire:loading.class="opacity-50" wire:target="moveUp, moveDown, remove">
            @forelse ($categories as $category)
                <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="cat-{{ $category->id }}">
                    @if ($editingId === $category->id)
                        {{-- inline edit --}}
                        <form wire:submit="updateEdit" class="flex flex-1 items-center gap-2">
                            <input type="text" wire:model="editingName" maxlength="80" autofocus
                                   class="flex-1 rounded-xl border border-primary-300 bg-white px-3 py-2 text-xs font-bold outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-primary-700 dark:bg-slate-800">
                            <button type="submit" class="grid size-8 place-items-center rounded-xl bg-emerald-500 text-white transition hover:bg-emerald-600" aria-label="ذخیره">
                                <x-icon name="check" class="size-4" />
                            </button>
                            <button type="button" wire:click="cancelEdit" class="grid size-8 place-items-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400" aria-label="انصراف">
                                <x-icon name="x" class="size-4" />
                            </button>
                            @error('editingName') <p class="w-full text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </form>
                    @else
                        {{-- normal row --}}
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-950 dark:text-primary-400">
                            <x-icon name="tag" class="size-4.5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-xs font-extrabold">{{ $category->name }}</div>
                            <div class="mt-0.5 text-[10px] font-semibold text-slate-400">{{ \App\Support\Fmt::fa($category->products_count) }} محصول</div>
                            {{-- «نمایش در صفحه فروشگاه» toggle — controls the public storefront --}}
                            <button type="button" wire:click="toggleStorefront({{ $category->id }})"
                                    wire:loading.attr="disabled" wire:target="toggleStorefront"
                                    class="mt-1.5 inline-flex items-center gap-1.5 text-[10px] font-bold transition"
                                    role="switch" aria-checked="{{ $category->show_on_storefront ? 'true' : 'false' }}"
                                    aria-label="نمایش دسته {{ $category->name }} در صفحه فروشگاه">
                                <span class="relative inline-flex h-4 w-7 shrink-0 items-center rounded-full transition {{ $category->show_on_storefront ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}">
                                    <span class="absolute size-3 rounded-full bg-white shadow transition-all {{ $category->show_on_storefront ? 'right-0.5' : 'right-[calc(100%-0.875rem)]' }}"></span>
                                </span>
                                <span class="{{ $category->show_on_storefront ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                    نمایش در صفحه فروشگاه
                                </span>
                            </button>
                        </div>

                        <div class="flex shrink-0 items-center gap-0.5">
                            <button type="button" wire:click="moveUp({{ $category->id }})"
                                    class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 disabled:opacity-30 dark:hover:bg-slate-800"
                                    @if ($loop->first) disabled @endif
                                    title="انتقال به بالا" aria-label="انتقال {{ $category->name }} به بالا">
                                <x-icon name="chevron-up" class="size-4" />
                            </button>
                            <button type="button" wire:click="moveDown({{ $category->id }})"
                                    class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 disabled:opacity-30 dark:hover:bg-slate-800"
                                    @if ($loop->last) disabled @endif
                                    title="انتقال به پایین" aria-label="انتقال {{ $category->name }} به پایین">
                                <x-icon name="chevron-down" class="size-4" />
                            </button>
                            <button type="button" wire:click="startEdit({{ $category->id }})"
                                    class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-800"
                                    title="تغییر نام" aria-label="تغییر نام {{ $category->name }}">
                                <x-icon name="edit" class="size-4" />
                            </button>
                            <button type="button" wire:click="remove({{ $category->id }})"
                                    wire:confirm="دسته «{{ $category->name }}» حذف شود؟ محصولات آن بدون دسته خواهند شد."
                                    class="grid size-8 place-items-center rounded-lg text-rose-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40"
                                    title="حذف" aria-label="حذف {{ $category->name }}">
                                <x-icon name="trash" class="size-4" />
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="mt-4 flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-300 py-12 text-center dark:border-slate-700">
                    <span class="grid size-16 place-items-center rounded-2xl bg-primary-50 text-primary-400 dark:bg-primary-950">
                        <x-icon name="tag" class="size-8" />
                    </span>
                    <p class="text-sm font-extrabold">هنوز دسته‌بندی‌ای نساخته‌اید</p>
                    <p class="max-w-[250px] text-xs leading-6 text-slate-400">دسته‌بندی‌ها خرید را برای مشتری راحت‌تر می‌کنند؛ از فرم بالا اولین دسته را بسازید.</p>
                </div>
            @endforelse
        </div>

        {{-- hint --}}
        <div class="mt-4 flex items-start gap-2.5 rounded-2xl border border-primary-200 bg-primary-50/70 p-3.5 text-xs leading-6 text-primary-800 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
            <x-icon name="info" class="mt-0.5 size-4 shrink-0" />
            <span>
                با حذف دسته‌بندی، محصولاتی که در آن بودند حذف نمی‌شوند؛ فقط «بدون دسته» می‌شوند. دسته‌هایی که کلید «نمایش در صفحه فروشگاه» آن‌ها روشن باشد، در صفحهٔ عمومی فروشگاه شما (آدرس اینستاگرامی شما) به‌عنوان فیلتر نمایش داده می‌شوند.
            </span>
        </div>
    </div>
</div>
