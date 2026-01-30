/* Offline queue + auto-sync for SIBALO (absensi + izin). */
(function () {
  const IDB = self.SibaloIDB;
  if (!IDB) return;

  const QUEUE_TAG = 'sibalo-sync';

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
    const item = {
      id: IDB.uid(),
      createdAt: Date.now(),
      tries: 0,
      lastError: null,
      url,
      method,
      kind, // 'absensi' | 'izin'
      payload, // see kinds below
    };
    await IDB.put(item);
    const reg = await registerSW();
    await maybeRegisterBackgroundSync(reg);
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
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
      },
      body: JSON.stringify(payload),
    });
    const json = await res.json().catch(() => null);
    return { ok: res.ok, status: res.status, json };
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
    if (!navigator.onLine) return { synced: 0, failed: 0 };
    const items = await IDB.getAll();
    let synced = 0;
    let failed = 0;

    for (const item of items) {
      try {
        let result = null;

        if (item.kind === 'absensi') {
          // payload: { image: 'data:image/...;base64,...', lokasi: 'lat,long' }
          result = await postJson(item.url, item.payload);
          if (result.ok && result.json && result.json.status === 'success') {
            await IDB.del(item.id);
            synced++;
            if (!silent) toast('Tersinkron', result.json.message || 'Absensi berhasil diupload', 'success');
          } else {
            // Application error: don't retry infinitely; keep item but mark error.
            item.tries = (item.tries || 0) + 1;
            item.lastError = (result.json && (result.json.message || result.json.error)) || 'Gagal upload absensi';
            await IDB.put(item);
            failed++;
            if (!silent) toast('Gagal sinkron', item.lastError, 'error');
          }
        } else if (item.kind === 'izin') {
          // payload: { fields: Array<[k,v]>, files: Array<{ name, type, lastModified, data: Blob }> }
          const fd = new FormData();
          for (const [k, v] of item.payload.fields || []) fd.append(k, v);
          for (const f of item.payload.files || []) {
            const file = new File([f.data], f.name, { type: f.type || 'application/octet-stream', lastModified: f.lastModified || Date.now() });
            fd.append(f.fieldName || 'bukti_sakit', file);
          }
          result = await postForm(item.url, fd);
          if (result.ok && result.json && result.json.status === 'success') {
            await IDB.del(item.id);
            synced++;
            if (!silent) toast('Tersinkron', result.json.message || 'Izin berhasil diupload', 'success');
          } else {
            item.tries = (item.tries || 0) + 1;
            item.lastError = (result.json && (result.json.message || result.json.error)) || 'Gagal upload izin';
            await IDB.put(item);
            failed++;
            if (!silent) toast('Gagal sinkron', item.lastError, 'error');
          }
        } else {
          // unknown item
          await IDB.del(item.id);
        }
      } catch (e) {
        // Network/other error: keep item for retry.
        item.tries = (item.tries || 0) + 1;
        item.lastError = String(e && e.message ? e.message : e);
        await IDB.put(item);
        failed++;
      }
    }

    return { synced, failed };
  }

  async function submitAbsensi({ image, lokasi }) {
    const client_uuid = (IDB && typeof IDB.uid === 'function') ? IDB.uid() : ('q_' + Date.now());
    const captured_at = new Date().toISOString();
    const payload = { image, lokasi, _token: getCsrfToken(), client_uuid, captured_at };

    // Offline OR network is unreliable: queue it, then auto-sync later.
    if (!navigator.onLine) {
      await enqueueRequest({
        url: '/absensi/store',
        method: 'POST',
        kind: 'absensi',
        payload: { ...payload, offline: 1 }
      });
      toast('Disimpan offline', 'Absensi akan diupload otomatis saat online.', 'info');
      return { queued: true };
    }

    try {
      const result = await postJson('/absensi/store', { ...payload, offline: 0 });
      if (result.ok && result.json && result.json.status === 'success') {
        return { queued: false, ok: true, json: result.json };
      }
      return { queued: false, ok: false, json: result.json };
    } catch (e) {
      // fetch() can fail even when navigator.onLine === true (wifi captive portal, DNS issues, server down).
      await enqueueRequest({
        url: '/absensi/store',
        method: 'POST',
        kind: 'absensi',
        payload: { ...payload, offline: 1 }
      });
      toast('Disimpan offline', 'Koneksi tidak stabil. Absensi diantrikan dan akan diupload saat online.', 'info');
      return { queued: true };
    }
  }

  async function enqueueForm(formEl) {
    const action = formEl.getAttribute('action') || window.location.pathname;
    const fd = new FormData(formEl);

    const fields = [];
    const files = [];

    for (const [k, v] of fd.entries()) {
      if (v instanceof File) {
        if (v && v.name) {
          const buf = await v.arrayBuffer();
          files.push({
            fieldName: k,
            name: v.name,
            type: v.type,
            lastModified: v.lastModified,
            data: new Blob([buf], { type: v.type || 'application/octet-stream' }),
          });
        }
      } else {
        fields.push([k, v]);
      }
    }

    await enqueueRequest({
      url: action,
      method: (formEl.getAttribute('method') || 'POST').toUpperCase(),
      kind: 'izin',
      payload: { fields, files },
    });

    toast('Disimpan offline', 'Pengajuan izin/sakit akan diupload otomatis saat online.', 'info');
    return true;
  }

  // Expose API for page scripts (e.g., selfie page).
  window.SibaloOffline = {
    registerSW,
    drainQueue,
    submitAbsensi,
    enqueueForm,
  };

  // Auto-register SW + auto-drain when online.
  registerSW().then(() => {
    if (navigator.onLine) drainQueue({ silent: true });
  });
  window.addEventListener('online', () => drainQueue({ silent: false }));
})();

