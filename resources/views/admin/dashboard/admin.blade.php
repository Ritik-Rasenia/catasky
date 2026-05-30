@extends('admin.layouts.app')

@section('title', 'Analytical Overview')

@section('content')
@php
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
<div class="container-fluid px-0">
    <!-- Analytical Header -->
    <div class="row align-items-center mb-4 g-3">
        <div class="col-sm">
            <h2 class="fw-bold mb-1 brand-font text-gradient" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Business Intelligence</h2>
            <p class="text-muted mb-0 small">Real-time performance metrics and catalogue engagement statistics.</p>
        </div>
        <div class="col-sm-auto d-flex align-items-center gap-2">
            <form method="GET" id="filterForm" class="d-inline-block m-0">
                <select name="filter" class="form-select form-select-sm border shadow-sm rounded-pill px-3" onchange="this.form.submit()" style="background-color: var(--surface-color); color: var(--text-primary); font-size: 0.8rem; height: 36px; min-width: 140px; cursor: pointer; border-color: var(--border) !important;">
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
            <span class="badge bg-success-soft text-success px-3 py-2 rounded-pill small fw-bold d-inline-flex align-items-center" style="height:36px;">
                <i class="bi bi-circle-fill me-1" style="font-size:8px;"></i> Live Data
            </span>
        </div>
    </div>

    <!-- Core Metrics Row -->
    <div class="row g-4 mb-4">
        <!-- Revenue Card -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card p-4 border-0 position-relative overflow-hidden h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    @php
                        $growthColor = $monthlyGrowth >= 0 ? 'success' : 'danger';
                        $growthIcon  = $monthlyGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                    @endphp
                    <div class="trend-indicator text-{{ $growthColor }} bg-{{ $growthColor }} bg-opacity-10 px-2 py-1 rounded-pill small fw-bold">
                        <i class="bi {{ $growthIcon }}"></i> {{ abs($monthlyGrowth) }}%
                    </div>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Revenue</div>
                <h3 class="fw-bold mb-0 text-dark">{{ $revenue }}</h3>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar" style="width: 75%; background: var(--primary-color);"></div>
                </div>
            </div>
        </div>
        <!-- Active Subscribers Card -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card p-4 border-0 position-relative overflow-hidden h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-success bg-opacity-10 text-success" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="trend-indicator text-success bg-success bg-opacity-10 px-2 py-1 rounded-pill small fw-bold">
                        {{ $subscribersCount }} total
                    </div>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">Subscribers</div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($subscribersCount) }}</h3>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: {{ $subscribersCount > 0 ? min(100, ($activeVendors / max($subscribersCount, 1)) * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
        <!-- Total Stores Card -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card p-4 border-0 position-relative overflow-hidden h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-shop-window"></i>
                    </div>
                    <div class="trend-indicator text-warning bg-warning bg-opacity-10 px-2 py-1 rounded-pill small fw-bold">
                        {{ $activeVendors }} Live
                    </div>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Stores</div>
                <h3 class="fw-bold mb-0 text-dark">{{ $totalStores }}</h3>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: {{ $totalStores > 0 ? min(100, ($activeVendors / max($totalStores, 1)) * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
        <!-- Pending Approval Accounts Card -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card p-4 border-0 position-relative overflow-hidden h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-danger bg-opacity-10 text-danger" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <a href="{{ route('admin.saas.approvals.index') }}" class="trend-indicator text-danger bg-danger bg-opacity-10 px-2 py-1 rounded-pill small fw-bold text-decoration-none" title="Go to Approvals">
                        Review <i class="bi bi-arrow-right small"></i>
                    </a>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">Pending Approval Accounts</div>
                <h3 class="fw-bold mb-0 text-dark">{{ $pendingApprovals }}</h3>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-danger" style="width: {{ $totalStores > 0 ? min(100, ($pendingApprovals / max($totalStores, 1)) * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Revenue & Orders Combo Chart -->
        <div class="col-xl-8">
            <div class="card border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark brand-font">Revenue & Orders Overview</h5>
                            <span class="text-muted small">Revenue & order count — {{ $filterDesc }}</span>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">{{ ucfirst(str_replace('_', ' ', $currentFilter)) }}</span>
                    </div>
                </div>
                <div class="card-body p-4 pt-3 position-relative">
                    <div id="revenueSkeleton" class="skeleton skeleton-chart w-100 position-absolute start-0 top-0 h-100 z-1" style="background-color: var(--surface-muted); margin: 0; padding: 24px;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="skeleton skeleton-line w-25"></div>
                            <div class="skeleton skeleton-line w-10"></div>
                        </div>
                        <div class="w-100 h-75 d-flex gap-3 align-items-end">
                            <div class="skeleton w-10" style="height: 40%;"></div>
                            <div class="skeleton w-10" style="height: 60%;"></div>
                            <div class="skeleton w-10" style="height: 80%;"></div>
                            <div class="skeleton w-10" style="height: 50%;"></div>
                            <div class="skeleton w-10" style="height: 70%;"></div>
                            <div class="skeleton w-10" style="height: 90%;"></div>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueComboChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sharing Distribution Doughnut -->
        <div class="col-xl-4">
            <div class="card border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark brand-font">Sharing Breakdown</h5>
                    <span class="text-muted small">Channel distribution — real data</span>
                </div>
                <div class="card-body p-4 pt-3 position-relative">
                    <div id="sharingSkeleton" class="skeleton skeleton-chart w-100 position-absolute start-0 top-0 h-100 z-1" style="background-color: var(--surface-muted); margin: 0;"></div>
                    <div class="chart-container d-flex align-items-center justify-content-center" style="height: 240px;">
                        <canvas id="sharingDoughnutChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold"><i class="bi bi-whatsapp text-success me-2"></i> WhatsApp</span>
                            <span class="small text-muted">{{ $analytics['sharing_breakdown']['whatsapp'] }} shares</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> PDF Generation</span>
                            <span class="small text-muted">{{ $analytics['sharing_breakdown']['pdf'] }} shares</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-bold"><i class="bi bi-link-45deg text-primary me-2"></i> Direct Link</span>
                            <span class="small text-muted">{{ $analytics['sharing_breakdown']['link'] }} shares</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Traffic + Top Products -->
    <div class="row g-4 mb-4">
        <!-- Catalogue Traffic Area Chart -->
        <div class="col-xl-8">
            <div class="card border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark brand-font">Traffic & Engagement Trends</h5>
                    <span class="text-muted small">Catalogue views & downloads — {{ $filterDesc }}</span>
                </div>
                <div class="card-body p-4 pt-3 position-relative">
                    <div id="trafficSkeleton" class="skeleton skeleton-chart w-100 position-absolute start-0 top-0 h-100 z-1" style="background-color: var(--surface-muted); margin: 0;"></div>
                    <div class="chart-container">
                        <canvas id="trafficAreaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Catalogue Items -->
        <div class="col-xl-4">
            <div class="card border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark brand-font">Top Catalogue Items</h5>
                        <span class="text-muted small">Best sellers and active views</span>
                    </div>
                </div>
                <div class="card-body p-0 mt-3">
                    @if(count($topProducts))
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                @foreach($topProducts as $tp)
                                <tr class="border-0">
                                    <td class="ps-4 py-3 border-0">
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 160px;">{{ $tp['name'] }}</div>
                                        <span class="small text-muted">{{ $tp['sales'] }} views</span>
                                    </td>
                                    <td class="py-3 text-end border-0 text-nowrap">
                                        <div class="fw-bold text-dark">{{ $tp['revenue'] }}</div>
                                    </td>
                                    <td class="pe-4 py-3 text-end border-0">
                                        @if($tp['trend'] === 'up')
                                            <span class="badge bg-success-soft text-success rounded-pill"><i class="bi bi-arrow-up"></i></span>
                                        @elseif($tp['trend'] === 'down')
                                            <span class="badge bg-danger-soft text-danger rounded-pill"><i class="bi bi-arrow-down"></i></span>
                                        @else
                                            <span class="badge bg-light text-muted rounded-pill"><i class="bi bi-dash"></i></span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-box-seam fs-1 opacity-25 d-block mb-2"></i>
                        <small>No approved products yet</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="row g-4">
        <!-- Recent Transactions -->
        <div class="col-xl-6">
            <div class="card border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark brand-font">Recent Transactions</h5>
                        <span class="text-muted small">Latest subscription payments</span>
                    </div>
                    <a href="{{ route('admin.saas.payments.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size:0.75rem;">View All</a>
                </div>
                <div class="card-body p-0 mt-2">
                    @if(count($recentTransactions))
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 fw-semibold small text-muted border-0">TXN ID</th>
                                    <th class="fw-semibold small text-muted border-0">Customer</th>
                                    <th class="fw-semibold small text-muted border-0">Amount</th>
                                    <th class="fw-semibold small text-muted border-0">Status</th>
                                    <th class="pe-4 fw-semibold small text-muted border-0 text-end">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $txn)
                                <tr>
                                    <td class="ps-4 py-3 border-0">
                                        <code class="small text-primary">{{ $txn['id'] }}</code>
                                    </td>
                                    <td class="py-3 border-0 fw-semibold small">{{ $txn['customer'] }}</td>
                                    <td class="py-3 border-0 fw-bold text-dark">{{ $txn['amount'] }}</td>
                                    <td class="py-3 border-0">
                                        @php
                                            $sc = match($txn['status']) {
                                                'success','completed','paid' => 'success',
                                                'pending' => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $sc }}-soft text-{{ $sc }} rounded-pill">{{ ucfirst($txn['status']) }}</span>
                                    </td>
                                    <td class="pe-4 py-3 border-0 text-end text-muted small">{{ $txn['date'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-credit-card fs-1 opacity-25 d-block mb-2"></i>
                        <small>No transactions recorded yet</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Support Tickets (Enquiries) -->
        <div class="col-xl-6">
            <div class="card border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark brand-font">Recent Enquiries</h5>
                        <span class="text-muted small">Latest customer enquiries</span>
                    </div>
                    <a href="{{ route('admin.enquiries.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size:0.75rem;">View All</a>
                </div>
                <div class="card-body p-0 mt-2">
                    @if(count($recentEnquiries))
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                @foreach($recentEnquiries as $enq)
                                <tr>
                                    <td class="ps-4 py-3 border-0">
                                        <div class="fw-semibold small text-dark">{{ $enq->name ?? 'Anonymous' }}</div>
                                        <div class="text-muted" style="font-size:0.75rem;">{{ Str::limit($enq->subject ?? $enq->message ?? 'Enquiry', 48) }}</div>
                                    </td>
                                    <td class="py-3 border-0 text-nowrap">
                                        <span class="badge {{ $enq->is_read ? 'bg-success-soft text-success' : 'bg-warning-soft text-warning' }} rounded-pill">
                                            {{ $enq->is_read ? 'Read' : 'Unread' }}
                                        </span>
                                    </td>
                                    <td class="pe-4 py-3 border-0 text-end text-muted small text-nowrap">
                                        {{ $enq->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-envelope fs-1 opacity-25 d-block mb-2"></i>
                        <small>No enquiries yet</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Recent Users + Catalogue Products -->
    <div class="row g-4 mt-0">
        <!-- Recent Users -->
        <div class="col-xl-6 mt-4">
            <div class="card border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark brand-font">Recent Users</h5>
                        <span class="text-muted small">Latest registrations</span>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size:0.75rem;">View All</a>
                </div>
                <div class="card-body p-0 mt-2">
                    @forelse($recentUsers as $u)
                    <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom" style="border-color: var(--border) !important;">
                        <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:16px;flex-shrink:0;">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold small text-dark text-truncate">{{ $u->name }}</div>
                            <div class="text-muted" style="font-size:0.72rem;">{{ $u->email }}</div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="badge bg-light text-muted rounded-pill small">{{ $u->roles->pluck('name')->first() ?? 'User' }}</div>
                            <div class="text-muted mt-1" style="font-size:0.7rem;">{{ $u->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-person-x fs-1 opacity-25 d-block mb-2"></i>
                        <small>No users yet</small>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Admin Products -->
        <div class="col-xl-6 mt-4">
            <div class="card border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark brand-font">Recent Products</h5>
                        <span class="text-muted small">Latest catalogue entries</span>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size:0.75rem;">View All</a>
                </div>
                <div class="card-body p-0 mt-2">
                    @forelse($recentProducts as $prod)
                    <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom" style="border-color: var(--border) !important;">
                        <div style="width:40px;height:40px;border-radius:10px;background:var(--surface-muted,#f8fafc);border:1px solid var(--border);overflow:hidden;flex-shrink:0;">
                            @if($prod->thumbnail)
                                <img src="{{ $prod->thumbnail_url }}" alt="{{ $prod->name }}" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted"><i class="bi bi-box-seam"></i></div>
                            @endif
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold small text-dark text-truncate">{{ $prod->name }}</div>
                            <div class="text-muted" style="font-size:0.72rem;">{{ $prod->category?->name ?? 'No category' }}</div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="fw-bold small text-dark">{{ $prod->price ? '₹' . number_format($prod->price, 2) : '—' }}</div>
                            <div class="mt-1">
                                <span class="badge {{ $prod->status ? 'bg-success-soft text-success' : 'bg-secondary-soft text-secondary' }} rounded-pill" style="font-size:0.65rem;">
                                    {{ $prod->status ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-box-seam fs-1 opacity-25 d-block mb-2"></i>
                        <small>No products yet</small>
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
        const theme     = document.documentElement.getAttribute('data-theme') || 'light';
        const isDark    = theme === 'dark';
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? '#243041' : '#f1f5f9';

        // ── Revenue & Orders from PHP (real) ────────────────────────
        const revenueLabels = {!! json_encode($revenueChart['labels']) !!};
        const revenueData   = {!! json_encode($revenueChart['revenue']) !!};
        const ordersData    = {!! json_encode($revenueChart['orders']) !!};

        const comboCtx = document.getElementById('revenueComboChart').getContext('2d');
        window.revenueComboChartInstance = new Chart(comboCtx, {
            type: 'bar',
            data: {
                labels: revenueLabels,
                datasets: [{
                    type: 'line',
                    label: 'Revenue (₹)',
                    data: revenueData,
                    borderColor: '#4f46e5',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4f46e5',
                    pointHoverRadius: 6,
                    fill: false,
                    tension: 0.35,
                    yAxisID: 'y'
                }, {
                    type: 'bar',
                    label: 'Orders',
                    data: ordersData,
                    backgroundColor: 'rgba(124, 58, 237, 0.15)',
                    hoverBackgroundColor: 'rgba(124, 58, 237, 0.45)',
                    borderRadius: 8,
                    borderSkipped: false,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { usePointStyle: true, font: { family: 'Poppins', size: 11 }, color: textColor }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.dataset.label.includes('Revenue')) return '₹' + ctx.parsed.y.toLocaleString('en-IN');
                                return ctx.dataset.label + ': ' + ctx.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        position: 'left',
                        grid: { color: gridColor, drawBorder: false },
                        ticks: {
                            color: textColor,
                            font: { family: 'Poppins', size: 10 },
                            callback: function(val) { return '₹' + val.toLocaleString('en-IN'); }
                        }
                    },
                    y1: {
                        position: 'right',
                        grid: { display: false },
                        ticks: { color: textColor, font: { family: 'Poppins', size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { family: 'Poppins', size: 11 } }
                    }
                }
            }
        });
        setTimeout(() => { document.getElementById('revenueSkeleton').style.display = 'none'; }, 350);

        // ── Sharing Distribution Doughnut (real) ────────────────────
        const doughnutCtx = document.getElementById('sharingDoughnutChart').getContext('2d');
        const whatsappCount = {{ $analytics['sharing_breakdown']['whatsapp'] }};
        const pdfCount      = {{ $analytics['sharing_breakdown']['pdf'] }};
        const linkCount     = {{ $analytics['sharing_breakdown']['link'] }};
        const totalShares   = whatsappCount + pdfCount + linkCount;

        window.sharingDoughnutChartInstance = new Chart(doughnutCtx, {
            type: 'doughnut',
            data: {
                labels: ['WhatsApp', 'PDF', 'Link'],
                datasets: [{
                    data: totalShares > 0 ? [whatsappCount, pdfCount, linkCount] : [1, 1, 1],
                    backgroundColor: ['#10b981', '#ef4444', '#3b82f6'],
                    hoverOffset: 12,
                    borderWidth: 0
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
                                if (totalShares === 0) return ctx.label + ': No data yet';
                                const pct = Math.round((ctx.parsed / totalShares) * 100);
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                },
                cutout: '80%'
            }
        });
        setTimeout(() => { document.getElementById('sharingSkeleton').style.display = 'none'; }, 400);

        // ── Traffic Area Chart — views & downloads per month (real) ─
        const trafficCtx = document.getElementById('trafficAreaChart').getContext('2d');
        const fillGradient = trafficCtx.createLinearGradient(0, 0, 0, 300);
        fillGradient.addColorStop(0, 'rgba(6, 182, 212, 0.2)');
        fillGradient.addColorStop(1, 'rgba(6, 182, 212, 0.0)');

        window.trafficAreaChartInstance = new Chart(trafficCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenueChart['labels']) !!},
                datasets: [{
                    label: 'Catalogue Views',
                    data: {!! json_encode($analytics['visits']) !!},
                    borderColor: '#06b6d4',
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: fillGradient,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#06b6d4',
                    pointRadius: 4,
                    tension: 0.4
                }, {
                    label: 'Downloads',
                    data: {!! json_encode($analytics['shares']) !!},
                    borderColor: '#10b981',
                    borderWidth: 2,
                    fill: false,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981',
                    pointRadius: 4,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { usePointStyle: true, font: { family: 'Poppins', size: 11 }, color: textColor }
                    }
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
        setTimeout(() => { document.getElementById('trafficSkeleton').style.display = 'none'; }, 450);

        // ── Dark-mode chart update ───────────────────────────────────
        window.addEventListener('themeChanged', function() {
            const next = document.documentElement.getAttribute('data-theme') || 'light';
            const nc = next === 'dark' ? '#94a3b8' : '#64748b';
            const ng = next === 'dark' ? '#243041' : '#f1f5f9';
            [window.revenueComboChartInstance, window.trafficAreaChartInstance].forEach(chart => {
                if (!chart) return;
                chart.options.scales.y.ticks.color = nc;
                chart.options.scales.y.grid.color  = ng;
                chart.options.scales.x.ticks.color = nc;
                if (chart.options.scales.y1) chart.options.scales.y1.ticks.color = nc;
                chart.options.plugins.legend.labels.color = nc;
                chart.update();
            });
        });
    });
</script>
@endpush
