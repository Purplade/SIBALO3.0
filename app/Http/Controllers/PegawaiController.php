<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        //DATA MASTER PEGAWAI DASHBOARD
        $query = Pegawai::query();
        $query->select('pegawai.*');
        $query->orderBy('nama_lengkap');
        if (!empty($request->nama_pegawai)) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama_pegawai . '%');
        }
        $pegawai = $query->paginate(10);
        return view('dbdmpegawai.index', compact('pegawai'));
    }

    public function store(Request $request)
    {
        $nik = $request->nik;
        $nama_lengkap = $request->nama_lengkap;
        $jabatan = $request->jabatan;
        $no_hp = $request->no_hp;
        $passwordInput = $request->password;
        $password = Hash::make(!empty($passwordInput) ? $passwordInput : 'pegawai123');
        if ($request->hasFile('foto')) {
            $foto = $nik . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = null;
        }

        try {
            $data = [
                'nik' => $nik,
                'nama_lengkap' => $nama_lengkap,
                'jabatan' => $jabatan,
                'no_hp' => $no_hp,
                'foto' => $foto,
                'password' => $password
            ];
            $simpan = DB::table('pegawai')->insert($data);
            if ($simpan) {
                if ($request->hasFile('foto')) {
                    // Simpan ke disk public -> storage/app/public/uploads/pegawai
                    Storage::disk('public')->putFileAs('uploads/pegawai', $request->file('foto'), $foto);
                }
                return Redirect::back()->with(['success' => 'Data Berhasil Di simpan']);
            }
        } catch (\Exception $e) {
            // dd($e);
            if ($e->getCode() == 23000) {
                $message = "Data dengan NIK" . $nik . "Sudah Ada";
            }
            return Redirect::back()->with(['warning' => 'Data Gagal Di simpan'.$e->getMessage()]);
        }
    }

    public function edit(Request $request)
    {
        $nik = $request->nik;
        $pegawai = DB::table('pegawai')->where('nik', $nik)->first();
        return view('dbdmpegawai.edit', compact('pegawai'));
    }

    public function update($nik, Request $request)
    {
        $nik = $request->nik;
        $nama_lengkap = $request->nama_lengkap;
        $jabatan = $request->jabatan;
        $no_hp = $request->no_hp;
        $passwordInput = $request->password; // optional
        $old_foto = $request->old_foto;
        if ($request->hasFile('foto')) {
            $foto = $nik . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = $old_foto;
        }

        try {
            $data = [
                'nama_lengkap' => $nama_lengkap,
                'jabatan' => $jabatan,
                'no_hp' => $no_hp,
                'foto' => $foto,
            ];
            // Update password hanya jika diisi dari form
            if (!empty($passwordInput)) {
                $data['password'] = Hash::make($passwordInput);
            }
            $update = DB::table('pegawai')->where('nik', $nik)->update($data);
            // Anggap sukses walau 0 row (data tidak berubah)
            if ($update !== false) {
                if ($request->hasFile('foto')) {
                    // Hapus file lama jika ada (kedua kemungkinan lokasi)
                    if (!empty($old_foto)) {
                        Storage::disk('public')->delete('uploads/pegawai/' . $old_foto);
                        Storage::disk('public')->delete('pegawai/' . $old_foto);
                    }
                    // Simpan file baru ke storage/app/public/uploads/pegawai
                    Storage::disk('public')->putFileAs('uploads/pegawai', $request->file('foto'), $foto);
                }
                return redirect('/pegawai')->with(['success' => 'Data Berhasil Di Update']);
            }
        } catch (\Exception $e) {
            // dd($e);
            return Redirect::back()->with(['warning' => 'Data Gagal Di Update']);
        }

    }
    public function delete($nik){
        $delete = DB::table('pegawai')->where
        ('nik', $nik)->delete();
        if($delete){
            return Redirect::back()->with(['success'=>'Data Berhasil Di hapus']);
        }else {
            return Redirect::back()->with(['warning'=>'Data Gagal Di hapus']);
        }
    }

    public function deletefoto($nik)
    {
        try {
            $pegawai = DB::table('pegawai')->where('nik', $nik)->first();
            if (!$pegawai) {
                return Redirect::back()->with(['warning' => 'Pegawai tidak ditemukan']);
            }

            if (!empty($pegawai->foto)) {
                // Hapus dari dua kemungkinan lokasi
                Storage::disk('public')->delete('uploads/pegawai/' . $pegawai->foto);
                Storage::disk('public')->delete('pegawai/' . $pegawai->foto);
            }

            DB::table('pegawai')->where('nik', $nik)->update(['foto' => null]);

            return Redirect::back()->with(['success' => 'Foto berhasil dihapus']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => 'Gagal menghapus foto']);
        }
    }

    public function resetpassword($nik)
    {
        try {
            $updated = DB::table('pegawai')
                ->where('nik', $nik)
                ->update(['password' => Hash::make('pegawai123')]);
            if ($updated) {
                return response()->json(['status' => 'success']);
            }
            return response()->json(['status' => 'error'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
