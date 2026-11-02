<div class="space-y-5">

    {{-- ── section header ── --}}
    <div class="flex items-center gap-3">
        <span class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white shadow-md shadow-primary-600/25">
            <x-icon name="database" class="size-5" />
        </span>
        <div>
            <h2 class="text-sm font-black">پشتیبان‌گیری خودکار</h2>
            <p class="text-[11px] text-slate-400">بکاپ کامل دیتابیس طبق زمان‌بندی دلخواه + حذف خودکار فایل‌های قدیمی + ارسال پشتیبان با ایمیل.</p>
        </div>
    </div>

    {{-- ── flash ── --}}
    @if (session('admin-backups-flash'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <x-icon name="circle-check" class="size-4.5 shrink-0" />
            {{ session('admin-backups-flash') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-5">

        {{-- ── schedule card ── --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="flex items-center gap-1.5 text-[13px] font-bold">
                        <x-icon name="clock" class="size-4 text-primary-600 dark:text-primary-400" />
                        فعال‌سازی پشتیبان‌گیری خودکار
                    </p>
                    <p class="mt-1 text-[11px] leading-5 text-slate-400">بکاپ، در پس‌زمینه و بدون تایم‌اوت ساخته می‌شود و در صفحهٔ «پشتیبان‌گیری» با برچسب «خودکار» دیده می‌شود.</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" wire:model.live="autoEnabled" class="peer sr-only" />
                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:start-0.5 after:top-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition-all peer-checked:bg-primary-600 peer-checked:after:translate-x-5 dark:bg-slate-700"></div>
                </label>
            </div>

            <div class="mt-4">
                <div x-show="$wire.autoEnabled" x-cloak class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">دورهٔ پشتیبان‌گیری</label>
                            <select wire:model="frequency"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900">
                                <option value="daily">هر روز</option>
                                <option value="weekly">هر هفته</option>
                                <option value="monthly">هر ماه</option>
                                <option value="custom">بازهٔ زمانی دلخواه (بر اساس روز)</option>
                            </select>
                        </div>
                        <div x-show="$wire.frequency === 'custom'" x-cloak>
                            <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">فاصلهٔ اجرا (هر چند روز؟)</label>
                            <input type="number" min="1" max="365" wire:model="intervalDays"
                                   placeholder="مثلاً ۳"
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900" />
                            @error('intervalDays') <p class="mt-1 text-[10.5px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">ساعت اجرا</label>
                            <input type="time" wire:model="runTime"
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900" />
                            @error('runTime') <p class="mt-1 text-[10.5px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">حذف خودکار پشتیبان‌های قدیمی‌تر از</label>
                            <div class="flex items-center gap-2">
                                <input type="number" min="7" max="3650" wire:model="retentionDays"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900" />
                                <span class="shrink-0 text-[11.5px] font-bold text-slate-400">روز</span>
                            </div>
                            <p class="mt-1 text-[10.5px] text-slate-400">مثلاً ۱۸۰ روز = ۶ ماه. فقط فایل‌های «خودکار» حذف می‌شوند؛ پشتیبان‌های دستی شما باقی می‌مانند.</p>
                            @error('retentionDays') <p class="mt-1 text-[10.5px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if ($nextRun)
                        <div class="flex items-center gap-2 rounded-xl bg-primary-50 px-3.5 py-2.5 text-[11.5px] font-bold text-primary-700 dark:bg-primary-950/40 dark:text-primary-300">
                            <x-icon name="info" class="size-4 shrink-0" />
                            اجرای بعدی: {{ \App\Support\JDate::dateTime($nextRun) }} — به شرط فعال‌بودن کرون جاب سرور.
                        </div>
                    @endif
                </div>

                <div x-show="!$wire.autoEnabled" x-cloak class="rounded-xl bg-slate-50 px-3.5 py-2.5 text-[11.5px] leading-6 text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                    پشتیبان‌گیری خودکار خاموش است — فایل‌های پشتیبان فقط با دکمهٔ «پشتیبان‌گیری الان» در صفحهٔ پشتیبان‌گیری ساخته می‌شوند.
                </div>
            </div>
        </section>

        {{-- ── email card ── --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="flex items-center gap-1.5 text-[13px] font-bold">
                        <x-icon name="message" class="size-4 text-primary-600 dark:text-primary-400" />
                        ارسال پشتیبان با ایمیل
                    </p>
                    <p class="mt-1 text-[11px] leading-5 text-slate-400">پس از هر پشتیبان‌گیری خودکار، فایل به ایمیل شما ارسال می‌شود؛ اگر حجیم باشد به‌صورت خودکار تکه‌تکه شده و در چند ایمیل می‌رسد.</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" wire:model.live="emailEnabled" class="peer sr-only" />
                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:start-0.5 after:top-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition-all peer-checked:bg-primary-600 peer-checked:after:translate-x-5 dark:bg-slate-700"></div>
                </label>
            </div>

            <div x-show="$wire.emailEnabled" x-cloak class="mt-4 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">آدرس ایمیل گیرندهٔ پشتیبان</label>
                        <input type="email" wire:model="emailTo" placeholder="backup@example.com" dir="ltr"
                               class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900" />
                        @error('emailTo') <p class="mt-1 text-[10.5px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">حداکثر حجم هر ایمیل (مگابایت)</label>
                        <select wire:model="emailMaxMb"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900">
                            <option value="5">۵ مگابایت</option>
                            <option value="10">۱۰ مگابایت</option>
                            <option value="15">۱۵ مگابایت</option>
                            <option value="20">۲۰ مگابایت</option>
                        </select>
                        <p class="mt-1 text-[10.5px] text-slate-400">فایل‌های بزرگ‌تر به چند ایمیل تقسیم می‌شوند (۱۰ مناسب‌ترین گزینه است).</p>
                    </div>
                </div>

                {{-- ── smtp ── --}}
                <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                    <p class="mb-3 flex items-center gap-1.5 text-[12px] font-black">
                        <x-icon name="globe" class="size-3.5 text-primary-600 dark:text-primary-400" />
                        تنظیمات SMTP برای ایمیل پشتیبان
                        <span class="text-[10px] font-normal text-slate-400">(مستقل از تنظیمات دیگر — صرفاً برای ایمیل پشتیبان)</span>
                    </p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">میزبان (Host)</label>
                            <input type="text" wire:model="smtpHost" placeholder="smtp.example.com" dir="ltr"
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900" />
                            @error('smtpHost') <p class="mt-1 text-[10.5px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">پورت</label>
                            <input type="number" wire:model="smtpPort" placeholder="587" dir="ltr"
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900" />
                            @error('smtpPort') <p class="mt-1 text-[10.5px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">نام کاربری</label>
                            <input type="text" wire:model="smtpUsername" dir="ltr"
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">رمز عبور</label>
                            <input type="password" wire:model="smtpPassword" dir="ltr"
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">نوع رمزنگاری</label>
                            <select wire:model="smtpEncryption"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900">
                                <option value="tls">TLS (معمولاً ۵۸۷)</option>
                                <option value="ssl">SSL (معمولاً ۴۶۵)</option>
                                <option value="none">بدون رمزنگاری</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">آدرس فرستنده</label>
                            <input type="email" wire:model="smtpFromAddress" placeholder="no-reply@example.com" dir="ltr"
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900" />
                            @error('smtpFromAddress') <p class="mt-1 text-[10.5px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11.5px] font-bold text-slate-500 dark:text-slate-400">نام فرستنده</label>
                            <input type="text" wire:model="smtpFromName" placeholder="پشتیبان‌گیری اینستاشاپ"
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-[12.5px] font-bold outline-none transition focus:border-primary-400 dark:border-slate-700 dark:bg-slate-900" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" wire:click="sendTestEmail" wire:loading.attr="disabled" wire:target="sendTestEmail"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-primary-200 bg-primary-50 px-4 py-2 text-[11px] font-bold text-primary-700 transition hover:bg-primary-100 disabled:opacity-60 dark:border-primary-900 dark:bg-primary-950/40 dark:text-primary-300">
                            <x-icon name="message" class="size-3.5" />
                            <span wire:loading.remove wire:target="sendTestEmail">ارسال ایمیل آزمایشی</span>
                            <span wire:loading wire:target="sendTestEmail">در حال ارسال...</span>
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="!$wire.emailEnabled" x-cloak class="mt-4 rounded-xl bg-slate-50 px-3.5 py-2.5 text-[11.5px] leading-6 text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                ارسال ایمیلی پشتیبان خاموش است — فایل‌ها فقط روی سرور نگهداری می‌شوند.
            </div>
        </section>

        {{-- ── save ── --}}
        <div class="flex items-center justify-end gap-2">
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-primary-600/25 transition hover:bg-primary-700 disabled:opacity-60">
                <x-icon name="check" class="size-4" />
                ذخیرهٔ تنظیمات پشتیبان‌گیری
            </button>
        </div>
    </form>
</div>
