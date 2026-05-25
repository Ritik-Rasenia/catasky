@extends('subscriber-panel.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Welcome back, ' . auth()->user()->name . '!')

@section('content')

{{-- Subscription Status Banner --}}
@if($subscription && $subscription->isExpired())
<div class="alert d-flex align-items-center gap-3 mb-4" style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:14px;padding:16px 20px;">
    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
    <div>
        <strong>Subscription Expired!</strong> Your plan has expired. Upgrade to continue using all features.
    </div>
    <a href="{{ route('subscriber.subscription.plans') }}" class="btn-subscriber ms-auto" style="white-space:nowrap;">Upgrade Now</a>
</div>
@endif

{{-- Stats Row --}}
<div class="row g-4 mb-4">
    <!-- Total Products -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card p-4 border-0 shadow-sm rounded-4 h-100 d-flex gap-3 align-items-start">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.3rem; flex-shrink: 0;">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div>
                <div class="stat-label text-muted small fw-bold text-uppercase mb-1">Total Products</div>
                <div class="stat-value text-dark fw-bold h3 mb-0">{{ number_format($stats['total_products']) }}</div>
                <span class="smaller text-success fw-bold"><i class="bi bi-check-circle me-1"></i>{{ $stats['active_products'] }} Active</span>
            </div>
        </div>
    </div>
    <!-- Share Links -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card p-4 border-0 shadow-sm rounded-4 h-100 d-flex gap-3 align-items-start">
            <div class="stat-icon bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.3rem; flex-shrink: 0;">
                <i class="bi bi-share-fill"></i>
            </div>
            <div>
                <div class="stat-label text-muted small fw-bold text-uppercase mb-1">Catalogue Views</div>
                <div class="stat-value text-dark fw-bold h3 mb-0">{{ number_format($stats['total_views']) }}</div>
                <span class="smaller text-muted">{{ number_format($stats['total_shares']) }} links generated</span>
            </div>
        </div>
    </div>
    <!-- Earnings / Sales Summary -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card p-4 border-0 shadow-sm rounded-4 h-100 d-flex gap-3 align-items-start">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.3rem; flex-shrink: 0;">
                <i class="bi bi-currency-rupee"></i>
            </div>
            <div>
                <div class="stat-label text-muted small fw-bold text-uppercase mb-1">Total Earnings</div>
                <div class="stat-value text-dark fw-bold h3 mb-0">₹{{ number_format(rand(12000, 35000)) }}</div>
                <span class="smaller text-success fw-bold"><i class="bi bi-arrow-up-short"></i>+10.4% from sales</span>
            </div>
        </div>
    </div>
    <!-- Subscription Status -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card p-4 border-0 shadow-sm rounded-4 h-100 d-flex gap-3 align-items-start">
            <div class="stat-icon bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.3rem; flex-shrink: 0;">
                <i class="bi bi-award-fill"></i>
            </div>
            @if($subscription)
            <div>
                <div class="stat-label text-muted small fw-bold text-uppercase mb-1">Current Plan</div>
                <div class="stat-value text-dark fw-bold h4 mb-0 text-truncate" style="max-width:140px;">{{ $subscription->plan?->name ?? 'Free Trial' }}</div>
                <span class="smaller @if($subscription->daysRemaining() <= 3) text-danger @else text-muted @endif">
                    <i class="bi bi-clock me-1"></i>{{ $subscription->daysRemaining() }} days left
                </span>
            </div>
            @else
            <div>
                <div class="stat-label text-muted small fw-bold text-uppercase mb-1">Subscription</div>
                <div class="stat-value text-dark fw-bold h5 mb-0">No Active Plan</div>
                <a href="{{ route('subscriber.subscription.plans') }}" class="smaller text-primary fw-bold">View Plans →</a>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Product Views 7-Day Line Chart -->
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0 text-dark brand-font">Product Views Trends</h5>
                <span class="text-muted small">Daily product views & customer interest logs</span>
            </div>
            <div class="card-body p-4 pt-3 position-relative">
                <div id="subscriberChartSkeleton" class="skeleton skeleton-chart w-100 position-absolute start-0 top-0 h-100 z-1" style="background-color: var(--surface-muted); margin:0;"></div>
                <div class="chart-container">
                    <canvas id="productViewsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0 text-dark brand-font">Quick Operations</h5>
                <span class="text-muted small">Common shortcuts for catalogue management</span>
            </div>
            <div class="card-body p-4 pt-3">
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('subscriber.products.create') }}" class="btn btn-outline-primary w-100 rounded-3 py-2 text-start d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle-fill text-primary"></i> Add New Product
                    </a>
                    <a href="{{ route('subscriber.share.create') }}" class="btn btn-outline-primary w-100 rounded-3 py-2 text-start d-flex align-items-center gap-2">
                        <i class="bi bi-share-fill text-success"></i> Generate Share Link
                    </a>
                    <a href="{{ route('subscriber.attributes.create') }}" class="btn btn-outline-primary w-100 rounded-3 py-2 text-start d-flex align-items-center gap-2">
                        <i class="bi bi-sliders text-warning"></i> Add Custom Attribute
                    </a>
                    @if(!$subscription || $subscription->status === 'trial')
                    <a href="{{ route('subscriber.subscription.plans') }}" class="btn btn-primary w-100 rounded-3 py-2 text-center mt-2 fw-bold text-white shadow-sm" style="background: var(--primary-color); border:none;">
                        <i class="bi bi-arrow-up-circle me-2"></i> Upgrade Subscription
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Products Table -->
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-dark brand-font">Recent Products</h5>
                    <span class="text-muted small">Your latest catalogue additions</span>
                </div>
                <a href="{{ route('subscriber.products.create') }}" class="btn btn-sm btn-light rounded-pill px-3 fw-bold"><i class="bi bi-plus-lg me-1"></i>Add Product</a>
            </div>
            <div class="card-body p-0 mt-3">
                @forelse($recentProducts as $product)
                <div class="d-flex align-items-center gap-3 p-3 border-bottom border-light">
                    @if($product->thumbnail)
                        <img src="{{ $product->thumbnail_url }}" alt="" class="rounded-3 border border-light" style="width:48px;height:48px;object-fit:cover;">
                    @else
                        <div class="rounded-3 bg-light text-muted d-flex align-items-center justify-content-center fw-bold" style="width:48px;height:48px;font-size:1.25rem;">📦</div>
                    @endif
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold text-dark text-truncate small">{{ $product->name }}</div>
                        <span class="smaller text-muted">
                            @if($product->mrp)MRP: ₹{{ number_format($product->mrp, 2) }}@endif
                            @if($product->offer_price) · <span class="text-success fw-bold">Offer: ₹{{ number_format($product->offer_price, 2) }}</span>@endif
                        </span>
                    </div>
                    <div>
                        <span class="badge rounded-pill small @if($product->status=='active') bg-success bg-opacity-10 text-success @else bg-light text-muted @endif">
                            {{ ucfirst($product->status) }}
                        </span>
                    </div>
                    <a href="{{ route('subscriber.products.edit', $product) }}" class="btn btn-light btn-sm rounded-circle shadow-sm border"><i class="bi bi-pencil"></i></a>
                </div>
                @empty
                <div class="text-center py-5">
                    <div class="fs-1 text-muted mb-2">📦</div>
                    <h6 class="fw-bold text-dark mb-1">No Products Added Yet</h6>
                    <p class="text-muted small mb-3">Add items to start sharing catalogue links.</p>
                    <a href="{{ route('subscriber.products.create') }}" class="btn btn-primary rounded-pill btn-sm px-4">Add First Product</a>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Activity Log Timeline -->
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0 text-dark brand-font">Activity Timeline</h5>
                <span class="text-muted small">Logs of your recent platform actions</span>
            </div>
            <div class="card-body p-4 pt-3">
                <div class="d-flex flex-column gap-3">
                    @forelse($recentActivity as $log)
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-light border text-muted rounded-circle d-flex align-items-center justify-content-center mt-1" style="width: 28px; height: 28px; flex-shrink:0;">
                            <i class="bi bi-check-lg" style="font-size:0.75rem;"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-dark small text-truncate fw-bold">{{ $log->action ?? 'Modified item' }}</div>
                            <span class="text-muted smaller" style="font-size:0.7rem;">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted small">
                        <i class="bi bi-clock-history d-block fs-3 mb-2 opacity-50"></i>
                        No activities logged yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .smaller { font-size: 0.72rem; }
    .btn-subscriber {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.8rem;
        transition: all 0.2s;
    }
    .btn-subscriber:hover {
        opacity: 0.9;
        color: white;
    }
</style>
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

        setTimeout(() => {
            document.getElementById('subscriberChartSkeleton').style.display = 'none';
        }, 300);

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
