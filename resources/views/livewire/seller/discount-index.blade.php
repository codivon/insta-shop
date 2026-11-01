<div class="ip-fade-up">
    {{-- ───────── Sticky page header ───────── --}}
    <div class="sticky top-0 z-20 border-b border-slate-200/80 bg-slate-50/95 px-4 py-3 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-fuchsia-500 to-rose-600 text-white shadow-lg shadow-rose-500/30">
                    <x-icon name="tag" class="size-5" />
                </span>
                <div>
                    <div class="text-sm font-extrabold leading-5">کدهای تخفیف</div>
                    <div class="text-[11px] text-slate-400">کدهای خرید فروشگاه شما</div>
                </div>
            </div>
            <a href="{{ route('seller.discounts.create') }}" wire:navigate
               class="flex items-center gap-1.5 rounded-2xl bg-primary-600 px-3.5 py-2.5 text-[11px] font-extrabold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95">
                <x-icon name="plus" class="size-4" />
                تخفیف جدید
            </a>
        </div>
    </div>

    <div class="px-4 pt-4">
        @if (session('discount-flash'))
            <div class="mb-3 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-3.5 py-2.5 text-[11px] font-bold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">
                <x-icon name="circle-check" class="size-4" />
                {{ session('discount-flash') }}
            </div>
        @endif

        {{-- stats --}}
        <div class="grid grid-cols-3 gap-2.5">
            @foreach ([
                [$stats['total'], 'کد تخفیف', 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'],
                [$stats['published'], 'منتشرشده', 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400'],
                [$stats['used'], 'بار استفاده', 'bg-fuchsia-100 text-fuchsia-600 dark:bg-fuchsia-950/50 dark:text-fuchsia-400'],
            ] as $st)
                <div class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-base font-black">{{ \App\Support\Fmt::toFa($st[0]) }}</div>
                    <div class="mt-0.5 text-[9px] font-bold text-slate-400">{{ $st[1] }}</div>
                </div>
            @endforeach
        </div>

        {{-- list --}}
        <div class="mt-4 space-y-3">
            @forelse ($discounts as $discount)
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="discount-{{ $discount->id }}">
                    <div class="flex items-start justify-between gap-2 p-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="truncate text-xs font-extrabold">{{ $discount->title }}</span>
                                @if ($discount->published)
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[8.5px] font-black text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">منتشرشده</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[8.5px] font-black text-slate-500 dark:bg-slate-800">پیش‌نویس</span>
                                @endif
                                @if (! $discount->inWindow())
                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[8.5px] font-black text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">خارج از بازه</span>
                                @endif
                            </div>

                            <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $discount->code }}'); this.dispatchEvent(new CustomEvent('panel-toast', { bubbles: true, detail: { message: 'کد کپی شد' } }))"
                                    class="mt-1.5 inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-[11px] font-black text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300"
                                    title="کپی کد" dir="ltr">
                                {{ $discount->code }}
                                <x-icon name="copy" class="size-3" />
                            </button>

                            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                <span class="rounded-full bg-rose-50 px-2 py-0.5 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">{{ $discount->valueLabel() }} تخفیف</span>
                                <span>{{ $discount->windowLabel() }}</span>
                                <span>·</span>
                                <span>استفاده: {{ \App\Support\Fmt::toFa($discount->used_count) }}{{ $discount->quantity > 0 ? ' از '.\App\Support\Fmt::toFa($discount->quantity) : '' }}</span>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-0.5">
                            <button type="button" wire:click="togglePublished({{ $discount->id }})"
                                    class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-800"
                                    aria-label="{{ $discount->published ? 'لغو انتشار' : 'انتشار' }}">
                                <x-icon name="{{ $discount->published ? 'eye' : 'eye-off' }}" class="size-4" />
                            </button>
                            <a href="{{ route('seller.discounts.edit', $discount) }}" wire:navigate
                               class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-primary-950/40"
                               aria-label="ویرایش">
                                <x-icon name="edit" class="size-4" />
                            </a>
                            <button type="button" wire:click="deleteDiscount({{ $discount->id }})" wire:confirm="این کد تخفیف حذف شود؟"
                                    class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40"
                                    aria-label="حذف">
                                <x-icon name="trash" class="size-4" />
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="mt-6 rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700">
                    <span class="mx-auto grid size-16 place-items-center rounded-full bg-rose-50 dark:bg-rose-950/30">
                        <x-icon name="tag" class="size-8 text-rose-300 dark:text-rose-700" />
                    </span>
                    <p class="mt-4 text-sm font-extrabold">هنوز کد تخفیفی نساخته‌اید</p>
                    <p class="mt-1 text-[11px] text-slate-400">با کد تخفیف، مشتری‌ها را به خرید بیشتر تشویق کنید.</p>
                    <a href="{{ route('seller.discounts.create') }}" wire:navigate
                       class="mt-5 inline-block rounded-2xl bg-primary-600 px-6 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-700 active:scale-95">
                        ایجاد اولین کد تخفیف
                    </a>
                </div>
            @endforelse
        </div>

        <div class="pb-2"></div>
    </div>
</div>
