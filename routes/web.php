<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PegawaiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ROUTE ROOT: Arahkan sesuai guard yang sedang login
Route::get('/', function () {
    if (Auth::guard('pegawai')->check()) {
        return redirect()->route('home');
    }
    if (Auth::guard('user')->check()) {
        return redirect('/dashboard');
    }
    return redirect()->route('login');
});


// ROUTE HALAMAN LOGIN HOME
Route::middleware('guest:pegawai')->group(function () {
    // ROUTE LOGIN
    Route::get('/login', function () {
        // Jika sudah login sebagai pegawai, arahkan ke home
        if (Auth::guard('pegawai')->check()) {
            return redirect()->route('home');
        }
        // Jika login sebagai admin, arahkan ke dashboard admin
        if (Auth::guard('user')->check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    })->name('login');
    
    // ROUTE PROSES LOGIN
    Route::post('/proseslogin', [AuthController::class, 'proseslogin']);
});


// ROUTE HOME dengan guard-aware redirect
Route::get('/home', function () {
    if (!Auth::guard('pegawai')->check()) {
        return redirect('/login');
    }
    return app(HomeController::class)->index();
})->name('home');

// ROUTE SAAT LOGIN SBG PEGAWAI
Route::middleware(['auth:pegawai'])->group(function () {

    // ROUTE ABSENSI
    Route::get('/absensi/selfie', [AbsensiController::class, 'selfie']);
    Route::post('/absensi/store', [AbsensiController::class, 'store']);

    // ROUTE UNTUK PROFIL
    Route::get('/editprofile', [AbsensiController::class, 'editprofile']);
    Route::post('/absensi/{nik}/updateprofile', [AbsensiController::class, 'updateprofile']);

    // ROUTE HISTORI ABSENSI
    Route::get('/absensi/histori', [AbsensiController::class, 'histori']);
    Route::post('/gethistori', [AbsensiController::class, 'gethistori']);

    // ROUTE IZIN
    Route::get('/absensi/izin', [AbsensiController::class, 'izin']);
    Route::get('/absensi/buatizin', [AbsensiController::class, 'buatizin']);
    Route::post('/absensi/storeizin', [AbsensiController::class, 'storeizin']);
    Route::post('/cekpengajuanizin', [AbsensiController::class, 'cekpengajuanizin']);
    Route::get('/absensi/{id}/batalkan', [AbsensiController::class, 'batalkanizin']);

    // ROUTE PROSES LOGOUT
    Route::get('/proseslogout', [AuthController::class, 'proseslogout']);
});

// ROUTE HALAMAN LOGIN DASHBOARD
Route::middleware('guest:user')->group(function () {
    // ROUTE LOGIN
    Route::get('/panel', function () {
        // Jika sudah login sebagai admin, langsung ke dashboard
        if (Auth::guard('user')->check()) {
            return redirect('/dashboard');
        }
        // Jika login sebagai pegawai, arahkan ke home pegawai
        if (Auth::guard('pegawai')->check()) {
            return redirect()->route('home');
        }
        return view('auth.loginadmin');
    });

    //ROUTE PROSES LOGIN
    Route::post('/prosesloginadmin', [AuthController::class, 'prosesloginadmin']);
});

// DASHBOARD route with guard-aware redirect
Route::get('/dashboard', function () {
    if (!Auth::guard('user')->check()) {
        return redirect('/panel');
    }
    return app(DashboardController::class)->dashboard();
});

// ROUTE SAAT LOGIN SEBAGAI ADMIN
Route::middleware(['auth:user'])->group(function () {
    Route::get('/proseslogoutadmin', [AuthController::class, 'proseslogoutadmin']);

    // ROUTE DATA MASTER ADMIN
    Route::get('/admin',[AdminController::class, 'index']);
    Route::post('/admin/store',[AdminController::class,'store']);
    Route::post('/admin/edit',[AdminController::class,'edit']);
    Route::post('/admin/{name}/update',[AdminController::class,'update']);
    Route::delete('/admin/{name}/delete',[AdminController::class,'delete']);
    Route::post('/admin/{name}/resetpassword',[AdminController::class,'resetpassword']);

    // ROUTE DATA MASTER PEGAWAI
    Route::get('/pegawai',[PegawaiController::class, 'index']);
    Route::post('/pegawai/store',[PegawaiController::class,'store']);
    Route::post('/pegawai/edit',[PegawaiController::class,'edit']);
    Route::post('/pegawai/{nik}/update',[PegawaiController::class,'update']);
    Route::post('/pegawai/{nik}/deletefoto',[PegawaiController::class,'deletefoto']);
    Route::delete('/pegawai/{nik}/delete',[PegawaiController::class,'delete']);
    Route::post('/pegawai/{nik}/resetpassword',[PegawaiController::class,'resetpassword']);

    // ROUTE MONITORING ABSENSI PEGAWAI
    Route::get('/monitoring', [DashboardController::class, 'monitoring']);
    Route::post('/getabsensi', [DashboardController::class, 'getabsensi']);
    Route::get('/absensi/{id}/hapusjamout', [DashboardController::class, 'hapusjamout']);
    
    // ROUTE LAPORAN ABSENSI PEGAWAI
    Route::get('/laporanabsensi', [DashboardController::class, 'laporan']);
    Route::post('/cetaklaporan', [DashboardController::class, 'cetaklaporan']);
    Route::get('/rekap', [DashboardController::class, 'rekap']);
    Route::post('/cetakrekap', [DashboardController::class, 'cetakrekap']);

    // ROUTE KONFIGURASI LOKASI
    Route::get('/konfigurasilokasi',[LokasiController::class, 'lokasisekolah']);
    Route::post('/updatelokasisekolah', [LokasiController::class, 'updatelokasisekolah']);

    //ROUTE PERSETUJUAN IZIN DAN SAKIT PEGAWAI
    Route::get('/izinsakit', [DashboardController::class, 'izinsakit']);
    Route::post('/approveizinsakit', [DashboardController::class, 'approveizinsakit']);
    Route::get('/{id}/batalkanizinsakit', [DashboardController::class, 'batalkanizinsakit']);
});
