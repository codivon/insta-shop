{{-- ═══════════════════════════════════════════════════════════════════
  Admin SELF-CONTAINED SHELL CSS (v0.17.19)
  ─────────────────────────────────────────────────────────────────
  WHY THIS EXISTS: three releases (v0.17.16→18) proved the client's
  phone applies the Tailwind bundle only PARTIALLY — colors/gradients/
  radius survive while position / width / grid-template-columns /
  translate are dropped (host HTML + CSS verified byte-identical to the
  repo, so the loss happens on that device/browser). Every "works in my
  browser" release kept breaking THERE.

  THE NEW APPROACH: the admin shell no longer DEPENDS on the compiled
  bundle for its structure. This hand-written stylesheet:
    • ships INLINE in <head> (nothing to 404, truncate or serve stale),
    • uses ONLY old-browser-safe syntax (no @layer, no @property, no
      oklch/color-mix, no logical properties, no `translate` property
      as the sole mechanism — transform is always set alongside),
    • re-specifies every structural decision the shell makes (sidebar,
      drawer state, overlay, content offset, topbar, hub tiles, table
      frames) with the SAME values Tailwind uses, so a healthy bundle
      renders identically and a broken bundle still yields a correct,
      usable panel,
    • wins the cascade where it matters (ID selectors + !important on
      the few properties that must never flip).

  It is included ONLY by layouts.admin → scoped to every admin page.
═══════════════════════════════════════════════════════════════════ --}}
<style>
/* ── 0 · page-level guards (admin documents only) ─────────────── */
html[data-panel="admin"],
html[data-panel="admin"] body {
    max-width: 100%;
    overflow-x: hidden;
}
html[data-panel="admin"] body {
    margin: 0;
    background: #f1f5f9; /* slate-100 — the body bg, bundle or not */
}
html[data-panel="admin"].dark body {
    background: #020617; /* slate-950 */
}
html[data-panel="admin"] body,
#admin-sidebar,
#admin-topbar {
    font-family: Vazirmatn, "Segoe UI", Tahoma, sans-serif;
}

/* ── 1 · content shell (the column that holds topbar/main/footer) ── */
#admin-shell {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    min-height: 100dvh;
    width: 100%;
    max-width: 100%;
    min-width: 0;
}
#admin-shell > main {
    flex: 1 1 auto;
    padding: 16px;
    min-width: 0;
    max-width: 100%;
}
#admin-shell > footer {
    margin-top: auto;
    padding: 16px 24px;
    border-top: 1px solid #e2e8f0;
    text-align: center;
    font-size: 12px;
    color: #94a3b8;
}
html[data-panel="admin"].dark #admin-shell > footer {
    border-top-color: #1e293b;
}
@media (min-width: 640px) {
    #admin-shell > main { padding: 24px; }
}

/* ── 2 · sidebar (right, RTL) — closed drawer on phones, pinned on desktop ── */
#admin-sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    right: 0;
    z-index: 50;
    display: flex;
    flex-direction: column;
    width: 260px !important;
    max-width: 88vw;
    background: #fff;
    border-left: 1px solid #e2e8f0;
    /* transform carries the hide state on EVERY engine; the modern
       `translate` property (Tailwind v4's mechanism) is reset in the
       open/desktop rules so the two can never fight */
    transform: translateX(100%);
}
html[data-panel="admin"].dark #admin-sidebar {
    background: #0f172a;
    border-left-color: #1e293b;
}
#admin-sidebar nav {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
    margin-top: 16px;
    padding: 0 12px 16px;
    min-height: 0;
}
#admin-side-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    height: 64px;
    padding: 0 20px;
    border-bottom: 1px solid #e2e8f0;
    flex: 0 0 auto;
}
html[data-panel="admin"].dark #admin-side-head {
    border-bottom-color: #1e293b;
}
#admin-side-head img {
    width: 36px;
    height: 36px;
    object-fit: contain;
    border-radius: 12px;
    flex: 0 0 auto;
}
#admin-sidebar > div:last-child {
    flex: 0 0 auto;
    border-top: 1px solid #e2e8f0;
    padding: 16px;
}
html[data-panel="admin"].dark #admin-sidebar > div:last-child {
    border-top-color: #1e293b;
}
/* nav rows — the layout half of .admin-nav-item (its COLORS live in
   app.css unlayered and survive any bundle loss) */
#admin-sidebar .admin-nav-item,
#admin-sidebar .admin-nav-sub {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
    padding: 10px 12px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 20px;
    text-decoration: none;
    min-width: 0;
}
#admin-sidebar .admin-nav-sub {
    justify-content: flex-start;
    padding: 8px 12px;
    font-size: 12.5px;
}
#admin-sidebar .admin-nav-item > span:first-child,
#admin-sidebar .admin-nav-sub > span:first-child {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
#admin-sidebar .admin-nav-badge,
#admin-sidebar .admin-nav-chip {
    flex: 0 0 auto;
    padding: 2px 6px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
}
#admin-sidebar .ip-avatar {
    width: 36px;
    height: 36px;
    flex: 0 0 auto;
    border-radius: 9999px;
}

