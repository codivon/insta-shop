{{-- ─────────────────────────────────────────────────────────
  Payment-gateway brand tile (24px-ish logo, inline — no remote
  assets). Rendered inside the checkout gateway cards.
  $gateway: local | zarinpal | zibal | novinopay | payping | sadad |
            behpardakht | parsian | irankish | pasargad |
            asanpardakht | sepehr | sep
───────────────────────────────────────────────────────────── --}}
@php $gateway = $gateway ?? 'local'; @endphp

@if ($gateway === 'zarinpal')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-amber-300 to-yellow-500 text-sm font-black text-white shadow-inner" title="زرین‌پال">Z</span>
@elseif ($gateway === 'zibal')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-700 text-sm font-black text-white shadow-inner" title="زیبال">ژ</span>
@elseif ($gateway === 'novinopay')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 text-sm font-black text-white shadow-inner" title="نوین‌پی">ن</span>
@elseif ($gateway === 'payping')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-rose-400 to-pink-600 text-sm font-black text-white shadow-inner" title="پی‌پینگ">پ</span>
@elseif ($gateway === 'sadad')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-sky-400 to-cyan-600 text-sm font-black text-white shadow-inner" title="سداد (بانک ملی)">س</span>
@elseif ($gateway === 'behpardakht')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-sky-500 to-blue-700 text-sm font-black text-white shadow-inner" title="به‌پرداخت ملت">ب</span>
@elseif ($gateway === 'parsian')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-orange-400 to-red-600 text-sm font-black text-white shadow-inner" title="پارسیان">پ</span>
@elseif ($gateway === 'irankish')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 text-sm font-black text-white shadow-inner" title="ایران‌کیش">ک</span>
@elseif ($gateway === 'pasargad')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-fuchsia-400 to-purple-600 text-sm font-black text-white shadow-inner" title="پاسارگاد">پ</span>
@elseif ($gateway === 'asanpardakht')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-lime-400 to-green-600 text-sm font-black text-white shadow-inner" title="آسان‌پرداخت">آ</span>
@elseif ($gateway === 'sepehr')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-cyan-400 to-teal-700 text-sm font-black text-white shadow-inner" title="سپهر (بانک صادرات)">س</span>
@elseif ($gateway === 'sep')
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-700 text-sm font-black text-white shadow-inner" title="سپ الکترونیک">س</span>
@else
    <span class="grid size-full place-items-center rounded-lg bg-gradient-to-br from-slate-200 to-slate-400 text-slate-600 shadow-inner dark:from-slate-700 dark:to-slate-800 dark:text-slate-300" title="درگاه آزمایشی">
        <x-icon name="credit-card" class="size-5" />
    </span>
@endif
