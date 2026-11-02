@props([
    // Livewire property the ISO (Gregorian Y-m-d) value binds to,
    // e.g. 'variants.0.discount_expires_at'
    'model'        => '',
    'placeholder'  => 'انتخاب تاریخ شمسی',
    'clearLabel'   => 'پاک کردن',
])

<div x-data="{
        open: false,
        view: 'days',
        isoLocal: '',
        jy: 0,
        jm: 1,
        months: {{ json_encode(['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند']) }},
        monthsShort: {{ json_encode(['فرو','ارد','خرد','تیر','مرد','شهر','مهر','آبا','آذر','دی','بهم','اسف']) }},
        weekdays: ['ش','ی','د','س','چ','پ','ج'],
        @if ($model !== '')
        get iso() { return $wire.get('{{ $model }}') ?? ''; },
        @else
        get iso() { return this.isoLocal; },
        @endif
        init() { this.syncView(); },
        syncView() {
            this.view = 'days';
            const j = window.jdateUtils.isoToJalali(this.iso);
            if (j) { this.jy = j.jy; this.jm = j.jm; return; }
            const t = window.jdateUtils.isoToJalali(this.todayIso());
            this.jy = t.jy; this.jm = t.jm;
        },
        todayIso() {
            const d = new Date(), p = (n) => String(n).padStart(2, '0');
            return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate());
        },
        get selected() { return window.jdateUtils.isoToJalali(this.iso); },
        get monthName() { return this.months[this.jm - 1] + ' ' + window.jdateUtils.faNum(this.jy); },
        get days() {
            const u = window.jdateUtils;
            const len = u.jMonthLen(this.jy, this.jm);
            const firstIso = u.jalaliToIso(this.jy, this.jm, 1);
            // Jalali week starts Saturday → Saturday = 0 … Friday = 6
            const dow = (new Date(firstIso + 'T00:00:00').getDay() + 1) % 7;
            const cells = [];
            for (let i = 0; i < dow; i++) cells.push(null);
            for (let d = 1; d <= len; d++) cells.push(d);
            return cells;
        },
        get yearWindow() {
            const base = this.jy - (this.jy % 12);
            const out = [];
            for (let y = base - 12; y <= base + 23; y++) out.push(y);
            return out;
        },
        get yearBase() { return this.jy - (this.jy % 12) - 12; },
        move(d) {
            let m = this.jm + d, y = this.jy;
            if (m < 1) { m = 12; y -= 1; }
            if (m > 12) { m = 1; y += 1; }
            this.jm = m; this.jy = y;
        },
        moveYear(d) { this.jy += d; },
        pickMonth(m) { this.jm = m; this.view = 'days'; },
        pickYear(y) { this.jy = y; this.view = 'days'; },
        pick(day) {
            if (!day) return;
            const iso = window.jdateUtils.jalaliToIso(this.jy, this.jm, day);
            @if ($model !== '')
            $wire.set('{{ $model }}', iso);
            @else
            this.isoLocal = iso;
            @endif
            this.open = false;
            this.view = 'days';
        },
        clear() {
            @if ($model !== '')
            $wire.set('{{ $model }}', '');
            @else
            this.isoLocal = '';
            @endif
            this.open = false;
        },
        today() {
            const t = window.jdateUtils.isoToJalali(this.todayIso());
            this.jy = t.jy; this.jm = t.jm;
            this.pick(t.jd);
        },
        fa: window.jdateUtils.faNum,
        toFa: window.jdateUtils.isoToFa,
     }"
     class="jdate-root" {{ $attributes }}>

    {{-- visible field (Jalali, readonly) — opens the inline calendar --}}
    <button type="button" x-on:click="open = !open; syncView()" :aria-expanded="open"
            class="group flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-right text-[11px] font-semibold outline-none transition hover:border-primary-300 focus:border-primary-400 dark:border-slate-600 dark:bg-slate-900 dark:hover:border-primary-700">
        <span class="truncate" :class="iso ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400'"
              x-text="iso ? toFa(iso) : '{{ $placeholder }}'">
            {{ $placeholder }}
        </span>
        <span class="flex shrink-0 items-center gap-1">
            <span x-show="iso" x-cloak x-on:click.stop="clear()"
                  class="grid size-5 place-items-center rounded-full text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40"
                  aria-label="{{ $clearLabel }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="size-3"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </span>
            <span class="grid size-6 place-items-center rounded-lg bg-white text-primary-500 shadow-sm transition group-hover:bg-primary-50 dark:bg-slate-800 dark:group-hover:bg-primary-950/40">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5">
                    <rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </span>
        </span>
    </button>

    {{-- inline expanding calendar — no popup clipping inside modals --}}
    <div x-cloak x-show="open"
         x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="mt-2 overflow-hidden rounded-2xl border border-primary-200 bg-white shadow-lg dark:border-primary-900 dark:bg-slate-900"
         role="group" aria-label="تقویم شمسی">

        {{-- header — month + year are BUTTONS opening their own pickers --}}
        <div class="flex items-center justify-between gap-1 border-b border-slate-100 bg-gradient-to-l from-primary-50/60 to-transparent px-2 py-2 dark:border-slate-800 dark:from-primary-950/30">
            <button type="button" x-show="view === 'days'" x-on:click="move(-1)" class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-primary-100 hover:text-primary-600 dark:hover:bg-slate-800" aria-label="ماه قبل">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <button type="button" x-show="view !== 'days'" x-cloak x-on:click="view = 'days'" class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-primary-100 hover:text-primary-600 dark:hover:bg-slate-800" aria-label="بازگشت به روزها">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="m15 18-6-6 6-6"/></svg>
            </button>

            <div class="flex items-center gap-1.5">
                {{-- month button → months grid --}}
                <button type="button" x-on:click="view = (view === 'months' ? 'days' : 'months')"
                        class="flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[11px] font-extrabold transition hover:bg-primary-100 dark:hover:bg-slate-800"
                        :class="view === 'months' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25 hover:bg-primary-700 dark:hover:bg-primary-700' : 'text-slate-700 dark:text-slate-200'"
                        :aria-expanded="view === 'months'">
                    <span x-text="months[jm - 1]"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="size-3"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                {{-- year button → years grid --}}
                <button type="button" x-on:click="view = (view === 'years' ? 'days' : 'years')"
                        class="flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[11px] font-extrabold transition hover:bg-primary-100 dark:hover:bg-slate-800"
                        :class="view === 'years' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25 hover:bg-primary-700 dark:hover:bg-primary-700' : 'text-slate-700 dark:text-slate-200'"
                        :aria-expanded="view === 'years'">
                    <span x-text="fa(jy)"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="size-3"><path d="m6 9 6 6 6-6"/></svg>
                </button>
            </div>

            <button type="button" x-show="view === 'days'" x-on:click="move(1)" class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-primary-100 hover:text-primary-600 dark:hover:bg-slate-800" aria-label="ماه بعد">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="m9 18 6-6-6-6"/></svg>
            </button>
            <button type="button" x-show="view !== 'days'" x-cloak x-on:click="view = 'days'" class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-primary-100 hover:text-primary-600 dark:hover:bg-slate-800" aria-label="بازگشت">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>

        {{-- ═════ DAYS VIEW ═══════════════════════════════════════════ --}}
        <div x-show="view === 'days'" class="p-3">
            {{-- weekday header --}}
            <div class="grid grid-cols-7 gap-0.5 text-center">
                <template x-for="w in weekdays" :key="w">
                    <span class="py-1 text-[9px] font-bold text-slate-400" x-text="w"></span>
                </template>
            </div>

            {{-- day grid --}}
            <div class="mt-0.5 grid grid-cols-7 gap-0.5">
                <template x-for="(day, idx) in days" :key="idx">
                    <button type="button" x-show="day !== null" x-on:click="pick(day)"
                            class="grid h-8 place-items-center rounded-lg text-[11px] font-bold transition"
                            :class="(() => { const s = selected;
                                if (iso && s && s.jy === jy && s.jm === jm && s.jd === day) return 'bg-primary-600 text-white shadow-md shadow-primary-600/30';
                                return 'text-slate-600 hover:bg-primary-50 hover:text-primary-600 dark:text-slate-300 dark:hover:bg-slate-800'; })()"
                            x-text="day ? fa(day) : ''" :aria-label="day ? 'انتخاب روز ' + day : ''">
                    </button>
                </template>
            </div>
        </div>

        {{-- ═════ MONTHS VIEW — click the month name to get here ══════ --}}
        <div x-show="view === 'months'" x-cloak class="p-3">
            <p class="mb-2 text-center text-[10px] font-bold text-slate-400">ماه مورد نظر را انتخاب کنید</p>
            <div class="grid grid-cols-3 gap-1.5">
                <template x-for="(name, i) in months" :key="name">
                    <button type="button" x-on:click="pickMonth(i + 1)"
                            class="rounded-xl border px-2 py-2.5 text-[11px] font-extrabold transition"
                            :class="jm === i + 1
                                ? 'border-primary-600 bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                : 'border-slate-200 text-slate-600 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'"
                            x-text="name" :aria-label="'انتخاب ماه ' + name"></button>
                </template>
            </div>
        </div>

        {{-- ═════ YEARS VIEW — click the year to get here ═════════════ --}}
        <div x-show="view === 'years'" x-cloak class="p-3">
            <div class="mb-2 flex items-center justify-between">
                <button type="button" x-on:click="moveYear(-12)" class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-primary-100 hover:text-primary-600 dark:hover:bg-slate-800" aria-label="۱۲ سال قبل">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="m11 17-5-5 5-5"/><path d="m18 17-5-5 5-5"/></svg>
                </button>
                <p class="text-[10px] font-bold text-slate-400">سال مورد نظر را انتخاب کنید</p>
                <button type="button" x-on:click="moveYear(12)" class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-primary-100 hover:text-primary-600 dark:hover:bg-slate-800" aria-label="۱۲ سال بعد">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="m13 17 5-5-5-5"/><path d="m6 17 5-5-5-5"/></svg>
                </button>
            </div>
            <div class="grid grid-cols-4 gap-1.5">
                <template x-for="y in yearWindow" :key="y">
                    <button type="button" x-on:click="pickYear(y)"
                            class="rounded-xl border px-2 py-2.5 text-[11px] font-extrabold transition"
                            :class="jy === y
                                ? 'border-primary-600 bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                : 'border-slate-200 text-slate-600 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'"
                            x-text="fa(y)" :aria-label="'انتخاب سال ' + y"></button>
                </template>
            </div>
        </div>

        {{-- footer actions --}}
        <div class="flex items-center justify-between border-t border-dashed border-slate-200 px-3 py-2 dark:border-slate-700">
            <button type="button" x-on:click="clear()" class="rounded-lg px-2.5 py-1 text-[10px] font-bold text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-950/40">
                {{ $clearLabel }}
            </button>
            <button type="button" x-on:click="today()" class="rounded-lg bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                امروز
            </button>
        </div>
    </div>
</div>
