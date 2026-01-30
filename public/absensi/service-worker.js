/* Compatibility SW entrypoint for pages under /absensi/.
 *
 * If an old cached script ever called:
 *   navigator.serviceWorker.register('service-worker.js')
 * from /absensi/* pages, the browser will fetch /absensi/service-worker.js.
 *
 * We forward it to the real service worker at /sw.js.
 */
importScripts('/sw.js');

