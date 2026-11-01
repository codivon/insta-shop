{{-- ─────────────────────────────────────────────────────────
  Payment-gateway brand tile (24px-ish logo, inline — no remote
  assets). Rendered inside the checkout gateway cards.
  $gateway: local | zarinpal | zibal | behpardakht | sep
───────────────────────────────────────────────────────────── --}}
@php $gateway = $gateway ?? 'local'; @endphp

@if ($gateway === 'zarinpal')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-amber-300 to-yellow-500 text-sm font-black text-white shadow-inner" title="زرین‌پال">Z</span>
@elseif ($gateway === 'zibal')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-700 text-sm font-black text-white shadow-inner" title="زیبال">ژ</span>
@elseif ($gateway === 'behpardakht')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-sky-500 to-blue-700 text-sm font-black text-white shadow-inner" title="به‌پرداخت ملت">ب</span>
@elseif ($gateway === 'sep')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-700 text-sm font-black text-white shadow-inner" title="درگاه سپ">س</span>
@else
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-slate-200 to-slate-400 text-slate-600 shadow-inner dark:from-slate-700 dark:to-slate-800 dark:text-slate-300" title="درگاه آزمایشی">
        <x-icon name="credit-card" class="size-5" />
    </span>
@endif
