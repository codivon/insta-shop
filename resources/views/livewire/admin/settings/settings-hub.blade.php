<div class="ip-fade-up space-y-5">

    {{-- ── page header (like reference design) ── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid size-12 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                <x-icon name="settings" class="size-6" />
            </span>
            <div>
                <h1 class="text-lg font-black leading-6">تنظیمات سیستم</h1>
                <p class="mt-0.5 text-[11px] text-slate-400">مدیریت پیکربندی، ظاهر، پیامک و درگاه پرداخت</p>
            </div>
        </div>
        <span class="inline-flex items-center gap-1.5 self-start rounded-full border border-primary-200 bg-primary-50 px-3 py-1.5 text-[11px] font-bold text-primary-700 sm:self-auto dark:border-primary-900 dark:bg-primary-950/50 dark:text-primary-300">
            <x-icon name="sparkles" class="size-3.5" />
            پنل مدیریت کل — تنظیمات
        </span>
    </div>

    {{-- ── hub: internal sidebar + section ──
         data-hub-grid = shell-CSS hook: the ≥1024px two-column template is
         re-specified without the bundle (arbitrary lg:grid-cols has already
         died on the client's devices) --}}
    <div data-hub-grid class="grid items-start gap-5 lg:grid-cols-[260px_minmax(0,1fr)]">

        {{-- internal section navigation — mobile: 2-col tiles, desktop: vertical list
             data-* hooks = the self-contained shell CSS (partials/admin-shell-css)
             re-specifies this grid without the Tailwind bundle --}}
        <nav id="settings-sections" class="min-w-0 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm lg:sticky lg:top-20 dark:border-slate-800 dark:bg-slate-900"
             aria-label="بخش‌های تنظیمات">
            <p class="px-2 pt-1 pb-2 text-[10px] font-black tracking-wide text-slate-400">بخش‌های تنظیمات</p>
            <div data-hub-tiles class="grid grid-cols-2 gap-2 lg:flex lg:flex-col lg:gap-0" role="tablist">
                @foreach ($sections as $key => $section)
                    <button type="button" wire:click="$set('tab', '{{ $key }}')" role="tab" data-hub-tile
                            data-hub-tile-active="{{ $tab === $key ? 'true' : 'false' }}"
                            :aria-selected="$wire.tab === '{{ $key }}'"
                            @if ($loop->first) wire:key="sec-first" @endif
                            class="group flex w-full flex-col items-center gap-2 rounded-xl px-2 py-3 text-center transition lg:flex-row lg:gap-3 lg:px-3 lg:py-2.5 lg:text-right {{ $tab === $key
                                ? 'bg-primary-600 shadow-md shadow-primary-600/25'
                                : 'hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg transition {{ $tab === $key
                            ? 'bg-white/20 text-white'
                            : 'bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400' }}">
                            <x-icon name="{{ $section['icon'] }}" class="size-4.5" />
                        </span>
                        <span data-hub-tile-body class="w-full min-w-0">
                            <span class="block truncate text-[11px] font-extrabold leading-5 lg:text-xs {{ $tab === $key ? 'text-white' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ $section['label'] }}
                            </span>
                            <span data-hub-tile-desc class="mt-0.5 hidden truncate text-[10px] lg:block {{ $tab === $key ? 'text-white/70' : 'text-slate-400' }}">
                                {{ $section['description'] }}
                            </span>
                        </span>
                        @if ($tab === $key)
                            {{-- data-hub-tile-check = shell-CSS hook: fixed 20px
                                 badge — the old `> span:last-of-type` rule used to
                                 stretch this into a full-width white pill --}}
                            <span data-hub-tile-check class="ms-auto hidden size-5 shrink-0 place-items-center rounded-full bg-white text-primary-700 lg:grid">
                                <x-icon name="check" class="size-3" />
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </nav>

        {{-- active section (embedded child component) — min-w-0 + data hook:
             a wide table inside must NEVER blow this grid item past the
             viewport (min-width:auto would size it to the table's 720px) --}}
        <div wire:key="section-{{ $tab }}" data-hub-section class="min-w-0">
            @if ($tab === 'general')
                @livewire(\App\Livewire\Admin\Settings\GeneralSettings::class, [], key('settings-general'))
            @elseif ($tab === 'landing')
                @livewire(\App\Livewire\Admin\Settings\LandingSettings::class, [], key('settings-landing'))
            @elseif ($tab === 'appearance')
                @livewire(\App\Livewire\Admin\AppearanceSettings::class, [], key('settings-appearance'))
            @elseif ($tab === 'sms')
                @livewire(\App\Livewire\Admin\Settings\SmsSettings::class, [], key('settings-sms'))
            @elseif ($tab === 'payment')
                @livewire(\App\Livewire\Admin\Settings\PaymentSettings::class, [], key('settings-payment'))
            @elseif ($tab === 'shops')
                @livewire(\App\Livewire\Admin\Settings\ShopsSettings::class, [], key('settings-shops'))
            @elseif ($tab === 'stories')
                @livewire(\App\Livewire\Admin\Settings\StoriesSettings::class, [], key('settings-stories'))
            @elseif ($tab === 'notifications')
                @livewire(\App\Livewire\Admin\Settings\NotificationsSettings::class, [], key('settings-notifications'))
            @elseif ($tab === 'backups')
                @livewire(\App\Livewire\Admin\Settings\BackupSettings::class, [], key('settings-backups'))
            @endif
        </div>
    </div>

    {{-- deploy diagnostic: this must show the LATEST release tag after a
         git pull — an older number (or the old 4-section nav above) means
         the host is still serving stale views/caches --}}
    <p class="text-center text-[10px] text-slate-400 dark:text-slate-500">
        نسخهٔ دیپلوی این صفحه: <span dir="ltr" class="font-bold text-slate-500 dark:text-slate-400">v{{ \App\Support\Version::code() }}</span>
    </p>
</div>
