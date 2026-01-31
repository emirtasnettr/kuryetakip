// Papyon Kurye Service Worker
const CACHE_NAME = 'papyon-v3';
const STATIC_ASSETS = [
    '/images/logo.png',
    '/images/app-icon.png',
    '/manifest.json'
];

// Install event - cache static assets only
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

// Fetch event - Network first for HTML, cache first for static assets
self.addEventListener('fetch', event => {
    const request = event.request;
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // For HTML pages (navigation requests) - always try network first
    if (request.mode === 'navigate' || request.headers.get('accept').includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    return response;
                })
                .catch(() => {
                    // If network fails, show offline page or cached version
                    return caches.match(request);
                })
        );
        return;
    }
    
    // For static assets - cache first
    event.respondWith(
        caches.match(request)
            .then(response => {
                if (response) {
                    return response;
                }
                return fetch(request).then(fetchResponse => {
                    // Cache successful responses
                    if (fetchResponse.ok) {
                        const responseClone = fetchResponse.clone();
                        caches.open(CACHE_NAME).then(cache => {
                            cache.put(request, responseClone);
                        });
                    }
                    return fetchResponse;
                });
            })
    );
});

// Activate event - clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.filter(cacheName => cacheName !== CACHE_NAME)
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
    // Take control immediately
    return self.clients.claim();
});
