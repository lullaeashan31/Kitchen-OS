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

        $requires2fa = $user->hasAnyRole(['super_admin', 'hr_manager', 'accounts']);

        if ($requires2fa && ! $user->twoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        if ($requires2fa && ! $request->session()->get('2fa_verified')) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
