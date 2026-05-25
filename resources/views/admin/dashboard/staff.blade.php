@extends('admin.layouts.app')

@section('title', 'Staff Dashboard')
@section('page-title', 'Workspace')

@section('content')
<div class="container-fluid px-0">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-info text-white shadow-lg overflow-hidden" style="border-radius: 20px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                <div class="card-body p-4 p-md-5 position-relative z-2">
                    <h2 class="fw-bold mb-2">Staff Workspace 👋</h2>
                    <p class="lead opacity-75 mb-0">Managing the day-to-day operations. You have <strong>{{ $enquiriesCount }}</strong> enquiries to review.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Metrics Row -->
    <div class="row g-4 mb-4">
        <!-- Products Metric -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-primary" style="background: var(--surface-color);">
                <div class="card-body p-4">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Catalogue Products</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($productsCount) }}</h3>
                </div>
            </div>
        </div>
        <!-- Categories Metric -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-success" style="background: var(--surface-color);">
                <div class="card-body p-4">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Categories</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($categoriesCount) }}</h3>
                </div>
            </div>
        </div>
        <!-- Enquiries Metric -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-warning" style="background: var(--surface-color);">
                <div class="card-body p-4">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Enquiries</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($enquiriesCount) }}</h3>
                </div>
            </div>
        </div>
        <!-- Brands Metric -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-info" style="background: var(--surface-color);">
                <div class="card-body p-4">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Active Brands</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($brandsCount) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Daily Enquiries Trend Chart -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="background: var(--surface-color);">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark brand-font">Enquiries Trend</h5>
                    <span class="text-muted small">Daily enquiry conversions</span>
                </div>
                <div class="card-body p-4 pt-3 position-relative">
                    <div id="staffChartSkeleton" class="skeleton skeleton-chart w-100 position-absolute start-0 top-0 h-100 z-1" style="background-color: var(--surface-muted); margin:0;"></div>
                    <div class="chart-container">
                        <canvas id="staffEnquiriesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Enquiries List -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="background: var(--surface-color);">
                <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark brand-font">Recent Enquiries</h5>
                        <span class="text-muted small">Queries requiring immediate review</span>
                    </div>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                @forelse($recentEnquiries as $e)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 180px;">{{ $e->name }}</div>
                                        <span class="smaller text-muted">{{ $e->created_at->format('M d, Y') }}</span>
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <a href="{{ route('admin.enquiries.show', $e->id) }}" class="btn btn-light btn-sm rounded-circle border shadow-sm"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="text-center py-5 text-muted small">No enquiries found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .smaller { font-size: 0.72rem; }
</style>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const theme = document.documentElement.getAttribute('data-theme') || 'light';
        const isDark = theme === 'dark';
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? '#243041' : '#f1f5f9';

        const chartCtx = document.getElementById('staffEnquiriesChart').getContext('2d');
        const fillGradient = chartCtx.createLinearGradient(0, 0, 0, 300);
        fillGradient.addColorStop(0, 'rgba(14, 165, 233, 0.2)');
        fillGradient.addColorStop(1, 'rgba(14, 165, 233, 0.0)');

        const enquiriesChart = new Chart(chartCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Daily Enquiries',
                    data: [12, 15, 10, 18, 22, 25, 21],
                    borderColor: '#0284c7',
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: fillGradient,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0284c7',
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
            document.getElementById('staffChartSkeleton').style.display = 'none';
        }, 300);

        window.addEventListener('themeChanged', function() {
            const nextTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const isDarkNext = nextTheme === 'dark';
            const nextColor = isDarkNext ? '#94a3b8' : '#64748b';
            const nextGrid = isDarkNext ? '#243041' : '#f1f5f9';

            if (enquiriesChart) {
                enquiriesChart.options.scales.y.ticks.color = nextColor;
                enquiriesChart.options.scales.y.grid.color = nextGrid;
                enquiriesChart.options.scales.x.ticks.color = nextColor;
                enquiriesChart.update();
            }
        });
    });
</script>
@endpush
