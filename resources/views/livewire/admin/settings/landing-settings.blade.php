<div class="ip-fade-up space-y-5">

    {{-- ── page header ── --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-extrabold">صفحهٔ اصلی (معرفی)</h2>
            <p class="mt-1 text-xs text-slate-400">همهٔ متن‌ها و فهرست‌های صفحهٔ معرفی + ترتیب و نمایش بخش‌ها</p>
        </div>
    </div>

    @php
        $inp = 'w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800';
        $lbl = 'mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-400';
        $del = 'grid size-8 shrink-0 place-items-center rounded-lg border border-rose-200 text-rose-500 transition hover:bg-rose-50 dark:border-rose-900/60 dark:text-rose-400 dark:hover:bg-rose-950/40';
        $add = 'inline-flex items-center gap-1.5 rounded-lg border border-dashed border-slate-300 px-3 py-2 text-[11px] font-extrabold text-slate-500 transition hover:border-primary-400 hover:text-primary-600 dark:border-slate-700 dark:text-slate-400 dark:hover:text-primary-300';
        $saveBtn = 'inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60';
        $resetBtn = 'inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3.5 py-2 text-[11px] font-bold text-slate-500 transition hover:border-rose-300 hover:text-rose-500 dark:border-slate-700 dark:text-slate-400';
        $subCard = 'rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700/70 dark:bg-slate-800/40';
        $iconNames = ['menu','bell','search','settings','home','store','bag','cart','heart','user','users','wallet','package','chart','trending-up','file-text','message','clock','flame','timer','shield','zap','rocket','lock','filter','phone','instagram','check','megaphone','coins','list','folder','star','credit-card','download','headset','sparkles','info','layout-grid','palette','smartphone','globe','puzzle','image','image-plus','truck','map-pin','calendar','camera','eye','tag','circle-check','layers','upload','database','external-link','sliders','qr-code','gift'];
    @endphp

    <datalist id="ip-icon-list">
        @foreach ($iconNames as $name)
            <option value="{{ $name }}"></option>
        @endforeach
    </datalist>

    {{-- ── ۱) ترتیب و نمایش بخش‌ها ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-start gap-3">
            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400">
                <x-icon name="layers" class="size-5" />
            </span>
            <div>
                <h3 class="text-sm font-extrabold">ترتیب و نمایش بخش‌ها</h3>
                <p class="mt-1 text-[11px] leading-5 text-slate-400">
                    با فلش‌ها ترتیب بخش‌ها را عوض کنید و با کلید، هر بخش را خاموش/روشن کنید. تغییرات همین لحظه روی صفحهٔ اصلی اعمال و ذخیره می‌شوند.
                </p>
            </div>
        </div>

        <ul class="mt-4 space-y-2">
            @foreach ($order as $oi => $key)
                @php($meta = $sectionsMeta[$key] ?? null)
                @if ($meta === null) @continue @endif
                <li wire:key="order-{{ $key }}"
                    class="flex items-center gap-3 rounded-xl border px-3 py-2.5 transition {{ ($enabled[$key] ?? true)
                        ? 'border-slate-200 bg-slate-50/60 dark:border-slate-700 dark:bg-slate-800/40'
                        : 'border-dashed border-slate-200 bg-slate-50/30 opacity-60 dark:border-slate-700/70 dark:bg-slate-800/20' }}">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="{{ $meta['icon'] }}" class="size-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-xs font-extrabold">{{ $meta['label'] }}</div>
                        <div class="truncate text-[10px] text-slate-400">{{ $meta['description'] }}</div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1" role="group" aria-label="جابه‌جایی {{ $meta['label'] }}">
                        <button type="button" wire:click="moveUp('{{ $key }}')" @disabled($oi === 0)
                                class="grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-primary-300 hover:text-primary-600 disabled:opacity-30 dark:border-slate-700 dark:text-slate-400"
                                aria-label="انتقال به بالا">
                            <x-icon name="chevron-up" class="size-4" />
                        </button>
                        <button type="button" wire:click="moveDown('{{ $key }}')" @disabled($oi === count($order) - 1)
                                class="grid size-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-primary-300 hover:text-primary-600 disabled:opacity-30 dark:border-slate-700 dark:text-slate-400"
                                aria-label="انتقال به پایین">
                            <x-icon name="chevron-down" class="size-4" />
                        </button>
                    </div>

                    <button type="button" wire:click="toggleSection('{{ $key }}')" wire:loading.attr="disabled" wire:target="toggleSection"
                            role="switch" aria-checked="{{ ($enabled[$key] ?? true) ? 'true' : 'false' }}"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center">
                        <span class="h-6 w-11 rounded-full transition {{ ($enabled[$key] ?? true) ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-700' }}"></span>
                        <span class="absolute start-0.5 top-0.5 size-5 rounded-full bg-white shadow transition {{ ($enabled[$key] ?? true) ? 'start-[1.375rem]' : '' }}"></span>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- ── ۲) ویرایشگر هر بخش ── --}}
    @php($sectionKeys = array_keys($sectionsMeta))
    @foreach ($sectionKeys as $sKey)
        @php($meta = $sectionsMeta[$sKey])
        <details class="group rounded-2xl border border-slate-200 bg-white transition open:shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="sec-edit-{{ $sKey }}">
            <summary class="flex cursor-pointer list-none items-center gap-3 px-5 py-4 [&::-webkit-details-marker]:hidden">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                    <x-icon name="{{ $meta['icon'] }}" class="size-4" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-extrabold">{{ $meta['label'] }}</span>
                    <span class="block truncate text-[10px] text-slate-400">{{ $meta['description'] }}</span>
                </span>
                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-400 transition group-open:rotate-180 dark:bg-slate-800">
                    <x-icon name="chevron-down" class="size-4" />
                </span>
            </summary>

            <div class="space-y-4 border-t border-slate-100 px-5 py-5 dark:border-slate-800">

                {{-- ═══════════ HERO ═══════════ --}}
                @if ($sKey === 'hero')
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label class="{{ $lbl }}">متن بج بالا</label>
                            <input type="text" wire:model.blur="content.hero.badge" class="{{ $inp }}">
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="{{ $lbl }}">تیتر — قبل</label>
                                <input type="text" wire:model.blur="content.hero.title_pre" class="{{ $inp }}">
                            </div>
                            <div>
                                <label class="{{ $lbl }}">تیتر — هایلایت</label>
                                <input type="text" wire:model.blur="content.hero.title_highlight" class="{{ $inp }}">
                            </div>
                            <div>
                                <label class="{{ $lbl }}">تیتر — بعد</label>
                                <input type="text" wire:model.blur="content.hero.title_post" class="{{ $inp }}">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="{{ $lbl }}">توضیح زیر تیتر</label>
                        <textarea rows="3" wire:model.blur="content.hero.lead" class="{{ $inp }}"></textarea>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            <p class="mb-2 text-[11px] font-black text-slate-500 dark:text-slate-400">دکمهٔ اصلی</p>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" wire:model.blur="content.hero.cta1_label" class="{{ $inp }}" placeholder="عنوان دکمه">
                                <input type="text" wire:model.blur="content.hero.cta1_href" class="{{ $inp }}" dir="ltr" placeholder="/seller">
                            </div>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            <p class="mb-2 text-[11px] font-black text-slate-500 dark:text-slate-400">دکمهٔ دوم</p>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" wire:model.blur="content.hero.cta2_label" class="{{ $inp }}" placeholder="عنوان دکمه">
                                <input type="text" wire:model.blur="content.hero.cta2_href" class="{{ $inp }}" dir="ltr" placeholder="/shop">
                            </div>
                        </div>
                    </div>

                    <div class="{{ $subCard }}">
                        <p class="mb-2 text-[11px] font-black text-slate-500 dark:text-slate-400">تیک‌های اعتماد زیر دکمه‌ها</p>
                        <div class="space-y-2">
                            @foreach ($content['hero']['ticks'] as $ti => $tick)
                                <div class="flex items-center gap-2" wire:key="tick-{{ $ti }}">
                                    <x-icon name="circle-check" class="size-4 shrink-0 text-primary-500" />
                                    <input type="text" wire:model.blur="content.hero.ticks.{{ $ti }}" class="{{ $inp }}">
                                    <button type="button" class="{{ $del }}" wire:click="removeRow('hero.ticks.{{ $ti }}')" aria-label="حذف">
                                        <x-icon name="trash" class="size-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="{{ $add }} mt-3" wire:click="addRow('hero.ticks')">
                            <x-icon name="plus" class="size-3.5" /> افزودن تیک
                        </button>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            <p class="mb-2 text-[11px] font-black text-slate-500 dark:text-slate-400">چیپ شناور ۱</p>
                            <input type="text" wire:model.blur="content.hero.chip1_title" class="{{ $inp }} mb-2" placeholder="عنوان">
                            <input type="text" wire:model.blur="content.hero.chip1_sub" class="{{ $inp }}" placeholder="زیرنویس (خالی = مخفی)">
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            <p class="mb-2 text-[11px] font-black text-slate-500 dark:text-slate-400">چیپ شناور ۲</p>
                            <input type="text" wire:model.blur="content.hero.chip2_title" class="{{ $inp }} mb-2" placeholder="عنوان">
                            <input type="text" wire:model.blur="content.hero.chip2_sub" class="{{ $inp }}" placeholder="زیرنویس (خالی = مخفی)">
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            <p class="mb-2 text-[11px] font-black text-slate-500 dark:text-slate-400">چیپ شناور ۳ (دسکتاپ)</p>
                            <input type="text" wire:model.blur="content.hero.chip3_title" class="{{ $inp }}" placeholder="عنوان">
                        </div>
                    </div>
                @endif

                {{-- ═══════════ STATS ═══════════ --}}
                @if ($sKey === 'stats')
                    <div class="space-y-2">
                        @foreach ($content['stats']['items'] as $si => $item)
                            <div class="flex items-end gap-2" wire:key="stat-{{ $si }}">
                                <div class="w-20 shrink-0">
                                    <label class="{{ $lbl }}">عدد</label>
                                    <input type="text" wire:model.blur="content.stats.items.{{ $si }}.n" class="{{ $inp }} text-center">
                                </div>
                                <div class="flex-1">
                                    <label class="{{ $lbl }}">برچسب</label>
                                    <input type="text" wire:model.blur="content.stats.items.{{ $si }}.l" class="{{ $inp }}">
                                </div>
                                <button type="button" class="{{ $del }}" wire:click="removeRow('stats.items.{{ $si }}')" aria-label="حذف">
                                    <x-icon name="trash" class="size-4" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="{{ $add }}" wire:click="addRow('stats.items')">
                        <x-icon name="plus" class="size-3.5" /> افزودن آمار
                    </button>
                @endif

                {{-- ═══════════ INFRA MARQUEE ═══════════ --}}
                @if ($sKey === 'infra')
                    <div>
                        <label class="{{ $lbl }}">خط بالای نوار متحرک</label>
                        <input type="text" wire:model.blur="content.infra.title" class="{{ $inp }}">
                    </div>
                    <div class="max-w-[220px]">
                        <label class="{{ $lbl }}">مدت یک دور کامل (ثانیه)</label>
                        <input type="number" min="6" max="120" wire:model.blur="content.infra.speed" class="{{ $inp }}" dir="ltr">
                    </div>
                    <div class="{{ $subCard }}">
                        <p class="mb-2 text-[11px] font-black text-slate-500 dark:text-slate-400">آیتم‌های نوار متحرک (هر آیتم یک پیل)</p>
                        <div class="space-y-2">
                            @foreach ($content['infra']['items'] as $ii => $item)
                                <div class="flex items-center gap-2" wire:key="infra-{{ $ii }}">
                                    <span class="w-6 shrink-0 text-center text-[10px] font-black text-slate-400">{{ $ii + 1 }}</span>
                                    <input type="text" wire:model.blur="content.infra.items.{{ $ii }}" class="{{ $inp }}">
                                    <button type="button" class="{{ $del }}" wire:click="removeRow('infra.items.{{ $ii }}')" aria-label="حذف">
                                        <x-icon name="trash" class="size-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="{{ $add }} mt-3" wire:click="addRow('infra.items')">
                            <x-icon name="plus" class="size-3.5" /> افزودن آیتم
                        </button>
                    </div>
                @endif

                {{-- ═══════════ FEATURES ═══════════ --}}
                @if ($sKey === 'features')
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label class="{{ $lbl }}">سرنویس (ریز بالای تیتر)</label>
                            <input type="text" wire:model.blur="content.features.eyebrow" class="{{ $inp }}">
                        </div>
                        <div>
                            <label class="{{ $lbl }}">تیتر بخش</label>
                            <input type="text" wire:model.blur="content.features.title" class="{{ $inp }}">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $lbl }}">توضیح</label>
                        <textarea rows="2" wire:model.blur="content.features.lead" class="{{ $inp }}"></textarea>
                    </div>
                    <div class="{{ $subCard }}">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <p class="text-[11px] font-black text-slate-500 dark:text-slate-400">تیتر فهرست جزئیات</p>
                            <input type="text" wire:model.blur="content.features.small_title" class="{{ $inp }} max-w-[280px]">
                        </div>
                        <p class="mb-2 text-[11px] font-black text-slate-500 dark:text-slate-400">آیتم‌های جزئیات (آیکون + متن)</p>
                        <div class="space-y-2">
                            @foreach ($content['features']['small'] as $fi => $item)
                                <div class="flex items-center gap-2" wire:key="feat-small-{{ $fi }}">
                                    <input type="text" list="ip-icon-list" wire:model.blur="content.features.small.{{ $fi }}.icon" class="{{ $inp }} max-w-[120px]" dir="ltr" aria-label="نام آیکون">
                                    <input type="text" wire:model.blur="content.features.small.{{ $fi }}.t" class="{{ $inp }}">
                                    <button type="button" class="{{ $del }}" wire:click="removeRow('features.small.{{ $fi }}')" aria-label="حذف">
                                        <x-icon name="trash" class="size-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="{{ $add }} mt-3" wire:click="addRow('features.small')">
                            <x-icon name="plus" class="size-3.5" /> افزودن جزئیات
                        </button>
                    </div>
                    <p class="rounded-xl bg-slate-50 px-3 py-2.5 text-[10px] font-semibold leading-5 text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                        شش کارت بزرگِ گرید (فروشگاه با آیدی اینستاگرام، ورود OTP، کیف پول، ارسال، تخفیف، اپ‌ها و درگاه‌ها) تصویری/تزئینی‌اند و ثابت می‌مانند؛ متن‌های اصلی بخش از همین فرم کنترل می‌شوند.
                    </p>
                @endif

                {{-- ═══════════ PANELS ═══════════ --}}
                @if ($sKey === 'panels')
                    <div class="grid gap-4 lg:grid-cols-3">
                        <div>
                            <label class="{{ $lbl }}">سرنویس</label>
                            <input type="text" wire:model.blur="content.panels.eyebrow" class="{{ $inp }}">
                        </div>
                        <div class="lg:col-span-2">
                            <label class="{{ $lbl }}">تیتر بخش</label>
                            <input type="text" wire:model.blur="content.panels.title" class="{{ $inp }}">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $lbl }}">توضیح</label>
                        <input type="text" wire:model.blur="content.panels.lead" class="{{ $inp }}">
                    </div>

                    <div class="space-y-3">
                        @foreach ($content['panels']['cards'] as $pci => $card)
                            <div class="{{ $subCard }}" wire:key="panel-card-{{ $pci }}">
                                <div class="mb-3 flex items-center justify-between">
                                    <p class="text-[11px] font-black text-slate-500 dark:text-slate-400">کارت {{ $pci + 1 }}</p>
                                    <button type="button" class="{{ $del }}" wire:click="removeRow('panels.cards.{{ $pci }}')" aria-label="حذف کارت">
                                        <x-icon name="trash" class="size-4" />
                                    </button>
                                </div>
                                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                    <div>
                                        <label class="{{ $lbl }}">آیکون</label>
                                        <input type="text" list="ip-icon-list" wire:model.blur="content.panels.cards.{{ $pci }}.icon" class="{{ $inp }}" dir="ltr">
                                    </div>
                                    <div>
                                        <label class="{{ $lbl }}">عنوان</label>
                                        <input type="text" wire:model.blur="content.panels.cards.{{ $pci }}.title" class="{{ $inp }}">
                                    </div>
                                    <div>
                                        <label class="{{ $lbl }}">بج (خالی = مخفی)</label>
                                        <input type="text" wire:model.blur="content.panels.cards.{{ $pci }}.badge" class="{{ $inp }}">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="{{ $lbl }}">متن لینک</label>
                                            <input type="text" wire:model.blur="content.panels.cards.{{ $pci }}.link_label" class="{{ $inp }}">
                                        </div>
                                        <div>
                                            <label class="{{ $lbl }}">مقصد</label>
                                            <input type="text" wire:model.blur="content.panels.cards.{{ $pci }}.link_href" class="{{ $inp }}" dir="ltr">
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-3 mb-2 text-[11px] font-black text-slate-500 dark:text-slate-400">ردیف‌های تیک داخل کارت</p>
                                <div class="space-y-2">
                                    @foreach ($card['items'] as $pii => $pitem)
                                        <div class="flex items-center gap-2" wire:key="panel-item-{{ $pci }}-{{ $pii }}">
                                            <x-icon name="check" class="size-4 shrink-0 text-primary-500" />
                                            <input type="text" wire:model.blur="content.panels.cards.{{ $pci }}.items.{{ $pii }}" class="{{ $inp }}">
                                            <button type="button" class="{{ $del }}" wire:click="removeRow('panels.cards.{{ $pci }}.items.{{ $pii }}')" aria-label="حذف">
                                                <x-icon name="trash" class="size-4" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="{{ $add }} mt-3" wire:click="addRow('panels.cards.{{ $pci }}.items')">
                                    <x-icon name="plus" class="size-3.5" /> افزودن ردیف
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="{{ $add }}" wire:click="addRow('panels.cards')">
                        <x-icon name="plus" class="size-3.5" /> افزودن کارت
                    </button>
                @endif

                {{-- ═══════════ HOW ═══════════ --}}
                @if ($sKey === 'how')
                    <div class="grid gap-4 lg:grid-cols-3">
                        <div>
                            <label class="{{ $lbl }}">سرنویس</label>
                            <input type="text" wire:model.blur="content.how.eyebrow" class="{{ $inp }}">
                        </div>
                        <div>
                            <label class="{{ $lbl }}">تیتر بخش</label>
                            <input type="text" wire:model.blur="content.how.title" class="{{ $inp }}">
                        </div>
                        <div>
                            <label class="{{ $lbl }}">توضیح</label>
                            <input type="text" wire:model.blur="content.how.lead" class="{{ $inp }}">
                        </div>
                    </div>
                    <div class="space-y-3">
                        @foreach ($content['how']['steps'] as $hi => $step)
                            <div class="{{ $subCard }}" wire:key="how-step-{{ $hi }}">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-[11px] font-black text-slate-500 dark:text-slate-400">قدم {{ $hi + 1 }}</p>
                                    <button type="button" class="{{ $del }}" wire:click="removeRow('how.steps.{{ $hi }}')" aria-label="حذف قدم">
                                        <x-icon name="trash" class="size-4" />
                                    </button>
                                </div>
                                <div class="grid gap-2 lg:grid-cols-[120px_1fr_2fr]">
                                    <div>
                                        <label class="{{ $lbl }}">آیکون</label>
                                        <input type="text" list="ip-icon-list" wire:model.blur="content.how.steps.{{ $hi }}.icon" class="{{ $inp }}" dir="ltr">
                                    </div>
                                    <div>
                                        <label class="{{ $lbl }}">عنوان</label>
                                        <input type="text" wire:model.blur="content.how.steps.{{ $hi }}.title" class="{{ $inp }}">
                                    </div>
                                    <div>
                                        <label class="{{ $lbl }}">توضیح</label>
                                        <input type="text" wire:model.blur="content.how.steps.{{ $hi }}.desc" class="{{ $inp }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="{{ $add }}" wire:click="addRow('how.steps')">
                        <x-icon name="plus" class="size-3.5" /> افزودن قدم
                    </button>
                @endif

                {{-- ═══════════ FAQ ═══════════ --}}
                @if ($sKey === 'faq')
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label class="{{ $lbl }}">سرنویس</label>
                            <input type="text" wire:model.blur="content.faq.eyebrow" class="{{ $inp }}">
                        </div>
                        <div>
                            <label class="{{ $lbl }}">تیتر بخش</label>
                            <input type="text" wire:model.blur="content.faq.title" class="{{ $inp }}">
                        </div>
                    </div>
                    <div class="space-y-3">
                        @foreach ($content['faq']['items'] as $qi => $qa)
                            <div class="{{ $subCard }}" wire:key="faq-{{ $qi }}">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-[11px] font-black text-slate-500 dark:text-slate-400">پرسش {{ $qi + 1 }}</p>
                                    <button type="button" class="{{ $del }}" wire:click="removeRow('faq.items.{{ $qi }}')" aria-label="حذف پرسش">
                                        <x-icon name="trash" class="size-4" />
                                    </button>
                                </div>
                                <input type="text" wire:model.blur="content.faq.items.{{ $qi }}.q" class="{{ $inp }} mb-2" placeholder="سوال">
                                <textarea rows="2" wire:model.blur="content.faq.items.{{ $qi }}.a" class="{{ $inp }}" placeholder="پاسخ"></textarea>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="{{ $add }}" wire:click="addRow('faq.items')">
                        <x-icon name="plus" class="size-3.5" /> افزودن پرسش
                    </button>
                @endif

                {{-- ═══════════ CTA ═══════════ --}}
                @if ($sKey === 'cta')
                    <div>
                        <label class="{{ $lbl }}">متن بج</label>
                        <input type="text" wire:model.blur="content.cta.badge" class="{{ $inp }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}">تیتر</label>
                        <input type="text" wire:model.blur="content.cta.title" class="{{ $inp }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}">توضیح</label>
                        <textarea rows="2" wire:model.blur="content.cta.lead" class="{{ $inp }}"></textarea>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            <p class="mb-2 text-[11px] font-black text-slate-500 dark:text-slate-400">دکمهٔ اصلی (روشن)</p>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" wire:model.blur="content.cta.btn1_label" class="{{ $inp }}" placeholder="عنوان">
                                <input type="text" wire:model.blur="content.cta.btn1_href" class="{{ $inp }}" dir="ltr" placeholder="/seller">
                            </div>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            <p class="mb-2 text-[11px] font-black text-slate-500 dark:text-slate-400">دکمهٔ دوم (حاشیه‌دار)</p>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" wire:model.blur="content.cta.btn2_label" class="{{ $inp }}" placeholder="عنوان">
                                <input type="text" wire:model.blur="content.cta.btn2_href" class="{{ $inp }}" dir="ltr" placeholder="/shop">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ═══════════ FOOTER ═══════════ --}}
                @if ($sKey === 'footer')
                    <div>
                        <label class="{{ $lbl }}">متن معرفی کنار لوگو</label>
                        <textarea rows="2" wire:model.blur="content.footer.about" class="{{ $inp }}"></textarea>
                    </div>

                    <div class="space-y-3">
                        @foreach ($content['footer']['groups'] as $fgi => $group)
                            <div class="{{ $subCard }}" wire:key="fgroup-{{ $fgi }}">
                                <div class="mb-3 flex items-center justify-between gap-2">
                                    <p class="text-[11px] font-black text-slate-500 dark:text-slate-400">منوی دسترسی سریع {{ $fgi + 1 }}</p>
                                    <button type="button" class="{{ $del }}" wire:click="removeRow('footer.groups.{{ $fgi }}')" aria-label="حذف منو">
                                        <x-icon name="trash" class="size-4" />
                                    </button>
                                </div>
                                <div class="mb-3">
                                    <label class="{{ $lbl }}">عنوان منو</label>
                                    <input type="text" wire:model.blur="content.footer.groups.{{ $fgi }}.title" class="{{ $inp }}">
                                </div>
                                <div class="space-y-2">
                                    @foreach ($group['links'] as $fli => $link)
                                        <div class="flex items-center gap-2" wire:key="flink-{{ $fgi }}-{{ $fli }}">
                                            <input type="text" wire:model.blur="content.footer.groups.{{ $fgi }}.links.{{ $fli }}.label" class="{{ $inp }}" placeholder="عنوان لینک">
                                            <input type="text" wire:model.blur="content.footer.groups.{{ $fgi }}.links.{{ $fli }}.href" class="{{ $inp }}" dir="ltr" placeholder="/shop یا #features">
                                            <button type="button" class="{{ $del }}" wire:click="removeRow('footer.groups.{{ $fgi }}.links.{{ $fli }}')" aria-label="حذف لینک">
                                                <x-icon name="trash" class="size-4" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="{{ $add }} mt-3" wire:click="addRow('footer.groups.{{ $fgi }}.links')">
                                    <x-icon name="plus" class="size-3.5" /> افزودن لینک
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="{{ $add }}" wire:click="addRow('footer.groups')">
                        <x-icon name="plus" class="size-3.5" /> افزودن منوی جدید
                    </button>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label class="{{ $lbl }}">خط کپی‌رایت — {year} و {app} جایگذاری می‌شوند</label>
                            <input type="text" wire:model.blur="content.footer.copyright" class="{{ $inp }}">
                        </div>
                        <div>
                            <label class="{{ $lbl }}">خط سمت چپ (تقدیم)</label>
                            <input type="text" wire:model.blur="content.footer.credit" class="{{ $inp }}">
                        </div>
                    </div>
                @endif

                {{-- ── save / reset ── --}}
                <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <button type="button" wire:click="saveSection('{{ $sKey }}')" wire:loading.attr="disabled" wire:target="saveSection"
                            class="{{ $saveBtn }}">
                        <x-icon name="check" class="size-4" />
                        ذخیرهٔ این بخش
                    </button>
                    <button type="button" wire:click="resetSection('{{ $sKey }}')"
                            wire:confirm="این بخش دقیقاً به متن‌های پیش‌فرض برمی‌گردد. مطمئنید؟"
                            class="{{ $resetBtn }}">
                        <x-icon name="rotate" class="size-3.5" />
                        بازگردانی پیش‌فرض
                    </button>
                    <span class="text-[10px] text-slate-400">تغییرات تا زمان ذخیره فقط در همین فرم است.</span>
                </div>
            </div>
        </details>
    @endforeach
</div>
