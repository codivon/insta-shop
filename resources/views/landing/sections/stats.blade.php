{{-- ═══════════════════ STATS BAND ═══════════════════ --}}
<section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:pb-20" aria-label="آمار پلتفرم">
    <dl class="ip-reveal grid grid-cols-2 gap-3 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-4 sm:gap-4 sm:p-7 dark:border-slate-800 dark:bg-slate-900">
        @foreach ($c['items'] as $s)
            <div class="rounded-2xl bg-slate-50 p-4 text-center dark:bg-slate-950/60">
                <dt class="sr-only">{{ $s['l'] }}</dt>
                <dd class="bg-gradient-to-l from-primary-500 to-primary-700 bg-clip-text text-3xl font-black text-transparent">{{ $s['n'] }}</dd>
                <dd class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">{{ $s['l'] }}</dd>
            </div>
        @endforeach
    </dl>
</section>
