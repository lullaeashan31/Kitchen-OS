<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Custom redirect for authenticated users hitting guest routes (e.g. /login)
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            $user = $request->user();

            // If we already know a kitchen slug (from route or session), send to tenant dashboard
            $slug = $request->route('kitchen_slug') ?? session('kitchen_slug');
            if ($slug) {
                return route('dashboard', ['kitchen_slug' => $slug]);
            }

            // Super admin: go to superadmin dashboard
            if ($user && method_exists($user, 'isAdmin') && $user->isAdmin() && method_exists($user, 'role') && $user->role?->value === 'admin') {
                return route('superadmin.dashboard');
            }

            // Fallback: send to home
            return '/';
        });

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'super_admin' => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
            'tenant' => \App\Http\Middleware\TenantMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, \Illuminate\Http\Request $request) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        });
    })->create();
