<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApprovedSubscriber
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $profile = $user ? $user->subscriberProfile : null;

        if (!$profile || !$profile->isApproved()) {
            return redirect()->route('subscriber.pending-approval');
        }

        return $next($request);
    }
}
