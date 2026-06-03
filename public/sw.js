// Minimal Service Worker to satisfy PWA installability requirements
self.addEventListener('install', (e) => {
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    self.clients.claim();
});

self.addEventListener('fetch', (e) => {
    // Pass-through to network, no custom caching logic to prevent data desync
});
