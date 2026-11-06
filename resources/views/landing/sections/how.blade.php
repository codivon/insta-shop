{{-- ═══════════════════ HOW IT WORKS ═══════════════════ --}}
@php($faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'])
<section id="how" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-20 sm:px-6">
    <div class="ip-reveal mx-auto max-w-2xl text-center">
        <span class="text-xs font-black tracking-widest text-primary-600 dark:text-primary-400">{{ $c['eyebrow'] }}</span>
        <h2 class="mt-2 text-2xl font-extrabold sm:text-4xl">{{ $c['title'] }}</h2>
        <p class="mt-4 text-sm leading-7 text-slate-500 dark:text-slate-400">{{ $c['lead'] }}</p>
    </div>

    <ol class="relative mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
        <div class="absolute inset-x-16 top-7 hidden border-t-2 border-dashed border-slate-200 lg:block dark:border-slate-700" aria-hidden="true"></div>
        @foreach ($c['steps'] as $si => $step)
            <li class="ip-reveal relative text-center lg:text-right">
                <div class="relative mx-auto grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-xl shadow-primary-600/25 lg:mx-0">
                    <x-icon name="{{ $step['icon'] }}" class="size-6" />
                    <span class="absolute -left-2 -top-2 grid size-6 place-items-center rounded-full border-2 border-white bg-slate-900 text-[10px] font-black text-white dark:border-slate-950">{{ implode('', array_map(fn ($ch) => $faDigits[(int) $ch] ?? $ch, str_split((string) ($si + 1)))) }}</span>
                </div>
                <h3 class="mt-4 font-extrabold">{{ $step['title'] }}</h3>
                <p class="mx-auto mt-1.5 max-w-[16rem] text-sm leading-7 text-slate-500 lg:mx-0 dark:text-slate-400">{{ $step['desc'] }}</p>
            </li>
        @endforeach
    </ol>
</section>
