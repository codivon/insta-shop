<div class="ip-fade-up">

    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                    <x-icon name="zap" class="size-5" />
                </span>
                <div>
                    <div class="text-sm font-extrabold leading-5">بازدید استوری‌ها</div>
                    <div class="text-[11px] text-slate-400">آمار بازدید، لایک و نظر استوری‌های فروشگاه</div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <x-seller-notif-bell />
                <a href="{{ route('seller.reports') }}" wire:navigate
                   class="grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بازگشت به گزارش‌ها">
                    <x-icon name="arrow-right" class="size-5" />
                </a>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">

        {{-- ───────── summary tiles ───────── --}}
        <div class="grid grid-cols-4 gap-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <x-icon name="eye" class="mx-auto size-4 text-primary-500" />
                <p class="mt-1.5 text-sm font-black">{{ \App\Support\Fmt::toFa($totals['views']) }}</p>
                <p class="text-[9.5px] font-bold text-slate-400">بازدید</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <x-icon name="heart" class="mx-auto size-4 text-rose-500" />
                <p class="mt-1.5 text-sm font-black">{{ \App\Support\Fmt::toFa($totals['likes']) }}</p>
                <p class="text-[9.5px] font-bold text-slate-400">لایک</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <x-icon name="message" class="mx-auto size-4 text-sky-500" />
                <p class="mt-1.5 text-sm font-black">{{ \App\Support\Fmt::toFa($totals['comments']) }}</p>
                <p class="text-[9.5px] font-bold text-slate-400">نظر</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <x-icon name="zap" class="mx-auto size-4 text-emerald-500" />
                <p class="mt-1.5 text-sm font-black">{{ \App\Support\Fmt::toFa($totals['active']) }}</p>
                <p class="text-[9.5px] font-bold text-slate-400">فعال</p>
            </div>
        </div>

        {{-- ───────── views-per-story chart ───────── --}}
        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900" data-testid="story-views-chart-card">
            <div class="flex items-center justify-between gap-2">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950 dark:text-primary-400"><x-icon name="chart" class="size-3.5" /></span>
                    بازدید هر استوری
                </h3>
                <span class="text-[10px] font-semibold text-slate-400">{{ \App\Support\Fmt::toFa($uniqueViewers) }} بازدیدکنندهٔ یکتا</span>
            </div>

            @if ($chart->isEmpty())
                <div class="mt-4 rounded-xl border border-dashed border-slate-200 p-6 text-center dark:border-slate-700">
                    <p class="text-[11px] font-bold text-slate-400">هنوز استوری‌ای ندارید — از «دسترسی سریع» اولین استوری را منتشر کنید.</p>
                </div>
            @else
                <div class="mt-4 flex h-36 items-end gap-1.5" role="img" aria-label="نمودار بازدید هر استوری">
                    @foreach ($chart as $bar)
                        <div class="group flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1" title="{{ $bar['label'] }} — {{ \App\Support\Fmt::toFa($bar['value']) }} بازدید">
                            <span class="text-[8.5px] font-black text-slate-400">{{ $bar['value'] > 0 ? \App\Support\Fmt::toFa($bar['value']) : '' }}</span>
                            <div class="w-full rounded-t-md bg-gradient-to-t from-primary-600 to-primary-400 transition-all group-hover:from-primary-700 group-hover:to-primary-500"
                                 style="height: {{ max(4, (int) round($bar['value'] / $chartMax * 100)) }}%"></div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 text-center text-[9px] font-semibold text-slate-400">۱۴ استوری اخیر — روی هر ستون بایستید</p>
            @endif
        </div>

        {{-- ───────── story list ───────── --}}
        <div class="mt-4 space-y-2">
            <h3 class="text-xs font-extrabold">همهٔ استوری‌ها</h3>

            @forelse ($stories as $story)
                <div wire:key="sv-{{ $story->id }}"
                     class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="relative h-16 w-12 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                        @if ($story->type === \App\Models\Story::TYPE_VIDEO)
                            <span class="grid size-full place-items-center bg-gradient-to-br from-slate-700 to-slate-900">
                                <x-icon name="play" class="size-4 text-white/85" />
                            </span>
                        @elseif ($story->media_path)
                            <img src="{{ $story->mediaUrl() }}" alt="استوری" loading="lazy" class="h-full w-full object-cover">
                        @else
                            <span class="grid size-full place-items-center"><x-icon name="zap" class="size-4 text-slate-300 dark:text-slate-600" /></span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2 py-0.5 text-[9px] font-black {{ $story->stateClass() }}">{{ $story->stateLabel() }}</span>
                            <span class="text-[9px] font-semibold text-slate-400">{{ \App\Support\JDate::date($story->created_at) }}</span>
                        </div>
                        <p class="mt-1 truncate text-[11px] font-bold text-slate-600 dark:text-slate-300">
                            {{ $story->caption ?: ($story->link_title ?: ($story->product?->name ?: 'بدون توضیح')) }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-2.5 text-[10px] font-bold text-slate-400">
                        <span class="flex items-center gap-1" title="بازدید"><x-icon name="eye" class="size-3.5" />{{ \App\Support\Fmt::fa($story->views) }}</span>
                        <span class="flex items-center gap-1" title="لایک"><x-icon name="heart" class="size-3.5" />{{ \App\Support\Fmt::fa($story->likes_count) }}</span>
                        <span class="flex items-center gap-1" title="نظر"><x-icon name="message" class="size-3.5" />{{ \App\Support\Fmt::fa($story->comments_count) }}</span>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                    <p class="text-[11px] font-bold text-slate-400">استوری‌ای برای نمایش نیست.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
