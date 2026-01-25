/* Basic PWA service worker for SIBALO */
importScripts('/offline/idb.js');

const CACHE_NAME = 'sibalo-cache-v1';
const OFFLINE_URL = '/offline.html';
const PRECACHE = [
  OFFLINE_URL,
  '/manifest.webmanifest',
  '/assets/css/style.css',
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

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Only handle same-origin
  if (url.origin !== self.location.origin) return;

  // Cache-first for static assets under /assets/
  if (request.method === 'GET' && url.pathname.startsWith('/assets/')) {
    event.respondWith((async () => {
      const cache = await caches.open(CACHE_NAME);
      const cached = await cache.match(request);
      if (cached) return cached;
      const res = await fetch(request);
      if (res.ok) cache.put(request, res.clone());
      return res;
    })());
    return;
  }

  // Navigation: network-first, fallback to offline page
  if (isNavigationRequest(request)) {
    event.respondWith((async () => {
      try {
        const res = await fetch(request);
        return res;
      } catch {
        const cache = await caches.open(CACHE_NAME);
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

