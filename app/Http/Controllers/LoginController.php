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
            'phone' => ['required', 'string', 'digits:10'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if password change is required
            if (!$user->is_password_changed) {
                return redirect()->route('password.change_form');
            }

            // Tenant-Aware Redirect
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }

            if ($user->kitchen_id && $user->kitchen) {
                return redirect("/k/{$user->kitchen->slug}/dashboard");
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

        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard')->with('success', 'Password updated successfully.');
        }

        if ($user->kitchen_id && $user->kitchen) {
            return redirect("/k/{$user->kitchen->slug}/dashboard")->with('success', 'Password updated successfully.');
        }

        return redirect()->route('dashboard')->with('success', 'Password updated successfully.');
    }
}
