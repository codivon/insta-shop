
//
// InstaShop — front-end runtime
//

// Jalali datepicker engine (x-jdate-picker)
import './jdate';

// «فروش ویژه» live countdown (special-sale deadline, window.sfCountdown)
import './countdown';

// Touch-swipe product gallery + fullscreen zoom modal (window.productGallery)
import './product-gallery';

// ── Theme mode toggle (dark / light) ─────────────────────────────────
document.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-theme-toggle]');
    if (!btn) return;

    const root = document.documentElement;
    const isDark = root.classList.toggle('dark');

    try {
        localStorage.setItem('ip-mode', isDark ? 'dark' : 'light');
    } catch (e) { /* private mode */ }

    window.dispatchEvent(new CustomEvent('ip-mode-changed', { detail: { dark: isDark } }));
});

// ── Panel-aware Livewire requests ────────────────────────────────────
// Livewire AJAX goes to the prefix-less /livewire/update endpoint. The
// backend (ResolvePanel middleware) resolves the panel session from this
// header so auth stays inside the panel the user is actually browsing.
document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ options }) => {
        options.headers = {
            ...(options.headers || {}),
            'X-Instashop-Panel': document.documentElement.dataset.panel || 'landing',
        };
    });
});

// ── Theme mode persistence across wire:navigate ──────────────────────
// Livewire navigate swaps the <html> attributes from the fetched page,
// which is server-rendered without the .dark class — so the mode reset to
// light on every SPA navigation. Re-apply the stored choice after each
// navigation (and right after the initial load, harmlessly).
const applyStoredThemeMode = () => {
    try {
        const mode = localStorage.getItem('ip-mode');

        document.documentElement.classList.toggle('dark', mode === 'dark');
    } catch (e) { /* private mode */ }
};

applyStoredThemeMode();
document.addEventListener('livewire:navigated', applyStoredThemeMode);

// ── Cart badge live update (everywhere) ──────────────────────────────
// Livewire cart actions dispatch a browser event (on window); EVERY badge
// on the page — storefront header, product-page header, customer tab bar —
// updates without a reload. Listener lives in app.js because wire:navigate
// does not re-execute page-level inline scripts.
window.addEventListener('cart-changed', (event) => {
    const count = Number(event.detail?.count ?? 0);

    document.querySelectorAll('[data-cart-badge]').forEach((badge) => {
        badge.dataset.count = String(count);
        badge.textContent = count.toLocaleString('fa-IR');
        badge.classList.toggle('hidden', count <= 0);

        // tiny pop so the change is felt, not just seen
        badge.classList.remove('cart-pop');
        void badge.offsetWidth; // restart the animation
        badge.classList.add('cart-pop');
    });
});

