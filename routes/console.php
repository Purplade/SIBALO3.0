<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\DemoQueueJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sibalo:queue-demo {message? : Message to log}', function () {
    $message = (string) ($this->argument('message') ?? 'Hello from queue');
    DemoQueueJob::dispatch($message);
    $this->info('Dispatched DemoQueueJob: ' . $message);
    $this->info('Run worker: php artisan queue:work --tries=3');
})->purpose('Dispatch a demo queued job (database queue) for smoke testing.');

Artisan::command('sibalo:seed-anomali {--nik=} {--count=6}', function () {
    $nikOpt = trim((string) $this->option('nik'));
    $count = max(1, (int) $this->option('count'));

    $nik = $nikOpt !== '' ? $nikOpt : (string) (DB::table('pegawai')->value('nik') ?? '');
    if ($nik === '') {
        $this->error('Tidak ada data pegawai (nik). Isi opsi --nik=....');
        return self::FAILURE;
    }

    $now = now();
    $ua = 'DemoSeeder/1.0 (SIBALO)';
    $ip = '127.0.0.1';

    $absensiRows = [];
    $izinRows = [];

    // Create some mixed events:
    // - delayed_sync: captured_at far in past
    // - client_time_in_future: captured_at in future
    for ($i = 0; $i < $count; $i++) {
        $type = ($i % 2 === 0) ? 'delayed_sync' : 'client_time_in_future';
        $receivedAt = $now->copy()->subMinutes($i);
        $capturedAt = $type === 'delayed_sync'
            ? $receivedAt->copy()->subMinutes(12)
            : $receivedAt->copy()->addMinutes(5);
        $delay = $receivedAt->getTimestamp() - $capturedAt->getTimestamp();
        $flags = [$type];

        $absensiRows[] = [
            'nik' => $nik,
            'absensi_id' => null,
            'client_uuid' => 'seed_abs_' . $now->getTimestamp() . '_' . $i,
            'captured_at' => $capturedAt->toDateTimeString(),
            'received_at' => $receivedAt->toDateTimeString(),
            'sync_delay_seconds' => $delay,
            'event_type' => ($i % 3 === 0) ? 'in' : 'out',
            'lokasi' => '-0.12345, 123.45678',
            'radius_meters' => null,
            'user_agent' => $ua,
            'ip' => $ip,
            'result_status' => 'success',
            'result_tag' => ($i % 3 === 0) ? 'in' : 'out',
            'message' => 'Demo audit event (' . $type . ')',
            'anomaly_flags' => json_encode($flags),
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ];

        // Add fewer izin events (every 2)
        if ($i % 2 === 0) {
            $izinRows[] = [
                'nik' => $nik,
                'pengajuan_id' => null,
                'client_uuid' => 'seed_izin_' . $now->getTimestamp() . '_' . $i,
                'captured_at' => $capturedAt->toDateTimeString(),
                'received_at' => $receivedAt->toDateTimeString(),
                'sync_delay_seconds' => $delay,
                'user_agent' => $ua,
                'ip' => $ip,
                'result_status' => 'success',
                'message' => 'Demo izin audit event (' . $type . ')',
                'anomaly_flags' => json_encode($flags),
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ];
        }
    }

    DB::table('absensi_events')->insert($absensiRows);
    if (count($izinRows)) {
        DB::table('izin_events')->insert($izinRows);
    }

    $this->info('Inserted demo anomaly events for NIK: ' . $nik);
    $this->info('Absensi events: ' . count($absensiRows) . ', Izin events: ' . count($izinRows));
    $this->info('Open: /monitoring/anomali');
    return self::SUCCESS;
})->purpose('Seed demo anomaly events into audit tables (for demo/sidang).');
