<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Check if password change is required
            if (!Auth::user()->is_password_changed) {
                return redirect()->route('password.change_form');
            }

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'phone' => 'The provided credentials do not match our records.',
        ])->onlyInput('phone');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function changePasswordForm()
    {
        return view('auth.change_password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        if (!$user)
            abort(401);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'is_password_changed' => true
        ]);

        return redirect()->route('dashboard')->with('success', 'Password updated successfully.');
    }
}
