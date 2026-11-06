{{-- ═══════════════════ PANELS ═══════════════════ --}}
<section id="panels" class="scroll-mt-20 border-y border-slate-200 bg-white py-20 dark:border-slate-800 dark:bg-slate-900/50">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="ip-reveal mx-auto max-w-2xl text-center">
            <span class="text-xs font-black tracking-widest text-primary-600 dark:text-primary-400">{{ $c['eyebrow'] }}</span>
            <h2 class="mt-2 text-2xl font-extrabold sm:text-4xl">{{ $c['title'] }}</h2>
            <p class="mt-4 text-sm leading-7 text-slate-500 dark:text-slate-400">{{ $c['lead'] }}</p>
        </div>

        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @foreach ($c['cards'] as $ci => $card)
                <div class="ip-reveal group relative flex flex-col overflow-hidden rounded-3xl {{ $ci === 1
                    ? 'border-2 border-primary-500/40 bg-gradient-to-b from-primary-50/60 to-white shadow-lg shadow-primary-600/5 transition hover:-translate-y-1 hover:shadow-xl dark:border-primary-500/30 dark:from-primary-950/40 dark:to-slate-950'
                    : 'border border-slate-200 bg-slate-50 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-950' }}">
                    @if (($card['badge'] ?? '') !== '')
                        <span class="absolute left-4 top-4 rounded-full bg-primary-600 px-2.5 py-1 text-[10px] font-black text-white shadow-md">{{ $card['badge'] }}</span>
                    @endif
                    <div class="p-5 pb-0">
                        @if ($ci === 0)
                            {{-- admin browser mockup --}}
                            <div class="overflow-hidden rounded-t-2xl border border-b-0 border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex items-center gap-1.5 border-b border-slate-100 px-3 py-2 dark:border-slate-800" dir="ltr">
                                    <span class="size-2 rounded-full bg-rose-400"></span>
                                    <span class="size-2 rounded-full bg-amber-400"></span>
                                    <span class="size-2 rounded-full bg-emerald-400"></span>
                                </div>
                                <div class="flex gap-2 p-3">
                                    <div class="w-10 space-y-1.5">
                                        @foreach ([1, 2, 3, 4] as $i)
                                            <div class="h-2.5 rounded-md {{ $i === 1 ? 'bg-primary-200 dark:bg-primary-800' : 'bg-slate-100 dark:bg-slate-800' }}"></div>
                                        @endforeach
                                    </div>
                                    <div class="flex-1 space-y-1.5">
                                        <div class="grid grid-cols-3 gap-1.5">
                                            <div class="h-7 rounded-lg bg-slate-100 dark:bg-slate-800"></div>
                                            <div class="h-7 rounded-lg bg-slate-100 dark:bg-slate-800"></div>
                                            <div class="h-7 rounded-lg bg-primary-100 dark:bg-primary-900"></div>
                                        </div>
                                        <div class="h-12 rounded-lg bg-slate-100 dark:bg-slate-800"></div>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- phone mockup --}}
                            <div class="mx-auto w-36 rounded-[1.6rem] border-4 border-slate-900 bg-slate-900 p-1 shadow-xl dark:border-slate-700 dark:bg-slate-800">
                                <div class="overflow-hidden rounded-[1.15rem] bg-white dark:bg-slate-950">
                                    <div class="bg-gradient-to-br {{ $ci === 1 ? 'from-primary-600 to-primary-800' : 'from-primary-500 to-primary-700' }} px-2.5 pb-5 pt-2.5 text-white">
                                        @if ($ci === 1)
                                            <div class="text-[8px] opacity-80">فروش امروز</div>
                                            <div class="text-[13px] font-black">۱٬۸۵۰٬۰۰۰</div>
                                        @else
                                            <div class="text-[8px] opacity-80">فروشگاه شما</div>
                                            <div class="text-[9px] opacity-75" dir="ltr">@your_shop</div>
                                        @endif
                                    </div>
                                    <div class="-mt-2.5 rounded-t-xl bg-white p-2 dark:bg-slate-950">
                                        @if ($ci === 1)
                                            <div class="space-y-1">
                                                <div class="h-5 rounded-md bg-primary-100 dark:bg-primary-950"></div>
                                                <div class="h-5 rounded-md bg-slate-100 dark:bg-slate-900"></div>
                                                <div class="h-5 rounded-md bg-slate-100 dark:bg-slate-900"></div>
                                            </div>
                                        @else
                                            <div class="grid grid-cols-2 gap-1">
                                                @foreach ([1, 2, 3, 4] as $i)
                                                    <div class="h-7 rounded-md bg-slate-100 dark:bg-slate-900"></div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <span class="grid size-11 place-items-center rounded-2xl bg-primary-600/10 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                            <x-icon name="{{ $card['icon'] }}" class="size-5" />
                        </span>
                        <h3 class="mt-3.5 text-lg font-extrabold">{{ $card['title'] }}</h3>
                        <ul class="mt-3 flex-1 space-y-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            @foreach ($card['items'] as $item)
                                <li class="flex items-center gap-2"><x-icon name="check" class="size-4 shrink-0 text-primary-500" /> {{ $item }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ $card['link_href'] }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-extrabold text-primary-600 transition hover:gap-2.5 dark:text-primary-400">
                            {{ $card['link_label'] }} <x-icon name="arrow-left" class="size-4" />
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
