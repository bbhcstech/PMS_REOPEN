const CACHE_NAME = 'bitroxia-pms-v2';
const OFFLINE_URL = '/offline.html';
const APP_SHELL = [
  OFFLINE_URL,
  '/logo.png',
  '/manifest.json',
  '/frontend/css/bbh-pms.css',
  '/frontend/js/bbh-pms.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(APP_SHELL))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.map(key => (key === CACHE_NAME ? null : caches.delete(key)))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const request = event.request;

  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);

  if (request.mode === 'navigate') {
    if (url.origin === self.location.origin && isSessionPage(url.pathname)) {
      event.respondWith(networkOnlyPage(request));
      return;
    }

    event.respondWith(networkFirstPage(request));
    return;
  }

  if (url.origin === self.location.origin && isStaticAsset(url.pathname)) {
    event.respondWith(cacheFirstAsset(request));
  }
});

function isStaticAsset(pathname) {
  return /\.(?:css|js|png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|eot)$/i.test(pathname);
}

function isSessionPage(pathname) {
  return pathname === '/login'
    || pathname === '/logout'
    || pathname.startsWith('/dashboard')
    || pathname.startsWith('/admin')
    || pathname.startsWith('/hr-login')
    || pathname.startsWith('/manager-login')
    || pathname.startsWith('/profile');
}

async function networkOnlyPage(request) {
  try {
    return await fetch(request);
  } catch (error) {
    return caches.match(OFFLINE_URL);
  }
}

async function networkFirstPage(request) {
  try {
    const response = await fetch(request);
    return response;
  } catch (error) {
    return caches.match(OFFLINE_URL);
  }
}

async function cacheFirstAsset(request) {
  const cached = await caches.match(request);
  if (cached) {
    return cached;
  }

  const response = await fetch(request);
  if (response && response.status === 200) {
    const cache = await caches.open(CACHE_NAME);
    cache.put(request, response.clone());
  }
  return response;
}
