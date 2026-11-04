<div class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-extrabold">تنظیمات استوری</h2>
            <p class="mt-1 text-xs text-slate-400">سقف استوریِ فعال هر فروشگاه و حالت تایید استوری‌ها</p>
        </div>
        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 active:scale-95 disabled:opacity-60">
            <x-icon name="check" class="size-4" />
            ذخیره تنظیمات
        </button>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- ── سقف استوری فعال ── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400">
                    <x-icon name="layers" class="size-5" />
                </span>
                <div>
                    <h3 class="text-sm font-extrabold">سقف استوری فعال هر فروشگاه</h3>
                    <p class="mt-1 text-[11px] leading-5 text-slate-400">
                        حداکثر تعداد استوریِ «در حال نمایش» که یک فروشگاه می‌تواند همزمان نگه دارد. استوری‌های منقضی و زمان‌بندی‌شده در این سقف حساب نمی‌شوند.
                    </p>
                </div>
            </div>

            <div class="mt-4 max-w-40">
                <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400" for="story-max-active">تعداد</label>
                <div class="mt-1.5 flex items-center rounded-xl border border-slate-200 bg-slate-50 focus-within:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                    <button type="button" wire:click="$set('maxActive', (string) max(1, (int) $wire.maxActive - 1))"
                            class="grid size-10 place-items-center text-slate-400 transition hover:text-primary-600" aria-label="کمتر کردن">
                        <x-icon name="minus" class="size-4" />
                    </button>
                    <input id="story-max-active" type="number" min="1" max="99" wire:model.live="maxActive"
                           class="w-full border-x border-slate-200 bg-transparent py-2.5 text-center text-sm font-extrabold outline-none dark:border-slate-700">
                    <button type="button" wire:click="$set('maxActive', (string) min(99, (int) $wire.maxActive + 1))"
                            class="grid size-10 place-items-center text-slate-400 transition hover:text-primary-600" aria-label="بیشتر کردن">
                        <x-icon name="plus" class="size-4" />
                    </button>
                </div>
                @error('maxActive') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ── حالت تایید استوری ── --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <x-icon name="shield" class="size-5" />
                </span>
                <div>
                    <h3 class="text-sm font-extrabold">حالت تایید استوری</h3>
                    <p class="mt-1 text-[11px] leading-5 text-slate-400">
                        تعیین می‌کند استوریِ جدیدِ فروشنده‌ها بلافاصله منتشر شود یا اول مدیر آن را ببیند و تایید کند.
                    </p>
                </div>
            </div>

            <div class="mt-4 grid gap-2" role="radiogroup" aria-label="حالت تایید استوری">
                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border p-3.5 text-xs font-extrabold transition {{ $moderation === 'auto'
                    ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                    : 'border-slate-200 hover:border-primary-300 dark:border-slate-700' }}">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="zap" class="size-4 text-primary-500" />
                        <span>
                            تایید خودکار
                            <span class="mt-0.5 block text-[10px] font-semibold text-slate-400">استوری همان لحظه منتشر می‌شود (پیش‌فرض)</span>
                        </span>
                    </span>
                    <input type="radio" wire:model.live="moderation" value="auto" class="size-4 accent-primary-600">
                </label>
                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border p-3.5 text-xs font-extrabold transition {{ $moderation === 'manual'
                    ? 'border-primary-500 bg-primary-50/70 ring-1 ring-primary-500/30 dark:border-primary-600 dark:bg-primary-950/30'
                    : 'border-slate-200 hover:border-primary-300 dark:border-slate-700' }}">
                    <span class="flex items-center gap-2.5">
                        <x-icon name="shield-off" class="size-4 text-amber-500" />
                        <span>
                            تایید دستی مدیر
                            <span class="mt-0.5 block text-[10px] font-semibold text-slate-400">استوری با وضعیت «در انتظار تأیید» به صف مدیر می‌رود</span>
                        </span>
                    </span>
                    <input type="radio" wire:model.live="moderation" value="manual" class="size-4 accent-primary-600">
                </label>
            </div>
            @error('moderation') <p class="mt-1.5 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
