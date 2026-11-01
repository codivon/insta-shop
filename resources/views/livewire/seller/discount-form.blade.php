<div class="ip-fade-up"
     x-data="{ tab: 'info' }"
     x-on:discount-form-errors.window="tab = $event.detail.tab || 'info'; document.getElementById('df-error-summary')?.scrollIntoView({ behavior: 'smooth', block: 'start' })">

    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('seller.discounts') }}" wire:navigate
               class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
               aria-label="بازگشت به کدهای تخفیف">
                <x-icon name="arrow-right" class="size-5" />
            </a>
            <div>
                <div class="text-sm font-extrabold leading-5">{{ $discountId ? 'ویرایش کد تخفیف' : 'ایجاد تخفیف جدید' }}</div>
                <div class="text-[11px] text-slate-400">کد تخفیف فروشگاه شما</div>
            </div>
            <x-theme-toggle class="mr-auto size-10 text-slate-500" />
        </div>
    </div>

    <form wire:submit="save" class="px-4 pt-4">
        {{-- error summary anchor --}}
        @if ($errors->isNotEmpty())
            <div id="df-error-summary" class="mb-3 flex items-start gap-2 rounded-2xl border border-rose-300 bg-rose-50 px-4 py-3 dark:border-rose-900 dark:bg-rose-950/40">
                <x-icon name="alert" class="mt-0.5 size-4 shrink-0 text-rose-500" />
                <div class="min-w-0">
                    <p class="text-[11px] font-extrabold text-rose-600 dark:text-rose-400">ذخیره انجام نشد — {{ \App\Support\Fmt::toFa($errors->count()) }} مورد نیاز به اصلاح دارد</p>
                    <p class="mt-0.5 text-[10px] font-bold leading-5 text-rose-500 dark:text-rose-400/90">{{ $errors->first() }}</p>
                </div>
            </div>
        @endif

        {{-- tabs --}}
        <div class="flex gap-1 rounded-2xl bg-slate-100 p-1 dark:bg-slate-800" role="tablist" aria-label="بخش‌های فرم تخفیف">
            <button type="button" @click="tab = 'info'" :aria-selected="tab === 'info'" role="tab"
                    class="flex-1 rounded-xl py-2 text-[11px] font-extrabold transition"
                    :class="tab === 'info'
                        ? 'bg-white text-primary-700 shadow-sm dark:bg-slate-900 dark:text-primary-300'
                        : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                اطلاعات
            </button>
            <button type="button" @click="tab = 'limits'" :aria-selected="tab === 'limits'" role="tab"
                    class="flex-1 rounded-xl py-2 text-[11px] font-extrabold transition"
                    :class="tab === 'limits'
                        ? 'bg-white text-primary-700 shadow-sm dark:bg-slate-900 dark:text-primary-300'
                        : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                محدودیت‌ها
            </button>
        </div>

        {{-- ═══════════════ TAB 1 · اطلاعات ═══════════════ --}}
        <div x-show="tab === 'info'" x-cloak class="mt-3 space-y-3" role="tabpanel">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-gradient-to-br from-fuchsia-500 to-rose-600 text-white"><x-icon name="sparkles" class="size-3.5" /></span>
                    مشخصات تخفیف
                </h3>

                <div class="mt-3 space-y-3">
                    <div>
                        <label for="df-title" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">عنوان <span class="text-rose-500">*</span></label>
                        <input id="df-title" type="text" wire:model.blur="title" maxlength="120" placeholder="مثال: حراج نوروزی"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800">
                        @error('title') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="df-code" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">کد تخفیف <span class="text-rose-500">*</span></label>
                        <div class="flex gap-2">
                            <input id="df-code" type="text" wire:model.blur="code" dir="ltr" maxlength="60" placeholder="SALE2025"
                                   class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left font-mono text-xs font-black outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                            <button type="button" wire:click="generateCode"
                                    class="grid size-12 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                                    title="ساخت خودکار کد تخفیف" aria-label="ساخت خودکار کد تخفیف">
                                <x-icon name="sparkles" class="size-4" />
                            </button>
                        </div>
                        @error('code') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        <p class="mt-1.5 text-[10px] leading-5 text-slate-400">کد فقط حروف انگلیسی، عدد، خط تیره و زیرخط — با دکمهٔ بالا به‌صورت خودکار ساخته می‌شود.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">تاریخ شروع</span>
                            <x-jdate-picker model="start_date" placeholder="از تاریخ…" />
                            @error('start_date') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <span class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">تاریخ پایان</span>
                            <x-jdate-picker model="end_date" placeholder="تا تاریخ…" />
                            @error('end_date') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="-mt-1 text-[10px] leading-5 text-slate-400">هر دو تاریخ اختیاری است — خالی یعنی بدون محدودیت زمانی.</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400"><x-icon name="tag" class="size-3.5" /></span>
                    مقدار تخفیف
                </h3>

                <div class="mt-3 grid grid-cols-2 gap-2" role="group" aria-label="نوع تخفیف">
                    <button type="button" wire:click="$set('type', 'amount')"
                            class="rounded-2xl border-2 p-3 text-right transition {{ $type === 'amount'
                                ? 'border-primary-500 bg-primary-50/60 dark:border-primary-600 dark:bg-primary-950/40'
                                : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900' }}">
                        <div class="flex items-center gap-2">
                            <span class="grid size-8 place-items-center rounded-xl {{ $type === 'amount' ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                <x-icon name="coins" class="size-4" />
                            </span>
                            <span class="text-[11px] font-extrabold">مبلغ</span>
                        </div>
                        <p class="mt-1.5 text-[9px] leading-4 text-slate-400">تخفیف ثابت به تومان</p>
                    </button>
                    <button type="button" wire:click="$set('type', 'percent')"
                            class="rounded-2xl border-2 p-3 text-right transition {{ $type === 'percent'
                                ? 'border-primary-500 bg-primary-50/60 dark:border-primary-600 dark:bg-primary-950/40'
                                : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900' }}">
                        <div class="flex items-center gap-2">
                            <span class="grid size-8 place-items-center rounded-xl {{ $type === 'percent' ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                <x-icon name="trending-up" class="size-4" />
                            </span>
                            <span class="text-[11px] font-extrabold">درصد</span>
                        </div>
                        <p class="mt-1.5 text-[9px] leading-4 text-slate-400">درصدی از مبلغ سفارش</p>
                    </button>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-3">
                    @if ($type === 'amount')
                        <div class="col-span-2">
                            <label for="df-price" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">مقدار تخفیف (تومان) <span class="text-rose-500">*</span></label>
                            <input id="df-price" type="text" inputmode="numeric" dir="ltr" wire:model="price" placeholder="100000"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                            @if (is_numeric($price) && (int) $price > 0)
                                <p class="mt-1 text-[9px] font-bold text-primary-500">{{ \App\Support\Fmt::money((int) $price) }} تومان</p>
                            @endif
                            @error('price') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div>
                            <label for="df-percent" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">درصد تخفیف <span class="text-rose-500">*</span></label>
                            <input id="df-percent" type="text" inputmode="numeric" dir="ltr" wire:model="percent" placeholder="20"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                            @error('percent') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="df-ceiling" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">سقف تخفیف (تومان)</label>
                            <input id="df-ceiling" type="text" inputmode="numeric" dir="ltr" wire:model="discount_ceiling" placeholder="بدون سقف"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                            @error('discount_ceiling') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="mt-3">
                    <label for="df-desc" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">توضیحات</label>
                    <textarea id="df-desc" wire:model.blur="description" rows="3" maxlength="1000" placeholder="توضیح دلخواه برای این تخفیف…"
                              class="scroll-slim w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold leading-6 outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800"></textarea>
                </div>
            </div>
        </div>

        {{-- ═══════════════ TAB 2 · محدودیت‌ها ═══════════════ --}}
        <div x-show="tab === 'limits'" x-cloak class="mt-3 space-y-3" role="tabpanel">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400"><x-icon name="filter" class="size-3.5" /></span>
                    حداقل سفارش و تعداد
                </h3>

                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div>
                        <label for="df-least-price" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">کمترین مبلغ سفارش (تومان)</label>
                        <input id="df-least-price" type="text" inputmode="numeric" dir="ltr" wire:model="least_price" placeholder="بدون محدودیت"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                        @error('least_price') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="df-least-count" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">کمترین تعداد محصول</label>
                        <input id="df-least-count" type="text" inputmode="numeric" dir="ltr" wire:model="least_products_count" placeholder="بدون محدودیت"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                        @error('least_products_count') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="df-quantity" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">تعداد مجاز استفاده کل</label>
                        <input id="df-quantity" type="text" inputmode="numeric" dir="ltr" wire:model="quantity" placeholder="بدون محدودیت"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                        @error('quantity') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="df-quantity-user" class="mb-1.5 block text-[11px] font-bold text-slate-500 dark:text-slate-300">تعداد مجاز برای هر کاربر</label>
                        <input id="df-quantity-user" type="text" inputmode="numeric" dir="ltr" wire:model="quantity_per_user" placeholder="بدون محدودیت"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-xs font-extrabold outline-none transition placeholder:text-slate-400 focus:border-primary-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800">
                        @error('quantity_per_user') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-950/60 dark:text-violet-400"><x-icon name="users" class="size-3.5" /></span>
                    مشتریان مجاز
                    <span class="text-[9px] font-semibold text-slate-400">(خالی = همهٔ مشتری‌ها)</span>
                </h3>

                <div class="mt-3">
                    <label class="sr-only" for="df-users-search">جستجوی مشتری</label>
                    <div class="relative">
                        <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                        <input id="df-users-search" type="search" wire:model.live.debounce.350ms="usersSearch" placeholder="جستجو بر اساس نام یا شماره موبایل…" autocomplete="off"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pr-9 pl-3 text-[11px] font-semibold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    </div>

                    <div class="scroll-slim mt-2 max-h-44 space-y-1 overflow-y-auto" x-show="$wire.usersSearch.length > 1" x-cloak>
                        @forelse ($searchResults as $result)
                            <button type="button" wire:click="toggleCustomer({{ $result->id }})" wire:key="cust-{{ $result->id }}"
                                    class="flex w-full items-center justify-between gap-2 rounded-xl border px-3 py-2 text-[11px] font-bold transition {{ in_array($result->id, $customerIds)
                                        ? 'border-primary-400 bg-primary-50 text-primary-700 dark:border-primary-700 dark:bg-primary-950/40 dark:text-primary-300'
                                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="grid size-6 shrink-0 place-items-center rounded-full bg-slate-100 text-[9px] font-black text-slate-500 dark:bg-slate-800">
                                        {{ mb_substr($result->full_name ?: '؟', 0, 1) }}
                                    </span>
                                    <span class="min-w-0 truncate">{{ $result->full_name ?: 'بدون نام' }}</span>
                                </span>
                                <span class="flex shrink-0 items-center gap-1.5 text-[9.5px] text-slate-400" dir="ltr">
                                    {{ \App\Support\Fmt::toFa($result->phone) }}
                                    @if (in_array($result->id, $customerIds))
                                        <x-icon name="check" class="size-3.5 text-primary-600" />
                                    @endif
                                </span>
                            </button>
                        @empty
                            <p class="rounded-xl bg-slate-50 px-3 py-2.5 text-center text-[10px] font-bold text-slate-400 dark:bg-slate-800/60">مشتری‌ای یافت نشد</p>
                        @endforelse
                    </div>

                    @if ($selectedCustomers->isNotEmpty())
                        <div class="mt-2.5 border-t border-dashed border-slate-200 pt-2.5 dark:border-slate-700">
                            <div class="text-[10px] font-bold text-slate-400">انتخاب‌شده‌ها ({{ \App\Support\Fmt::toFa($selectedCustomers->count()) }} نفر):</div>
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                @foreach ($selectedCustomers as $customer)
                                    <button type="button" wire:click="toggleCustomer({{ $customer->id }})"
                                            class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2.5 py-1 text-[10px] font-extrabold text-primary-600 transition hover:bg-rose-50 hover:text-rose-500 dark:bg-primary-950/40 dark:text-primary-300"
                                            aria-label="حذف {{ $customer->full_name }}">
                                        {{ $customer->full_name ?: \App\Support\Fmt::toFa($customer->phone) }}
                                        <x-icon name="x" class="size-2.5" />
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- include / exclude scope --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400"><x-icon name="check" class="size-3.5" /></span>
                    محصولات مشمول تخفیف
                </h3>

                <div class="mt-3 grid grid-cols-3 gap-2">
                    @foreach ([
                        ['value' => 'all',      'label' => 'همه محصولات', 'icon' => 'package'],
                        ['value' => 'category', 'label' => 'انتخاب دسته',  'icon' => 'tag'],
                        ['value' => 'product',  'label' => 'انتخاب محصول', 'icon' => 'bag'],
                    ] as $opt)
                        <button type="button" wire:click="$set('include_type', '{{ $opt['value'] }}')"
                                class="rounded-2xl border-2 px-2 py-2.5 text-center transition {{ $include_type === $opt['value']
                                    ? 'border-primary-500 bg-primary-50/60 dark:border-primary-600 dark:bg-primary-950/40'
                                    : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900' }}">
                            <span class="mx-auto grid size-7 place-items-center rounded-lg {{ $include_type === $opt['value'] ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                <x-icon name="{{ $opt['icon'] }}" class="size-3.5" />
                            </span>
                            <span class="mt-1 block text-[9.5px] font-extrabold {{ $include_type === $opt['value'] ? 'text-primary-700 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400' }}">{{ $opt['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                @if ($include_type === 'category')
                    <div class="scroll-slim mt-3 grid max-h-40 grid-cols-2 gap-1.5 overflow-y-auto">
                        @foreach ($categories as $category)
                            <label class="flex cursor-pointer items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[10px] font-bold transition {{ in_array($category->id, $includeIds)
                                ? 'border-primary-400 bg-primary-50 text-primary-700 dark:border-primary-700 dark:bg-primary-950/40 dark:text-primary-300'
                                : 'border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}" wire:key="inc-cat-{{ $category->id }}">
                                <input type="checkbox" value="{{ $category->id }}" wire:model="includeIds" class="size-3.5 accent-primary-600">
                                <span class="truncate">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('includeIds') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                @elseif ($include_type === 'product')
                    <div class="scroll-slim mt-3 max-h-40 space-y-1 overflow-y-auto">
                        @foreach ($products as $product)
                            <label class="flex cursor-pointer items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[10px] font-bold transition {{ in_array($product->id, $includeIds)
                                ? 'border-primary-400 bg-primary-50 text-primary-700 dark:border-primary-700 dark:bg-primary-950/40 dark:text-primary-300'
                                : 'border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}" wire:key="inc-prod-{{ $product->id }}">
                                <input type="checkbox" value="{{ $product->id }}" wire:model="includeIds" class="size-3.5 shrink-0 accent-primary-600">
                                <span class="min-w-0 truncate">{{ $product->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('includeIds') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="flex items-center gap-2 text-xs font-extrabold">
                    <span class="grid size-7 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400"><x-icon name="circle-slash" class="size-3.5" /></span>
                    محصولات مستثنی
                </h3>

                <div class="mt-3 grid grid-cols-3 gap-2">
                    @foreach ([
                        ['value' => 'none',     'label' => 'هیچ کدام',     'icon' => 'package'],
                        ['value' => 'category', 'label' => 'انتخاب دسته',  'icon' => 'tag'],
                        ['value' => 'product',  'label' => 'انتخاب محصول', 'icon' => 'bag'],
                    ] as $opt)
                        <button type="button" wire:click="$set('exclude_type', '{{ $opt['value'] }}')"
                                class="rounded-2xl border-2 px-2 py-2.5 text-center transition {{ $exclude_type === $opt['value']
                                    ? 'border-amber-500 bg-amber-50/60 dark:border-amber-600 dark:bg-amber-950/40'
                                    : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900' }}">
                            <span class="mx-auto grid size-7 place-items-center rounded-lg {{ $exclude_type === $opt['value'] ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                <x-icon name="{{ $opt['icon'] }}" class="size-3.5" />
                            </span>
                            <span class="mt-1 block text-[9.5px] font-extrabold {{ $exclude_type === $opt['value'] ? 'text-amber-700 dark:text-amber-300' : 'text-slate-500 dark:text-slate-400' }}">{{ $opt['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                @if ($exclude_type === 'category')
                    <div class="scroll-slim mt-3 grid max-h-40 grid-cols-2 gap-1.5 overflow-y-auto">
                        @foreach ($categories as $category)
                            <label class="flex cursor-pointer items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[10px] font-bold transition {{ in_array($category->id, $excludeIds)
                                ? 'border-amber-400 bg-amber-50 text-amber-700 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-300'
                                : 'border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}" wire:key="exc-cat-{{ $category->id }}">
                                <input type="checkbox" value="{{ $category->id }}" wire:model="excludeIds" class="size-3.5 accent-amber-600">
                                <span class="truncate">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                @elseif ($exclude_type === 'product')
                    <div class="scroll-slim mt-3 max-h-40 space-y-1 overflow-y-auto">
                        @foreach ($products as $product)
                            <label class="flex cursor-pointer items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[10px] font-bold transition {{ in_array($product->id, $excludeIds)
                                ? 'border-amber-400 bg-amber-50 text-amber-700 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-300'
                                : 'border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}" wire:key="exc-prod-{{ $product->id }}">
                                <input type="checkbox" value="{{ $product->id }}" wire:model="excludeIds" class="size-3.5 shrink-0 accent-amber-600">
                                <span class="min-w-0 truncate">{{ $product->name }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-2.5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <label class="flex cursor-pointer items-center justify-between gap-2 text-[11px] font-bold">
                    <span>فقط برای اولین خرید مشتری</span>
                    <input type="checkbox" wire:model="only_first_purchase" class="size-4 rounded-md border-slate-300 text-primary-600 focus:ring-primary-500/30 dark:border-slate-600">
                </label>
                <label class="flex cursor-pointer items-center justify-between gap-2 text-[11px] font-bold">
                    <span>مستثنی‌کردن محصولات تخفیف‌دار</span>
                    <input type="checkbox" wire:model="not_discount_products" class="size-4 rounded-md border-slate-300 text-primary-600 focus:ring-primary-500/30 dark:border-slate-600">
                </label>
                <label class="flex cursor-pointer items-center justify-between gap-2 text-[11px] font-bold">
                    <span>انتشار کد تخفیف</span>
                    <input type="checkbox" wire:model="published" class="size-4 rounded-md border-slate-300 text-primary-600 focus:ring-primary-500/30 dark:border-slate-600">
                </label>
            </div>
        </div>

        {{-- ── pinned save bar ── --}}
        <div class="sticky bottom-0 z-20 -mx-4 mt-4 flex items-center justify-between gap-3 border-t border-slate-200/80 bg-white/95 px-4 pb-[max(0.875rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/95">
            <a href="{{ route('seller.discounts') }}" wire:navigate
               class="rounded-2xl border border-slate-200 px-4 py-2.5 text-[11px] font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                انصراف
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="inline-flex items-center gap-2 rounded-2xl bg-primary-600 px-6 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                <span wire:loading.remove wire:target="save">{{ $discountId ? 'ذخیره تغییرات' : 'ذخیره تخفیف' }}</span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <span class="size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    در حال ذخیره…
                </span>
            </button>
        </div>
    </form>
</div>
