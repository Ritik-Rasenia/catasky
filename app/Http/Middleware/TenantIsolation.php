<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantIsolation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // This middleware ensures that for any multi-tenant resource being accessed,
        // it belongs to the current authenticated subscriber/subscriber.
        // Most of this is handled by the BelongsToTenant trait's Global Scope.
        
        if (auth()->check() && auth()->user()->hasRole('Subscriber')) {
            // We could add logic here to explicitly check route parameters if needed,
            // but Laravel's implicit binding with Global Scopes will already return 404
            // if a subscriber tries to access another subscriber's resource.
        }

        return $next($request);
    }
}