/* ── 3 · drawer state (single class on <html>, set by the inline script) ── */
@media (max-width: 1023.98px) {
    html.admin-drawer-open #admin-sidebar {
        transform: translateX(0);
        translate: 0 0; /* neutralize Tailwind v4's translate-based hiding */
    }
    html.admin-drawer-open #admin-sidebar-overlay {
        opacity: 1;
        pointer-events: auto;
    }
    html.admin-drawer-open,
    html.admin-drawer-open body {
        overflow: hidden;
    }
}
@media (min-width: 1024px) {
    #admin-sidebar {
        transform: none;
        translate: none;
    }
    #admin-sidebar-overlay {
        display: none;
    }
    /* desktop: the column leaves room for the pinned sidebar */
    #admin-shell {
        margin-right: 260px;
    }
}

/* ── 4 · dim overlay ── */
#admin-sidebar-overlay {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 40;
    background: rgba(2, 6, 23, 0.4); /* slate-950/40 — matches bg-slate-950/40 */
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

/* ── 5 · topbar ──
   NOTE: icon sizes are NOT re-specified here on purpose — the icon
   component ships intrinsic width/height attributes (lose to ANY css
   rule), so a dead bundle still gets sane 20px icons while a live one
   keeps every size-* utility untouched. -- */
#admin-topbar {
    position: sticky;
    top: 0;
    z-index: 30;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    flex: 0 0 auto;
}
html[data-panel="admin"].dark #admin-topbar {
    background: #0f172a;
    border-bottom-color: #1e293b;
}
#admin-topbar > div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    height: 64px;
    padding: 0 16px;
    min-width: 0;
}
#admin-topbar > div > div {
    min-width: 0;
}
#admin-topbar .ip-crumb svg[data-icon] {
    width: 12px;
    height: 12px;
}
@media (min-width: 640px) {
    #admin-topbar > div { padding: 0 24px; }
}

/* ── 6 · settings hub section tiles — ALWAYS 2 compact columns on
      phones (the reported «تب‌ها بهم ریخته» comes from this grid) ── */
[data-hub-tiles] {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px;
}
[data-hub-tile] {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    width: 100%;
    min-width: 0;
    padding: 12px 8px;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
}
[data-hub-tile] > span:last-of-type {
    min-width: 0;
    width: 100%;
}
[data-hub-tile-desc] {
    display: none;
}
@media (min-width: 1024px) {
    [data-hub-tiles] {
        display: flex !important;
        flex-direction: column;
        gap: 0;
    }
    [data-hub-tile] {
        flex-direction: row;
        gap: 12px;
        padding: 10px 12px;
        text-align: right;
    }
    [data-hub-tile-desc] {
        display: block;
    }
}

/* ── 7 · table / scroll frames — horizontal scroll stays INSIDE the
      frame, never widens the page (SMS logs, notifications, …) ── */
[data-hub-section] {
    min-width: 0;
    max-width: 100%;
}
[data-table-frame] {
    min-width: 0;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
}
/* :where() keeps this a pure FALLBACK — matches the views' min-w-[720px];
   (unlayered rules beat the layered bundle anyway, so the value must be
   identical to keep healthy rendering pixel-identical) */
:where([data-table-frame]) table {
    min-width: 720px;
}
[data-table-frame] th,
[data-table-frame] td {
    overflow-wrap: anywhere;
}

/* generic bleed guards for the whole admin content column */
#admin-shell img,
#admin-shell iframe,
#admin-shell video {
    max-width: 100%;
}
#admin-shell input,
#admin-shell select,
#admin-shell textarea {
    max-width: 100%;
}

/* ── 8 · stylesheet-health probe banner (filled by the inline script
      at the end of the layout — visible ONLY when the Tailwind bundle
      failed to apply on this device, e.g. the client's phone) ── */
#ip-css-probe-banner {
    display: none;
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    z-index: 90;
    padding: 10px 14px;
    background: #fef3c7;
    color: #92400e;
    border-bottom: 1px solid #f59e0b;
    font-size: 12px;
    line-height: 18px;
    text-align: center;
}
#ip-css-probe-banner.ip-css-broken {
    display: block;
}
#ip-css-probe-banner button {
    margin-right: 8px;
    padding: 2px 10px;
    border: 1px solid #f59e0b;
    border-radius: 8px;
    background: #fffbeb;
    color: #92400e;
    font: inherit;
    cursor: pointer;
}
</style>
