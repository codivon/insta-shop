
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
