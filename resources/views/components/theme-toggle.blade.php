@props(['class' => ''])

<button type="button" data-theme-toggle
        aria-label="تغییر تم روشن/تیره"
        class="inline-flex items-center justify-center rounded-xl transition-colors hover:bg-slate-100 dark:hover:bg-slate-800 {{ $class }}">
    <x-icon name="sun" class="size-5 dark:hidden" />
    <x-icon name="moon" class="hidden size-5 dark:block" />
</button>
