<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\SubscriberProduct;
use App\Models\SubscriberShareLink;
use App\Models\SubscriberActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $subscription = $user->activeSubscription();
        $profile = $user->subscriberProfile;

        // Render limited onboarding dashboard if store is not approved and live yet
        if (!$profile || $profile->store_status !== 'live') {
            return view('subscriber-panel.dashboard.limited', compact('user', 'subscription', 'profile'));
        }

        $stats = [
            'total_products'  => SubscriberProduct::where('user_id', $user->id)->count(),
            'active_products' => SubscriberProduct::where('user_id', $user->id)->where('status', 'active')->count(),
            'total_shares'    => SubscriberShareLink::where('user_id', $user->id)->count(),
            'total_views'     => SubscriberShareLink::where('user_id', $user->id)->sum('view_count'),
            'total_downloads' => SubscriberShareLink::where('user_id', $user->id)->sum('download_count'),
        ];

        $recentProducts = SubscriberProduct::where('user_id', $user->id)
            ->with('images')
            ->latest()
            ->take(6)
            ->get();

        $recentActivity = SubscriberActivityLog::where('user_id', $user->id)
            ->latest('created_at')
            ->take(10)
            ->get();

        $topShareLinks = SubscriberShareLink::where('user_id', $user->id)
            ->with('product')
            ->orderByDesc('view_count')
            ->take(5)
            ->get();

        return view('subscriber-panel.dashboard.index', compact(
            'user', 'subscription', 'profile', 'stats',
            'recentProducts', 'recentActivity', 'topShareLinks'
        ));
    }
}
