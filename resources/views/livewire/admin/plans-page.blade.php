<div class="ip-fade-up space-y-5">

    {{-- ── header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="grid size-12 place-items-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-600/30">
                <x-icon name="crown" class="size-6" />
            </span>
            <div>
                <h1 class="text-lg font-black leading-6">پلن‌های اشتراک</h1>
                <p class="mt-0.5 text-[11px] text-slate-400">محصولاتی که فروشندگان می‌خرند — مدت اعتبار، نرخ کمیسیون مخصوص و اولویت نمایش هر پلن</p>
            </div>
        </div>
        <button type="button" wire:click="openCreate"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-l from-amber-500 to-amber-600 px-5 py-3 text-xs font-extrabold text-white shadow-lg shadow-amber-500/25 transition hover:from-amber-600 hover:to-amber-700 active:scale-95">
            <x-icon name="plus" class="size-4" />
            پلن جدید
        </button>
    </div>

    {{-- ── stats ── --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                <span class="grid size-8 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-950/50 dark:text-primary-400"><x-icon name="layers" class="size-4" /></span>
                پلن‌های فعال
            </div>
            <div class="mt-2 text-xl font-black">{{ \App\Support\Fmt::toFa($plans->where('is_active', true)->count()) }} <span class="text-xs font-bold text-slate-400">از {{ \App\Support\Fmt::toFa($plans->count()) }}</span></div>
            <div class="text-[10px] text-slate-400">پلن غیرفعال در لیست خرید فروشندگان دیده نمی‌شود</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                <span class="grid size-8 place-items-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-950/50 dark:text-emerald-400"><x-icon name="store" class="size-4" /></span>
                اشتراک‌های فعال
            </div>
            <div class="mt-2 text-xl font-black">{{ \App\Support\Fmt::toFa($activeSubs) }}</div>
            <div class="text-[10px] text-slate-400">مجموع فروشندگانِ دارای اعتبار</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                <span class="grid size-8 place-items-center rounded-xl bg-amber-50 text-amber-500 dark:bg-amber-950/50 dark:text-amber-400"><x-icon name="coins" class="size-4" /></span>
                درآمد اشتراک (کل)
            </div>
            <div class="mt-2 text-xl font-black text-amber-600 dark:text-amber-400">{{ \App\Support\Fmt::money($subsRevenue) }}</div>
            <div class="text-[10px] text-slate-400">تومان — همهٔ دوره‌ها از ابتدا</div>
        </div>
    </div>

    {{-- ── plans card grid ── --}}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($plans as $plan)
            <div class="relative flex flex-col overflow-hidden rounded-3xl border bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900 {{ $plan->is_active ? 'border-slate-200' : 'border-dashed border-slate-300 opacity-80 dark:border-slate-700' }}"
                 wire:key="plan-{{ $plan->id }}">
                {{-- top accent --}}
                <div class="h-1.5 w-full bg-gradient-to-l {{ $plan->boost_level >= 2 ? 'from-amber-400 via-orange-400 to-rose-400' : ($plan->boost_level == 1 ? 'from-primary-400 to-sky-400' : 'from-slate-300 to-slate-200 dark:from-slate-700 dark:to-slate-600') }}"></div>

                <div class="flex flex-1 flex-col p-5">
                    {{-- head --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-md shadow-amber-500/25">
                                <x-icon name="crown" class="size-4.5" />
                            </span>
                            <div>
                                <div class="text-sm font-black">{{ $plan->title }}</div>
                                <div class="mt-0.5 text-[10px] font-bold text-slate-400">{{ $plan->durationLabel() }}</div>
                            </div>
                        </div>
                        <span class="rounded-full px-2 py-1 text-[9px] font-black {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $plan->is_active ? 'فعال' : 'غیرفعال' }}
                        </span>
                    </div>

                    @if ($plan->tagline)
                        <p class="mt-3 rounded-xl bg-slate-50 px-3 py-2 text-[10.5px] font-semibold leading-5 text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">{{ $plan->tagline }}</p>
                    @endif

                    {{-- price + chips --}}
                    <div class="mt-3 flex items-end justify-between gap-2">
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ \App\Support\Fmt::fa($plan->price) }}</span>
                            <span class="text-[10px] font-bold text-slate-400">تومان</span>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-1">
                            @if ($plan->isOneTime())
                                <span class="rounded-lg bg-fuchsia-50 px-2 py-1 text-[9px] font-black text-fuchsia-700 dark:bg-fuchsia-950/40 dark:text-fuchsia-300" title="هر فروشنده فقط یک بار می‌تواند این پلن را بخرد">
                                    یک‌بارمصرف
                                </span>
                            @endif
                            <span class="rounded-lg bg-amber-50 px-2 py-1 text-[9px] font-black text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                {{ $plan->commission_type === 'percent' ? '٪'.\App\Support\Fmt::toFa($plan->commission_value) : ($plan->commission_type === 'fixed' ? \App\Support\Fmt::moneyShort($plan->commission_value).' ت' : 'نرخ سراسری') }}
                            </span>
                            @if ($plan->boost_level > 0)
                                <span class="rounded-lg bg-primary-50 px-2 py-1 text-[9px] font-black text-primary-600 dark:bg-primary-950/40 dark:text-primary-300" title="{{ $plan->boostLabel() }}">
                                    {{ $plan->boost_level >= 2 ? 'اولویت ویژه' : 'اولویت عادی' }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- features --}}
                    @if (filled($plan->features))
                        <ul class="mt-3.5 space-y-1.5">
                            @foreach ($plan->features as $feature)
                                <li class="flex items-start gap-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                                    <x-icon name="circle-check" class="mt-px size-3.5 shrink-0 text-emerald-500" />
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    {{-- footer: count + actions --}}
                    <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-3.5 dark:border-slate-800">
                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                            {{ \App\Support\Fmt::toFa($subscriptionCounts[$plan->id] ?? 0) }} اشتراک فروخته شده
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
                                    wire:confirm="پلن «{{ $plan->title }}» حذف شود؟ فقط پلن‌های بدون اشتراک حذف می‌شوند."
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
                    <x-icon name="crown" class="size-7" />
                </span>
                <p class="mt-3 text-sm font-bold text-slate-500">هنوز پلنی ساخته نشده</p>
                <p class="mt-1 text-xs text-slate-400">با «پلن جدید» اولین پلن اشتراک را بسازید تا فروشندگان بتوانند تهیه کنند.</p>
            </div>
        @endforelse
    </div>

    <p class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[11px] leading-6 text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
        <span class="font-extrabold">خوب است بدانید:</span>
        کمیسیون پلن هنگام خرید، در «کمیسیون اختصاصی» همان فروشنده (صفحهٔ فروشندگان ← ویرایش) ثبت می‌شود؛
        اولویت نمایش، فروشگاه و محصولاتِ فروشندهٔ دارای پلن اولویت‌دار را در صفحهٔ اول جلوتر می‌برد (میان خریدارانِ زیاد، چرخش روزانه برقرار است)؛
        و «اولویت نمایش» با نردبانِ محصولات تفاوت دارد — نردبان از صفحهٔ «نردبان» خود فروشنده فروخته می‌شود.
        برای اعطای دستی اشتراک به «اشتراک فروشندگان» بروید.
    </p>

    {{-- ═══════════════════ CREATE / EDIT MODAL ═══════════════════ --}}
    <x-modal-panel open="$wire.formOpen" max-width="lg" labelledby="plan-form-title" z-class="z-[75]"
                   close-js="__ipOpen = false; $wire.cancelForm()">
        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 id="plan-form-title" class="flex items-center gap-2 text-sm font-extrabold">
                    <span class="grid size-8 place-items-center rounded-xl bg-amber-600/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                        <x-icon name="crown" class="size-4" />
                    </span>
                    {{ $editingId !== null ? 'ویرایش پلن «'.($formPlan?->title ?? '').'»' : 'پلن اشتراک جدید' }}
                </h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.cancelForm()"
                        class="grid size-8 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="p-5">
            <div class="grid gap-4 lg:grid-cols-2">
                {{-- identity --}}
                <div class="space-y-3">
                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">عنوان پلن *</span>
                        <input type="text" wire:model="title" maxlength="80" placeholder="مثلاً: پلن سالانه طلایی"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-amber-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('title') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">معرفی یک‌خطی (روی کارت فروشنده)</span>
                        <input type="text" wire:model="tagline" maxlength="160" placeholder="مثلاً: به‌صرفه‌ترین انتخاب برای فروشندگان پر‌فروش"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-amber-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('tagline') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">ویژگی‌ها (هر خط یک مورد — حداکثر ۸ خط)</span>
                        <textarea wire:model="features" rows="4" placeholder="پشتیبانی ویژه&#10;نشان ویژه روی فروشگاه&#10;اولویت در صفحهٔ اصلی"
                                  class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs leading-6 shadow-sm focus:border-amber-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800"></textarea>
                        @error('features') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>
                </div>

                {{-- pricing + commission + boost --}}
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">مدت اعتبار (روز) *</span>
                            <input type="number" min="1" max="3650" wire:model="durationDays" placeholder="۳۰ / ۱۸۰ / ۳۶۵"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-amber-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            @error('durationDays') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">قیمت (تومان) * — ۰ = رایگان</span>
                            <input type="text" inputmode="numeric" wire:model="price" placeholder="مثلاً 690000"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-amber-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            @error('price') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </label>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-3.5 dark:border-slate-700 dark:bg-slate-800/40">
                        <div class="flex items-center gap-2 text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                            <x-icon name="coins" class="size-3.5 text-amber-500" />
                            کمیسیون مخصوص این پلن
                        </div>
                        <div class="mt-2 grid gap-2">
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold transition has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50/60 dark:border-slate-700 dark:bg-slate-800 dark:has-[:checked]:border-amber-700 dark:has-[:checked]:bg-amber-950/30">
                                <input type="radio" value="" wire:model="commissionType" class="accent-amber-600">
                                <span>طبق نرخ سراسری (پلن فقط مدت می‌فروشد)</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold transition has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50/60 dark:border-slate-700 dark:bg-slate-800 dark:has-[:checked]:border-amber-700 dark:has-[:checked]:bg-amber-950/30">
                                <input type="radio" value="percent" wire:model="commissionType" class="accent-amber-600">
                                <span>درصدی — مثلاً ۵٪ به‌ازای هر سفارش</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold transition has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50/60 dark:border-slate-700 dark:bg-slate-800 dark:has-[:checked]:border-amber-700 dark:has-[:checked]:bg-amber-950/30">
                                <input type="radio" value="fixed" wire:model="commissionType" class="accent-amber-600">
                                <span>مبلغ ثابت — مثلاً ۲۰٬۰۰۰ تومان به‌ازای هر سفارش</span>
                            </label>
                        </div>
                        @if ($commissionType !== '')
                            <label class="mt-2 block">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $commissionType === 'percent' ? 'درصد کمیسیون (۰ تا ۱۰۰)' : 'مبلغ ثابت کمیسیون (تومان)' }}</span>
                                <input type="number" min="0" max="{{ $commissionType === 'percent' ? 100 : 999999999999 }}" wire:model="commissionValue"
                                       class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs shadow-sm focus:border-amber-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                                @error('commissionValue') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </label>
                        @endif
                        <p class="mt-2 text-[10px] leading-5 text-slate-400">اولویت نهایی: نرخ اختصاصی مدیر ← نرخ پلن فعال ← نرخ سراسری. هنگام خرید پلن، نرخِ آن در «کمیسیون اختصاصی» فروشنده ثبت می‌شود.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                <x-icon name="sparkles" class="size-3.5 text-primary-500" />
                                اولویت نمایش در صفحهٔ اول
                            </span>
                            <select wire:model="boostLevel"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-amber-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                                @foreach ($boostOptions as $level => $label)
                                    <option value="{{ $level }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('boostLevel') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">ترتیب نمایش</span>
                            <input type="number" min="0" max="9999" wire:model="sort"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-amber-400 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            @error('sort') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </label>
                    </div>

                    <label class="flex cursor-pointer items-start justify-between gap-2 rounded-xl border border-fuchsia-200 bg-fuchsia-50/60 px-3 py-2.5 dark:border-fuchsia-800 dark:bg-fuchsia-950/20">
                        <span>
                            <span class="block text-[11px] font-extrabold text-slate-600 dark:text-slate-300">خرید یک‌بار مصرف (بدون تمدید)</span>
                            <span class="mt-0.5 block text-[9.5px] leading-4 text-slate-400">هر فروشنده فقط یک بار می‌تواند این پلن را بخرد — بعد از آن دیگر قابل خرید نیست (دکمهٔ خریدش قفل می‌شود).</span>
                        </span>
                        <input type="checkbox" wire:model="oneTime" class="mt-0.5 size-4 shrink-0 accent-fuchsia-600">
                    </label>

                    <label class="flex cursor-pointer items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800/40">
                        <span class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">فعال (قابل خرید فروشندگان)</span>
                        <input type="checkbox" wire:model="isActive" class="size-4 accent-amber-600">
                    </label>
                </div>
            </div>

            @error('form') <p class="mt-3 rounded-xl bg-rose-50 px-3 py-2 text-[11px] font-bold text-rose-600 dark:bg-rose-950/30 dark:text-rose-300">{{ $message }}</p> @enderror

            <div class="mt-5 flex items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                <button type="button" x-on:click="__ipOpen = false; $wire.cancelForm()"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    انصراف
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        class="rounded-xl bg-gradient-to-l from-amber-500 to-amber-600 px-6 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-amber-500/25 transition hover:from-amber-600 hover:to-amber-700 active:scale-95 disabled:opacity-50">
                    {{ $editingId !== null ? 'ذخیرهٔ تغییرات' : 'ساخت پلن' }}
                </button>
            </div>
        </div>
    </x-modal-panel>
</div>
