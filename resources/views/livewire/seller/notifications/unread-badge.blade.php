{{-- Live unread badge on the seller tab bar. --}}
{{-- client v0.17.15: the 60s wire:poll is GONE («رفرش یا reload خودکار را
     حذف کن» — poll commits cancelled in-flight user actions and threw
     «AbortError: signal is aborted without reason» in the console). The
     badge re-renders on every other interaction of its host pages. --}}
{{-- ONE always-rendered root element: when count = 0 the old @if-only
     view rendered an EMPTY string and Livewire crashed every seller
     panel page with «missing root tag». Toggling `hidden` (which
     Tailwind sorts after `grid`, so it always wins) keeps a stable
     root for mount + morph. --}}
<span class="absolute -top-1 end-2 grid min-w-[16px] place-items-center rounded-full bg-rose-500 px-1 text-[9px] font-black leading-4 text-white ring-2 ring-white dark:ring-slate-900 {{ $this->count > 0 ? '' : 'hidden' }}"
      wire:key="seller-unread-badge">
    {{ $this->count > 0 ? \App\Support\Fmt::fa($this->count) : '' }}
</span>
