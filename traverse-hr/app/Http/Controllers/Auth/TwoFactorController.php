<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Self-hosted TOTP enrolment and challenge — mandatory for Super Admin, HR
 * and Accounts (§6). No SMS. Recovery codes are generated once at
 * enrolment and shown exactly once.
 */
class TwoFactorController extends Controller
{
    private function engine(): Google2FA
    {
        return new Google2FA;
    }

    public function showSetup(Request $request)
    {
        $user = $request->user();

        if ($user->twoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        $engine = $this->engine();
        $secret = $request->session()->get('pending_2fa_secret') ?? $engine->generateSecretKey();
        $request->session()->put('pending_2fa_secret', $secret);

        $qrUrl = $engine->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret,
        );

        return view('auth.two-factor-setup', [
            'secret' => $secret,
            'qrUrl' => $qrUrl,
        ]);
    }

    public function confirmSetup(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $secret = $request->session()->get('pending_2fa_secret');
        $user = $request->user();

        if (! $secret || ! $this->engine()->verifyKey($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'That code did not match. Try again.']);
        }

        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->values()
            ->all();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_enabled_at' => now(),
        ])->save();

        $request->session()->forget(['pending_2fa_secret', 'needs_2fa_enrollment']);
        $request->session()->put('2fa_verified', true);
        AuditLogger::log('2fa_enrolled', $user);

        return view('auth.two-factor-recovery-codes', ['codes' => $recoveryCodes]);
    }

    public function showChallenge(Request $request)
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $request->validate(['code' => ['required', 'string']]);
        $user = $request->user();
        $input = $request->input('code');

        $ok = $this->engine()->verifyKey($user->two_factor_secret, $input);

        if (! $ok) {
            $codes = $user->two_factor_recovery_codes ?? [];
            $normalized = Str::upper(trim($input));
            if (in_array($normalized, $codes, true)) {
                $ok = true;
                $user->forceFill([
                    'two_factor_recovery_codes' => array_values(array_diff($codes, [$normalized])),
                ])->save();
                AuditLogger::log('2fa_recovery_code_used', $user);
            }
        }

        if (! $ok) {
            AuditLogger::log('2fa_challenge_failed', $user);

            return back()->withErrors(['code' => 'Invalid code.']);
        }

        $request->session()->put('2fa_verified', true);
        $request->session()->forget('needs_2fa_challenge');
        $user->forceFill(['last_login_at' => now()])->save();
        AuditLogger::log('login_succeeded', $user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
}
