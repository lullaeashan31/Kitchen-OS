<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to the app past login until a user whose role requires 2FA
 * has completed the TOTP challenge for this session. Users without a 2FA
 * requirement (none in phase 1, since Employee has no login) pass through.
 */
class RequireTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Enrolment is only forced when mandatory 2FA is switched on.
        if ($user->requiresTwoFactor() && ! $user->twoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        // Anyone who HAS enrolled must still pass the challenge, whether or
        // not it is mandatory — opting in must not be weaker than opting out.
        if ($user->twoFactorEnabled() && ! $request->session()->get('2fa_verified')) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
