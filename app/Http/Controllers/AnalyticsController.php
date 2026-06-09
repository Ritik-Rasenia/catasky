<?php

namespace App\Http\Controllers;

use App\Models\VisitLog;
use App\Models\ShareTrack;
use App\Models\ProductViewLog;
use App\Models\DownloadLog;
use App\Models\OrderLog;
use App\Models\Enquiry;
use App\Models\SubscriberProduct;
use App\Models\SubscriberShareLink;
use App\Models\EngagementLog;
use App\Models\User;
use App\Exports\AnalyticsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Admin Analytics Dashboard
     */
    public function adminAnalytics(Request $request)
    {
        $filter = $request->query('filter', 'all_time');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');
        $user = auth()->user();

        // KPI Cards
        $totalShares = $this->applyDateFilter(ShareTrack::query(), $filter, 'shared_at', $dateFrom, $dateTo)->count();
        $totalOpens = $this->applyDateFilter(VisitLog::query(), $filter, 'opened_at', $dateFrom, $dateTo)->count();
        $uniqueVisitors = $this->applyDateFilter(VisitLog::query(), $filter, 'opened_at', $dateFrom, $dateTo)->distinct('visitor_uuid')->count('visitor_uuid');
        $productViews = $this->applyDateFilter(ProductViewLog::query(), $filter, 'viewed_at', $dateFrom, $dateTo)->count();
        $totalDownloads = $this->applyDateFilter(DownloadLog::query(), $filter, 'downloaded_at', $dateFrom, $dateTo)->count();
        $totalEnquiries = $this->applyDateFilter(Enquiry::query(), $filter, 'created_at', $dateFrom, $dateTo)->count();
        $totalOrders = $this->applyDateFilter(OrderLog::query(), $filter, 'created_at', $dateFrom, $dateTo)->count();

        $avgSessionDuration = (int) $this->applyDateFilter(VisitLog::query(), $filter, 'opened_at', $dateFrom, $dateTo)->avg('total_time_spent');
        $bounceRate = $totalOpens > 0
            ? round(($this->applyDateFilter(VisitLog::where('bounce', true), $filter, 'opened_at', $dateFrom, $dateTo)->count() / $totalOpens) * 100, 1)
            : 0;
        $conversionRate = $totalOpens > 0 ? round(($totalEnquiries / $totalOpens) * 100, 1) : 0;

        // Charts Data
        $chartData = $this->getChartData($filter, $dateFrom, $dateTo);

        // Top 10 Most Viewed Products
        $topProducts = ProductViewLog::select('subscriber_product_id', DB::raw('COUNT(*) as view_count'), DB::raw('AVG(duration) as avg_duration'))
            ->when($filter !== 'all_time' || ($dateFrom || $dateTo), fn($q) => $this->applyDateFilter($q, $filter, 'viewed_at', $dateFrom, $dateTo))
            ->groupBy('subscriber_product_id')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $product = SubscriberProduct::withTrashed()->find($row->subscriber_product_id);
                return [
                    'id' => $row->subscriber_product_id,
                    'name' => $product?->name ?? 'Deleted Product',
                    'view_count' => $row->view_count,
                    'avg_duration' => round($row->avg_duration),
                ];
            });

        // Device Distribution
        $deviceDistribution = $this->applyDateFilter(VisitLog::query(), $filter, 'opened_at', $dateFrom, $dateTo)
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->pluck('count', 'device_type')
            ->toArray();

        // Channel Distribution (from share_tracks)
        $channelDistribution = $this->applyDateFilter(ShareTrack::query(), $filter, 'shared_at', $dateFrom, $dateTo)
            ->select('channel', DB::raw('COUNT(*) as count'))
            ->groupBy('channel')
            ->get()
            ->pluck('count', 'channel')
            ->toArray();

        // Engagement Events breakdown (new engagement_logs table)
        $engagementByType = $this->applyDateFilter(EngagementLog::query(), $filter, 'created_at', $dateFrom, $dateTo)
            ->select('event_type', DB::raw('COUNT(*) as count'))
            ->groupBy('event_type')
            ->get()
            ->pluck('count', 'event_type')
            ->toArray();

        $totalEngagements = array_sum($engagementByType);

        // Recent Engagement Events
        $recentEngagements = EngagementLog::with(['user', 'product', 'shareLink'])
            ->latest()
            ->take(20)
            ->get();

        // Top Products by Engagement Events
        $topEngagedProducts = EngagementLog::select('subscriber_product_id', DB::raw('COUNT(*) as engagement_count'))
            ->whereNotNull('subscriber_product_id')
            ->when($filter !== 'all_time' || ($dateFrom || $dateTo), fn($q) => $this->applyDateFilter($q, $filter, 'created_at', $dateFrom, $dateTo))
            ->groupBy('subscriber_product_id')
            ->orderByDesc('engagement_count')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $product = SubscriberProduct::withTrashed()->find($row->subscriber_product_id);
                return [
                    'name' => $product?->name ?? 'Deleted Product',
                    'engagement_count' => $row->engagement_count,
                ];
            });

        // Top Performing Subscribers
        $topSubscribers = ShareTrack::select('user_id', DB::raw('COUNT(*) as shares'))
            ->whereNotNull('user_id')
            ->when($filter !== 'all_time' || ($dateFrom || $dateTo), fn($q) => $this->applyDateFilter($q, $filter, 'shared_at', $dateFrom, $dateTo))
            ->groupBy('user_id')
            ->orderByDesc('shares')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $user = User::find($row->user_id);
                $views = VisitLog::whereHas('shareTrack', fn($q) => $q->where('user_id', $row->user_id))->count();
                $downloads = DownloadLog::where('user_id', $row->user_id)->count();
                $enquiries = Enquiry::whereHas('shareTrack', fn($q) => $q->where('user_id', $row->user_id))->count();
                $engagements = EngagementLog::where('user_id', $row->user_id)->count();
                return [
                    'name' => $user?->name ?? 'Unknown',
                    'email' => $user?->email ?? '',
                    'shares' => $row->shares,
                    'views' => $views,
                    'downloads' => $downloads,
                    'conversions' => $enquiries,
                    'engagements' => $engagements,
                ];
            });

        // Most Shared Catalogues (ShareLinks)
        $mostShared = SubscriberShareLink::select('subscriber_share_links.*', DB::raw('(SELECT COUNT(*) FROM share_tracks WHERE share_tracks.subscriber_share_link_id = subscriber_share_links.id) as share_count'))
            ->orderByDesc('share_count')
            ->limit(10)
            ->get();

        // Engagement trend chart data
        $engagementTrend = $this->getEngagementTrendData($filter, null, $dateFrom, $dateTo);

        // Most Downloaded Catalogues
        $mostDownloaded = DownloadLog::select('subscriber_share_link_id', DB::raw('COUNT(*) as download_count'), DB::raw('COUNT(DISTINCT ip_address) as unique_downloads'))
            ->when($filter !== 'all_time', fn($q) => $this->applyDateFilter($q, $filter, 'downloaded_at'))
            ->groupBy('subscriber_share_link_id')
            ->orderByDesc('download_count')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $link = SubscriberShareLink::withTrashed()->find($row->subscriber_share_link_id);
                return [
                    'title' => $link?->title ?? ($link?->token ?? 'Unknown'),
                    'download_count' => $row->download_count,
                    'unique_downloads' => $row->unique_downloads,
                ];
            });

        // Recent Visit Logs (Visitor Engagement)
        $recentVisits = VisitLog::with(['shareTrack.user', 'productViews.product'])
            ->latest('opened_at')
            ->take(20)
            ->get();

        return view('admin.analytics.index', compact(
            'totalShares', 'totalOpens', 'uniqueVisitors', 'productViews', 'totalDownloads',
            'totalEnquiries', 'totalOrders', 'avgSessionDuration', 'bounceRate', 'conversionRate',
            'chartData', 'topProducts', 'deviceDistribution', 'channelDistribution',
            'topSubscribers', 'mostShared', 'mostDownloaded', 'recentVisits', 'filter',
            'engagementByType', 'totalEngagements', 'recentEngagements', 'topEngagedProducts',
            'engagementTrend', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Subscriber Analytics Dashboard
     */
    public function subscriberAnalytics(Request $request)
    {
        $filter = $request->query('filter', 'all_time');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');
        $user = auth()->user();
        $userId = $user->id;

        // KPI Cards (scoped to subscriber)
        $totalShares = $this->applyDateFilter(ShareTrack::where('user_id', $userId), $filter, 'shared_at', $dateFrom, $dateTo)->count();
        $totalOpens = $this->applyDateFilter(
            VisitLog::whereHas('shareTrack', fn($q) => $q->where('user_id', $userId)),
            $filter, 'opened_at', $dateFrom, $dateTo
        )->count();
        $uniqueVisitors = $this->applyDateFilter(
            VisitLog::whereHas('shareTrack', fn($q) => $q->where('user_id', $userId)),
            $filter, 'opened_at', $dateFrom, $dateTo
        )->distinct('visitor_uuid')->count('visitor_uuid');
        $productViews = $this->applyDateFilter(
            ProductViewLog::whereHas('visitLog.shareTrack', fn($q) => $q->where('user_id', $userId)),
            $filter, 'viewed_at', $dateFrom, $dateTo
        )->count();
        $totalDownloads = $this->applyDateFilter(DownloadLog::where('user_id', $userId), $filter, 'downloaded_at', $dateFrom, $dateTo)->count();
        $totalEnquiries = $this->applyDateFilter(Enquiry::query(), $filter, 'created_at', $dateFrom, $dateTo)->count();
        $totalOrders = $this->applyDateFilter(
            OrderLog::whereHas('shareLink', fn($q) => $q->where('user_id', $userId)),
            $filter, 'created_at', $dateFrom, $dateTo
        )->count();

        $avgSessionDuration = (int) $this->applyDateFilter(
            VisitLog::whereHas('shareTrack', fn($q) => $q->where('user_id', $userId)),
            $filter, 'opened_at', $dateFrom, $dateTo
        )->avg('total_time_spent');

        $bounceRate = $totalOpens > 0
            ? round(($this->applyDateFilter(
                VisitLog::where('bounce', true)->whereHas('shareTrack', fn($q) => $q->where('user_id', $userId)),
                $filter, 'opened_at', $dateFrom, $dateTo
            )->count() / $totalOpens) * 100, 1)
            : 0;

        $conversionRate = $totalOpens > 0 ? round(($totalEnquiries / $totalOpens) * 100, 1) : 0;

        // Charts Data (subscriber scoped)
        $chartData = $this->getSubscriberChartData($filter, $userId, $dateFrom, $dateTo);

        // Top Viewed Products for this subscriber
        $topProducts = ProductViewLog::select('subscriber_product_id', DB::raw('COUNT(*) as view_count'), DB::raw('AVG(duration) as avg_duration'))
            ->whereHas('visitLog.shareTrack', fn($q) => $q->where('user_id', $userId))
            ->when($filter !== 'all_time' || ($dateFrom || $dateTo), fn($q) => $this->applyDateFilter($q, $filter, 'viewed_at', $dateFrom, $dateTo))
            ->groupBy('subscriber_product_id')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $product = SubscriberProduct::withTrashed()->find($row->subscriber_product_id);
                return [
                    'id' => $row->subscriber_product_id,
                    'name' => $product?->name ?? 'Deleted Product',
                    'view_count' => $row->view_count,
                    'avg_duration' => round($row->avg_duration),
                ];
            });

        // Device Distribution
        $deviceDistribution = $this->applyDateFilter(
            VisitLog::whereHas('shareTrack', fn($q) => $q->where('user_id', $userId)),
            $filter, 'opened_at', $dateFrom, $dateTo
        )->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->pluck('count', 'device_type')
            ->toArray();

        // Channel Distribution
        $channelDistribution = $this->applyDateFilter(ShareTrack::where('user_id', $userId), $filter, 'shared_at', $dateFrom, $dateTo)
            ->select('channel', DB::raw('COUNT(*) as count'))
            ->groupBy('channel')
            ->get()
            ->pluck('count', 'channel')
            ->toArray();

        // Engagement Events (scoped to subscriber)
        $engagementByType = $this->applyDateFilter(EngagementLog::where('user_id', $userId), $filter, 'created_at', $dateFrom, $dateTo)
            ->select('event_type', DB::raw('COUNT(*) as count'))
            ->groupBy('event_type')
            ->get()
            ->pluck('count', 'event_type')
            ->toArray();

        $totalEngagements = array_sum($engagementByType);

        // Recent Engagement Events for this subscriber
        $recentEngagements = EngagementLog::where('user_id', $userId)
            ->with(['product', 'shareLink'])
            ->latest()
            ->take(15)
            ->get();

        // Engagement trend chart data (subscriber scoped)
        $engagementTrend = $this->getEngagementTrendData($filter, $userId, $dateFrom, $dateTo);

        // Conversion Funnel
        $funnel = [
            'shares' => $totalShares,
            'opens' => $totalOpens,
            'product_views' => $productViews,
            'downloads' => $totalDownloads,
            'enquiries' => $totalEnquiries,
            'orders' => $totalOrders,
        ];

        // Recent Visits
        $recentVisits = VisitLog::whereHas('shareTrack', fn($q) => $q->where('user_id', $userId))
            ->with(['shareTrack', 'productViews.product'])
            ->latest('opened_at')
            ->take(15)
            ->get();

        return view('subscriber-panel.analytics.index', compact(
            'totalShares', 'totalOpens', 'uniqueVisitors', 'productViews', 'totalDownloads',
            'totalEnquiries', 'totalOrders', 'avgSessionDuration', 'bounceRate', 'conversionRate',
            'chartData', 'topProducts', 'deviceDistribution', 'channelDistribution',
            'funnel', 'recentVisits', 'filter',
            'engagementByType', 'totalEngagements', 'recentEngagements', 'engagementTrend',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Visitor Activity Timeline
     */
    public function activityTimeline(string $visitorUuid)
    {
        $events = collect();

        // Visit/Open events
        $visits = VisitLog::where('visitor_uuid', $visitorUuid)
            ->with(['shareTrack.user', 'productViews.product', 'downloads', 'orders', 'enquiries'])
            ->orderBy('opened_at')
            ->get();

        foreach ($visits as $visit) {
            $events->push([
                'type' => 'visit',
                'time' => $visit->opened_at,
                'data' => $visit,
                'icon' => 'bi-eye-fill',
                'color' => 'primary',
                'title' => 'Catalogue Opened',
                'description' => "Visited from {$visit->country}, {$visit->city} via {$visit->browser} on {$visit->device_type}",
            ]);

            // Product views in this visit
            foreach ($visit->productViews as $pv) {
                $events->push([
                    'type' => 'product_view',
                    'time' => $pv->viewed_at,
                    'data' => $pv,
                    'icon' => 'bi-box-seam-fill',
                    'color' => 'info',
                    'title' => 'Product Viewed',
                    'description' => ($pv->product?->name ?? 'Unknown') . " - Duration: {$pv->duration}s (Order #{$pv->browse_order})",
                ]);
            }

            // Downloads in this visit
            foreach ($visit->downloads as $dl) {
                $events->push([
                    'type' => 'download',
                    'time' => $dl->downloaded_at,
                    'data' => $dl,
                    'icon' => 'bi-download',
                    'color' => 'success',
                    'title' => 'File Downloaded',
                    'description' => "Downloaded {$dl->file_type} file",
                ]);
            }

            // Enquiries in this visit
            foreach ($visit->enquiries as $enq) {
                $events->push([
                    'type' => 'enquiry',
                    'time' => $enq->created_at,
                    'data' => $enq,
                    'icon' => 'bi-chat-left-dots-fill',
                    'color' => 'warning',
                    'title' => 'Enquiry Submitted',
                    'description' => "From {$enq->name} - " . ($enq->subject ?? 'Product Enquiry'),
                ]);
            }

            // Orders in this visit
            foreach ($visit->orders as $order) {
                $events->push([
                    'type' => 'order',
                    'time' => $order->created_at,
                    'data' => $order,
                    'icon' => 'bi-bag-check-fill',
                    'color' => 'danger',
                    'title' => 'Order Placed',
                    'description' => ($order->product?->name ?? 'Unknown') . " - Qty: {$order->quantity}" . ($order->total_price ? " - ₹{$order->total_price}" : ''),
                ]);
            }
        }

        // Share events for this visitor
        $shareTracks = ShareTrack::whereHas('visitLogs', fn($q) => $q->where('visitor_uuid', $visitorUuid))->get();
        foreach ($shareTracks as $st) {
            $events->push([
                'type' => 'share',
                'time' => $st->shared_at,
                'data' => $st,
                'icon' => 'bi-share-fill',
                'color' => 'secondary',
                'title' => 'Shared via ' . ucfirst(str_replace('_', ' ', $st->channel)),
                'description' => "Shared by {$st->user?->name} on " . ucfirst($st->channel),
            ]);
        }

        // Sort all events chronologically
        $events = $events->sortBy('time')->values();

        return view('admin.analytics.timeline', compact('visitorUuid', 'events'));
    }

    /**
     * Subscriber Timeline
     */
    public function subscriberTimeline(string $visitorUuid)
    {
        $userId = auth()->id();

        $events = collect();

        $visits = VisitLog::where('visitor_uuid', $visitorUuid)
            ->whereHas('shareTrack', fn($q) => $q->where('user_id', $userId))
            ->with(['shareTrack', 'productViews.product', 'downloads', 'orders', 'enquiries'])
            ->orderBy('opened_at')
            ->get();

        foreach ($visits as $visit) {
            $events->push([
                'type' => 'visit', 'time' => $visit->opened_at, 'data' => $visit,
                'icon' => 'bi-eye-fill', 'color' => 'primary',
                'title' => 'Catalogue Opened',
                'description' => "From {$visit->country}, {$visit->city} via {$visit->browser}",
            ]);

            foreach ($visit->productViews as $pv) {
                $events->push([
                    'type' => 'product_view', 'time' => $pv->viewed_at, 'data' => $pv,
                    'icon' => 'bi-box-seam-fill', 'color' => 'info',
                    'title' => 'Product Viewed',
                    'description' => ($pv->product?->name ?? 'Unknown') . " - {$pv->duration}s",
                ]);
            }

            foreach ($visit->downloads as $dl) {
                $events->push([
                    'type' => 'download', 'time' => $dl->downloaded_at, 'data' => $dl,
                    'icon' => 'bi-download', 'color' => 'success',
                    'title' => 'Downloaded', 'description' => "{$dl->file_type} file",
                ]);
            }

            foreach ($visit->enquiries as $enq) {
                $events->push([
                    'type' => 'enquiry', 'time' => $enq->created_at, 'data' => $enq,
                    'icon' => 'bi-chat-left-dots-fill', 'color' => 'warning',
                    'title' => 'Enquiry', 'description' => $enq->name,
                ]);
            }

            foreach ($visit->orders as $order) {
                $events->push([
                    'type' => 'order', 'time' => $order->created_at, 'data' => $order,
                    'icon' => 'bi-bag-check-fill', 'color' => 'danger',
                    'title' => 'Order', 'description' => ($order->product?->name ?? '') . " x{$order->quantity}",
                ]);
            }
        }

        $events = $events->sortBy('time')->values();
        $isAdmin = false;

        return view('admin.analytics.timeline', compact('visitorUuid', 'events', 'isAdmin'));
    }

    /**
     * Real-time Analytics API (JSON)
     */
    public function realtimeData(Request $request)
    {
        $user = auth()->user();
        $isSubscriber = $user->hasRole('Subscriber');

        if ($isSubscriber) {
            $userId = $user->id;
            $recentVisits = VisitLog::whereHas('shareTrack', fn($q) => $q->where('user_id', $userId))
                ->with(['shareTrack', 'productViews.product'])
                ->latest('opened_at')
                ->take(10)
                ->get();

            $totalOpens = VisitLog::whereHas('shareTrack', fn($q) => $q->where('user_id', $userId))->count();
            $totalViews = ProductViewLog::whereHas('visitLog.shareTrack', fn($q) => $q->where('user_id', $userId))->count();
            $totalDownloads = DownloadLog::where('user_id', $userId)->count();
        } else {
            $recentVisits = VisitLog::with(['shareTrack.user', 'productViews.product'])
                ->latest('opened_at')
                ->take(10)
                ->get();

            $totalOpens = VisitLog::count();
            $totalViews = ProductViewLog::count();
            $totalDownloads = DownloadLog::count();
        }

        return response()->json([
            'total_opens' => $totalOpens,
            'total_views' => $totalViews,
            'total_downloads' => $totalDownloads,
            'recent_visits' => $recentVisits->map(function ($v) {
                return [
                    'id' => $v->id,
                    'visitor_uuid' => $v->visitor_uuid,
                    'ip' => $v->ip_address,
                    'device' => $v->device_type,
                    'browser' => $v->browser,
                    'country' => $v->country,
                    'city' => $v->city,
                    'opened_at' => $v->opened_at?->diffForHumans(),
                    'duration' => $v->total_time_spent,
                    'products_viewed' => $v->productViews->count(),
                ];
            }),
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Admin Export Excel
     */
    public function exportExcel(Request $request)
    {
        $filter = $request->query('filter', 'all_time');
        return (new AnalyticsExport($filter, null))->download('analytics-report-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Subscriber Export Excel
     */
    public function subscriberExport(Request $request)
    {
        $filter = $request->query('filter', 'all_time');
        $userId = auth()->id();
        return (new AnalyticsExport($filter, $userId))->download('my-analytics-report-' . date('Y-m-d') . '.xlsx');
    }

    /* ------------------------------------------------------------------
     | HELPER METHODS
     * ----------------------------------------------------------------- */

    protected function applyDateFilter($query, $filter, $column = 'created_at', $dateFrom = null, $dateTo = null)
    {
        // Custom date range takes priority
        if ($dateFrom || $dateTo) {
            if ($dateFrom && $dateTo) {
                return $query->whereBetween($column, [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay(),
                ]);
            } elseif ($dateFrom) {
                return $query->where($column, '>=', Carbon::parse($dateFrom)->startOfDay());
            } else {
                return $query->where($column, '<=', Carbon::parse($dateTo)->endOfDay());
            }
        }

        $now = now();
        switch ($filter) {
            case 'today':
                return $query->whereDate($column, $now->toDateString());
            case 'yesterday':
                return $query->whereDate($column, $now->copy()->subDay()->toDateString());
            case 'this_week':
                return $query->whereBetween($column, [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
            case 'last_30_days':
                return $query->whereBetween($column, [$now->copy()->subDays(30), $now]);
            case 'this_month':
                return $query->whereMonth($column, $now->month)->whereYear($column, $now->year);
            case 'last_month':
                $lm = $now->copy()->subMonth();
                return $query->whereMonth($column, $lm->month)->whereYear($column, $lm->year);
            case 'this_year':
                return $query->whereYear($column, $now->year);
            default:
                return $query;
        }
    }

    /**
     * Get engagement event counts per time period for trend chart.
     */
    protected function getEngagementTrendData(string $filter, ?int $userId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $now = now();
        $labels = [];
        $counts = [];

        $baseQuery = fn() => $userId
            ? EngagementLog::where('user_id', $userId)
            : EngagementLog::query();

        if ($filter === 'today' || $filter === 'yesterday') {
            $targetDate = $filter === 'today' ? $now : $now->copy()->subDay();
            $dateStr = $targetDate->toDateString();
            for ($h = 0; $h < 24; $h++) {
                $labels[] = sprintf('%02d:00', $h);
                $counts[] = (int) $baseQuery()->whereDate('created_at', $dateStr)->whereRaw('HOUR(created_at) = ?', [$h])->count();
            }
        } elseif ($filter === 'this_week') {
            $start = $now->copy()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $day = $start->copy()->addDays($i);
                $labels[] = $day->format('D');
                $counts[] = (int) $baseQuery()->whereDate('created_at', $day->toDateString())->count();
            }
        } elseif ($filter === 'last_30_days' || ($dateFrom && $dateTo)) {
            $start = $dateFrom ? Carbon::parse($dateFrom) : $now->copy()->subDays(29);
            $end   = $dateTo  ? Carbon::parse($dateTo)   : $now;
            $diff  = (int) $start->diffInDays($end);
            $step  = max(1, (int) ceil($diff / 30)); // group days if range > 30
            for ($i = 0; $i <= $diff; $i += $step) {
                $day = $start->copy()->addDays($i);
                $labels[] = $day->format('M d');
                $counts[] = (int) $baseQuery()->whereBetween('created_at', [
                    $day->copy()->startOfDay(),
                    $day->copy()->addDays($step - 1)->endOfDay(),
                ])->count();
            }
        } else {
            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            $labels = $months;
            $year = $now->year;
            for ($m = 1; $m <= 12; $m++) {
                $counts[] = (int) $baseQuery()->whereYear('created_at', $year)->whereMonth('created_at', $m)->count();
            }
        }

        return compact('labels', 'counts');
    }

    protected function getChartData(string $filter, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $now = now();
        $labels = [];
        $visits = [];
        $views = [];
        $downloads = [];
        $enquiries = [];

        // Custom range: iterate day-by-day (grouped if range is large)
        if ($dateFrom && $dateTo) {
            $start = Carbon::parse($dateFrom)->startOfDay();
            $end   = Carbon::parse($dateTo)->endOfDay();
            $diff  = (int) $start->diffInDays($end);
            $step  = max(1, (int) ceil($diff / 30));
            for ($i = 0; $i <= $diff; $i += $step) {
                $day     = $start->copy()->addDays($i);
                $dayEnd  = $day->copy()->addDays($step - 1)->endOfDay();
                $labels[]    = $day->format('M d');
                $visits[]    = (int) VisitLog::whereBetween('opened_at', [$day, $dayEnd])->count();
                $views[]     = (int) ProductViewLog::whereBetween('viewed_at', [$day, $dayEnd])->count();
                $downloads[] = (int) DownloadLog::whereBetween('downloaded_at', [$day, $dayEnd])->count();
                $enquiries[] = (int) Enquiry::whereBetween('created_at', [$day, $dayEnd])->count();
            }
            return compact('labels', 'visits', 'views', 'downloads', 'enquiries');
        }

        if ($filter === 'today' || $filter === 'yesterday') {
            $targetDate = $filter === 'today' ? $now : $now->copy()->subDay();
            $dateStr = $targetDate->toDateString();
            for ($h = 0; $h < 24; $h++) {
                $labels[] = sprintf('%02d:00', $h);
                $visits[] = (int) VisitLog::whereDate('opened_at', $dateStr)->whereRaw('HOUR(opened_at) = ?', [$h])->count();
                $views[] = (int) ProductViewLog::whereDate('viewed_at', $dateStr)->whereRaw('HOUR(viewed_at) = ?', [$h])->count();
                $downloads[] = (int) DownloadLog::whereDate('downloaded_at', $dateStr)->whereRaw('HOUR(downloaded_at) = ?', [$h])->count();
                $enquiries[] = (int) Enquiry::whereDate('created_at', $dateStr)->whereRaw('HOUR(created_at) = ?', [$h])->count();
            }
        } elseif ($filter === 'this_week') {
            $start = $now->copy()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $day = $start->copy()->addDays($i);
                $labels[] = $day->format('D');
                $dateStr = $day->toDateString();
                $visits[] = (int) VisitLog::whereDate('opened_at', $dateStr)->count();
                $views[] = (int) ProductViewLog::whereDate('viewed_at', $dateStr)->count();
                $downloads[] = (int) DownloadLog::whereDate('downloaded_at', $dateStr)->count();
                $enquiries[] = (int) Enquiry::whereDate('created_at', $dateStr)->count();
            }
        } elseif ($filter === 'last_30_days') {
            for ($i = 29; $i >= 0; $i--) {
                $day = $now->copy()->subDays($i);
                $labels[] = $day->format('M d');
                $dateStr = $day->toDateString();
                $visits[] = (int) VisitLog::whereDate('opened_at', $dateStr)->count();
                $views[] = (int) ProductViewLog::whereDate('viewed_at', $dateStr)->count();
                $downloads[] = (int) DownloadLog::whereDate('downloaded_at', $dateStr)->count();
                $enquiries[] = (int) Enquiry::whereDate('created_at', $dateStr)->count();
            }
        } else {
            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            $labels = $months;
            $year = $now->year;
            for ($m = 1; $m <= 12; $m++) {
                $visits[] = (int) VisitLog::whereYear('opened_at', $year)->whereMonth('opened_at', $m)->count();
                $views[] = (int) ProductViewLog::whereYear('viewed_at', $year)->whereMonth('viewed_at', $m)->count();
                $downloads[] = (int) DownloadLog::whereYear('downloaded_at', $year)->whereMonth('downloaded_at', $m)->count();
                $enquiries[] = (int) Enquiry::whereYear('created_at', $year)->whereMonth('created_at', $m)->count();
            }
        }

        return compact('labels', 'visits', 'views', 'downloads', 'enquiries');
    }

    protected function getSubscriberChartData(string $filter, int $userId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $now = now();
        $labels = [];
        $visits = [];
        $views = [];
        $downloads = [];
        $enquiries = [];

        $visitScope = fn($q) => $q->whereHas('shareTrack', fn($sq) => $sq->where('user_id', $userId));
        $viewScope = fn($q) => $q->whereHas('visitLog.shareTrack', fn($sq) => $sq->where('user_id', $userId));

        if ($filter === 'today' || $filter === 'yesterday') {
            $targetDate = $filter === 'today' ? $now : $now->copy()->subDay();
            $dateStr = $targetDate->toDateString();
            for ($h = 0; $h < 24; $h++) {
                $labels[] = sprintf('%02d:00', $h);
                $visits[] = (int) VisitLog::where($visitScope)->whereDate('opened_at', $dateStr)->whereRaw('HOUR(opened_at) = ?', [$h])->count();
                $views[] = (int) ProductViewLog::where($viewScope)->whereDate('viewed_at', $dateStr)->whereRaw('HOUR(viewed_at) = ?', [$h])->count();
                $downloads[] = (int) DownloadLog::where('user_id', $userId)->whereDate('downloaded_at', $dateStr)->whereRaw('HOUR(downloaded_at) = ?', [$h])->count();
                $enquiries[] = (int) Enquiry::whereDate('created_at', $dateStr)->whereRaw('HOUR(created_at) = ?', [$h])->count();
            }
        } elseif ($filter === 'this_week') {
            $start = $now->copy()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $day = $start->copy()->addDays($i);
                $labels[] = $day->format('D');
                $dateStr = $day->toDateString();
                $visits[] = (int) VisitLog::where($visitScope)->whereDate('opened_at', $dateStr)->count();
                $views[] = (int) ProductViewLog::where($viewScope)->whereDate('viewed_at', $dateStr)->count();
                $downloads[] = (int) DownloadLog::where('user_id', $userId)->whereDate('downloaded_at', $dateStr)->count();
                $enquiries[] = (int) Enquiry::whereDate('created_at', $dateStr)->count();
            }
        } elseif ($filter === 'last_30_days') {
            for ($i = 29; $i >= 0; $i--) {
                $day = $now->copy()->subDays($i);
                $labels[] = $day->format('M d');
                $dateStr = $day->toDateString();
                $visits[] = (int) VisitLog::where($visitScope)->whereDate('opened_at', $dateStr)->count();
                $views[] = (int) ProductViewLog::where($viewScope)->whereDate('viewed_at', $dateStr)->count();
                $downloads[] = (int) DownloadLog::where('user_id', $userId)->whereDate('downloaded_at', $dateStr)->count();
                $enquiries[] = (int) Enquiry::whereDate('created_at', $dateStr)->count();
            }
        } else {
            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            $labels = $months;
            $year = $now->year;
            for ($m = 1; $m <= 12; $m++) {
                $visits[] = (int) VisitLog::where($visitScope)->whereYear('opened_at', $year)->whereMonth('opened_at', $m)->count();
                $views[] = (int) ProductViewLog::where($viewScope)->whereYear('viewed_at', $year)->whereMonth('viewed_at', $m)->count();
                $downloads[] = (int) DownloadLog::where('user_id', $userId)->whereYear('downloaded_at', $year)->whereMonth('downloaded_at', $m)->count();
                $enquiries[] = (int) Enquiry::whereYear('created_at', $year)->whereMonth('created_at', $m)->count();
            }
        }

        return compact('labels', 'visits', 'views', 'downloads', 'enquiries');
    }
}
