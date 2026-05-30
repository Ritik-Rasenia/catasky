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
            return redirect()->route('subscriber.pending-approval');
        }

        if (!$user->hasActiveSubscription()) {
            // Allow limited onboarding access to dashboard & profile setup before payment
            if ($profile && $profile->store_status !== 'live') {
                $allowedOnboarding = [
                    'dashboard',
                    'subscriber.profile.edit',
                    'subscriber.profile.update',
                    'subscriber.profile.password',
                    'subscriber.logout',
                ];
                if (in_array($currentRoute, $allowedOnboarding, true)) {
                    return $next($request);
                }
            }

            // Once store is live, force plan selection to unlock full access
            $billingRoutes = [
                'subscriber.subscription.plans',
                'subscriber.subscription.checkout',
                'subscriber.subscription.pay',
                'subscriber.subscription.razorpay.order',
                'subscriber.subscription.razorpay.verify',
            ];
            if (in_array($currentRoute, $billingRoutes, true)) {
                return $next($request);
            }

            return redirect()->route('subscriber.subscription.plans')
                ->with('warning', 'Please subscribe to an active plan to access the subscriber panel.');
        }

        return $next($request);
    }
}
