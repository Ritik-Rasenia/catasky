<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        // Convert string roles to array
        $rolesArray = is_array($roles[0]) ? $roles[0] : $roles;

        // Check if user has any of the required roles
        if (!auth()->user()->hasAnyRole($rolesArray)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
