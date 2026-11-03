@props([
    // REACTIVE Alpine expression controlling visibility — pass the wire
    // property directly, e.g. open="$wire.modalOpen". A local x-data copy
    // (open="modalOpen") would never re-sync when the server flips it.
    'open'       => '',
    // max-w-* key: sm | md | lg | xl | 2xl | full
    // «full» = near-full-height sheet on phones, centered panel on ≥sm screens
    'maxWidth'   => 'lg',
    // stacking: zClass for the overlay (raise when modals stack on each other)
    'zClass'     => 'z-[70]',
    // id of the heading element for aria-labelledby
    'labelledby' => '',
    // JS to run when the backdrop / Escape is hit.
    // `__ipOpen = false;` hides instantly; pair it with the wire close call,
    // e.g. "__ipOpen = false; $wire.closeEdit()"
    'closeJs'    => '',
    // seller/customer app: cover only the phone frame on desktop
    'inFrame'    => false,
])

@php
    $mw = match ($maxWidth) {
        'sm'    => 'sm:max-w-sm',
        'md'    => 'sm:max-w-md',
        'xl'    => 'sm:max-w-xl',
        '2xl'   => 'sm:max-w-2xl',
        'full'  => 'sm:max-w-lg',
        default => 'sm:max-w-lg',
    };

    // ── Mobile: bottom sheet ──
    // Every modal docks to the bottom edge on phones (app-like, one-thumb
    // reach): full-width, rounded top corners, slides up, drag handle on
    // top. «full» additionally stretches to near viewport height.
    // ── ≥sm: classic centered floating card (unchanged).
    if ($maxWidth === 'full') {
        $sheetSizing = 'h-[calc(100dvh-1.5rem)] max-h-none sm:h-auto sm:max-h-[calc(100dvh-1.5rem)]';
    } else {
        $sheetSizing = 'max-h-[92dvh] sm:max-h-[calc(100vh-1.5rem)] supports-[height:100dvh]:sm:max-h-[calc(100dvh-1.5rem)]';
    }
@endphp

<div {{ $attributes->merge(['x-data' => '{ __ipOpen: false }']) }}
     x-effect="__ipOpen = !!({{ $open }})"
     x-cloak x-show="__ipOpen"
     @if ($closeJs !== '')
         x-on:keydown.escape.window="{{ $closeJs }}"
         x-on:click.self="{{ $closeJs }}"
     @endif
     class="fixed inset-0 {{ $inFrame ? 'lg:absolute' : '' }} {{ $zClass }} overflow-hidden sm:overflow-y-auto sm:overscroll-contain bg-slate-950/50 backdrop-blur-sm"
     role="dialog" aria-modal="true" @if ($labelledby !== '') aria-labelledby="{{ $labelledby }}" @endif
     x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    {{-- Bottom-sheet wrapper on phones (items-end, no padding); the
         classic centering wrapper from sm: up. min-h-full (NOT h-full) —
         when the ≥sm panel is taller than the viewport the wrapper simply
         grows and the overlay scrolls from the top, so the header can
         never end up unreachable. --}}
    <div class="flex min-h-full items-end justify-center sm:items-center sm:p-4">
        <div class="flex w-full {{ $mw }} {{ $sheetSizing }} flex-col overflow-hidden rounded-t-2xl rounded-b-none border border-b-0 border-slate-200 bg-white shadow-2xl sm:rounded-2xl sm:border-b dark:border-slate-800 dark:bg-slate-900"
             x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">

            {{-- drag handle — swipe down to close (phones only) --}}
            <div class="mx-auto mt-2 h-1.5 w-11 shrink-0 cursor-grab rounded-full bg-slate-300 sm:hidden dark:bg-slate-700"
                 role="separator" aria-hidden="true"
                 @if ($closeJs !== '')
                     x-on:touchstart="window.__ipSheetY = $event.touches[0].clientY"
                     x-on:touchmove="if (window.__ipSheetY && $event.touches[0].clientY - window.__ipSheetY > 60) { window.__ipSheetY = null; {{ $closeJs }} }"
                     x-on:touchend="window.__ipSheetY = null"
                 @endif
            ></div>

            @if (isset($header) && $header->isNotEmpty())
                <div class="shrink-0 border-b border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900">
                    {{ $header }}
                </div>
            @endif

            {{-- body — the ONLY scroll container inside the panel --}}
            <div class="scroll-slim min-h-0 flex-1 overflow-y-auto">
                {{ $slot }}
            </div>

            @if (isset($footer) && $footer->isNotEmpty())
                <div class="shrink-0 border-t border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
