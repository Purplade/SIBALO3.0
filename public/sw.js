/* Basic PWA service worker for SIBALO */
importScripts('/offline/idb.js');

// Bump cache version when caching logic/assets change.
const CACHE_NAME = 'sibalo-cache-v6';
const OFFLINE_URL = '/offline.html';
const PRECACHE = [
  OFFLINE_URL,
  '/manifest.webmanifest',
  '/assets/css/style.css',
  '/assets/js/base.js',
  '/assets/js/lib/jquery-3.4.1.min.js',
  '/assets/js/lib/popper.min.js',
  '/assets/js/lib/bootstrap.min.js',
  '/offline/idb.js',
  '/offline/offline-sync.js',
  '/vendor/ionicons/ionicons.esm.js',
  '/vendor/ionicons/ionicons.js',
  '/vendor/sweetalert2/sweetalert2.all.min.js',
  '/vendor/webcamjs/webcam.min.js',
  '/vendor/leaflet/leaflet.css',
  '/vendor/leaflet/leaflet.js',
  '/vendor/leaflet/images/marker-icon.png',
  '/vendor/leaflet/images/marker-icon-2x.png',
  '/vendor/leaflet/images/marker-shadow.png',
  '/vendor/leaflet/images/layers.png',
  '/vendor/leaflet/images/layers-2x.png',
  '/assets/img/favicon.png',
  '/assets/img/icon/192x192.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.map((k) => (k === CACHE_NAME ? null : caches.delete(k))));
    await self.clients.claim();
  })());
});

function isNavigationRequest(request) {
  return request.mode === 'navigate' ||
    (request.method === 'GET' && request.headers.get('accept') && request.headers.get('accept').includes('text/html'));
}

function isCacheableHtmlPath(pathname) {
  // Avoid caching auth pages / logout / admin panel pages.
  const deny = [
    '/login',
    '/panel',
    '/dashboard',
    '/proseslogout',
    '/proseslogoutadmin',
  ];
  if (deny.some((p) => pathname === p || pathname.startsWith(p + '/'))) return false;
  return true;
}

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Only handle same-origin
  if (url.origin !== self.location.origin) return;

  // Stale-while-revalidate for static assets under /assets/, /vendor/, and /offline/
  // This avoids "stuck" JS/CSS after updates (e.g., base.js).
  if (request.method === 'GET' && (url.pathname.startsWith('/assets/') || url.pathname.startsWith('/vendor/') || url.pathname.startsWith('/offline/'))) {
    event.respondWith((async () => {
      const cache = await caches.open(CACHE_NAME);
      const cached = await cache.match(request);

      // Cache API doesn't support 206 Partial Content (Range requests).
      // Also avoid caching any request that includes a Range header.
      const isRangeRequest = request.headers && request.headers.has('range');

      const fetchAndUpdate = fetch(request)
        .then((res) => {
          // Only cache full 200 responses (avoid 206 partial responses).
          if (!isRangeRequest && res && res.ok && res.status === 200) {
            cache.put(request, res.clone());
          }
          return res;
        })
        .catch(() => null);

      // Return cached immediately, but refresh cache in background.
      if (cached) {
        event.waitUntil(fetchAndUpdate);
        return cached;
      }

      const res = await fetchAndUpdate;
      return res || Response.error();
    })());
    return;
  }

  // Navigation: network-first, fallback to offline page
  if (isNavigationRequest(request)) {
    event.respondWith((async () => {
      try {
        const res = await fetch(request);
        // Cache-on-visit for server-rendered HTML (offline-first navigation).
        // Only cache successful HTML responses (avoid caching redirects/login pages).
        const ct = (res.headers.get('content-type') || '').toLowerCase();
        if (res && res.ok && res.status === 200 && ct.includes('text/html') && isCacheableHtmlPath(url.pathname)) {
          const cache = await caches.open(CACHE_NAME);
          // Cache by normalized path so querystrings won't break offline matches.
          await cache.put(new Request(url.pathname, { method: 'GET' }), res.clone());
        }
        return res;
      } catch {
        const cache = await caches.open(CACHE_NAME);
        // Try serving cached HTML first (if user has visited it before).
        const cachedPage = await cache.match(url.pathname, { ignoreSearch: true });
        if (cachedPage) return cachedPage;
        // Fallback to offline landing page.
        return (await cache.match(OFFLINE_URL)) || Response.error();
      }
    })());
    return;
  }
});

// Optional Background Sync: drain IndexedDB queue when supported
async function postJson(url, data, csrf) {
  const res = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-SIBALO-SYNC': '1',
      ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
    },
    body: JSON.stringify(data),
  });
  const json = await res.json().catch(() => null);
  return { ok: res.ok, json };
}

const API_ABSENSI_BULK = '/api/offline-sync/absensi/bulk';
const API_IZIN_BULK = '/api/offline-sync/izin/bulk';

async function drainQueueInSW() {
  const IDB = self.SibaloIDB;
  if (!IDB) return;
  const items = await IDB.getAll();
  const absensiItems = items.filter((x) => x && x.kind === 'absensi');
  const izinItems = items.filter((x) => x && x.kind === 'izin');

  async function applyBulkResult(originalItems, json) {
    const results = (json && Array.isArray(json.results)) ? json.results : [];
    const okUuids = new Set(results.filter(r => r && r.status === 'success' && r.client_uuid).map(r => r.client_uuid));
    for (const item of originalItems) {
      const cu = item && item.payload ? item.payload.client_uuid : null;
      if (cu && okUuids.has(cu)) {
        await IDB.del(item.id);
      }
    }
  }

  // Absensi bulk
  if (absensiItems.length) {
    const csrf = absensiItems.find(x => x && x.csrf)?.csrf || null;
    try {
      const r = await postJson(API_ABSENSI_BULK, { _token: csrf, items: absensiItems.map(x => x.payload) }, null);
      if (r.ok && r.json && r.json.status) await applyBulkResult(absensiItems, r.json);
    } catch {
      // ignore; will retry later
    }
  }

  // Izin bulk
  if (izinItems.length) {
    const csrf = izinItems.find(x => x && x.csrf)?.csrf || null;
    try {
      const r = await postJson(API_IZIN_BULK, { _token: csrf, items: izinItems.map(x => x.payload) }, null);
      if (r.ok && r.json && r.json.status) await applyBulkResult(izinItems, r.json);
    } catch {
      // ignore; will retry later
    }
  }
}

self.addEventListener('sync', (event) => {
  if (event.tag === 'sibalo-sync') {
    event.waitUntil(drainQueueInSW());
  }
});

