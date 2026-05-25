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

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('domain', 'like', '%' . $search . '%')
                  ->orWhereHas('user.subscriberProfile', function($qp) use ($search) {
                      $qp->where('company_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $domains = $query->latest()->paginate(15);

        return view('admin.saas.domains.index', compact('domains'));
    }

    /**
     * Verify domain's simulated DNS status.
     */
    public function verify(CustomDomain $domain)
    {
        $domain->verifyDnsMock();

        return back()->with('success', 'Domain DNS check completed. Simulated DNS records validated successfully!');
    }

    /**
     * Activate the custom domain.
     */
    public function approve(CustomDomain $domain)
    {
        $domain->update([
            'status' => 'active',
            'ssl_status' => 'active',
            'dns_verified' => true
        ]);

        return back()->with('success', 'Custom domain ' . $domain->domain . ' activated successfully!');
    }

    /**
     * Reject/Block the custom domain.
     */
    public function reject(CustomDomain $domain)
    {
        $domain->update([
            'status' => 'rejected'
        ]);

        return back()->with('success', 'Custom domain ' . $domain->domain . ' has been rejected.');
    }
}
