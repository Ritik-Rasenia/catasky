@extends('admin.layouts.app')

@section('title', 'Analytical Overview')

@section('content')
<div class="container-fluid px-0">
    <!-- Analytical Header -->
    <div class="row align-items-center mb-4 g-3">
        <div class="col-sm">
            <h2 class="fw-bold mb-1 brand-font text-gradient" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Business Intelligence</h2>
            <p class="text-muted mb-0 small">Real-time performance metrics and catalogue engagement statistics.</p>
        </div>
        <div class="col-sm-auto">
            <div class="btn-group rounded-pill shadow-sm overflow-hidden border bg-white p-1">
                <button type="button" class="btn btn-sm btn-white rounded-pill px-3 active" onclick="updatePeriod('day', this)">Day</button>
                <button type="button" class="btn btn-sm btn-white rounded-pill px-3" onclick="updatePeriod('week', this)">Week</button>
                <button type="button" class="btn btn-sm btn-white rounded-pill px-3" onclick="updatePeriod('month', this)">Month</button>
                <button type="button" class="btn btn-sm btn-white rounded-pill px-3" onclick="updatePeriod('year', this)">Year</button>
            </div>
        </div>
    </div>

    <!-- Core Metrics Row -->
    <div class="row g-4 mb-4">
        <!-- Revenue Card -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm p-4 border-0 position-relative overflow-hidden h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="trend-indicator up text-success bg-success bg-opacity-10 px-2 py-1 rounded-pill small fw-bold">+12.5%</div>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Revenue</div>
                <h3 class="fw-bold mb-0 text-dark" id="metric-revenue">₹12,45,320</h3>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar" style="width: 75%; background: var(--primary-color);"></div>
                </div>
            </div>
        </div>
        <!-- Active Subscribers Card -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm p-4 border-0 position-relative overflow-hidden h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-success bg-opacity-10 text-success" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="trend-indicator up text-success bg-success bg-opacity-10 px-2 py-1 rounded-pill small fw-bold">+8.2%</div>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">Subscribers</div>
                <h3 class="fw-bold mb-0 text-dark" id="metric-subscribers">{{ number_format($subscribersCount) }}</h3>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: 60%"></div>
                </div>
            </div>
        </div>
        <!-- Active Vendors Card -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm p-4 border-0 position-relative overflow-hidden h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div class="trend-indicator up text-success bg-success bg-opacity-10 px-2 py-1 rounded-pill small fw-bold">+15.4%</div>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">Active Vendors</div>
                <h3 class="fw-bold mb-0 text-dark" id="metric-vendors">{{ $activeVendors }}</h3>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: 85%"></div>
                </div>
            </div>
        </div>
        <!-- Conversion Rate Card -->
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm p-4 border-0 position-relative overflow-hidden h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-info bg-opacity-10 text-info" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="trend-indicator up text-success bg-success bg-opacity-10 px-2 py-1 rounded-pill small fw-bold">+4.8%</div>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">Conversion Rate</div>
                <h3 class="fw-bold mb-0 text-dark" id="metric-conversion">{{ $conversionRate }}%</h3>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-info" style="width: 50%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Revenue & Orders Combo Chart -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark brand-font">Revenue & Orders Overview</h5>
                            <span class="text-muted small">Interactive monthly financials and conversions</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 pt-3 position-relative">
                    <!-- Skeleton Loader -->
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
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark brand-font">Sharing Breakdown</h5>
                    <span class="text-muted small">Channel distribution analysis</span>
                </div>
                <div class="card-body p-4 pt-3 position-relative">
                    <div id="sharingSkeleton" class="skeleton skeleton-chart w-100 position-absolute start-0 top-0 h-100 z-1" style="background-color: var(--surface-muted); margin: 0;"></div>
                    <div class="chart-container d-flex align-items-center justify-content-center" style="height: 240px;">
                        <canvas id="sharingDoughnutChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold"><i class="bi bi-whatsapp text-success me-2"></i> WhatsApp</span>
                            <span class="small text-muted" id="breakdown-whatsapp">{{ $analytics['sharing_breakdown']['whatsapp'] }} shares</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> PDF Generation</span>
                            <span class="small text-muted" id="breakdown-pdf">{{ $analytics['sharing_breakdown']['pdf'] }} shares</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-bold"><i class="bi bi-link-45deg text-primary me-2"></i> Direct Link</span>
                            <span class="small text-muted" id="breakdown-link">{{ $analytics['sharing_breakdown']['link'] }} shares</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Area Chart & Activity Logs -->
    <div class="row g-4">
        <!-- Visitor Traffic Area Chart -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark brand-font">Traffic & Engagement Trends</h5>
                    <span class="text-muted small">Catalogue visits vs lead conversions</span>
                </div>
                <div class="card-body p-4 pt-3 position-relative">
                    <div id="trafficSkeleton" class="skeleton skeleton-chart w-100 position-absolute start-0 top-0 h-100 z-1" style="background-color: var(--surface-muted); margin: 0;"></div>
                    <div class="chart-container">
                        <canvas id="trafficAreaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Summary / Top Products -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark brand-font">Top Catalogue Items</h5>
                        <span class="text-muted small">Best sellers and active views</span>
                    </div>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                @foreach($topProducts as $tp)
                                <tr class="border-0">
                                    <td class="ps-4 py-3 border-0">
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 160px;">{{ $tp['name'] }}</div>
                                        <span class="small text-muted">{{ $tp['sales'] }} sales</span>
                                    </td>
                                    <td class="py-3 text-end border-0">
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
    .bg-success-soft { background: rgba(16, 185, 129, 0.1); }
    .bg-danger-soft { background: rgba(239, 110, 110, 0.1); }
    .btn-white {
        background: transparent;
        color: var(--text-muted);
        border: none;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .btn-white:hover {
        color: var(--text-color);
    }
    .btn-white.active {
        background: var(--primary-color) !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
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

        // Periodic Fake Data mappings
        window.dashboardData = {
            day: {
                revenue: "₹45,820",
                subscribers: "12,544",
                vendors: "86",
                conversion: "4.9%",
                revenueLabels: ['9 AM','11 AM','1 PM','3 PM','5 PM','7 PM'],
                revenueData: [8000, 12000, 15000, 11000, 18000, 22000],
                ordersData: [8, 12, 14, 9, 16, 19],
                trafficLabels: ['9 AM','11 AM','1 PM','3 PM','5 PM','7 PM'],
                visitsData: [120, 180, 210, 150, 240, 290],
                conversionsData: [12, 18, 15, 10, 22, 25]
            },
            week: {
                revenue: "₹3,12,400",
                subscribers: "12,610",
                vendors: "88",
                conversion: "4.7%",
                revenueLabels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                revenueData: [45000, 52000, 38000, 65000, 82000, 95000, 88000],
                ordersData: [40, 48, 35, 60, 75, 88, 80],
                trafficLabels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                visitsData: [800, 950, 780, 1100, 1400, 1650, 1520],
                conversionsData: [45, 52, 38, 65, 82, 95, 88]
            },
            month: {
                revenue: "₹12,45,320",
                subscribers: "12,842",
                vendors: "86",
                conversion: "4.8%",
                revenueLabels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                revenueData: [845000, 920000, 780000, 1050000, 1120000, 980000, 1245320, 1180000, 1320000, 1150000, 1280000, 1390000],
                ordersData: [820, 910, 756, 1020, 1090, 945, 1284, 1150, 1310, 1080, 1240, 1350],
                trafficLabels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                visitsData: [8000, 9200, 7500, 10200, 10900, 9400, 12800, 11500, 13100, 10800, 12400, 13500],
                conversionsData: [450, 520, 380, 650, 820, 950, 880, 790, 910, 720, 840, 930]
            },
            year: {
                revenue: "₹1,48,92,300",
                subscribers: "14,240",
                vendors: "95",
                conversion: "5.1%",
                revenueLabels: ['2021','2022','2023','2024','2025','2026'],
                revenueData: [7200000, 8900000, 11200000, 13400000, 14500000, 14892300],
                ordersData: [7100, 8500, 10800, 12900, 13900, 14200],
                trafficLabels: ['2021','2022','2023','2024','2025','2026'],
                visitsData: [72000, 89000, 112000, 134000, 145000, 148900],
                conversionsData: [4100, 5200, 6800, 8200, 9100, 9500]
            }
        };

        // ─── Revenue & Orders Combo Chart ───
        const comboCtx = document.getElementById('revenueComboChart').getContext('2d');
        window.revenueComboChartInstance = new Chart(comboCtx, {
            type: 'bar',
            data: {
                labels: window.dashboardData.month.revenueLabels,
                datasets: [{
                    type: 'line',
                    label: 'Revenue (₹)',
                    data: window.dashboardData.month.revenueData,
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
                    data: window.dashboardData.month.ordersData,
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
                    }
                },
                scales: {
                    y: {
                        position: 'left',
                        grid: { color: gridColor, drawBorder: false },
                        ticks: {
                            color: textColor,
                            font: { family: 'Poppins', size: 10 },
                            callback: function(val) { return '₹' + val.toLocaleString(); }
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

        // Hide Skeletons after Chart Rendering
        setTimeout(() => {
            document.getElementById('revenueSkeleton').style.display = 'none';
        }, 350);

        // ─── Sharing Distribution Doughnut Chart ───
        const doughnutCtx = document.getElementById('sharingDoughnutChart').getContext('2d');
        window.sharingDoughnutChartInstance = new Chart(doughnutCtx, {
            type: 'doughnut',
            data: {
                labels: ['WhatsApp', 'PDF', 'Link'],
                datasets: [{
                    data: [
                        {{ $analytics['sharing_breakdown']['whatsapp'] }},
                        {{ $analytics['sharing_breakdown']['pdf'] }},
                        {{ $analytics['sharing_breakdown']['link'] }}
                    ],
                    backgroundColor: ['#10b981', '#ef4444', '#3b82f6'],
                    hoverOffset: 12,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '80%'
            }
        });

        setTimeout(() => {
            document.getElementById('sharingSkeleton').style.display = 'none';
        }, 400);

        // ─── Visitor Traffic Area Chart ───
        const trafficCtx = document.getElementById('trafficAreaChart').getContext('2d');
        const fillGradient = trafficCtx.createLinearGradient(0, 0, 0, 300);
        fillGradient.addColorStop(0, 'rgba(6, 182, 212, 0.2)');
        fillGradient.addColorStop(1, 'rgba(6, 182, 212, 0.0)');

        window.trafficAreaChartInstance = new Chart(trafficCtx, {
            type: 'line',
            data: {
                labels: window.dashboardData.month.trafficLabels,
                datasets: [{
                    label: 'Catalogue Visits',
                    data: window.dashboardData.month.visitsData,
                    borderColor: '#06b6d4',
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: fillGradient,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#06b6d4',
                    pointRadius: 4,
                    tension: 0.4
                }, {
                    label: 'Conversions',
                    data: window.dashboardData.month.conversionsData,
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

        setTimeout(() => {
            document.getElementById('trafficSkeleton').style.display = 'none';
        }, 450);

        // Dynamic Theme Change Listener for Charts
        window.addEventListener('themeChanged', function() {
            const nextTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const isDarkNext = nextTheme === 'dark';
            const nextColor = isDarkNext ? '#94a3b8' : '#64748b';
            const nextGrid = isDarkNext ? '#243041' : '#f1f5f9';

            [window.revenueComboChartInstance, window.trafficAreaChartInstance].forEach(chart => {
                if (chart) {
                    chart.options.scales.y.ticks.color = nextColor;
                    chart.options.scales.y.grid.color = nextGrid;
                    chart.options.scales.x.ticks.color = nextColor;
                    if (chart.options.scales.y1) {
                        chart.options.scales.y1.ticks.color = nextColor;
                    }
                    chart.options.plugins.legend.labels.color = nextColor;
                    chart.update();
                }
            });
        });
    });

    // Toggle Period Metrics
    window.updatePeriod = function(period, btn) {
        // Toggle Active Button Styles
        $(btn).siblings().removeClass('active');
        $(btn).addClass('active');

        const dataSet = window.dashboardData[period];
        if (!dataSet) return;

        // Update core statistics titles
        document.getElementById('metric-revenue').innerText = dataSet.revenue;
        document.getElementById('metric-subscribers').innerText = Number(dataSet.subscribers).toLocaleString();
        document.getElementById('metric-vendors').innerText = dataSet.vendors;
        document.getElementById('metric-conversion').innerText = dataSet.conversion;

        // Transition Revenue Combo Chart
        if (window.revenueComboChartInstance) {
            window.revenueComboChartInstance.data.labels = dataSet.revenueLabels;
            window.revenueComboChartInstance.data.datasets[0].data = dataSet.revenueData;
            window.revenueComboChartInstance.data.datasets[1].data = dataSet.ordersData;
            window.revenueComboChartInstance.update('active');
        }

        // Transition Traffic Area Chart
        if (window.trafficAreaChartInstance) {
            window.trafficAreaChartInstance.data.labels = dataSet.trafficLabels;
            window.trafficAreaChartInstance.data.datasets[0].data = dataSet.visitsData;
            window.trafficAreaChartInstance.data.datasets[1].data = dataSet.conversionsData;
            window.trafficAreaChartInstance.update('active');
        }
    };
</script>
@endpush
