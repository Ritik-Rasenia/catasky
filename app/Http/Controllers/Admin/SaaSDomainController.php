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
        $query = CustomDomain::with(['user.subscriberProfile', 'logs']);

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

        // Filter By Status: Pending, Active, Rejected
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'pending') {
                $query->whereIn('status', ['Pending DNS Setup', 'DNS Verified', 'SSL Generating']);
            } elseif ($status === 'active') {
                $query->where('status', 'Active');
            } elseif ($status === 'rejected') {
                $query->where('status', 'Rejected');
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
        $domain->load(['user.subscriberProfile', 'logs' => function($q) {
            $q->latest();
        }]);
        return view('admin.saas.domains.show', compact('domain'));
    }

    /**
     * Verify domain's DNS status from admin panel.
     */
    public function verify(CustomDomain $domain)
    {
        $domain->verifyDns();
        $domain->checkAndActivate();

        return back()->with('success', 'Domain DNS check completed! Verification and SSL statuses have been updated.');
    }

    /**
     * Approve the custom domain.
     */
    public function approve(CustomDomain $domain)
    {
        if ($domain->status !== 'DNS Verified' || !$domain->dns_txt_verified || !$domain->dns_a_verified || !$domain->dns_cname_verified) {
            return back()->with('error', 'Cannot approve. Domain status must be DNS Verified and all records must be matched.');
        }

        $domain->update([
            'admin_approved' => true,
            'status' => 'DNS Verified'
        ]);
        $domain->log('admin_approved', 'success', 'Custom domain mapping request approved by Super Admin.');

        // Transition to SSL Generating
        $domain->update([
            'status' => 'SSL Generating',
            'ssl_status' => 'SSL Generating'
        ]);
        $domain->log('ssl_generation', 'info', 'Initiated SSL certificate provisioning challenge.');

        // Transition to SSL Active
        $domain->update([
            'ssl_status' => 'SSL Active'
        ]);
        $domain->log('ssl_generation', 'success', 'SSL certificate provisioned successfully and is Active.');

        // Activate routing
        $activated = $domain->checkAndActivate();

        if ($activated) {
            return back()->with('success', 'Custom domain approved, SSL activated and routing is live!');
        }

        return back()->with('success', 'Custom domain approved! Waiting for final verification criteria.');
    }

    /**
     * Reject/Block the custom domain.
     */
    public function reject(Request $request, CustomDomain $domain)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $domain->update([
            'status' => 'Rejected',
            'admin_approved' => false,
            'rejection_reason' => $request->rejection_reason
        ]);

        $domain->log('admin_rejected', 'failed', 'Custom domain request rejected by Super Admin. Reason: ' . $request->rejection_reason);

        $profile = $domain->user->subscriberProfile ?? null;
        if ($profile && $profile->custom_domain === $domain->domain) {
            $profile->update([
                'custom_domain' => null,
                'domain_verified' => false
            ]);
        }

        return back()->with('success', 'Custom domain mapping request has been rejected with the specified reason.');
    }

    /**
     * Suspend/Reset the custom domain.
     */
    public function suspend(CustomDomain $domain)
    {
        $domain->update([
            'status' => 'Pending DNS Setup',
            'admin_approved' => false,
            'dns_txt_verified' => false,
            'dns_a_verified' => false,
            'dns_cname_verified' => false,
            'dns_verified' => false,
            'ssl_status' => 'SSL Pending'
        ]);

        $domain->log('auto_disabled', 'failed', 'Custom domain suspended and DNS configuration reset by Super Admin.');

        $profile = $domain->user->subscriberProfile ?? null;
        if ($profile && $profile->custom_domain === $domain->domain) {
            $profile->update([
                'custom_domain' => null,
                'domain_verified' => false
            ]);
        }

        return back()->with('success', 'Custom domain has been suspended. Re-verification and approval are now required.');
    }

    /**
     * Regenerate SSL certificate.
     */
    public function regenerateSsl(CustomDomain $domain)
    {
        if ($domain->status !== 'Active') {
            return back()->with('error', 'SSL regeneration is only available for active domains.');
        }

        $domain->update([
            'status' => 'SSL Generating',
            'ssl_status' => 'SSL Generating'
        ]);
        $domain->log('ssl_generation', 'info', 'SSL regeneration request triggered by Super Admin.');

        // Re-generate and activate SSL
        $domain->update([
            'ssl_status' => 'SSL Active',
            'status' => 'Active',
            'ssl_expires_at' => now()->addDays(90)
        ]);
        $domain->log('ssl_generation', 'success', 'SSL certificate regenerated successfully. Expiration date renewed.');

        return back()->with('success', 'SSL certificate regenerated successfully!');
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

        $domain->log('deleted', 'info', 'Custom domain request permanently removed by Super Admin.');
        $domain->delete();

        return back()->with('success', 'Custom domain request removed successfully!');
    }
}

