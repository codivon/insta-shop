<div class="space-y-4" x-data="{
        dragId: 0,
        overId: 0,
        pos: '',
        start(id) { this.dragId = id; },
        over(id, e) {
            if (this.dragId === 0 || this.dragId === id) return;
            this.overId = id;
            const r = e.currentTarget.getBoundingClientRect();
            const y = e.clientY - r.top;
            this.pos = y < r.height * 0.3 ? 'before' : (y > r.height * 0.7 ? 'after' : 'inside');
        },
        leave() { this.overId = 0; this.pos = ''; },
        drop(id) {
            if (this.dragId > 0 && this.dragId !== id) $wire.moveCat(this.dragId, id, this.pos);
            this.dragId = 0; this.overId = 0; this.pos = '';
        },
        dragCls(id) {
            if (this.overId !== id) return '';
            return this.pos === 'before' ? 'cat-drop-before' : (this.pos === 'after' ? 'cat-drop-after' : 'cat-drop-inside');
        },
    }">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 -mx-4 border-b border-slate-200/80 bg-white/90 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90 sm:-mx-6 sm:px-6">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                    <x-icon name="layers" class="size-5" />
                </span>
                <div>
                    <h1 class="text-sm font-black leading-5">مدیریت دسته‌بندی‌ها</h1>
                    <p class="text-[11px] text-slate-400">درخت سراسری دسته‌ها — فروشندگان فقط انتخاب می‌کنند</p>
                </div>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-[10.5px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                {{ \App\Support\Fmt::fa($total) }} دسته
            </span>
        </div>
    </div>

    {{-- ───────── add form ───────── --}}
    <form wire:submit="add" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="mb-3 flex items-center gap-2 text-xs font-extrabold"><x-icon name="plus" class="size-4 text-primary-500" /> افزودن دسته‌بندی</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-1 block text-[10.5px] font-bold text-slate-500 dark:text-slate-400">نام دسته <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="newName" maxlength="120" placeholder="مثلاً: لوازم ورزشی"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('newName') <p class="mt-1 text-[9.5px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-[10.5px] font-bold text-slate-500 dark:text-slate-400">والد</label>
                <select wire:model="newParentId"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    <option value="0">— دسته اصلی (ریشه) —</option>
                    @foreach ($parentOptions as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-[10.5px] font-bold text-slate-500 dark:text-slate-400">نامک (اختیاری)</label>
                <input type="text" wire:model="newSlug" dir="ltr" maxlength="190" placeholder="sports-equipment"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('newSlug') <p class="mt-1 text-[9.5px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-[10.5px] font-bold text-slate-500 dark:text-slate-400">تصویر (URL اختیاری)</label>
                <input type="text" wire:model="newImage" dir="ltr" maxlength="400" placeholder="https://..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
            </div>
        </div>
        <div class="mt-3 flex justify-end">
            <button type="submit" class="rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
                افزودن دسته
            </button>
        </div>
    </form>

    {{-- ───────── tree ───────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <p class="mb-2 flex items-center gap-1.5 px-1 text-[10.5px] font-bold text-slate-400">
            <x-icon name="info" class="size-3.5" />
            برای جابه‌جایی، ردیف را بکشید و روی جای خالی (قبل/بعد) یا روی یک دستهٔ دیگر (زیر‌مجموعه شدن) رها کنید.
        </p>
        <div class="space-y-1.5">
            @foreach ($roots as $root)
                @include('livewire.admin.partials.category-row', ['category' => $root, 'depth' => 0])
            @endforeach
        </div>
    </div>
</div>

<style>
    .cat-drop-before  { box-shadow: inset 0 3px 0 0 var(--cat-drop-color, #7c3aed); }
    .cat-drop-after   { box-shadow: inset 0 -3px 0 0 var(--cat-drop-color, #7c3aed); }
    .cat-drop-inside  { outline: 2px dashed #7c3aed; outline-offset: -2px; }
    [draggable="true"] { -webkit-user-drag: element; user-select: none; }
</style>
