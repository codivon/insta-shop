/*
|--------------------------------------------------------------------------
| InstaShop service worker — Web Push for admin notifications
|--------------------------------------------------------------------------
| Lets the admin receive notification pushes even when the panel tab or
| the whole browser is closed. Payload shape (from App\Support\WebPusher):
|   { "title": "...", "body": "...", "url": "/admin/..." }
*/

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    let data = {};

    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: 'InstaShop', body: event.data ? event.data.text() : '' };
    }

    const title = data.title || 'InstaShop';
    const options = {
        body: data.body || '',
        icon: data.icon || '/apple-touch-icon.png',
        badge: data.icon || '/apple-touch-icon.png',
        dir: 'rtl',
        lang: 'fa',
        tag: 'instashop-' + Date.now(),
        data: { url: data.url || '/admin' },
        vibrate: [100, 50, 100],
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = (event.notification.data && event.notification.data.url) || '/admin';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // focus an existing panel window when there is one
            for (const client of clientList) {
                if ('focus' in client) {
                    client.navigate(url);
                    return client.focus();
                }
            }

            return self.clients.openWindow(url);
        }),
    );
});

/*
|--------------------------------------------------------------------------
| Static asset cache (PWA installability + faster cold starts)
|--------------------------------------------------------------------------
| A fetch handler is REQUIRED for the browser install prompt to appear.
| Strategy — deliberately conservative so nothing can break the panels:
|   • only same-origin GET                    → Livewire POSTs untouched
|   • never touch /livewire/* nor /ping       → live traffic stays live
|   • only versioned build files, fonts and images → stale-while-revalidate
|   • everything else (pages, admin, …)       → straight to the network
*/
const STATIC_PATTERN = /\.(css|js|mjs|png|jpe?g|webp|avif|gif|svg|ico|woff2?|ttf)$/i;

self.addEventListener('fetch', (event) => {
    const req = event.request;

    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    if (url.origin !== self.location.origin) return;
    if (url.pathname.startsWith('/livewire') || url.pathname === '/ping') return;
    if (!url.pathname.startsWith('/build/') && !url.pathname.startsWith('/fonts/') && !STATIC_PATTERN.test(url.pathname)) return;

    // range requests (partial media) must go to the network untouched
    if (req.headers.has('range')) return;

    event.respondWith(
        caches.open('instashop-static-v2').then(async (cache) => {
            const cached = await cache.match(req);
            const network = fetch(req)
                .then((res) => {
                    if (res && res.ok) cache.put(req, res.clone());
                    return res;
                })
                .catch(() => cached);

            return cached || network;
        }),
    );
});
