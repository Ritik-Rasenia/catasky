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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $filter = $request->query('filter', 'all_time');

        if ($user->hasRole('Subscriber')) {
            $profile = $user->subscriberProfile;

            if ($profile && $profile->isSuspended()) {
                auth()->logout();
                return redirect()->route('subscriber.login')
                    ->with('error', 'Your subscriber account has been suspended. Reason: ' . ($profile->suspension_reason ?? 'Contact support.'));
            }

            if (!$user->hasActiveSubscription()) {
                return redirect()->route('subscriber.subscription.plans')
                    ->with('warning', 'Please subscribe to an active plan to access the subscriber panel.');
            }

            if (!$profile || $profile->status === 'pending') {
                return redirect()->route('subscriber.pending-approval');
            }

            return $this->subscriberDashboard($user, $filter);
        }

        if (! $user->can('dashboard.view') && ! $user->can('view-dashboard')) {
            return view('admin.dashboard.limited', [
                'user'        => $user,
                'currentRole' => $this->currentRole($user),
            ]);
        }

        $data = $this->buildAdminData($user, $filter);
        $data['currentFilter'] = $filter;

        return view('admin.dashboard.admin', $data);
    }

    /* ─────────────────────────────────────────────────────────────
     |  SUBSCRIBER DASHBOARD  — 100 % real data
     * ──────────────────────────────────────────────────────────── */
    protected function subscriberDashboard($user, $filter)
    {
        $subscription = $user->activeSubscription();
        $profile      = $user->subscriberProfile;

        // ── Core stats with filter ─────────────────────────
        $totalViewsQuery = SubscriberShareLink::where('user_id', $user->id);
        $totalViews = $this->applyDateFilter($totalViewsQuery, $filter)->sum('view_count') ?: 0;

        $totalDownloadsQuery = SubscriberShareLink::where('user_id', $user->id);
        $totalDownloads = $this->applyDateFilter($totalDownloadsQuery, $filter)->sum('download_count') ?: 0;

        $conversionRate = $totalViews > 0 ? round(($totalDownloads / $totalViews) * 100, 1) : 0;

        $monthlyViewsQuery = SubscriberShareLink::where('user_id', $user->id);
        $monthlyViews = $this->applyDateFilter($monthlyViewsQuery, $filter)->sum('view_count') ?: 0;

        $stats = [
            'total_products'           => $this->applyDateFilter(SubscriberProduct::where('user_id', $user->id), $filter)->count(),
            'active_products'          => $this->applyDateFilter(SubscriberProduct::where('user_id', $user->id)->where('status', 'active'), $filter)->count(),
            'pending_products'         => $this->applyDateFilter(SubscriberProduct::where('user_id', $user->id)->where('approval_status', 'pending'), $filter)->count(),
            'categories_count'         => $this->applyDateFilter(SubscriberProduct::where('user_id', $user->id)->whereNotNull('category_id'), $filter)->distinct('category_id')->count('category_id'),
            'total_shares'             => $this->applyDateFilter(SubscriberShareLink::where('user_id', $user->id), $filter)->count(),
            'total_views'              => $totalViews,
            'total_downloads'          => $totalDownloads,
            'unread_notifications_count' => $user->unreadNotifications()->count(),
            'monthly_views'            => $monthlyViews,
            'conversion_rate'          => $conversionRate,
        ];

        // ── Recent products ─────────────────────────────────────
        $recentProducts = SubscriberProduct::where('user_id', $user->id)
            ->with('images')
            ->latest()
            ->take(6)
            ->get();

        // ── Activity log ────────────────────────────────────────
        $recentActivity = SubscriberActivityLog::where('user_id', $user->id)
            ->latest('created_at')
            ->take(10)
            ->get();

        // ── Top share links by views ─────────────────────────────
        $topShareLinks = SubscriberShareLink::where('user_id', $user->id)
            ->with('product')
            ->orderByDesc('view_count')
            ->take(5)
            ->get();

        // ── Notifications ───────────────────────────────────────
        $recentNotifications = $user->notifications()->latest()->take(5)->get();

        // ── Chart data based on filter (100% real) ──────────────
        $chartData = $this->getChartDataForFilter($filter, $user->id);

        $conversionRateData = [];
        foreach ($chartData['visits'] as $idx => $v) {
            $s = $chartData['shares'][$idx] ?? 0;
            $conversionRateData[] = $v > 0 ? round(($s / $v) * 100, 1) : 0;
        }

        $dashboardCharts = [
            'monthlyViews' => [
                'labels' => $chartData['labels'],
                'data'   => $chartData['visits'],
            ],
            'conversionRate' => [
                'labels' => $chartData['labels'],
                'data'   => $conversionRateData,
            ],
        ];

        $currentFilter = $filter;

        return view('subscriber-panel.dashboard.index', compact(
            'user',
            'subscription',
            'profile',
            'stats',
            'recentProducts',
            'recentActivity',
            'topShareLinks',
            'dashboardCharts',
            'recentNotifications',
            'currentFilter'
        ));
    }

    /* ─────────────────────────────────────────────────────────────
     |  ADMIN DASHBOARD  — 100 % real data
     * ──────────────────────────────────────────────────────────── */
    protected function buildAdminData($user, $filter = 'all_time'): array
    {
        // ── Core counts ──────────────────────────────────────────
        $brandsCount       = Brand::count();
        $categoriesCount   = Category::count();
        $subcategoriesCount = Subcategory::count();
        $productsCount     = $this->applyDateFilter(Product::query(), $filter)->count();
        $usersCount        = $this->applyDateFilter(User::query(), $filter)->count();
        $rolesCount        = Role::count();
        $enquiriesQuery = Enquiry::query();
        if ($user->isDemo()) {
            $enquiriesQuery->whereNull('subscriber_product_id');
        }
        $enquiriesCount    = $this->applyDateFilter($enquiriesQuery, $filter)->count();

        $recentProducts  = Product::latest()->take(5)->get();
        $recentUsers     = User::latest()->take(5)->get();

        $recentEnquiriesQuery = Enquiry::with(['product', 'brand', 'subscriberProduct']);
        if ($user->isDemo()) {
            $recentEnquiriesQuery->whereNull('subscriber_product_id');
        }
        $recentEnquiries = $recentEnquiriesQuery->latest()->take(5)->get();

        // ── Permission flags ─────────────────────────────────────
        $dashboardAccess = [
            'dashboard'   => $user->can('dashboard.view')    || $user->can('view-dashboard'),
            'analytics'   => $user->can('dashboard.analytics')|| $user->can('view-dashboard'),
            'users'       => $user->can('users.view')        || $user->can('view-users'),
            'products'    => $user->can('products.view')     || $user->can('view-products'),
            'brands'      => $user->can('brands.view')       || $user->can('view-brands'),
            'categories'  => $user->can('categories.view')   || $user->can('view-categories'),
            'enquiries'   => $user->can('enquiries.view')    || $user->can('view-enquiries'),
            'roles'       => $user->can('roles.manage')      || $user->can('view-roles'),
            'permissions' => $user->can('permissions.manage')|| $user->can('view-permissions'),
            'settings'    => $user->can('settings.manage')   || $user->can('view-settings'),
            'system'      => $user->can('system.manage')     || $user->can('manage-system'),
        ];

        // ── Subscriber & vendor counts (real) ────────────────────
        $subscriberCount = User::role('Subscriber')->count();
        $activeVendors   = \App\Models\SubscriberProfile::whereIn('status', ['approved', 'active'])->count();
        $totalStores     = \App\Models\SubscriberProfile::count();
        $pendingApprovals = \App\Models\SubscriberProfile::where('status', 'pending')->count();

        // ── Revenue (real) ───────────────────────────────────────
        $totalRevenue  = $this->applyDateFilter(\App\Models\Payment::where('status', 'success'), $filter, 'paid_at')->sum('amount') ?: 0.00;
        $revenue       = '₹' . number_format($totalRevenue, 2);
        $monthlyOrders = $this->applyDateFilter(\App\Models\Payment::where('status', 'success'), $filter, 'paid_at')->count();

        // ── Conversion rate: enquiries vs total share-link views ─
        $totalViews     = $this->applyDateFilter(SubscriberShareLink::query(), $filter)->sum('view_count') ?: 0;
        $conversionRate = $totalViews > 0 ? round(($enquiriesCount / $totalViews) * 100, 1) : 0;

        // ── Chart data dynamically loaded based on filter (100% real) ──
        $chartData = $this->getChartDataForFilter($filter);

        // ── Sharing breakdown (real) ─────────────────────────────
        $whatsappShares = $this->applyDateFilter(SubscriberShareLink::where('type', 'whatsapp'), $filter)->count();
        $pdfShares      = $this->applyDateFilter(SubscriberShareLink::where(function($q) {
            $q->where('type', 'pdf')->orWhereNotNull('pdf_path');
        }), $filter)->count();
        $linkShares     = $this->applyDateFilter(SubscriberShareLink::where(function($q) {
            $q->where('type', 'catalog')->orWhere('type', 'image');
        }), $filter)->count();

        // ── Top catalogue products by share-link views (real) ────
        $isDemo = $user && $user->isDemo();

        $topProductsQuery = $isDemo ? collect() : SubscriberProduct::where('approval_status', 'approved')
            ->withCount(['shareLinks as total_views' => function ($q) use ($filter) {
                $q->select(DB::raw('COALESCE(SUM(view_count),0)'));
                $this->applyDateFilter($q, $filter);
            }])
            ->orderByDesc('total_views')
            ->take(5)
            ->get();

        $topProducts = $topProductsQuery->map(function ($tp) {
            return [
                'name'    => $tp->name,
                'sales'   => $tp->total_views ?? 0,
                'revenue' => $tp->offer_price
                    ? '₹' . number_format($tp->offer_price, 2)
                    : ($tp->mrp ? '₹' . number_format($tp->mrp, 2) : '—'),
                'trend'   => ($tp->total_views ?? 0) > 0 ? 'up' : 'neutral',
            ];
        })->toArray();

        // Fallback when there are no subscriber products yet
        if (empty($topProducts)) {
            // Use global admin products instead
            $adminProducts = Product::orderByDesc('created_at')->take(5)->get();
            $topProducts = $adminProducts->map(function ($p) {
                return [
                    'name'    => $p->name,
                    'sales'   => 0,
                    'revenue' => $p->price ? '₹' . number_format($p->price, 2) : '—',
                    'trend'   => 'neutral',
                ];
            })->toArray();
        }

        // ── Recent transactions (real) ───────────────────────────
        $payments = $this->applyDateFilter(\App\Models\Payment::with(['user', 'plan']), $filter)
            ->latest()
            ->take(5)
            ->get();

        $recentTransactions = $payments->map(function ($p) {
            return [
                'id'       => $p->transaction_id ?? ('TXN-' . str_pad($p->id, 5, '0', STR_PAD_LEFT)),
                'customer' => $p->user?->name ?? 'Unknown',
                'amount'   => '₹' . number_format($p->amount, 2),
                'status'   => $p->status,
                'date'     => $p->created_at->diffForHumans(),
            ];
        })->toArray();

        // ── Support tickets from real enquiries ──────────────────
        $ticketsQuery = $this->applyDateFilter(Enquiry::query(), $filter)->latest()->take(4)->get();
        $supportTickets = $ticketsQuery->map(function ($t) {
            return [
                'id'       => 'TKT-' . str_pad($t->id, 3, '0', STR_PAD_LEFT),
                'subject'  => $t->subject ?: ($t->name ? $t->name . "'s Enquiry" : 'B2B Product Enquiry'),
                'priority' => $t->is_read ? 'low' : 'medium',
                'status'   => $t->is_read ? 'resolved' : 'open',
            ];
        })->toArray();

        // ── Active users (real — last 30 minutes) ─────────────────
        $recentlyActiveUsers = User::with('roles')
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->orderByDesc('updated_at')
            ->take(4)
            ->get()
            ->map(function ($u) {
                $minutesAgo = $u->updated_at->diffInMinutes(now());
                return [
                    'name'       => $u->name,
                    'role'       => $u->roles->pluck('name')->first() ?? 'User',
                    'lastActive' => $u->updated_at->diffForHumans(),
                    'status'     => $minutesAgo < 5 ? 'online' : ($minutesAgo < 15 ? 'away' : 'offline'),
                ];
            })->toArray();

        // Fallback: load last 4 users if none active recently
        if (empty($recentlyActiveUsers)) {
            $recentlyActiveUsers = User::with('roles')
                ->orderByDesc('updated_at')
                ->take(4)
                ->get()
                ->map(function ($u) {
                    return [
                        'name'       => $u->name,
                        'role'       => $u->roles->pluck('name')->first() ?? 'User',
                        'lastActive' => $u->updated_at->diffForHumans(),
                        'status'     => 'offline',
                    ];
                })->toArray();
        }

        // ── Notifications from real DB notifications ──────────────
        $notifList = $user->notifications()->latest()->take(4)->get()
            ->map(function ($n) {
                $data = $n->data;
                return [
                    'type'    => $data['type']    ?? 'system',
                    'message' => $data['message'] ?? ($data['title'] ?? 'New notification'),
                    'time'    => $n->created_at->diffForHumans(),
                ];
            })->toArray();

        // ── Comparative growth % based on filter ──────────────────
        $now = now();
        if ($filter === 'today') {
            $currentPeriod = \App\Models\Payment::where('status', 'success')->whereDate('paid_at', $now->toDateString())->sum('amount') ?: 0;
            $previousPeriod = \App\Models\Payment::where('status', 'success')->whereDate('paid_at', $now->copy()->subDay()->toDateString())->sum('amount') ?: 0;
        } elseif ($filter === 'yesterday') {
            $currentPeriod = \App\Models\Payment::where('status', 'success')->whereDate('paid_at', $now->copy()->subDay()->toDateString())->sum('amount') ?: 0;
            $previousPeriod = \App\Models\Payment::where('status', 'success')->whereDate('paid_at', $now->copy()->subDays(2)->toDateString())->sum('amount') ?: 0;
        } elseif ($filter === 'this_week') {
            $currentPeriod = \App\Models\Payment::where('status', 'success')->whereBetween('paid_at', [$now->copy()->startOfWeek()->toDateTimeString(), $now->copy()->endOfWeek()->toDateTimeString()])->sum('amount') ?: 0;
            $previousPeriod = \App\Models\Payment::where('status', 'success')->whereBetween('paid_at', [$now->copy()->subWeek()->startOfWeek()->toDateTimeString(), $now->copy()->subWeek()->endOfWeek()->toDateTimeString()])->sum('amount') ?: 0;
        } elseif ($filter === 'last_30_days') {
            $currentPeriod = \App\Models\Payment::where('status', 'success')->whereBetween('paid_at', [$now->copy()->subDays(30)->toDateTimeString(), now()->toDateTimeString()])->sum('amount') ?: 0;
            $previousPeriod = \App\Models\Payment::where('status', 'success')->whereBetween('paid_at', [$now->copy()->subDays(60)->toDateTimeString(), $now->copy()->subDays(30)->toDateTimeString()])->sum('amount') ?: 0;
        } elseif ($filter === 'this_month') {
            $currentPeriod = \App\Models\Payment::where('status', 'success')->whereMonth('paid_at', $now->month)->whereYear('paid_at', $now->year)->sum('amount') ?: 0;
            $previousPeriod = \App\Models\Payment::where('status', 'success')->whereMonth('paid_at', $now->copy()->subMonth()->month)->whereYear('paid_at', $now->copy()->subMonth()->year)->sum('amount') ?: 0;
        } elseif ($filter === 'last_month') {
            $currentPeriod = \App\Models\Payment::where('status', 'success')->whereMonth('paid_at', $now->copy()->subMonth()->month)->whereYear('paid_at', $now->copy()->subMonth()->year)->sum('amount') ?: 0;
            $previousPeriod = \App\Models\Payment::where('status', 'success')->whereMonth('paid_at', $now->copy()->subMonths(2)->month)->whereYear('paid_at', $now->copy()->subMonths(2)->year)->sum('amount') ?: 0;
        } elseif ($filter === 'this_year') {
            $currentPeriod = \App\Models\Payment::where('status', 'success')->whereYear('paid_at', $now->year)->sum('amount') ?: 0;
            $previousPeriod = \App\Models\Payment::where('status', 'success')->whereYear('paid_at', $now->copy()->subYear()->year)->sum('amount') ?: 0;
        } else {
            $currentPeriod = \App\Models\Payment::where('status', 'success')->sum('amount') ?: 0;
            $previousPeriod = 0;
        }
        $monthlyGrowth = $previousPeriod > 0 ? round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 1) : 0;

        return [
            'user'               => $user,
            'currentRole'        => $this->currentRole($user),
            'dashboardAccess'    => $dashboardAccess,

            // Counts
            'brandsCount'        => $brandsCount,
            'categoriesCount'    => $categoriesCount,
            'subcategoriesCount' => $subcategoriesCount,
            'productsCount'      => $productsCount,
            'usersCount'         => $usersCount,
            'rolesCount'         => $rolesCount,
            'enquiriesCount'     => $enquiriesCount,
            'subscriberCount'    => $subscriberCount,
            'subscribersCount'   => $subscriberCount,
            'activeVendors'      => $activeVendors,
            'totalStores'        => $totalStores,
            'pendingApprovals'   => $pendingApprovals,

            // KPIs
            'revenue'            => $revenue,
            'monthlyOrders'      => $monthlyOrders,
            'conversionRate'     => $conversionRate,
            'totalViews'         => $totalViews,
            'monthlyGrowth'      => $monthlyGrowth,

            // Charts (100% real)
            'revenueChart' => [
                'labels'  => $chartData['labels'],
                'revenue' => $chartData['revenue'],
                'orders'  => $chartData['orders'],
            ],
            'analytics' => [
                'visits'      => $chartData['visits'],
                'shares'      => $chartData['shares'],
                'enquiries'   => $chartData['enquiries'],
                'labels'      => $chartData['labels'],
                'sharing_breakdown' => [
                    'whatsapp' => $whatsappShares,
                    'pdf'      => $pdfShares,
                    'link'     => $linkShares,
                ],
            ],

            // Tables
            'topProducts'        => $topProducts,
            'recentTransactions' => $recentTransactions,
            'supportTickets'     => $supportTickets,
            'activeUsers'        => $recentlyActiveUsers,
            'notifications'      => $notifList,
            'recentProducts'     => $recentProducts,
            'recentUsers'        => $recentUsers,
            'recentEnquiries'    => $recentEnquiries,
        ];
    }

    protected function applyDateFilter($query, $filter, $column = 'created_at')
    {
        $now = now();
        switch ($filter) {
            case 'today':
                return $query->whereDate($column, $now->toDateString());
            case 'yesterday':
                return $query->whereDate($column, $now->copy()->subDay()->toDateString());
            case 'this_week':
                return $query->whereBetween($column, [$now->copy()->startOfWeek()->toDateTimeString(), $now->copy()->endOfWeek()->toDateTimeString()]);
            case 'last_30_days':
                return $query->whereBetween($column, [$now->copy()->subDays(30)->toDateTimeString(), now()->toDateTimeString()]);
            case 'this_month':
                return $query->whereMonth($column, $now->month)->whereYear($column, $now->year);
            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                return $query->whereMonth($column, $lastMonth->month)->whereYear($column, $lastMonth->year);
            case 'this_year':
                return $query->whereYear($column, $now->year);
            case 'all_time':
            default:
                return $query;
        }
    }

    protected function getChartDataForFilter($filter, $userId = null)
    {
        $now = now();
        $labels = [];
        $revenue = [];
        $orders = [];
        $visits = [];
        $shares = [];
        $enquiries = [];

        $paymentQuery = \App\Models\Payment::where('status', 'success');
        $shareQuery = \App\Models\SubscriberShareLink::query();
        $enquiryQuery = \App\Models\Enquiry::query();

        if ($userId) {
            $paymentQuery->where('user_id', $userId);
            $shareQuery->where('user_id', $userId);
        }

        if ($filter === 'today' || $filter === 'yesterday') {
            $targetDate = $filter === 'today' ? $now : $now->copy()->subDay();
            $dateStr = $targetDate->toDateString();

            for ($h = 0; $h < 24; $h++) {
                $labels[] = sprintf('%02d:00', $h);

                $revenue[] = (float) (clone $paymentQuery)->whereDate('paid_at', $dateStr)->whereRaw('HOUR(paid_at) = ?', [$h])->sum('amount');
                $orders[] = (int) (clone $paymentQuery)->whereDate('paid_at', $dateStr)->whereRaw('HOUR(paid_at) = ?', [$h])->count();
                $visits[] = (int) (clone $shareQuery)->whereDate('created_at', $dateStr)->whereRaw('HOUR(created_at) = ?', [$h])->sum('view_count');
                $shares[] = (int) (clone $shareQuery)->whereDate('created_at', $dateStr)->whereRaw('HOUR(created_at) = ?', [$h])->sum('download_count');
                if (!$userId) {
                    $enquiries[] = (int) (clone $enquiryQuery)->whereDate('created_at', $dateStr)->whereRaw('HOUR(created_at) = ?', [$h])->count();
                } else {
                    $enquiries[] = 0;
                }
            }
        } elseif ($filter === 'this_week') {
            $start = $now->copy()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $day = $start->copy()->addDays($i);
                $labels[] = $day->format('D');
                $dateStr = $day->toDateString();

                $revenue[] = (float) (clone $paymentQuery)->whereDate('paid_at', $dateStr)->sum('amount');
                $orders[] = (int) (clone $paymentQuery)->whereDate('paid_at', $dateStr)->count();
                $visits[] = (int) (clone $shareQuery)->whereDate('created_at', $dateStr)->sum('view_count');
                $shares[] = (int) (clone $shareQuery)->whereDate('created_at', $dateStr)->sum('download_count');
                if (!$userId) {
                    $enquiries[] = (int) (clone $enquiryQuery)->whereDate('created_at', $dateStr)->count();
                } else {
                    $enquiries[] = 0;
                }
            }
        } elseif ($filter === 'last_30_days') {
            for ($i = 29; $i >= 0; $i--) {
                $day = $now->copy()->subDays($i);
                $labels[] = $day->format('M d');
                $dateStr = $day->toDateString();

                $revenue[] = (float) (clone $paymentQuery)->whereDate('paid_at', $dateStr)->sum('amount');
                $orders[] = (int) (clone $paymentQuery)->whereDate('paid_at', $dateStr)->count();
                $visits[] = (int) (clone $shareQuery)->whereDate('created_at', $dateStr)->sum('view_count');
                $shares[] = (int) (clone $shareQuery)->whereDate('created_at', $dateStr)->sum('download_count');
                if (!$userId) {
                    $enquiries[] = (int) (clone $enquiryQuery)->whereDate('created_at', $dateStr)->count();
                } else {
                    $enquiries[] = 0;
                }
            }
        } elseif ($filter === 'this_month' || $filter === 'last_month') {
            $targetMonth = $filter === 'this_month' ? $now : $now->copy()->subMonth();
            $daysInMonth = $targetMonth->daysInMonth;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $day = $targetMonth->copy()->day($d);
                $labels[] = $day->format('d');
                $dateStr = $day->toDateString();

                $revenue[] = (float) (clone $paymentQuery)->whereDate('paid_at', $dateStr)->sum('amount');
                $orders[] = (int) (clone $paymentQuery)->whereDate('paid_at', $dateStr)->count();
                $visits[] = (int) (clone $shareQuery)->whereDate('created_at', $dateStr)->sum('view_count');
                $shares[] = (int) (clone $shareQuery)->whereDate('created_at', $dateStr)->sum('download_count');
                if (!$userId) {
                    $enquiries[] = (int) (clone $enquiryQuery)->whereDate('created_at', $dateStr)->count();
                } else {
                    $enquiries[] = 0;
                }
            }
        } else {
            // this_year, all_time or default: monthly breakdown
            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            $labels = $months;
            $year = $filter === 'last_year' ? $now->year - 1 : $now->year;

            for ($m = 1; $m <= 12; $m++) {
                $revenue[] = (float) (clone $paymentQuery)->whereYear('paid_at', $year)->whereMonth('paid_at', $m)->sum('amount');
                $orders[] = (int) (clone $paymentQuery)->whereYear('paid_at', $year)->whereMonth('paid_at', $m)->count();
                $visits[] = (int) (clone $shareQuery)->whereYear('created_at', $year)->whereMonth('created_at', $m)->sum('view_count');
                $shares[] = (int) (clone $shareQuery)->whereYear('created_at', $year)->whereMonth('created_at', $m)->sum('download_count');
                if (!$userId) {
                    $enquiries[] = (int) (clone $enquiryQuery)->whereYear('created_at', $year)->whereMonth('created_at', $m)->count();
                } else {
                    $enquiries[] = 0;
                }
            }
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'orders' => $orders,
            'visits' => $visits,
            'shares' => $shares,
            'enquiries' => $enquiries,
        ];
    }

    protected function currentRole($user): string
    {
        return $user->roles->pluck('name')->first() ?? 'User';
    }
}
