const CACHE_NAME = 'gel-sec-v1';

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([
                '/gel-secretary/dashboard',
                '/manifest.json'
            ]);
        })
    );
});

self.addEventListener('fetch', (e) => {
    e.respondWith(
        fetch(e.request).catch(() => {
            return caches.match(e.request);
        })
    );
});

self.addEventListener('push', (e) => {
    const data = e.data ? e.data.json() : {};
    const title = data.title || 'Nouvelle Notification';
    const options = {
        body: data.description || 'Vous avez reçu une notification sur GEL Secrétariat.',
        icon: '/images/icons/icon-192x192.png',
        badge: '/images/icons/icon-192x192.png',
        data: {
            url: data.link || '/gel-secretary/dashboard'
        }
    };
    e.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (e) => {
    e.notification.close();
    if (e.notification.data && e.notification.data.url) {
        e.waitUntil(clients.openWindow(e.notification.data.url));
    }
});
