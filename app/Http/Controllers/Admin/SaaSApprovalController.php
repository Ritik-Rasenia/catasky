<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SubscriberProfile;
use App\Models\SubscriberProduct;
use App\Models\SubscriberShareLink;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SaaSApprovalController extends Controller
{
    /**
     * List all subscribers.
     */
    public function subscribers(Request $request)
    {
        $query = User::role('Subscriber')->with(['subscriberProfile', 'subscription.plan']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereHas('subscriberProfile', function($qp) use ($search) {
                      $qp->where('company_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $subscribers = $query->latest()->paginate(15);

        return view('admin.saas.subscribers.index', compact('subscribers'));
    }

    /**
     * Suspend subscriber account.
     */
    public function suspendSubscriber(Request $request, User $user)
    {
        $profile = $user->subscriberProfile;
        if ($profile) {
            $profile->update([
                'status' => 'suspended',
                'suspended_at' => Carbon::now(),
                'suspension_reason' => $request->input('reason', 'Violation of terms of service.')
            ]);
        }

        return back()->with('success', 'Subscriber account suspended successfully.');
    }

    /**
     * Unsuspend subscriber account.
     */
    public function unsuspendSubscriber(User $user)
    {
        $profile = $user->subscriberProfile;
        if ($profile) {
            $profile->update([
                'status' => 'approved',
                'suspended_at' => null,
                'suspension_reason' => null
            ]);
        }

        return back()->with('success', 'Subscriber account unsuspended successfully.');
    }

    /**
     * Display Approvals dashboard for Stores, Products, and Share Links.
     */
    public function approvals(Request $request)
    {
        // 1. Pending Stores Queue
        $pendingStores = SubscriberProfile::where('status', 'pending')
            ->with('user.subscription.plan')
            ->latest()
            ->get();

        // 2. Pending Products Queue
        $pendingProducts = SubscriberProduct::where('approval_status', 'pending')
            ->with(['user.subscriberProfile', 'category'])
            ->latest()
            ->get();

        // 3. Pending Share Links Queue
        $pendingShares = SubscriberShareLink::where('approval_status', 'pending')
            ->with('user.subscriberProfile')
            ->latest()
            ->get();

        return view('admin.saas.approvals.index', compact('pendingStores', 'pendingProducts', 'pendingShares'));
    }

    /**
     * Approve store profile.
     */
    public function approveStore(SubscriberProfile $profile)
    {
        $profile->update([
            'status' => 'approved',
            'is_verified' => true,
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        return back()->with('success', 'Store ' . $profile->company_name . ' approved successfully!');
    }

    /**
     * Reject/suspend store profile.
     */
    public function rejectStore(SubscriberProfile $profile)
    {
        $profile->update([
            'status' => 'rejected',
            'suspended_at' => Carbon::now(),
            'suspension_reason' => 'Store registration rejected by administrator.'
        ]);

        return back()->with('success', 'Store ' . $profile->company_name . ' has been rejected/suspended.');
    }

    /**
     * Approve product.
     */
    public function approveProduct(SubscriberProduct $product)
    {
        $product->update([
            'approval_status' => 'approved'
        ]);

        return back()->with('success', 'Product ' . $product->name . ' approved for public sharing!');
    }

    /**
     * Reject product.
     */
    public function rejectProduct(SubscriberProduct $product)
    {
        $product->update([
            'approval_status' => 'rejected'
        ]);

        return back()->with('success', 'Product ' . $product->name . ' has been rejected.');
    }

    /**
     * Approve Share link.
     */
    public function approveShare(SubscriberShareLink $shareLink)
    {
        $shareLink->update([
            'approval_status' => 'approved'
        ]);

        return back()->with('success', 'Share link ' . $shareLink->title . ' approved for public sharing!');
    }

    /**
     * Reject Share link.
     */
    public function rejectShare(SubscriberShareLink $shareLink)
    {
        $shareLink->update([
            'approval_status' => 'rejected'
        ]);

        return back()->with('success', 'Share link ' . $shareLink->title . ' has been rejected.');
    }
}
