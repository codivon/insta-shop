{{-- ═══════════════════════════════════════════════════════════
  Global panel toast — listens to the `panel-toast` window event
  that every Livewire component dispatches when a save fails
  validation or an action succeeds. Renders the FIRST error
  message so the seller/admin/customer always knows WHICH part
  of the form is the problem (task: پیغام خطا در همه پنل‌ها).
═════════════════════════════════════════════════════════════ --}}
<div x-data="{ show: false, msg: '', kind: 'error', timer: null }"
     @panel-toast.window="
        msg = $event.detail.message ?? '';
        kind = $event.detail.type ?? 'error';
        show = msg !== '';
        clearTimeout(timer);
        timer = setTimeout(() => show = false, $event.detail.type === 'success' ? 2800 : 4200);
     "
     x-show="show"
     x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     x-cloak
     class="fixed bottom-24 left-1/2 z-[95] w-[min(20rem,calc(100vw-2rem))] -translate-x-1/2"
     role="status" aria-live="polite">
    <div class="flex items-start gap-2.5 rounded-2xl border px-4 py-3 shadow-2xl backdrop-blur"
         :class="kind === 'success'
            ? 'border-emerald-300/60 bg-emerald-600/95 text-white dark:border-emerald-700'
            : 'border-rose-300/60 bg-rose-600/95 text-white dark:border-rose-700'">
        <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-white/20">
            <x-icon name="alert" class="size-3" x-show="kind !== 'success'" />
            <x-icon name="check" class="size-3" x-show="kind === 'success'" x-cloak />
        </span>
        <p class="min-w-0 flex-1 text-[11px] font-bold leading-5" x-text="msg"></p>
        <button type="button" @click="show = false" class="shrink-0 rounded-md p-0.5 text-white/70 transition hover:bg-white/15 hover:text-white"
                aria-label="بستن پیام">
            <x-icon name="x" class="size-3.5" />
        </button>
    </div>
</div>
