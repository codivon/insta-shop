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
