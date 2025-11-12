<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class LokasiController extends Controller
{
    public function lokasisekolah()
    {
        $lokasi_sklh = DB::table('lokasi_sekolah')->where('id', 1)->first();
        return view('konfigurasi.lokasi', compact('lokasi_sklh'));
    }

    public function updatelokasisekolah(Request $request)
    {
        // PERBAIKAN: Gunakan nama field yang sesuai dengan form
        $lokasi_sekolah = $request->lokasi_sekolah; // sebelumnya $request->lokasi
        $radius = $request->radius;
        // Optional fields (tilt only; shape fixed to rectangle in UI)
        $tilt = (int) $request->input('tilt_value', 0);

        // Validasi data
        $request->validate([
            'lokasi_sekolah' => 'required',
            'radius' => 'required|numeric',
            'tilt_value' => 'nullable|integer|min:0|max:359',
        ]);

        $payload = [
            'lokasi' => $lokasi_sekolah,
            'radius' => $radius,
        ];
        // Save tilt if column exists
        try {
            DB::table('lokasi_sekolah')->where('id', 1)->update(array_merge($payload, ['tilt' => $tilt]));
            $update = true;
        } catch (\Throwable $e) {
            // Fallback: column 'tilt' might not exist yet
            $update = DB::table('lokasi_sekolah')->where('id', 1)->update($payload);
        }

        if ($update) {
            return Redirect::back()->with(['success' => 'Data Berhasil Di Update!']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Di Update!']);
        }
    }
}
