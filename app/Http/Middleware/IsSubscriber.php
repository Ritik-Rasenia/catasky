<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSubscriber
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('subscriber.login');
        }

        if (!auth()->user()->hasRole('Subscriber')) {
            abort(403, 'Unauthorized. Subscriber/Subscriber access required.');
        }

        return $next($request);
    }
}
