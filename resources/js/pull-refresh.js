//
// InstaShop — Chrome-style pull-to-refresh for the mobile shells
//

// The customer & seller shells pin the viewport (h-dvh + overflow-hidden)
// and scroll INSIDE <main> — so the browser's native pull-to-refresh can
// never fire. This module restores the Chrome-on-Android behavior: when
// the scroller sits at the very top and the user drags down, a spinner
// drops in with rubber-band resistance; past the threshold it "arms" and
// releasing the finger reloads the page.
//
// Touch-only (a mouse can never trigger it), and disabled on focused
// pages (chat/product) whose <main> does not scroll, and inside elements
// marked data-ptr-ignore (e.g. the story viewer overlay).

const THRESHOLD = 72; // px of raw drag that arms the refresh

const init = () => {
    // The scroller: the first <main> that actually scrolls (customer +
    // seller shells keep header/tabbar pinned and scroll main only).
    let scroller = null;

    document.querySelectorAll('main').forEach((el) => {
        if (scroller) return;
        const oy = getComputedStyle(el).overflowY;
        if (oy === 'auto' || oy === 'scroll') scroller = el;
    });

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

    scroller.addEventListener('touchstart', (e) => {
        if (e.touches.length !== 1) return;
        if (e.target.closest('[data-ptr-ignore]')) return;
        if (scroller.scrollTop > 0) return;

        startY = e.touches[0].clientY;
        tracking = true;
        armed = false;
        indicator.classList.remove('ip-ptr-back', 'ip-ptr-loading');
    }, { passive: true });

    scroller.addEventListener('touchmove', (e) => {
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
    }, { passive: true });

    scroller.addEventListener('touchend', () => {
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
    }, { passive: true });
};

init();
document.addEventListener('livewire:navigated', init);
