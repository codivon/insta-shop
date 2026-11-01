@props([
    'target' => '',          // Livewire property/action the upload belongs to
    'label'  => 'در حال آپلود',
])

{{-- Percentage progress for a Livewire file upload.
     Visibility is scoped by wire:target; the percentage is fed by the
     livewire-upload-progress event (0-100). --}}
<div class="mt-2" wire:loading wire:target="{{ $target }}"
     x-data="{ p: 0 }"
     x-on:livewire-upload-start.window="p = 0"
     x-on:livewire-upload-progress.window="p = $event.detail.progress"
     x-on:livewire-upload-finish.window="p = 100"
     x-on:livewire-upload-error.window="p = 0">
    <div class="flex items-center gap-2">
        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700" role="progressbar" aria-label="{{ $label }}">
            <div class="h-full rounded-full bg-gradient-to-l from-fuchsia-500 via-primary-500 to-orange-400 transition-[width] duration-150"
                 :style="'width:' + p + '%'"></div>
        </div>
        <span class="min-w-10 text-left text-[10px] font-black text-primary-600 dark:text-primary-400" dir="ltr" x-text="p + '%'">0%</span>
    </div>
    <p class="mt-1 flex items-center gap-1.5 text-[10px] font-bold text-slate-400">
        <span class="inline-block size-3 animate-spin rounded-full border-2 border-primary-400 border-t-transparent"></span>
        {{ $label }}...
    </p>
</div>
