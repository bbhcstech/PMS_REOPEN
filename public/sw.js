const BASE_URL = new URL('./', self.location.href || self.location.origin + '/sw.js');
const CACHE_PREFIX = 'bitroxia-pms-' + encodeURIComponent(BASE_URL.pathname) + '-';
const CACHE_NAME = CACHE_PREFIX + 'v10';
const OFFLINE_URL = new URL('offline.html', BASE_URL).href;
const APP_SHELL = ['offline.html', 'pwa/icons/icon-192x192.png', 'pwa/icons/icon-512x512.png',
  'admin/assets/css/pms-mobile.css', 'admin/assets/js/pms-mobile.js'].map(path => new URL(path, BASE_URL).href);

self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE_NAME).then(async cache => {
    await cache.add(new Request(OFFLINE_URL, {cache: 'reload'}));
    await Promise.allSettled(APP_SHELL.filter(url => url !== OFFLINE_URL).map(url => cache.add(new Request(url, {cache: 'reload'}))));
  }));
});
self.addEventListener('activate', event => {
  event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME &&
    (key.startsWith(CACHE_PREFIX) || (BASE_URL.pathname === '/' && /^bitroxia-pms-v\d+$/.test(key))))
    .map(key => caches.delete(key)))).then(() => self.clients.claim()));
});
self.addEventListener('message', event => {
  if (event.data?.type === 'SKIP_WAITING') event.waitUntil(self.skipWaiting());
});
self.addEventListener('fetch', event => {
  const request = event.request;
  if (request.method !== 'GET') return;
  const url = new URL(request.url);
  if (url.origin !== self.location.origin || !url.pathname.startsWith(BASE_URL.pathname)) return;
  if (request.mode === 'navigate') {
    // Network-only HTML preserves sessions, permission checks, and live server errors.
    event.respondWith(fetch(request).catch(async () =>
      (await caches.open(CACHE_NAME)).match(OFFLINE_URL).then(response => response || Response.error())));
    return;
  }
  if (isStaticAsset(url.pathname)) event.respondWith(networkFirstAsset(request));
});
function isStaticAsset(pathname) {
  if (!pathname.startsWith(BASE_URL.pathname)) return false;
  const relative = pathname.slice(BASE_URL.pathname.length);
  return /^(?:frontend\/|admin\/assets\/|assets\/|build\/|vendor\/|pwa\/|css\/|js\/|fonts\/)/.test(relative)
    && /\.(?:css|js|png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|eot)$/i.test(relative);
}
async function networkFirstAsset(request) {
  let cache;
  try { cache = await caches.open(CACHE_NAME); } catch (_) {}
  try {
    const response = await fetch(request, {cache: 'no-cache'});
    if (response.status === 200 && !response.redirected &&
      !/private|no-store/i.test(response.headers.get('Cache-Control') || '') &&
      !/text\/html/i.test(response.headers.get('Content-Type') || '')) {
      // Full storage must not break a successful online request.
      try { if (cache) await cache.put(request, response.clone()); } catch (_) {}
    } else {
      if (cache) await cache.delete(request).catch(() => {});
    }
    return response;
  } catch (error) {
    const cached = cache && await cache.match(request);
    if (cached) return cached;
    throw error;
  }
}
