<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // is_active = true: akun nonaktif tidak bisa masuk.
        $ok = Auth::attempt([
            'username' => $data['username'],
            'password' => $data['password'],
            'is_active' => true,
        ], $request->boolean('remember'));

        if (! $ok) {
            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi salah, atau akun tidak aktif.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended($request->user()->isStaff() ? '/admin' : '/akun');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
