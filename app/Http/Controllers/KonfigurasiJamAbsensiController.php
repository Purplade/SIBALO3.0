<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class KonfigurasiJamAbsensiController extends Controller
{
    public function index()
    {
        $konfigurasi = DB::table('konfigurasi_jam_absensi')->orderBy('id')->first();

        return view('konfigurasi.jamabsensi', compact('konfigurasi'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'jam_masuk_mulai' => 'required|date_format:H:i',
            'jam_pulang_mulai' => 'required|date_format:H:i',
        ]);

        $jamMasukMulai = $request->input('jam_masuk_mulai') . ':00';
        $jamPulangMulai = $request->input('jam_pulang_mulai') . ':00';

        if ($jamPulangMulai <= $jamMasukMulai) {
            return Redirect::back()->withInput()->with([
                'warning' => 'Jam pulang harus lebih besar dari jam masuk.',
            ]);
        }

        $existing = DB::table('konfigurasi_jam_absensi')->orderBy('id')->first();

        $payload = [
            'jam_masuk_mulai' => $jamMasukMulai,
            'jam_pulang_mulai' => $jamPulangMulai,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('konfigurasi_jam_absensi')
                ->where('id', $existing->id)
                ->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('konfigurasi_jam_absensi')->insert($payload);
        }

        return Redirect::back()->with([
            'success' => 'Jam absensi berhasil diperbarui.',
        ]);
    }
}
