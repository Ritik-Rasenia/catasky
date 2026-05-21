<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     * Usage in routes/controllers: ->middleware('permission:users.edit')
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
