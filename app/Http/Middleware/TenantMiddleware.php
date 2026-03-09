<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Kitchen;
use Illuminate\Support\Facades\URL;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('kitchen_slug');
        $kitchen = Kitchen::where('slug', $slug)->where('is_active', true)->first();

        if (!$kitchen) {
            abort(404, 'Kitchen not found or inactive.');
        }

        // Security Check: Non-SuperAdmins can only access their assigned kitchen
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            if ($user->kitchen_id !== $kitchen->id) {
                // Redirect to their own kitchen dashboard if they try to peek into another branch
                if ($user->kitchen) {
                    return redirect('/k/' . $user->kitchen->slug . '/dashboard')
                        ->with('error', 'You do not have access to that branch.');
                }
                abort(403, 'Unauthorized branch access.');
            }
        }

        // Globally share the kitchen object
        app()->instance('current_kitchen', $kitchen);

        // Set default route parameter for URL generation
        URL::defaults(['kitchen_slug' => $kitchen->slug]);

        return $next($request);
    }
}
