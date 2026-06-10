<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude public analytics endpoints from CSRF (sendBeacon cannot carry tokens)
        $middleware->validateCsrfTokens(except: [
            'api/track-event',
        ]);
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('admin*') || $request->is('dashboard/admin*')) {
                return route('login'); // secure-admin-login
            }
            return route('subscriber.login'); // subscriber/login
        });
        $middleware->append(\App\Http\Middleware\CustomDomainMiddleware::class);
        // Block pending/rejected/suspended subscribers globally (only allow pending-approval & logout)
        $middleware->append(\App\Http\Middleware\BlockPendingSubscriber::class);
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role'       => \App\Http\Middleware\CheckRole::class,
            'subscriber'     => \App\Http\Middleware\SubscriberMiddleware::class,
            'superadmin' => \App\Http\Middleware\IsSuperAdmin::class,
            'admin_panel' => \App\Http\Middleware\AdminPanelAccess::class,
            'is_subscriber' => \App\Http\Middleware\IsSubscriber::class,
            'tenant_isolation' => \App\Http\Middleware\TenantIsolation::class,
            'active_subscription' => \App\Http\Middleware\ActiveSubscription::class,
            'approved_subscriber' => \App\Http\Middleware\ApprovedSubscriber::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
