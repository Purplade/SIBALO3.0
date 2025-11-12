<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function proseslogin(Request $request){
        if(Auth::guard('pegawai')->attempt(['nik' => $request->nik, 'password'=> $request->password])) {
            return redirect('/home');
        }else {
            return redirect('/login')->with(['warning' => 'NIP atau Password Salah']);
        }
    }

    public function proseslogout(Request $request) {
        if(Auth::guard('pegawai')->check()) {
            Auth::guard('pegawai')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login');
        }
    }

    public function prosesloginadmin(Request $request){
        $remember = (bool) $request->input('remember');
        if(Auth::guard('user')->attempt(['email' => $request->email, 'password'=> $request->password], $remember)) {
            $request->session()->regenerate();
            return redirect('/dashboard');
        } else {
            return redirect('/panel')->with(['warning' => 'Email atau Password Salah']);
        }
    }

    public function proseslogoutadmin(){
        if(Auth::guard('user')->check()) {
            Auth::guard('user')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/panel');
        }
    }
}
