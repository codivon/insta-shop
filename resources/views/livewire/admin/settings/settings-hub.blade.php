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

    {{-- ── hub: internal sidebar + section ── --}}
    <div class="grid items-start gap-5 lg:grid-cols-[260px_minmax(0,1fr)]">

        {{-- internal section navigation --}}
        <nav class="min-w-0 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm lg:sticky lg:top-20 dark:border-slate-800 dark:bg-slate-900"
             aria-label="بخش‌های تنظیمات">
            <p class="px-2 pt-1 pb-2 text-[10px] font-black tracking-wide text-slate-400">بخش‌های تنظیمات</p>
            <div class="scroll-slim flex gap-2 overflow-x-auto pb-1 lg:flex-col lg:overflow-visible lg:pb-0" role="tablist">
                @foreach ($sections as $key => $section)
                    <button type="button" wire:click="$set('tab', '{{ $key }}')" role="tab"
                            :aria-selected="$wire.tab === '{{ $key }}'"
                            @if ($loop->first) wire:key="sec-first" @endif
                            class="group flex w-full min-w-44 shrink-0 items-center gap-3 rounded-xl px-3 py-2.5 text-right transition {{ $tab === $key
                                ? 'bg-primary-600 shadow-md shadow-primary-600/25'
                                : 'hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg transition {{ $tab === $key
                            ? 'bg-white/20 text-white'
                            : 'bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400' }}">
                            <x-icon name="{{ $section['icon'] }}" class="size-4.5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-xs font-extrabold {{ $tab === $key ? 'text-white' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ $section['label'] }}
                            </span>
                            <span class="block truncate text-[10px] {{ $tab === $key ? 'text-white/70' : 'text-slate-400' }}">
                                {{ $section['description'] }}
                            </span>
                        </span>
                        @if ($tab === $key)
                            <span class="ms-auto grid size-5 shrink-0 place-items-center rounded-full bg-white text-primary-700">
                                <x-icon name="check" class="size-3" />
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </nav>

        {{-- active section (embedded child component) --}}
        <div wire:key="section-{{ $tab }}">
            @if ($tab === 'general')
                @livewire(\App\Livewire\Admin\Settings\GeneralSettings::class, [], key('settings-general'))
            @elseif ($tab === 'appearance')
                @livewire(\App\Livewire\Admin\AppearanceSettings::class, [], key('settings-appearance'))
            @elseif ($tab === 'sms')
                @livewire(\App\Livewire\Admin\Settings\SmsSettings::class, [], key('settings-sms'))
            @elseif ($tab === 'payment')
                @livewire(\App\Livewire\Admin\Settings\PaymentSettings::class, [], key('settings-payment'))
            @endif
        </div>
    </div>
</div>
