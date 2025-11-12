<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        //DATA MASTER PEGAWAI DASHBOARD
        $query = User::query();
        // Select from the correct table name
        $query->select('users.*');
        $query->orderBy('name');
        // Match search field name with the view (nama_admin)
        if (!empty($request->nama_admin)) {
            $query->where('name', 'like', '%' . $request->nama_admin . '%');
        }
        $admin = $query->paginate(10);
        return view('dbadmin.index', compact('admin'));
    }

    public function store(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        $passwordInput = $request->password;
        // Default password ke admin123 jika kosong
        $password = Hash::make(!empty($passwordInput) ? $passwordInput : 'admin123');


        try {
            $data = [
                'name' => $name,
                'email' => $email,
                'password' => $password
            ];
            $simpan = DB::table('users')->insert($data);
            if ($simpan) {
                return Redirect::back()->with(['success' => 'Data Berhasil Di simpan']);
            }
        } catch (\Exception $e) {
            // dd($e);
            if ($e->getCode() == 23000) {
                $message = "Data dengan Email" . $email . "Sudah Ada";
            }
            return Redirect::back()->with(['warning' => 'Data Gagal Di simpan'.$e->getMessage()]);
        }
    }

    public function edit(Request $request)
    {
        $name = $request->name;
        $admin = DB::table('users')->where('name', $name)->first();
        return view('dbadmin.edit', compact('admin'));
    }

    public function update($name, Request $request)
    {
        $originalName = $name; // route parameter (nama lama)
        $newName = $request->name;
        $email = $request->email;
        $passwordInput = $request->password;
        
        try {
            $data = [
                'name' => $newName,
                'email' => $email,
            ];
            // Update password hanya jika diisi
            if (!empty($passwordInput)) {
                $data['password'] = Hash::make($passwordInput);
            }
            $update = DB::table('users')->where('name', $originalName)->update($data);
            // Anggap sukses walau 0 row (data tidak berubah)
            if ($update !== false) {
                return Redirect::back()->with(['success' => 'Data Berhasil Di Update']);
            }
        } catch (\Exception $e) {
            // dd($e);
            return Redirect::back()->with(['warning' => 'Data Gagal Di Update']);
        }

    }
    public function delete($name){
        $delete = DB::table('users')->where
        ('name', $name)->delete();
        if($delete){
            return Redirect::back()->with(['success'=>'Data Berhasil Di hapus']);
        }else {
            return Redirect::back()->with(['warning'=>'Data Gagal Di hapus']);
        }
    }

    public function resetpassword($name)
    {
        try {
            $updated = DB::table('users')
                ->where('name', $name)
                ->update(['password' => Hash::make('admin123')]);
            if ($updated) {
                return response()->json(['status' => 'success']);
            }
            return response()->json(['status' => 'error'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
