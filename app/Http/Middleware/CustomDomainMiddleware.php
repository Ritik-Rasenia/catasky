<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CustomDomain;
use App\Models\SubscriberProfile;

class CustomDomainMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $primaryHost = parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url');

        // Normalize hosts
        $host = strtolower(trim($host));
        $primaryHost = strtolower(trim($primaryHost));

        // Skip check if it is the primary app domain, localhost, or 127.0.0.1
        if ($host !== $primaryHost && $host !== '127.0.0.1' && $host !== 'localhost') {
            $cleanHost = preg_replace('/^www\./i', '', $host);

            // Fetch the custom domain matching the requested host
            $customDomain = CustomDomain::where(function($q) use ($host, $cleanHost) {
                $q->where('domain', $host)
                  ->orWhere('domain', $cleanHost)
                  ->orWhere('domain', 'www.' . $cleanHost);
            })->first();

            if ($customDomain) {
                // Reject traffic if domain is not fully verified, approved, and active
                if ($customDomain->status !== 'Active' || $customDomain->ssl_status !== 'SSL Active') {
                    abort(403, 'This custom domain routing is not active or verified yet.');
                }

                $user = $customDomain->user;
                if ($user) {
                    $sub = $user->activeSubscription();
                    $plan = $sub ? $sub->plan : null;
                    $isEnterprise = $plan && ($plan->slug === 'enterprise' || $plan->custom_branding);

                    // Check if subscription has not expired and user is on an Enterprise plan
                    if ($isEnterprise && $user->hasActiveSubscription()) {
                        $profile = $user->subscriberProfile;
                        if ($profile && $profile->isApproved() && $profile->store_status === 'live') {
                            // Inject domain routing tags into the request
                            $request->attributes->set('custom_domain_subscriber_id', $user->id);
                            $request->attributes->set('custom_domain_slug', $profile->company_slug);
                        } else {
                            abort(403, 'This storefront profile is pending approval or currently suspended.');
                        }
                    } else {
                        // The Enterprise Plan has expired or downgraded! Automatically suspend domain routing status
                        $customDomain->update([
                            'status' => 'Pending DNS Setup',
                            'admin_approved' => false
                        ]);

                        $profile = $user->subscriberProfile;
                        if ($profile) {
                            $profile->update([
                                'domain_verified' => false
                            ]);
                        }

                        // Render subscription expired page
                        return response()->view('errors.subscription-expired', [
                            'company_name' => $profile ? $profile->company_name : 'Subscriber Store',
                            'fallback_url' => $profile ? route('store.catalog', $profile->company_slug) : url('/')
                        ]);
                    }
                }
            }
        }

        return $next($request);
    }
}
