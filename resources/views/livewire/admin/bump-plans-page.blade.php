<div class="ip-fade-up space-y-5">

    {{-- ── header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="grid size-12 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 text-white shadow-lg shadow-orange-600/30">
                <x-icon name="rocket" class="size-6" />
            </span>
            <div>
                <h1 class="text-lg font-black leading-6">طرح‌های نردبان</h1>
                <p class="mt-0.5 text-[11px] text-slate-400">بسته‌های «مدت + قیمت» که فروشنده از پنل خودش یکی را انتخاب و از کیف پول پرداخت می‌کند — مثل دیوار</p>
            </div>
        </div>
        <button type="button" wire:click="openCreate"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-l from-orange-500 to-amber-600 px-5 py-3 text-xs font-extrabold text-white shadow-lg shadow-orange-500/25 transition hover:from-orange-600 hover:to-amber-700 active:scale-95">
            <x-icon name="plus" class="size-4" />
            طرح جدید
        </button>
    </div>

    {{-- ── master switch hint ── --}}
    <div class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border px-4 py-3 {{ $bumpEnabled
        ? 'border-emerald-200 bg-emerald-50/70 dark:border-emerald-900 dark:bg-emerald-950/20'
        : 'border-amber-200 bg-amber-50/70 dark:border-amber-900 dark:bg-amber-950/20' }}">
        <span class="flex items-center gap-2 text-[11px] font-bold {{ $bumpEnabled ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
            <x-icon name="{{ $bumpEnabled ? 'circle-check' : 'info' }}" class="size-4" />
            @if ($bumpEnabled)
                نردبان «روشن» است — بخش نردبان در پنل فروشنده نمایش داده می‌شود.
            @else
                نردبان «خاموش» است — فروشندگان این بخش را نمی‌بینند تا وقتی از «تنظیمات فروشگاه‌ها» روشنش کنید.
            @endif
        </span>
        <a href="{{ route('admin.settings', ['tab' => 'shops']) }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3 py-2 text-[10px] font-extrabold text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:text-primary-600 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700">
            <x-icon name="settings" class="size-3.5" />
            کلید روشن/خاموش در تنظیمات
        </a>
    </div>

    {{-- ── stats ── --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                <span class="grid size-8 place-items-center rounded-xl bg-orange-50 text-orange-500 dark:bg-orange-950/50 dark:text-orange-400"><x-icon name="layers" class="size-4" /></span>
                طرح‌های فعال
            </div>
            <div class="mt-2 text-xl font-black">{{ \App\Support\Fmt::toFa($plans->where('is_active', true)->count()) }} <span class="text-xs font-bold text-slate-400">از {{ \App\Support\Fmt::toFa($plans->count()) }}</span></div>
            <div class="text-[10px] text-slate-400">طرح غیرفعال در لیست خرید فروشندگان دیده نمی‌شود</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                <span class="grid size-8 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-950/50 dark:text-primary-400"><x-icon name="rocket" class="size-4" /></span>
                نردبان‌های فعال
            </div>
            <div class="mt-2 text-xl font-black">{{ \App\Support\Fmt::toFa($activeBumps) }}</div>
            <div class="text-[10px] text-slate-400">محصول‌هایی که همین حالا در صدر صفحهٔ اول‌اند</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                <span class="grid size-8 place-items-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-950/50 dark:text-emerald-400"><x-icon name="coins" class="size-4" /></span>
                درآمد نردبان (کل)
            </div>
            <div class="mt-2 text-xl font-black text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::money($bumpRevenue) }}</div>
            <div class="text-[10px] text-slate-400">تومان — همهٔ دوره‌ها از ابتدا</div>
        </div>
    </div>

    {{-- ── plans card grid ── --}}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($plans as $plan)
            <div class="relative flex flex-col overflow-hidden rounded-3xl border bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900 {{ $plan->is_active ? 'border-slate-200' : 'border-dashed border-slate-300 opacity-80 dark:border-slate-700' }}"
                 wire:key="bump-plan-{{ $plan->id }}">
                {{-- top accent --}}
                <div class="h-1.5 w-full bg-gradient-to-l from-orange-400 via-amber-400 to-rose-300"></div>

                <div class="flex flex-1 flex-col p-5">
                    {{-- head --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 text-white shadow-md shadow-orange-500/25">
                                <x-icon name="rocket" class="size-4.5" />
                            </span>
                            <div>
                                <div class="text-sm font-black">{{ $plan->title }}</div>
                                <div class="mt-0.5 text-[10px] font-bold text-slate-400">{{ $plan->daysLabel() }} در صدر صفحهٔ اول</div>
                            </div>
                        </div>
                        <span class="rounded-full px-2 py-1 text-[9px] font-black {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $plan->is_active ? 'فعال' : 'غیرفعال' }}
                        </span>
                    </div>

                    {{-- price hero --}}
                    <div class="mt-3 flex items-end justify-between gap-2">
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-black text-orange-600 dark:text-orange-400">{{ \App\Support\Fmt::fa($plan->price) }}</span>
                            <span class="text-[10px] font-bold text-slate-400">تومان</span>
                        </div>
                        @if ($plan->days > 0 && $plan->price > 0)
                            <span class="rounded-lg bg-orange-50 px-2 py-1 text-[9px] font-black text-orange-700 dark:bg-orange-950/40 dark:text-orange-300" title="نرخ موثر هر روز">
                                ≈ {{ \App\Support\Fmt::moneyShort((int) round($plan->price / $plan->days)) }} / روز
                            </span>
                        @endif
                    </div>

                    <ul class="mt-3.5 space-y-1.5">
                        <li class="flex items-start gap-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                            <x-icon name="circle-check" class="mt-px size-3.5 shrink-0 text-emerald-500" />
                            محصول {{ $plan->daysLabel() }} در نوار «نردبان‌ها» صدر صفحهٔ اول
                        </li>
                        <li class="flex items-start gap-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                            <x-icon name="circle-check" class="mt-px size-3.5 shrink-0 text-emerald-500" />
                            پرداخت از کیف پول فروشنده — تمدید انباشتی از انتهای نردبان فعال
                        </li>
                    </ul>

                    {{-- footer: count + actions --}}
                    <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-3.5 dark:border-slate-800">
                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                            {{ \App\Support\Fmt::toFa($soldCounts[$plan->id] ?? 0) }} بار فروخته شده
                        </span>
                        <div class="flex items-center gap-1">
                            <button type="button" wire:click="toggleForm({{ $plan->id }})" title="ویرایش"
                                    class="grid size-9 place-items-center rounded-xl border border-slate-200 text-slate-400 transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600 dark:border-slate-700 dark:hover:border-primary-800 dark:hover:bg-primary-950/30">
                                <x-icon name="edit" class="size-4" />
                            </button>
                            <button type="button" wire:click="toggleActive({{ $plan->id }})" title="{{ $plan->is_active ? 'غیرفعال‌سازی' : 'فعال‌سازی' }}"
                                    class="grid size-9 place-items-center rounded-xl border border-slate-200 text-slate-400 transition {{ $plan->is_active ? 'hover:border-rose-300 hover:bg-rose-50 hover:text-rose-500 dark:hover:border-rose-900 dark:hover:bg-rose-950/30' : 'hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600 dark:hover:border-emerald-900 dark:hover:bg-emerald-950/30' }}">
                                <x-icon name="{{ $plan->is_active ? 'eye-off' : 'circle-check' }}" class="size-4" />
                            </button>
                            <button type="button" wire:click="delete({{ $plan->id }})" title="حذف"
                                    wire:confirm="طرح «{{ $plan->title }}» حذف شود؟ فقط طرح‌های بدون فروش حذف می‌شوند."
                                    class="grid size-9 place-items-center rounded-xl border border-slate-200 text-slate-400 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-500 dark:border-slate-700 dark:hover:border-rose-900 dark:hover:bg-rose-950/30">
                                <x-icon name="trash" class="size-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <x-icon name="rocket" class="size-7" />
                </span>
                <p class="mt-3 text-sm font-bold text-slate-500">هنوز طرحی ساخته نشده</p>
                <p class="mt-1 text-xs text-slate-400">با «طرح جدید» اولین طرح نردبان را بسازید — مثلاً «نردبان ۱۰ روزه» با قیمت دلخواه.</p>
            </div>
        @endforelse
    </div>

    <p class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[11px] leading-6 text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
        <span class="font-extrabold">خوب است بدانید:</span>
        کلید اصلی روشن/خاموشِ نردبان در «تنظیمات ← تنظیمات فروشگاه‌ها» است (خاموش = بخش نردبان کلاً در پنل فروشنده پنهان می‌شود)؛
        هزینهٔ هر خرید نردبان از کیف پول فروشنده کسر می‌شود و در «گردش حساب» او با عنوان «خرید نردبان» ثبت می‌شود؛
        محصولِ دارای نردبان فعال، اگر دوباره همان طرح را بخرد، روزهای جدید به انتهای نردبان فعلی اضافه می‌شود.
    </p>

    {{-- ═══════════════════ CREATE / EDIT MODAL ═══════════════════ --}}
    <x-modal-panel open="$wire.formOpen" max-width="sm" labelledby="bump-plan-form-title" z-class="z-[75]"
                   close-js="__ipOpen = false; $wire.cancelForm()">
        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 id="bump-plan-form-title" class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-orange-600/10 text-orange-600 dark:bg-orange-500/15 dark:text-orange-400">
                        <x-icon name="rocket" class="size-4" />
                    </span>
                    {{ $editingId !== null ? 'ویرایش طرح «'.($formPlan?->title ?? '').'»' : 'طرح نردبان جدید' }}
                </h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.cancelForm()"
                        class="grid size-8 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="p-5">
            <div class="space-y-3">
                <label class="block">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">عنوان طرح *</span>
                    <input type="text" wire:model="title" maxlength="80" placeholder="مثلاً: نردبان ۱۰ روزه"
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-orange-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    @error('title') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">مدت نردبان (روز) *</span>
                        <input type="number" min="1" max="365" wire:model="days" placeholder="مثلاً 10"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-orange-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('days') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">قیمت (تومان) *</span>
                        <input type="text" inputmode="numeric" wire:model="price" placeholder="مثلاً 150000"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-orange-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('price') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>
                </div>

                @if ((int) $days > 0 && (int) $price > 0)
                    <p class="rounded-xl bg-orange-50 px-3 py-2 text-[10px] font-bold text-orange-700 dark:bg-orange-950/30 dark:text-orange-300">
                        پیش‌نمایش: با این طرح، نردبانِ {{ \App\Support\Fmt::toFa((int) $days) }} روزه {{ \App\Support\Fmt::money((int) $price) }} تومان از کیف پول فروشنده کسر می‌شود.
                    </p>
                @endif

                <div class="grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">ترتیب نمایش</span>
                        <input type="number" min="0" max="9999" wire:model="sort"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-orange-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('sort') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>
                    <label class="flex cursor-pointer items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800/40">
                        <span class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">فعال (قابل خرید)</span>
                        <input type="checkbox" wire:model="isActive" class="size-4 accent-orange-600">
                    </label>
                </div>

                @error('form') <p class="rounded-xl bg-rose-50 px-3 py-2 text-[11px] font-bold text-rose-600 dark:bg-rose-950/30 dark:text-rose-300">{{ $message }}</p> @enderror
            </div>

            <div class="mt-5 flex items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                <button type="button" x-on:click="__ipOpen = false; $wire.cancelForm()"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    انصراف
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        class="rounded-xl bg-gradient-to-l from-orange-500 to-amber-600 px-6 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-orange-500/25 transition hover:from-orange-600 hover:to-amber-700 active:scale-95 disabled:opacity-50">
                    {{ $editingId !== null ? 'ذخیرهٔ تغییرات' : 'ساخت طرح' }}
                </button>
            </div>
        </div>
    </x-modal-panel>
</div>
