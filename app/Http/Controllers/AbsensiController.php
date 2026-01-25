<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Services\HolidayService;

class AbsensiController extends Controller
{
    private function parseCapturedAt(Request $request): ?Carbon
    {
        $v = $request->input('captured_at');
        if (empty($v)) {
            return null;
        }
        try {
            return Carbon::parse($v);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function buildAnomalyFlags(?Carbon $capturedAt, ?int $delaySeconds): array
    {
        $flags = [];
        if ($capturedAt) {
            if ($delaySeconds !== null && $delaySeconds < 0) {
                $flags[] = 'client_time_in_future';
            }
            // Sync delay threshold: 5 minutes (tunable)
            if ($delaySeconds !== null && $delaySeconds > 300) {
                $flags[] = 'delayed_sync';
            }
        }
        return $flags;
    }

    private function logAbsensiEvent(Request $request, string $status, string $message, string $tag, ?int $absensiId = null, ?string $eventType = null): void
    {
        try {
            $nik = Auth::guard('pegawai')->user()->nik ?? null;
            if (!$nik) {
                return;
            }

            $clientUuid = $request->input('client_uuid');
            $capturedAt = $this->parseCapturedAt($request);
            $receivedAt = now();
            $delaySeconds = null;
            if ($capturedAt) {
                $delaySeconds = $receivedAt->getTimestamp() - $capturedAt->getTimestamp();
            }
            $flags = $this->buildAnomalyFlags($capturedAt, $delaySeconds);

            DB::table('absensi_events')->insert([
                'nik' => $nik,
                'absensi_id' => $absensiId,
                'client_uuid' => $clientUuid,
                'captured_at' => $capturedAt?->toDateTimeString(),
                'received_at' => $receivedAt->toDateTimeString(),
                'sync_delay_seconds' => $delaySeconds,
                'event_type' => $eventType,
                'lokasi' => $request->input('lokasi'),
                'radius_meters' => null,
                'user_agent' => (string) $request->userAgent(),
                'ip' => (string) $request->ip(),
                'result_status' => $status,
                'result_tag' => $tag,
                'message' => $message,
                'anomaly_flags' => empty($flags) ? null : json_encode($flags),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Never block absensi due to audit logging failure
            Log::warning('absensi_events logging failed: ' . $e->getMessage());
        }
    }

    private function respondAbsensi(Request $request, string $status, string $message, string $tag, ?int $absensiId = null, ?string $eventType = null)
    {
        $ok = $status === 'success';
        $this->logAbsensiEvent($request, $status, $message, $tag, $absensiId, $eventType);
        if ($request->expectsJson()) {
            return response()->json(
                [
                    'status' => $status,
                    'message' => $message,
                    'tag' => $tag,
                ],
                $ok ? 200 : 422
            );
        }
        return response($status . '|' . $message . '|' . $tag, $ok ? 200 : 200);
    }

    private function logIzinEvent(Request $request, string $status, string $message, ?int $pengajuanId = null): void
    {
        try {
            $nik = Auth::guard('pegawai')->user()->nik ?? null;
            if (!$nik) {
                return;
            }
            $clientUuid = $request->input('client_uuid');
            $capturedAt = $this->parseCapturedAt($request);
            $receivedAt = now();
            $delaySeconds = null;
            if ($capturedAt) {
                $delaySeconds = $receivedAt->getTimestamp() - $capturedAt->getTimestamp();
            }
            $flags = $this->buildAnomalyFlags($capturedAt, $delaySeconds);

            DB::table('izin_events')->insert([
                'nik' => $nik,
                'pengajuan_id' => $pengajuanId,
                'client_uuid' => $clientUuid,
                'captured_at' => $capturedAt?->toDateTimeString(),
                'received_at' => $receivedAt->toDateTimeString(),
                'sync_delay_seconds' => $delaySeconds,
                'user_agent' => (string) $request->userAgent(),
                'ip' => (string) $request->ip(),
                'result_status' => $status,
                'message' => $message,
                'anomaly_flags' => empty($flags) ? null : json_encode($flags),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('izin_events logging failed: ' . $e->getMessage());
        }
    }

    private function respondIzin(Request $request, string $status, string $message, int $httpStatus = 200, ?int $pengajuanId = null)
    {
        $this->logIzinEvent($request, $status, $message, $pengajuanId);
        if ($request->expectsJson()) {
            return response()->json(
                [
                    'status' => $status, // success|warning|error
                    'message' => $message,
                ],
                $httpStatus
            );
        }
        // legacy redirect responses
        if ($status === 'success') {
            return redirect('/absensi/izin')->with(['success' => $message]);
        }
        if ($status === 'warning') {
            return redirect('/absensi/izin')->with(['warning' => $message]);
        }
        return redirect('/absensi/izin')->with(['error' => $message]);
    }

    public function selfie()
    {
        $hariini = date("Y-m-d");
        $nik = Auth::guard('pegawai')->user()->nik;

        // Cek apakah sudah absen masuk hari ini
        $cek_masuk = DB::table('absensi')
            ->where('tgl_absensi', $hariini)
            ->where('nik', $nik)
            ->whereNotNull('jam_in')
            ->count();

        // Cek apakah sudah absen pulang hari ini
        $cek_pulang = DB::table('absensi')
            ->where('tgl_absensi', $hariini)
            ->where('nik', $nik)
            ->whereNotNull('jam_out')
            ->count();

        // Jika sudah absen masuk tapi belum pulang, tampilkan tombol pulang
        $show_pulang = ($cek_masuk > 0 && $cek_pulang == 0);
        $lokasi_sklh = DB::table('lokasi_sekolah')->where('id', 1)->first();
        $isWeekend = in_array(date('N'), [6, 7]);
        $isHoliday = false;
        $isOnLeave = false;
        try {
            $holidayService = app(HolidayService::class);
            $isHoliday = $holidayService->isHoliday(new \DateTimeImmutable($hariini));
        } catch (\Throwable $e) {
            // Fail-open if service error; optionally log
            Log::warning('HolidayService error: ' . $e->getMessage());
        }

        // Cek apakah pegawai punya pengajuan izin/sakit yang disetujui dan aktif hari ini
        $onLeave = DB::table('pengajuan_izin')
            ->where('nik', $nik)
            ->where('status_approved', 1)
            ->where(function($q) use ($hariini) {
                $q->where(function($qq) use ($hariini) {
                    $qq->whereNotNull('izin_sampai')
                       ->where('tgl_izin', '<=', $hariini)
                       ->where('izin_sampai', '>=', $hariini);
                })
                ->orWhere(function($qq) use ($hariini) {
                    $qq->whereNull('izin_sampai')
                       ->where('tgl_izin', $hariini);
                });
            })
            ->exists();
        $isOnLeave = $onLeave ? true : false;
        return view('layouts.selfie', compact('show_pulang', 'lokasi_sklh', 'isWeekend', 'isHoliday', 'isOnLeave'));
    }

    // fungsi menyimpan data di database dan juga di storage
    public function store(Request $request)
    {
        $nik = Auth::guard('pegawai')->user()->nik;
        $tgl_absensi = date("Y-m-d");
        $jam = date("H:i:s");

        // Idempotency: if client_uuid already processed successfully, return success (prevents duplicate sync)
        $clientUuid = (string) $request->input('client_uuid');
        if ($clientUuid !== '') {
            $existing = DB::table('absensi_events')
                ->where('nik', $nik)
                ->where('client_uuid', $clientUuid)
                ->where('result_status', 'success')
                ->orderByDesc('id')
                ->first();
            if ($existing) {
                return $this->respondAbsensi(
                    $request,
                    'success',
                    $existing->message ?: 'Absensi sudah tersimpan',
                    $existing->result_tag ?: 'in',
                    $existing->absensi_id ? (int) $existing->absensi_id : null,
                    $existing->event_type ?: null
                );
            }
        }

        // Blokir absensi pada akhir pekan (Sabtu/Minggu)
        $dayOfWeek = date('N'); // 1=Senin ... 7=Minggu
        if ($dayOfWeek >= 6) {
            return $this->respondAbsensi($request, 'error', 'Tidak dapat absen pada hari libur (Sabtu/Minggu)', 'weekend', null, 'unknown');
        }
        // Blokir jika tanggal masuk kalender libur (Google Calendar)
        try {
            $holidayService = app(HolidayService::class);
            if ($holidayService->isHoliday(new \DateTimeImmutable($tgl_absensi))) {
                return $this->respondAbsensi($request, 'error', 'Tidak dapat absen pada hari libur nasional', 'holiday', null, 'unknown');
            }
        } catch (\Throwable $e) {
            // Fail-open; jangan blokir jika service error
            Log::warning('HolidayService error: ' . $e->getMessage());
        }

        // Blokir jika sedang izin/sakit yang sudah disetujui dan aktif pada tanggal ini
        $onLeave = DB::table('pengajuan_izin')
            ->where('nik', $nik)
            ->where('status_approved', 1)
            ->where(function($q) use ($tgl_absensi) {
                $q->where(function($qq) use ($tgl_absensi) {
                    $qq->whereNotNull('izin_sampai')
                       ->where('tgl_izin', '<=', $tgl_absensi)
                       ->where('izin_sampai', '>=', $tgl_absensi);
                })
                ->orWhere(function($qq) use ($tgl_absensi) {
                    $qq->whereNull('izin_sampai')
                       ->where('tgl_izin', $tgl_absensi);
                });
            })
            ->exists();
        if ($onLeave) {
            return $this->respondAbsensi($request, 'error', 'Tidak dapat absen karena ada pengajuan izin/sakit aktif', 'leave', null, 'unknown');
        }
        $lokasi_sklh = DB::table('lokasi_sekolah')->where('id', 1)->first();
        $lok = explode(",", $lokasi_sklh->lokasi);
        $lokasi = $request->lokasi;
        $latitudekantor = $lok[0];
        $longitudekantor = $lok[1];
        $lokasiuser = explode(",", $lokasi);
        $latitudeuser = $lokasiuser[0];
        $longitudeuser = $lokasiuser[1];

        $jarak = $this->distance($latitudekantor, $longitudekantor, $latitudeuser, $longitudeuser);
        $radius = round($jarak["meters"]);

        // VALIDASI RADIUS - DIPINDAHKAN KE ATAS
        if ($radius > $lokasi_sklh->radius) {
            return $this->respondAbsensi($request, 'error', 'Maaf Anda Berada Diluar Radius, Jarak Anda ' . $radius . ' meter dari Kantor', 'radius', null, 'unknown');
        }

        $image = $request->image;
        if (empty($image)) {
            return $this->respondAbsensi($request, 'error', 'Tidak ada data gambar yang dikirim', 'in', null, 'unknown');
        }

        // Cek apakah sudah absen masuk hari ini
        $cek_masuk = DB::table('absensi')
            ->where('tgl_absensi', $tgl_absensi)
            ->where('nik', $nik)
            ->whereNotNull('jam_in')
            ->count();

        // Cek apakah sudah absen pulang hari ini
        $cek_pulang = DB::table('absensi')
            ->where('tgl_absensi', $tgl_absensi)
            ->where('nik', $nik)
            ->whereNotNull('jam_out')
            ->count();

        // Aturan: Absen masuk hanya bisa mulai pukul 06:00
        if ($cek_masuk == 0 && $jam < '06:00:00') {
            return $this->respondAbsensi($request, 'error', 'Absen masuk hanya diperbolehkan mulai pukul 06:00', 'time', null, 'in');
        }

        // Menambahkan Keterangan in pada file foto
        if ($cek_masuk && $cek_pulang == 0) {
            $ket = "out";
        } else {
            $ket = "in";
        }

        // Generate nama file unik dengan timestamp
        $timestamp = time();
        $formatName = $nik . "-" . $tgl_absensi . "-" . $ket;
        // Robustly strip data URI header if present and decode
        $normalizedBase64 = preg_replace('/^data:image\/(png|jpe?g);base64,/i', '', $image);
        if ($normalizedBase64 === null) {
            return $this->respondAbsensi($request, 'error', 'Format gambar tidak valid', 'in', null, 'unknown');
        }
        $image_binary = base64_decode($normalizedBase64);
        if ($image_binary === false) {
            return $this->respondAbsensi($request, 'error', 'Gagal decode gambar', 'in', null, 'unknown');
        }
        $fileName = $formatName . '-' . $timestamp . ".png";
        $folderPath = "uploads/absensi/";
        $filePath = $folderPath . $fileName;
        // Ensure directory exists
        if (!Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->makeDirectory($folderPath);
        }

        // Jika sudah absen masuk tapi belum pulang, lakukan absen pulang
        if ($cek_masuk > 0 && $cek_pulang == 0) {
            // Aturan: Absen pulang hanya bisa mulai pukul 12:00
            if ($jam < '12:00:00') {
                return $this->respondAbsensi($request, 'error', 'Absen pulang hanya diperbolehkan mulai pukul 12:00', 'time', null, 'out');
            }
            // Cari record absensi masuk hari ini
            $absensi = DB::table('absensi')
                ->where('tgl_absensi', $tgl_absensi)
                ->where('nik', $nik)
                ->whereNotNull('jam_in')
                ->first();

            if ($absensi) {
                $data_pulang = [
                    'jam_out' => $jam,
                    'foto_out' => $fileName, // Simpan nama file baru
                    'lokasi_out' => $lokasi
                ];

                $update = DB::table('absensi')
                    ->where('id', $absensi->id)
                    ->update($data_pulang);

                if ($update) {
                    // Simpan file foto pulang
                    Storage::disk('public')->put($filePath, $image_binary);
                    return $this->respondAbsensi($request, 'success', 'Terima kasih, Hati-Hati di Jalan', 'out', (int) $absensi->id, 'out');
                } else {
                    return $this->respondAbsensi($request, 'error', 'Maaf Gagal Absen Pulang, Hubungi Tim IT', 'out', (int) $absensi->id, 'out');
                }
            }
        }
        // Jika belum absen masuk sama sekali, buat record baru
        else if ($cek_masuk == 0) {
            $data = [
                'nik' => $nik,
                'tgl_absensi' => $tgl_absensi,
                'jam_in' => $jam,
                'jam_out' => null,
                'foto_in' => $fileName, // Simpan nama file
                'foto_out' => null,
                'lokasi_in' => $lokasi,
                'lokasi_out' => null
            ];

            $simpan = DB::table('absensi')->insert($data);

            if ($simpan) {
                // Simpan file foto masuk
                Storage::disk('public')->put($filePath, $image_binary);
                // Get the latest inserted id best-effort (not perfect under concurrency, but good for audit)
                $absensiId = (int) (DB::getPdo()->lastInsertId() ?: 0);
                return $this->respondAbsensi($request, 'success', 'Terima kasih, Selamat Bekerja!', 'in', $absensiId ?: null, 'in');
            } else {
                return $this->respondAbsensi($request, 'error', 'Maaf Gagal Absen Masuk, Hubungi Tim IT', 'in', null, 'in');
            }
        }
        // Jika sudah absen masuk dan pulang, beri pesan error
        else {
            return $this->respondAbsensi($request, 'error', 'Anda sudah melakukan absen masuk dan pulang hari ini', 'in', null, 'unknown');
        }
    }

    // fungsi menghitung jarak
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }
    public function profil()
    {
        $nik = Auth::guard('pegawai')->user()->nik;
        $pegawai = DB::table('pegawai')->where('nik', $nik)->first();
        return view('layouts.profil', compact('pegawai'));
    }

    public function updateprofile(Request $request)
    {
        $nik = Auth::guard('pegawai')->user()->nik;
        $pegawai = DB::table('pegawai')->where('nik', $nik)->first();
        $uploadedFoto = null;
        $fotoFileName = $pegawai->foto;
        $hapusFoto = $request->input('hapus_foto') == '1';

        // Normalisasi input (boleh kosong, hanya yang diisi yang akan di-update)
        $no_hp_input = trim((string) $request->no_hp);
        $password_input = (string) $request->password;

        if ($request->hasFile('foto')) {
            $uploadedFoto = $request->file('foto');
            $fotoFileName = $nik . '.' . $uploadedFoto->getClientOriginalExtension();
        }

        // Susun data yang akan di-update hanya untuk field yang diisi
        $data = [];
        if ($no_hp_input !== '') {
            $data['no_hp'] = $no_hp_input;
        }
        if ($password_input !== '') {
            $data['password'] = Hash::make($password_input);
        }

        // Handle delete current photo request
        if ($hapusFoto) {
            if (!empty($pegawai->foto)) {
                Storage::disk('public')->delete('uploads/pegawai/' . $pegawai->foto);
            }
            $data['foto'] = null;
        } elseif ($uploadedFoto) {
            // Akan menyimpan nama file baru hanya jika ada upload foto
            $data['foto'] = $fotoFileName;
        }

        // Jika tidak ada field yang diubah sama sekali, langsung beri pesan
        if (empty($data)) {
            return Redirect::back()->with(['warning' => 'Tidak ada perubahan data yang disimpan']);
        }

        // Lakukan update data ke database (hanya field yang ada di $data)
        $update = DB::table('pegawai')
            ->where('nik', $nik)
            ->update($data);

        // Simpan / timpa file foto baru jika di-upload dan tidak sedang dihapus
        if ($uploadedFoto && !$hapusFoto) {
            // Hapus file lama jika berbeda nama
            if (!empty($pegawai->foto) && $pegawai->foto !== $fotoFileName) {
                Storage::disk('public')->delete('uploads/pegawai/' . $pegawai->foto);
            }
            Storage::disk('public')->put(
                'uploads/pegawai/' . $fotoFileName,
                file_get_contents($uploadedFoto)
            );
        }

        // Anggap BERHASIL jika:
        // - ada baris yang ter-update, atau
        // - ada upload foto baru, atau
        // - ada permintaan hapus foto
        if ($update > 0 || $uploadedFoto || $hapusFoto) {
            return Redirect::back()->with(['success' => 'Data Berhasil Di Update']);
        } else {
            return Redirect::back()->with(['error' => 'Data Gagal Di Update']);
        }

        // Jika tidak ada perubahan sama sekali (tidak update, tidak upload, tidak hapus),
        // beri pesan lebih informatif, bukan error proses.
        return Redirect::back()->with(['warning' => 'Tidak ada perubahan data yang disimpan']);
    }

    public function histori(){
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni",
         "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        return view ('layouts.histori', compact('namabulan'));
    }

    public function gethistori(Request $request) {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $nik = Auth::guard('pegawai')->user()->nik;

        $histori = DB::table('absensi')
        ->whereRaw('MONTH(tgl_absensi)="' . $bulan . '"')
        ->whereRaw('YEAR(tgl_absensi)="' . $tahun . '"')
        ->where('nik',$nik)
        ->orderBy('tgl_absensi')
        ->get();

        // Ambil tanggal yang memiliki pengajuan izin/sakit (termasuk rentang)
        $awalBulan = date('Y-m-01', strtotime($tahun.'-'.$bulan.'-01'));
        $akhirBulan = date('Y-m-t', strtotime($awalBulan));
        $izin = DB::table('pengajuan_izin')
            ->where('nik', $nik)
            ->where(function($q) use ($awalBulan, $akhirBulan) {
                $q->where(function($qq) use ($awalBulan, $akhirBulan) {
                    $qq->whereNotNull('izin_sampai')
                       ->where(function($x) use ($awalBulan, $akhirBulan) {
                           $x->whereBetween('tgl_izin', [$awalBulan, $akhirBulan])
                             ->orWhereBetween('izin_sampai', [$awalBulan, $akhirBulan])
                             ->orWhere(function($y) use ($awalBulan, $akhirBulan) {
                                 $y->where('tgl_izin', '<=', $awalBulan)
                                   ->where('izin_sampai', '>=', $akhirBulan);
                             });
                       });
                })
                ->orWhere(function($qq) use ($awalBulan, $akhirBulan) {
                    $qq->whereNull('izin_sampai')
                       ->whereBetween('tgl_izin', [$awalBulan, $akhirBulan]);
                });
            })
            ->get();

        $tanggalIzin = [];
        foreach ($izin as $z) {
            $start = $z->tgl_izin;
            $end = $z->izin_sampai ?? $z->tgl_izin;
            for ($d = strtotime($start); $d <= strtotime($end); $d = strtotime('+1 day', $d)) {
                $tanggalIzin[date('Y-m-d', $d)] = true;
            }
        }

        return view('layouts.gethistori', compact('histori','tanggalIzin'));
    }

    public function izin(){
        $nik = Auth::guard('pegawai')->user()->nik;
        $dataizin = DB::table('pengajuan_izin')
            ->where('nik',$nik)
            ->orderBy('tgl_izin','desc')
            ->get();
        return view('layouts.izin', compact('dataizin'));
    }

    public function buatizin(){
        return view('layouts.buatizin');
    }
    
    public function storeizin(Request $request) {
        $nik = Auth::guard('pegawai')->user()->nik;
        $status = $request->status;
        $keterangan = $request->keterangan;

        // Support both legacy single date and new date range
        $singleDate = $request->tgl_izin; // legacy
        $dari = $request->dari;
        $sampai = $request->sampai;

        // Optional upload when sakit
        if ($status === 's' && $request->hasFile('bukti_sakit')) {
            $bukti = $request->file('bukti_sakit');
            $filename = $nik . '-' . date('YmdHis') . '.' . $bukti->getClientOriginalExtension();
            Storage::disk('public')->put('uploads/bukti_sakit/' . $filename, file_get_contents($bukti));
        }

        // Simpan satu baris per rentang tanggal menggunakan tgl_izin (dari) dan izin_sampai (sampai)
        if (!empty($dari) && !empty($sampai)) {
            try {
                $start = new \DateTime($dari);
                $end = new \DateTime($sampai);
                if ($start > $end) {
                    return $this->respondIzin($request, 'error', 'Rentang tanggal tidak valid', 422);
                }
            } catch (\Exception $e) {
                return $this->respondIzin($request, 'error', 'Rentang tanggal tidak valid', 422);
            }

            // Cek tabrakan dengan pengajuan lain (overlap)
            $overlap = DB::table('pengajuan_izin')
                ->where('nik', $nik)
                ->where(function($q) use ($dari, $sampai) {
                    $q->where(function($qq) use ($dari, $sampai) {
                        $qq->whereNotNull('izin_sampai')
                           ->where(function($x) use ($dari, $sampai) {
                               $x->whereBetween('tgl_izin', [$dari, $sampai])
                                 ->orWhereBetween('izin_sampai', [$dari, $sampai])
                                 ->orWhere(function($y) use ($dari, $sampai) {
                                     $y->where('tgl_izin', '<=', $dari)
                                       ->where('izin_sampai', '>=', $sampai);
                                 });
                           });
                    })
                    ->orWhere(function($qq) use ($dari, $sampai) {
                        $qq->whereNull('izin_sampai')->whereBetween('tgl_izin', [$dari, $sampai]);
                    });
                })
                ->exists();
            if ($overlap) {
                return $this->respondIzin($request, 'warning', 'Rentang tanggal bertabrakan dengan pengajuan sebelumnya', 409);
            }

            $data = [
                'nik' => $nik,
                'tgl_izin' => $dari,
                'izin_sampai' => $sampai,
                'status' => $status,
                'keterangan' => $keterangan
            ];
            if (isset($filename)) {
                $data['surat_sakit'] = $filename;
            }
            $ok = DB::table('pengajuan_izin')->insert($data);
            if ($ok) {
                $pengajuanId = (int) (DB::getPdo()->lastInsertId() ?: 0);
                return $this->respondIzin($request, 'success', 'Pengajuan izin berhasil disimpan', 200, $pengajuanId ?: null);
            }
            return $this->respondIzin($request, 'error', 'Data Gagal Disimpan', 500);
        } elseif (!empty($singleDate)) {
            $exists = DB::table('pengajuan_izin')
                ->where('nik', $nik)
                ->where('tgl_izin', $singleDate)
                ->whereNull('izin_sampai')
                ->count();
            if ($exists) {
                return $this->respondIzin($request, 'warning', 'Izin tanggal tersebut sudah diajukan', 409);
            }
            $data = [
                'nik' => $nik,
                'tgl_izin' => $singleDate,
                'izin_sampai' => null,
                'status' => $status,
                'keterangan' => $keterangan
            ];
            if (isset($filename)) {
                $data['surat_sakit'] = $filename;
            }
            $ok = DB::table('pengajuan_izin')->insert($data);
            if ($ok) {
                $pengajuanId = (int) (DB::getPdo()->lastInsertId() ?: 0);
                return $this->respondIzin($request, 'success', 'Pengajuan izin berhasil disimpan', 200, $pengajuanId ?: null);
            }
            return $this->respondIzin($request, 'error', 'Data Gagal Disimpan', 500);
        } else {
            return $this->respondIzin($request, 'error', 'Harap isi tanggal izin atau rentang tanggal', 422);
        }

    }
    public function cekpengajuanizin(Request $request) {
        $nik = Auth::guard('pegawai')->user()->nik;

        // Return total count in current month for SweetAlert notification
        if ($request->mode === 'countmonth') {
            $bulanini = date('m');
            $tahunini = date('Y');
            $total = DB::table('pengajuan_izin')
                ->where('nik', $nik)
                ->whereRaw('MONTH(tgl_izin)= "' . $bulanini . '"')
                ->whereRaw('YEAR(tgl_izin)= "' . $tahunini . '"')
                ->count();
            return $total;
        }

        // Legacy: check specific date
        $tgl_izin = $request->tgl_izin;
        $cek = DB::table('pengajuan_izin')->where('nik', $nik)->where('tgl_izin', $tgl_izin)->count();
        return $cek;
    }

    // Batalkan pengajuan izin/sakit (pegawai) sebelum disetujui
    public function batalkanizin($id)
    {
        $nik = Auth::guard('pegawai')->user()->nik;
        $deleted = DB::table('pengajuan_izin')
            ->where('id', $id)
            ->where('nik', $nik)
            ->where(function($q){
                $q->whereNull('status_approved')->orWhere('status_approved', 0);
            })
            ->delete();
        if ($deleted) {
            return Redirect::back()->with(['success' => 'Pengajuan berhasil dibatalkan']);
        }
        return Redirect::back()->with(['error' => 'Tidak dapat membatalkan pengajuan ini']);
    }
}
