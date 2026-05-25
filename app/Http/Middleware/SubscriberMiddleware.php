<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriberMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('subscriber.login')
                ->with('error', 'Please login to access the subscriber panel.');
        }

        if (!auth()->user()->hasRole('Subscriber')) {
            abort(403, 'Access denied. Subscriber account required.');
        }

        $user = auth()->user();
        $profile = $user->subscriberProfile;

        // Exclude only pending-approval and logout to strictly enforce approval gating
        $allowedRoutes = [
            'subscriber.logout',
            'subscriber.pending-approval',
        ];

        $currentRoute = $request->route() ? $request->route()->getName() : null;

        if (in_array($currentRoute, $allowedRoutes)) {
            return $next($request);
        }

        if ($profile && $profile->isSuspended()) {
            return redirect()->route('subscriber.pending-approval')
                ->with('error', 'Your subscriber account has been suspended. Reason: ' . ($profile->suspension_reason ?? 'Contact support.'));
        }

        if (!$profile || !$profile->isApproved()) {
            $billingRoutes = [
                'subscriber.subscription.plans',
                'subscriber.subscription.checkout',
                'subscriber.subscription.pay',
            ];

            if ($profile?->isPending() && !$user->hasActiveSubscription() && in_array($currentRoute, $billingRoutes, true)) {
                return $next($request);
            }

            return redirect()->route('subscriber.pending-approval');
        }

        if (!$user->hasActiveSubscription()) {
            return redirect()->route('subscriber.subscription.plans')
                ->with('warning', 'Please subscribe to an active plan to access the subscriber panel.');
        }

        return $next($request);
    }
}
