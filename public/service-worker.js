/* Backward-compatible SW entrypoint.
 *
 * Some browsers/devtools or older deployments may still try to update
 * `/service-worker.js`. Our actual service worker lives at `/sw.js`.
 */
importScripts('/sw.js');