// ── Global loading feedback (every panel) ────────────────────────────
// The user asked for loading feedback on the PAGE and on the BUTTON they
// clicked, for every clickable thing in every panel, until the content is
// fully loaded. One shared runtime does it all:
//
//   1. #ip-loadbar  — slim indeterminate progress bar on top, visible for
//                     EVERY Livewire request (commits included).
//   2. .ip-busy     — the element that was actually clicked gets a busy
//                     treatment (inline spinner + muted + inert) for the
//                     duration of that request.
//   3. #ip-navveil  — soft full-viewport veil while a wire:navigate page
//                     transition fetches the next document.
//
// The DOM nodes are injected once from JS, so no layout file needs edits.
// Snapshot-safe: wire:navigate stores the WHOLE <body> in the history
// snapshot and later restores it with document.body.replaceWith(...) —
// so our injected nodes (and any .ip-on/.ip-busy state baked into the
// snapshot) come back on every back/forward, and stale element references
// die. Every operation therefore re-queries the live DOM and never trusts
// a captured node.
(function () {
    const ensureNodes = () => {
        if (!document.getElementById('ip-loadbar')) {
            const bar = document.createElement('div');
            bar.id = 'ip-loadbar';
            document.body.appendChild(bar);
        }
        if (!document.getElementById('ip-navveil')) {
            const veil = document.createElement('div');
            veil.id = 'ip-navveil';
            veil.setAttribute('aria-hidden', 'true');
            veil.innerHTML = '<div class="ip-navveil-spin"></div>';
            document.body.appendChild(veil);
        }
    };

    /* the element the user interacted with last (capture phase, before
       Livewire sees the click) */
    let origin = null;

    document.addEventListener('pointerdown', (e) => {
        origin = e.target.closest('button, a, [role="button"], label, summary, [wire\\:click]') || null;
    }, { capture: true });

    /* hide EVERYTHING, wherever it currently lives — including nodes that
       just came back from a history snapshot with .ip-on baked in */
    const resetOverlays = () => {
        clearTimeout(veilTimer);
        document.getElementById('ip-loadbar')?.classList.remove('ip-on');
        document.getElementById('ip-navveil')?.classList.remove('ip-on');
        document.querySelectorAll('.ip-busy').forEach((el) => el.classList.remove('ip-busy'));
    };

    let veilTimer = null;

    const showVeil = () => {
        ensureNodes();
        document.getElementById('ip-navveil').classList.add('ip-on');
        // safety net: if the navigation never completes (the fetch failed
        // while the browser still reports "online"), take the veil back
        // off so the user is never trapped behind a frozen loader.
        clearTimeout(veilTimer);
        veilTimer = setTimeout(resetOverlays, 8000);
    };

    let inflight = 0;

    document.addEventListener('livewire:init', () => {
        // every Livewire request (wire:click, wire:model.live commits, …)
        Livewire.hook('request', ({ respond }) => {
            inflight += 1;
            ensureNodes();
            document.getElementById('ip-loadbar').classList.add('ip-on');
            // the LAST pointerdown before this request wins the busy mark
            if (origin && origin.isConnected) {
                origin.classList.add('ip-busy');
            }
            origin = null;

            respond(() => {
                inflight = Math.max(0, inflight - 1);
                if (inflight === 0) {
                    document.getElementById('ip-loadbar')?.classList.remove('ip-on');
                    document.querySelectorAll('.ip-busy').forEach((el) => el.classList.remove('ip-busy'));
                }
            });
        });

        // wire:navigate page transitions
        document.addEventListener('livewire:navigate', showVeil);

        document.addEventListener('livewire:navigated', () => {
            inflight = 0;
            resetOverlays();
        });

        // safety: if a request errors out without respond() firing cleanly
        window.addEventListener('offline', () => {
            inflight = 0;
            resetOverlays();
        });
    });

    // Back/forward restores swap <body> out from under us (snapshot may
    // carry baked .ip-on/.ip-busy) and bfcache restores can skip Livewire
    // events entirely — so clear everything on pageshow, ALWAYS.
    window.addEventListener('pageshow', () => {
        inflight = 0;
        resetOverlays();
    });
})();

/* ═══════════════════════════════════════════════════════════════
   Category picker (product forms) — Basalam-style bottom sheet.
   One field opens a sheet with live client-side search over the
   WHOLE admin-managed category tree (JSON rendered once into
   <script type="application/json" x-ref="cat-tree">), expandable
   branches with «N زیرشاخه» badges + dashed connectors, and a
   single-pick (radio) selection confirmed by the «تایید» bar.
   ═══════════════════════════════════════════════════════════════ */
