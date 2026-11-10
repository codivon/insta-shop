<div class="ip-fade-up">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/90">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-lg shadow-amber-500/30">
                    <x-icon name="crown" class="size-5" />
                </span>
                <div>
                    <div class="text-sm font-extrabold leading-5">اشتراک و ارتقا</div>
                    <div class="text-[11px] text-slate-400">پلن‌ها، مزایا و کمیسیون ویژه</div>
                </div>
            </div>
            <x-seller-notif-bell class="ms-auto" />
            <x-theme-toggle class="size-10 text-slate-500" />
        </div>
    </div>

    <div class="px-4 pt-4 pb-8">
        {{-- flash toast --}}
        @if (session('subscription-flash'))
            <div class="mb-3 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-bold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <x-icon name="circle-check" class="size-4" />
                {{ session('subscription-flash') }}
            </div>
        @endif

        {{-- ───────── status hero card ───────── --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-bl from-amber-600 via-amber-500 to-amber-400 p-5 text-white shadow-xl shadow-amber-500/25">
            <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true">
                <div class="absolute -top-8 left-6 size-36 rounded-full bg-white/30 blur-2xl"></div>
                <div class="absolute -bottom-12 right-10 size-40 rounded-full bg-white/20 blur-2xl"></div>
            </div>

            <div class="relative">
                @if ($active)
                    <p class="flex items-center gap-1.5 text-[11px] font-bold text-amber-100">
                        <x-icon name="crown" class="size-3.5" />
                        {{ $active->title() }} — {{ $active->sourceLabel() }}
                    </p>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black tracking-tight">{{ \App\Support\Fmt::toFa($active->daysLeft()) }}</span>
                        <span class="text-xs font-bold text-amber-100">روز اعتبار باقی‌مانده</span>
                    </div>
                    <p class="mt-1 text-[10px] text-amber-100/90">تا {{ \App\Support\JDate::date($active->ends_at) }}</p>
                @else
                    <p class="flex items-center gap-1.5 text-[11px] font-bold text-amber-100">
                        <x-icon name="info" class="size-3.5" />
                        اشتراک فعالی ندارید
                    </p>
                    <div class="mt-1.5 text-lg font-black">پلن خود را انتخاب کنید</div>
                    <p class="mt-1 text-[10px] text-amber-100/90">با یک پلن فعال، مزایا و نرخ ویژه بلافاصله اعمال می‌شود.</p>
                @endif

                {{-- the always-honest resolved rate --}}
                <div class="mt-4 rounded-2xl bg-white/15 px-3 py-2.5 backdrop-blur">
                    <div class="flex items-center justify-between text-[11px] font-bold">
                        <span class="text-amber-100">نرخ کمیسیون فعلی شما</span>
                        <span>{{ $commissionLabel }}</span>
                    </div>
                    @if (auth('seller')->user()?->hasCommissionOverride())
                        <p class="mt-1 text-[9px] text-amber-100/80">این نرخ به‌صورت اختصاصی برای شما توسط مدیر تعیین شده — خرید پلن آن را تغییر نمی‌دهد.</p>
                    @elseif ($active && $active->commissionOverride() === null && $active->plan)
                        <p class="mt-1 text-[9px] text-amber-100/80">پلن فعلی نرخ ویژه‌ای تعیین نکرده؛ نرخ سراسری ملاک است.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ───────── wallet balance strip ───────── --}}
        <div class="mt-3 flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <span class="flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400">
                    <x-icon name="wallet" class="size-4.5" />
                </span>
                <span>
                    <span class="block text-xs font-extrabold">موجودی کیف پول</span>
                    <span class="block text-[10px] text-slate-400">پلن‌ها با اعتبار فروش شما فعال می‌شوند</span>
                </span>
            </span>
            <span class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ \App\Support\Fmt::money($walletBalance) }}</span>
        </div>

        {{-- ───────── plans grid ───────── --}}
        <h2 class="mt-5 mb-2 flex items-center gap-2 text-xs font-extrabold text-slate-500 dark:text-slate-400">
            <x-icon name="sparkles" class="size-4 text-amber-500" />
            پلن‌های اشتراک
        </h2>

        @forelse ($plans as $plan)
            @php
                $isCurrentActive = $active && $active->isActive() && $active->plan_id === $plan->id;
                $affordable = $walletBalance >= (int) $plan->price;
            @endphp
            <div class="mt-2 rounded-2xl border p-4 shadow-sm transition {{ $isCurrentActive ? 'border-amber-300 bg-amber-50/60 dark:border-amber-800 dark:bg-amber-950/20' : 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2 text-sm font-extrabold">
                            {{ $plan->title }}
                            @if ($isCurrentActive)
                                <span class="rounded-full bg-amber-500 px-2 py-0.5 text-[9px] font-black text-white">پلن فعلی شما</span>
                            @endif
                        </div>
                        @if ($plan->tagline)
                            <div class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">{{ $plan->tagline }}</div>
                        @endif
                    </div>
                    <span class="shrink-0 rounded-xl bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $plan->durationLabel() }}</span>
                </div>

                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-lg font-black text-amber-600 dark:text-amber-400">{{ \App\Support\Fmt::fa($plan->price) }}</span>
                    <span class="text-[10px] font-bold text-slate-400">تومان</span>
                </div>

                <ul class="mt-3 space-y-1.5">
                    <li class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                        <x-icon name="circle-check" class="size-3.5 shrink-0 text-emerald-500" />
                        {{ $plan->commissionLabel() }}
                    </li>
                    @foreach ($plan->features ?? [] as $feature)
                        <li class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                            <x-icon name="circle-check" class="size-3.5 shrink-0 text-emerald-500" />
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                <button type="button" wire:click="openBuy({{ $plan->id }})"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl py-3 text-xs font-extrabold transition active:scale-[.98] {{ $affordable ? 'bg-gradient-to-l from-amber-500 to-amber-600 text-white shadow-lg shadow-amber-500/25 hover:from-amber-600 hover:to-amber-700' : 'bg-slate-200 text-slate-400 dark:bg-slate-800' }}">
                    <x-icon name="crown" class="size-4" />
                    @if ($isCurrentActive)
                        تمدید این پلن
                    @elseif ($affordable)
                        خرید از کیف پول — {{ \App\Support\Fmt::money($plan->price) }}
                    @else
                        موجودی کافی نیست
                    @endif
                </button>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-xs text-slate-400 dark:border-slate-700 dark:bg-slate-900">
                هنوز پلنی تعریف نشده است. برای فعال‌سازی پلن‌ها با پشتیبانی تماس بگیرید.
            </div>
        @endforelse

        {{-- ───────── history ───────── --}}
        @if ($history->isNotEmpty())
            <h2 class="mt-6 mb-2 flex items-center gap-2 text-xs font-extrabold text-slate-500 dark:text-slate-400">
                <x-icon name="clock" class="size-4" />
                سابقهٔ اشتراک‌ها
            </h2>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @foreach ($history as $row)
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-3.5 py-2.5 last:border-0 dark:border-slate-800">
                        <span class="flex items-center gap-2">
                            <x-icon name="{{ $row->isActive() ? 'crown' : 'clock' }}" class="size-4 {{ $row->isActive() ? 'text-amber-500' : 'text-slate-400' }}" />
                            <span>
                                <span class="block text-[11px] font-extrabold">{{ $row->title() }}</span>
                                <span class="block text-[9px] text-slate-400">{{ \App\Support\JDate::date($row->starts_at) }} تا {{ \App\Support\JDate::date($row->ends_at) }} · {{ $row->sourceLabel() }}</span>
                            </span>
                        </span>
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-black {{ $row->isActive() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $row->isActive() ? 'فعال' : 'منقضی' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ───────── purchase confirm modal ───────── --}}
    @if ($buyOpen && $buyPlan)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/60 backdrop-blur-sm sm:items-center" x-data x-on:click.prevent="false" wire:key="buy-modal-{{ $buyPlan->id }}">
            <div class="w-full max-w-[430px] rounded-t-3xl bg-white p-5 shadow-2xl dark:bg-slate-900 sm:rounded-3xl">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold">تأیید خرید اشتراک</h3>
                    <button type="button" wire:click="closeBuy" class="grid size-8 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs dark:border-slate-800 dark:bg-slate-800/50">
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">پلن</span>
                        <span class="font-extrabold">{{ $buyPlan->title }} — {{ $buyPlan->durationLabel() }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">مبلغ</span>
                        <span class="font-extrabold text-amber-600 dark:text-amber-400">{{ \App\Support\Fmt::money($buyPlan->price) }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">موجودی فعلی</span>
                        <span class="font-bold">{{ \App\Support\Fmt::money($walletBalance) }}</span>
                    </div>
                    <div class="mt-1 flex justify-between border-t border-dashed border-slate-200 pt-2 dark:border-slate-700">
                        <span class="text-slate-400">پس از خرید</span>
                        <span class="font-bold">{{ \App\Support\Fmt::money(max(0, $walletBalance - (int) $buyPlan->price)) }}</span>
                    </div>
                    <div class="mt-2 flex justify-between py-1">
                        <span class="text-slate-400">کمیسیون این پلن</span>
                        <span class="font-bold">{{ $buyPlan->commissionLabel() }}</span>
                    </div>
                </div>

                @php $isRenewal = $active && $active->isActive() && $active->plan_id === $buyPlan->id; @endphp

                <p class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-[10px] leading-5 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300">
                    @if ($isRenewal)
                        تمدید: {{ \App\Support\Fmt::toFa($buyPlan->duration_days) }} روز به انتهای اشتراک فعلی شما اضافه می‌شود.
                    @elseif ($active && $active->isActive())
                        جایگزینی: اشتراک فعلی «{{ $active->title() }}» بلافاصله بسته و پلن جدید فعال می‌شود.
                    @else
                        پلن بلافاصله فعال می‌شود و مزایا از همین حالت اعمال می‌شوند.
                    @endif
                </p>

                @if ($errors->has('buy'))
                    <p class="mt-2 rounded-xl bg-rose-50 px-3 py-2 text-[10px] font-bold text-rose-600 dark:bg-rose-950/30 dark:text-rose-300">{{ $errors->first('buy') }}</p>
                @endif

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button type="button" wire:click="closeBuy" class="rounded-2xl border border-slate-200 py-3 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                        انصراف
                    </button>
                    <button type="button" wire:click="confirmBuy" {{ $walletBalance < (int) $buyPlan->price ? 'disabled' : '' }}
                            class="rounded-2xl py-3 text-xs font-extrabold transition active:scale-[.98] {{ $walletBalance >= (int) $buyPlan->price ? 'bg-gradient-to-l from-amber-500 to-amber-600 text-white shadow-lg shadow-amber-500/25' : 'bg-slate-200 text-slate-400 dark:bg-slate-800' }}">
                        پرداخت و فعال‌سازی
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
