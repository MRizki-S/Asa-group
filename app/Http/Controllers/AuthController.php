<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // dd($credentials);
        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            return redirect()
                ->intended('/') // arahkan ke halaman tujuan
                ->with('success', 'Selamat datang, ' . Auth::user()->username . '! Anda berhasil login.');
        }

        return back()->withErrors([
            'username atau password salah.',
        ])->onlyInput('no_hp');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
