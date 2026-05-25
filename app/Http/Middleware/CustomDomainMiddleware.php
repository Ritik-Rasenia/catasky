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
            // Find active custom domain mapping
            $customDomain = CustomDomain::where('domain', $host)
                ->where('status', 'active')
                ->first();

            if ($customDomain) {
                // Fetch the subscriber's active profile
                $subscriberProfile = SubscriberProfile::where('user_id', $customDomain->user_id)
                    ->where('status', 'active')
                    ->first();

                if ($subscriberProfile) {
                    // Inject domain routing tags into the request
                    $request->attributes->set('custom_domain_subscriber_id', $customDomain->user_id);
                    $request->attributes->set('custom_domain_slug', $subscriberProfile->company_slug);
                }
            }
        }

        return $next($request);
    }
}
