<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        // Convert string permissions to array
        $perms = is_array($permissions[0]) ? $permissions[0] : $permissions;

        // Check if user has any of the required permissions
        if (!auth()->user()->hasAnyPermission($perms)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
