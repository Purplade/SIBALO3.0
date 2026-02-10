<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class OfflineBulkSyncController extends Controller
{
    public function absensiBulk(Request $request)
    {
        // Accept cookie-session auth (pegawai) + CSRF (_token) like normal web routes.
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.client_uuid' => ['required', 'string', 'max:80'],
            'items.*.captured_at' => ['nullable', 'date'],
            'items.*.image' => ['required', 'string'],
            'items.*.lokasi' => ['required', 'string'],
        ]);

        $items = $validated['items'];
        $results = [];
        $synced = 0;

        // Preserve original request state while we reuse AbsensiController logic.
        $origInput = $request->request->all();
        $origFiles = $request->files->all();
        $origAccept = (string) $request->headers->get('Accept');
        $request->headers->set('Accept', 'application/json');

        try {
            $absensiController = app(AbsensiController::class);

            foreach ($items as $item) {
                $request->request->replace($item);
                $request->files->replace([]);

                $resp = $absensiController->store($request);
                $statusCode = method_exists($resp, 'getStatusCode') ? $resp->getStatusCode() : 200;
                $payload = method_exists($resp, 'getData') ? (array) $resp->getData(true) : [];

                $ok = ($statusCode >= 200 && $statusCode < 300) && (($payload['status'] ?? null) === 'success');
                if ($ok) {
                    $synced++;
                }
                $results[] = [
                    'client_uuid' => $item['client_uuid'],
                    'status' => $payload['status'] ?? ($ok ? 'success' : 'error'),
                    'message' => $payload['message'] ?? null,
                    'tag' => $payload['tag'] ?? null,
                ];
            }
        } finally {
            $request->request->replace($origInput);
            $request->files->replace($origFiles);
            $request->headers->set('Accept', $origAccept);
        }

        $overallStatus = $synced === count($items)
            ? 'success'
            : ($synced === 0 ? 'error' : 'partial');

        return response()->json([
            'status' => $overallStatus,
            'synced' => $synced,
            'failed' => count($items) - $synced,
            'results' => $results,
        ]);
    }

    public function izinBulk(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.client_uuid' => ['required', 'string', 'max:80'],
            'items.*.captured_at' => ['nullable', 'date'],
            'items.*.dari' => ['required', 'date_format:Y-m-d'],
            'items.*.sampai' => ['required', 'date_format:Y-m-d'],
            'items.*.status' => ['required', 'in:i,s'],
            'items.*.keterangan' => ['required', 'string', 'max:2000'],
            'items.*.bukti_sakit' => ['nullable', 'array'],
            'items.*.bukti_sakit.name' => ['nullable', 'string', 'max:255'],
            'items.*.bukti_sakit.type' => ['nullable', 'string', 'max:100'],
            'items.*.bukti_sakit.base64' => ['nullable', 'string'],
        ]);

        $items = $validated['items'];
        $results = [];
        $synced = 0;

        $origInput = $request->request->all();
        $origFiles = $request->files->all();
        $origAccept = (string) $request->headers->get('Accept');
        $request->headers->set('Accept', 'application/json');

        $absensiController = app(AbsensiController::class);

        foreach ($items as $item) {
            $tmpPath = null;
            try {
                $request->request->replace([
                    'client_uuid' => $item['client_uuid'],
                    'captured_at' => $item['captured_at'] ?? null,
                    'dari' => $item['dari'],
                    'sampai' => $item['sampai'],
                    'status' => $item['status'],
                    'keterangan' => $item['keterangan'],
                ]);

                $request->files->replace([]);

                // Optional attachment (only valid when status = sakit)
                if (($item['status'] ?? null) === 's' && !empty($item['bukti_sakit']['base64'])) {
                    $name = $item['bukti_sakit']['name'] ?? ('bukti_sakit_' . $item['client_uuid'] . '.bin');
                    $type = $item['bukti_sakit']['type'] ?? 'application/octet-stream';
                    $raw = base64_decode((string) $item['bukti_sakit']['base64'], true);
                    if ($raw !== false) {
                        $tmpPath = tempnam(sys_get_temp_dir(), 'sibalo_izin_');
                        file_put_contents($tmpPath, $raw);
                        $uploaded = new UploadedFile(
                            $tmpPath,
                            $name,
                            $type,
                            null,
                            true
                        );
                        $request->files->set('bukti_sakit', $uploaded);
                    }
                }

                $resp = $absensiController->storeizin($request);
                $statusCode = method_exists($resp, 'getStatusCode') ? $resp->getStatusCode() : 200;
                $payload = method_exists($resp, 'getData') ? (array) $resp->getData(true) : [];

                $ok = ($statusCode >= 200 && $statusCode < 300) && (($payload['status'] ?? null) === 'success');
                if ($ok) {
                    $synced++;
                }

                $results[] = [
                    'client_uuid' => $item['client_uuid'],
                    'status' => $payload['status'] ?? ($ok ? 'success' : 'error'),
                    'message' => $payload['message'] ?? null,
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'client_uuid' => $item['client_uuid'],
                    'status' => 'error',
                    'message' => 'Gagal memproses item izin: ' . $e->getMessage(),
                ];
            } finally {
                if ($tmpPath && is_file($tmpPath)) {
                    @unlink($tmpPath);
                }
            }
        }

        // Restore request
        $request->request->replace($origInput);
        $request->files->replace($origFiles);
        $request->headers->set('Accept', $origAccept);

        $overallStatus = $synced === count($items)
            ? 'success'
            : ($synced === 0 ? 'error' : 'partial');

        return response()->json([
            'status' => $overallStatus,
            'synced' => $synced,
            'failed' => count($items) - $synced,
            'results' => $results,
        ]);
    }
}

