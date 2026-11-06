{{-- ═══════════════════ HERO ═══════════════════ --}}
@php($ipAppName = $ipAppName ?? \App\Support\Theme::resolve()['app_name'])
<section class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(100,116,139,0.16)_1px,transparent_0)] [background-size:22px_22px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_35%,black,transparent)] dark:bg-[radial-gradient(circle_at_1px_1px,rgba(148,163,184,0.13)_1px,transparent_0)]"></div>
        <div class="absolute -top-36 right-[12%] size-[30rem] rounded-full bg-primary-400/25 blur-3xl dark:bg-primary-600/20"></div>
        <div class="absolute -bottom-44 left-[8%] size-[26rem] rounded-full bg-pink-400/15 blur-3xl dark:bg-pink-600/10"></div>
    </div>

    <div class="mx-auto grid max-w-6xl items-center gap-14 px-4 pb-16 pt-14 sm:px-6 md:pt-20 lg:grid-cols-2 lg:gap-8 lg:pb-24">
        <div class="ip-fade-up text-center lg:text-right">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-primary-200 bg-primary-50 px-3.5 py-1.5 text-xs font-bold text-primary-700 shadow-sm dark:border-primary-800 dark:bg-primary-950 dark:text-primary-300">
                <x-icon name="sparkles" class="size-3.5" />
                {{ $c['badge'] }}
            </span>

            <h1 class="mt-6 text-4xl font-black leading-[1.25] tracking-tight sm:text-5xl lg:text-[3.3rem]">
                {{ $c['title_pre'] }}
                <span class="relative inline-block bg-gradient-to-l from-primary-500 via-primary-600 to-pink-600 bg-clip-text text-transparent dark:from-primary-400 dark:via-primary-500 dark:to-pink-500">
                    {{ $c['title_highlight'] }}
                </span>
                {{ $c['title_post'] }}
            </h1>

            <p class="mx-auto mt-6 max-w-xl text-base leading-8 text-slate-600 dark:text-slate-300 lg:mx-0 md:text-lg md:leading-9">
                {{ $c['lead'] }}
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                <a href="{{ $c['cta1_href'] }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-primary-600 px-7 py-4 text-sm font-extrabold text-white shadow-xl shadow-primary-600/30 transition hover:-translate-y-0.5 hover:bg-primary-700 active:scale-[0.98]">
                    <x-icon name="rocket" class="size-5" />
                    {{ $c['cta1_label'] }}
                </a>
                <a href="{{ $c['cta2_href'] }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-7 py-4 text-sm font-extrabold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-primary-300 hover:text-primary-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-primary-700 dark:hover:text-primary-300 active:scale-[0.98]">
                    <x-icon name="cart" class="size-5" />
                    {{ $c['cta2_label'] }}
                </a>
            </div>

            <div class="mt-9 flex flex-wrap items-center justify-center gap-x-7 gap-y-3 text-[13px] font-medium text-slate-500 dark:text-slate-400 lg:justify-start">
                @foreach ($c['ticks'] as $tick)
                    <span class="flex items-center gap-1.5"><x-icon name="circle-check" class="size-4 text-primary-600" /> {{ $tick }}</span>
                @endforeach
            </div>
        </div>

        {{-- app mockups --}}
        <div class="relative mx-auto w-fit" aria-hidden="true">
            <div class="absolute -inset-10 -z-10 rounded-[3.5rem] bg-gradient-to-br from-primary-500/20 via-pink-500/10 to-orange-400/10 blur-2xl"></div>

            <div class="flex items-end justify-center gap-4 sm:gap-6">
                {{-- customer app phone --}}
                <div class="hidden w-44 rotate-[5deg] rounded-[2rem] border-4 border-slate-900 bg-slate-900 p-1.5 shadow-2xl transition duration-500 hover:rotate-2 sm:block dark:border-slate-700 dark:bg-slate-800">
                    <div class="overflow-hidden rounded-[1.55rem] bg-white dark:bg-slate-950">
                        <div class="bg-gradient-to-br from-primary-500 to-primary-700 px-3 pb-7 pt-3 text-white">
                            <div class="flex items-center justify-between text-[8px] opacity-90" dir="ltr">
                                <span>۱۲:۳۰</span><span>{{ $ipAppName }}</span>
                            </div>
                            <div class="mt-2.5 text-[10px] font-extrabold">فروشگاه شما</div>
                            <div class="text-[7.5px] opacity-85" dir="ltr">@your_shop</div>
                        </div>
                        <div class="-mt-3.5 rounded-t-2xl bg-white p-2.5 dark:bg-slate-950">
                            <div class="h-5 rounded-lg bg-slate-100 dark:bg-slate-900"></div>
                            <div class="mt-2 grid grid-cols-2 gap-1.5">
                                @foreach ([1, 2, 3, 4] as $i)
                                    <div class="rounded-lg border border-slate-100 p-1 dark:border-slate-800">
                                        <div class="h-9 rounded-md bg-slate-100 dark:bg-slate-900"></div>
                                        <div class="mt-1 h-1 w-3/4 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                                        <div class="mt-0.5 h-1 w-1/2 rounded-full bg-primary-300 dark:bg-primary-800"></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-2 flex items-center justify-around rounded-xl bg-slate-50 py-1.5 dark:bg-slate-900">
                                <x-icon name="home" class="size-3 text-primary-600" />
                                <x-icon name="list" class="size-3 text-slate-300 dark:text-slate-600" />
                                <x-icon name="cart" class="size-3 text-slate-300 dark:text-slate-600" />
                                <x-icon name="user" class="size-3 text-slate-300 dark:text-slate-600" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- seller app phone (main) --}}
                <div class="w-48 rounded-[2.2rem] border-4 border-slate-900 bg-slate-900 p-2 shadow-2xl transition duration-500 hover:-rotate-1 sm:w-52 dark:border-slate-700 dark:bg-slate-800">
                    <div class="overflow-hidden rounded-[1.8rem] bg-white dark:bg-slate-950">
                        <div class="bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 px-3.5 pb-8 pt-3.5 text-white">
                            <div class="flex items-center justify-between text-[8px] opacity-90" dir="ltr">
                                <span>۱۲:۳۰</span><span>اپ فروشنده</span>
                            </div>
                            <div class="mt-3 text-[10px] opacity-80">فروش امروز</div>
                            <div class="text-lg font-black tracking-tight">۲٬۴۵۰٬۰۰۰ <span class="text-[8px] font-normal opacity-80">تومان</span></div>
                            <div class="mt-2 flex items-end gap-1" dir="ltr">
                                @foreach ([35, 55, 40, 70, 52, 88, 64] as $h)
                                    <span class="w-2.5 rounded-t-sm bg-white/40" style="height: {{ $h * 0.32 }}px"></span>
                                @endforeach
                            </div>
                        </div>
                        <div class="-mt-4 space-y-1.5 rounded-t-2xl bg-white p-2.5 dark:bg-slate-950">
                            <div class="flex items-center gap-2 rounded-xl border border-slate-100 p-2 dark:border-slate-800">
                                <span class="size-6 shrink-0 rounded-lg bg-primary-100 dark:bg-primary-950"></span>
                                <span class="h-1.5 flex-1 rounded-full bg-slate-200 dark:bg-slate-800"></span>
                                <span class="h-1.5 w-7 rounded-full bg-primary-300 dark:bg-primary-800"></span>
                            </div>
                            <div class="flex items-center gap-2 rounded-xl border border-slate-100 p-2 dark:border-slate-800">
                                <span class="size-6 shrink-0 rounded-lg bg-slate-100 dark:bg-slate-900"></span>
                                <span class="h-1.5 flex-1 rounded-full bg-slate-200 dark:bg-slate-800"></span>
                                <span class="h-1.5 w-7 rounded-full bg-slate-200 dark:bg-slate-800"></span>
                            </div>
                            <div class="grid grid-cols-4 gap-1.5 pt-1">
                                @foreach (['package', 'wallet', 'chart', 'bell'] as $ic)
                                    <span class="grid place-items-center rounded-xl bg-slate-50 py-2 text-slate-400 dark:bg-slate-900">
                                        <x-icon name="{{ $ic }}" class="size-3.5" />
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- floating glass chips --}}
            <div class="ip-float absolute -top-4 right-0 flex items-center gap-2 rounded-2xl border border-white/70 bg-white/85 px-3 py-2 shadow-xl backdrop-blur sm:-right-6 dark:border-slate-700/70 dark:bg-slate-900/85">
                <span class="grid size-7 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15">
                    <x-icon name="circle-check" class="size-4" />
                </span>
                <div class="text-[11px] font-extrabold leading-4 text-slate-800 dark:text-slate-100">{{ $c['chip1_title'] }}@if (($c['chip1_sub'] ?? '') !== '')<div class="text-[9px] font-medium text-slate-400">{{ $c['chip1_sub'] }}</div>@endif</div>
            </div>
            <div class="ip-float-slow absolute -bottom-4 left-0 flex items-center gap-2 rounded-2xl border border-white/70 bg-white/85 px-3 py-2 shadow-xl backdrop-blur sm:-left-4 dark:border-slate-700/70 dark:bg-slate-900/85" style="animation-delay: 1.2s">
                <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950">
                    <x-icon name="bell" class="size-4" />
                </span>
                <div class="text-[11px] font-extrabold leading-4 text-slate-800 dark:text-slate-100">{{ $c['chip2_title'] }}@if (($c['chip2_sub'] ?? '') !== '')<div class="text-[9px] font-medium text-slate-400">{{ $c['chip2_sub'] }}</div>@endif</div>
            </div>
            <div class="ip-float absolute bottom-16 right-2 hidden items-center gap-2 rounded-2xl border border-white/70 bg-white/85 px-3 py-2 shadow-xl backdrop-blur md:flex dark:border-slate-700/70 dark:bg-slate-900/85" style="animation-delay: 2.1s">
                <span class="grid size-7 place-items-center rounded-xl bg-pink-100 text-pink-600 dark:bg-pink-500/15">
                    <x-icon name="download" class="size-4" />
                </span>
                <div class="text-[11px] font-extrabold text-slate-800 dark:text-slate-100">{{ $c['chip3_title'] }}</div>
            </div>
        </div>
    </div>
</section>
