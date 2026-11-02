<?php
/*
 * ────────────────────────────────────────────────────────────────
 *  InstaShop — local TEST gateway page (replaces the vendor's
 *  plain local-form). Rendered as raw PHP (required by
 *  Shetabit\Multipay\RedirectionForm::render()) — NO Blade here.
 *
 *  Available: $inputs (action URLs + labels), $action, $method, $view
 * ────────────────────────────────────────────────────────────────
 */
$in = static fn (string $key, string $default = '') => htmlspecialchars((string) ($inputs[$key] ?? $default), ENT_QUOTES, 'UTF-8');

$themeVars = '';
$amountFa  = (string) ($inputs['price'] ?? '0');
$orderNo   = (string) ($inputs['orderId'] ?? '');

try {
    $themeVars = (string) \App\Support\Theme::cssVariables();
    $amountFa  = \App\Support\Fmt::toFa((string) ($inputs['price'] ?? '0'));
    $orderNo   = \App\Support\Fmt::toFa((string) ($inputs['orderId'] ?? ''));
} catch (\Throwable) {
    // theme helpers unavailable — fall back to the raw values above
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $in('title', 'درگاه پرداخت آزمایشی') ?></title>
<link rel="stylesheet" href="/fonts/vazirmatn.css">
<style>
:root { <?= $themeVars ?> --lg-primary: var(--ip-primary, #D62976); }
* { margin: 0; padding: 0; box-sizing: border-box; }
html { -webkit-tap-highlight-color: transparent; }
body {
    font-family: Vazirmatn, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
    min-height: 100dvh; display: grid; place-items: center; padding: 20px;
    background: linear-gradient(155deg, #f8fafc 0%, #fdf2f8 45%, #f1f5f9 100%);
    color: #0f172a; -webkit-font-smoothing: antialiased;
}
.blob { position: fixed; border-radius: 9999px; filter: blur(70px); opacity: .35; pointer-events: none; }
.blob.b1 { width: 300px; height: 300px; top: -90px; right: 12%; background: #f9a8c9; background: color-mix(in srgb, var(--lg-primary) 45%, #fff); }
.blob.b2 { width: 260px; height: 260px; bottom: -80px; left: 10%; background: #fecdd3; opacity: .3; }
.blob.b3 { width: 220px; height: 220px; top: 40%; left: 45%; background: #e2e8f0; opacity: .4; }

.wrap { position: relative; width: 100%; max-width: 400px; animation: rise .4s ease-out both; }
@keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

.card {
    background: rgba(255,255,255,.92); border: 1px solid rgba(226,232,240,.9);
    border-radius: 28px; box-shadow: 0 24px 60px -18px rgba(15,23,42,.18);
    overflow: hidden; backdrop-filter: blur(14px);
}
.head { position: relative; padding: 26px 22px 20px; text-align: center; color: #fff;
    background: linear-gradient(140deg, #d62976, #8d1b50);
    background: linear-gradient(140deg, var(--lg-primary), color-mix(in srgb, var(--lg-primary) 72%, #000)); }
.head::after { content: ""; position: absolute; inset: 0; opacity: .22;
    background: radial-gradient(140px 90px at 82% 12%, rgba(255,255,255,.75), transparent 70%); }
.head .icon { position: relative; margin: 0 auto 10px; width: 52px; height: 52px; border-radius: 17px;
    display: grid; place-items: center; background: rgba(255,255,255,.16); box-shadow: inset 0 0 0 1px rgba(255,255,255,.28); }
.head h1 { position: relative; font-size: 15px; font-weight: 800; letter-spacing: -.2px; }
.head p { position: relative; margin-top: 5px; font-size: 10.5px; line-height: 1.9; color: rgba(255,255,255,.85); }
.badge { position: relative; display: inline-flex; align-items: center; gap: 6px; margin-top: 12px;
    padding: 5px 12px; border-radius: 999px; font-size: 9.5px; font-weight: 800;
    background: rgba(255,255,255,.16); box-shadow: inset 0 0 0 1px rgba(255,255,255,.3); }
.dot { width: 6px; height: 6px; border-radius: 999px; background: #6ee7b7; box-shadow: 0 0 0 3px rgba(110,231,183,.25); }

.body { padding: 20px 18px 18px; }
.rows { display: grid; gap: 9px; }
.row { display: flex; align-items: center; justify-content: space-between; gap: 10px;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 11px 14px; }
.row .lbl { font-size: 10.5px; font-weight: 700; color: #94a3b8; }
.row .val { font-size: 12px; font-weight: 800; color: #0f172a; }
.row.amount { background: linear-gradient(135deg, #fff1f7, #fdf4ff); border-color: #fbcfe8; }
.row.amount .val { font-size: 16.5px; font-weight: 900; color: var(--lg-primary); }
.row.amount .unit { font-size: 10px; font-weight: 700; color: #94a3b8; margin-inline-start: 4px; }

.mcard { margin-top: 14px; border-radius: 18px; padding: 14px 16px; color: #fff; direction: ltr;
    background: linear-gradient(120deg, #1e293b, #334155 55%, #475569);
    box-shadow: 0 14px 28px -14px rgba(15,23,42,.55); }
.mcard .top { display: flex; align-items: center; justify-content: space-between; }
.chip { width: 30px; height: 22px; border-radius: 6px; background: linear-gradient(135deg, #fbbf24, #f59e0b); position: relative; overflow: hidden; }
.chip::after { content: ""; position: absolute; inset: 5px 6px; border: 1px solid rgba(120,53,15,.5); border-radius: 3px; }
.net { font-size: 9px; font-weight: 800; letter-spacing: 1.5px; color: #cbd5e1; }
.mcard .no { margin-top: 12px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 13px; font-weight: 700; letter-spacing: 3px; color: #f1f5f9; }
.mcard .sub { display: flex; justify-content: space-between; margin-top: 10px; font-size: 8.5px; font-weight: 700; color: #94a3b8; letter-spacing: 1px; }

.btns { display: grid; gap: 10px; margin-top: 18px; }
button { font-family: inherit; width: 100%; border: 0; border-radius: 16px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 14px 18px; font-size: 12.5px; font-weight: 800; transition: transform .15s ease, box-shadow .2s ease, opacity .2s; }
button:active { transform: scale(.98); }
button[disabled] { opacity: .65; pointer-events: none; }
.pay { color: #fff; background: linear-gradient(135deg, #059669, #10b981); box-shadow: 0 14px 26px -12px rgba(5,150,105,.6); }
.pay:hover { box-shadow: 0 16px 30px -12px rgba(5,150,105,.75); }
.cancel { color: #e11d48; background: #fff; border: 1.5px solid #fecdd3; }
.cancel:hover { background: #fff1f2; }
.spin { width: 15px; height: 15px; border-radius: 999px; border: 2px solid rgba(255,255,255,.45); border-top-color: #fff; animation: rot .7s linear infinite; display: none; }
.cancel .spin { border-color: rgba(225,29,72,.3); border-top-color: #e11d48; }
button[disabled] .spin { display: inline-block; }
button[disabled] .ic { display: none; }
@keyframes rot { to { transform: rotate(360deg); } }

.foot { margin-top: 15px; padding: 0 6px; text-align: center; font-size: 9.5px; font-weight: 700; line-height: 2; color: #94a3b8; }
.foot .lock { display: inline-flex; vertical-align: -3px; margin-inline-end: 4px; color: #10b981; }
</style>
</head>
<body>
<span class="blob b1"></span><span class="blob b2"></span><span class="blob b3"></span>

<div class="wrap">
    <div class="card">
        <div class="head">
            <span class="icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
            </span>
            <h1><?= $in('title', 'درگاه پرداخت آزمایشی') ?></h1>
            <p><?= $in('description', 'این درگاه صرفاً برای تست روند خرید است و پول واقعی جابه‌جا نمی‌شود.') ?></p>
            <span class="badge"><span class="dot"></span>حالت تست — بدون تراکنش واقعی</span>
        </div>

        <div class="body">
            <div class="rows">
                <div class="row">
                    <span class="lbl"><?= $in('orderLabel', 'شماره سفارش') ?></span>
                    <span class="val"><?= $orderNo ?></span>
                </div>
                <div class="row amount">
                    <span class="lbl"><?= $in('amountLabel', 'مبلغ قابل پرداخت') ?></span>
                    <span class="val"><?= htmlspecialchars($amountFa, ENT_QUOTES, 'UTF-8') ?><span class="unit">تومان</span></span>
                </div>
            </div>

            <div class="mcard" aria-hidden="true">
                <div class="top"><span class="chip"></span><span class="net">TEST CARD</span></div>
                <div class="no">****&nbsp;****&nbsp;****&nbsp;4242</div>
                <div class="sub"><span>INSTASHOP&nbsp;SANDBOX</span><span>12/36</span></div>
            </div>

            <div class="btns">
                <form action="<?= $in('successUrl') ?>" method="post" id="success_form"></form>
                <form action="<?= $in('cancelUrl') ?>" method="post" id="cancel_form"></form>

                <button type="button" id="success" class="pay">
                    <span class="spin"></span>
                    <svg class="ic" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
                    <?= $in('payButton', 'پرداخت موفق (تست)') ?>
                </button>
                <button type="button" id="cancel" class="cancel">
                    <span class="spin"></span>
                    <svg class="ic" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    <?= $in('cancelButton', 'انصراف / پرداخت ناموفق') ?>
                </button>
            </div>

            <p class="foot">
                <span class="lock"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                این صفحه یک شبیه‌ساز بانکی است؛ هیچ مبلغی از هیچ کارتی کسر نمی‌شود.
            </p>
        </div>
    </div>
</div>

<script>
(function () {
    var busy = false;
    function submit(id) {
        if (busy) return;
        busy = true;
        var b = document.getElementById(id);
        var other = document.getElementById(id === 'success' ? 'cancel' : 'success');
        if (b) { b.disabled = true; }
        if (other) { other.disabled = true; }
        var f = document.getElementById(id + '_form');
        if (f) { f.submit(); }
    }
    var s = document.getElementById('success'), c = document.getElementById('cancel');
    if (s) s.addEventListener('click', function () { submit('success'); });
    if (c) c.addEventListener('click', function () { submit('cancel'); });
})();
</script>
</body>
</html>
