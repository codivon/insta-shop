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
/* ── 0 · page-level guards (admin documents only) ───────────────
   SCROLL REGRESSION NOTE (v0.17.20): v0.17.19 guarded horizontal bleed
   with `overflow-x: hidden` here. Being INLINE + unlayered it OVERRIDES
   the bundle's scroll-safe `overflow-x: clip` (app.css) on BOTH html and
   body — and on the client's phone that froze ALL vertical scrolling on
   every admin page («هیچ صفحه اسکرول نمیشه، محتویات ثابته»). Healthy
   Chrome tolerates root-level overflow-x:hidden, that phone's engine
   does NOT. `clip` NEVER creates a scroll container and never freezes
   page scroll on any engine; engines without clip ignore the declaration
   (worst case: cosmetic sideways panning). ── */
html[data-panel="admin"],
html[data-panel="admin"] body {
    max-width: 100%;
    overflow-x: clip;
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
    /* width:auto (NOT 100%): on ≥1024px the shell carries margin-right:
       260px for the pinned sidebar — width:100% ignored the margin and
       pushed a phantom 260px column past the document's left edge in
       RTL (width:auto correctly subtracts the margin). */
    width: auto;
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
        visibility: visible;
        pointer-events: auto;
    }
    /* NO body scroll-lock while the drawer is open (v0.17.20): a stuck
       `overflow: hidden` on html/body — one dropped close event on a
       quirky engine is enough — freezes EVERY admin page. The dim
       overlay already communicates modality; background scroll behind
       it is harmless. */
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
    /* belt & suspenders: a hidden overlay can NEVER intercept touches,
       even on engines that mishandle pointer-events (the scroll-killer
       class of bugs) — visibility fades with the opacity */
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.3s ease, visibility 0.3s;
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
      phones (the reported «تب‌ها بهم ریخته» comes from this grid)
      ────────────────────────────────────────────────────────────────
      v0.17.21 — ENGINE-PROOF REWRITE. The old version (a) fought the
      bundle with a display:grid↔flex switch across breakpoints and
      (b) styled the text wrapper via `> span:last-of-type`, which ALSO
      matched the active tile's check badge — result on the client's
      desktop (and in healthy Chrome too, verified!): the title collapsed
      to zero width and the 20px check circle stretched into a full-
      width white pill hanging out of the card. The rewrite:
        • the container is ALWAYS grid — only the COLUMN TEMPLATE changes
          per breakpoint (a lost desktop rule degrades to 2-col tiles,
          never to a broken strip),
        • every styled node carries an explicit data hook from the blade
          (data-hub-tile-body / data-hub-tile-check / data-hub-tile-active),
        • the active state is fully re-specified server-side (no JS,
          no bundle needed). ── */
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
    margin: 0;
    padding: 12px 8px;
    border: 0;
    border-radius: 12px;
    box-sizing: border-box;
    text-align: center;
    font: inherit;
    color: inherit;
    cursor: pointer;
}
/* icon box — 36px grid square (identical to size-9 place-items-center
   rounded-lg so a healthy bundle renders unchanged, a dead one still
   gets a sane square instead of a stretched monster) */
[data-hub-tile] > span:first-child {
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    flex: 0 0 auto;
    border-radius: 8px;
}
/* text wrapper — THE fix for the collapsed title (the old rule hit the
   check badge too via span:last-of-type) */
[data-hub-tile-body] {
    display: block;
    min-width: 0;
    width: 100%;
}
[data-hub-tile-body] > span:first-child {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 11px;
    line-height: 20px;
    font-weight: 800;
}
[data-hub-tile-desc] {
    display: none;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-top: 2px;
    font-size: 10px;
}
/* check badge — hidden on phones, a FIXED 20px circle on desktop
   (never allowed to grow: min/max width are pinned) */
[data-hub-tile-check] {
    display: none;
}
/* active tile — server-rendered attribute (data-hub-tile-active), so it
   works even when Alpine/the bundle never boots on a device; values are
   identical to the bundle's utilities → healthy rendering unchanged */
[data-hub-tile-active="true"] {
    background: var(--ip-primary-600, #db2777);
    color: #fff;
}
[data-hub-tile-active="true"] > span:first-child {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
}
[data-hub-tile-active="true"] [data-hub-tile-body] > span:first-child {
    color: #fff;
}
[data-hub-tile-active="true"] [data-hub-tile-desc] {
    color: rgba(255, 255, 255, 0.7);
}
@media (min-width: 1024px) {
    /* template-only switch — grid stays grid, there is no display:flex
       for a broken engine to lose: worst case (rule dropped) the tiles
       simply remain the 2-column phone grid, which is still correct */
    [data-hub-tiles],
    #settings-sections [data-hub-tiles] {
        grid-template-columns: none !important;
        gap: 0;
    }
    [data-hub-tile],
    #settings-sections [data-hub-tile] {
        flex-direction: row !important;
        flex-wrap: nowrap;
        justify-content: flex-start;
        gap: 12px;
        padding: 10px 12px;
        text-align: right;
    }
    [data-hub-tile-body] > span:first-child {
        font-size: 12px;
    }
    [data-hub-tile-desc] {
        display: block;
    }
    [data-hub-tile-check],
    #settings-sections [data-hub-tile-check] {
        display: grid !important;
        place-items: center;
        flex: 0 0 auto;
        width: 20px !important;
        height: 20px !important;
        min-width: 20px !important;
        max-width: 20px !important;
        min-height: 20px;
        margin-inline-start: auto;
        border-radius: 9999px;
        box-sizing: border-box;
        background: #fff;
        color: var(--ip-primary-600, #db2777);
    }
    /* the two-column hub layout — the bundle's arbitrary
       lg:grid-cols-[260px_minmax(0,1fr)] is a layout utility that has
       already died on the client's devices */
    [data-hub-grid] {
        grid-template-columns: 260px minmax(0, 1fr) !important;
    }
    /* bundle lg:sticky lg:top-20, re-specified for dead-bundle engines */
    #settings-sections {
        position: sticky;
        top: 80px;
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
