<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockPendingSubscriber
{
    /**
     * Block subscribers who are not yet approved (or suspended/rejected) from accessing the app
     * except for the pending approval page and logout.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if (! $user->hasRole('Subscriber')) {
            return $next($request);
        }

        $profile = $user->subscriberProfile;
        $status = $profile?->status;

        // Approved subscribers may continue into normal route-level middleware.
        if ($profile?->isApproved()) {
            return $next($request);
        }

        $routeName = $request->route() ? $request->route()->getName() : null;
        $allowed = [
            'subscriber.pending-approval',
            'subscriber.logout',
        ];

        // Before first successful payment, a pending subscriber may complete billing.
        if ($status === 'pending' && ! $user->hasActiveSubscription()) {
            $allowed = array_merge($allowed, [
                'subscriber.subscription.plans',
                'subscriber.subscription.checkout',
                'subscriber.subscription.pay',
            ]);
        }

        if (in_array($routeName, $allowed, true)) {
            return $next($request);
        }

        if ($user->hasActiveSubscription() || in_array($status, ['pending', 'rejected', 'suspended'], true) || !$status) {
            return redirect()->route('subscriber.pending-approval');
        }

        return $next($request);
    }
}
