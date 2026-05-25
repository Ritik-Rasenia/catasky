<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriberProduct;
use App\Models\SubscriberShareLink;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SaaSAnalyticsController extends Controller
{
    /**
     * Display subscription analytics and metrics.
     */
    public function index()
    {
        $totalSubscribers = User::role('Subscriber')->count();
        
        $activeSubscriptions = Subscription::whereIn('status', ['active', 'trial'])
            ->where('ends_at', '>', Carbon::now())
            ->count();

        $totalRevenue = Payment::where('status', 'success')->sum('amount');
        
        // Plan Distribution
        $starterCount = Subscription::whereIn('status', ['active', 'trial'])
            ->whereHas('plan', function($q) {
                $q->where('slug', 'starter');
            })->count();

        $businessCount = Subscription::whereIn('status', ['active', 'trial'])
            ->whereHas('plan', function($q) {
                $q->where('slug', 'business');
            })->count();

        $enterpriseCount = Subscription::whereIn('status', ['active', 'trial'])
            ->whereHas('plan', function($q) {
                $q->where('slug', 'enterprise');
            })->count();

        // Revenue over the last 30 days
        $monthlyRevenue = Payment::where('status', 'success')
            ->where('paid_at', '>=', Carbon::now()->subDays(30))
            ->sum('amount');

        // Recent 5 payments
        $recentPayments = Payment::with(['user.subscriberProfile', 'plan'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.saas.analytics.index', compact(
            'totalSubscribers',
            'activeSubscriptions',
            'totalRevenue',
            'starterCount',
            'businessCount',
            'enterpriseCount',
            'monthlyRevenue',
            'recentPayments'
        ));
    }

    /**
     * Display a subscriber limits usage tracking grid.
     */
    public function usage(Request $request)
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

        $subscribers = $query->paginate(15);

        // Process usage metrics for each subscriber
        foreach ($subscribers as $sub) {
            $sub->products_count = SubscriberProduct::where('user_id', $sub->id)->count();
            $sub->shares_count = SubscriberShareLink::where('user_id', $sub->id)->count();
            
            $activeSub = $sub->activeSubscription();
            if ($activeSub && $activeSub->plan) {
                $sub->plan_name = $activeSub->plan->name;
                $sub->products_limit = $activeSub->plan->product_limit;
                $sub->attributes_limit = $activeSub->plan->attribute_limit;
            } else {
                $sub->plan_name = 'No Active Plan';
                $sub->products_limit = 0;
                $sub->attributes_limit = 0;
            }
        }

        return view('admin.saas.usage.index', compact('subscribers'));
    }
}
