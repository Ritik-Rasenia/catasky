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

    <section class="dash-hero-container mb-4">
        <div class="dash-hero-content">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge-workspace"><i class="bi bi-cpu-fill"></i> Subscriber Workspace</span>
                <span class="badge-status-live"><span class="pulse-dot"></span> Storefront: Active &amp; Live</span>
            </div>
            <h1>{{ $profile?->company_name ?? $user->name }} Catalog Command Center</h1>
            <p class="hero-desc">Configure catalog readiness, publish products, review sharing activity, and manage your custom B2B storefront hub.</p>
            
           
        </div>

        <div class="dash-hero-actions-panel mt-4 mt-lg-0">
            <div class="actions-wrapper">
                <div class="action-label"><i class="bi bi-sliders"></i> Workspace Controls</div>
                
                <form method="GET" id="filterForm" class="filter-action-form m-0">
                    <div class="filter-select-wrapper">
                        <i class="bi bi-funnel-fill select-icon"></i>
                        <select name="filter" class="form-select workspace-select" onchange="this.form.submit()">
                            <option value="all_time" {{ $currentFilter === 'all_time' ? 'selected' : '' }}>All Time Stats</option>
                            <option value="today" {{ $currentFilter === 'today' ? 'selected' : '' }}>Today's Activity</option>
                            <option value="yesterday" {{ $currentFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="this_week" {{ $currentFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="last_30_days" {{ $currentFilter === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="this_month" {{ $currentFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ $currentFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="this_year" {{ $currentFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                </form>

                <a href="{{ route('subscriber.products.create') }}" class="btn btn-workspace-primary btn-add-product">
                    <i class="bi bi-plus-circle-fill"></i> Add Product
                </a>

                @can('manage-subscriber-products')
                <div class="d-flex gap-2">
                    <a href="{{ route('subscriber.products.import') }}" class="btn btn-workspace-light flex-grow-1">
                        <i class="bi bi-file-earmark-arrow-up text-primary"></i> Import
                    </a>
                    <a href="{{ route('subscriber.products.export') }}" class="btn btn-workspace-light flex-grow-1">
                        <i class="bi bi-file-earmark-spreadsheet text-success"></i> Export
                    </a>
                </div>
                @endcan

                <a href="{{ route('subscriber.profile.edit') }}" class="btn btn-workspace-secondary btn-go-store">
                    <i class="bi bi-shop"></i> Go to Store <i class="bi bi-chevron-right ms-1 small-arrow" style="font-size:0.75rem;"></i>
                </a>
            </div>
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
                        <h3><i class="bi bi-clock-history"></i> Activity</h3>
                        <span>Latest workspace events</span>
                    </div>
                </div>
                <div class="activity-list">
                    @forelse($recentActivity as $act)
                        <div class="activity-item action-{{ strtolower($act->action ?? 'modified') }}">
                            <span></span>
                            <div class="activity-text">
                                <strong>{{ $act->description ?? 'Modified catalog specifications' }}</strong>
                                <small><i class="bi bi-clock me-1"></i>{{ $act->created_at?->diffForHumans() ?? 'Recently' }}</small>
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

    /* Redesigned Subscriber Workspace Hero Section */
    .dash-hero-container {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: stretch;
        gap: 28px;
        padding: 36px;
        background: linear-gradient(135deg, rgba(29, 111, 235, 0.09) 0%, rgba(124, 58, 237, 0.08) 100%), var(--surface-color, #fff);
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 20px;
        box-shadow: 0 10px 30px -15px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .dash-hero-container::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 80%);
        pointer-events: none;
    }
    
    .dash-hero-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .badge-workspace {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(37, 99, 235, 0.09);
        color: var(--primary-color);
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: 1px solid rgba(37, 99, 235, 0.15);
    }
    
    .badge-status-live {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(16, 185, 129, 0.09);
        color: #059669;
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: 1px solid rgba(16, 185, 129, 0.15);
    }
    
    .pulse-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse-active 1.8s infinite;
    }
    
    @keyframes pulse-active {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }
    
    .dash-hero-container h1 {
        margin: 12px 0 8px;
        color: var(--text-color, #0f172a);
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 32px;
        line-height: 1.15;
        letter-spacing: -0.02em !important;
    }
    
    .hero-desc {
        color: var(--text-muted);
        font-size: 14px;
        line-height: 1.6;
        max-width: 760px;
        margin-bottom: 24px;
    }
    
    /* Onboarding / Instruction Cards */
    .workspace-instructions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }
    
    .instruction-card {
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        padding: 12px 16px;
        border-radius: 14px;
        display: flex;
        gap: 12px;
        align-items: center;
        height: 100%;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .instruction-card:hover {
        transform: translateY(-3px);
        background: rgba(255, 255, 255, 0.85);
        border-color: rgba(37, 99, 235, 0.25);
        box-shadow: 0 10px 20px -10px rgba(37, 99, 235, 0.12);
    }
    
    .instruction-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }
    
    .icon-add {
        background: rgba(37, 99, 235, 0.08);
        color: var(--primary-color);
    }
    
    .icon-share {
        background: rgba(124, 58, 237, 0.08);
        color: var(--secondary-color);
    }
    
    .icon-profile {
        background: rgba(16, 185, 129, 0.08);
        color: #059669;
    }
    
    .instruction-text h6 {
        margin: 0 0 4px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-color);
    }
    
    .instruction-text p {
        margin: 0;
        font-size: 11px;
        line-height: 1.45;
        color: var(--text-muted);
    }
    
    /* Control Action Panel */
    .dash-hero-actions-panel {
        width: 280px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        flex-shrink: 0;
        border-left: 1px dashed rgba(100, 116, 139, 0.15);
        padding-left: 28px;
    }
    
    .actions-wrapper {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .action-label {
        font-size: 11px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .filter-action-form {
        margin: 0;
        width: 100%;
    }
    
    .filter-select-wrapper {
        position: relative;
        width: 100%;
    }
    
    .select-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 14px;
        pointer-events: none;
        z-index: 5;
    }
    
    .workspace-select {
        padding-left: 36px !important;
        background-color: var(--surface-color, #fff) !important;
        color: var(--text-color) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        height: 44px !important;
        border-radius: 12px !important;
        border: 1px solid var(--border-color, #e2e8f0) !important;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
    }
    
    .workspace-select:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
    }
    
    .btn-workspace-primary,
    .btn-workspace-secondary,
    .btn-workspace-light {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 44px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }
    
    .btn-workspace-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: #fff !important;
        border: none;
    }
    
    .btn-workspace-primary:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.25);
        opacity: 0.95;
    }
    
    .btn-workspace-secondary {
        background: transparent;
        color: var(--primary-color) !important;
        border: 1px solid var(--primary-color);
    }
    
    .btn-workspace-secondary:hover {
        background: rgba(37, 99, 235, 0.04);
        transform: translateY(-1.5px);
        box-shadow: 0 6px 15px rgba(37, 99, 235, 0.1);
    }
    
    .btn-workspace-secondary:hover .small-arrow {
        transform: translate(1.5px, -1.5px);
    }
    
    .small-arrow {
        transition: transform 0.2s ease;
    }
    
    .btn-workspace-light {
        background: var(--surface-muted, #f8fafc);
        color: var(--text-color) !important;
        border: 1px solid var(--border-color, #e2e8f0);
    }
    
    .btn-workspace-light:hover {
        background: var(--border-color, #e2e8f0);
        transform: translateY(-1.5px);
    }
    
    .dash-plan-card h2 {
        margin: 0;
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        letter-spacing: 0 !important;
    }

    .dash-plan-card p,
    .dash-card-header span,
    .dash-metric span,
    .dash-list-item small,
    .activity-item small {
        color: var(--text-muted);
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

    .activity-list {
        position: relative;
        padding: 16px 20px 8px !important;
    }

    .activity-list::before {
        content: '';
        position: absolute;
        top: 24px;
        left: 25px;
        bottom: 24px;
        width: 2px;
        background: linear-gradient(to bottom, var(--primary-color) 0%, rgba(37, 99, 235, 0.05) 100%);
        border-radius: 2px;
    }

    .activity-item {
        display: flex;
        gap: 16px;
        position: relative;
        padding-bottom: 20px;
        z-index: 2;
    }

    .activity-item:last-child {
        padding-bottom: 5px;
    }

    .activity-item > span {
        width: 12px;
        height: 12px;
        margin-top: 5px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid var(--primary-color);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        flex-shrink: 0;
        z-index: 3;
        transition: all 0.2s ease;
    }

    .activity-item:hover > span {
        background: var(--primary-color);
        transform: scale(1.15);
        box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.25);
    }

    .activity-item.action-created > span {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    }
    .activity-item.action-created:hover > span {
        background: #10b981;
        box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.25);
    }

    .activity-item.action-updated > span {
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
    }
    .activity-item.action-updated:hover > span {
        background: #f59e0b;
        box-shadow: 0 0 0 6px rgba(245, 158, 11, 0.25);
    }

    .activity-item.action-deleted > span {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
    }
    .activity-item.action-deleted:hover > span {
        background: #ef4444;
        box-shadow: 0 0 0 6px rgba(239, 68, 68, 0.25);
    }

    .activity-text {
        flex: 1;
    }

    .activity-item strong {
        color: var(--text-color, #1e293b);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
        margin-bottom: 2px;
        display: block;
    }

    .activity-item small {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: var(--text-muted, #64748b);
        font-size: 11px;
        font-weight: 500;
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

    html[data-theme="dark"] .dash-list-item,
    html[data-theme="dark"] .shortcut-stack a,
    html[data-theme="dark"] .icon-action {
        background: var(--surface-muted);
    }

    /* Redesigned Workspace Dark Mode Accents */
    html[data-theme="dark"] .dash-hero-container {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.16) 0%, rgba(124, 58, 237, 0.14) 100%), var(--surface-color);
        box-shadow: 0 10px 30px -15px rgba(0, 0, 0, 0.3);
        border-color: rgba(255, 255, 255, 0.05);
    }
    
    html[data-theme="dark"] .instruction-card {
        background: rgba(17, 24, 39, 0.45);
        border-color: rgba(255, 255, 255, 0.05);
    }
    
    html[data-theme="dark"] .instruction-card:hover {
        background: rgba(17, 24, 39, 0.65);
        border-color: rgba(37, 99, 235, 0.35);
        box-shadow: 0 10px 20px -10px rgba(0, 0, 0, 0.3);
    }

    @media (max-width: 1199.98px) {
        .dash-hero-container {
            flex-direction: column;
            padding: 28px;
        }
        .dash-hero-actions-panel {
            width: 100%;
            border-left: none;
            border-top: 1px dashed rgba(100, 116, 139, 0.15);
            padding-left: 0;
            padding-top: 24px;
            margin-top: 20px;
        }
        .actions-wrapper {
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-action-form, .btn-workspace-primary, .btn-workspace-secondary, .btn-workspace-light {
            flex: 1 1 200px;
        }

        .dash-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dash-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .workspace-instructions {
            grid-template-columns: 1fr;
        }
        .actions-wrapper {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-action-form, .btn-workspace-primary, .btn-workspace-secondary, .btn-workspace-light {
            flex: 1 1 auto;
            width: 100%;
        }

        .dash-plan-card {
            align-items: flex-start;
            flex-direction: column;
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
