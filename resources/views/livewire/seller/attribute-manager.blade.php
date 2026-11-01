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
                    <div class="text-sm font-extrabold leading-5">ویژگی‌های فروشگاه</div>
                    <div class="text-[11px] text-slate-400">فیلدهای تنوع محصول (رنگ، سایز، گارانتی و ...)</div>
                </div>
            </div>
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">
        @if (session('attr-flash'))
            <div class="mb-3 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-[11px] font-bold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300" x-data x-show="true">
                <x-icon name="circle-check" class="size-4 shrink-0" />
                {{ session('attr-flash') }}
            </div>
        @endif

        {{-- ───────── Attribute cards ───────── --}}
        <div class="space-y-3">
            @forelse ($attributeList as $attribute)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900" wire:key="attr-{{ $attribute->id }}">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="grid size-8 shrink-0 place-items-center rounded-xl {{ $attribute->type === 'color'
                                    ? 'bg-gradient-to-br from-fuchsia-500 to-orange-400 text-white'
                                    : 'bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400' }}">
                                <x-icon name="{{ $attribute->type === 'color' ? 'palette' : ($attribute->type === 'text' ? 'file-text' : 'tag') }}" class="size-4" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="truncate text-xs font-extrabold">{{ $attribute->name }}</span>
                                    @if ($attribute->is_default)
                                        <span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-400 dark:bg-slate-800">پیش‌فرض</span>
                                    @endif
                                </div>
                                <div class="text-[10px] font-semibold text-slate-400">{{ $attribute->typeLabel() }} · {{ $attribute->values->count() }} مقدار</div>
                            </div>
                        </div>
                        <button type="button" wire:click="deleteAttribute({{ $attribute->id }})" wire:confirm="این ویژگی و همه مقادیرش حذف شود؟"
                                class="grid size-9 shrink-0 place-items-center rounded-xl border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-900/60 dark:hover:bg-rose-950/40"
                                aria-label="حذف ویژگی {{ $attribute->name }}">
                            <x-icon name="trash" class="size-4" />
                        </button>
                    </div>

                    {{-- values --}}
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @forelse ($attribute->values as $value)
                            <span class="group inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 py-1 pl-2 pr-2.5 text-[11px] font-bold text-slate-600 transition hover:border-rose-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                  wire:key="val-{{ $value->id }}">
                                @if ($attribute->type === 'color')
                                    <span class="size-3.5 rounded-full border border-slate-300 shadow-inner" style="background: {{ $value->color_hex ?? '#CBD5E1' }}"></span>
                                @endif
                                {{ $value->value }}
                                <button type="button" wire:click="deleteValue({{ $value->id }})"
                                        class="grid size-4 place-items-center rounded-full text-slate-400 transition hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-950"
                                        aria-label="حذف مقدار {{ $value->value }}">
                                    <x-icon name="x" class="size-3" />
                                </button>
                            </span>
                        @empty
                            <span class="text-[10px] font-semibold text-slate-400">هنوز مقداری ندارد.</span>
                        @endforelse
                    </div>

                    {{-- add value --}}
                    <form wire:submit="addValue({{ $attribute->id }})" class="mt-3 flex gap-2">
                        @if ($attribute->type === 'color')
                            <input type="color" value="{{ $newHex[$attribute->id] ?? '#D62976' }}"
                                   wire:model="newHex.{{ $attribute->id }}"
                                   class="size-10 shrink-0 cursor-pointer rounded-xl border border-slate-200 bg-white p-1 dark:border-slate-700 dark:bg-slate-800"
                                   aria-label="انتخاب رنگ">
                        @endif
                        <input type="text" placeholder="{{ $attribute->type === 'color' ? 'مثال: زرشکی' : 'مقدار جدید...' }}"
                               wire:model="newValue.{{ $attribute->id }}" maxlength="80"
                               class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-[11px] font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                        <button type="submit"
                                class="grid size-10 shrink-0 place-items-center rounded-xl bg-primary-600 text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-50"
                                wire:loading.attr="disabled" wire:target="addValue({{ $attribute->id }})"
                                aria-label="افزودن مقدار">
                            <span wire:loading wire:target="addValue({{ $attribute->id }})" class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                            <span wire:loading.remove wire:target="addValue({{ $attribute->id }})"><x-icon name="plus" class="size-4" /></span>
                        </button>
                    </form>
                    @error("newValue.{{ $attribute->id }}") <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                    <x-icon name="tag" class="mx-auto size-10 text-slate-300" />
                    <p class="mt-3 text-xs font-bold text-slate-500">هیچ ویژگی‌ای ندارید. اولین فیلد را اضافه کنید.</p>
                </div>
            @endforelse
        </div>

        {{-- ───────── Add attribute card ───────── --}}
        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="flex items-center gap-2 text-xs font-extrabold">
                <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="plus" class="size-3.5" /></span>
                افزودن ویژگی جدید
            </h3>
            <p class="mt-1.5 text-[10px] leading-5 text-slate-400">نام فیلد و نوع آن را انتخاب کنید؛ بعد از ساخت، مقادیرش را داخل همان کارت اضافه کنید.</p>

            <form wire:submit="addAttribute" class="mt-3 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="attr-name" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">نام ویژگی <span class="text-rose-500">*</span></label>
                        <input id="attr-name" type="text" wire:model="name" maxlength="80" placeholder="مثال: رنگ"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                        @error('name') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="attr-type" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">نوع ویژگی <span class="text-rose-500">*</span></label>
                        <select id="attr-type" wire:model="type"
                                class="w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">
                            @foreach (\App\Models\ShopAttribute::TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-1.5" aria-hidden="true">
                    @foreach (['رنگی' => 'from-fuchsia-500 to-orange-400', 'انتخابی' => 'bg-primary-600', 'متنی' => 'bg-slate-600'] as $label => $cls)
                        <div class="rounded-xl border border-slate-200 px-2 py-1.5 text-center text-[9px] font-bold text-slate-400 dark:border-slate-700">{{ $label }}</div>
                    @endforeach
                </div>
                <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-l from-fuchsia-600 via-primary-600 to-orange-500 py-3 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:brightness-105 active:scale-[.98] disabled:opacity-60"
                        wire:loading.attr="disabled" wire:target="addAttribute">
                    <span wire:loading wire:target="addAttribute" class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    <span wire:loading.remove wire:target="addAttribute">افزودن ویژگی</span>
                    <span wire:loading wire:target="addAttribute">در حال افزودن...</span>
                </button>
            </form>
        </div>
    </div>
</div>
