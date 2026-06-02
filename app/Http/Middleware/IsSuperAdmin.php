<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     * 
     * Super Admin & Admin → full access (bypass all permission checks).
     * Any other non-Subscriber role → enters admin panel, but is subject to
     * individual route permission checks (middleware('permission:...') on routes).
     * Subscriber role → blocked, must use Subscriber panel.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        return $next($request);
    }
}
