<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $hariini = date("Y-m-d");
        $bulanini = date("m");
        $tahunini = date("Y");
        $nik = Auth::guard('pegawai')->user()->nik;
        $absensihariini = DB::table('absensi')->where('nik', $nik)->where('tgl_absensi', $hariini)->first();
        $historibulanini = DB::table('absensi')
            ->where('nik', $nik)
            ->whereRaw('MONTH(tgl_absensi)= "' . $bulanini . '"')
            ->whereRaw('YEAR(tgl_absensi)="' . $tahunini . '"')
            ->orderBy('tgl_absensi')
            ->get();

        $rekapabsensi = DB::table('absensi')
            ->selectRaw('COUNT(nik) as jmlhadir, SUM(IF(jam_in > "07:00",1,0)) as jmlterlambat')
            ->where('nik', $nik)
            ->whereRaw('MONTH(tgl_absensi)= "' . $bulanini . '"')
            ->whereRaw('YEAR(tgl_absensi)="' . $tahunini . '"')
            ->first();

        $leaderboard = DB::table('absensi')
            ->join('pegawai', 'absensi.nik', '=', 'pegawai.nik')
            ->where('tgl_absensi', $hariini)
            ->orderBy('jam_in')
            ->get();

        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        $rekapizin = DB::table('pengajuan_izin')
            ->selectRaw('SUM(IF(status="i",1,0)) as jmlizin,SUM(IF(status="s",1,0)) as jmlsakit')
            ->where('nik', $nik)
            ->whereRaw('MONTH(tgl_izin)= "' . $bulanini . '"')
            ->whereRaw('YEAR(tgl_izin)="' . $tahunini . '"')
            ->where('status_approved',1)
            ->first();

        return view('layouts.app', compact(
            'absensihariini',
            'historibulanini',
            'namabulan',
            'bulanini',
            'tahunini',
            'rekapabsensi',
            'leaderboard',
            'rekapizin'
        ));
    }
}
