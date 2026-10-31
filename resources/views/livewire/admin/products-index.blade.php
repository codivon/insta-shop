<div class="ip-fade-up space-y-4">

    {{-- ── flash toast ── --}}
    @if (session('admin-catalog-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-catalog-flash') }}
        </div>
    @endif

    {{-- ── toolbar ── --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="relative w-full xl:max-w-xs">
                <x-icon name="search" class="absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input type="search" wire:model.live.debounce.350ms="search" placeholder="نام محصول یا فروشگاه…"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-4 pr-10 text-sm shadow-sm transition focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800"
                       aria-label="جستجوی محصول">
                <span wire:loading wire:target="search" class="absolute left-3 top-1/2 -translate-y-1/2">
                    <x-icon name="rotate" class="size-4 animate-spin text-primary-500" />
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="flex flex-wrap items-center gap-2" role="group" aria-label="فیلتر وضعیت">
                    @foreach ([
                        ['key' => 'all',       'label' => 'همه ('.\App\Support\Fmt::fa($stats['all']).')'],
                        ['key' => 'published', 'label' => 'منتشرشده ('.\App\Support\Fmt::fa($stats['published']).')'],
                        ['key' => 'draft',     'label' => 'پیش‌نویس ('.\App\Support\Fmt::fa($stats['draft']).')'],
                    ] as $tab)
                        <button type="button" wire:click="$set('status', '{{ $tab['key'] }}')"
                                class="rounded-xl px-3.5 py-2 text-xs font-bold transition {{ $status === $tab['key']
                                    ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                    : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>

                <select wire:model.live="shopId" aria-label="فیلتر فروشگاه"
                        class="max-w-44 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold shadow-sm focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    <option value="0">همه فروشگاه‌ها</option>
                    @foreach ($shopOptions as $shopOption)
                        <option value="{{ $shopOption->id }}">{{ $shopOption->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ── table ── --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($products->isEmpty())
            <div class="flex flex-col items-center gap-3 py-16 text-center">
                <span class="grid size-14 place-items-center rounded-3xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <x-icon name="package" class="size-7" />
                </span>
                <p class="text-sm font-bold text-slate-500">محصولی یافت نشد</p>
                <p class="text-xs text-slate-400">{{ $search !== '' || $shopId > 0 || $status !== 'all' ? 'فیلترها را تغییر دهید.' : 'محصولات از اپ فروشنده اضافه می‌شوند.' }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                            <th class="px-5 py-3 font-semibold">محصول</th>
                            <th class="px-3 py-3 font-semibold">فروشگاه</th>
                            <th class="px-3 py-3 font-semibold">قیمت</th>
                            <th class="px-3 py-3 font-semibold">موجودی</th>
                            <th class="px-3 py-3 font-semibold">بازدید</th>
                            <th class="px-3 py-3 font-semibold">وضعیت</th>
                            <th class="px-5 py-3 font-semibold">اقدام</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach ($products as $product)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="p-{{ $product->id }}">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-10 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                                            @if ($product->mainImage)
                                                <img src="{{ $product->mainImage->url() }}" alt="{{ $product->name }}" class="size-full object-cover" loading="lazy">
                                            @else
                                                <x-icon name="image" class="size-4" />
                                            @endif
                                        </span>
                                        <div class="max-w-56">
                                            <span class="block truncate text-xs font-bold">{{ $product->name }}</span>
                                            <span class="block text-[10px] text-slate-400">{{ \App\Support\JDate::date($product->created_at) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $product->shop?->name ?? '—' }}</td>
                                <td class="px-3 py-3.5">
                                    <span class="block text-xs font-bold">{{ \App\Support\Fmt::money($product->price) }}</span>
                                    @if ($product->compare_at_price > $product->price)
                                        <span class="text-[10px] text-slate-400 line-through">{{ \App\Support\Fmt::money($product->compare_at_price) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    @if ($product->stock > 0)
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::fa($product->stock) }}</span>
                                    @else
                                        <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-500 dark:bg-rose-950/40">ناموجود</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-xs text-slate-500">{{ \App\Support\Fmt::fa($product->views) }}</td>
                                <td class="px-3 py-3.5">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $product->status === 'published'
                                        ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $product->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <button type="button" wire:click="deleteProduct({{ $product->id }})"
                                            wire:confirm="محصول «{{ $product->name }}» برای همیشه حذف شود؟ این عمل قابل بازگشت نیست."
                                            class="grid size-8 place-items-center rounded-lg bg-rose-50 text-rose-600 transition hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-400"
                                            aria-label="حذف محصول">
                                        <x-icon name="trash" class="size-3.5" />
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($hasMore)
                <div class="border-t border-slate-100 p-4 text-center dark:border-slate-800">
                    <button type="button" wire:click="loadMore" wire:loading.attr="disabled"
                            class="rounded-xl border border-slate-200 px-5 py-2 text-xs font-bold text-slate-600 transition hover:border-primary-400 hover:text-primary-600 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300">
                        نمایش بیشتر
                    </button>
                </div>
            @endif
        @endif
    </div>

    <p class="text-center text-[11px] text-slate-400">
        نمایش {{ \App\Support\Fmt::fa(min($products->count(), $perPage)) }} از {{ \App\Support\Fmt::fa($stats['all']) }} محصول
    </p>
</div>
