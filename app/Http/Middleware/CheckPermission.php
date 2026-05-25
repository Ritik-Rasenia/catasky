<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        // Implicitly allow Super Admin all permissions
        if (auth()->user()->hasRole('Super Admin')) {
            return $next($request);
        }

        // Convert string permissions to array
        $perms = is_array($permissions[0]) ? $permissions[0] : $permissions;

        // Check if user has any of the required permissions
        if (!auth()->user()->hasAnyPermission($perms)) {
            $role = auth()->user()->roles->pluck('name')->first() ?? 'User';
            Log::warning('Unauthorized access attempt', [
                'user_id' => auth()->id(),
                'route' => $request->path(),
                'required_permissions' => $perms,
                'role' => $role,
            ]);

            return response()->view('errors.403', ['role' => $role], 403);
        }

        return $next($request);
    }
}
