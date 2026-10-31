<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>نصب InstaShop</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    <link rel="stylesheet" href="{{ route('installer.asset', ['file' => 'installer.css']) }}?v={{ $assetVersion }}">
    <script>
        (function () { try {
            var m = localStorage.getItem('ip-mode');
            if (m === 'dark' || (!m && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {} })();
    </script>
</head>
<body data-migrations="{{ $migrationCount }}" data-seeders="{{ $seederCount }}" data-resume="{{ $resume ? '1' : '0' }}">

    <div class="bg-decor" aria-hidden="true">
        <span class="blob blob-1"></span>
        <span class="blob blob-2"></span>
    </div>

    {{-- ═══════════════════ TOP BAR ═══════════════════ --}}
    <header class="topbar">
        <span class="brand">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/></svg>
            </span>
            <span class="brand-name">Insta<span>Shop</span></span>
            <span class="brand-badge">نصب‌کننده</span>
        </span>

        <button type="button" id="themeToggle" class="icon-btn" aria-label="تغییر تم روشن/تیره">
            <span class="i-sun" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg></span>
            <span class="i-moon" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg></span>
        </button>
    </header>

    {{-- ═══════════════════ WIZARD ═══════════════════ --}}
    <main class="shell">
        <div class="card">
            <ol class="stepper" aria-label="مراحل نصب">
                @foreach (['خوش‌آمدید', 'بررسی سرور', 'دیتابیس', 'نصب', 'پایان'] as $i => $label)
                    <li class="step" data-step-dot="{{ $i + 1 }}">
                        <span class="dot">{{ ['۱', '۲', '۳', '۴', '۵'][$i] }}</span>
                        <span class="lbl">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>

            {{-- ────────── STEP 1 · welcome ────────── --}}
            <section class="pane" data-pane="1">
                <h1>به نصب‌کننده InstaShop خوش آمدید 👋</h1>
                <p class="lead">
                    این ویزارد InstaShop را در چند دقیقه روی هاست شما نصب می‌کند —
                    بدون نیاز به SSH، ترمینال یا دستور artisan. فقط اطلاعات دیتابیس را وارد کنید
                    و بقیه کارها به‌صورت خودکار و با نمایش درصد پیشرفت انجام می‌شود.
                </p>

                <ul class="hints">
                    <li>
                        <span class="tick" aria-hidden="true"><svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        بررسی خودکار پیش‌نیازهای سرور و دسترسی پوشه‌ها
                    </li>
                    <li>
                        <span class="tick" aria-hidden="true"><svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        ساخت خودکار فایل <code dir="ltr">.env</code> و تولید کلید امن برنامه
                    </li>
                    <li>
                        <span class="tick" aria-hidden="true"><svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        اجرای جداول و داده‌های اولیه با نوار پیشرفت — بدون کرش حتی با صدها جدول
                    </li>
                    <li>
                        <span class="tick" aria-hidden="true"><svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        قفل شدن نصب‌کننده پس از اتمام برای حفظ امنیت
                    </li>
                </ul>

                <div class="env-info">
                    <span class="env-chip">PHP {{ $phpVersion }}</span>
                    <span class="env-chip">Laravel {{ $laravelVersion }}</span>
                    <span class="env-chip">{{ $migrationCount }} مهاجرت</span>
                    <span class="env-chip">{{ $seederCount }} سیدر</span>
                </div>

                <div class="actions">
                    <span class="muted" style="margin:0">نسخه ۱٫۰</span>
                    <button type="button" class="btn btn-primary" data-goto="2">
                        شروع نصب
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                </div>
            </section>

            {{-- ────────── STEP 2 · requirements & permissions ────────── --}}
            <section class="pane" data-pane="2" hidden>
                <h2>بررسی پیش‌نیازهای سرور</h2>
                <ul class="checklist">
                    @foreach ($required as $r)
                        <li class="{{ $r['ok'] ? '' : 'is-fail' }}">
                            <span class="name">
                                @if ($r['ok'])
                                    <span class="state ok" aria-label="موجود"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                @else
                                    <span class="state fail" aria-label="ناموجود"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg></span>
                                @endif
                                {{ $r['label'] }}
                            </span>
                            <span class="state {{ $r['ok'] ? 'ok' : 'fail' }}">{{ $r['ok'] ? 'موجود' : 'ناموجود' }}</span>
                        </li>
                    @endforeach
                </ul>

                @if (collect($optional)->where('ok', false)->isNotEmpty())
                    <details class="optional">
                        <summary>پیش‌نیازهای اختیاری ({{ collect($optional)->where('ok', false)->count() }} مورد اختیاری موجود نیست)</summary>
                        <ul class="checklist">
                            @foreach ($optional as $r)
                                <li class="{{ $r['ok'] ? '' : 'is-warn' }}">
                                    <span class="name">
                                        <span class="state {{ $r['ok'] ? 'ok' : 'warn' }}">
                                            {{ $r['ok'] ? '✔' : '!' }}
                                        </span>
                                        {{ $r['label'] }}
                                    </span>
                                    <span class="state {{ $r['ok'] ? 'ok' : 'warn' }}">{{ $r['ok'] ? 'موجود' : 'اختیاری' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endif

                <h2>دسترسی نوشتن پوشه‌ها</h2>
                <ul class="checklist">
                    @foreach ($permissions as $p)
                        <li class="{{ $p['ok'] ? '' : 'is-fail' }}">
                            <span class="name">
                                @if ($p['ok'])
                                    <span class="state ok"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                @else
                                    <span class="state fail"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg></span>
                                @endif
                                {{ $p['label'] }}
                                <code dir="ltr">{{ $p['path'] }}</code>
                            </span>
                            <span class="state {{ $p['ok'] ? 'ok' : 'fail' }}">{{ $p['ok'] ? 'قابل نوشتن' : 'بدون دسترسی' }}</span>
                        </li>
                    @endforeach
                </ul>

                @if (! $ready)
                    <div class="alert alert-error" role="alert">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4M12 17h.01"/></svg>
                        <div>برای ادامه، موارد قرمز رنگ را رفع کنید (مثلاً دسترسی پوشه‌ها را با <code dir="ltr">chmod 755</code> اصلاح کنید) و صفحه را دوباره بارگذاری کنید.</div>
                    </div>
                @endif

                <div class="actions">
                    <button type="button" class="btn btn-ghost" data-goto="1">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        بازگشت
                    </button>
                    <button type="button" class="btn btn-primary" data-goto="3" @unless ($ready) disabled @endunless>
                        ادامه
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                </div>
            </section>

            {{-- ────────── STEP 3 · database ────────── --}}
            <section class="pane" data-pane="3" hidden>
                <h2>اطلاعات دیتابیس MySQL</h2>
                <p class="muted">
                    ابتدا از پنل هاست خود یک دیتابیس MySQL و کاربر آن را بسازید، سپس اطلاعات را وارد کنید.
                    نصب‌کننده اتصال را آزمایش می‌کند، فایل <code dir="ltr">.env</code> را می‌سازد و کلید امن برنامه را تولید می‌کند.
                </p>

                <form id="dbForm" novalidate>
                    <div class="grid-form">
                        <div class="field">
                            <label for="db_host">میزبان دیتابیس <small>(DB Host)</small></label>
                            <input type="text" id="db_host" name="db_host" dir="ltr" value="localhost" required autocomplete="off" spellcheck="false">
                        </div>
                        <div class="field">
                            <label for="db_port">پورت</label>
                            <input type="number" id="db_port" name="db_port" dir="ltr" value="3306" min="1" max="65535" required>
                        </div>
                        <div class="field">
                            <label for="db_database">نام دیتابیس</label>
                            <input type="text" id="db_database" name="db_database" dir="ltr" placeholder="instashop_db" required autocomplete="off" spellcheck="false">
                        </div>
                        <div class="field">
                            <label for="db_username">نام کاربری دیتابیس</label>
                            <input type="text" id="db_username" name="db_username" dir="ltr" placeholder="instashop_user" required autocomplete="off" spellcheck="false">
                        </div>
                        <div class="field wide">
                            <label for="db_password">رمز عبور دیتابیس <small>(اگر دارد)</small></label>
                            <input type="password" id="db_password" name="db_password" dir="ltr" autocomplete="new-password">
                        </div>

                        <div class="field-note" role="note">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex:none;margin-top:.25rem" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                            <span>رمز دیتابیس با حروف بزرگ/کوچک و علامت‌ها دقیقاً همان چیزی است که در هاست تنظیم کرده‌اید.</span>
                        </div>

                        <div class="alert alert-error" id="dbError" role="alert" hidden></div>
                    </div>

                    <div class="actions">
                        <button type="button" class="btn btn-ghost" data-goto="2">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                            بازگشت
                        </button>
                        <button type="submit" class="btn btn-primary" id="dbSubmit">
                            بررسی و ذخیره
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                    </div>
                </form>
            </section>

            {{-- ────────── STEP 4 · installing ────────── --}}
            <section class="pane" data-pane="4" hidden>
                <h2 id="phaseTitle">در حال نصب…</h2>
                <p class="muted">این مرحله خودکار است — صفحه را نبندید. هر مورد جداگانه اجرا می‌شود؛ حتی با صدها جدول هیچ کرشی رخ نمی‌دهد.</p>

                <div class="progress-row">
                    <div class="progress" role="progressbar" aria-label="پیشرفت نصب" aria-valuemin="0" aria-valuemax="100" id="progressBar">
                        <div class="bar" id="bar"></div>
                    </div>
                    <div class="percent" id="percent">۰٪</div>
                </div>

                <div class="phase"><span class="dot-live" aria-hidden="true"></span> <span>پردازش خودکار</span></div>
                <div class="task" id="task">آماده‌سازی…</div>

                <div class="terminal" id="log" aria-live="polite"></div>

                <div class="alert alert-error" id="installError" role="alert" hidden>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex:none;margin-top:.2rem" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4M12 17h.01"/></svg>
                    <div>
                        <span id="installErrorMsg"></span>
                        <span class="detail" id="installErrorDetail"></span>
                        <button type="button" class="btn btn-primary" id="retryBtn" style="margin-top:.6rem">
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                            تلاش مجدد
                        </button>
                    </div>
                </div>
            </section>

            {{-- ────────── STEP 5 · finish ────────── --}}
            <section class="pane" data-pane="5" hidden>
                <div class="success-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </div>
                <h1 style="text-align:center">نصب با موفقیت انجام شد 🎉</h1>
                <p class="muted" style="text-align:center">InstaShop آماده است؛ اکنون می‌توانید فروشگاه اینستاگرامی‌تان را بسازید.</p>

                <dl class="summary">
                    <div><dt>آدرس سایت</dt><dd id="sumUrl">—</dd></div>
                    <div><dt>دیتابیس</dt><dd id="sumDb">—</dd></div>
                    <div><dt>لینک storage</dt><dd id="sumStorage">—</dd></div>
                    <div><dt>وضعیت نصب‌کننده</dt><dd>قفل شد 🔒</dd></div>
                </dl>

                <div class="alert alert-info">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex:none;margin-top:.2rem" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    <div>
                        ورود مدیر: <code dir="ltr">/admin</code> — اپ فروشنده: <code dir="ltr">/seller</code> — فروشگاه: <code dir="ltr">/shop</code>
                        <br>برای نصب مجدد (فقط در صورت نیاز) فایل‌های <code dir="ltr">.env</code> و <code dir="ltr">storage/app/install.lock</code> را حذف کنید.
                    </div>
                </div>

                <div class="actions center">
                    <a class="btn btn-primary" href="/">
                        ورود به InstaShop
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                </div>
            </section>
        </div>
    </main>

    {{-- ═══════════════════ FOOTER (sticky) ═══════════════════ --}}
    <footer class="foot">
        <b>InstaShop</b> © ۱۴۰۵ — نصب‌کننده وب نسخه ۱٫۰ • اجرای تمام مراحل بدون نیاز به SSH یا exec
    </footer>

    <script src="{{ route('installer.asset', ['file' => 'installer.js']) }}?v={{ $assetVersion }}"></script>
</body>
</html>
