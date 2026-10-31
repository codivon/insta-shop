//
// InstaShop — front-end runtime
//

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

// ── Cart badge live update (customer app) ────────────────────────────
// Livewire cart actions dispatch a browser event; the tab-bar badge
// updates without a full page reload. Listener lives in app.js because
// wire:navigate does not re-execute page-level inline scripts.
document.addEventListener('cart-changed', (event) => {
    const badge = document.getElementById('cart-badge');
    if (!badge) return;

    const count = Number(event.detail?.count ?? badge.dataset.count ?? 0);

    badge.dataset.count = String(count);
    badge.textContent = count.toLocaleString('fa-IR');
    badge.classList.toggle('hidden', count <= 0);
});
