@extends('subscriber-panel.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}"><i class="bi bi-house-door-fill me-1.5" style="font-size: 14px;"></i> Dashboard</a> &nbsp;/&nbsp; <span>Overview</span>
@endsection

@section('content')

{{-- Subscription Status Banner --}}
@if($subscription && $subscription->isExpired())
<div class="alert d-flex align-items-center gap-3 mb-4 animate-fade-in" style="background:rgba(239, 68, 68, 0.08); border:1px solid rgba(239, 68, 68, 0.16); border-radius:16px; padding:16px 20px; color:#ef4444;">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div class="flex-grow-1">
        <strong style="font-weight:700;">Subscription Expired!</strong> Your current subscription has expired. Please upgrade or renew your plan to continue displaying your B2B catalogue storefront.
    </div>
    <a href="{{ route('subscriber.subscription.plans') }}" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold">Renew Plan</a>
</div>
@endif

{{-- Overview Metric Grid --}}
<div class="row g-3 mb-4">
    <!-- Total Products -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card h-100 w-100" style="border-bottom: 3px solid var(--subscriber-primary) !important;">
            <div class="stat-icon" style="background: rgba(79, 70, 229, 0.08); color: var(--subscriber-primary);">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-label">Total Products</div>
                <div class="stat-value">{{ number_format($stats['total_products']) }}</div>
                <span class="text-muted" style="font-size: 0.75rem;">Items registered</span>
            </div>
        </div>
    </div>
    
    <!-- Active Products -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card h-100 w-100" style="border-bottom: 3px solid #10b981 !important;">
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-label">Active Products</div>
                <div class="stat-value" style="color: #10b981;">{{ number_format($stats['active_products']) }}</div>
                <span class="text-success" style="font-size: 0.75rem; font-weight:600;"><i class="bi bi-globe me-1"></i>Live on catalog</span>
            </div>
        </div>
    </div>
    
    <!-- Pending Products -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card h-100 w-100" style="border-bottom: 3px solid #f59e0b !important;">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.08); color: #f59e0b;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-label">Pending Approval</div>
                <div class="stat-value" style="color: #f59e0b;">{{ number_format($stats['pending_products']) }}</div>
                <span class="text-warning" style="font-size: 0.75rem; font-weight:600;"><i class="bi bi-shield-exclamation me-1"></i>Awaiting review</span>
            </div>
        </div>
    </div>
    
    <!-- Categories Count -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card h-100 w-100" style="border-bottom: 3px solid #06b6d4 !important;">
            <div class="stat-icon" style="background: rgba(6, 182, 212, 0.08); color: #06b6d4;">
                <i class="bi bi-tag-fill"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-label">Categories</div>
                <div class="stat-value" style="color: #06b6d4;">{{ number_format($stats['categories_count']) }}</div>
                <span class="text-muted" style="font-size: 0.75rem;">Represented sections</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Column: Subscription & Analytics -->
    <div class="col-lg-8">
        <!-- Subscription Status Overview Card -->
        <div class="vp-card mb-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); border:none; color: white;">
            <div class="vp-card-body p-4 position-relative overflow-hidden">
                <div class="position-absolute opacity-10 end-0 bottom-0 pointer-events-none" style="font-size: 8rem; transform: translate(10px, 30px);">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <div class="badge rounded-pill bg-primary bg-opacity-20 text-primary mb-2 px-3 py-1.5 fw-bold" style="font-size:0.75rem;">
                            <i class="bi bi-star-fill me-1"></i> B2B Subscription Workspace
                        </div>
                        <h4 class="fw-bold mb-1 text-white" style="font-family:'Outfit', sans-serif;">
                            @if($subscription)
                                Plan: {{ $subscription->plan?->name ?? 'Enterprise Pro' }}
                            @else
                                Plan Status: No Active Subscription
                            @endif
                        </h4>
                        <p class="opacity-75 small mb-3 mb-md-0 text-white-50">
                            @if($subscription)
                                Your subscription billing is active. You have full catalog editor privileges.
                            @else
                                Upgrade to a professional plan to instantly list and share custom products.
                            @endif
                        </p>
                    </div>
                    <div class="col-md-5 text-md-end">
                        @if($subscription)
                            <div class="d-inline-block text-start text-md-end bg-white bg-opacity-10 p-3 rounded-4" style="border: 1px solid rgba(255,255,255,0.08);">
                                <div class="text-white-50 small text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing:0.04em;">Days Remaining</div>
                                <div class="h2 fw-extrabold mb-0 brand-font text-warning mt-1" style="font-weight: 800;">{{ $subscription->daysRemaining() }} Days</div>
                                <div class="text-white-50 extra-small mt-1">Ends: {{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y') : 'N/A' }}</div>
                            </div>
                        @else
                            <a href="{{ route('subscriber.subscription.plans') }}" class="btn btn-warning rounded-pill px-4 fw-bold text-dark ">
                                <i class="bi bi-arrow-up-circle-fill me-1"></i> Upgrade Catalogue
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Views Line Chart -->
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-graph-up me-2 text-primary"></i>Storefront View Analytics</h6>
            </div>
            <div class="vp-card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="productViewsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Recent Notifications & Quick Links -->
    <div class="col-lg-4">
        <!-- Recent Notifications -->
        <div class="vp-card mb-4">
            <div class="vp-card-header d-flex justify-content-between align-items-center">
                <h6 class="vp-card-title"><i class="bi bi-bell me-2 text-warning"></i>Recent Notifications</h6>
                @if($stats['unread_notifications_count'] > 0)
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1 extra-small">{{ $stats['unread_notifications_count'] }} New</span>
                @endif
            </div>
            <div class="vp-card-body p-0">
                <div class="d-flex flex-column" style="max-height: 290px; overflow-y: auto;">
                    @forelse($recentNotifications as $notif)
                        @php
                            $notifData = $notif->data;
                            $icon = $notifData['icon'] ?? 'bi-info-circle';
                            $title = $notifData['title'] ?? 'Notification';
                            $msg = $notifData['message'] ?? '';
                            $redirectUrl = route('subscriber.notifications.redirect', $notif->id);
                        @endphp
                        <a href="{{ $redirectUrl }}" class="d-flex gap-3 p-3 border-bottom text-decoration-none hover-bg" style="transition:background 0.2s; border-color: var(--border) !important;">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px; height:38px; background: rgba(79, 70, 229, 0.06); color: var(--subscriber-primary);">
                                <i class="bi {{ $icon }}"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-bold text-dark small text-truncate d-flex align-items-center gap-2">
                                    {{ $title }}
                                    @if(is_null($notif->read_at))
                                        <span class="badge bg-danger rounded-circle p-1" style="width:6px; height:6px; content:'';"></span>
                                    @endif
                                </div>
                                <div class="text-muted extra-small text-truncate mt-0.5">{{ $msg }}</div>
                                <div class="text-muted mt-1" style="font-size: 0.65rem;">{{ $notif->created_at->diffForHumans() }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-5 text-muted empty-state" style="border: none !important; background: transparent !important;">
                            <i class="bi bi-bell-slash fs-1 text-muted opacity-25"></i>
                            <h6 class="fw-bold text-dark mt-2 mb-1">No New Notifications</h6>
                            <p class="text-muted extra-small mb-0">You are all caught up!</p>
                        </div>
                    @endforelse
                </div>
                <div class="p-3 border-top text-center bg-light bg-opacity-40" style="border-color: var(--border) !important;">
                    <a href="{{ route('subscriber.notifications.index') }}" class="extra-small fw-bold text-primary text-decoration-none">
                        View All Notifications <i class="bi bi-chevron-right ms-0.5"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Shortcuts -->
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-lightning-charge me-2 text-success"></i>Quick Shortcuts</h6>
            </div>
            <div class="vp-card-body p-3">
                <a href="{{ route('subscriber.products.create') }}" class="btn-quick-shortcut">
                    <span class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-plus-circle-fill text-primary" style="font-size: 16px;"></i>
                        <span>Add New Product</span>
                    </span>
                    <div class="shortcut-icon-wrap">
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </a>
                <a href="{{ route('subscriber.share.create') }}" class="btn-quick-shortcut" style="border-left-color: #10b981 !important;">
                    <span class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-share-fill text-success" style="font-size: 16px;"></i>
                        <span>Share Catalog Link</span>
                    </span>
                    <div class="shortcut-icon-wrap" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </a>
                <a href="{{ route('subscriber.attributes.index') }}" class="btn-quick-shortcut" style="border-left-color: #f59e0b !important;">
                    <span class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-sliders2 text-warning" style="font-size: 16px;"></i>
                        <span>Manage Specifications</span>
                    </span>
                    <div class="shortcut-icon-wrap" style="background: rgba(245, 158, 11, 0.08); color: #f59e0b;">
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Products -->
    <div class="col-xl-8">
        <div class="vp-card">
            <div class="vp-card-header d-flex justify-content-between align-items-center">
                <h6 class="vp-card-title"><i class="bi bi-box-seam me-2 text-primary"></i>Recent Products</h6>
                <a href="{{ route('subscriber.products.create') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold extra-small" style="min-height: auto; height: 30px; padding: 4px 12px !important;"><i class="bi bi-plus-lg me-1"></i>Add Product</a>
            </div>
            <div class="vp-card-body p-0">
                <div class="table-responsive" style="border: none !important; box-shadow: none !important; margin: 0 !important;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 border-0 small text-muted">Thumbnail</th>
                                <th class="border-0 small text-muted">Product Name</th>
                                <th class="border-0 small text-muted">SKU</th>
                                <th class="border-0 small text-muted">Price</th>
                                <th class="border-0 small text-muted">Status</th>
                                <th class="text-end pe-3 border-0 small text-muted">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentProducts as $prod)
                                <tr>
                                    <td class="ps-3">
                                        @if($prod->thumbnail)
                                            <img src="{{ $prod->thumbnail_url }}" alt="" class="rounded-3 border" style="width:40px; height:40px; object-fit:cover;">
                                        @else
                                            <div class="rounded-3 bg-light text-muted d-flex align-items-center justify-content-center fw-bold" style="width:40px; height:40px; font-size: 0.95rem;">📦</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark text-truncate small" style="max-width:200px;">{{ $prod->name }}</div>
                                        <span class="text-muted extra-small">{{ $prod->category?->name ?? 'No Category' }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $prod->sku ?? '-' }}</td>
                                    <td>
                                        @if($prod->offer_price)
                                            <span class="fw-bold text-dark small">₹{{ number_format($prod->offer_price, 2) }}</span>
                                        @elseif($prod->mrp)
                                            <span class="fw-bold text-dark small">₹{{ number_format($prod->mrp, 2) }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($prod->status === 'active')
                                            <span class="badge rounded-pill bg-success-soft text-success px-2.5 py-1 extra-small">Active</span>
                                        @elseif($prod->status === 'draft')
                                            <span class="badge rounded-pill bg-warning-soft text-warning px-2.5 py-1 extra-small">Draft</span>
                                        @else
                                            <span class="badge rounded-pill bg-danger-soft text-danger px-2.5 py-1 extra-small">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('subscriber.products.edit', $prod->id) }}" class="btn btn-white btn-sm" title="Edit Product">
                                            <i class="bi bi-pencil text-primary"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted empty-state" style="border: none !important; background: transparent !important;">
                                        <i class="bi bi-box-seam fs-1 opacity-25"></i>
                                        <h6 class="fw-bold text-dark mt-2 mb-1">No Products Registered</h6>
                                        <p class="text-muted extra-small mb-3">Add items to start sharing catalogue links.</p>
                                        <a href="{{ route('subscriber.products.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Create Product</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Activity Logs -->
    <div class="col-xl-4">
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-clock-history me-2 text-warning"></i>Activity Timeline</h6>
            </div>
            <div class="vp-card-body">
                <div class="d-flex flex-column gap-3" style="max-height: 380px; overflow-y: auto; padding-left: 4px;">
                    @forelse($recentActivity as $act)
                        <div class="d-flex align-items-start gap-3 position-relative">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; flex-shrink:0;">
                                <i class="bi bi-check-lg" style="font-size:0.75rem;"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-dark small fw-bold text-truncate">{{ $act->action ?? 'Modified item' }}</div>
                                <span class="text-muted mt-0.5 d-block" style="font-size:0.7rem;">{{ $act->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted extra-small">
                            <i class="bi bi-clock-history d-block fs-3 mb-2 opacity-50"></i>
                            No activities logged yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const theme = document.documentElement.getAttribute('data-theme') || 'light';
        const isDark = theme === 'dark';
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? '#243041' : '#f1f5f9';

        const viewsCtx = document.getElementById('productViewsChart').getContext('2d');
        const viewsGradient = viewsCtx.createLinearGradient(0, 0, 0, 300);
        viewsGradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
        viewsGradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

        const viewsChart = new Chart(viewsCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dashboardCharts['monthlyViews']['labels']) !!},
                datasets: [{
                    label: 'Catalogue Views',
                    data: {!! json_encode($dashboardCharts['monthlyViews']['data']) !!},
                    borderColor: '#4f46e5',
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: viewsGradient,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4f46e5',
                    pointRadius: 4,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { color: textColor, font: { family: 'Poppins', size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { family: 'Poppins', size: 11 } }
                    }
                }
            }
        });

        window.addEventListener('themeChanged', function() {
            const nextTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const isDarkNext = nextTheme === 'dark';
            const nextColor = isDarkNext ? '#94a3b8' : '#64748b';
            const nextGrid = isDarkNext ? '#243041' : '#f1f5f9';

            if (viewsChart) {
                viewsChart.options.scales.y.ticks.color = nextColor;
                viewsChart.options.scales.y.grid.color = nextGrid;
                viewsChart.options.scales.x.ticks.color = nextColor;
                viewsChart.update();
            }
        });
    });
</script>
@endpush
