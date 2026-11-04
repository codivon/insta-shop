                {{-- ── دسته‌بندی سراسری: دستهٔ اصلی + جستجو در زیر‌دسته‌ها ── --}}
                <div class="mt-3">
                    <label class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">دسته‌بندی <span class="text-rose-500" title="الزامی">*</span></label>

                    {{-- مرحله ۱ — دسته اصلی (سراسری، مدیریت‌شده توسط ادمین) --}}
                    <div class="relative" @click.outside="rootOpen = false">
                        <button type="button" @click="rootOpen = !rootOpen" :aria-expanded="rootOpen"
                                class="flex w-full items-center justify-between gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition hover:border-slate-300 focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                            <span class="truncate" :class="$wire.categoryRootId > 0 ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400'"
                                  x-text="$wire.categoryRootId > 0 ? (({{ str_replace('"', "'", $categoryRoots->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->toJson()) }}).find(c => c.id === $wire.categoryRootId)?.name ?? 'دستهٔ نامعتبر') : 'انتخاب دسته اصلی…'">
                                انتخاب دسته اصلی
                            </span>
                            <span class="shrink-0 text-slate-400 transition-transform" :class="rootOpen ? 'rotate-180' : ''">
                                <x-icon name="chevron-down" class="size-4" />
                            </span>
                        </button>

                        <div x-cloak x-show="rootOpen"
                             x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="absolute inset-x-0 top-full z-30 mt-1.5 max-h-60 overflow-y-auto scroll-slim rounded-2xl border border-slate-200 bg-white p-1.5 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                            @foreach ($categoryRoots as $root)
                                <button type="button" wire:key="root-opt-{{ $root->id }}"
                                        @click="$wire.set('categoryRootId', {{ $root->id }}); rootOpen = false; catQuery = ''"
                                        class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-[11px] font-bold transition"
                                        :class="$wire.categoryRootId === {{ $root->id }} ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300' : 'text-slate-500 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'">
                                    <x-icon name="tag" class="size-3.5 opacity-50" />
                                    {{ $root->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- مرحله ۲ — زیر‌دسته با جستجو (فقط زیر‌مجموعهٔ دستهٔ اصلی انتخابی) --}}
                    @if ($categoryRootId > 0)
                        <div class="relative mt-2" @click.outside="catOpen = false">
                            <button type="button" @click="catOpen = !catOpen" :aria-expanded="catOpen"
                                    class="flex w-full items-center justify-between gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition hover:border-slate-300 focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                                <span class="truncate" :class="$wire.categoryId > 0 ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400'">
                                    {{ $selectedCategoryLabel !== '' ? $selectedCategoryLabel : 'انتخاب زیر‌دسته (اختیاری — کل دسته اصلی)' }}
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
                                        <input type="search" x-model="catQuery" placeholder="جستجوی زیر‌دسته..." x-ref="catSearch"
                                               class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-9 pl-3 text-[11px] font-semibold outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                                    </div>
                                </div>
                                <div class="scroll-slim max-h-44 overflow-y-auto p-1.5">
                                    {{-- انتخاب خودِ دسته اصلی (کل شاخه) --}}
                                    <button type="button" x-show="catQuery.trim() === ''" wire:key="cat-opt-root-{{ $categoryRootId }}"
                                            @click="$wire.set('categoryId', $wire.categoryRootId); catOpen = false"
                                            class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-[11px] font-bold transition"
                                            :class="$wire.categoryId === $wire.categoryRootId ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300' : 'text-slate-500 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'">
                                        <x-icon name="tag" class="size-3.5 opacity-50" />
                                        {{ $categoryRoots->firstWhere('id', $categoryRootId)?->name }} (کل دسته)
                                    </button>
                                    @foreach ($categoryOptions as $opt)
                                        <button type="button" x-show="catQuery.trim() === '' || '{{ e($opt['path']) }}'.includes(catQuery.trim())" wire:key="cat-opt-{{ $opt['id'] }}"
                                                @click="$wire.set('categoryId', {{ $opt['id'] }}); catOpen = false"
                                                class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-right text-[11px] font-bold transition"
                                                :class="$wire.categoryId === {{ $opt['id'] }} ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300' : 'text-slate-500 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'">
                                            <x-icon name="tag" class="size-3.5 shrink-0 opacity-50" />
                                            <span class="truncate">{{ $opt['path'] }}</span>
                                            <span x-show="$wire.categoryId === {{ $opt['id'] }}" class="mr-auto grid shrink-0 place-items-center" wire:key="cat-check-{{ $opt['id'] }}">
                                                <x-icon name="circle-check" class="size-3.5 text-primary-600" />
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @error('categoryId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    @error('categoryRootId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>
