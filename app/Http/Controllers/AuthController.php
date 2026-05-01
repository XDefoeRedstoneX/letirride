<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
 //LOGIN & REGISTER
    public function showLogin(){
        return view('ganti');
    }
    public function showRegister(){
        return view('ganti');}

    public function logAuth(Request $request){
        $creds = $request;
        $creds['password'] = md5($creds['password']);
        $creds->validate([
            'email' => 'required|email:dns',
            'password' => 'required'
        ]);

        if (Auth::attempt($creds)){
            $request->session()->regenerate();
            return redirect()->intended(route('ganti'));
        }
        return back()->with('loginError', 'Email atau password salah!');
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('home'));
    }

    public function regAuth(Request $request){
        $creds = $request->validate([
            'name' => 'required',
            'email' => 'required|email:dns|unique:users',
            'password' => 'required|min:5'
        ]);

        $creds['password'] = md5($creds['password']);
        \App\Models\User::create($creds);
        return redirect(route('login'))->with('success', 'Registrasi berhasil! Silakan login.');
    }

    //SETTINGS
    public function changeEmail(Request $request){
        $request->validate([
            'email' => 'required|email:dns|unique:users'
        ]);

        Auth::user()->update($request->only('email'));
        return back()->with('success', 'Email berhasil diubah!');
    }

    public function changePassword(Request $request){
        $request->validate([
            'password' => 'required|min:5'
        ]);

        Auth::user()->update(['password' => md5($request->password)]);
        return back()->with('success', 'Password berhasil diubah!');
    }

}
