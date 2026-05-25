@extends('admin.layouts.app')

@section('title', 'Overview')

@section('content')
<div class="container-fluid px-0">

    {{-- Welcome Hero Banner --}}
    <div class="dash-hero mb-4 p-4 p-md-5 rounded-4 text-white position-relative overflow-hidden shadow-sm" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
        <div class="position-relative z-2">
            <div class="small fw-bold text-uppercase opacity-75 mb-2">📅 {{ now()->format('l, d F Y') }}</div>
            <h2 class="fw-bold mb-1">Welcome back, {{ auth()->user()->name }}! 👋</h2>
            <p class="opacity-75 mb-4">Here is your general operations and catalogue management overview.</p>
            
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.products.create') }}" class="btn btn-white btn-sm rounded-pill px-3 shadow-sm text-dark bg-white fw-bold"><i class="bi bi-plus-lg me-2"></i>Add Product</a>
                <a href="{{ route('catalogue') }}" target="_blank" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-bold"><i class="bi bi-eye me-2"></i>View Catalogue</a>
                @can('view-enquiries')
                <a href="{{ route('admin.enquiries.index') }}" class="btn btn-outline-light btn-sm rounded-pill px-3 position-relative fw-bold">
                    <i class="bi bi-chat-left-dots me-2"></i>Enquiries
                    @if($enquiriesCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.65rem;">
                            {{ $enquiriesCount }}
                        </span>
                    @endif
                </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- General Metrics Stats Row --}}
    <div class="row g-4 mb-4">
        <!-- Revenue Stat -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card p-3 shadow-sm border-0 d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="stat-label text-muted small fw-bold text-uppercase mb-1">Revenue</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $revenue }}</h4>
                </div>
                <span class="stat-trend up text-success small fw-bold mt-2"><i class="bi bi-arrow-up-short"></i>+12.5%</span>
            </div>
        </div>
        <!-- Monthly Orders Stat -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card p-3 shadow-sm border-0 d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="stat-label text-muted small fw-bold text-uppercase mb-1">Monthly Orders</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($monthlyOrders) }}</h4>
                </div>
                <span class="stat-trend up text-success small fw-bold mt-2"><i class="bi bi-arrow-up-short"></i>+8.2%</span>
            </div>
        </div>
        <!-- Active Subscribers Stat -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card p-3 shadow-sm border-0 d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="stat-label text-muted small fw-bold text-uppercase mb-1">Subscribers</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($subscribersCount) }}</h4>
                </div>
                <span class="stat-trend up text-success small fw-bold mt-2"><i class="bi bi-arrow-up-short"></i>+4.1%</span>
            </div>
        </div>
        <!-- Active Vendors Stat -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card p-3 shadow-sm border-0 d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="stat-label text-muted small fw-bold text-uppercase mb-1">Active Vendors</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $activeVendors }}</h4>
                </div>
                <span class="stat-trend neutral text-muted small fw-bold mt-2"><i class="bi bi-dash"></i>Steady</span>
            </div>
        </div>
        <!-- Conversion Rate Stat -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card p-3 shadow-sm border-0 d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="stat-label text-muted small fw-bold text-uppercase mb-1">Conversion</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $conversionRate }}%</h4>
                </div>
                <span class="stat-trend up text-success small fw-bold mt-2"><i class="bi bi-arrow-up-short"></i>+2.1%</span>
            </div>
        </div>
        <!-- Operations Products Stat -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card p-3 shadow-sm border-0 d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="stat-label text-muted small fw-bold text-uppercase mb-1">Total Products</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($productsCount) }}</h4>
                </div>
                <span class="stat-trend up text-success small fw-bold mt-2"><i class="bi bi-arrow-up-short"></i>Growing</span>
            </div>
        </div>
    </div>

    <!-- Charts and Operations Tables Row -->
    <div class="row g-4 mb-4">
        <!-- Monthly Revenue Chart -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark brand-font">Monthly Financial Performance</h5>
                    <span class="text-muted small">Standard monthly tracking graph</span>
                </div>
                <div class="card-body p-4 pt-3 position-relative">
                    <div id="generalRevenueSkeleton" class="skeleton skeleton-chart w-100 position-absolute start-0 top-0 h-100 z-1" style="background-color: var(--surface-muted); margin:0;"></div>
                    <div class="chart-container">
                        <canvas id="generalRevenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Widget -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark brand-font">System Notifications</h5>
                    <span class="text-muted small">Latest administrative activity alerts</span>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="d-flex flex-column gap-3">
                        @foreach($notifications as $notif)
                        <div class="d-flex align-items-start gap-3 p-2 rounded-3 border border-light" style="background: var(--surface-muted);">
                            <div class="p-2 rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px; flex-shrink: 0; background: @if($notif['type']=='order') #4F46E5 @elseif($notif['type']=='subscriber') #10B981 @elseif($notif['type']=='alert') #EF4444 @else #64748B @endif;">
                                <i class="bi @if($notif['type']=='order') bi-cart @elseif($notif['type']=='subscriber') bi-person-check @elseif($notif['type']=='alert') bi-exclamation-triangle @else bi-info-circle @endif"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="text-dark small fw-bold text-truncate" style="max-height: 40px; overflow: hidden;">{{ $notif['message'] }}</div>
                                <span class="text-muted smaller">{{ $notif['time'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory, Transactions, and User Tables Row -->
    <div class="row g-4 mb-4">
        <!-- Recent Transactions -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark brand-font">Recent Transactions</h5>
                        <span class="text-muted small">Payment histories and subscriber transfers</span>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 small text-muted border-0">Txn ID</th>
                                <th class="py-3 small text-muted border-0">Customer</th>
                                <th class="py-3 small text-muted border-0">Amount</th>
                                <th class="pe-4 py-3 small text-muted border-0 text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $txn)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $txn['id'] }}</td>
                                <td>{{ $txn['customer'] }}</td>
                                <td class="fw-bold">{{ $txn['amount'] }}</td>
                                <td class="pe-4 text-end">
                                    <span class="badge rounded-pill @if($txn['status']=='completed') bg-success bg-opacity-10 text-success @elseif($txn['status']=='processing') bg-warning bg-opacity-10 text-warning @else bg-danger bg-opacity-10 text-danger @endif small px-2 py-1">
                                        {{ ucfirst($txn['status']) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Selling Products -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark brand-font">Top Catalogue Products</h5>
                    <span class="text-muted small">Most popular cable and infrastructure options</span>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 small text-muted border-0">Product</th>
                                <th class="py-3 small text-muted border-0">Sales</th>
                                <th class="pe-4 py-3 small text-muted border-0 text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $tp)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 220px;">{{ $tp['name'] }}</div>
                                </td>
                                <td>{{ $tp['sales'] }} units</td>
                                <td class="pe-4 text-end fw-bold">{{ $tp['revenue'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Support & User Activity Row -->
    <div class="row g-4">
        <!-- Support Tickets -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark brand-font">Support Tickets</h5>
                    <span class="text-muted small">Open queries requiring review</span>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="d-flex flex-column gap-3">
                        @foreach($supportTickets as $ticket)
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 border">
                            <div class="min-w-0">
                                <div class="fw-bold text-dark text-truncate mb-1" style="font-size:0.875rem;">{{ $ticket['subject'] }}</div>
                                <span class="smaller text-muted">{{ $ticket['id'] }} · Priority: </span>
                                <span class="badge rounded-pill smaller @if($ticket['priority']=='high') bg-danger-soft text-danger @elseif($ticket['priority']=='medium') bg-warning-soft text-warning @else bg-success-soft text-success @endif">{{ ucfirst($ticket['priority']) }}</span>
                            </div>
                            <span class="badge rounded-pill @if($ticket['status']=='open') bg-primary-soft text-primary @else bg-light text-muted @endif py-1 px-2 small">{{ ucfirst($ticket['status']) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Users Widget -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark brand-font">Active Admins & Staff</h5>
                    <span class="text-muted small">Currently online team members</span>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="d-flex flex-column gap-3">
                        @foreach($activeUsers as $au)
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="position-relative">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($au['name']) }}&background=4f46e5&color=fff" class="rounded-circle" width="38" height="38">
                                    <span class="position-absolute bottom-0 end-0 border border-2 border-white rounded-circle" style="width: 10px; height: 10px; background: @if($au['status']=='online') #10B981 @elseif($au['status']=='away') #F59E0B @else #94A3B8 @endif;"></span>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">{{ $au['name'] }}</div>
                                    <span class="smaller text-muted">{{ $au['role'] }}</span>
                                </div>
                            </div>
                            <span class="smaller text-muted">{{ $au['lastActive'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories & Portfolio Overview -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark brand-font">Product Categories</h5>
                        <span class="text-muted small">General catalogue breakdown</span>
                    </div>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="d-flex flex-column">
                        @forelse(\App\Models\Category::withCount('products')->where('status', 1)->take(5)->get() as $cat)
                        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom border-light">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-tag"></i>
                                </div>
                                <span class="fw-bold text-dark small text-truncate" style="max-width: 160px;">{{ $cat->name }}</span>
                            </div>
                            <span class="badge bg-light text-muted rounded-pill px-3">{{ $cat->products_count }} products</span>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted small">No categories found</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .metric-card {
        background: var(--surface-color);
        border-radius: 20px;
        border: 1px solid var(--border-color);
    }
    .smaller { font-size: 0.72rem; }
    .bg-primary-soft { background: rgba(79, 70, 229, 0.1); }
    .bg-success-soft { background: rgba(16, 185, 129, 0.1); }
    .bg-warning-soft { background: rgba(245, 158, 11, 0.1); }
    .bg-danger-soft { background: rgba(239, 68, 68, 0.1); }
</style>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const theme = document.documentElement.getAttribute('data-theme') || 'light';
        const isDark = theme === 'dark';
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? '#243041' : '#f1f5f9';

        const chartCtx = document.getElementById('generalRevenueChart').getContext('2d');
        const generalRevenueChart = new Chart(chartCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenueChart['labels']) !!},
                datasets: [{
                    label: 'Monthly Revenue (₹)',
                    data: {!! json_encode($revenueChart['revenue']) !!},
                    borderColor: '#4f46e5',
                    borderWidth: 3,
                    fill: false,
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
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { usePointStyle: true, font: { family: 'Poppins', size: 11 }, color: textColor }
                    }
                },
                scales: {
                    y: {
                        grid: { color: gridColor, drawBorder: false },
                        ticks: {
                            color: textColor,
                            font: { family: 'Poppins', size: 10 },
                            callback: function(val) { return '₹' + val.toLocaleString(); }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { family: 'Poppins', size: 11 } }
                    }
                }
            }
        });

        // Hide Skeletons
        setTimeout(() => {
            document.getElementById('generalRevenueSkeleton').style.display = 'none';
        }, 300);

        // Responsive Theme adaptation
        window.addEventListener('themeChanged', function() {
            const nextTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const isDarkNext = nextTheme === 'dark';
            const nextColor = isDarkNext ? '#94a3b8' : '#64748b';
            const nextGrid = isDarkNext ? '#243041' : '#f1f5f9';

            if (generalRevenueChart) {
                generalRevenueChart.options.scales.y.ticks.color = nextColor;
                generalRevenueChart.options.scales.y.grid.color = nextGrid;
                generalRevenueChart.options.scales.x.ticks.color = nextColor;
                generalRevenueChart.options.plugins.legend.labels.color = nextColor;
                generalRevenueChart.update();
            }
        });
    });
</script>
@endpush
