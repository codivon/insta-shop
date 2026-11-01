@props([
    // REACTIVE Alpine expression controlling visibility — pass the wire
    // property directly, e.g. open="$wire.modalOpen". A local x-data copy
    // (open="modalOpen") would never re-sync when the server flips it.
    'open'       => '',
    // max-w-* key: sm | md | lg | xl | 2xl
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
        'sm'    => 'max-w-sm',
        'md'    => 'max-w-md',
        'xl'    => 'max-w-xl',
        '2xl'   => 'max-w-2xl',
        default => 'max-w-lg',
    };
@endphp

<div x-data="{ __ipOpen: false }"
     x-effect="__ipOpen = !!({{ $open }})"
     x-cloak x-show="__ipOpen"
     @if ($closeJs !== '')
         x-on:keydown.escape.window="{{ $closeJs }}"
         x-on:click.self="{{ $closeJs }}"
     @endif
     class="fixed inset-0 {{ $inFrame ? 'lg:absolute' : '' }} {{ $zClass }} overflow-y-auto overscroll-contain bg-slate-950/50 backdrop-blur-sm"
     role="dialog" aria-modal="true" @if ($labelledby !== '') aria-labelledby="{{ $labelledby }}" @endif
     x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    {{-- Centering wrapper: min-h-full (NOT h-full) — when the panel is
         taller than the viewport the wrapper simply grows and the overlay
         scrolls from the top, so the header can never end up unreachable
         (the old flex + m-auto pattern clipped the top inside scroll). --}}
    <div class="flex min-h-full items-center justify-center p-3 sm:p-4">
        <div class="flex max-h-[calc(100vh-1.5rem)] w-full {{ $mw }} flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl supports-[height:100dvh]:max-h-[calc(100dvh-1.5rem)] dark:border-slate-800 dark:bg-slate-900"
             x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100">

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
