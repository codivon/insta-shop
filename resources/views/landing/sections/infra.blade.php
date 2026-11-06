{{-- ═══════════════════ INFRASTRUCTURE MARQUEE ═══════════════════
     RTL FIX (v0.18.0): the marquee wrapper is now dir="ltr" — in an RTL
     page an overflowing w-max track spills to the LEFT, so only the TAIL
     of the list was visible and translateX(-50%) dragged everything out
     leaving an empty band («فقط یک بار نمایش میده و رد میشه»). With the
     ltr wrapper the track spills right and -50% = exactly one copy → a
     TRUE seamless loop. Each chip is dir="rtl" to keep Persian text
     (mixed chips like «نصب PWA») rendering identically to before.
     The list is rendered TWICE as two equal groups (not flex-gap on the
     track) so half the track is EXACTLY one copy → zero jump at wrap.
     Items + title + speed come from the admin panel (landing.content.infra). --}}
<section class="border-y border-slate-200 bg-white py-7 dark:border-slate-800 dark:bg-slate-900/50" aria-label="زیرساخت‌های متصل">
    <p class="mb-5 text-center text-xs font-bold tracking-wide text-slate-400 dark:text-slate-500">{{ $c['title'] }}</p>
    <div class="ip-marquee relative overflow-hidden [mask-image:linear-gradient(to_left,transparent,black_12%,black_88%,transparent)]" dir="ltr">
        <div class="ip-marquee-track flex w-max items-center" style="animation-duration: {{ max(6, (int) $c['speed']) }}s">
            @foreach ([0, 1] as $copy)
                <div class="flex items-center gap-3 px-1.5" @if ($copy === 1) aria-hidden="true" @endif>
                    @foreach ($c['items'] as $chip)
                        <span dir="rtl" class="flex items-center gap-1.5 whitespace-nowrap rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-[13px] font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                            <x-icon name="circle-check" class="size-3.5 shrink-0 text-primary-500" />
                            {{ $chip }}
                        </span>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>
