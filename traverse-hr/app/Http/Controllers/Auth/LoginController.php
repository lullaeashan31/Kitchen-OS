<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RepeatedFailedLoginAlert;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * §6: failed-login lockout with exponential backoff, alert Super Admin by
 * email on repeated failures, no shared accounts (every login is a named
 * human, so we never auto-provision on first login).
 */
class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        $throttleKey = 'login:'.strtolower($credentials['email']).'|'.$request->ip();

        if ($user && $user->isLocked()) {
            throw ValidationException::withMessages([
                'email' => 'This account is temporarily locked due to repeated failed logins. Try again later.',
            ]);
        }

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Too many attempts. Try again in {$seconds} seconds.",
            ]);
        }

        if (! $user || ! $user->active || ! Auth::validate($credentials)) {
            RateLimiter::hit($throttleKey, $this->backoffSeconds($user));

            if ($user) {
                $user->increment('failed_login_count');
                $this->applyBackoffLock($user);
                $this->maybeAlertSuperAdmin($user);
                AuditLogger::log('login_failed', $user, actorId: $user->id);
            }

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $user->forceFill(['failed_login_count' => 0, 'locked_until' => null])->save();

        // Two-factor is opt-in (see config/security.php). Users who have
        // enrolled are always challenged; enrolment is only *forced* when
        // the mandatory setting is switched on for their role.
        if ($user->twoFactorEnabled()) {
            Auth::login($user);
            $request->session()->put('needs_2fa_challenge', true);

            return redirect()->route('two-factor.challenge');
        }

        if ($user->requiresTwoFactor()) {
            Auth::login($user);
            $request->session()->put('needs_2fa_enrollment', true);

            return redirect()->route('two-factor.setup');
        }

        Auth::login($user, $request->boolean('remember'));
        $user->forceFill(['last_login_at' => now()])->save();
        AuditLogger::log('login_succeeded', $user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request)
    {
        AuditLogger::log('logout', $request->user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function backoffSeconds(?User $user): int
    {
        $count = $user?->failed_login_count ?? 0;

        // Exponential backoff: 30s, 60s, 120s, 240s, capped at 30 min.
        return (int) min(30 * (2 ** $count), 1800);
    }

    private function applyBackoffLock(User $user): void
    {
        if ($user->failed_login_count >= 5) {
            $user->forceFill([
                'locked_until' => now()->addSeconds($this->backoffSeconds($user)),
            ])->save();
        }
    }

    private function maybeAlertSuperAdmin(User $user): void
    {
        if ($user->failed_login_count === 5 || $user->failed_login_count % 10 === 0) {
            $alertEmail = config('mail.security_alert_email');
            if ($alertEmail) {
                Notification::route('mail', $alertEmail)
                    ->notify(new RepeatedFailedLoginAlert($user));
            }
        }
    }
}
