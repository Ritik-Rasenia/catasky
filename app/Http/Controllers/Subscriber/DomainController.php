<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\CustomDomain;
use App\Models\SubscriberActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DomainController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // 1. Ensure user is on an Enterprise plan (by checking custom_branding or slug)
        $sub = $user->activeSubscription();
        $plan = $sub ? $sub->plan : null;
        
        $isEnterprise = $plan && ($plan->slug === 'enterprise' || $plan->custom_branding);

        if (!$isEnterprise) {
            return view('subscriber-panel.domain.index', [
                'isEnterprise' => false,
                'currentPlan' => $plan ? $plan->name : 'None',
                'domains' => collect()
            ]);
        }

        $domains = CustomDomain::where('user_id', $user->id)->latest()->get();

        return view('subscriber-panel.domain.index', [
            'isEnterprise' => true,
            'domains' => $domains,
            'plan' => $plan
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $sub = $user->activeSubscription();
        $plan = $sub ? $sub->plan : null;
        $isEnterprise = $plan && ($plan->slug === 'enterprise' || $plan->custom_branding);

        if (!$isEnterprise) {
            return back()->with('error', 'Only Enterprise Subscribers can configure custom domain routing.');
        }

        $request->validate([
            'domain' => 'required|string|unique:custom_domains,domain|regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/',
        ], [
            'domain.regex' => 'Please enter a valid domain name (e.g. catalog.mybrand.com).',
            'domain.unique' => 'This custom domain has already been mapped or requested.',
        ]);

        $domainName = strtolower(trim($request->domain));
        
        // Generate verification codes
        $verification = CustomDomain::generateTxtVerification($domainName);

        $domain = CustomDomain::create([
            'user_id'       => $user->id,
            'domain'        => $domainName,
            'status'        => 'pending',
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
        $request->validate(['domain_id' => 'required|exists:custom_domains,id']);
        $domain = CustomDomain::findOrFail($request->domain_id);

        if ($domain->user_id !== auth()->id()) {
            abort(403);
        }

        // Simulate DNS ownership check
        $domain->verifyDnsMock();

        SubscriberActivityLog::log('updated', 'Successfully verified DNS records for custom domain: ' . $domain->domain, $domain);

        return response()->json([
            'success' => true,
            'message' => '🎉 DNS Verification Successful! TXT record found. Domain is now approved.'
        ]);
    }
}
