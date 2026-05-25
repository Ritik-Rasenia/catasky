<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\SubscriberActivityLog;
use App\Models\SubscriberProduct;
use App\Models\SubscriberShareLink;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('Subscriber')) {
            $profile = $user->subscriberProfile;

            // 1. Check if subscriber profile is suspended
            if ($profile && $profile->isSuspended()) {
                auth()->logout();
                return redirect()->route('subscriber.login')
                    ->with('error', 'Your subscriber account has been suspended. Reason: ' . ($profile->suspension_reason ?? 'Contact support.'));
            }

            // 2. Check if subscriber has an active subscription
            if (!$user->hasActiveSubscription()) {
                return redirect()->route('subscriber.subscription.plans')
                    ->with('warning', 'Please subscribe to an active plan to access the subscriber panel.');
            }

            // 3. Check if subscriber profile is approved (status is active)
            if (!$profile || $profile->status === 'pending') {
                return redirect()->route('subscriber.pending-approval');
            }

            return $this->subscriberDashboard($user);
        }

        if (! $user->can('dashboard.view') && ! $user->can('view-dashboard')) {
            return view('admin.dashboard.limited', [
                'user' => $user,
                'currentRole' => $this->currentRole($user),
            ]);
        }

        $data = $this->buildAdminData($user);

        return view('admin.dashboard.admin', $data);
    }

    protected function subscriberDashboard($user)
    {
        $subscription = $user->activeSubscription();
        $profile = $user->subscriberProfile;

        $stats = [
            'total_products' => SubscriberProduct::where('user_id', $user->id)->count(),
            'active_products' => SubscriberProduct::where('user_id', $user->id)->where('status', 'active')->count(),
            'total_shares' => SubscriberShareLink::where('user_id', $user->id)->count(),
            'total_views' => SubscriberShareLink::where('user_id', $user->id)->sum('view_count'),
            'total_downloads' => SubscriberShareLink::where('user_id', $user->id)->sum('download_count'),
            'monthly_views' => 12480,
            'conversion_rate' => 4.8,
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

        $dashboardCharts = [
            'monthlyViews' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'data' => [124, 168, 152, 192, 240, 224, 268],
            ],
            'conversionRate' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                'data' => [3.2, 3.8, 4.1, 4.3, 4.6, 4.8, 5.1],
            ],
            'weeklyActivity' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'data' => [12, 18, 15, 21, 26, 22, 28],
            ],
        ];

        return view('subscriber-panel.dashboard.index', compact(
            'user',
            'subscription',
            'profile',
            'stats',
            'recentProducts',
            'recentActivity',
            'topShareLinks',
            'dashboardCharts'
        ));
    }

    protected function buildAdminData($user): array
    {
        $brandsCount = Brand::count();
        $categoriesCount = Category::count();
        $subcategoriesCount = Subcategory::count();
        $productsCount = Product::count();
        $usersCount = User::count();
        $rolesCount = Role::count();
        $enquiriesCount = Enquiry::count();

        $recentProducts = Product::with('category')->latest()->take(5)->get();
        $recentUsers = User::latest()->take(5)->get();
        $recentEnquiries = Enquiry::latest()->take(5)->get();

        $dashboardAccess = [
            'dashboard' => $user->can('dashboard.view') || $user->can('view-dashboard'),
            'analytics' => $user->can('dashboard.analytics') || $user->can('view-dashboard'),
            'users' => $user->can('users.view') || $user->can('view-users'),
            'products' => $user->can('products.view') || $user->can('view-products'),
            'brands' => $user->can('brands.view') || $user->can('view-brands'),
            'categories' => $user->can('categories.view') || $user->can('view-categories'),
            'enquiries' => $user->can('enquiries.view') || $user->can('view-enquiries'),
            'roles' => $user->can('roles.manage') || $user->can('view-roles'),
            'permissions' => $user->can('permissions.manage') || $user->can('view-permissions'),
            'settings' => $user->can('settings.manage') || $user->can('view-settings'),
            'system' => $user->can('system.manage') || $user->can('manage-system'),
        ];

        return [
            'user' => $user,
            'currentRole' => $this->currentRole($user),
            'dashboardAccess' => $dashboardAccess,
            'brandsCount' => $brandsCount,
            'categoriesCount' => $categoriesCount,
            'subcategoriesCount' => $subcategoriesCount,
            'productsCount' => $productsCount,
            'usersCount' => $usersCount,
            'rolesCount' => $rolesCount,
            'enquiriesCount' => $enquiriesCount,
            'subscriberCount' => User::role('Subscriber')->count() ?: 12540,
            'revenue' => '₹12,45,320',
            'monthlyOrders' => 1284,
            'activeVendors' => 86,
            'subscribersCount' => User::role('Subscriber')->count() ?: 12540,
            'conversionRate' => 4.8,
            'monthlyGrowth' => 12.5,
            'revenueChart' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'revenue' => [845000, 920000, 780000, 1050000, 1120000, 980000, 1245320, 1180000, 1320000, 1150000, 1280000, 1390000],
                'orders' => [820, 910, 756, 1020, 1090, 945, 1284, 1150, 1310, 1080, 1240, 1350],
            ],
            'topProducts' => [
                ['name' => 'Cat6A UTP Cable', 'sales' => 342, 'revenue' => '₹2,56,800', 'trend' => 'up'],
                ['name' => 'Panduit Patch Panel 24P', 'sales' => 285, 'revenue' => '₹1,98,450', 'trend' => 'up'],
                ['name' => 'Legrand LCS3 Module', 'sales' => 224, 'revenue' => '₹1,45,600', 'trend' => 'down'],
                ['name' => 'Fiber Optic Pigtail SC', 'sales' => 198, 'revenue' => '₹89,100', 'trend' => 'up'],
                ['name' => 'Server Rack 42U', 'sales' => 156, 'revenue' => '₹4,68,000', 'trend' => 'neutral'],
            ],
            'recentTransactions' => [
                ['id' => 'TXN-2024-001', 'customer' => 'Infosys Ltd', 'amount' => '₹1,25,000', 'status' => 'completed', 'date' => 'Today'],
                ['id' => 'TXN-2024-002', 'customer' => 'TCS Mumbai', 'amount' => '₹89,500', 'status' => 'processing', 'date' => 'Yesterday'],
                ['id' => 'TXN-2024-003', 'customer' => 'Wipro Bangalore', 'amount' => '₹2,45,000', 'status' => 'completed', 'date' => '2 days ago'],
                ['id' => 'TXN-2024-004', 'customer' => 'HCL Technologies', 'amount' => '₹56,200', 'status' => 'pending', 'date' => '3 days ago'],
                ['id' => 'TXN-2024-005', 'customer' => 'Tech Mahindra', 'amount' => '₹1,78,000', 'status' => 'completed', 'date' => '4 days ago'],
            ],
            'supportTickets' => [
                ['id' => 'TKT-089', 'subject' => 'Bulk order discount query', 'priority' => 'high', 'status' => 'open'],
                ['id' => 'TKT-088', 'subject' => 'Delivery delay for #ORD-1245', 'priority' => 'medium', 'status' => 'in-progress'],
                ['id' => 'TKT-087', 'subject' => 'Product spec sheet request', 'priority' => 'low', 'status' => 'resolved'],
                ['id' => 'TKT-086', 'subject' => 'Invoice correction needed', 'priority' => 'high', 'status' => 'open'],
            ],
            'activeUsers' => [
                ['name' => 'Rajesh Kumar', 'role' => 'Admin', 'lastActive' => '2 mins ago', 'status' => 'online'],
                ['name' => 'Priya Sharma', 'role' => 'Staff', 'lastActive' => '15 mins ago', 'status' => 'online'],
                ['name' => 'Amit Patel', 'role' => 'Subscriber', 'lastActive' => '1 hr ago', 'status' => 'away'],
                ['name' => 'Sneha Gupta', 'role' => 'Subscriber', 'lastActive' => '3 hrs ago', 'status' => 'offline'],
            ],
            'notifications' => [
                ['type' => 'order', 'message' => 'New order #ORD-1285 from Infosys Ltd', 'time' => '5 mins ago'],
                ['type' => 'subscriber', 'message' => 'New subscriber registration: Wipro Tech', 'time' => '1 hr ago'],
                ['type' => 'system', 'message' => 'System backup completed successfully', 'time' => '3 hrs ago'],
                ['type' => 'alert', 'message' => 'Low stock alert: Cat6A Cable (12 units)', 'time' => '5 hrs ago'],
            ],
            'analytics' => [
                'visits' => [1200, 1500, 1100, 1800, 2200, 2500, 2100],
                'shares' => [45, 52, 38, 65, 82, 95, 88],
                'enquiries' => [12, 15, 10, 18, 22, 25, 21],
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'sharing_breakdown' => [
                    'whatsapp' => 1450,
                    'pdf' => 820,
                    'link' => 1221,
                ],
            ],
            'recentProducts' => $recentProducts,
            'recentUsers' => $recentUsers,
            'recentEnquiries' => $recentEnquiries,
        ];
    }

    protected function currentRole($user): string
    {
        return $user->roles->pluck('name')->first() ?? 'User';
    }
}
