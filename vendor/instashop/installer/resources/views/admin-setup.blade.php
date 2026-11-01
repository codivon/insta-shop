<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>ساخت حساب مدیر — InstaShop</title>
    <link rel="stylesheet" href="/fonts/vazirmatn.css">
    <link rel="stylesheet" href="/install/assets/installer.css?v={{ $assetVersion }}">
    <script>
        (function () { try {
            var m = localStorage.getItem('ip-mode');
            if (m === 'dark' || (!m && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {} })();
    </script>
</head>
<body data-page="admin-setup">

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
            <span class="brand-badge">راه‌اندازی اولیه</span>
        </span>

        <button type="button" id="themeToggle" class="icon-btn" aria-label="تغییر تم روشن/تیره">
            <span class="i-sun" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg></span>
            <span class="i-moon" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg></span>
        </button>
    </header>

    {{-- ═══════════════════ SETUP CARD ═══════════════════ --}}
    <main class="shell">
        <div class="card">

            <div class="setup-hero">
                <span class="success-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <div>
                    <h1>نصب کامل شد — حساب مدیر را بسازید 🎉</h1>
                    <p class="muted" style="margin-bottom:0">
                        این صفحه فقط برای اولین راه‌اندازی نمایش داده می‌شود و پس از ساختن حساب مدیر
                        برای همیشه قفل می‌شود. پوسته و تنظیمات فروشگاه بعداً از پنل مدیریت هم قابل تغییرند.
                    </p>
                </div>
            </div>

            <form id="setupForm" novalidate>
                {{-- ── admin account ── --}}
                <h2 class="setup-heading">حساب مدیر</h2>
                <div class="grid-form setup-grid">
                    <div class="field wide">
                        <label for="su_name">نام و نام خانوادگی</label>
                        <input type="text" id="su_name" name="name" placeholder="مثلاً مهدی رضایی" required autocomplete="name">
                    </div>
                    <div class="field wide">
                        <label for="su_email">ایمیل (برای ورود به پنل)</label>
                        <input type="email" id="su_email" name="email" dir="ltr" placeholder="admin@example.com" required autocomplete="email" spellcheck="false">
                    </div>
                    <div class="field">
                        <label for="su_password">رمز عبور <small>(حداقل ۸ کاراکتر)</small></label>
                        <input type="password" id="su_password" name="password" dir="ltr" minlength="8" required autocomplete="new-password">
                    </div>
                    <div class="field">
                        <label for="su_password2">تکرار رمز عبور</label>
                        <input type="password" id="su_password2" name="password_confirmation" dir="ltr" minlength="8" required autocomplete="new-password">
                    </div>
                </div>

                {{-- ── theme picker ── --}}
                <h2 class="setup-heading">پوسته فروشگاه</h2>
                <p class="muted" style="margin-top:-.5rem">گرادیان اینستاگرامی به‌صورت پیش‌فرض انتخاب شده؛ هر پالت را که خواستید بزنید.</p>
                <div class="swatch-grid" id="swatchGrid" role="radiogroup" aria-label="انتخاب پوسته">
                    @foreach ($swatches as $slug => $s)
                        <button type="button" role="radio" aria-checked="{{ $slug === $defaultPreset ? 'true' : 'false' }}"
                                class="swatch {{ $slug === $defaultPreset ? 'is-selected' : '' }}"
                                data-swatch="{{ $slug }}">
                            <span class="swatch-bar" style="background: {{ $s['css'] }}" aria-hidden="true"></span>
                            <span class="swatch-name">{{ $s['label'] }}</span>
                            <span class="swatch-hint">{{ $s['hint'] }}</span>
                        </button>
                    @endforeach
                </div>
                <input type="hidden" name="theme_preset" id="themePreset" value="{{ $defaultPreset }}">

                {{-- ── optional store settings ── --}}
                <details class="store-optional">
                    <summary>
                        <span class="summary-chev" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </span>
                        تنظیمات پیش‌فرض فروشگاه <small>(اختیاری — می‌توانید رد کنید)</small>
                    </summary>
                    <div class="grid-form setup-grid">
                        <div class="field wide">
                            <label for="store_name">نام فروشگاه <small>(به‌جای InstaShop در سراسر سایت)</small></label>
                            <input type="text" id="store_name" name="store_name" placeholder="InstaShop" maxlength="60">
                        </div>
                        <div class="field">
                            <label for="store_phone">شماره تماس پشتیبانی</label>
                            <input type="tel" id="store_phone" name="store_phone" dir="ltr" placeholder="09121234567" maxlength="20" autocomplete="tel">
                        </div>
                        <div class="field">
                            <label for="store_instagram">آیدی اینستاگرام فروشگاه</label>
                            <input type="text" id="store_instagram" name="store_instagram" dir="ltr" placeholder="myshop" maxlength="60" spellcheck="false">
                        </div>
                    </div>
                </details>

                <div class="alert alert-error" id="setupError" role="alert" hidden></div>

                <div class="actions">
                    <span class="muted" style="margin:0">پس از ثبت، مستقیم به پنل مدیریت می‌روید.</span>
                    <button type="submit" class="btn btn-primary" id="setupSubmit">
                        ساخت حساب و ورود به پنل
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </main>

    {{-- ═══════════════════ FOOTER (sticky) ═══════════════════ --}}
    <footer class="foot">
        <b>InstaShop</b> © ۱۴۰۵ — راه‌اندازی اولیه • اجرای تمام مراحل بدون نیاز به SSH یا exec
    </footer>

    <script src="/install/assets/installer.js?v={{ $assetVersion }}"></script>
</body>
</html>
