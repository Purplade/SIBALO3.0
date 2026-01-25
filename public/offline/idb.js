/* Minimal IndexedDB helper (no deps). Works in window + service worker (importScripts). */
(function (global) {
  const DB_NAME = 'sibalo_offline_db';
  const DB_VERSION = 1;
  const STORE = 'queue';

  function openDb() {
    return new Promise((resolve, reject) => {
      const req = indexedDB.open(DB_NAME, DB_VERSION);
      req.onupgradeneeded = () => {
        const db = req.result;
        if (!db.objectStoreNames.contains(STORE)) {
          const store = db.createObjectStore(STORE, { keyPath: 'id' });
          store.createIndex('createdAt', 'createdAt');
        }
      };
      req.onsuccess = () => resolve(req.result);
      req.onerror = () => reject(req.error);
    });
  }

  async function withStore(mode, fn) {
    const db = await openDb();
    try {
      const tx = db.transaction(STORE, mode);
      const store = tx.objectStore(STORE);
      const res = await fn(store);
      await new Promise((resolve, reject) => {
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
        tx.onabort = () => reject(tx.error);
      });
      return res;
    } finally {
      db.close();
    }
  }

  function reqToPromise(req) {
    return new Promise((resolve, reject) => {
      req.onsuccess = () => resolve(req.result);
      req.onerror = () => reject(req.error);
    });
  }

  function uid() {
    return 'q_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2);
  }

  async function put(item) {
    return withStore('readwrite', (store) => reqToPromise(store.put(item)));
  }

  async function getAll() {
    return withStore('readonly', async (store) => {
      const all = await reqToPromise(store.getAll());
      all.sort((a, b) => (a.createdAt || 0) - (b.createdAt || 0));
      return all;
    });
  }

  async function del(id) {
    return withStore('readwrite', (store) => reqToPromise(store.delete(id)));
  }

  async function clear() {
    return withStore('readwrite', (store) => reqToPromise(store.clear()));
  }

  global.SibaloIDB = { DB_NAME, STORE, uid, put, getAll, del, clear };
})(typeof self !== 'undefined' ? self : window);

