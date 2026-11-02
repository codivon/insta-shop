
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
(function () {
    let bar, veil;

    const ensureNodes = () => {
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'ip-loadbar';
            document.body.appendChild(bar);
        }
        if (!veil) {
            veil = document.createElement('div');
            veil.id = 'ip-navveil';
            veil.setAttribute('aria-hidden', 'true');
            veil.innerHTML = '<div class="ip-navveil-spin"></div>';
            document.body.appendChild(veil);
        }
    };

    /* the element the user interacted with last (capture phase, before
       Livewire sees the click) */
    let origin = null;
    const busyTargets = new Set();

    document.addEventListener('pointerdown', (e) => {
        const el = e.target.closest('button, a, [role="button"], label, summary, [wire\\:click]');
        origin = el || null;
    }, { capture: true });

    const markBusy = (el) => {
        if (!el || !el.isConnected) return;
        el.classList.add('ip-busy');
        busyTargets.add(el);
    };

    const clearBusy = () => {
        busyTargets.forEach((el) => el.classList.remove('ip-busy'));
        busyTargets.clear();
    };

    const showBar = () => { ensureNodes(); bar.classList.add('ip-on'); };
    const hideBar = () => { bar && bar.classList.remove('ip-on'); };

    let inflight = 0;

    document.addEventListener('livewire:init', () => {
        // every Livewire request (wire:click, wire:model.live commits, …)
        Livewire.hook('request', ({ respond }) => {
            inflight += 1;
            showBar();
            // the LAST pointerdown before this request wins the busy mark
            markBusy(origin);
            origin = null;

            respond(() => {
                inflight = Math.max(0, inflight - 1);
                if (inflight === 0) {
                    hideBar();
                    clearBusy();
                }
            });
        });

        // wire:navigate page transitions
        document.addEventListener('livewire:navigate', () => {
            ensureNodes();
            veil.classList.add('ip-on');
        });

        document.addEventListener('livewire:navigated', () => {
            veil && veil.classList.remove('ip-on');
            hideBar();
            clearBusy();
            inflight = 0;
        });

        // safety: if a request errors out without respond() firing cleanly
        window.addEventListener('offline', () => {
            inflight = 0;
            hideBar();
            clearBusy();
        });
    });
})();
