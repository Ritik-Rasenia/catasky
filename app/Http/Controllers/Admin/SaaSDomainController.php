<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomDomain;
use Illuminate\Http\Request;

class SaaSDomainController extends Controller
{
    /**
     * Display all requested custom domains.
     */
    public function index(Request $request)
    {
        $query = CustomDomain::with('user.subscriberProfile');

        // Search By: Store Name, Subscriber Name, Domain Name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('domain', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('user.subscriberProfile', function($qp) use ($search) {
                      $qp->where('company_name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter By Status: Pending, Verified, Suspended
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'pending') {
                $query->whereIn('status', ['pending_dns', 'dns_verified', 'ssl_provisioning']);
            } elseif ($status === 'verified') {
                $query->where('status', 'active_routing');
            } elseif ($status === 'suspended') {
                $query->where('status', 'suspended');
            }
        }

        $domains = $query->latest()->paginate(15);

        return view('admin.saas.domains.index', compact('domains'));
    }

    /**
     * Display a subscriber's custom domain details.
     */
    public function show(CustomDomain $domain)
    {
        $domain->load(['user.subscriberProfile']);
        return view('admin.saas.domains.show', compact('domain'));
    }

    /**
     * Verify domain's simulated DNS status (runs complete automatic workflow).
     */
    public function verify(CustomDomain $domain)
    {
        $domain->verifyDns();

        // Automatically sync to subscriber profile upon DNS verification check
        $profile = $domain->user->subscriberProfile ?? null;
        if ($profile) {
            $profile->update([
                'custom_domain' => $domain->domain,
                'domain_verified' => true
            ]);
        }

        return back()->with('success', 'Domain DNS automated check completed! Verification, SSL provisioning, and routing are now fully Active.');
    }

    /**
     * Activate the custom domain.
     */
    public function approve(CustomDomain $domain)
    {
        $domain->update([
            'status' => 'active_routing',
            'ssl_status' => 'active',
            'dns_verified' => true
        ]);

        $profile = $domain->user->subscriberProfile ?? null;
        if ($profile) {
            $profile->update([
                'custom_domain' => $domain->domain,
                'domain_verified' => true
            ]);
        }

        return back()->with('success', 'Custom domain ' . $domain->domain . ' routing activated successfully!');
    }

    /**
     * Reject/Block the custom domain.
     */
    public function reject(CustomDomain $domain)
    {
        $domain->update([
            'status' => 'suspended'
        ]);

        $profile = $domain->user->subscriberProfile ?? null;
        if ($profile && $profile->custom_domain === $domain->domain) {
            $profile->update([
                'domain_verified' => false
            ]);
        }

        return back()->with('success', 'Custom domain ' . $domain->domain . ' has been suspended.');
    }

    /**
     * Suspend the custom domain.
     */
    public function suspend(CustomDomain $domain)
    {
        $domain->update([
            'status' => 'suspended'
        ]);

        $profile = $domain->user->subscriberProfile ?? null;
        if ($profile && $profile->custom_domain === $domain->domain) {
            $profile->update([
                'domain_verified' => false
            ]);
        }

        return back()->with('success', 'Custom domain ' . $domain->domain . ' has been suspended successfully.');
    }

    /**
     * Remove the custom domain.
     */
    public function destroy(CustomDomain $domain)
    {
        $domainName = $domain->domain;

        $profile = $domain->user->subscriberProfile ?? null;
        if ($profile && $profile->custom_domain === $domainName) {
            $profile->update([
                'custom_domain' => null,
                'domain_verified' => false
            ]);
        }

        $domain->delete();

        return back()->with('success', 'Custom domain ' . $domainName . ' removed successfully!');
    }
}
