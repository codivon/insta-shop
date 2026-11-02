<div class="ip-fade-up">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('seller.profile') }}" wire:navigate
                   class="grid size-10 shrink-0 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بازگشت">
                    <x-icon name="arrow-right" class="size-5" />
                </a>
                <div>
                    <div class="text-sm font-extrabold leading-5">راهنمای استفاده</div>
                    <div class="text-[11px] text-slate-400">هر بخش پنل، چه‌کار می‌کند؟</div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <x-seller-notif-bell />
            </div>
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">
        {{-- ── intro ── --}}
        <div class="rounded-2xl border border-primary-200 bg-gradient-to-l from-primary-600/10 to-primary-500/5 p-4 dark:border-primary-900 dark:from-primary-600/20 dark:to-primary-500/10">
            <h2 class="flex items-center gap-2 text-sm font-extrabold">
                <span class="grid size-8 place-items-center rounded-xl bg-primary-600 text-white shadow-sm"><x-icon name="file-text" class="size-4" /></span>
                با پنل فروشنده در چند دقیقه کار کنيد
            </h2>
            <p class="mt-2 text-[11px] leading-6 text-slate-600 dark:text-slate-300">
                برای هر بخش از پنل، یک اسکرین‌شات واقعی، توضیح کاربرد و اثری که روی فروشگاه عمومی شما می‌گذارد آماده کرده‌ایم. به‌ترتیب بخوانید یا بخش موردنظر را انتخاب کنید.
            </p>
        </div>

        {{-- ── quick jump chips ── --}}
        <div class="scroll-slim mt-3 flex gap-1.5 overflow-x-auto pb-1" role="navigation" aria-label="پرش به بخش">
            @foreach ($sections as $i => $section)
                <a href="#guide-{{ $section['file'] }}"
                   class="flex shrink-0 items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[10px] font-bold text-slate-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    <x-icon name="{{ $section['icon'] }}" class="size-3" />
                    {{ $section['title'] }}
                </a>
            @endforeach
        </div>

        {{-- ── sections ── --}}
        <div class="mt-4 space-y-4">
            @foreach ($sections as $i => $section)
                <article id="guide-{{ $section['file'] }}"
                         class="scroll-mt-20 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    {{-- screenshot --}}
                    <img src="{{ asset('images/guide/'.$section['file'].'.png') }}"
                         alt="اسکرین‌شات بخش {{ $section['title'] }} در پنل فروشنده"
                         loading="lazy" decoding="async" width="390" height="500"
                         class="w-full border-b border-slate-200 bg-slate-100 object-cover object-top dark:border-slate-800 dark:bg-slate-800">

                    <div class="p-4">
                        <h3 class="flex items-center gap-2 text-sm font-extrabold">
                            <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-300">
                                <x-icon name="{{ $section['icon'] }}" class="size-4" />
                            </span>
                            {{ $section['title'] }}
                        </h3>

                        <p class="mt-2.5 text-[11px] leading-6 text-slate-600 dark:text-slate-300">
                            <b class="text-slate-700 dark:text-slate-200">چه‌کار می‌کند؟</b>
                            {{ $section['what'] }}
                        </p>

                        <p class="mt-2.5 flex items-start gap-2 rounded-xl bg-emerald-50/80 p-2.5 text-[11px] leading-6 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
                            <x-icon name="sparkles" class="mt-1 size-3.5 shrink-0" />
                            <span><b>در فروشگاه شما چه تغییر می‌کند؟</b> {{ $section['effect'] }}</span>
                        </p>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- ── closing hint ── --}}
        <div class="mt-5 flex items-start gap-2.5 rounded-2xl border border-primary-200 bg-primary-50/70 p-3.5 text-[11px] leading-6 text-primary-800 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
            <x-icon name="headset" class="mt-0.5 size-4 shrink-0" />
            <span>
                اگر سؤال دیگری دارید، از بخش «پشتیبانی» در پروفایل استفاده کنید یا با پشتیبانی InstaShop در تماس باشید.
            </span>
        </div>
    </div>
</div>
