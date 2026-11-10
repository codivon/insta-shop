<div class="ip-fade-up space-y-4">

    {{-- ── header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-base font-black">پلن‌های اشتراک</h1>
            <p class="mt-0.5 text-xs text-slate-400">پلن‌های قابل فروش به فروشندگان (ماهانه / ۶ماهه / سالانه …) — هر پلن می‌تواند نرخ کمیسیون مخصوص خودش را داشته باشد</p>
        </div>
        <button type="button" wire:click="toggleForm(null)"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
            <x-icon name="{{ $formOpen && $editingId === null ? 'minus' : 'plus' }}" class="size-4" />
            {{ $formOpen && $editingId === null ? 'بستن فرم' : 'پلن جدید' }}
        </button>
    </div>

    {{-- ── form (create + edit) ── --}}
    @if ($formOpen)
        <div class="rounded-2xl border border-primary-200 bg-white p-5 shadow-sm dark:border-primary-900/60 dark:bg-slate-900">
            <h2 class="text-sm font-extrabold">{{ $editingId !== null ? 'ویرایش پلن' : 'پلن جدید' }}</h2>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <div class="space-y-3">
                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">عنوان پلن *</span>
                        <input type="text" wire:model="title" maxlength="80" placeholder="مثلاً: پلن سالانه طلایی"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('title') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">معرفی یک‌خطی (روی کارت فروشنده)</span>
                        <input type="text" wire:model="tagline" maxlength="160" placeholder="مثلاً: به‌صرفه‌ترین انتخاب برای فروشندگان پر‌فروش"
                               class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                        @error('tagline') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">ویژگی‌ها (هر خط یک مورد — حداکثر ۸ خط)</span>
                        <textarea wire:model="features" rows="4" placeholder="پشتیبانی ویژه&#10;نشان ویژه روی فروشگاه&#10;اولویت در صفحهٔ اصلی"
                                  class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs leading-6 shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800"></textarea>
                        @error('features') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">مدت اعتبار (روز) *</span>
                            <input type="number" min="1" max="3650" wire:model="durationDays" placeholder="۳۰ / ۱۸۰ / ۳۶۵"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            @error('durationDays') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">قیمت (تومان) * — ۰ = رایگان</span>
                            <input type="text" inputmode="numeric" wire:model="price" placeholder="مثلاً 690000"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            @error('price') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </label>
                    </div>

                    <div class="rounded-2xl border border-slate-200 p-3.5 dark:border-slate-700">
                        <div class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">کمیسیون مخصوص این پلن</div>
                        <div class="mt-2 grid gap-2">
                            <label class="flex cursor-pointer items-center gap-2 text-[11px] font-bold">
                                <input type="radio" value="" wire:model="commissionType" class="accent-primary-600">
                                <span>طبق نرخ سراسری (پلن فقط مدت می‌فروشد)</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 text-[11px] font-bold">
                                <input type="radio" value="percent" wire:model="commissionType" class="accent-primary-600">
                                <span>درصدی — مثلاً ۵٪ به‌ازای هر سفارش</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 text-[11px] font-bold">
                                <input type="radio" value="fixed" wire:model="commissionType" class="accent-primary-600">
                                <span>مبلغ ثابت — مثلاً ۲۰٬۰۰۰ تومان به‌ازای هر سفارش</span>
                            </label>
                        </div>
                        @if ($commissionType !== '')
                            <label class="mt-2 block">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $commissionType === 'percent' ? 'درصد کمیسیون (۰ تا ۱۰۰)' : 'مبلغ ثابت کمیسیون (تومان)' }}</span>
                                <input type="number" min="0" max="{{ $commissionType === 'percent' ? 100 : 999999999999 }}" wire:model="commissionValue"
                                       class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                                @error('commissionValue') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </label>
                        @endif
                        <p class="mt-2 text-[10px] leading-5 text-slate-400">اولویت نهایی کمیسیون هر فروشنده: نرخ اختصاصی مدیر ← نرخ پلن فعال ← نرخ سراسری.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex cursor-pointer items-center justify-between gap-2 rounded-xl border border-slate-200 px-3 py-2.5 dark:border-slate-700">
                            <span class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">فعال (قابل خرید)</span>
                            <input type="checkbox" wire:model="isActive" class="size-4 accent-primary-600">
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">ترتیب نمایش</span>
                            <input type="number" min="0" max="9999" wire:model="sort"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                            @error('sort') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </label>
                    </div>
                </div>
            </div>

            @error('form') <p class="mt-3 rounded-xl bg-rose-50 px-3 py-2 text-[11px] font-bold text-rose-600 dark:bg-rose-950/30 dark:text-rose-300">{{ $message }}</p> @enderror

            <div class="mt-5 flex items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                <button type="button" wire:click="cancelForm"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    انصراف
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        class="rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-50">
                    ذخیره پلن
                </button>
            </div>
        </div>
    @endif

    {{-- ── plans table ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="hidden grid-cols-12 gap-2 border-b border-slate-100 bg-slate-50 px-4 py-2.5 text-[10px] font-black text-slate-400 md:grid dark:border-slate-800 dark:bg-slate-800/50">
            <span class="col-span-3">پلن</span>
            <span class="col-span-2">مدت / قیمت</span>
            <span class="col-span-3">کمیسیون</span>
            <span class="col-span-2">اشتراک‌ها</span>
            <span class="col-span-2 text-end">وضعیت / عملیات</span>
        </div>

        @forelse ($plans as $plan)
            <div class="grid grid-cols-2 items-center gap-2 border-b border-slate-100 px-4 py-3 last:border-0 md:grid-cols-12 dark:border-slate-800">
                <div class="col-span-2 md:col-span-3">
                    <div class="flex items-center gap-2 text-xs font-extrabold">
                        <span class="grid size-7 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400"><x-icon name="crown" class="size-3.5" /></span>
                        {{ $plan->title }}
                    </div>
                    @if ($plan->tagline)<div class="mt-1 pe-9 text-[10px] text-slate-400">{{ $plan->tagline }}</div>@endif
                </div>

                <div class="md:col-span-2">
                    <span class="block text-[11px] font-bold">{{ $plan->durationLabel() }}</span>
                    <span class="text-[11px] font-black text-amber-600 dark:text-amber-400">{{ \App\Support\Fmt::money($plan->price) }}</span>
                </div>

                <div class="md:col-span-3">
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-300">{{ $plan->commissionLabel() }}</span>
                </div>

                <div class="md:col-span-2">
                    <span class="rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-black text-slate-500 dark:bg-slate-800 dark:text-slate-300">{{ \App\Support\Fmt::toFa($subscriptionCounts[$plan->id] ?? 0) }} اشتراک</span>
                </div>

                <div class="col-span-2 flex items-center justify-end gap-1.5 md:col-span-2">
                    <span class="rounded-full px-2 py-1 text-[9px] font-black {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                        {{ $plan->is_active ? 'فعال' : 'غیرفعال' }}
                    </span>
                    <button type="button" wire:click="toggleForm({{ $plan->id }})" title="ویرایش"
                            class="grid size-8 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-800">
                        <x-icon name="edit" class="size-4" />
                    </button>
                    <button type="button" wire:click="toggleActive({{ $plan->id }})" title="{{ $plan->is_active ? 'غیرفعال‌سازی' : 'فعال‌سازی' }}"
                            class="grid size-8 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 {{ $plan->is_active ? 'hover:text-rose-500' : 'hover:text-emerald-600' }} dark:hover:bg-slate-800">
                        <x-icon name="{{ $plan->is_active ? 'eye-off' : 'circle-check' }}" class="size-4" />
                    </button>
                    <button type="button" wire:click="delete({{ $plan->id }})" title="حذف"
                            wire:confirm="پلن «{{ $plan->title }}» حذف شود؟ فقط پلن‌های بدون اشتراک حذف می‌شوند."
                            class="grid size-8 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40">
                        <x-icon name="trash" class="size-4" />
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-xs text-slate-400">
                هنوز پلنی ساخته نشده — با «پلن جدید» اولین پلن را بسازید.
            </div>
        @endforelse
    </div>

    <p class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[11px] leading-6 text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
        <span class="font-extrabold">مدیریت اشتراک‌ها:</span> برای اعطای دستی، تمدید یا لغو اشتراک هر فروشنده به صفحهٔ «اشتراک فروشندگان» بروید.
        دورهٔ آزمایشی خودکار برای فروشندگان تازه‌تأیید در «تنظیمات ← تنظیمات فروشگاه‌ها» فعال می‌شود.
    </p>
</div>
