@extends('admin.layouts.app')

@section('title', 'Analytical Overview')

@section('content')
<div class="container-fluid px-4">
    <!-- Analytical Header -->
    <div class="row align-items-center mb-5">
        <div class="col">
            <h2 class="fw-bold mb-1">Business Intelligence</h2>
            <p class="text-muted">Real-time performance metrics and catalogue engagement.</p>
        </div>
        <div class="col-auto">
            <div class="btn-group rounded-pill shadow-sm overflow-hidden">
                <button class="btn btn-white active border">Day</button>
                <button class="btn btn-white border">Week</button>
                <button class="btn btn-white border">Month</button>
            </div>
        </div>
    </div>

    <!-- Core Metrics -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-primary-soft text-primary">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <div class="trend-indicator up">+14.5%</div>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">Catalogue Views</div>
                <h2 class="fw-bold mb-0">12,842</h2>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar" style="width: 70%"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-success-soft text-success">
                        <i class="bi bi-share"></i>
                    </div>
                    <div class="trend-indicator up">+8.2%</div>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Shares</div>
                <h2 class="fw-bold mb-0">3,491</h2>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: 45%"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-warning-soft text-warning">
                        <i class="bi bi-chat-left-dots"></i>
                    </div>
                    <div class="trend-indicator down">-2.1%</div>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">Conversion Rate</div>
                <h2 class="fw-bold mb-0">4.8%</h2>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: 60%"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-info-soft text-info">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="trend-indicator up">+12%</div>
                </div>
                <div class="text-muted small fw-bold text-uppercase mb-1">New Enquiries</div>
                <h2 class="fw-bold mb-0">{{ $enquiriesCount }}</h2>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-info" style="width: 80%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Analytics Section -->
    <div class="row g-4 mb-5">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Engagement & Traffic</h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-light text-dark border">Traffic</button>
                            <button class="btn btn-primary">Shares</button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <canvas id="mainPerformanceChart" height="350"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-0">Sharing Distribution</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <canvas id="sharingChart" height="280"></canvas>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small fw-bold"><i class="bi bi-whatsapp text-success me-2"></i> WhatsApp</div>
                            <div class="small text-muted">{{ $analytics['sharing_breakdown']['whatsapp'] }} shares</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small fw-bold"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> PDF Generation</div>
                            <div class="small text-muted">{{ $analytics['sharing_breakdown']['pdf'] }} shares</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="small fw-bold"><i class="bi bi-link-45deg text-primary me-2"></i> Direct Link</div>
                            <div class="small text-muted">{{ $analytics['sharing_breakdown']['link'] }} shares</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products & Categories Table -->
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Inventory & Engagement</h5>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-light rounded-pill btn-sm px-3 fw-bold">View Inventory</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 border-0 py-3 small text-muted text-uppercase">Product Details</th>
                                <th class="border-0 py-3 small text-muted text-uppercase">Category</th>
                                <th class="border-0 py-3 small text-muted text-uppercase">Engagement</th>
                                <th class="border-0 py-3 small text-muted text-uppercase">Status</th>
                                <th class="border-0 py-3 small text-muted text-uppercase text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentProducts as $product)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $product->thumbnail_url }}" width="45" height="45" class="rounded-3 border" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($product->name) }}'">
                                        <div>
                                            <div class="fw-bold">{{ $product->name }}</div>
                                            <div class="text-muted smaller">ID: CAT-{{ $product->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark rounded-pill">{{ $product->category->name ?? 'N/A' }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; min-width: 60px;">
                                            <div class="progress-bar" style="width: {{ rand(30, 90) }}%"></div>
                                        </div>
                                        <span class="smaller fw-bold text-muted">{{ rand(100, 999) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $product->status ? 'bg-success-soft' : 'bg-danger-soft' }}">
                                        {{ $product->status ? 'Online' : 'Offline' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-light btn-sm rounded-circle"><i class="bi bi-pencil"></i></button>
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

<style>
    .metric-card { background: white; border-radius: 24px; padding: 28px; border: 1px solid #edf2f7; }
    .icon-box { width: 54px; height: 54px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .trend-indicator { font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 50px; }
    .trend-indicator.up { background: #dcfce7; color: #15803d; }
    .trend-indicator.down { background: #fee2e2; color: #b91c1c; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; }
    .smaller { font-size: 11px; }
    .bg-primary-soft { background: #e0e7ff; color: #4338ca; }
    .bg-success-soft { background: #dcfce7; color: #15803d; }
    .bg-warning-soft { background: #fef3c7; color: #92400e; }
    .bg-info-soft { background: #e0f2fe; color: #075985; }
    .btn-white { background: white; border: 1px solid #e2e8f0; }
    .btn-white.active { background: #f8fafc; font-weight: bold; }
</style>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('mainPerformanceChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.15)');
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($analytics['labels']) !!},
                datasets: [{
                    label: 'Catalogue Traffic',
                    data: {!! json_encode($analytics['visits']) !!},
                    borderColor: '#4338ca',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4338ca',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.4
                }, {
                    label: 'Enquiries',
                    data: {!! json_encode($analytics['enquiries']) !!},
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', align: 'end', labels: { usePointStyle: true, padding: 25 } } },
                scales: {
                    y: { grid: { color: '#f1f5f9', drawBorder: false }, ticks: { font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });

        // Sharing Distribution Chart
        const shareCtx = document.getElementById('sharingChart').getContext('2d');
        new Chart(shareCtx, {
            type: 'doughnut',
            data: {
                labels: ['WhatsApp', 'PDF', 'Link'],
                datasets: [{
                    data: [
                        {!! $analytics['sharing_breakdown']['whatsapp'] !!}, 
                        {!! $analytics['sharing_breakdown']['pdf'] !!}, 
                        {!! $analytics['sharing_breakdown']['link'] !!}
                    ],
                    backgroundColor: ['#10b981', '#ef4444', '#3b82f6'],
                    hoverOffset: 15,
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
    });
</script>
@endpush
@endsection
@endsection
