<div class="ip-fade-up space-y-5"
     x-data="{
         editOpen: $wire.editOpen,
         testOpen: $wire.testOpen,
         insertVar(el, name) {
             const v = '{'+name+'}';
             const s = el.selectionStart ?? el.value.length;
             const e = el.selectionEnd ?? el.value.length;
             el.value = el.value.slice(0, s) + v + el.value.slice(e);
             el.dispatchEvent(new Event('input'));
             el.focus();
             el.setSelectionRange(s + v.length, s + v.length);
         }
     }"
     x-on:keydown.escape.window="editOpen = false; testOpen = false; $wire.closeEdit(); $wire.closeTest()"
     x-on:smstpl-close-edit.window="editOpen = false"
     x-on:smstpl-close-test.window="testOpen = false">

    {{-- ── flash ── --}}
    @if (session('admin-smstpl-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4500)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-smstpl-flash') }}
        </div>
    @endif
    @if (session('admin-smstpl-error'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" x-data x-init="setTimeout(() => $el.remove(), 6000)">
            <x-icon name="alert" class="size-4.5 shrink-0" />
            {{ session('admin-smstpl-error') }}
        </div>
    @endif

    {{-- ── header ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-3">
                <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30">
                    <x-icon name="megaphone" class="size-5" />
                </span>
                <div>
                    <h1 class="text-base font-black">مرکز پیامک</h1>
                    <p class="mt-1 max-w-2xl text-[11px] leading-6 text-slate-500 dark:text-slate-400">
                        متن پیامک‌های خودکار سیستم — محتوای هر رویداد از قبل آماده شده است؛ با «ویرایش» می‌توانید متن را تغییر دهید و با «تست» بدون ارسال واقعی آن را چک کنید. اگر پرووایدر شما پترن‌محور است (مثل کاوه‌نگار Verify یا پترن فراز)، در پنل پیامک قالب را با همین متغیرها بسازید و کد پترن را در ویرایش وارد کنید تا ارسال از پنل موفقیت‌آمیز شود. با غیرفعال‌کردن هر قالب، آن رویداد دیگر پیامک نمی‌فرستد.
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.settings', ['tab' => 'sms']) }}" wire:navigate
               class="inline-flex shrink-0 items-center gap-1.5 self-start rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-[11px] font-bold text-slate-600 transition hover:bg-slate-50 lg:self-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                <x-icon name="sliders" class="size-3.5" />
                تنظیمات پرووایدر
            </a>
        </div>

        {{-- ── toolbar ── --}}
        <div class="mt-4 flex flex-col gap-3 border-t border-dashed border-slate-200 pt-4 xl:flex-row xl:items-center xl:justify-between dark:border-slate-700">
            {{-- group chips --}}
            <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="فیلتر دسته‌بندی">
                @php
                    $groupChips = array_merge(['all' => ['همه', 'bag']], \App\Support\Sms\SmsTemplates::GROUPS);
                @endphp
                @foreach ($groupChips as $key => [$label, $icon])
                    <button type="button" wire:click="$set('group', '{{ $key }}')"
                            class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold transition {{ $group === $key
                                ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                        <x-icon name="{{ $icon }}" class="size-3.5" />
                        {{ $label }}
                        <span class="rounded-full px-1.5 text-[9px] font-black {{ $group === $key ? 'bg-white/25' : 'bg-white/80 dark:bg-slate-900/70' }}">{{ \App\Support\Fmt::fa($counts[$key] ?? 0) }}</span>
                    </button>
                @endforeach
            </div>

            {{-- search + count --}}
            <div class="flex items-center gap-2">
                <div class="relative flex-1 xl:w-64">
                    <x-icon name="search" class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" wire:model.live.debounce.400ms="search" placeholder="جستجوی قالب..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pe-3 ps-9 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                </div>
                <span class="whitespace-nowrap rounded-xl bg-emerald-50 px-3 py-2.5 text-[11px] font-black text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300">
                    همه {{ \App\Support\Fmt::fa($counts['all'] ?? 0) }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── template cards ── --}}
    <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
        @forelse ($templates as $tpl)
            <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900" wire:key="tpl-{{ $tpl->id }}" wire:loading.class="opacity-70" wire:target="toggleEnabled({{ $tpl->id }})">
                {{-- top row --}}
                <div class="flex items-start justify-between gap-2">
                    <div class="flex min-w-0 items-center gap-2">
                        {{-- toggle --}}
                        <button type="button" wire:click="toggleEnabled({{ $tpl->id }})" wire:loading.attr="disabled" wire:target="toggleEnabled({{ $tpl->id }})"
                                role="switch" aria-checked="{{ $tpl->enabled ? 'true' : 'false' }}" aria-label="{{ $tpl->enabled ? 'غیرفعال کردن' : 'فعال کردن' }} {{ $tpl->title }}"
                                class="flex h-6 w-11 shrink-0 items-center rounded-full px-0.5 transition {{ $tpl->enabled ? 'justify-end bg-emerald-500' : 'justify-start bg-slate-300 dark:bg-slate-700' }}">
                            <span class="inline-block size-5 rounded-full bg-white shadow"></span>
                        </button>
                        <h3 class="truncate text-sm font-extrabold">{{ $tpl->title }}</h3>
                    </div>
                    <code class="shrink-0 rounded-lg bg-slate-100 px-2 py-1 text-[9.5px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400" dir="ltr">{{ $tpl->key }}</code>
                </div>

                {{-- body preview --}}
                <div class="mt-3 flex-1">
                    <p class="whitespace-pre-line rounded-2xl bg-slate-50 p-3.5 text-[11.5px] leading-6 text-slate-600 line-clamp-4 dark:bg-slate-800/60 dark:text-slate-300" dir="auto">
                        {{ $tpl->body }}
                    </p>
                </div>

                {{-- variable chips --}}
                @if (count($tpl->variables ?? []) > 0)
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($tpl->variables as $var)
                            <code class="rounded-md border border-primary-200 bg-primary-50 px-1.5 py-0.5 text-[9px] font-bold text-primary-600 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300" dir="ltr">{ {{ $var }} }</code>
                        @endforeach
                    </div>
                @endif

                {{-- footer --}}
                <div class="mt-4 flex items-center justify-between gap-2 border-t border-dashed border-slate-200 pt-3.5 dark:border-slate-700">
                    <span class="text-[10px] text-slate-400">آخرین ویرایش: {{ \App\Support\JDate::dateTime($tpl->updated_at) }}</span>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="openEdit({{ $tpl->id }})" x-on:click="editOpen = true"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-[11px] font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                            <x-icon name="edit" class="size-3.5" />
                            ویرایش
                        </button>
                        <button type="button" wire:click="openTest('{{ $tpl->key }}')" x-on:click="testOpen = true"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-3.5 py-2 text-[11px] font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700">
                            <x-icon name="megaphone" class="size-3.5" />
                            تست
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 p-12 text-center md:col-span-2 2xl:col-span-3 dark:border-slate-700">
                <x-icon name="megaphone" class="mx-auto size-10 text-slate-300" />
                <p class="mt-3 text-xs font-bold text-slate-400">قالبی مطابق فیلتر فعلی یافت نشد.</p>
                <p class="mt-1 text-[11px] text-slate-400">قالب‌ها با اجرای مایگریشن‌ها و سیدر جدید به‌صورت خودکار ساخته می‌شوند.</p>
            </div>
        @endforelse
    </div>

    {{-- ── recent sms logs ── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <h3 class="flex items-center gap-2 text-sm font-extrabold">
                <x-icon name="file-text" class="size-4 text-primary-500" />
                آخرین پیامک‌های ارسال‌شده
            </h3>
            <span class="text-[11px] text-slate-400">{{ \App\Support\Fmt::fa($logs->count()) }} ردیف اخیر</span>
        </div>
        @if ($logs->isEmpty())
            <p class="px-5 py-8 text-center text-xs text-slate-400">هنوز پیامکی ارسال نشده است.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-right text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-800/40">
                            <th class="px-5 py-3 font-semibold">گیرنده</th>
                            <th class="px-3 py-3 font-semibold">قالب</th>
                            <th class="px-3 py-3 font-semibold">متن</th>
                            <th class="px-3 py-3 font-semibold">زمان</th>
                            <th class="px-5 py-3 font-semibold">وضعیت</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach ($logs as $log)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40" wire:key="slog-{{ $log->id }}">
                                <td class="px-5 py-3 text-xs font-bold" dir="ltr">{{ \App\Support\Fmt::toFa(\App\Support\Phone::pretty($log->phone)) }}</td>
                                <td class="px-3 py-3"><code class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400" dir="ltr">{{ $log->template ?? '—' }}</code></td>
                                <td class="max-w-72 px-3 py-3 text-[11px] text-slate-500"><span class="line-clamp-1" dir="auto">{{ $log->message }}</span></td>
                                <td class="px-3 py-3 text-[10px] text-slate-400">{{ \App\Support\JDate::dateTime($log->created_at) }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $log->status === 'sent'
                                        ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                                        : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                                        {{ $log->status === 'sent' ? 'ارسال شد' : 'ناموفق' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ═══════════════════ EDIT MODAL ═══════════════════ --}}
    <x-modal-panel open="$wire.editOpen" max-width="lg" labelledby="tpl-edit-title"
                   close-js="__ipOpen = false; $wire.closeEdit()">

        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 id="tpl-edit-title" class="text-sm font-extrabold">ویرایش قالب پیامک</h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeEdit()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="space-y-5 px-5 py-5">
                {{-- status + chips row --}}
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="$toggle('editEnabled')" wire:loading.attr="disabled" wire:target="editEnabled"
                            role="switch" aria-checked="{{ $editEnabled ? 'true' : 'false' }}" aria-label="فعال بودن قالب"
                            class="flex h-6.5 w-12 shrink-0 items-center rounded-full px-1 transition {{ $editEnabled ? 'justify-end bg-emerald-500' : 'justify-start bg-slate-300 dark:bg-slate-700' }}">
                        <span class="inline-block size-5 rounded-full bg-white shadow"></span>
                    </button>
                    <span class="text-xs font-extrabold {{ $editEnabled ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">{{ $editEnabled ? 'فعال' : 'غیرفعال' }}</span>
                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-600 dark:bg-amber-950/40 dark:text-amber-300">{{ \App\Support\Sms\SmsTemplates::GROUPS[$editGroup][0] ?? $editGroup }}</span>
                    <code class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400" dir="ltr">{{ $editKey }}</code>
                </div>

                {{-- title --}}
                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">عنوان رویداد</span>
                    <input type="text" wire:model="editTitle"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    @error('editTitle') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>

                {{-- pattern code --}}
                <div class="rounded-2xl border border-dashed border-primary-300 bg-primary-50/50 p-4 dark:border-primary-800 dark:bg-primary-950/30">
                    <p class="text-[11px] font-extrabold text-primary-700 dark:text-primary-300">کد قرار گرفتن در پنل پیامک (الزامی برای پرووایدرهای پترن‌محور — پیش‌فرض خالی است)</p>
                    <input type="text" wire:model="editPattern" dir="ltr" placeholder="مثال: orderPaidNotify"
                           class="mt-2.5 w-full rounded-xl border border-primary-200 bg-white px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-primary-800 dark:bg-slate-900">
                    @error('editPattern') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                    <p class="mt-2 text-[10px] leading-5 text-slate-500 dark:text-slate-400">
                        کد/نام قالب تعریف‌شده در پنل پیامک (مثل Verify Lookup کاوه‌نگار یا پترن فراز اس‌ام‌اس). متغیرهای قالب در پنل باید دقیقاً با متغیرهای پایین یکی باشد. اگر پرووایدر شما با «متن آزاد» ارسال می‌کند، این فیلد را خالی بگذارید (خروجی پیش‌فرض = خودکار).
                    </p>
                </div>

                {{-- body --}}
                <div>
                    <label class="mb-1.5 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">متن پیامک</span>
                        <span class="text-[10px] font-bold text-slate-400">{{ \App\Support\Fmt::fa(mb_strlen($editBody)) }} کاراکتر</span>
                    </label>
                    <textarea wire:model.live.debounce.300ms="editBody" rows="5" x-ref="bodyInput"
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs leading-6 shadow-sm focus:border-primary-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800"></textarea>
                    @error('editBody') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror

                    {{-- variables --}}
                    @php $editVars = $this->editVariables(); @endphp
                    @if (count($editVars) > 0)
                        <p class="mt-3 text-[11px] font-extrabold text-slate-600 dark:text-slate-300">متغیرهای قابل استفاده <span class="font-normal text-slate-400">(کلیک = درج در متن)</span></p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($editVars as $var)
                                <button type="button" x-on:click="insertVar($refs.bodyInput, '{{ $var }}')"
                                        class="rounded-lg border border-primary-200 bg-primary-50 px-2.5 py-1 text-[10px] font-bold text-primary-600 transition hover:bg-primary-100 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300" dir="ltr">
                                    { {{ $var }} }
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- live preview --}}
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60">
                    <p class="text-[11px] font-extrabold text-primary-600 dark:text-primary-300">پیش‌نمایش زنده (با داده نمونه):</p>
                    <p class="mt-2 whitespace-pre-line text-[11.5px] leading-6 text-slate-600 dark:text-slate-300" dir="auto">{{ $this->editPreview() }}</p>
                </div>
            </div>

        <x-slot:footer>
            <div class="flex gap-2 px-5 py-4">
                <button type="button" wire:click="openTest('{{ $editKey }}', true)"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                    <x-icon name="megaphone" class="size-3.5" />
                    ارسال آزمایشی
                </button>
                <button type="button" wire:click="saveTemplate" wire:loading.attr="disabled"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                    <span wire:loading wire:target="saveTemplate" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    ذخیره قالب
                </button>
            </div>
        </x-slot:footer>
    </x-modal-panel>

    {{-- ═══════════════════ TEST MODAL ═══════════════════ --}}
    <x-modal-panel open="$wire.testOpen" max-width="md" z-class="z-[80]" labelledby="tpl-test-title"
                   close-js="__ipOpen = false; $wire.closeTest()">

        <x-slot:header>
            <div class="flex items-center justify-between px-5 py-4">
                <h3 id="tpl-test-title" class="text-sm font-extrabold">تست «{{ $this->testTitle() }}»</h3>
                <button type="button" x-on:click="__ipOpen = false; $wire.closeTest()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        </x-slot:header>

        <div class="space-y-4 px-5 py-5">
                {{-- preview with sample data --}}
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60">
                    <p class="text-[11px] font-extrabold text-primary-600 dark:text-primary-300">پیش‌نمایش با متغیرهای نمونه:</p>
                    <p class="mt-2 whitespace-pre-line text-[11.5px] leading-6 text-slate-600 dark:text-slate-300" dir="auto">{{ \App\Support\Sms\SmsTemplates::preview($testKey) }}</p>
                </div>

                <label class="block">
                    <span class="mb-1.5 block text-[11px] font-bold text-slate-600 dark:text-slate-300">موبایل آزمایشی</span>
                    <input type="tel" wire:model="testPhone" dir="ltr" placeholder="09123456789" autocomplete="off"
                           class="w-full rounded-xl border border-primary-300 bg-white px-3 py-2.5 text-xs shadow-sm focus:border-primary-500 focus:outline-none dark:border-primary-800 dark:bg-slate-900">
                    @error('testPhone') <span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </label>

                <label class="flex cursor-pointer items-center gap-2 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                    <input type="checkbox" wire:model="testReal" class="size-4 rounded" style="accent-color: var(--ip-primary)">
                    ارسال واقعی با پرووایدر فعال (در غیر این صورت فقط پشت‌نمایش)
                </label>
            </div>

        <x-slot:footer>
            <div class="px-5 py-4">
                <button type="button" wire:click="sendTest" wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-50">
                    <span wire:loading wire:target="sendTest" class="inline-block size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    تست
                </button>
            </div>
        </x-slot:footer>
    </x-modal-panel>
</div>
