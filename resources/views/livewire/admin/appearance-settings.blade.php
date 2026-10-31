<div class="ip-fade-up mx-auto max-w-4xl space-y-6">

    {{-- ── flash ── --}}
    @if (session('appearance-saved'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300">
            <x-icon name="check" class="size-4.5" />
            {{ session('appearance-saved') }}
        </div>
    @endif

    {{-- ── page card ── --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="flex items-center gap-2 text-lg font-extrabold">
                    <span class="grid size-9 place-items-center rounded-xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <x-icon name="palette" class="size-4.5" />
                    </span>
                    ظاهر و رنگ‌بندی
                </h2>
                <p class="mt-2 max-w-xl text-xs leading-6 text-slate-500 dark:text-slate-400">
                    رنگ اصلی می‌تواند یک رنگ مشخص باشد. با یک کلیک روی هر پالت، همه چیز — از منوها تا
                    دکمه‌ها — با کلیک روی دکمه ذخیره، در همه صفحه‌ها همزمان تغییر می‌کند.
                </p>
            </div>
            <span class="hidden rounded-full bg-primary-50 px-3 py-1.5 text-[11px] font-bold text-primary-700 sm:inline dark:bg-primary-950 dark:text-primary-300">
                ذخیره‌سازی: {{ $preset === 'custom' ? 'شخصی‌سازی — رنگ سفارشی' : (\App\Support\Theme::PRESETS[$preset]['label'] ?? $preset) }}
            </span>
        </div>

        {{-- ── presets ── --}}
        <h3 class="mt-6 text-sm font-extrabold">پالت‌های آماده</h3>
        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach (\App\Support\Theme::PRESETS as $slug => $p)
                <button type="button" wire:click="$set('preset', '{{ $slug }}')"
                        class="group relative flex flex-col items-center gap-2 rounded-2xl border p-3 transition
                               {{ $preset === $slug
                                   ? 'border-primary-500 bg-primary-50/60 ring-2 ring-primary-500/30 dark:bg-primary-950/40'
                                   : 'border-slate-200 hover:border-primary-300 dark:border-slate-800 dark:hover:border-primary-800' }}">
                    <span class="h-10 w-full rounded-xl bg-gradient-to-l {{ $p['gradient'] }}"></span>
                    <span class="text-[11px] font-bold leading-4 text-slate-700 dark:text-slate-200">{{ $p['label'] }}</span>
                    <span class="text-[9px] text-slate-400">{{ $p['hint'] }}</span>
                    @if ($preset === $slug)
                        <span class="absolute -left-1.5 -top-1.5 grid size-5 place-items-center rounded-full bg-primary-600 text-white shadow">
                            <x-icon name="check" class="size-3" />
                        </span>
                    @endif
                </button>
            @endforeach

            {{-- custom --}}
            <button type="button" wire:click="$set('preset', 'custom')"
                    class="group relative flex flex-col items-center gap-2 rounded-2xl border p-3 transition
                           {{ $preset === 'custom'
                               ? 'border-primary-500 bg-primary-50/60 ring-2 ring-primary-500/30 dark:bg-primary-950/40'
                               : 'border-slate-200 hover:border-primary-300 dark:border-slate-800 dark:hover:border-primary-800' }}">
                <span class="h-10 w-full rounded-xl" style="background: conic-gradient(from 0deg, #f43f5e, #f59e0b, #10b981, #06b6d4, #6366f1, #d946ef, #f43f5e);"></span>
                <span class="text-[11px] font-bold leading-4 text-slate-700 dark:text-slate-200">شخصی‌سازی</span>
                <span class="text-[9px] text-slate-400">رنگ کاملاً خودت را بساز</span>
                @if ($preset === 'custom')
                    <span class="absolute -left-1.5 -top-1.5 grid size-5 place-items-center rounded-full bg-primary-600 text-white shadow">
                        <x-icon name="check" class="size-3" />
                    </span>
                @endif
            </button>
        </div>

        {{-- ── custom color + preview ── --}}
        <div class="mt-6 rounded-2xl border border-dashed border-slate-300 p-4 dark:border-slate-700">
            <h3 class="text-sm font-extrabold">رنگ اصلی</h3>
            <p class="mt-1 text-[11px] text-slate-400">کد HEX را وارد کنید یا انتخابگر رنگ را باز کنید؛ با تغییر، «شخصی‌سازی» فعال می‌شود.</p>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <input type="color" wire:model.live="primary" wire:debounce.300ms
                       class="size-11 cursor-pointer rounded-xl border border-slate-200 bg-transparent p-1 dark:border-slate-700"
                       aria-label="انتخابگر رنگ اصلی">
                <input type="text" wire:model.live="primary" wire:debounce.300ms dir="ltr"
                       class="w-32 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-center text-sm font-bold shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
                       placeholder="#2563EB" aria-label="کد رنگ HEX">
                @error('primary')
                    <span class="text-xs font-semibold text-rose-600">{{ $message }}</span>
                @enderror
            </div>

            {{-- shades preview (50 → 950) --}}
            <div class="mt-4">
                <div class="mb-2 text-[11px] font-semibold text-slate-500 dark:text-slate-400">طیف تولیدشده (۵۰ تا ۹۵۰) — در تمام اجزای سایت استفاده می‌شود:</div>
                <div class="grid grid-cols-11 gap-1.5">
                    @foreach ($this->shades as $step => $hex)
                        <div class="flex flex-col items-center gap-1">
                            <span class="h-9 w-full rounded-lg border border-black/5 shadow-sm" style="background: {{ $hex }};" title="{{ $step }} → {{ $hex }}"></span>
                            <span class="text-[8.5px] font-bold text-slate-400">{{ $step }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- mini preview UI --}}
            <div class="mt-4 rounded-2xl border border-slate-200 p-3 dark:border-slate-800">
                <div class="mb-2 text-[11px] font-semibold text-slate-500 dark:text-slate-400">پیش‌نمایش زنده:</div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="rounded-xl px-4 py-2 text-xs font-bold text-white shadow-md" style="background: {{ $this->shades[600] ?? '#2563EB' }};">دکمه اصلی</span>
                    <span class="rounded-xl border px-4 py-2 text-xs font-bold" style="border-color: {{ $this->shades[300] ?? '#93C5FD' }}; color: {{ $this->shades[700] ?? '#1D4ED8' }};">دکمه خطی</span>
                    <span class="rounded-full px-3 py-1 text-[10px] font-bold" style="background: {{ $this->shades[100] ?? '#DBEAFE' }}; color: {{ $this->shades[800] ?? '#1E40AF' }};">برچسب</span>
                    <span class="grid size-8 place-items-center rounded-full text-white shadow" style="background: {{ $this->shades[700] ?? '#1D4ED8' }};">
                        <x-icon name="store" class="size-4" />
                    </span>
                </div>
            </div>
        </div>

        {{-- ── default mode ── --}}
        <div class="mt-6">
            <h3 class="text-sm font-extrabold">حالت پیش‌فرض سایت</h3>
            <div class="mt-3 grid grid-cols-2 gap-3">
                @foreach (['light' => ['label' => 'روشن', 'icon' => 'sun'], 'dark' => ['label' => 'تیره', 'icon' => 'moon']] as $mode => $m)
                    <button type="button" wire:click="$set('defaultMode', '{{ $mode }}')"
                            class="flex items-center justify-center gap-2 rounded-2xl border py-3.5 text-sm font-bold transition
                                   {{ $defaultMode === $mode
                                       ? 'border-primary-500 bg-primary-50/60 text-primary-700 ring-2 ring-primary-500/30 dark:bg-primary-950/40 dark:text-primary-300'
                                       : 'border-slate-200 text-slate-500 hover:border-primary-300 dark:border-slate-800 dark:text-slate-400' }}">
                        <x-icon name="{{ $m['icon'] }}" class="size-4.5" />
                        {{ $m['label'] }}
                    </button>
                @endforeach
            </div>
            <p class="mt-2 text-[11px] leading-6 text-slate-400">کاربران می‌توانند با دکمه ماه/خورشید بالای هر صفحه، تم را موقتاً عوض کنند؛ انتخاب آن‌ها در گوشی ذخیره می‌شود.</p>
        </div>

        {{-- ── actions ── --}}
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5 dark:border-slate-800">
            <div class="flex gap-2.5">
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-2xl bg-primary-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 disabled:opacity-60">
                    <x-icon name="check" class="size-4.5" />
                    ذخیره پوسته
                    <span wire:loading.delay class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                </button>
                <button type="button" wire:click="resetTheme" wire:confirm="تم به حالت پیش‌فرض برگردد؟"
                        class="rounded-2xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-600 transition hover:border-rose-300 hover:text-rose-600 dark:border-slate-700 dark:text-slate-300">
                    بازگشت به پیش‌فرض
                </button>
            </div>
            <span class="text-[11px] text-slate-400">بلافاصله پس از ذخیره، همه پنل‌ها (ادمین/فروشنده/مشتری/لندینگ) رنگ جدید را می‌گیرند.</span>
        </div>
    </div>
</div>
