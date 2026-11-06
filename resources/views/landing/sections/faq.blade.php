{{-- ═══════════════════ FAQ ═══════════════════ --}}
<section id="faq" class="scroll-mt-20 border-t border-slate-200 bg-white py-20 dark:border-slate-800 dark:bg-slate-900/50">
    <div class="mx-auto max-w-3xl px-4 sm:px-6">
        <div class="ip-reveal text-center">
            <span class="text-xs font-black tracking-widest text-primary-600 dark:text-primary-400">{{ $c['eyebrow'] }}</span>
            <h2 class="mt-2 text-2xl font-extrabold sm:text-3xl">{{ $c['title'] }}</h2>
        </div>

        <div class="mt-10 space-y-3">
            @foreach ($c['items'] as $item)
                <details class="ip-reveal group rounded-2xl border border-slate-200 bg-slate-50 transition open:border-primary-200 open:bg-white open:shadow-lg dark:border-slate-800 dark:bg-slate-950 dark:open:border-primary-800">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 text-sm font-extrabold text-slate-700 [&::-webkit-details-marker]:hidden dark:text-slate-200">
                        {{ $item['q'] }}
                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-white text-slate-400 shadow-sm transition group-open:rotate-180 group-open:text-primary-600 dark:bg-slate-900">
                            <x-icon name="chevron-down" class="size-4" />
                        </span>
                    </summary>
                    <p class="border-t border-slate-100 px-5 py-4 text-sm leading-8 text-slate-500 dark:border-slate-800 dark:text-slate-400">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
