@extends('subscriber-panel.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}"><i class="bi bi-house-door-fill me-1"></i> Dashboard</a>
    <i class="bi bi-chevron-right text-muted mx-2" style="font-size: 10px;"></i>
    <span>Overview</span>
@endsection

@php
    $daysRemaining = $subscription ? max((int) $subscription->daysRemaining(), 0) : 0;
    $planName = $subscription?->plan?->name ?? 'No active plan';
    $planEnds = $subscription?->ends_at ? $subscription->ends_at->format('M d, Y') : 'Not scheduled';
    $activePercent = $stats['total_products'] > 0 ? round(($stats['active_products'] / $stats['total_products']) * 100) : 0;

    $filterDescriptions = [
        'all_time' => 'All time overview',
        'today' => 'Today\'s hourly activity',
        'yesterday' => 'Yesterday\'s hourly activity',
        'this_week' => 'Weekly daily breakdown',
        'last_30_days' => 'Last 30 days activity',
        'this_month' => 'This month\'s daily breakdown',
        'last_month' => 'Last month\'s daily breakdown',
        'this_year' => 'This year\'s monthly breakdown',
    ];
    $filterDesc = $filterDescriptions[$currentFilter] ?? 'Overview';
@endphp

@section('content')
<div class="subscriber-dashboard">
    @if($subscription && $subscription->isExpired())
        <div class="dash-alert dash-alert-danger">
            <div class="dash-alert-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div>
                <strong>Subscription expired</strong>
                <span>Renew your plan to continue displaying your B2B catalogue storefront.</span>
            </div>
            <a href="{{ route('subscriber.subscription.plans') }}" class="btn btn-danger btn-sm">Renew Plan</a>
        </div>
    @endif

    <section class="dash-hero">
        <div>
            <div class="dash-kicker">Subscriber workspace</div>
            <h1>{{ $profile?->company_name ?? $user->name }} catalog command center</h1>
            <p>Track catalog readiness, publish products, review sharing activity, and keep your storefront moving.</p>
        </div>
        <div class="dash-hero-actions d-flex align-items-center gap-2">
            <form method="GET" id="filterForm" class="d-inline-block m-0">
                <select name="filter" class="form-select form-select-sm border shadow-sm px-3" onchange="this.form.submit()" style="background-color: var(--surface-color, #fff); color: var(--text-primary); font-size: 0.85rem; height: 38px; border-radius: 10px; border: 1px solid var(--border) !important; min-width: 140px; cursor: pointer;">
                    <option value="all_time" {{ $currentFilter === 'all_time' ? 'selected' : '' }}>All Time</option>
                    <option value="today" {{ $currentFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $currentFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $currentFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="last_30_days" {{ $currentFilter === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="this_month" {{ $currentFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $currentFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $currentFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                </select>
            </form>
            <a href="{{ route('subscriber.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Product</a>
            <a href="{{ route('subscriber.share.create') }}" class="btn btn-light"><i class="bi bi-share"></i> Create Share</a>
        </div>
    </section>

    <section class="dash-metrics">
        <article class="dash-metric">
            <span class="metric-icon metric-blue"><i class="bi bi-box-seam"></i></span>
            <div>
                <small>Total Products</small>
                <strong>{{ number_format($stats['total_products']) }}</strong>
                <span>Items registered</span>
            </div>
        </article>
        <article class="dash-metric">
            <span class="metric-icon metric-green"><i class="bi bi-check2-circle"></i></span>
            <div>
                <small>Active Products</small>
                <strong>{{ number_format($stats['active_products']) }}</strong>
                <span>{{ $activePercent }}% catalog ready</span>
            </div>
        </article>
        <article class="dash-metric">
            <span class="metric-icon metric-amber"><i class="bi bi-hourglass-split"></i></span>
            <div>
                <small>Pending Approval</small>
                <strong>{{ number_format($stats['pending_products']) }}</strong>
                <span>Awaiting review</span>
            </div>
        </article>
        <article class="dash-metric">
            <span class="metric-icon metric-cyan"><i class="bi bi-eye"></i></span>
            <div>
                <small>Total Views</small>
                <strong>{{ number_format($stats['total_views']) }}</strong>
                <span>{{ number_format($stats['total_shares']) }} share links</span>
            </div>
        </article>
    </section>

    <section class="dash-grid">
        <div class="dash-main">
            <div class="dash-plan-card">
                <div>
                    <div class="dash-kicker">Plan health</div>
                    <h2>{{ $planName }}</h2>
                    <p>
                        @if($subscription)
                            Active billing with catalog editor privileges. Plan ends on {{ $planEnds }}.
                        @else
                            Choose a subscription plan to unlock publishing and share workflows.
                        @endif
                    </p>
                </div>
                <div class="plan-ring">
                    <strong>{{ $subscription ? $daysRemaining : 0 }}</strong>
                    <span>days left</span>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <h3><i class="bi bi-graph-up-arrow"></i> Storefront Views</h3>
                        <span>Catalog engagement — {{ $filterDesc }}</span>
                    </div>
                    <span class="dash-chip">{{ number_format($stats['total_downloads']) }} downloads</span>
                </div>
                <div class="chart-container">
                    <canvas id="productViewsChart"></canvas>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <h3><i class="bi bi-box"></i> Recent Products</h3>
                        <span>Latest catalog entries</span>
                    </div>
                    <a href="{{ route('subscriber.products.create') }}" class="btn btn-light btn-sm"><i class="bi bi-plus-lg"></i> Add</a>
                </div>
                <div class="table-responsive dash-table-wrap">
                    <table class="table align-middle mb-0 dash-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentProducts as $prod)
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            @if($prod->thumbnail)
                                                <img src="{{ $prod->thumbnail_url }}" alt="{{ $prod->name }}">
                                            @else
                                                <span><i class="bi bi-box-seam"></i></span>
                                            @endif
                                            <div>
                                                <strong>{{ $prod->name }}</strong>
                                                <small>{{ $prod->category?->name ?? 'No category' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $prod->sku ?? '-' }}</td>
                                    <td>
                                        @if($prod->offer_price)
                                            Rs {{ number_format($prod->offer_price, 2) }}
                                        @elseif($prod->mrp)
                                            Rs {{ number_format($prod->mrp, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td><span class="dash-status status-{{ $prod->status ?? 'draft' }}">{{ ucfirst($prod->status ?? 'draft') }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('subscriber.products.edit', $prod->id) }}" class="icon-action" title="Edit product"><i class="bi bi-pencil"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="dash-empty">
                                            <i class="bi bi-box-seam"></i>
                                            <strong>No products registered</strong>
                                            <span>Add products to start sharing catalog links.</span>
                                            <a href="{{ route('subscriber.products.create') }}" class="btn btn-primary btn-sm">Create Product</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside class="dash-side">
            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <h3><i class="bi bi-bell"></i> Notifications</h3>
                        <span>{{ $stats['unread_notifications_count'] }} unread</span>
                    </div>
                </div>
                <div class="dash-list">
                    @forelse($recentNotifications as $notif)
                        @php
                            $notifData = $notif->data;
                            $icon = $notifData['icon'] ?? 'bi-info-circle';
                            $title = $notifData['title'] ?? 'Notification';
                            $msg = $notifData['message'] ?? '';
                        @endphp
                        <a href="{{ route('subscriber.notifications.redirect', $notif->id) }}" class="dash-list-item">
                            <span class="list-icon"><i class="bi {{ $icon }}"></i></span>
                            <span>
                                <strong>{{ $title }}</strong>
                                <small>{{ Str::limit($msg, 54) }}</small>
                                <em>{{ $notif->created_at?->diffForHumans() ?? 'Recently' }}</em>
                            </span>
                        </a>
                    @empty
                        <div class="dash-empty compact">
                            <i class="bi bi-bell-slash"></i>
                            <strong>No new notifications</strong>
                            <span>You are all caught up.</span>
                        </div>
                    @endforelse
                </div>
                <a href="{{ route('subscriber.notifications.index') }}" class="dash-footer-link">View all notifications <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <h3><i class="bi bi-lightning-charge"></i> Shortcuts</h3>
                        <span>Common actions</span>
                    </div>
                </div>
                <div class="shortcut-stack">
                    <a href="{{ route('subscriber.products.create') }}"><i class="bi bi-plus-circle"></i><span>Add New Product</span><i class="bi bi-chevron-right"></i></a>
                    <a href="{{ route('subscriber.share.create') }}"><i class="bi bi-share"></i><span>Share Catalog Link</span><i class="bi bi-chevron-right"></i></a>
                    <a href="{{ route('subscriber.attributes.index') }}"><i class="bi bi-sliders2"></i><span>Manage Specifications</span><i class="bi bi-chevron-right"></i></a>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <h3><i class="bi bi-link-45deg"></i> Top Share Links</h3>
                        <span>Most viewed links</span>
                    </div>
                </div>
                <div class="dash-list">
                    @forelse($topShareLinks as $link)
                        <div class="dash-list-item static">
                            <span class="list-icon"><i class="bi bi-bar-chart"></i></span>
                            <span>
                                <strong>{{ $link->title ?? $link->product?->name ?? 'Catalog share' }}</strong>
                                <small>{{ number_format($link->view_count) }} views</small>
                            </span>
                        </div>
                    @empty
                        <div class="dash-empty compact">
                            <i class="bi bi-link"></i>
                            <strong>No share links yet</strong>
                            <span>Create a link to track engagement.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <h3><i class="bi bi-clock-history"></i> Activity</h3>
                        <span>Latest workspace events</span>
                    </div>
                </div>
                <div class="activity-list">
                    @forelse($recentActivity as $act)
                        <div class="activity-item">
                            <span></span>
                            <div>
                                <strong>{{ $act->action ?? 'Modified item' }}</strong>
                                <small>{{ $act->created_at?->diffForHumans() ?? 'Recently' }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="dash-empty compact">
                            <i class="bi bi-clock"></i>
                            <strong>No activity yet</strong>
                            <span>Events will appear here.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </aside>
    </section>
</div>
@endsection

@push('css')
<style>
    .subscriber-dashboard {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .dash-alert,
    .dash-hero,
    .dash-card,
    .dash-metric,
    .dash-plan-card {
        background: var(--surface-color, #fff);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow);
    }

    .dash-alert {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
    }

    .dash-alert-danger {
        border-color: rgba(239, 68, 68, 0.18);
        background: rgba(239, 68, 68, 0.06);
    }

    .dash-alert-icon,
    .metric-icon,
    .list-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .dash-alert-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        color: #dc2626;
        background: rgba(239, 68, 68, 0.1);
    }

    .dash-alert div:nth-child(2) {
        flex: 1;
    }

    .dash-alert strong,
    .dash-alert span {
        display: block;
    }

    .dash-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 28px;
        background: linear-gradient(135deg, rgba(29, 111, 235, 0.08), rgba(16, 185, 129, 0.07)), var(--surface-color, #fff);
    }

    .dash-kicker {
        color: var(--primary-color);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 8px;
    }

    .dash-hero h1,
    .dash-plan-card h2 {
        margin: 0;
        color: var(--text-primary);
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        letter-spacing: 0 !important;
    }

    .dash-hero h1 {
        font-size: 30px;
        line-height: 1.15;
    }

    .dash-hero p,
    .dash-plan-card p,
    .dash-card-header span,
    .dash-metric span,
    .dash-list-item small,
    .activity-item small {
        color: var(--text-muted);
    }

    .dash-hero p {
        max-width: 720px;
        margin: 10px 0 0;
        font-size: 14px;
    }

    .dash-hero-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .dash-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .dash-metric {
        display: flex;
        gap: 14px;
        padding: 18px;
        min-height: 112px;
    }

    .metric-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        font-size: 20px;
    }

    .metric-blue { color: #2563eb; background: rgba(37, 99, 235, 0.1); }
    .metric-green { color: #059669; background: rgba(16, 185, 129, 0.12); }
    .metric-amber { color: #d97706; background: rgba(245, 158, 11, 0.12); }
    .metric-cyan { color: #0891b2; background: rgba(6, 182, 212, 0.12); }

    .dash-metric small {
        display: block;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .dash-metric strong {
        display: block;
        color: var(--text-primary);
        font-family: 'Outfit', sans-serif;
        font-size: 28px;
        line-height: 1.1;
        margin: 5px 0;
    }

    .dash-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 24px;
        align-items: start;
    }

    .dash-main,
    .dash-side {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .dash-plan-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 24px;
        background: #0f172a;
        border-color: #0f172a;
        overflow: hidden;
    }

    .dash-plan-card h2,
    .dash-plan-card p {
        color: #fff;
    }

    .dash-plan-card p {
        opacity: 0.72;
        margin: 8px 0 0;
    }

    .plan-ring {
        width: 126px;
        height: 126px;
        border-radius: 50%;
        border: 10px solid rgba(255, 255, 255, 0.14);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: #fff;
        background: rgba(255, 255, 255, 0.08);
        flex-shrink: 0;
    }

    .plan-ring strong {
        font-family: 'Outfit', sans-serif;
        font-size: 34px;
        line-height: 1;
    }

    .plan-ring span {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.68);
        text-transform: uppercase;
        font-weight: 700;
    }

    .dash-card {
        overflow: hidden;
    }

    .dash-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
    }

    .dash-card-header h3 {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: var(--text-primary);
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: 0 !important;
    }

    .dash-card-header span {
        display: block;
        margin-top: 3px;
        font-size: 12px;
    }

    .dash-chip {
        border: 1px solid rgba(37, 99, 235, 0.16);
        background: rgba(37, 99, 235, 0.08);
        color: #2563eb !important;
        border-radius: 999px;
        padding: 6px 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .chart-container {
        height: 320px;
        padding: 18px 20px 20px;
    }

    .dash-table-wrap {
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .product-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 220px;
    }

    .product-cell img,
    .product-cell > span {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: var(--surface-muted, #f8fafc);
    }

    .product-cell img {
        object-fit: cover;
    }

    .product-cell > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
    }

    .product-cell strong,
    .product-cell small {
        display: block;
    }

    .product-cell strong {
        color: var(--text-primary);
        max-width: 280px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dash-status {
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 800;
    }

    .status-active { color: #047857; background: rgba(16, 185, 129, 0.12); }
    .status-draft { color: #b45309; background: rgba(245, 158, 11, 0.14); }
    .status-inactive { color: #dc2626; background: rgba(239, 68, 68, 0.12); }

    .icon-action {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--surface-color, #fff);
        color: var(--primary-color);
    }

    .dash-list,
    .shortcut-stack,
    .activity-list {
        padding: 14px;
    }

    .dash-list-item,
    .shortcut-stack a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 12px;
        color: var(--text-primary);
        text-decoration: none;
        margin-bottom: 10px;
        background: var(--surface-color, #fff);
    }

    .dash-list-item:hover,
    .shortcut-stack a:hover {
        border-color: rgba(37, 99, 235, 0.24);
        background: rgba(37, 99, 235, 0.03);
    }

    .dash-list-item.static:hover {
        background: var(--surface-color, #fff);
    }

    .list-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: rgba(37, 99, 235, 0.08);
        color: var(--primary-color);
    }

    .dash-list-item strong,
    .dash-list-item small,
    .dash-list-item em {
        display: block;
    }

    .dash-list-item strong {
        color: var(--text-primary);
        line-height: 1.25;
    }

    .dash-list-item em {
        color: var(--text-muted);
        font-size: 11px;
        font-style: normal;
        margin-top: 4px;
    }

    .dash-footer-link {
        display: flex;
        justify-content: center;
        gap: 8px;
        padding: 13px;
        border-top: 1px solid var(--border);
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 800;
    }

    .shortcut-stack a {
        justify-content: space-between;
        font-weight: 800;
    }

    .shortcut-stack a i:first-child {
        color: var(--primary-color);
    }

    .shortcut-stack a span {
        flex: 1;
    }

    .activity-item {
        display: flex;
        gap: 12px;
        padding: 0 0 18px;
    }

    .activity-item > span {
        width: 12px;
        height: 12px;
        margin-top: 4px;
        border-radius: 50%;
        background: var(--primary-color);
        box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.12);
        flex-shrink: 0;
    }

    .activity-item strong,
    .activity-item small {
        display: block;
    }

    .dash-empty {
        min-height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 8px;
        text-align: center;
        color: var(--text-muted);
        padding: 24px;
    }

    .dash-empty.compact {
        min-height: 150px;
    }

    .dash-empty i {
        font-size: 34px;
        opacity: 0.45;
    }

    .dash-empty strong {
        color: var(--text-primary);
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
    }

    html[data-theme="dark"] .dash-hero {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.16), rgba(16, 185, 129, 0.12)), var(--surface-color);
    }

    html[data-theme="dark"] .dash-list-item,
    html[data-theme="dark"] .shortcut-stack a,
    html[data-theme="dark"] .icon-action {
        background: var(--surface-muted);
    }

    @media (max-width: 1199.98px) {
        .dash-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dash-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .dash-hero,
        .dash-plan-card {
            align-items: flex-start;
            flex-direction: column;
        }

        .dash-hero h1 {
            font-size: 24px;
        }

        .dash-hero-actions {
            width: 100%;
            justify-content: stretch;
        }

        .dash-hero-actions .btn {
            flex: 1 1 150px;
        }

        .dash-metrics {
            grid-template-columns: 1fr;
        }

        .chart-container {
            height: 260px;
        }
    }
</style>
@endpush

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('productViewsChart');
        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        const theme = document.documentElement.getAttribute('data-theme') || 'light';
        const textColor = theme === 'dark' ? '#94a3b8' : '#64748b';
        const gridColor = theme === 'dark' ? '#243041' : '#e2e8f0';
        const viewsCtx = canvas.getContext('2d');
        const viewsGradient = viewsCtx.createLinearGradient(0, 0, 0, 300);
        viewsGradient.addColorStop(0, 'rgba(37, 99, 235, 0.24)');
        viewsGradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

        // Real data from database — last 7 days of catalogue views
        const chartLabels = {!! json_encode($dashboardCharts['monthlyViews']['labels']) !!};
        const chartData   = {!! json_encode($dashboardCharts['monthlyViews']['data']) !!};

        const viewsChart = new Chart(viewsCtx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Catalogue Views',
                    data: chartData,
                    borderColor: '#2563eb',
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: viewsGradient,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 3,
                    pointRadius: 4,
                    tension: 0.36
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.parsed.y + ' views';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        border: { display: false },
                        ticks: {
                            color: textColor,
                            font: { family: 'Poppins', size: 11 },
                            precision: 0
                        }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: textColor, font: { family: 'Poppins', size: 11 } }
                    }
                }
            }
        });

        window.addEventListener('themeChanged', function() {
            const nextTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const nextColor = nextTheme === 'dark' ? '#94a3b8' : '#64748b';
            const nextGrid  = nextTheme === 'dark' ? '#243041' : '#e2e8f0';
            viewsChart.options.scales.y.ticks.color = nextColor;
            viewsChart.options.scales.y.grid.color  = nextGrid;
            viewsChart.options.scales.x.ticks.color = nextColor;
            viewsChart.update();
        });
    });
</script>
@endpush
