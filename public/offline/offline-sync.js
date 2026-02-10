/* Offline queue + auto-sync for SIBALO (absensi + izin). */
(function () {
  const IDB = self.SibaloIDB;
  if (!IDB) return;

  const QUEUE_TAG = 'sibalo-sync';
  const API_ABSENSI_BULK = '/api/offline-sync/absensi/bulk';
  const API_IZIN_BULK = '/api/offline-sync/izin/bulk';
  const AUTO_DRAIN_INTERVAL_MS = 15000; // safety net: devtools "SW Offline" toggle may not fire online event

  let draining = false;
  let drainTimer = null;
  let scheduled = null;
  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.content) return meta.content;
    const input = document.querySelector('input[name="_token"]');
    if (input && input.value) return input.value;
    return '';
  }

  function toast(title, text, icon) {
    // Prefer SweetAlert2 if present (already used in pages)
    if (window.Swal && typeof window.Swal.fire === 'function') {
      return window.Swal.fire({ title, text, icon });
    }
    // Fallback
    alert(title + (text ? '\n' + text : ''));
  }

  async function registerSW() {
    if (!('serviceWorker' in navigator)) return null;
    try {
      const reg = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
      return reg;
    } catch {
      return null;
    }
  }

  async function maybeRegisterBackgroundSync(reg) {
    try {
      if (reg && reg.sync && typeof reg.sync.register === 'function') {
        await reg.sync.register(QUEUE_TAG);
      }
    } catch {
      // ignore
    }
  }

  async function enqueueRequest({ url, method, kind, payload }) {
    const csrf = getCsrfToken();
    const item = {
      id: IDB.uid(),
      createdAt: Date.now(),
      tries: 0,
      lastError: null,
      url,
      method,
      kind, // 'absensi' | 'izin'
      csrf,
      client_timestamp: payload && payload.captured_at ? payload.captured_at : new Date().toISOString(),
      payload, // see kinds below
    };
    await IDB.put(item);
    window.dispatchEvent(new CustomEvent('sibalo:queue-changed'));
    const reg = await registerSW();
    await maybeRegisterBackgroundSync(reg);
    // If we are already online, attempt sync soon (DevTools SW offline toggle often doesn't trigger 'online' event)
    scheduleDrainSoon();
    return item;
  }

  async function postJson(url, data) {
    const csrf = getCsrfToken();
    const payload = (csrf && data && typeof data === 'object' && !('_token' in data))
      ? { ...data, _token: csrf }
      : data;
    const res = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-SIBALO-SYNC': '1',
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
      },
      body: JSON.stringify(payload),
    });
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    const json = ct.includes('application/json') ? await res.json().catch(() => null) : null;
    const text = !json ? await res.text().catch(() => '') : '';
    return { ok: res.ok, status: res.status, json, text };
  }

  async function postForm(url, formData) {
    const csrf = getCsrfToken();
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
    return { ok: res.ok, status: res.status, json };
  }

  async function drainQueue({ silent = true } = {}) {
    if (draining) return { synced: 0, failed: 0 };
    if (!navigator.onLine) return { synced: 0, failed: 0 };
    draining = true;
    const items = await IDB.getAll();
    const absensiItems = items.filter((x) => x && x.kind === 'absensi');
    const izinItems = items.filter((x) => x && x.kind === 'izin');

    let synced = 0;
    let failed = 0;

    async function applyResults(originalItems, responseJson) {
      const results = (responseJson && Array.isArray(responseJson.results)) ? responseJson.results : [];
      const okUuids = new Set(results.filter(r => r && r.status === 'success' && r.client_uuid).map(r => r.client_uuid));
      const errMap = new Map(results.filter(r => r && r.status !== 'success' && r.client_uuid).map(r => [r.client_uuid, r.message || 'Gagal sinkron']));

      for (const item of originalItems) {
        const cu = item && item.payload ? item.payload.client_uuid : null;
        if (cu && okUuids.has(cu)) {
          await IDB.del(item.id);
          synced++;
        } else if (cu && errMap.has(cu)) {
          item.tries = (item.tries || 0) + 1;
          item.lastError = errMap.get(cu) || 'Gagal sinkron';
          await IDB.put(item);
          failed++;
        }
      }
    }

    try {
      // Bulk Absensi
      if (absensiItems.length) {
        const csrf = absensiItems.find(x => x && x.csrf)?.csrf || getCsrfToken();
        try {
          const res = await postJson(API_ABSENSI_BULK, { _token: csrf, items: absensiItems.map(x => x.payload) });
          if (res.ok && res.json && res.json.status) {
            await applyResults(absensiItems, res.json);
            if (!silent && res.json.synced) toast('Sinkronisasi', 'Absensi tersinkron: ' + res.json.synced, 'success');
          } else {
            // mark all as failed (keep for retry)
            for (const item of absensiItems) {
              item.tries = (item.tries || 0) + 1;
              item.lastError = (res.json && (res.json.message || res.json.error)) || 'Gagal sinkron absensi';
              await IDB.put(item);
              failed++;
            }
          if (!silent) toast('Gagal sinkron', 'Absensi gagal disinkronkan. (HTTP ' + (res.status || '?') + ')', 'error');
          }
        } catch (e) {
          for (const item of absensiItems) {
            item.tries = (item.tries || 0) + 1;
            item.lastError = String(e && e.message ? e.message : e);
            await IDB.put(item);
            failed++;
          }
        }
      }

      // Bulk Izin
      if (izinItems.length) {
        const csrf = izinItems.find(x => x && x.csrf)?.csrf || getCsrfToken();
        try {
          const res = await postJson(API_IZIN_BULK, { _token: csrf, items: izinItems.map(x => x.payload) });
          if (res.ok && res.json && res.json.status) {
            await applyResults(izinItems, res.json);
            if (!silent && res.json.synced) toast('Sinkronisasi', 'Izin tersinkron: ' + res.json.synced, 'success');
          } else {
            for (const item of izinItems) {
              item.tries = (item.tries || 0) + 1;
              item.lastError = (res.json && (res.json.message || res.json.error)) || 'Gagal sinkron izin';
              await IDB.put(item);
              failed++;
            }
            if (!silent) toast('Gagal sinkron', 'Izin gagal disinkronkan. (HTTP ' + (res.status || '?') + ')', 'error');
          }
        } catch (e) {
          for (const item of izinItems) {
            item.tries = (item.tries || 0) + 1;
            item.lastError = String(e && e.message ? e.message : e);
            await IDB.put(item);
            failed++;
          }
        }
      }
    } finally {
      draining = false;
    }

    window.dispatchEvent(new CustomEvent('sibalo:queue-changed'));
    return { synced, failed };
  }

  function scheduleDrainSoon() {
    // Avoid spamming drain calls; coalesce within 1s
    if (scheduled) return;
    scheduled = setTimeout(async () => {
      scheduled = null;
      try {
        const cnt = await IDB.count();
        if (cnt > 0 && navigator.onLine) {
          await drainQueue({ silent: true });
        }
      } catch {
        // ignore
      }
    }, 1000);
  }

  function startAutoDrainSafetyNet() {
    if (drainTimer) return;
    drainTimer = setInterval(async () => {
      try {
        if (!navigator.onLine) return;
        const cnt = await IDB.count();
        if (cnt > 0) {
          await drainQueue({ silent: true });
        }
      } catch {
        // ignore
      }
    }, AUTO_DRAIN_INTERVAL_MS);
  }

  async function submitAbsensi({ image, lokasi }) {
    const client_uuid = (IDB && typeof IDB.uid === 'function') ? IDB.uid() : ('q_' + Date.now());
    const captured_at = new Date().toISOString();
    const payload = { image, lokasi, client_uuid, captured_at };

    // Offline OR network is unreliable: queue it, then auto-sync later.
    if (!navigator.onLine) {
      await enqueueRequest({
        url: API_ABSENSI_BULK,
        method: 'POST',
        kind: 'absensi',
        payload: { ...payload, offline: 1 }
      });
      toast('Disimpan offline', 'Absensi akan diupload otomatis saat online.', 'info');
      return { queued: true };
    }

    try {
      // Online fast-path: hit the normal endpoint so UI can react correctly (audio, errors, etc.)
      // Bulk endpoint is reserved for background sync / outbox drain.
      const result = await postJson('/absensi/store', { ...payload, offline: 0 });
      if (result.ok && result.json && result.json.status === 'success') {
        return { queued: false, ok: true, json: result.json };
      }
      return {
        queued: false,
        ok: false,
        json: result.json || { status: 'error', message: 'Gagal absensi', tag: 'unknown' },
        httpStatus: result.status,
      };
    } catch (e) {
      // fetch() can fail even when navigator.onLine === true (wifi captive portal, DNS issues, server down).
      await enqueueRequest({
        url: API_ABSENSI_BULK,
        method: 'POST',
        kind: 'absensi',
        payload: { ...payload, offline: 1 }
      });
      toast('Disimpan offline', 'Koneksi tidak stabil. Absensi diantrikan dan akan diupload saat online.', 'info');
      return { queued: true };
    }
  }

  function arrayBufferToBase64(buf) {
    let binary = '';
    const bytes = new Uint8Array(buf);
    const len = bytes.byteLength;
    for (let i = 0; i < len; i++) binary += String.fromCharCode(bytes[i]);
    return btoa(binary);
  }

  async function enqueueForm(formEl) {
    // Ensure idempotency + timestamp exist even if page script missed it
    try {
      const cu = formEl.querySelector('#client_uuid') || formEl.querySelector('input[name="client_uuid"]');
      const ca = formEl.querySelector('#captured_at') || formEl.querySelector('input[name="captured_at"]');
      if (cu && !cu.value) cu.value = (window.SibaloIDB && SibaloIDB.uid) ? SibaloIDB.uid() : ('q_' + Date.now());
      if (ca && !ca.value) ca.value = new Date().toISOString();
    } catch (e) {}

    const fd = new FormData(formEl);
    const payload = {
      client_uuid: String(fd.get('client_uuid') || (IDB.uid ? IDB.uid() : ('q_' + Date.now()))),
      captured_at: String(fd.get('captured_at') || new Date().toISOString()),
      dari: String(fd.get('dari') || ''),
      sampai: String(fd.get('sampai') || ''),
      status: String(fd.get('status') || ''),
      keterangan: String(fd.get('keterangan') || ''),
      bukti_sakit: null,
    };

    const f = fd.get('bukti_sakit');
    if (f instanceof File && f.name) {
      const buf = await f.arrayBuffer();
      payload.bukti_sakit = {
        name: f.name,
        type: f.type || 'application/octet-stream',
        base64: arrayBufferToBase64(buf),
      };
    }

    await enqueueRequest({
      url: API_IZIN_BULK,
      method: 'POST',
      kind: 'izin',
      payload,
    });

    toast('Disimpan offline', 'Pengajuan izin/sakit akan diupload otomatis saat online.', 'info');
    return true;
  }

  async function getPendingCount() {
    try {
      return await IDB.count();
    } catch {
      try {
        const all = await IDB.getAll();
        return all.length;
      } catch {
        return 0;
      }
    }
  }

  async function renderQueueBadge() {
    const els = document.querySelectorAll('[data-offline-queue-badge]');
    if (!els || !els.length) return;
    const count = await getPendingCount();
    els.forEach((el) => {
      if (!el) return;
      el.textContent = String(count);
      el.style.display = count > 0 ? 'inline-block' : 'none';
    });
  }

  // Expose API for page scripts (e.g., selfie page).
  window.SibaloOffline = {
    registerSW,
    drainQueue,
    submitAbsensi,
    enqueueForm,
    getPendingCount,
  };

  // Auto-register SW + auto-drain when online.
  registerSW().then(() => {
    if (navigator.onLine) drainQueue({ silent: true });
  });
  window.addEventListener('online', () => drainQueue({ silent: false }));
  // Safety triggers: DevTools SW offline toggle doesn't always emit online/offline events.
  window.addEventListener('focus', () => scheduleDrainSoon());
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') scheduleDrainSoon();
  });
  startAutoDrainSafetyNet();

  // UI badge: "Menunggu sinkronisasi"
  renderQueueBadge();
  window.addEventListener('sibalo:queue-changed', renderQueueBadge);
  window.addEventListener('online', renderQueueBadge);
  window.addEventListener('offline', renderQueueBadge);
})();