document.addEventListener('alpine:init', () => {
    /* Persian search normalizer: Arabic ي/ك → Persian ی/ک, ZWNJ → space */
    const normFa = (s) => (s || '')
        .replace(/ي/g, 'ی')
        .replace(/ك/g, 'ک')
        .replace(/ۀ/g, 'ه\u200cی')
        .replace(/\u200c/g, ' ')
        .toLowerCase()
        .trim();

    /* ۱۲۳ → Persian digits (badges + counter, no Intl dependency) */
    const faDigits = (n) => String(n).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);

    Alpine.data('ipCatPicker', () => ({
        open: false,
        query: '',
        picked: null,      // preview selection (id) — committed by «تایید»
        expanded: {},      // branch id → true
        tree: [],          // nested [{i, n, c}]
        index: new Map(),  // id → { id, name, path, parentId, children }

        init() {
            try {
                this.tree = JSON.parse(this.$refs['cat-tree']?.textContent || '[]');
            } catch (e) {
                this.tree = [];
            }

            const index = this.index;
            const walk = (nodes, parentId, path, depth) => {
                (nodes || []).forEach((node) => {
                    const nodePath = path ? path + ' › ' + node.n : node.n;

                    index.set(node.i, {
                        id: node.i,
                        name: node.n,
                        path: nodePath,
                        parentId,
                        depth,
                        children: node.c || [],
                    });

                    walk(node.c, node.i, nodePath, depth + 1);
                });
            };

            walk(this.tree, null, '', 0);
        },

        faNum: (n) => faDigits(n),

        toggle(id) {
            this.expanded[id] = !this.expanded[id];
        },

        pick(id) {
            this.picked = id;
        },

        /* open the ancestors of the picked node so it is visible at once */
        expandPathTo(id) {
            let current = this.index.get(id);

            while (current && current.parentId !== null) {
                this.expanded[current.parentId] = true;
                current = this.index.get(current.parentId);
            }
        },

        openPicker() {
            this.query = '';
            this.picked = this.$wire.categoryId > 0 ? this.$wire.categoryId : null;
            this.expanded = {};
            this.expandPathTo(this.picked);
            this.open = true;
        },

        confirmPick() {
            if (this.picked === null) {
                return;
            }

            this.$wire.set('categoryId', this.picked);
            this.open = false;
        },

        get searching() {
            return this.query.trim() !== '';
        },

        /* flat match list (name or full path contains the query) */
        get searchRows() {
            const q = normFa(this.query);

            if (q === '') {
                return [];
            }

            const out = [];

            for (const node of this.index.values()) {
                if (normFa(node.name).includes(q) || normFa(node.path).includes(q)) {
                    out.push({ id: node.id, name: node.name, path: node.path });

                    if (out.length >= 80) {
                        break;
                    }
                }
            }

            return out;
        },

        /* depth-first visible rows of the expanded tree */
        get treeRows() {
            const rows = [];
            const walk = (nodes, depth) => {
                (nodes || []).forEach((node) => {
                    rows.push({
                        id: node.i,
                        name: node.n,
                        depth,
                        childCount: (node.c || []).length,
                    });

                    if (this.expanded[node.i] && (node.c || []).length) {
                        walk(node.c, depth + 1);
                    }
                });
            };

            walk(this.tree, 0);

            return rows;
        },
    }));

    /* ═════════════════════════════════════════════════════════════
       ipChatThread — ticket chat (admin / seller / customer)

       The page NEVER scrolls: the header and the reply composer are
       pinned and ONLY the message list scrolls. The list always rests
       on the newest message — on first paint (incl. after wire:navigate)
       and after every Livewire commit of the ticket component (reply
       sent, ticket closed/reopened…). The textarea only commits on real
       actions (wire:model is deferred), so typing never yanks the user
       back to the bottom.
       Usage: <div x-data="ipChatThread"> … <div x-ref="msgs" …>
       ═════════════════════════════════════════════════════════════ */
    Alpine.data('ipChatThread', () => ({
        init() {
            this.toBottom();
            // settle again once webfonts finished swapping (heights shift)
            if (document.fonts?.ready) {
                document.fonts.ready.then(() => this.toBottom());
            }

            // re-run after the DOM morph of every commit of THIS component
            this.$wire.$hook('commit', ({ succeed }) => {
                succeed(() => this.toBottom());
            });
        },

        toBottom() {
            this.$nextTick(() => {
                const el = this.$refs.msgs;

                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            });
        },

        /* auto-grow composer textarea (1 → 5 rows max) */
        grow() {
            const el = this.$el;

            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        },
    }));
});

/* ═══════════════════════════════════════════════════════════════
   Instant filter-pick feedback — delegated optimistic toggle.
   Any element carrying [data-ip-pick] (storefront filter chips,
   attribute picks, seller product-list chips, …) flips to its
   "picked" look the moment it is tapped; the Livewire morph that
   follows confirms the real state.

   • data-ip-pick          → simple toggle (multi-select chips);
                             uses the generic .ip-picked look
   • data-ip-pick + data-ip-group="x"
                           → single-select: clears the siblings in
                             the same group first, then highlights
                             this one (category pills, sort tabs, …)
   • data-ip-pick + data-ip-pick-class="bg-white …"
                           → apply THESE Tailwind classes instead of
                             .ip-picked, i.e. exactly the classes the
                             server uses for the selected state — so
                             the optimistic flash is pixel-identical
                             to the settled state (no style jump)
   ═══════════════════════════════════════════════════════════════ */
document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-ip-pick]');
    if (! el) {
        return;
    }

    const custom = (el.getAttribute('data-ip-pick-class') || '')
        .split(/\s+/)
        .filter(Boolean);
    const group = el.getAttribute('data-ip-group');

    if (group) {
        // single-select: the tapped chip wins immediately
        document
            .querySelectorAll('[data-ip-group="' + group + '"]')
            .forEach((sib) => {
                if (custom.length) {
                    sib.classList.remove(...custom);
                }
                sib.classList.remove('ip-picked');
            });

        if (custom.length) {
            el.classList.add(...custom);
        } else {
            el.classList.add('ip-picked');
        }

        return;
    }

    if (custom.length) {
        custom.forEach((c) => el.classList.toggle(c));
    } else {
        el.classList.toggle('ip-picked');
    }
}, true);
