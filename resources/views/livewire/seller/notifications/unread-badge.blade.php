{{-- Live unread badge on the seller tab bar — polls quietly. --}}
@if ($this->count > 0)
    <span class="absolute -top-1 end-2 grid min-w-[16px] place-items-center rounded-full bg-rose-500 px-1 text-[9px] font-black leading-4 text-white ring-2 ring-white dark:ring-slate-900"
          wire:key="seller-unread-badge">
        {{ \App\Support\Fmt::fa($this->count) }}
    </span>
@endif
