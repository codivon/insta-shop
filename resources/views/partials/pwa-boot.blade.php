{{-- ═══════════════════════════════════════════════════════════
  PWA boot splash — the professional «app is opening» cover.

  Chrome paints its native splash (bg + icon) only until the first
  paint; after that the user used to stare at an empty white page
  while the panel booted (client: «صفحهٔ لودینگ ورود به اپ فروشنده
  اصلاً خوب نیست — کلاً تغییرش بده، ظاهر زیبا و مدرن + لودینگ حرفه‌ای»
  و «لودینگ ورود اپ مشتری هم درست کن»).

  This cover paints INSTANTLY (inline critical CSS, no bundle needed)
  and continues the native splash seamlessly: same white background,
  the (new, smaller) brand icon floating over a soft gradient halo,
  app name, three bouncing brand dots and a slim gradient progress
  bar on the bottom edge. Fades out as soon as the panel is ready —
  min ~0.9s so it never flashes, hard cap 3.2s so it never traps.

  Shown only for real app-opens: start_url carries ?source=pwa, or
  the page runs in standalone display-mode. Plain browser visits
  never see it. Include right after <body> in the seller/customer
  layouts with the panel role.
═══════════════════════════════════════════════════════════ --}}
@php
    $bootRole = $role ?? (\App\Support\PanelContext::current()->key() === 'seller' ? 'seller' : 'customer');
    $bootIsSeller = $bootRole === 'seller';
    $bootApp = \App\Support\Theme::resolve()['app_name'];
    $bootTitle = $bootIsSeller ? 'اپ فروشنده' : $bootApp;
    $bootSub = $bootIsSeller ? 'مدیریت فروشگاه، همیشه در جیب شما' : 'خرید آسان از فروشگاه‌های اینستاگرامی';
    $bootIcon = "/pwa/icons/{$bootRole}-192.png";
    // presence check — NOT boolean('source'): filter_var('pwa', FILTER_VALIDATE_BOOLEAN) is false
    $bootViaPwa = request()->filled('source');
@endphp
<div id="ip-boot" class="ipboot" {{ $bootViaPwa ? '' : 'hidden' }} aria-hidden="true">
    <div class="ipboot-halo">
        <img src="{{ $bootIcon }}" alt="" width="92" height="92" class="ipboot-icon" decoding="async">
    </div>
    <div class="ipboot-name">{{ $bootTitle }}</div>
    <div class="ipboot-sub">{{ $bootSub }}</div>
    <div class="ipboot-dots">
        <span class="ipboot-dot"></span>
        <span class="ipboot-dot"></span>
        <span class="ipboot-dot"></span>
    </div>
    <div class="ipboot-bar"></div>
</div>
<style>
    .ipboot{position:fixed;inset:0;z-index:99999;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#ffffff;transition:opacity .38s ease,visibility .38s ease}
    .ipboot[hidden]{display:none}
    .ipboot.ipboot-done{opacity:0;visibility:hidden;pointer-events:none}
    @media (prefers-color-scheme: dark){.ipboot{background:#020617}}
    .ipboot-halo{position:relative;display:grid;place-items:center}
    .ipboot-halo::before{content:'';position:absolute;inset:-20px;border-radius:40px;background:linear-gradient(135deg,rgba(250,126,30,.20),rgba(214,41,118,.22),rgba(79,91,213,.20));filter:blur(20px);animation:ipboot-pulse 2.6s ease-in-out infinite}
    .ipboot-icon{width:92px;height:92px;border-radius:26px;box-shadow:0 22px 44px -14px rgba(214,41,118,.45);animation:ipboot-float 2.6s ease-in-out infinite;position:relative}
    @keyframes ipboot-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
    @keyframes ipboot-pulse{0%,100%{opacity:.65;transform:scale(.94)}50%{opacity:1;transform:scale(1.05)}}
    .ipboot-name{margin-top:24px;font-size:19px;font-weight:800;color:#0f172a;font-family:Vazirmatn,system-ui,sans-serif}
    .ipboot-sub{margin-top:6px;font-size:12.5px;color:#64748b;font-family:Vazirmatn,system-ui,sans-serif}
    @media (prefers-color-scheme: dark){.ipboot-name{color:#f1f5f9}.ipboot-sub{color:#94a3b8}}
    .ipboot-dots{margin-top:28px;display:flex;gap:8px}
    .ipboot-dot{width:9px;height:9px;border-radius:9999px;animation:ipboot-bounce 1.2s ease-in-out infinite}
    .ipboot-dot:nth-child(1){background:#fa7e1e}
    .ipboot-dot:nth-child(2){background:#d62976;animation-delay:.15s}
    .ipboot-dot:nth-child(3){background:#962fbf;animation-delay:.3s}
    @keyframes ipboot-bounce{0%,100%{transform:translateY(0);opacity:.5}50%{transform:translateY(-8px);opacity:1}}
    .ipboot-bar{position:absolute;right:0;bottom:0;left:0;bottom:env(safe-area-inset-bottom,0px);height:3px;overflow:hidden}
    .ipboot-bar::before{content:'';position:absolute;inset:0;transform:translateX(-100%);background:linear-gradient(90deg,transparent,#fa7e1e,#d62976,#962fbf,transparent);animation:ipboot-slide 1.15s linear infinite}
    @keyframes ipboot-slide{to{transform:translateX(100%)}}
    @media (prefers-reduced-motion: reduce){.ipboot-halo::before,.ipboot-icon,.ipboot-dot,.ipboot-bar::before{animation:none}}
</style>
<script>
(function () {
    'use strict';
    var el = document.getElementById('ip-boot');
    if (!el) return;

    // standalone display-mode (installed app / opened from the home
    // screen) also counts as a real app-open, even when a redirect
    // stripped ?source=pwa on the way to the panel
    var standalone = window.matchMedia && window.matchMedia('(display-mode: standalone)').matches;
    if (el.hasAttribute('hidden')) {
        if (standalone) { el.removeAttribute('hidden'); } else { el.remove(); return; }
    }

    var t0 = Date.now();
    var done = false;
    var readyFired = false;
    var minDone = false;
    var MIN = 900, MAX = 3200;

    function dismiss() {
        if (done) return;
        done = true;
        el.classList.add('ipboot-done');
        setTimeout(function () { el.remove(); }, 450);
    }
    function tryDismiss() { if (readyFired && minDone) dismiss(); }

    window.__ipboot = { dismiss: dismiss };

    setTimeout(function () { minDone = true; tryDismiss(); }, MIN);

    // Livewire fires navigated on the FIRST render too — the panel is up
    document.addEventListener('livewire:navigated', function () { readyFired = true; tryDismiss(); }, { once: true });

    // no Livewire (or slow) — window load + small grace still dismisses
    window.addEventListener('load', function () {
        setTimeout(function () { readyFired = true; tryDismiss(); }, 300);
    });

    // never trap the user behind the cover
    setTimeout(dismiss, MAX);
})();
</script>
