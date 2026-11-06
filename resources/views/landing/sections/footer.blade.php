{{-- ═══════════════════ FOOTER (sticky via mt-auto) ═══════════════════
     MOBILE FIX (v0.18.0): the quick-access menu groups used to stack
     1-per-row on phones; they now live in their own 2-column grid →
     2 groups sit SIDE BY SIDE on mobile, a 3rd wraps BELOW the pair
     (2×2 for four). Desktop keeps the brand + menus split. --}}
@php($footerGroups = array_values(array_filter($c['groups'], fn ($g) => trim((string) ($g['title'] ?? '')) !== '' || ! empty($g['links']))))
<footer class="mt-auto border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
        <div class="grid gap-10 md:grid-cols-[1.4fr_1.6fr] md:gap-14">
            <div>
                <div class="flex items-center gap-2.5">
                    @if (\App\Support\Branding::logoUrl())
                        <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ $ipAppName }}" class="size-9 rounded-xl object-contain">
                    @else
                        <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white">
                            <x-icon name="store" class="size-5" />
                        </span>
                    @endif
                    <span class="text-lg font-extrabold">{{ $ipAppName }}</span>
                </div>
                <p class="mt-4 max-w-sm text-sm leading-7 text-slate-500 dark:text-slate-400">
                    {{ $c['about'] }}
                </p>
            </div>

            @if (count($footerGroups) > 0)
                <div class="{{ count($footerGroups) > 1 ? 'grid grid-cols-2' : 'grid' }} content-start gap-x-6 gap-y-10">
                    @foreach ($footerGroups as $gi => $group)
                        <nav aria-label="{{ $group['title'] ?: 'پیوندها' }}" wire:key="footer-group-{{ $gi }}">
                            <h3 class="text-sm font-extrabold text-slate-700 dark:text-slate-200">{{ $group['title'] }}</h3>
                            @if (! empty($group['links']))
                                <ul class="mt-4 space-y-2.5 text-sm text-slate-500 dark:text-slate-400">
                                    @foreach ($group['links'] as $link)
                                        <li><a href="{{ $link['href'] }}" class="transition hover:text-primary-600">{{ $link['label'] }}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </nav>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="border-t border-slate-100 dark:border-slate-800/70">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-slate-400 sm:flex-row sm:px-6 dark:text-slate-500">
            <span>{{ str_replace(['{year}', '{app}'], [\App\Support\LandingContent::jYear(), $ipAppName], $c['copyright']) }}</span>
            <span class="flex items-center gap-1.5">{{ $c['credit'] }} <x-icon name="heart" class="size-3.5 text-pink-500" /></span>
        </div>
    </div>
</footer>
