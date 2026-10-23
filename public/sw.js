const CACHE = 'medlicar-v1';

self.addEventListener('install', (e) => { self.skipWaiting(); });
self.addEventListener('activate', (e) => { e.waitUntil(self.clients.claim()); });

self.addEventListener('fetch', (e) => {
    const req = e.request;
    if (req.method !== 'GET' || !req.url.startsWith(self.location.origin)) return;
    if (req.url.includes('/app') || req.url.includes('/reservation') || req.url.includes('/track')) return; // ne pas cacher l'admin ni les actions
    e.respondWith((async () => {
        const cache = await caches.open(CACHE);
        const cached = await cache.match(req);
        const network = fetch(req).then((res) => {
            if (res && res.ok) cache.put(req, res.clone());
            return res;
        }).catch(() => cached);
        return cached || network;
    })());
});
