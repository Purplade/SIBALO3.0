/* Basic PWA service worker for SIBALO */
importScripts('/offline/idb.js');

// Bump cache version when caching logic/assets change.
const CACHE_NAME = 'sibalo-cache-v5';
const OFFLINE_URL = '/offline.html';
const OFFLINE_MODE_URL = '/offline-mode.html';
const PRECACHE = [
  OFFLINE_URL,
  OFFLINE_MODE_URL,
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

// Pages that should be usable in offline mode (served from cache if available).
// These are server-rendered HTML pages, so they need to be visited at least once while online.
const OFFLINE_HTML_ALLOWLIST = [
  '/absensi/selfie',
  '/absensi/buatizin',
  '/absensi/izin',
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

function isAllowlistedHtmlPath(pathname) {
  return OFFLINE_HTML_ALLOWLIST.some((p) => pathname === p || pathname.startsWith(p + '/'));
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
        // Cache allowlisted HTML pages for offline navigation.
        if (res && res.ok && res.status === 200 && isAllowlistedHtmlPath(url.pathname)) {
          const cache = await caches.open(CACHE_NAME);
          // Cache by normalized path so querystrings won't break offline matches.
          cache.put(new Request(url.pathname, { method: 'GET' }), res.clone());
        }
        return res;
      } catch {
        const cache = await caches.open(CACHE_NAME);
        // If this navigation is allowlisted, try serving cached HTML first.
        if (isAllowlistedHtmlPath(url.pathname)) {
          const cachedPage = await cache.match(url.pathname);
          if (cachedPage) return cachedPage;
        }
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
      ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
    },
    body: JSON.stringify(data),
  });
  const json = await res.json().catch(() => null);
  return { ok: res.ok, json };
}

async function postForm(url, formData, csrf) {
  const res = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json',
      ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
    },
    body: formData,
  });
  const json = await res.json().catch(() => null);
  return { ok: res.ok, json };
}

async function drainQueueInSW() {
  // We can't easily access CSRF token in SW; rely on session + _token field for form submits.
  // For absensi JSON, we send without CSRF header and expect app to accept it only if CSRF cookie works.
  // If your CSRF setup requires header, main-thread sync will handle it.
  const IDB = self.SibaloIDB;
  if (!IDB) return;
  const items = await IDB.getAll();
  for (const item of items) {
    try {
      if (item.kind === 'absensi') {
        const r = await postJson(item.url, item.payload, null);
        if (r.ok && r.json && r.json.status === 'success') await IDB.del(item.id);
      } else if (item.kind === 'izin') {
        const fd = new FormData();
        for (const [k, v] of item.payload.fields || []) fd.append(k, v);
        for (const f of item.payload.files || []) {
          const file = new File([f.data], f.name, { type: f.type || 'application/octet-stream', lastModified: f.lastModified || Date.now() });
          fd.append(f.fieldName || 'bukti_sakit', file);
        }
        const r = await postForm(item.url, fd, null);
        if (r.ok && r.json && r.json.status === 'success') await IDB.del(item.id);
      } else {
        await IDB.del(item.id);
      }
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

