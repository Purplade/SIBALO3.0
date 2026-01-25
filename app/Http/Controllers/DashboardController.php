<?php

namespace App\Http\Controllers;

use App\Models\Pengajuanizin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $hariini = date("Y-m-d");
        $rekapabsensi = DB::table('absensi')
            ->selectRaw('COUNT(nik) as jmlhadir, COALESCE(SUM(IF(jam_in > "07:00",1,0)),0) as jmlterlambat')
            ->where('tgl_absensi', $hariini)
            ->first();

        $rekapizin = DB::table('pengajuan_izin')
            ->selectRaw('SUM(IF(status="i",1,0)) as jmlizin,SUM(IF(status="s",1,0)) as jmlsakit')
            ->where('tgl_izin', $hariini)
            ->where('status_approved', 1)
            ->first();
        $pegawai = DB::table('pegawai')->orderBy('nama_lengkap')->get();
        return view('dashboard.dbadmin', compact('rekapabsensi', 'rekapizin', 'pegawai'));
    }

    public function monitoring()
    {
        return view('dbmonitoring.monitoring');
    }

    public function anomali(Request $request)
    {
        $today = Carbon::today();
        $dari = $request->input('dari', $today->copy()->subDays(7)->toDateString());
        $sampai = $request->input('sampai', $today->toDateString());
        $nik = trim((string) $request->input('nik', ''));
        $flag = trim((string) $request->input('flag', ''));
        $showAll = $request->boolean('show_all', false);

        $flags = [
            'delayed_sync' => 'Upload terlambat',
            'client_time_in_future' => 'Jam device tidak wajar',
        ];

        $absensiQ = DB::table('absensi_events as e')
            ->leftJoin('pegawai as p', 'e.nik', '=', 'p.nik')
            ->select(
                'e.*',
                'p.nama_lengkap',
                'p.jabatan'
            )
            ->orderByDesc('e.received_at');

        $izinQ = DB::table('izin_events as e')
            ->leftJoin('pegawai as p', 'e.nik', '=', 'p.nik')
            ->select(
                'e.*',
                'p.nama_lengkap',
                'p.jabatan'
            )
            ->orderByDesc('e.received_at');

        // Date filters (based on received_at = server time)
        if (!empty($dari) && !empty($sampai)) {
            $absensiQ->whereBetween(DB::raw('DATE(e.received_at)'), [$dari, $sampai]);
            $izinQ->whereBetween(DB::raw('DATE(e.received_at)'), [$dari, $sampai]);
        } elseif (!empty($dari)) {
            $absensiQ->whereDate('e.received_at', $dari);
            $izinQ->whereDate('e.received_at', $dari);
        }

        if ($nik !== '') {
            $absensiQ->where('e.nik', $nik);
            $izinQ->where('e.nik', $nik);
        }

        if (!$showAll) {
            $absensiQ->whereNotNull('e.anomaly_flags');
            $izinQ->whereNotNull('e.anomaly_flags');
        }

        if ($flag !== '' && array_key_exists($flag, $flags)) {
            $absensiQ->whereJsonContains('e.anomaly_flags', $flag);
            $izinQ->whereJsonContains('e.anomaly_flags', $flag);
        }

        $absensiEvents = $absensiQ->simplePaginate(20)->appends($request->all());
        $izinEvents = $izinQ->simplePaginate(20)->appends($request->all());

        $countAbsensi = (clone $absensiQ)->count();
        $countIzin = (clone $izinQ)->count();

        return view('dbmonitoring.anomali', compact(
            'dari',
            'sampai',
            'nik',
            'flag',
            'showAll',
            'flags',
            'absensiEvents',
            'izinEvents',
            'countAbsensi',
            'countIzin'
        ));
    }

    public function getabsensi(Request $request)
    {
        $dari = $request->dari;
        $sampai = $request->sampai;

        $query = DB::table('absensi')
            ->select('absensi.*', 'nama_lengkap')
            ->join('pegawai', 'absensi.nik', '=', 'pegawai.nik');

        if (!empty($dari) && !empty($sampai)) {
            $query->whereBetween('tgl_absensi', [$dari, $sampai]);
        } elseif (!empty($dari)) {
            $query->where('tgl_absensi', $dari);
        }

        $absensi = $query->orderBy('tgl_absensi', 'asc')->get();

        return view('dbmonitoring.getabsensi', compact('absensi'));
    }

    public function hapusjamout($id)
    {
        $update = DB::table('absensi')->where('id', $id)->update([
            'jam_out' => null,
            'foto_out' => null,
            'lokasi_out' => null
        ]);
        if ($update) {
            return Redirect::back()->with(['success' => 'Jam pulang dihapus']);
        }
        return Redirect::back()->with(['warning' => 'Gagal menghapus jam pulang']);
    }

    public function laporan()
    {
        $namabulan = [
            "",
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember"
        ];
        $pegawai = DB::table('pegawai')->orderBy('nama_lengkap')->get();
        return view('dblaporan.laporan', compact('namabulan', 'pegawai'));
    }

    public function cetaklaporan(Request $request)
    {
        $nik = $request->nik;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $namabulan = [
            "",
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember"
        ];
        $pegawai = DB::table('pegawai')->where('nik', $nik)->first();
        $absensi = DB::table('absensi')
            ->where('nik', $nik)
            ->whereRaw('MONTH(tgl_absensi)="' . $bulan . '"')
            ->whereRaw('YEAR(tgl_absensi)="' . $tahun . '"')
            ->orderBy('tgl_absensi')
            ->get();

        if (isset($_POST['exportpdf'])) {
            $time = date("d-M-Y H:i:s");
            $pdf = Pdf::setOptions([
                    'isRemoteEnabled' => true,
                    'chroot' => public_path(),
                ])
                ->loadView('dblaporan.cetaklaporan', [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'namabulan' => $namabulan,
                    'pegawai' => $pegawai,
                    'absensi' => $absensi,
                    'isPdf' => true,
                ])
                ->setPaper('a4');
            return $pdf->download("laporan_absensi_$time.pdf");
        } elseif (isset($_POST['exportexcel'])) {
            $time = date("d-M-Y H:i:s");
            // fungsi header mengirimkan row data excel
            header("Content-type: application/vnd-ms-excel");
            // Mendefinisikan nama file export "hasil-export.xls"
            header("Content-Disposition: attachment; filename=laporan_absensi_$time.xls");
        }
        return view('dblaporan.cetaklaporan', compact('bulan', 'tahun', 'namabulan', 'pegawai', 'absensi'));
    }

    public function rekap()
    {
        $namabulan = [
            "",
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember"
        ];
        return view('dblaporan.rekap', compact('namabulan'));
    }

    public function cetakrekap(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $namabulan = [
            "",
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember"
        ];
        $rekap = DB::table('absensi')
            ->selectRaw('absensi.nik,nama_lengkap,
MAX(IF(DAY(tgl_absensi) = 1,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_1,
MAX(IF(DAY(tgl_absensi) = 2,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_2,
MAX(IF(DAY(tgl_absensi) = 3,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_3,
MAX(IF(DAY(tgl_absensi) = 4,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_4,
MAX(IF(DAY(tgl_absensi) = 5,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_5,
MAX(IF(DAY(tgl_absensi) = 6,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_6,
MAX(IF(DAY(tgl_absensi) = 7,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_7,
MAX(IF(DAY(tgl_absensi) = 8,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_8,
MAX(IF(DAY(tgl_absensi) = 9,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_9,
MAX(IF(DAY(tgl_absensi) = 10,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_10,
MAX(IF(DAY(tgl_absensi) = 11,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_11,
MAX(IF(DAY(tgl_absensi) = 12,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_12,
MAX(IF(DAY(tgl_absensi) = 13,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_13,
MAX(IF(DAY(tgl_absensi) = 14,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_14,
MAX(IF(DAY(tgl_absensi) = 15,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_15,
MAX(IF(DAY(tgl_absensi) = 16,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_16,
MAX(IF(DAY(tgl_absensi) = 17,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_17,
MAX(IF(DAY(tgl_absensi) = 18,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_18,
MAX(IF(DAY(tgl_absensi) = 19,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_19,
MAX(IF(DAY(tgl_absensi) = 20,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_20,
MAX(IF(DAY(tgl_absensi) = 21,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_21,
MAX(IF(DAY(tgl_absensi) = 22,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_22,
MAX(IF(DAY(tgl_absensi) = 23,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_23,
MAX(IF(DAY(tgl_absensi) = 24,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_24,
MAX(IF(DAY(tgl_absensi) = 25,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_25,
MAX(IF(DAY(tgl_absensi) = 26,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_26,
MAX(IF(DAY(tgl_absensi) = 27,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_27,
MAX(IF(DAY(tgl_absensi) = 28,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_28,
MAX(IF(DAY(tgl_absensi) = 29,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_29,
MAX(IF(DAY(tgl_absensi) = 30,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_30,
MAX(IF(DAY(tgl_absensi) = 31,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_31
')
            ->join('pegawai', 'absensi.nik', '=', 'pegawai.nik')
            ->whereRaw('MONTH(tgl_absensi)="' . $bulan . '"')
            ->whereRaw('YEAR(tgl_absensi)="' . $tahun . '"')
            ->groupByRaw('absensi.nik,nama_lengkap')
            ->get();

        if (isset($_POST['exportexcel'])) {
            $time = date("d-M-Y H:i:s");
            // fungsi header mengirimkan row data excel
            header("Content-type: application/vnd-ms-excel");
            // Mendefinisikan nama file export "hasil-export.xls"
            header("COntent-Disposition: attachmen; filename=Rekap Absensi Pegawai $time.xls");
        }
        if (isset($_POST['exportpdf'])) {
            $time = date("d-M-Y H:i:s");
            $pdf = Pdf::setOptions([
                    'isRemoteEnabled' => true,
                    'chroot' => public_path(),
                ])
                ->loadView('dblaporan.cetakrekap', [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'rekap' => $rekap,
                    'namabulan' => $namabulan,
                    'isPdf' => true,
                ])
                ->setPaper('a3', 'landscape');
            return $pdf->download("rekap_absensi_$time.pdf");
        }

        return view('dblaporan.cetakrekap', compact('bulan', 'tahun', 'rekap', 'namabulan'));
    }

    public function izinsakit(Request $request)
    {
        $query = Pengajuanizin::query();
        $query->select(
            'pengajuan_izin.id',
            'pengajuan_izin.tgl_izin',
            'pengajuan_izin.izin_sampai',
            'pengajuan_izin.surat_sakit',
            'pengajuan_izin.nik',
            'pegawai.nama_lengkap',
            'pegawai.jabatan',
            'pengajuan_izin.status',
            'pengajuan_izin.status_approved',
            'pengajuan_izin.keterangan'
        );
        $query->join('pegawai', 'pengajuan_izin.nik', '=', 'pegawai.nik');
        if (!empty($request->dari) && !empty($request->sampai)) {
            $dari = $request->dari;
            $sampai = $request->sampai;
            // Ambil data yang overlap dengan rentang filter
            $query->where(function ($q) use ($dari, $sampai) {
                $q->where(function ($qq) use ($dari, $sampai) {
                    // Baris rentang (izin_sampai tidak null)
                    $qq->whereNotNull('izin_sampai')
                        ->where(function ($x) use ($dari, $sampai) {
                            $x->whereBetween('tgl_izin', [$dari, $sampai])
                                ->orWhereBetween('izin_sampai', [$dari, $sampai])
                                ->orWhere(function ($y) use ($dari, $sampai) {
                                    $y->where('tgl_izin', '<=', $dari)
                                        ->where('izin_sampai', '>=', $sampai);
                                });
                        });
                })
                ->orWhere(function ($qq) use ($dari, $sampai) {
                    // Baris harian (izin_sampai null)
                    $qq->whereNull('izin_sampai')
                        ->whereBetween('tgl_izin', [$dari, $sampai]);
                });
            });
        }

        if (!empty($request->nik)) {
            $query->where('pengajuan_izin.nik', $request->nik);
        }

        if (!empty($request->nama_lengkap)) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama_lengkap . '%');
        }

        if ($request->filled('status_approved') && in_array($request->status_approved, ['0', '1', '2'], true)) {
            $query->where('status_approved', $request->status_approved);
        }
        $query->orderBy('tgl_izin', 'desc');
        $izinsakit = $query->simplePaginate(10);
        $izinsakit->appends($request->all());
        return view('dbpersetujuan.izinsakit', compact('izinsakit'));
    }

    public function approveizinsakit(Request $request)
    {
        $status_approved = $request->status_approved;
        $id_izinsakit_form = $request->id_izinsakit_form;
        $update = DB::table('pengajuan_izin')
            ->where('id', $id_izinsakit_form)
            ->update(['status_approved' => $status_approved]);

        if ($update) {
            return Redirect::back()->with(['success' => 'Data Berhasil Di Update']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Di Update']);
        }
    }

    public function batalkanizinsakit($id)
    {
        $update = DB::table('pengajuan_izin')
            ->where('id', $id)
            ->update(['status_approved' => 0]);

        if ($update) {
            return Redirect::back()->with(['success' => 'Data Berhasil Di Update']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Di Update']);
        }
    }
}
