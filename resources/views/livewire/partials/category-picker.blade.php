{{-- ═══════════════════════════════════════════════════════════════════════════
    دسته‌بندی سراسری (مدیریت‌شده توسط ادمین) — پیکر بات‌شیت مثل باسلام:
    یک فیلد → شیت پایین با جستجوی زنده + درخت دسته‌ها (شاخه‌ها با بج
    «N زیرشاخه» و اتصال‌خط‌چین) + انتخاب تکی (رادیویی) + نوار «تایید».
    استفاده در: فرم محصول فروشنده و ادمین (هر دو `categoryId` را ست می‌کنند).
    ── نیاز به داده از کامپوننت: categoryTreeJson / selectedCategoryLabel ──
═════════════════════════════════════════════════════════════════════════ --}}
<div x-data="ipCatPicker" class="mt-3">
    {{-- درخت دسته‌ها به‌صورت JSON — یک‌بار رندر، جستجو و پرده‌ها سمت کلاینت.
         تگ script عنصر raw-text است (اینتیتی decode نمی‌شود) → خروجی خام؛
         فقط «<» به \u003c امن‌سازی می‌شود تا هرگز تگ نبندد. --}}
    <script type="application/json" x-ref="cat-tree">{!! str_replace('<', '\u003c', $categoryTreeJson) !!}</script>

    <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">
        دسته‌بندی <span class="text-rose-500" title="الزامی">*</span>
    </label>

    {{-- فیلد انتخاب — مسیر کامل دستهٔ انتخابی یا placeholder --}}
    <button type="button" @click="openPicker()" :aria-expanded="open"
            class="flex w-full items-center justify-between gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs outline-none transition hover:border-slate-300 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
        @if ($selectedCategoryLabel !== '')
            <span class="truncate font-bold text-slate-700 dark:text-slate-200">{{ $selectedCategoryLabel }}</span>
        @else
            <span class="font-semibold text-slate-400">انتخاب دسته‌بندی…</span>
        @endif
        <span class="shrink-0 text-slate-400"><x-icon name="chevron-left" class="size-4" /></span>
    </button>
    @error('categoryId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

    {{-- ═══ بات‌شیت پیکر (موبایل: شیت پایین — دسکتاپ: مودال وسط) ═══ --}}
    <x-modal-panel open="open" max-width="md" z-class="z-[80]" labelledby="cat-picker-title" in-frame
                   close-js="open = false">

        <x-slot:header>
            <h2 id="cat-picker-title" class="sr-only">انتخاب دسته‌بندی</h2>

            {{-- جستجو — مثل باسلام، نوار اول شیت و چسبیده به بالا --}}
            <div class="border-b border-slate-100 p-3 dark:border-slate-800">
                <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 transition focus-within:border-primary-400 focus-within:bg-white dark:border-slate-700 dark:bg-slate-800">
                    <x-icon name="search" class="size-4 shrink-0 text-slate-400" />
                    <input type="search" x-model="query" placeholder="جستجوی دسته‌بندی…"
                           x-effect="if (open === false) query = ''"
                           class="w-full bg-transparent text-xs font-semibold outline-none placeholder:text-slate-400">
                </label>
            </div>
        </x-slot:header>

        {{-- بدنه — درخت یا نتایج جستجو --}}
        <nav class="min-h-24 px-2 py-1.5" aria-label="درخت دسته‌بندی‌ها">

            {{-- حالت جستجو: فهرست تخت نتایج با مسیر کامل --}}
            <template x-if="searching">
                <div role="listbox" aria-label="نتایج جستجوی دسته‌بندی">
                    <template x-for="row in searchRows" :key="'s-'+row.id">
                        <div role="option" :aria-selected="picked === row.id" tabindex="0"
                             @click="pick(row.id)"
                             @keydown.enter.prevent="pick(row.id)"
                             class="flex cursor-pointer items-center gap-2.5 rounded-xl px-2 py-2.5 transition"
                             :class="picked === row.id ? 'bg-slate-100 dark:bg-slate-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'">
                            <span class="grid size-5 shrink-0 place-items-center rounded-full border-2 transition"
                                  :class="picked === row.id ? 'border-primary-600 bg-primary-600' : 'border-slate-300 dark:border-slate-600'">
                                <x-icon name="check" class="size-3 text-white" x-show="picked === row.id" x-cloak />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-xs font-extrabold text-slate-700 dark:text-slate-200" x-text="row.name"></span>
                                <span class="mt-0.5 block truncate text-[9.5px] font-semibold text-slate-400" x-text="row.path"></span>
                            </span>
                        </div>
                    </template>
                    <p x-show="searchRows.length === 0" class="px-3 py-10 text-center text-[11px] font-bold text-slate-400">
                        دسته‌بندی‌ای با این عبارت پیدا نشد.
                    </p>
                </div>
            </template>

            {{-- حالت درخت: شاخه‌ها با بج زیرشاخه + اتصال خط‌چین برای زیرشاخه‌ها --}}
            <template x-if="! searching">
                <div role="listbox" aria-label="دسته‌بندی‌ها">
                    <template x-for="row in treeRows" :key="row.id">
                        <div role="option" :aria-selected="picked === row.id" tabindex="0"
                             @click="pick(row.id)"
                             @keydown.enter.prevent="pick(row.id)"
                             class="relative flex cursor-pointer items-center gap-2 rounded-xl py-1.5 pl-2 transition"
                             :class="picked === row.id ? 'bg-slate-100 dark:bg-slate-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                             :style="'padding-inline-start: ' + (row.depth * 36 + 4) + 'px'">

                            {{-- خط‌چین اتصال زیرشاخه به شاخهٔ والد --}}
                            <span x-show="row.depth > 0" aria-hidden="true"
                                  class="absolute top-0 bottom-0 border-s border-dashed border-slate-200 dark:border-slate-700"
                                  :style="'inset-inline-start: ' + (row.depth * 36 - 2) + 'px'"></span>

                            {{-- نشانگر: شاخه = دکمهٔ پرده (chevron) — برگ = نقطه --}}
                            <button type="button" x-show="row.childCount > 0"
                                    @click.stop="toggle(row.id)"
                                    :aria-expanded="!! expanded[row.id]"
                                    :aria-label="(expanded[row.id] ? 'بستن' : 'باز کردن') + ' ' + row.name"
                                    class="grid size-7 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-200/70 dark:hover:bg-slate-700">
                                <x-icon name="chevron-left" class="size-3.5" x-show="! expanded[row.id]" />
                                <x-icon name="chevron-down" class="size-3.5" x-show="!! expanded[row.id]" x-cloak />
                            </button>
                            <span x-show="row.childCount === 0" class="w-7 shrink-0 text-center" aria-hidden="true">
                                <span class="inline-block size-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                            </span>

                            {{-- دایرهٔ انتخاب (رادیو تک‌انتخابی) --}}
                            <span class="grid size-5 shrink-0 place-items-center rounded-full border-2 transition"
                                  :class="picked === row.id ? 'border-primary-600 bg-primary-600' : 'border-slate-300 dark:border-slate-600'">
                                <x-icon name="check" class="size-3 text-white" x-show="picked === row.id" x-cloak />
                            </span>

                            {{-- نام + بج تعداد زیرشاخه‌ها --}}
                            <span class="min-w-0 flex-1 truncate text-xs font-extrabold text-slate-700 dark:text-slate-200" x-text="row.name"></span>
                            <span x-show="row.childCount > 0"
                                  class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                <span x-text="faNum(row.childCount)"></span> زیرشاخه
                            </span>
                        </div>
                    </template>
                </div>
            </template>
        </nav>

        <x-slot:footer>
            {{-- نوار پایین: شمارنده + تایید — دقیقاً مثل باسلام --}}
            <div class="flex items-center justify-between gap-3 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                    <span x-text="faNum(picked === null ? 0 : 1)"></span>
                    مورد انتخاب شده
                </span>
                <button type="button" @click="confirmPick()" :disabled="picked === null"
                        class="rounded-xl bg-primary-600 px-7 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none">
                    تایید
                </button>
            </div>
        </x-slot:footer>
    </x-modal-panel>
</div>
