{{-- ═══════════════════ FINAL CTA ═══════════════════ --}}
<section class="px-4 py-20 sm:px-6">
    <div class="ip-reveal relative mx-auto max-w-4xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 p-10 text-center text-white shadow-2xl shadow-primary-700/30 md:p-16">
        <div class="pointer-events-none absolute inset-0 opacity-25" aria-hidden="true">
            <div class="absolute -top-16 right-10 size-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-20 left-10 size-72 rounded-full bg-white/20 blur-3xl"></div>
        </div>
        <span class="relative inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-bold backdrop-blur">
            <x-icon name="sparkles" class="size-3.5" />
            {{ $c['badge'] }}
        </span>
        <h2 class="relative mt-5 text-2xl font-black leading-relaxed sm:text-4xl sm:leading-relaxed">{{ $c['title'] }}</h2>
        <p class="relative mx-auto mt-4 max-w-md text-sm leading-7 text-primary-100">{{ $c['lead'] }}</p>
        <div class="relative mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ $c['btn1_href'] }}"
               class="inline-flex items-center gap-2 rounded-2xl bg-white px-8 py-4 text-sm font-black text-primary-700 shadow-xl transition hover:-translate-y-0.5 hover:bg-primary-50 active:scale-[0.98]">
                <x-icon name="rocket" class="size-5" />
                {{ $c['btn1_label'] }}
            </a>
            <a href="{{ $c['btn2_href'] }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-white/30 bg-white/10 px-8 py-4 text-sm font-black text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/20 active:scale-[0.98]">
                <x-icon name="cart" class="size-5" />
                {{ $c['btn2_label'] }}
            </a>
        </div>
    </div>
</section>
