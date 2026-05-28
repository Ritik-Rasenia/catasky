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
            'pending_products' => SubscriberProduct::where('user_id', $user->id)->where('approval_status', 'pending')->count(),
            'categories_count' => SubscriberProduct::where('user_id', $user->id)->whereNotNull('category_id')->distinct('category_id')->count('category_id'),
            'total_shares'    => SubscriberShareLink::where('user_id', $user->id)->count(),
            'total_views'     => SubscriberShareLink::where('user_id', $user->id)->sum('view_count'),
            'total_downloads' => SubscriberShareLink::where('user_id', $user->id)->sum('download_count'),
            'unread_notifications_count' => $user->unreadNotifications()->count(),
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

        $recentNotifications = $user->notifications()->latest()->take(5)->get();

        $dashboardCharts = [
            'monthlyViews' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'data' => [124, 168, 152, 192, 240, 224, 268],
            ],
        ];

        return view('subscriber-panel.dashboard.index', compact(
            'user', 'subscription', 'profile', 'stats',
            'recentProducts', 'recentActivity', 'topShareLinks',
            'recentNotifications', 'dashboardCharts'
        ));
    }
}
