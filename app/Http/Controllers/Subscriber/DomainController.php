<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\CustomDomain;
use App\Models\SubscriberActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DomainController extends Controller
{
    /**
     * Enforce strict enterprise subscriber access.
     */
    protected function ensureEnterprise()
    {
        $user = auth()->user();
        $sub = $user ? $user->activeSubscription() : null;
        $plan = $sub ? $sub->plan : null;
        $isEnterprise = $plan && ($plan->slug === 'enterprise' || $plan->custom_branding);

        if (!$isEnterprise || !$user->hasActiveSubscription()) {
            abort(403, 'Unauthorized. Only Enterprise subscribers can access the custom domain management page.');
        }

        return [$user, $plan];
    }

    public function index()
    {
        list($user, $plan) = $this->ensureEnterprise();

        $domains = CustomDomain::where('user_id', $user->id)->latest()->get();

        return view('subscriber-panel.domain.index', [
            'isEnterprise' => true,
            'domains' => $domains,
            'plan' => $plan,
            'user' => $user
        ]);
    }

    public function store(Request $request)
    {
        list($user, $plan) = $this->ensureEnterprise();

        // One domain per Enterprise store
        $existingCount = CustomDomain::where('user_id', $user->id)->count();
        if ($existingCount >= 1) {
            return back()->with('error', 'Maximum limit reached. Your Enterprise plan supports 1 active custom domain.');
        }

        // Validate domain uniqueness against BOTH custom_domains AND subscriber_profiles tables
        $request->validate([
            'domain' => [
                'required',
                'string',
                'unique:custom_domains,domain',
                'unique:subscriber_profiles,custom_domain',
                'regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/'
            ],
        ], [
            'domain.regex' => 'Please enter a valid domain name (e.g. www.mycompany.com).',
            'domain.unique' => 'This custom domain has already been mapped or requested.',
        ]);

        $domainName = strtolower(trim($request->domain));
        
        // Generate verification codes
        $verification = CustomDomain::generateTxtVerification($domainName);

        $domain = CustomDomain::create([
            'user_id'       => $user->id,
            'domain'        => $domainName,
            'status'        => 'pending_dns',
            'ssl_status'    => 'pending',
            'dns_txt_key'   => $verification['key'],
            'dns_txt_value' => $verification['value'],
            'dns_verified'  => false,
        ]);

        SubscriberActivityLog::log('created', 'Requested custom domain mapping: ' . $domainName, $domain);

        return redirect()->route('subscriber.domain.index')
            ->with('success', 'Custom domain mapping request submitted successfully! Please configure DNS settings.');
    }

    public function verify(Request $request)
    {
        $this->ensureEnterprise();

        $request->validate(['domain_id' => 'required|exists:custom_domains,id']);
        $domain = CustomDomain::findOrFail($request->domain_id);

        if ($domain->user_id !== auth()->id()) {
            abort(403);
        }

        // Trigger automatic DNS verification flow (transitions status to active_routing & ssl_status to active)
        $domain->verifyDns();

        // Update subscriber profile to sync verified custom domain
        $profile = auth()->user()->subscriberProfile;
        if ($profile) {
            $profile->update([
                'custom_domain' => $domain->domain,
                'domain_verified' => true
            ]);
        }

        SubscriberActivityLog::log('updated', 'Successfully automated DNS verification and active routing for custom domain: ' . $domain->domain, $domain);

        return response()->json([
            'success' => true,
            'message' => '🎉 DNS Verification Successful! Domain is now active and routing traffic.'
        ]);
    }

    public function destroy($id)
    {
        $this->ensureEnterprise();

        $domain = CustomDomain::findOrFail($id);

        if ($domain->user_id !== auth()->id()) {
            abort(403);
        }

        $domainName = $domain->domain;

        // Clear custom domain on subscriber profile if this is the active domain
        $profile = auth()->user()->subscriberProfile;
        if ($profile && $profile->custom_domain === $domainName) {
            $profile->update([
                'custom_domain' => null,
                'domain_verified' => false
            ]);
        }

        $domain->delete();

        SubscriberActivityLog::log('deleted', 'Removed custom domain mapping: ' . $domainName, $domain);

        return redirect()->route('subscriber.domain.index')
            ->with('success', 'Custom domain mapping removed successfully.');
    }
}
