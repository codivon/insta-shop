<div class="ip-fade-up flex min-h-full flex-col">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center justify-between gap-2">
            <a href="{{ url()->previous() ?: route('shop.home') }}"
               class="grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-800"
               aria-label="بازگشت">
                <x-icon name="arrow-left" class="size-5" />
            </a>
            <div class="truncate text-xs font-bold text-slate-500 dark:text-slate-400">{{ $product->shop?->name }}</div>
            <div class="flex shrink-0 items-center gap-0.5">
                {{-- live cart badge in the page header --}}
                <a href="{{ route('shop.cart') }}" wire:navigate
                   class="relative grid size-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-800"
                   aria-label="سبد خرید">
                    <x-icon name="cart" class="size-5" />
                    <span data-cart-badge
                          class="absolute right-0.5 top-0.5 grid min-w-4 place-items-center rounded-full bg-primary-600 px-1 text-[9px] font-bold text-white shadow {{ $cartCount === 0 ? 'hidden' : '' }}"
                          data-count="{{ $cartCount }}">{{ \App\Support\Fmt::toFa($cartCount) }}</span>
                </a>
                <button type="button" wire:click="toggleFavorite"
                        class="grid size-10 place-items-center rounded-xl transition hover:bg-slate-100 dark:hover:bg-slate-800 {{ $isFavorite ? 'text-rose-500' : 'text-slate-400 hover:text-rose-400' }}"
                        aria-label="{{ $isFavorite ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}">
                    <x-icon name="heart" class="size-5 {{ $isFavorite ? 'fill-current' : '' }}" />
                </button>
            </div>
        </div>
    </div>

    @php $specialActive = $product->isSpecialActive(); @endphp

    <div class="px-4 pt-4">
        {{-- ── فروش ویژه — بالای تصویر (special sale, above the image) ── --}}
        @if ($specialActive)
            @if ($specialEndsMs)
                <div x-data="sfCountdown({{ $specialEndsMs }})"
                     class="relative mb-3 overflow-hidden rounded-2xl bg-gradient-to-l from-rose-600 via-rose-500 to-orange-500 p-3.5 shadow-lg shadow-rose-500/25">
                    <div class="pointer-events-none absolute inset-0 opacity-25" aria-hidden="true">
                        <div class="absolute -top-8 left-6 size-24 rounded-full bg-white/30 blur-2xl"></div>
                        <div class="absolute -bottom-10 right-8 size-28 rounded-full bg-amber-300/30 blur-2xl"></div>
                    </div>

                    <div class="relative flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-white/20 backdrop-blur">
                                <x-icon name="timer" class="size-4 text-white" />
                            </span>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[9px] font-black text-rose-600 shadow-sm">
                                        <x-icon name="flame" class="size-2.5" />
                                        فروش ویژه
                                    </span>
                                    <span class="text-[10px] font-extrabold text-white">پیشنهاد محدود!</span>
                                </div>
                                <div class="mt-0.5 text-[9px] font-bold text-rose-50/85">تا پایان زمان باقی‌مانده فرصت دارید</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1" dir="ltr">
                            <span class="grid min-w-8 place-items-center rounded-lg bg-white/20 px-1 py-1 backdrop-blur">
                                <b class="font-mono text-[13px] font-black leading-4 text-white" x-text="pad(d)">۰۰</b>
                                <i class="text-[6.5px] font-bold not-italic text-rose-50/80">روز</i>
                            </span>
                            <span class="text-white/60">:</span>
                            <span class="grid min-w-8 place-items-center rounded-lg bg-white/20 px-1 py-1 backdrop-blur">
                                <b class="font-mono text-[13px] font-black leading-4 text-white" x-text="pad(h)">۰۰</b>
                                <i class="text-[6.5px] font-bold not-italic text-rose-50/80">ساعت</i>
                            </span>
                            <span class="text-white/60">:</span>
                            <span class="grid min-w-8 place-items-center rounded-lg bg-white/20 px-1 py-1 backdrop-blur">
                                <b class="font-mono text-[13px] font-black leading-4 text-white" x-text="pad(m)">۰۰</b>
                                <i class="text-[6.5px] font-bold not-italic text-rose-50/80">دقیقه</i>
                            </span>
                            <span class="text-white/60">:</span>
                            <span class="grid min-w-8 place-items-center rounded-lg bg-white px-1 py-1 shadow-md">
                                <b class="font-mono text-[13px] font-black leading-4 text-rose-600" x-text="pad(s)">۰۰</b>
                                <i class="text-[6.5px] font-bold not-italic text-rose-500/80">ثانیه</i>
                            </span>
                        </div>
                    </div>

                    <p class="relative mt-2.5 rounded-xl bg-white/15 px-2.5 py-1.5 text-center text-[9.5px] font-bold text-white backdrop-blur"
                       x-show="over" x-cloak>
                        فروش ویژه پایان یافت — قیمت عادی اعمال شد
                    </p>
                </div>
            @else
                <div class="mb-3 flex items-center gap-2 rounded-2xl bg-gradient-to-l from-rose-600 to-orange-500 px-3.5 py-2.5 shadow-lg shadow-rose-500/25">
                    <span class="grid size-7 place-items-center rounded-lg bg-white/20 backdrop-blur">
                        <x-icon name="flame" class="size-3.5 text-white" />
                    </span>
                    <span class="text-[11px] font-black text-white">فروش ویژه — پیشنهاد محدود!</span>
                    <span class="mr-auto rounded-full bg-white px-2 py-0.5 text-[9px] font-black text-rose-600">ویژه</span>
                </div>
            @endif
        @endif

        {{-- ── gallery ── --}}
        <div class="grid h-64 place-items-center overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @if ($activeImg)
                <img src="{{ $activeImg['url'] }}" alt="{{ $product->name }}" class="size-full object-contain">
            @else
                <x-icon name="package" class="size-16 text-slate-300 dark:text-slate-600" />
            @endif
        </div>

        @if (count($gallery) > 1)
            <div class="scroll-slim mt-3 flex gap-2 overflow-x-auto pb-1">
                @foreach ($gallery as $img)
                    <button type="button" wire:click="selectImage({{ $img['id'] }})"
                            class="size-16 shrink-0 overflow-hidden rounded-xl border-2 transition {{ $activeImg && $activeImg['id'] === $img['id'] ? 'border-primary-500' : 'border-transparent opacity-70 hover:opacity-100' }}"
                            aria-label="تصویر {{ \App\Support\Fmt::toFa($loop->iteration) }}">
                        <img src="{{ $img['url'] }}" alt="" class="size-full object-cover">
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── info ── --}}
    <div class="px-4 pt-4">
        <h1 class="text-base font-extrabold leading-7">{{ $product->name }}</h1>

        <div class="mt-2 flex flex-wrap items-center gap-2 text-[10px]">
            @if ($product->category)
                <span class="rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">{{ $product->category->name }}</span>
            @endif
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                <x-icon name="eye" class="size-3" /> {{ \App\Support\Fmt::toFa($product->views) }} بازدید
            </span>
            <span class="rounded-full px-2.5 py-1 font-bold {{ $isDownloadable || $displayStock > 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                @if ($isDownloadable)
                    دانلودی — دسترسی فوری
                @else
                    {{ $displayStock > 0 ? \App\Support\Fmt::toFa($displayStock).' عدد موجود' : 'ناموجود' }}
                @endif
            </span>
        </div>

        {{-- ── انتخاب تنوع — گزینه‌های گرد برای هر ویژگی (رنگ / سایز / گارانتی …)
                ارزان‌ترین ترکیب موجود پیش‌فرض است و قیمت لحظه‌ای تغییر می‌کند ── --}}
        @if ($variantMatrix->isNotEmpty())
            <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="flex items-center gap-1.5 text-xs font-extrabold">
                        <span class="grid size-6 place-items-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400">
                            <x-icon name="layers" class="size-3" />
                        </span>
                        انتخاب تنوع
                    </h3>
                    @if ($selectedVariant)
                        <span class="max-w-40 truncate rounded-full bg-primary-50 px-2 py-0.5 text-[9px] font-bold text-primary-600 dark:bg-primary-950/40 dark:text-primary-400">{{ $selectedVariant->label() }}</span>
                    @else
                        <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[9px] font-bold text-rose-500 dark:bg-rose-950/40 dark:text-rose-400">این ترکیب موجود نیست</span>
                    @endif
                </div>

                @foreach ($variantMatrix as $attr)
                    <div class="mt-3.5">
                        <div class="mb-2 flex items-center gap-1 text-[10.5px] font-extrabold text-slate-500 dark:text-slate-400">
                            @if ($attr['type'] === 'color')
                                <x-icon name="palette" class="size-3" />
                            @endif
                            {{ $attr['name'] }}
                        </div>

                        <div class="flex flex-wrap gap-2" role="group" aria-label="{{ $attr['name'] }}">
                            @foreach ($attr['options'] as $optIndex => $opt)
                                @php $picked = ($picks[$attr['id']] ?? null) === $opt['key']; @endphp
                                <button type="button"
                                        wire:click="selectOption({{ $attr['id'] }}, {{ $optIndex }})"
                                        @disabled(! $opt['available'])
                                        title="{{ $opt['label'] }}{{ $opt['available'] && ! $opt['inStock'] ? ' — ناموجود' : '' }}"
                                        aria-pressed="{{ $picked ? 'true' : 'false' }}"
                                        aria-label="انتخاب {{ $attr['name'] }} {{ $opt['label'] }}"
                                        class="inline-flex items-center gap-1.5 rounded-full border py-1 transition active:scale-95
                                               {{ $picked
                                                   ? 'border-primary-600 bg-primary-600/10 ring-2 ring-primary-500/40 dark:border-primary-500 dark:bg-primary-500/15'
                                                   : 'border-slate-200 bg-white hover:border-primary-300 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-primary-700' }}
                                               {{ ! $opt['available'] ? 'pointer-events-none opacity-35 grayscale' : (! $opt['inStock'] ? 'opacity-60' : '') }}">
                                    @if ($attr['type'] === 'color')
                                        <span class="relative grid size-7 shrink-0 place-items-center rounded-full border border-slate-900/10 shadow-inner"
                                              style="background: {{ $opt['hex'] ?: '#e2e8f0' }}">
                                            @if ($picked)
                                                <x-icon name="check" class="size-3.5 text-white drop-shadow-[0_1px_1px_rgba(0,0,0,0.65)]" />
                                            @endif
                                        </span>
                                        <span class="pl-2 text-[10px] font-extrabold {{ $picked ? 'text-primary-700 dark:text-primary-300' : 'text-slate-600 dark:text-slate-300' }}">{{ $opt['label'] }}</span>
                                    @else
                                        <span class="px-3.5 py-1.5 text-[10.5px] font-extrabold {{ $picked ? 'text-primary-700 dark:text-primary-300' : 'text-slate-600 dark:text-slate-300' }}">
                                            {{ $opt['label'] }}
                                            @if ($opt['available'] && ! $opt['inStock'])
                                                <span class="text-[8.5px] font-bold text-rose-400">(ناموجود)</span>
                                            @endif
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- ویژگی‌های متنی (مثل حکاکی) — فقط نمایش، انتخاب نیستند --}}
                @if ($textNotes)
                    <div class="mt-3.5 flex flex-wrap gap-1.5 border-t border-dashed border-slate-200 pt-3 dark:border-slate-700">
                        @foreach ($textNotes as $note)
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[9.5px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                <x-icon name="edit" class="size-2.5" />
                                {{ $note['name'] }}: {{ $note['text'] }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        @elseif ($variants->isNotEmpty())
            {{-- حالت قدیمی: تنوع‌ها بدون ویژگی انتخابی → کارت‌های ترکیبی کامل --}}
            <div class="mt-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-extrabold">انتخاب تنوع</h3>
                    @if ($selectedVariant)
                        <span class="max-w-44 truncate rounded-full bg-primary-50 px-2 py-0.5 text-[9px] font-bold text-primary-600 dark:bg-primary-950/40 dark:text-primary-400">{{ $selectedVariant->label() }}</span>
                    @endif
                </div>

                <div class="mt-2 grid grid-cols-2 gap-2">
                    @foreach ($variants as $variant)
                        <button type="button" wire:click="selectLegacyVariant({{ $variant->id }})" @disabled($variant->stock < 1)
                                class="rounded-2xl border p-2.5 text-right transition {{ $selectedVariant && $selectedVariant->id === $variant->id
                                    ? 'border-primary-500 bg-primary-50 ring-1 ring-primary-500 dark:border-primary-500 dark:bg-primary-950/40'
                                    : 'border-slate-200 bg-white hover:border-primary-300 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-primary-700' }}
                                {{ $variant->stock < 1 ? 'cursor-not-allowed opacity-50' : '' }}"
                                aria-pressed="{{ $selectedVariant && $selectedVariant->id === $variant->id ? 'true' : 'false' }}" aria-label="انتخاب {{ $variant->label() }}">
                            <div class="line-clamp-1 text-[11px] font-bold">{{ $variant->label() }}</div>
                            <div class="mt-1.5 flex items-center justify-between gap-1">
                                <span class="text-[10px] font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::moneyShort($variant->finalPrice()) }}</span>
                                @if ($variant->isDiscountActive())
                                    <span class="rounded-full bg-rose-500 px-1.5 py-0.5 text-[8px] font-black text-white">٪{{ \App\Support\Fmt::toFa($variant->discount_percent) }}</span>
                                @endif
                                <span class="text-[8.5px] font-bold {{ $variant->stock > 0 ? 'text-slate-400' : 'text-rose-500' }}">{{ $variant->stock > 0 ? \App\Support\Fmt::toFa($variant->stock).' عدد' : 'ناموجود' }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── price (reacts to the picked variant) ── --}}
        @php
            $unit = $displayPrice;
            $unitOriginal = null;

            if ($selectedVariant && $selectedVariant->isDiscountActive()) {
                $unitOriginal = (int) $selectedVariant->price;
            } elseif ($product->compare_at_price > 0 && $product->compare_at_price > $unit) {
                $unitOriginal = (int) $product->compare_at_price;
            }

            $unitOff = $unitOriginal ? (int) round(100 - ($unit / $unitOriginal * 100)) : 0;
        @endphp
        <div class="mt-4 flex items-end justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                @if ($unitOriginal)
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[9px] font-bold text-white">٪{{ \App\Support\Fmt::toFa($unitOff) }} تخفیف</span>
                        <span class="text-[11px] text-slate-400 line-through">{{ \App\Support\Fmt::fa($unitOriginal) }}</span>
                    </div>
                @endif
                <div class="mt-1 text-lg font-extrabold text-primary-600 dark:text-primary-400">
                    {{ \App\Support\Fmt::fa($unit) }}
                    <span class="text-[10px] font-semibold text-slate-400">تومان</span>
                </div>
            </div>
            <span class="rounded-xl bg-slate-100 px-2 py-1 text-[9px] font-bold text-slate-400 dark:bg-slate-800">قیمت واحد</span>
        </div>

        {{-- ── تب توضیحات / مشخصات ── --}}
        @php
            $specGroups = collect($product->specifications ?? [])->filter(
                fn ($group) => trim((string) ($group['name'] ?? '')) !== ''
                    || collect($group['items'] ?? [])->contains(fn ($item) => trim((string) ($item['title'] ?? '')) !== ''),
            )->values();
        @endphp
        <div class="mt-4" x-data="{ tab: 'desc' }">
            <div class="flex gap-1 rounded-2xl bg-slate-100 p-1 dark:bg-slate-800" role="tablist" aria-label="اطلاعات محصول">
                <button type="button" @click="tab = 'desc'" :aria-selected="tab === 'desc'" role="tab"
                        class="flex-1 rounded-xl py-2 text-[11px] font-extrabold transition"
                        :class="tab === 'desc'
                            ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-900 dark:text-white'
                            : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                    توضیحات
                </button>
                <button type="button" @click="tab = 'specs'" :aria-selected="tab === 'specs'" role="tab"
                        class="flex-1 rounded-xl py-2 text-[11px] font-extrabold transition"
                        :class="tab === 'specs'
                            ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-900 dark:text-white'
                            : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                    مشخصات
                </button>
            </div>

            <div class="mt-3" x-show="tab === 'desc'" role="tabpanel" aria-label="توضیحات محصول">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    @if ($product->description)
                        <p class="whitespace-pre-line text-xs leading-6 text-slate-500 dark:text-slate-400">{{ $product->description }}</p>
                    @else
                        <div class="py-4 text-center">
                            <x-icon name="file-text" class="mx-auto size-7 text-slate-300 dark:text-slate-600" />
                            <p class="mt-2 text-[11px] font-bold text-slate-400">توضیحی برای این محصول ثبت نشده است.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-3" x-show="tab === 'specs'" x-cloak role="tabpanel" aria-label="مشخصات محصول">
                @if ($specGroups->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($specGroups as $group)
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                                @if (trim((string) ($group['name'] ?? '')) !== '')
                                    <div class="border-b border-dashed border-slate-200 bg-slate-50/80 px-3.5 py-2 text-[11px] font-extrabold text-slate-700 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200">
                                        {{ $group['name'] }}
                                    </div>
                                @endif
                                <dl class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach (($group['items'] ?? []) as $item)
                                        @continue(trim((string) ($item['title'] ?? '')) === '')
                                        <div class="flex items-start justify-between gap-3 px-3.5 py-2">
                                            <dt class="shrink-0 text-[10.5px] font-bold text-slate-400">{{ $item['title'] }}</dt>
                                            <dd class="text-left text-[10.5px] font-semibold leading-5 text-slate-600 dark:text-slate-300">{{ $item['value'] }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <div class="py-4 text-center">
                            <x-icon name="list" class="mx-auto size-7 text-slate-300 dark:text-slate-600" />
                            <p class="mt-2 text-[11px] font-bold text-slate-400">مشخصاتی برای این محصول ثبت نشده است.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── محصولات مرتبط (related products slider) ── --}}
        @if ($related->isNotEmpty())
            <div class="mt-5">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-xs font-extrabold">محصولات مرتبط</h3>
                    <span class="text-[10px] font-bold text-slate-400">{{ \App\Support\Fmt::toFa($related->count()) }} محصول</span>
                </div>
                <div class="scroll-slim -mx-4 flex snap-x gap-3 overflow-x-auto px-4 pb-2">
                    @foreach ($related as $rel)
                        <a href="{{ route('shop.product.show', $rel->id) }}" wire:navigate wire:key="related-{{ $rel->id }}"
                           class="group w-32 shrink-0 snap-start overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                            <div class="relative grid h-28 place-items-center overflow-hidden bg-slate-100 dark:bg-slate-800">
                                @if ($rel->mainImage)
                                    <img src="{{ $rel->mainImage->url() }}" alt="{{ $rel->name }}" loading="lazy"
                                         class="size-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <x-icon name="package" class="size-8 text-slate-300 dark:text-slate-600" />
                                @endif
                                @if ($rel->isSpecialActive())
                                    <span class="absolute right-1.5 top-1.5 flex items-center gap-0.5 rounded-full bg-gradient-to-l from-rose-600 to-orange-500 px-1.5 py-0.5 text-[8px] font-black text-white shadow">
                                        <x-icon name="flame" class="size-2" /> ویژه
                                    </span>
                                @endif
                                @if ($rel->type === \App\Models\Product::TYPE_PHYSICAL && $rel->stock < 1)
                                    <span class="absolute inset-0 grid place-items-center bg-slate-950/55 text-[10px] font-extrabold text-white backdrop-blur-[2px]">ناموجود</span>
                                @endif
                            </div>
                            <div class="p-2">
                                <div class="line-clamp-1 min-h-4 text-[10.5px] font-bold leading-4 transition group-hover:text-primary-600 dark:group-hover:text-primary-400">{{ $rel->name }}</div>
                                <div class="mt-1 text-[11px] font-extrabold text-primary-600 dark:text-primary-400">
                                    {{ \App\Support\Fmt::moneyShort($rel->price) }}
                                    <span class="text-[8px] font-bold text-slate-400">تومان</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="h-5"></div>
    </div>

    {{-- ───────── Fixed bottom buy bar (replaces the tab bar on this page) ───────── --}}
    <div class="sticky bottom-0 z-30 mt-auto {{ $specialActive
        ? 'border-t border-rose-400/40 bg-gradient-to-l from-rose-600 via-rose-500 to-orange-500 shadow-[0_-8px_24px_-12px_rgba(225,29,72,0.55)]'
        : 'border-t border-slate-200/80 bg-white/95 shadow-[0_-8px_24px_-16px_rgba(15,23,42,0.25)] backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/95' }}">
        <div class="flex items-center gap-3 px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            {{-- price / summary zone --}}
            <div class="min-w-0">
                @if ($specialActive)
                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[9px] font-black text-rose-600 shadow-sm">
                        <x-icon name="flame" class="size-2.5" />
                        ویژه
                    </span>
                @endif
                <div class="flex items-baseline gap-1.5">
                    <span class="text-base font-black {{ $specialActive ? 'text-white' : 'text-primary-600 dark:text-primary-400' }}">
                        {{ \App\Support\Fmt::fa($unit) }}
                    </span>
                    <span class="text-[9px] font-bold {{ $specialActive ? 'text-rose-50/85' : 'text-slate-400' }}">تومان</span>
                    @if ($unitOriginal)
                        <span class="text-[9px] font-semibold {{ $specialActive ? 'text-rose-100/80' : 'text-slate-400' }} line-through">{{ \App\Support\Fmt::fa($unitOriginal) }}</span>
                    @endif
                </div>
                @if ($cartQty > 0)
                    <div class="mt-0.5 text-[9px] font-bold {{ $specialActive ? 'text-rose-50/90' : 'text-slate-400' }}">
                        {{ \App\Support\Fmt::toFa($cartQty) }} عدد در سبد — مجموع {{ \App\Support\Fmt::money($unit * $cartQty) }}
                    </div>
                @endif
            </div>

            {{-- action zone: add button ⇄ quantity stepper (instant) --}}
            <div class="ms-auto shrink-0">
                @if (! $isDownloadable && $displayStock < 1)
                    <span class="inline-flex cursor-not-allowed items-center justify-center gap-2 rounded-2xl bg-slate-200 px-6 py-3 text-xs font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        ناموجود
                    </span>
                @elseif ($cartQty === 0)
                    <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-xs font-extrabold shadow-lg transition active:scale-[0.97]
                                   {{ $specialActive
                                       ? 'bg-white text-rose-600 shadow-rose-900/20 hover:bg-rose-50'
                                       : 'bg-primary-600 text-white shadow-primary-600/30 hover:bg-primary-700' }} disabled:opacity-50">
                        <x-icon name="cart" class="size-4" />
                        افزودن به سبد خرید
                    </button>
                @else
                    <div class="flex items-center gap-1 rounded-2xl border p-1 {{ $specialActive ? 'border-white/40 bg-white/15 backdrop-blur' : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900' }}">
                        <button type="button" wire:click="cartIncrement" @disabled($cartQty >= $purchaseCap)
                                class="grid size-8 place-items-center rounded-xl transition {{ $specialActive ? 'text-white hover:bg-white/20 disabled:opacity-40' : 'text-slate-500 hover:bg-slate-100 disabled:opacity-30 dark:hover:bg-slate-800' }}"
                                aria-label="افزایش تعداد در سبد">
                            <x-icon name="plus" class="size-4" />
                        </button>
                        <span class="min-w-8 text-center text-sm font-black {{ $specialActive ? 'text-white' : '' }}">{{ \App\Support\Fmt::toFa($cartQty) }}</span>
                        <button type="button" wire:click="cartDecrement"
                                class="grid size-8 place-items-center rounded-xl transition {{ $specialActive ? 'text-white hover:bg-white/20' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                                aria-label="کاهش تعداد در سبد">
                            @if ($cartQty <= 1)
                                <x-icon name="trash" class="size-3.5" />
                            @else
                                <x-icon name="minus" class="size-4" />
                            @endif
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- toast --}}
    <div x-data="{ show: false, msg: '' }"
         @product-toast.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2600)"
         x-show="show" x-transition.opacity
         class="fixed bottom-24 left-1/2 z-40 w-72 -translate-x-1/2 rounded-2xl bg-slate-900/95 px-4 py-3 text-center text-[11px] font-bold text-white shadow-2xl dark:bg-slate-800/95"
         x-cloak>
        <span x-text="msg"></span>
    </div>
</div>
