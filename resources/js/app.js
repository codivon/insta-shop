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
