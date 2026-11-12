<div class="ip-fade-up">
    {{-- ───────── Sticky in-feed header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('shop.cart') }}" wire:navigate
               class="grid size-10 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800"
               aria-label="بازگشت به سبد خرید">
                <x-icon name="arrow-left" class="size-5" />
            </a>
            <div>
                <div class="text-sm font-extrabold leading-5">تکمیل خرید و پرداخت</div>
                <div class="text-[11px] text-slate-400">روش ارسال، کد تخفیف و درگاه پرداخت</div>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4">
        @if ($validItems->isEmpty())
            {{-- ── nothing purchasable ── --}}
            <div class="mt-10 rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-slate-100 dark:bg-slate-800">
                    <x-icon name="cart" class="size-8 text-slate-300 dark:text-slate-600" />
                </span>
                <p class="mt-4 text-sm font-extrabold">کالای قابل خریدی در سبد نیست</p>
                <p class="mt-1 text-[11px] text-slate-400">برای تکمیل خرید حداقل یک محصول موجود لازم است.</p>
                <a href="{{ route('shop.cart') }}" wire:navigate
                   class="mt-5 inline-block rounded-2xl bg-primary-600 px-6 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-95">
                    بازگشت به سبد خرید
                </a>
            </div>
        @else
            @error('cart')
                <div class="mb-3 rounded-2xl border border-rose-200 bg-rose-50/70 p-3 text-[11px] font-bold leading-5 text-rose-600 dark:border-rose-900 dark:bg-rose-950/30 dark:text-rose-400">
                    {{ $message }}
                </div>
            @enderror

            {{--
                انتخاب‌ها (حامل/روز/درگاه) تماماً سمت مرورگرند: هایلایت با
                peer-checked (CSS خالص) انجام می‌شود و هیچ roundtrip ای روی
                این تعامل‌ها سوار نیست — پس هیچ morph ای هم نمی‌تواند صفحه را
                به هم بریزد. فقط جمع‌ها (هزینهٔ ارسال و مبلغ قابل پرداخت) با
                Alpine و از روی data-cost خود radioها بلافاصله به‌روز می‌شوند.
                اعتبارسنجی نهایی همچنان سمت سرور، هنگام placeOrder است.
            --}}
            <form wire:submit="placeOrder"
                  x-data="{
                      total: {{ $total }},
                      ship: {{ $shippingCost }},
                      post: {{ $hasPayOnDelivery ? 'true' : 'false' }},
                      discount: {{ $liveDiscount[0] }},
                      gw: '{{ $gateway }}',
                      bnplFees: @js($bnplFees),
                      get bnplFee() { return Number(this.bnplFees[this.gw] ?? 0) },
                      get payable() { return Math.max(0, this.total + this.bnplFee + this.ship - this.discount) },
                      fa(n) {
                          return String(Math.max(0, Math.round(Number(n) || 0)))
                              .replace(/\B(?=(\d{3})+(?!\d))/g, '٬')
                              .replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d])
                      },
                      recalc() {
                          let s = 0, p = false
                          const gw = this.$el.querySelector('input[name=gateway]:checked')
                          if (gw) this.gw = gw.value
                          this.$el.querySelectorAll('input[name^=carrier-]:checked').forEach((r) => {
                              if (r.dataset.postpaid === '1') { p = true; return }
                              s += Number(r.dataset.cost || 0)
                          })
                          this.ship = s
                          this.post = p
                          this.discount = Number(this.$el.dataset.discount || 0)
                      },
                  }"
                  x-on:change="recalc()"
                  x-on:discount-updated.window="discount = Number($event.detail?.amount ?? $el.dataset.discount ?? 0); recalc()"
                  data-discount="{{ $liveDiscount[0] }}">
                {{-- ── delivery: آدرس منتخب از دفترچه (73) ── --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2">
                        <span class="grid size-9 place-items-center rounded-xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                            <x-icon name="map-pin" class="size-4.5" />
                        </span>
                        <h3 class="text-xs font-extrabold">آدرس تحویل سفارش</h3>
                    </div>

                    @if ($selectedAddress)
                        <div class="mt-3 rounded-2xl border border-primary-200 bg-primary-50/50 p-3.5 dark:border-primary-800 dark:bg-primary-950/20" wire:key="sel-addr-{{ $selectedAddress->id }}">
                            <div class="flex items-start gap-2.5">
                                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-white text-primary-600 shadow-sm dark:bg-slate-900 dark:text-primary-400">
                                    <x-icon name="{{ $selectedAddress->title === 'محل کار' ? 'store' : 'home' }}" class="size-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="truncate text-[11px] font-extrabold">{{ $selectedAddress->title ?: 'آدرس تحویل' }}</span>
                                        @if ($selectedAddress->is_default)
                                            <span class="rounded-full bg-primary-100 px-1.5 py-0.5 text-[8.5px] font-extrabold text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">پیش‌فرض</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-[10.5px] font-bold leading-5 text-slate-600 dark:text-slate-300">{{ $selectedAddress->fullLabel() }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[9px] text-slate-400">
                                        @if ($selectedAddress->recipient_name)
                                            <span class="flex items-center gap-1"><x-icon name="user" class="size-2.5" /> {{ $selectedAddress->recipient_name }}</span>
                                        @endif
                                        @if ($selectedAddress->phone)
                                            <span class="flex items-center gap-1" dir="ltr"><x-icon name="phone" class="size-2.5" /> {{ \App\Support\Fmt::toFa($selectedAddress->phone) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- آدرسی انتخاب/ثبت نشده — راهنما + مسیر لیست آدرس‌ها --}}
                        <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50/80 p-3.5 text-center dark:border-amber-900 dark:bg-amber-950/30">
                            <span class="mx-auto grid size-10 place-items-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-300">
                                <x-icon name="map-pin" class="size-5" />
                            </span>
                            <p class="mt-2 text-[11px] font-extrabold text-amber-700 dark:text-amber-300">
                                {{ $addresses->isNotEmpty() ? 'هنوز آدرس تحویل را انتخاب نکرده‌اید.' : 'شما هنوز آدرسی ثبت نکرده‌اید.' }}
                            </p>
                            <p class="mt-0.5 text-[9.5px] font-bold leading-4 text-amber-600/80 dark:text-amber-400/80">
                                برای ادامهٔ خرید، از لیست آدرس‌ها یک نشانی انتخاب یا آدرس جدید ثبت کنید.
                            </p>
                            <button type="button" wire:click="openAddressModal"
                                    class="mt-2.5 inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-500 py-2.5 text-[11px] font-extrabold text-white shadow-md shadow-amber-500/25 transition hover:bg-amber-600 active:scale-95">
                                <x-icon name="list" class="size-3.5" />
                                لیست آدرس‌ها
                            </button>
                        </div>
                    @endif

                    @error('selectedAddressId') <p class="mt-2 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                    @if ($selectedAddress)
                        <button type="button" wire:click="openAddressModal"
                                class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border border-dashed border-primary-300 bg-primary-50/50 py-2.5 text-[11px] font-extrabold text-primary-600 transition hover:bg-primary-100/60 dark:border-primary-800 dark:bg-primary-950/20 dark:text-primary-400 dark:hover:bg-primary-950/40">
                            <x-icon name="edit" class="size-3.5" />
                            تغییر آدرس
                        </button>
                    @endif
                </div>

                {{-- ── خلاصه سفارش — گروه‌بندی بر اساس فروشگاه + حامل هر فروشگاه ── --}}
                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-2">
                        <span class="grid size-9 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                            <x-icon name="file-text" class="size-4.5" />
                        </span>
                        <h3 class="text-xs font-extrabold">خلاصه سفارش</h3>
                        <span class="mr-auto text-[10px] text-slate-400">{{ \App\Support\Fmt::toFa($units) }} کالا از {{ \App\Support\Fmt::toFa($shopGroups->count()) }} فروشگاه</span>
                    </div>

                    <div class="mt-3 space-y-4">
                        @foreach ($shopGroups as $group)
                            @php $shopId = (int) $group['shop']->id; @endphp
                            <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800" wire:key="ck-group-{{ $shopId }}">

                                {{-- shop header --}}
                                <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/70 px-3 py-2.5 dark:border-slate-800 dark:bg-slate-800/40">
                                    <span class="grid size-7 shrink-0 place-items-center rounded-lg bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                                        <x-icon name="store" class="size-3.5" />
                                    </span>
                                    <span class="min-w-0 truncate text-[11px] font-extrabold">{{ $group['shop']?->name ?? 'کالاهای بدون فروشگاه' }}</span>
                                    @if ($this->discountShop && (int) $this->discountShop->id === $shopId && $liveDiscount[0] > 0)
                                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[8.5px] font-extrabold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300" title="کد تخفیف فقط روی محصولات همین فروشگاه اعمال می‌شود">
                                            <x-icon name="tag" class="size-2.5" />
                                            کد تخفیف روی این فروشگاه − {{ \App\Support\Fmt::money($liveDiscount[0]) }}
                                        </span>
                                    @endif
                                    <span class="mr-auto shrink-0 text-[9.5px] font-bold text-slate-400">جمع {{ \App\Support\Fmt::money($group['subtotal']) }}</span>
                                </div>

                                {{-- items with thumbnails --}}
                                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach ($group['items'] as $item)
                                        <div class="flex items-center gap-2.5 px-3 py-2.5" wire:key="ck-item-{{ $item->id }}">
                                            <a href="{{ $item->product->publicUrl() }}" wire:navigate
                                               class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                                @if ($item->product->mainImage)
                                                    <img src="{{ $item->product->mainImage->url() }}" alt="{{ $item->product->name }}" loading="lazy" class="size-full object-cover">
                                                @else
                                                    <x-icon name="package" class="size-5 text-slate-300 dark:text-slate-600" />
                                                @endif
                                            </a>
                                            <div class="min-w-0 flex-1">
                                                <div class="truncate text-[11px] font-bold">{{ $item->product->name }}</div>
                                                @if ($item->variant_id > 0)
                                                    <div class="mt-0.5 truncate text-[9px] font-bold text-slate-500 dark:text-slate-400">{{ $item->variant?->label() ?? 'تنوع نامعتبر' }}</div>
                                                @endif
                                                <div class="text-[9px] text-slate-400">{{ \App\Support\Fmt::money($item->unitPrice()) }} × {{ \App\Support\Fmt::toFa($item->quantity) }}</div>
                                            </div>
                                            <div class="shrink-0 text-[11px] font-extrabold text-primary-600 dark:text-primary-400">{{ \App\Support\Fmt::money($item->lineTotal()) }}</div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- ── حامل این فروشگاه (اجباری برای کالای فیزیکی) ── --}}
                                @if ($group['needsCarrier'])
                                    @php
                                        $pickedCarrier      = $group['carriers']->firstWhere('id', (int) ($carrierPicks[$shopId] ?? 0));
                                        $pickedIsUserSelect = $pickedCarrier && $pickedCarrier->delivery_time_type === \App\Models\Carrier::TIME_USER_SELECT;

                                        // v0.13.10: the carrier radio is LIVE (wire:model.live), so the
                                        // server ALWAYS knows the pick — the day-picker is rendered for
                                        // the picked carrier only, server-side. No pre-rendered hidden
                                        // sections, no client/server divergence for the morph to trip on.
                                        $pickedUcDays = ($pickedIsUserSelect && $pickedCarrier)
                                            ? $pickedCarrier->selectableDeliveryDays()
                                            : [];
                                    @endphp
                                    {{-- مقطع «حامل + روز تحویل» هر فروشگاه — نسخهٔ ضدشکنجه (v0.13.10):
                                          سه بار روی گوشیِ واقعی گزارش شد که با انتخاب حامل، محتوای
                                          پایین صفحه (تخفیف/درگاه/جمع/دکمهٔ پرداخت) محو می‌شود؛ منشأ آن
                                          ترکیبِ تجربیِ zero-roundtrip بود: dwrap های از پیش رندرشده +
                                          x-show + style="display:none" سرسری + dispatch تغییرِ مصنوعی.
                                          این‌ها divergence بین DOM سمت مرورگر و HTML سرور می‌سازند و
                                          morph را وادار به جابه‌جا کردن نودها می‌کنند (در v0.13.9 هم
                                          ثابت شد نودها جابه‌جا می‌شوند). پس حامل حالا LIVE است: سرور
                                          همیشه انتخاب را می‌داند و بخش روز فقط برای همان حامل، سمت
                                          سرور رندر می‌شود. هیچ state موازی، هیچ x-show رقص، هیچ
                                          رویداد مصنوعی — مسیرِ خسته‌کننده و بادوام. --}}
                                    <div class="border-t border-slate-100 bg-slate-50/60 p-3 dark:border-slate-800 dark:bg-slate-800/30">
                                        <p class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold">
                                            <x-icon name="truck" class="size-3.5 text-primary-500" />
                                            روش ارسال این فروشگاه
                                            <span class="text-rose-500">*</span>
                                        </p>

                                        @if ($group['carriers']->isEmpty())
                                            @php
                                                $zoneBlocked = $group['allCarriers']->contains(
                                                    fn (\App\Models\Carrier $c) => ! $c->isPayOnDelivery() && $c->hasTariffs(),
                                                );
                                            @endphp
                                            <div class="rounded-xl border border-amber-200 bg-amber-50/80 px-3 py-2 text-[10px] font-bold leading-5 text-amber-700 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300">
                                                @if ($zoneBlocked)
                                                    روش‌های ارسال این فروشگاه برای آدرس شما فعال نیست؛ از فروشنده بخواهید تعرفهٔ منطقهٔ شما (درون/بیرون استانی) را ثبت کند.
                                                @elseif ($group['allCarriers']->isNotEmpty())
                                                    روش‌های ارسال این فروشگاه هنوز تعرفه وزنی ثبت نکرده‌اند؛ تا اطلاع فروشنده امکان خرید ارسالی نیست.
                                                @else
                                                    این فروشگاه هنوز روش ارسالی ثبت نکرده است؛ تا اطلاع فروشنده امکان خرید حضوری‌نیست.
                                                @endif
                                            </div>
                                        @else
                                            <div class="space-y-2" role="radiogroup" aria-label="روش ارسال {{ $group['shop']?->name }}">
                                                @foreach ($group['carriers'] as $carrier)
                                                    @php
                                                        $cost     = $carrier->costFor((int) $group['subtotal'], (int) $group['weight'], $this->isInProvince($carrier));
                                                        $postpaid = $carrier->isPayOnDelivery();
                                                        $inProv   = $this->isInProvince($carrier);
                                                    @endphp
                                                    {{-- هایلایت انتخاب = peer-checked خالص (فوری)؛
                                                         wire:model.live حامل را همان لحظه به سرور می‌رساند
                                                         تا بخش روزِ همان حامل سمت سرور رندر شود. --}}
                                                    {{-- relative روی label = لنگرِ absolute برای input سر-only؛
                                                         بدون آن، input به shell لنگر می‌شود و جعبهٔ مطلقش در
                                                         انتهای محتوای main می‌افتد — فکوس روی آن (تپِ واقعی
                                                         گوشی!) کل shell را اسکرول می‌کند و کل اپ بالا می‌پرد. --}}
                                                    <label class="relative block cursor-pointer" wire:key="carrier-{{ $carrier->id }}">
                                                        <input type="radio" name="carrier-{{ $shopId }}" value="{{ $carrier->id }}"
                                                               wire:model.live="carrierPicks.{{ $shopId }}" class="peer sr-only"
                                                               data-cost="{{ $cost }}" data-postpaid="{{ $postpaid ? 1 : 0 }}">
                                                        <span class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-2.5 transition hover:border-primary-300 peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:ring-1 peer-checked:ring-primary-500/30 dark:border-slate-700 dark:bg-slate-900 dark:peer-checked:border-primary-600 dark:peer-checked:bg-primary-950/40">
                                                        <span class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800">
                                                            @if ($carrier->image_path)
                                                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($carrier->image_path) }}" alt="{{ $carrier->title }}" class="size-full object-cover">
                                                            @else
                                                                <x-icon name="truck" class="size-4" />
                                                            @endif
                                                        </span>
                                                        <span class="min-w-0 flex-1">
                                                            <span class="block truncate text-[11px] font-extrabold">{{ $carrier->title }}</span>
                                                            <span class="mt-0.5 block truncate text-[9px] font-bold text-slate-400">{{ $carrier->deliveryRangeLabel() }} · {{ $carrier->waiting_time ?: $carrier->coverageLabel() }}</span>
                                                            <span class="mt-0.5 inline-flex items-center gap-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-[8px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                                <x-icon name="map-pin" class="size-2.5" />
                                                                {{ $inProv ? 'درون استانی' : 'بیرون استانی' }}
                                                            </span>
                                                        </span>
                                                        <span class="shrink-0 text-left">
                                                            @if ($postpaid)
                                                                <span class="block rounded-full bg-amber-100 px-2 py-0.5 text-[9px] font-extrabold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">پس‌کرایه</span>
                                                                <span class="mt-0.5 block text-[8.5px] font-bold text-slate-400">پرداخت هنگام تحویل</span>
                                                            @elseif ($cost < 1)
                                                                <span class="block rounded-full bg-emerald-100 px-2 py-0.5 text-[9px] font-extrabold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">رایگان</span>
                                                            @else
                                                                <span class="block text-[11px] font-extrabold text-slate-600 dark:text-slate-300">{{ \App\Support\Fmt::moneyShort($cost) }}</span>
                                                                <span class="block text-[8.5px] font-bold text-slate-400">تومان</span>
                                                            @endif
                                                        </span>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('carrierPicks.'.$shopId) <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror

                                            @if (count($pickedUcDays) > 0)
                                                {{-- ── انتخاب روز تحویل (نوع بازه: انتخاب توسط کاربر) ──
                                                     v0.13.10: فقط برای «همان حاملی که سرور انتخابش را
                                                     می‌داند» رندر می‌شود — چون حامل LIVE است، بعد از
                                                     هر تغییر حامل یک re-render کوچک همین بخش را عوض
                                                     می‌کند. بدون x-show موازی، بدون style دستی، بدون
                                                     dispatch مصنوعی؛ هر چیزی که DOM و سرور ببینند
                                                     یکی است و morph چیزی برای اشتباه کردن ندارد. --}}
                                                <div class="mt-3 rounded-xl border border-primary-200 bg-primary-50/60 p-2.5 dark:border-primary-900 dark:bg-primary-950/25"
                                                     wire:key="dwrap-{{ $shopId }}-{{ (int) $pickedCarrier->id }}">
                                                    <p class="mb-2 flex flex-wrap items-center gap-1.5 text-[10.5px] font-extrabold">
                                                        <x-icon name="calendar" class="size-3.5 text-primary-500" />
                                                        روز تحویل را انتخاب کنید
                                                        <span class="text-rose-500">*</span>
                                                        <span class="ms-auto text-[9px] font-bold text-slate-400">از {{ \App\Support\Fmt::toFa($pickedCarrier->start_days_after_order) }} روز بعد — {{ \App\Support\Fmt::toFa(count($pickedUcDays)) }} روز</span>
                                                    </p>

                                                    {{-- انتخاب روز = deferral بی‌خطر: چیپ با peer-checked فوراً
                                                         هایلایت می‌شود و مقدار با اولین درخواست بعدی (درگاه،
                                                         تغییر حامل، ثبت سفارش) همگام می‌شود — این تعامل هیچ
                                                         تغییر ساختاری در DOM نمی‌سازد، پس هیچ ریسکی برای
                                                         morph ندارد. اعتبارسنجی نهایی سمت سرور است. --}}
                                                    <div class="scroll-slim flex gap-1.5 overflow-x-auto pb-1" role="radiogroup" aria-label="روز تحویل سفارش {{ $group['shop']?->name }}">
                                                        @foreach ($pickedUcDays as $day)
                                                            <label class="relative block shrink-0 cursor-pointer"
                                                                   title="{{ $day['long'] }}{{ !empty($day['holiday']) ? ' — '.$day['holiday'] : '' }}" wire:key="dday-{{ $shopId }}-{{ $day['date'] }}">
                                                                <input type="radio" name="delivery-day-{{ $shopId }}" value="{{ $day['date'] }}"
                                                                       wire:model="deliveryPicks.{{ $shopId }}" class="peer sr-only"
                                                                       aria-label="{{ $day['long'] }}">
                                                                <span class="flex flex-col items-center gap-0.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-center transition hover:border-primary-300 peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:ring-1 peer-checked:ring-primary-500/30 dark:border-slate-700 dark:bg-slate-900 dark:peer-checked:border-primary-600 dark:peer-checked:bg-primary-950/40">
                                                                    <span class="text-[10px] font-extrabold text-slate-600 dark:text-slate-300">{{ $day['weekday'] }}</span>
                                                                    <span class="text-[9px] font-bold text-slate-400">{{ $day['jdate'] }}</span>
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>

                                                    @error('deliveryPicks.'.$shopId) <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                                </div>
                                            @elseif ($pickedCarrier && ! $pickedIsUserSelect)
                                                {{-- حامل بدون «انتخاب روز» — بازهٔ زمانی خودش را همان کارت حامل نشان می‌دهد؛
                                                     فقط یک نشانگر کوتاه تا کاربر بداند ورودی روزی لازم نیست. --}}
                                                <p class="mt-2.5 flex items-center gap-1.5 rounded-xl bg-slate-100/80 px-2.5 py-2 text-[9.5px] font-bold text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                                                    <x-icon name="calendar" class="size-3.5 text-slate-400" />
                                                    انتقال روز تحویل طبق بازهٔ اعلام‌شدهٔ این روش ارسال انجام می‌شود.
                                                </p>
                                            @endif

                                            {{-- بارگذاری نرم هنگام تغییر حامل (درخواست live): چیپِ حامل فوراً
                                                 با peer-checked هایلایت می‌شود و این ردیف، roundtrip کوتاه
                                                 را پوشش می‌دهد تا صفحه «خالی» به نظر نرسد. --}}
                                            <div wire:loading wire:target="carrierPicks.{{ $shopId }}"
                                                 class="mt-3 flex items-center gap-2 rounded-xl border border-primary-200/70 bg-primary-50/40 px-3 py-2.5 text-[10px] font-bold text-primary-600 dark:border-primary-900 dark:bg-primary-950/20 dark:text-primary-300">
                                                <span class="size-3.5 animate-spin rounded-full border-2 border-primary-300 border-t-primary-600" aria-hidden="true"></span>
                                                در حال به‌روزرسانی روزهای تحویل…
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- ── کد تخفیف ── (wire:key پایدار = لنگر morph؛ اگر morphی در بدترین حالت به‌هم بریزد، این بلوک‌ها با کلید پیدا می‌شوند نه با موقعیت) --}}
                    <div wire:key="ck-discount" class="mt-4 rounded-2xl border border-dashed border-slate-300 p-3 dark:border-slate-700">
                        <p class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold">
                            <x-icon name="tag" class="size-3.5 text-primary-500" />
                            کد تخفیف
                        </p>

                        @if ($discountApplied)
                            <div class="flex items-center justify-between gap-2 rounded-xl border border-emerald-200 bg-emerald-50/80 px-3 py-2.5 dark:border-emerald-900 dark:bg-emerald-950/30">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="grid size-7 shrink-0 place-items-center rounded-full bg-emerald-500 text-white">
                                        <x-icon name="check" class="size-3.5" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-mono text-[11px] font-extrabold text-emerald-700 dark:text-emerald-300" dir="ltr">{{ mb_strtoupper(trim($discountCode)) }}</span>
                                        <span class="block text-[9.5px] font-bold text-emerald-600 dark:text-emerald-400">{{ \App\Support\Fmt::money($discountAmount) }} تومان تخفیف اعمال شد</span>
                                        <span class="mt-0.5 block text-[9px] font-bold text-emerald-600/80 dark:text-emerald-400/80">
                                            @if ($this->discountShop)
                                                فقط برای محصولات فروشگاه «{{ $this->discountShop->name }}»
                                            @else
                                                فقط برای محصولات همان فروشگاهی که این کد را ساخته است
                                            @endif
                                        </span>
                                    </span>
                                </span>
                                <button type="button" wire:click="removeDiscount"
                                        class="grid size-8 shrink-0 place-items-center rounded-lg text-emerald-600 transition hover:bg-emerald-100 hover:text-rose-500 dark:hover:bg-emerald-900/40"
                                        aria-label="حذف کد تخفیف">
                                    <x-icon name="x" class="size-4" />
                                </button>
                            </div>
                        @else
                            <div class="flex gap-2">
                                <input type="text" wire:model="discountCode" dir="ltr" maxlength="60" placeholder="SALE2025"
                                       class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 font-mono text-xs font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                                <button type="button" wire:click="applyDiscount" wire:loading.attr="disabled" wire:target="applyDiscount"
                                        class="shrink-0 rounded-xl bg-slate-900 px-4 py-2.5 text-[11px] font-extrabold text-white transition hover:bg-slate-800 active:scale-95 disabled:opacity-50 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                                    اعمال
                                </button>
                            </div>
                            @error('discountCode') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            @if (! $errors->has('discountCode') && $discountMessage !== '')
                                <p class="mt-1.5 text-[10px] font-bold text-slate-400">{{ $discountMessage }}</p>
                            @endif
                        @endif
                    </div>

                    {{-- ── درگاه پرداخت (v0.29.0 redesign: مرتب، آیکون رسمی هر درگاه، اسم کامل) ── --}}
                    @if (count($gateways) > 0)
                        <div wire:key="ck-gateways" class="mt-4">
                            <p class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold">
                                <x-icon name="credit-card" class="size-3.5 text-primary-500" />
                                درگاه پرداخت
                                <span class="text-rose-500">*</span>
                                <span class="ms-auto rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                    {{ \App\Support\Fmt::fa(count($gateways)) }} درگاه فعال
                                </span>
                            </p>

                            <div class="space-y-2.5" role="radiogroup" aria-label="انتخاب درگاه پرداخت">
                                @foreach ($gateways as $key => $label)
                                    @php $gwIsDefault = $key === \App\Support\Payments\PaymentService::activeGateway(); @endphp
                                    <label wire:key="gw-{{ $key }}"
                                           class="group flex cursor-pointer items-center gap-3 rounded-2xl border-2 border-slate-200 bg-white p-3 transition hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50/60 has-[:checked]:shadow-lg has-[:checked]:shadow-primary-600/10 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-primary-700 dark:has-[:checked]:border-primary-600 dark:has-[:checked]:bg-primary-950/40">
                                        <input type="radio" name="gateway" value="{{ $key }}" wire:model="gateway" class="sr-only"
                                               aria-label="پرداخت با {{ $label }}">

                                        {{-- آیکون رسمی/سفارشی درگاه --}}
                                        <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-100 bg-white p-1 shadow-sm transition group-has-[:checked]:border-primary-200 dark:border-slate-800 dark:bg-slate-800 dark:group-has-[:checked]:border-primary-800">
                                            @include('partials.gateway-logo', ['gateway' => $key])
                                        </span>

                                        <span class="min-w-0 flex-1">
                                            <span class="flex items-center gap-2">
                                                <span class="truncate text-[12.5px] font-extrabold">{{ $label }}</span>
                                                @if ($gwIsDefault)
                                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-primary-600 px-2 py-0.5 text-[8.5px] font-extrabold text-white">
                                                        <x-icon name="star" class="size-2.5" />
                                                        پیش‌فرض
                                                    </span>
                                                @endif
                                            </span>
                                            <span class="mt-1 flex items-center gap-1 text-[9.5px] font-bold text-slate-400 dark:text-slate-500">
                                                <x-icon name="shield" class="size-3 shrink-0" />
                                                پرداخت امن آنلاین — انتقال به سایت بانک
                                            </span>
                                        </span>

                                        {{-- radio indicator --}}
                                        <span class="grid size-5 shrink-0 place-items-center rounded-full border-2 border-slate-300 bg-white transition group-has-[:checked]:border-primary-600 group-has-[:checked]:bg-primary-600 dark:border-slate-600"
                                              aria-hidden="true">
                                            <x-icon name="check" class="size-3 text-white opacity-0 transition group-has-[:checked]:opacity-100" />
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ── درگاه‌های اقساطی (v0.30.0 — بخش کاملاً جدا: خرید حالا، پرداخت بعداً) ── --}}
                    @if (count($bnplGateways) > 0)
                        <div wire:key="ck-bnpl" class="mt-4">
                            <p class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold">
                                <x-icon name="calendar-clock" class="size-3.5 text-violet-500" />
                                پرداخت اقساطی — خرید حالا، پرداخت بعداً
                                <span class="ms-auto rounded-full bg-violet-100 px-2 py-0.5 text-[9px] font-extrabold text-violet-700 dark:bg-violet-950/60 dark:text-violet-300">
                                    {{ \App\Support\Fmt::fa(count($bnplGateways)) }} روش فعال
                                </span>
                            </p>
                            <p class="mb-2.5 rounded-xl bg-violet-50 px-3 py-2 text-[9.5px] font-semibold leading-4 text-violet-600 dark:bg-violet-950/30 dark:text-violet-300">
                                قسط اول هنگام خرید از درگاه پرداخت می‌شود و باقی اقساط را در پنل خود درگاه می‌پردازید؛ کارمزدِ اقساطِ هر فروشگاه (در صورت تعیین) به جمع کالاها اضافه می‌شود و شامل هزینهٔ ارسال نیست.
                            </p>

                            <div class="space-y-2.5" role="radiogroup" aria-label="انتخاب روش پرداخت اقساطی">
                                @foreach ($bnplGateways as $key => $label)
                                    @php
                                        $bnplInstallments = \App\Support\Payments\PaymentService::bnplInstallments($key);
                                        $bnplFee = (int) ($bnplFees[$key] ?? 0);
                                    @endphp
                                    <label wire:key="bnpl-{{ $key }}"
                                           class="group flex cursor-pointer items-center gap-3 rounded-2xl border-2 border-slate-200 bg-white p-3 transition hover:border-violet-300 has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50/60 has-[:checked]:shadow-lg has-[:checked]:shadow-violet-600/10 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-violet-700 dark:has-[:checked]:border-violet-600 dark:has-[:checked]:bg-violet-950/40">
                                        <input type="radio" name="gateway" value="{{ $key }}" wire:model="gateway" class="sr-only"
                                               aria-label="پرداخت اقساطی با {{ $label }}">

                                        <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-100 bg-white p-1 shadow-sm transition group-has-[:checked]:border-violet-200 dark:border-slate-800 dark:bg-slate-800 dark:group-has-[:checked]:border-violet-800">
                                            @include('partials.gateway-logo', ['gateway' => $key])
                                        </span>

                                        <span class="min-w-0 flex-1">
                                            <span class="flex items-center gap-2">
                                                <span class="truncate text-[12.5px] font-extrabold">{{ $label }}</span>
                                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-violet-100 px-2 py-0.5 text-[8.5px] font-extrabold text-violet-700 dark:bg-violet-950/60 dark:text-violet-300">
                                                    {{ \App\Support\Fmt::fa($bnplInstallments) }} قسط
                                                </span>
                                            </span>
                                            <span class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[9.5px] font-bold">
                                                <span class="flex items-center gap-1 text-slate-400 dark:text-slate-500">
                                                    <x-icon name="calendar" class="size-3 shrink-0" />
                                                    قسط اول در خرید — باقی اقساط در پنل {{ $label }}
                                                </span>
                                                @if ($bnplFee > 0)
                                                    <span class="flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300">
                                                        + کارمزد اقساط {{ \App\Support\Fmt::money($bnplFee) }}
                                                    </span>
                                                @else
                                                    <span class="flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                        بدون کارمزد
                                                    </span>
                                                @endif
                                            </span>
                                        </span>

                                        <span class="grid size-5 shrink-0 place-items-center rounded-full border-2 border-slate-300 bg-white transition group-has-[:checked]:border-violet-600 group-has-[:checked]:bg-violet-600 dark:border-slate-600"
                                              aria-hidden="true">
                                            <x-icon name="check" class="size-3 text-white opacity-0 transition group-has-[:checked]:opacity-100" />
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ── totals ── --}}
                    <div wire:key="ck-totals" class="mt-4 space-y-1.5 border-t border-dashed border-slate-200 pt-3 text-xs dark:border-slate-700">
                        <div class="flex items-center justify-between text-[10.5px] text-slate-400">
                            <span>جمع کالاها ({{ \App\Support\Fmt::toFa($units) }})</span>
                            <span class="font-bold">{{ \App\Support\Fmt::money($total) }}</span>
                        </div>
                        {{-- کارمزد اقساط (v0.30.0): فقط وقتی درگاه اقساطی انتخاب شده — روی جمع کالاها، بدون ارسال --}}
                        <div class="flex items-center justify-between text-[10.5px] text-violet-600 dark:text-violet-400" x-show="bnplFee > 0" @if (! isset($bnplFees[$gateway])) style="display:none" @endif>
                            <span class="flex items-center gap-1">
                                <x-icon name="calendar-clock" class="size-3 shrink-0" />
                                کارمزد خرید اقساطی
                            </span>
                            <span class="font-bold" x-text="'+ ' + fa(bnplFee) + ' تومان'"></span>
                        </div>
                        <div class="flex items-center justify-between text-[10.5px] text-slate-400">
                            <span>هزینه ارسال</span>
                            <span class="font-bold" :class="ship > 0 ? 'text-slate-600 dark:text-slate-300' : 'text-emerald-600 dark:text-emerald-400'"
                                  x-text="ship > 0 ? fa(ship) + ' تومان' : 'رایگان'">{{ $shippingCost > 0 ? \App\Support\Fmt::money($shippingCost) : 'رایگان' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-amber-600 dark:text-amber-400" x-show="post" @if (! $hasPayOnDelivery) style="display:none" @endif>
                            <span>یکی از حامل‌ها پس‌کرایه است</span>
                            <span>پرداخت هنگام تحویل</span>
                        </div>
                        @if ($discountApplied && $liveDiscount[0] > 0)
                            <div class="flex items-center justify-between text-[10.5px] text-emerald-600 dark:text-emerald-400">
                                <span class="flex min-w-0 items-center gap-1">
                                    <x-icon name="tag" class="size-3 shrink-0" />
                                    <span class="truncate">تخفیف فروشگاه «{{ $this->discountShop?->name ?? '—' }}» ({{ mb_strtoupper(trim($discountCode)) }})</span>
                                </span>
                                <span class="shrink-0 font-extrabold">− {{ \App\Support\Fmt::money($liveDiscount[0]) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between border-t border-slate-100 pt-2 dark:border-slate-800" aria-live="polite">
                            <span class="font-bold text-slate-500 dark:text-slate-400">مبلغ قابل پرداخت</span>
                            <span class="text-sm font-extrabold text-primary-600 dark:text-primary-400" x-text="fa(payable) + ' تومان'">{{ \App\Support\Fmt::money($payable) }}</span>
                        </div>
                    </div>

                    {{-- ── pay button ── --}}
                    <button type="submit" wire:key="ck-paybtn" wire:loading.attr="disabled" wire:target="placeOrder"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-600 py-3.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-[0.98] disabled:opacity-60">
                        <span wire:loading.remove wire:target="placeOrder" class="flex items-center gap-2">
                            <x-icon name="credit-card" class="size-4" />
                            پرداخت <span x-text="fa(payable) + ' تومان'">{{ \App\Support\Fmt::money($payable) }}</span>
                        </span>
                        <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                            <x-icon name="rotate" class="size-4 animate-spin" />
                            در حال اتصال به درگاه…
                        </span>
                    </button>

                    <p class="mt-2.5 flex items-center justify-center gap-1 text-center text-[9px] text-slate-400">
                        <x-icon name="shield" class="size-3" />
                        پرداخت در درگاه امن بانکی انجام می‌شود.
                    </p>
                </div>
            </form>

            <div class="pb-2"></div>
        @endif
    </div>

    {{-- ═══════════════ تغییر آدرس — انتخاب از دفترچه + افزودن آدرس (73) ═══════════════ --}}

    {{-- ═══════════════ لیست آدرس‌ها — مودال تمام‌صفحه روی موبایل (73) ═══════════════ --}}
    <x-modal-panel open="$wire.showAddressModal" maxWidth="full" wire:key="ck-addr-list"
               labelledby="addr-modal-title"
               closeJs="__ipOpen = false; $wire.closeAddressModal()">

    <x-slot:header>
        <div class="flex items-center justify-between px-4 py-3">
            <h2 id="addr-modal-title" class="flex items-center gap-2 text-xs font-extrabold">
                <span class="grid size-8 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                    <x-icon name="map-pin" class="size-4" />
                </span>
                آدرس‌های من
            </h2>
            <button type="button" class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-800"
                    x-on:click="__ipOpen = false; $wire.closeAddressModal()" aria-label="بستن">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    </x-slot:header>

    <div class="p-4">
        {{-- ── دفترچه خالی ── --}}
        @if ($addresses->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                    <x-icon name="map-pin" class="size-7 text-slate-300 dark:text-slate-600" />
                </span>
                <p class="mt-3 text-xs font-extrabold">شما هنوز آدرسی اضافه نکردید</p>
                <p class="mt-1 text-[10px] leading-5 text-slate-400">اولین نشانی را ثبت کنید تا همان‌جا پیش‌فرض شود.</p>
                <button type="button" wire:click="openAddAddress"
                        class="mx-auto mt-4 flex w-full max-w-56 items-center justify-center gap-1.5 rounded-xl bg-primary-600 py-3 text-xs font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
                    <x-icon name="plus" class="size-4" />
                    افزودن آدرس
                </button>
            </div>
        @else
            {{-- ── لیست آدرس‌ها: انتخاب + ویرایش ── --}}
            <div class="space-y-2.5">
                @foreach ($addresses as $address)
                    <div class="flex items-stretch gap-2" wire:key="pick-{{ $address->id }}">
                        <button type="button" wire:click="pickAddress({{ $address->id }})"
                                class="flex min-w-0 flex-1 items-start gap-2.5 rounded-2xl border p-3 text-right transition {{ (int) $selectedAddressId === (int) $address->id
                                    ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                                    : 'border-slate-200 bg-white hover:border-primary-300 dark:border-slate-700 dark:bg-slate-900' }}">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl {{ (int) $selectedAddressId === (int) $address->id
                                ? 'bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400'
                                : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                <x-icon name="{{ $address->title === 'محل کار' ? 'store' : 'home' }}" class="size-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-1.5">
                                    <span class="truncate text-[11px] font-extrabold">{{ $address->title ?: 'بدون عنوان' }}</span>
                                    @if ($address->is_default)
                                        <span class="rounded-full bg-primary-100 px-1.5 py-0.5 text-[8px] font-extrabold text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">پیش‌فرض</span>
                                    @endif
                                </span>
                                <span class="mt-0.5 block text-[10px] font-bold leading-5 text-slate-500 dark:text-slate-400">{{ $address->fullLabel() }}</span>
                                @if ($address->recipient_name)
                                    <span class="mt-0.5 block text-[9px] text-slate-400">گیرنده: {{ $address->recipient_name }} @if($address->phone) · {{ \App\Support\Fmt::toFa($address->phone) }} @endif</span>
                                @endif
                            </span>
                            @if ((int) $selectedAddressId === (int) $address->id)
                                <span class="grid size-5 shrink-0 place-items-center rounded-full bg-primary-600 text-white">
                                    <x-icon name="check" class="size-3" />
                                </span>
                            @endif
                        </button>
                        <button type="button" wire:click="openEditAddress({{ $address->id }})" aria-label="ویرایش {{ $address->title ?: 'آدرس' }}"
                                class="grid w-11 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-900 dark:hover:text-primary-400">
                            <x-icon name="edit" class="size-4" />
                        </button>
                    </div>
                @endforeach
            </div>

            <button type="button" wire:click="openAddAddress"
                    class="mt-4 flex w-full items-center justify-center gap-1.5 rounded-xl border border-dashed border-primary-300 bg-primary-50/50 py-3 text-[11px] font-extrabold text-primary-600 transition hover:bg-primary-100/60 dark:border-primary-800 dark:bg-primary-950/20 dark:text-primary-400 dark:hover:bg-primary-950/40">
                <x-icon name="plus" class="size-3.5" />
                افزودن آدرس
            </button>
        @endif
    </div>
</x-modal-panel>

    {{-- ═══════════════ افزودن / ویرایش آدرس — همان فرم «آدرس‌های من» (73) ═══════════════ --}}
    <x-modal-panel open="$wire.showAddressForm" maxWidth="md" zClass="z-[80]" wire:key="ck-addr-form"
               labelledby="addr-form-title"
               closeJs="__ipOpen = false; $wire.backToAddressList()">

    <x-slot:header>
        <div class="flex items-center justify-between px-4 py-3">
            <h2 id="addr-form-title" class="flex items-center gap-2 text-xs font-extrabold">
                <span class="grid size-8 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                    <x-icon name="{{ $addressEditId ? 'edit' : 'plus' }}" class="size-4" />
                </span>
                {{ $addressEditId ? 'ویرایش آدرس' : 'آدرس جدید' }}
            </h2>
            <button type="button" class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-rose-500 dark:hover:bg-slate-800"
                    x-on:click="__ipOpen = false; $wire.backToAddressList()" aria-label="بستن">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    </x-slot:header>

    <form wire:submit="saveAddressForm" class="space-y-3 p-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">عنوان (اختیاری)</label>
                <input type="text" wire:model="newTitle" maxlength="60" placeholder="خانه / محل کار"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">نام گیرنده <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="newRecipient" maxlength="80" placeholder="مثلاً: سارا محمدی"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('newRecipient') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">شماره همراه <span class="text-rose-500">*</span></label>
            <input type="tel" wire:model="newPhone" inputmode="tel" dir="ltr" maxlength="14" placeholder="09123456789"
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-right text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
            @error('newPhone') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">استان <span class="text-rose-500">*</span></label>
                <select wire:model.live="newProvinceId"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    <option value="0">انتخاب استان…</option>
                    @foreach ($newProvinces as $province)
                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                    @endforeach
                </select>
                @error('newProvinceId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">شهر <span class="text-rose-500">*</span></label>
                <select wire:model.live="newCityId"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    <option value="0">انتخاب شهر…</option>
                    @foreach ($newCities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @endforeach
                </select>
                @error('newCityId') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">نشانی کامل <span class="text-rose-500">*</span></label>
            <textarea wire:model="newAddress" rows="3" maxlength="500" placeholder="خیابان، کوچه، نام ساختمان…"
                      class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs leading-5 outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800"></textarea>
            @error('newAddress') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">کد پستی</label>
                <input type="text" wire:model="newPostal" inputmode="numeric" dir="ltr" maxlength="10" placeholder="—"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-right text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                @error('newPostal') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">پلاک</label>
                <input type="text" wire:model="newPlaque" inputmode="numeric" maxlength="20" placeholder="—"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-bold text-slate-500 dark:text-slate-400">واحد</label>
                <input type="text" wire:model="newUnit" inputmode="numeric" maxlength="20" placeholder="—"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
            </div>
        </div>

        <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
            <input type="checkbox" wire:model="newIsDefault" class="size-4 border-slate-300 text-primary-600 focus:ring-primary-500/30 dark:border-slate-600">
            <span class="text-[11px] font-bold">این آدرس، پیش‌فرض من باشد</span>
        </label>

        <div class="flex gap-2 pt-1">
            <button type="button" wire:click="backToAddressList"
                    class="flex-1 rounded-xl border border-slate-200 bg-white py-3 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                انصراف
            </button>
            <button type="submit" wire:loading.attr="disabled" wire:target="saveAddressForm"
                    class="flex-1 rounded-xl bg-primary-600 py-3 text-xs font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
                ذخیره آدرس
            </button>
        </div>
    </form>
</x-modal-panel>
</div>
