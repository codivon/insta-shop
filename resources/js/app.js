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
