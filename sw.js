const CACHE_NAME = 'msj-dashboard-v1';

// Assets to cache on install (App Shell)
const STATIC_ASSETS = [
  '/new_dashboard/',
  '/new_dashboard/assets/css/app.min.css',
  '/new_dashboard/assets/css/icons.min.css',
  '/new_dashboard/assets/css/custom.css',
  '/new_dashboard/assets/js/vendor.min.js',
  '/new_dashboard/assets/js/app.min.js',
  '/new_dashboard/assets/images/logos/MSJ logo new 512.png',
  '/new_dashboard/assets/images/users/avatar-1.jpg'
];

// Install: cache static assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

// Activate: clear old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames =>
      Promise.all(
        cacheNames
          .filter(name => name !== CACHE_NAME)
          .map(name => caches.delete(name))
      )
    ).then(() => self.clients.claim())
  );
});

// Fetch strategy:
// - Static assets (CSS/JS/images) → Cache First
// - API calls → Network Only (ต้อง online เสมอ)
// - Pages (PHP) → Network First, fallback to cache
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // Skip non-GET and cross-origin
  if (event.request.method !== 'GET' || url.origin !== location.origin) return;

  // API calls → Network only
  if (url.pathname.includes('/api/')) {
    return; // let browser handle normally
  }

  // Static assets → Cache first
  if (
    url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/)
  ) {
    event.respondWith(
      caches.match(event.request).then(cached =>
        cached || fetch(event.request).then(response => {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
          return response;
        })
      )
    );
    return;
  }

  // Pages → Network first, fallback to cache
  event.respondWith(
    fetch(event.request)
      .then(response => {
        const clone = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        return response;
      })
      .catch(() => caches.match(event.request))
  );
});
