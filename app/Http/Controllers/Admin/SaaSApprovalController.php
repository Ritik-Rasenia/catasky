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
        // 1. Pending Accounts Queue (Stage 1 Compliance)
        $pendingAccounts = SubscriberProfile::where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        // 2. Pending Stores Queue (Stage 2 Configuration)
        $pendingStores = SubscriberProfile::where('status', 'approved')
            ->where('store_status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        // 3. Pending Custom Attributes Queue (Subscriber Custom Fields)
        $pendingAttributes = \App\Models\Attribute::where('is_global', false)
            ->where('approval_status', 'pending')
            ->with(['subscriber', 'group'])
            ->latest()
            ->get();

        return view('admin.saas.approvals.index', compact('pendingAccounts', 'pendingStores', 'pendingAttributes'));
    }

    /**
     * Approve B2B Compliance Account (Stage 1).
     */
    public function approveAccount(SubscriberProfile $profile)
    {
        $profile->update([
            'status' => 'approved',
            'is_verified' => true,
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        // Send approval notification to subscriber
        try {
            $user = $profile->user;
            if ($user) {
                $user->notify(new \App\Notifications\AccountApprovedNotification([
                    'title' => 'B2B Account Approved',
                    'message' => 'Your B2B Compliance Account has been approved by the administration.',
                ]));
            }
        } catch (\Exception $e) {}

        return back()->with('success', 'B2B Compliance Account for ' . $profile->company_name . ' approved successfully!');
    }

    /**
     * Reject B2B Compliance Account (Stage 1).
     */
    public function rejectAccount(SubscriberProfile $profile)
    {
        $profile->update([
            'status' => 'rejected',
            'suspended_at' => Carbon::now(),
            'suspension_reason' => 'B2B registration compliance check failed.'
        ]);

        return back()->with('success', 'B2B Compliance Account for ' . $profile->company_name . ' has been rejected.');
    }

    /**
     * Approve store profile configuration (Stage 2).
     */
    public function approveStore(SubscriberProfile $profile)
    {
        $profile->update([
            'store_status' => 'live',
        ]);

        // Send store approved notification
        try {
            $user = $profile->user;
            if ($user) {
                $user->notify(new \App\Notifications\StoreApprovedNotification([
                    'title' => 'Store Configuration Approved',
                    'message' => 'Good news! Your B2B store configuration and branding have been approved.',
                ]));
            }
        } catch (\Exception $e) {}

        return back()->with('success', 'Store branding for ' . $profile->company_name . ' approved successfully! Store status is now Live.');
    }

    /**
     * Reject store profile configuration (Stage 2).
     */
    public function rejectStore(SubscriberProfile $profile)
    {
        $profile->update([
            'store_status' => 'rejected',
            'suspension_reason' => 'Store branding or GST proof verification rejected.'
        ]);

        return back()->with('success', 'Store branding for ' . $profile->company_name . ' has been rejected.');
    }

    /**
     * Approve product.
     */
    public function approveProduct(SubscriberProduct $product)
    {
        $product->update([
            'approval_status' => 'approved'
        ]);

        // Send product approved notification
        try {
            $user = $product->user;
            if ($user) {
                $user->notify(new \App\Notifications\ProductApprovedNotification([
                    'status' => 'approved',
                    'product_name' => $product->name,
                    'title' => 'Product Approved',
                    'message' => 'Your product "' . $product->name . '" has been approved for public sharing.',
                ]));
            }
        } catch (\Exception $e) {}

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

        // Send product rejected notification
        try {
            $user = $product->user;
            if ($user) {
                $user->notify(new \App\Notifications\ProductApprovedNotification([
                    'status' => 'rejected',
                    'product_name' => $product->name,
                    'title' => 'Product Rejected',
                    'message' => 'Your product "' . $product->name . '" has been rejected by compliance.',
                ]));
            }
        } catch (\Exception $e) {}

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
