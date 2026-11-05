//
// InstaShop — Chrome-style pull-to-refresh for the mobile shells
//
// The customer & seller shells pin the viewport (h-dvh + overflow-hidden)
// and scroll INSIDE <main> — so the browser's native pull-to-refresh can
// never fire. This module restores the Chrome-on-Android behavior: the
// page reloads only when the finger STARTS inside the very top strip of
// the screen (~24px, over the pinned header too) and drags down past the
// threshold.
//
// Client report (v0.17.4): «رفرش خودکار رو بر دار… فقط باید z-index از همه
// بیشتر باشه تا هرجایی کار کنه» — the old scroller-bound version armed
// from ANYWHERE at scroll-top (a drag on the first card, the banner
// carousel… felt like the page refreshes ITSELF). Now the gesture is
// deliberate: start inside the top strip or it never arms. Document-level
// capture binding makes it work on EVERY page (over headers, bells,
// theme toggles — anything), and the spinner z-index sits above every
// overlay in the app.
//
// Touch-only (a mouse can never trigger it), and inert inside elements
// marked data-ptr-ignore (e.g. the story viewer overlay). Focused shells
// (chat/product) whose <main> does not scroll bind the DOCUMENT scroller
// instead (v0.17.7) — the top-strip rule still keeps the gesture
// deliberate there.
//
// v0.17.7: client «رفرش دستی را در سمت اپ مشتری هم بزار» — the public
// storefront layout (/shop/{instagram}, where the stories live) has NO
// scrollable <main>: the page scrolls on the document itself, so the old
// main-only lookup never bound there and the gesture was dead. Now, when
// no scrollable <main> exists, the module binds the DOCUMENT scroller
// (document.scrollingElement) — the top-strip rule keeps it deliberate.
//

const THRESHOLD = 72;      // px of downward drag that arms the refresh
const TOP_ZONE = 36;       // px strip under the top edge where the pull may start
                           // (v0.17.8: client «محدوده رفرش دستی را یکم بیشتر کن…
                           // ۱۰ پیکسل پایین‌تر» — was 26, +10px on mobile)
const SPINNER_TOP = 20;    // px under the top edge the spinner sits at (Chrome)

const init = () => {
    // The scroller: the first <main> that actually scrolls (customer +
    // seller shells keep header/tabbar pinned and scroll main only).
    let scroller = null;

    document.querySelectorAll('main').forEach((el) => {
        if (scroller) return;
        const oy = getComputedStyle(el).overflowY;
        if (oy === 'auto' || oy === 'scroll') scroller = el;
    });

    // Storefront-style pages scroll on the document, not on a <main> —
    // fall back to the root scroller so the gesture exists there too.
    if (!scroller) scroller = document.scrollingElement;

    if (!scroller || scroller.dataset.ptrBound === '1') return;
    scroller.dataset.ptrBound = '1';

    // One shared indicator node (survives re-inits; recreated after
    // wire:navigate replaces the body).
    let indicator = document.getElementById('ip-ptr');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'ip-ptr';
        indicator.setAttribute('aria-hidden', 'true');
        indicator.innerHTML = '<span class="ip-ptr-spin"></span>';
        document.body.appendChild(indicator);
    }

    // Notch / status-bar inset — the top strip starts BELOW it.
    let safeTop = 0;
    const probe = document.createElement('div');
    probe.style.cssText = 'position:fixed;top:0;height:env(safe-area-inset-top,0px);visibility:hidden;pointer-events:none;';
    document.body.appendChild(probe);
    safeTop = probe.offsetHeight;
    probe.remove();

    let startY = 0;
    let tracking = false;
    let armed = false;

    const reset = (animate = true) => {
        tracking = false;
        armed = false;
        indicator.classList.toggle('ip-ptr-back', animate);
        indicator.classList.remove('ip-ptr-armed', 'ip-ptr-loading');
        indicator.style.transform = '';
        indicator.style.setProperty('--ptr-rot', '0deg');
        indicator.style.setProperty('--ptr-scale', '0.5');
    };

    // ── document-level capture binding: works EVERYWHERE (the user's
    //    ask — «تا هرجایی کار کنه»), including on the pinned header,
    //    notification bell, theme toggle… anything under the top strip.
    document.addEventListener(
        'touchstart',
        (e) => {
            if (e.touches.length !== 1) return;
            if (e.target.closest('[data-ptr-ignore]')) return;
            // deliberate gesture ONLY: the finger must start inside the
            // very top strip of the screen (≈20px) — never mid-page
            if (e.touches[0].clientY > TOP_ZONE + safeTop) return;
            // and only while the page sits at its very top
            if (scroller.scrollTop > 0) return;

            startY = e.touches[0].clientY;
            tracking = true;
            armed = false;
            indicator.classList.remove('ip-ptr-back', 'ip-ptr-loading');
        },
        { capture: true, passive: true },
    );

    document.addEventListener(
        'touchmove',
        (e) => {
            if (!tracking || e.touches.length !== 1) return;

            const dy = e.touches[0].clientY - startY;

            if (dy <= 0) {
                if (indicator.style.transform !== '') reset(false);
                return;
            }

            // Chrome-style: the spinner NEVER travels down the page — it stays
            // pinned ~20px under the top edge and only grows/rotates in place
            // while the pull progresses.
            const p = Math.min(1, dy / THRESHOLD);
            armed = dy >= THRESHOLD;

            indicator.classList.toggle('ip-ptr-armed', armed);
            indicator.style.transform = 'translate(-50%, 0)';
            indicator.style.setProperty('--ptr-rot', `${Math.round(p * 360)}deg`);
            indicator.style.setProperty('--ptr-scale', (0.5 + 0.5 * p).toFixed(3));
        },
        { capture: true, passive: true },
    );

    document.addEventListener(
        'touchend',
        () => {
            if (!tracking) return;

            if (armed) {
                tracking = false;
                armed = false;
                indicator.classList.add('ip-ptr-loading');
                indicator.style.transform = 'translate(-50%, 0)';
                indicator.style.setProperty('--ptr-rot', '0deg');
                indicator.style.setProperty('--ptr-scale', '1');

                // Chrome reloads the full document — do the same.
                setTimeout(() => window.location.reload(), 120);

                return;
            }

            reset(true);
        },
        { capture: true, passive: true },
    );
};

init();
document.addEventListener('livewire:navigated', init);
