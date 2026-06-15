@extends('admin.layouts.app')

@section('title', 'Advanced Analytics')

@push('css')
@include('partials.analytics-dashboard-styles')
@endpush

@section('content')
<div class="container-fluid analytics-page">
    {{-- Header --}}
    <div class="analytics-toolbar">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-graph-up-arrow text-primary"></i> Advanced Analytics</h1>
            <p class="text-muted mb-0 small">Real-time tracking, visitor engagement, and conversion insights</p>
        </div>
        <div class="analytics-actions">
            {{-- Date Filter --}}
            <select id="dateFilter" class="form-select form-select-sm" style="width:160px" onchange="applyFilter(this.value)">
                @foreach(['all_time'=>'All Time','today'=>'Today','yesterday'=>'Yesterday','this_week'=>'This Week','last_30_days'=>'Last 30 Days','this_month'=>'This Month','last_month'=>'Last Month','this_year'=>'This Year'] as $val=>$label)
                    <option value="{{ $val }}" {{ $filter===$val?'selected':'' }}>{{ $label }}</option>
                @endforeach
            </select>
            {{-- Custom Date Range --}}
            <form id="dateRangeForm" method="GET" action="{{ route('admin.analytics') }}" class="d-flex gap-1 align-items-center">
                <input type="hidden" name="filter" value="custom">
                <input type="date" name="date_from" class="form-control form-control-sm" style="width:130px" value="{{ $dateFrom }}" placeholder="From">
                <span class="small text-muted">–</span>
                <input type="date" name="date_to" class="form-control form-control-sm" style="width:130px" value="{{ $dateTo }}" placeholder="To">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            </form>
            {{-- Auto Refresh Toggle --}}
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                <label class="form-check-label small" for="autoRefresh">Live</label>
            </div>
            <a href="{{ route('admin.analytics.export', ['filter'=>$filter]) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Export
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4" id="kpiCards">
        @php
        $kpis = [
            ['label'=>'Total Shares','value'=>$totalShares,'icon'=>'bi-share-fill','color'=>'primary'],
            ['label'=>'Total Opens','value'=>$totalOpens,'icon'=>'bi-eye-fill','color'=>'info'],
            ['label'=>'Unique Visitors','value'=>$uniqueVisitors,'icon'=>'bi-person-badge','color'=>'success'],
            ['label'=>'Product Views','value'=>$productViews,'icon'=>'bi-box-seam-fill','color'=>'warning'],
            ['label'=>'Downloads','value'=>$totalDownloads,'icon'=>'bi-download','color'=>'secondary'],
            ['label'=>'Avg Session','value'=>$avgSessionDuration.'s','icon'=>'bi-clock-fill','color'=>'dark'],
            ['label'=>'Bounce Rate','value'=>$bounceRate.'%','icon'=>'bi-arrow-return-left','color'=>'danger'],
            ['label'=>'Conversion','value'=>$conversionRate.'%','icon'=>'bi-bullseye','color'=>'primary'],
            ['label'=>'Engagements','value'=>$totalEngagements,'icon'=>'bi-lightning-charge-fill','color'=>'warning'],
        ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
            <div class="card analytics-card analytics-kpi h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="analytics-kpi-icon d-flex align-items-center justify-content-center bg-{{ $kpi['color'] }}-subtle">
                        <i class="bi {{ $kpi['icon'] }} text-{{ $kpi['color'] }} fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">{{ $kpi['label'] }}</div>
                        <div class="fw-bold fs-5 kpi-value" data-key="{{ Str::slug($kpi['label'],'_') }}">{{ $kpi['value'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Charts Row 1 --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-activity text-primary"></i> Visits & Product Views Over Time</div>
                <div class="card-body analytics-chart-body pt-0"><canvas id="visitsChart" height="90"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-pie-chart text-info"></i> Device Distribution</div>
                <div class="card-body analytics-chart-body pt-0"><canvas id="deviceChart" height="200"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-share text-success"></i> Channel Breakdown</div>
                <div class="card-body analytics-chart-body pt-0"><canvas id="channelChart" height="200"></canvas></div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-bar-chart text-warning"></i> Top 10 Most Viewed Products</div>
                <div class="card-body analytics-chart-body pt-0"><canvas id="topProductsChart" height="90"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Charts Row 3: Downloads + Enquiries --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-12">
            <div class="card analytics-card">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-arrow-down-circle text-secondary"></i> Downloads & Enquiries Over Time</div>
                <div class="card-body analytics-chart-body pt-0"><canvas id="downloadsChart" height="60"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Tables Row --}}
    <div class="row g-3 mb-4">
        {{-- Top Performing Subscribers --}}
        <div class="col-lg-6">
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-trophy text-warning"></i> Top Performing Subscribers</div>
                <div class="card-body pt-0 table-responsive">
                    <table class="table table-sm align-middle mb-0 analytics-table">
                        <thead class="table-light small"><tr><th>Name</th><th class="text-center">Shares</th><th class="text-center">Views</th><th class="text-center">Downloads</th><th class="text-center">Engagements</th><th class="text-center">Conversions</th></tr></thead>
                        <tbody>
                        @forelse($topSubscribers as $sub)
                            <tr>
                                <td class="fw-semibold">{{ $sub['name'] }}<br><small class="text-muted">{{ $sub['email'] }}</small></td>
                                <td class="text-center">{{ $sub['shares'] }}</td>
                                <td class="text-center">{{ $sub['views'] }}</td>
                                <td class="text-center">{{ $sub['downloads'] }}</td>
                                <td class="text-center"><span class="badge bg-warning-subtle text-warning">{{ $sub['engagements'] }}</span></td>
                                <td class="text-center"><span class="badge bg-success-subtle text-success">{{ $sub['conversions'] }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No data yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Most Downloaded --}}
        <div class="col-lg-6">
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-download text-secondary"></i> Most Downloaded Catalogues</div>
                <div class="card-body pt-0 table-responsive">
                    <table class="table table-sm align-middle mb-0 analytics-table">
                        <thead class="table-light small"><tr><th>Catalogue</th><th class="text-center">Downloads</th><th class="text-center">Unique</th></tr></thead>
                        <tbody>
                        @forelse($mostDownloaded as $dl)
                            <tr>
                                <td class="fw-semibold">{{ Str::limit($dl['title'], 30) }}</td>
                                <td class="text-center">{{ $dl['download_count'] }}</td>
                                <td class="text-center"><span class="badge bg-info-subtle text-info">{{ $dl['unique_downloads'] }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No downloads yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         ENGAGEMENT EVENTS SECTION
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="d-flex align-items-center gap-2 mb-3 mt-2">
        <h5 class="fw-bold mb-0"><i class="bi bi-lightning-charge-fill text-warning"></i> Engagement Events</h5>
        <span class="badge bg-warning-subtle text-warning fs-6 px-3">{{ number_format($totalEngagements) }} total</span>
    </div>

    {{-- Engagement KPI Row --}}
    <div class="row g-3 mb-4">
        @php
        $eventLabels = [
            'catalogue_open'      => ['label' => 'Catalogue Opens',    'icon' => 'bi-book-open-fill',    'color' => 'primary'],
            'product_detail_open' => ['label' => 'Product Detail',     'icon' => 'bi-box-seam-fill',     'color' => 'info'],
            'whatsapp_click'      => ['label' => 'WhatsApp Clicks',    'icon' => 'bi-whatsapp',          'color' => 'success'],
            'call_click'          => ['label' => 'Call Clicks',        'icon' => 'bi-telephone-fill',    'color' => 'warning'],
            'email_click'         => ['label' => 'Email Clicks',       'icon' => 'bi-envelope-fill',     'color' => 'secondary'],
            'enquiry_submit'      => ['label' => 'Enquiry Submits',    'icon' => 'bi-chat-left-dots-fill','color' => 'danger'],
            'direct_link'         => ['label' => 'Direct Link Shares', 'icon' => 'bi-link-45deg',        'color' => 'dark'],
            'pdf_share'           => ['label' => 'PDF Shares',         'icon' => 'bi-file-earmark-pdf',  'color' => 'primary'],
            'image_share'         => ['label' => 'Image Shares',       'icon' => 'bi-images',            'color' => 'info'],
            'whatsapp_pdf_share'  => ['label' => 'WhatsApp PDFs',      'icon' => 'bi-whatsapp',          'color' => 'success'],
            'whatsapp_image_share'=> ['label' => 'WhatsApp Images',    'icon' => 'bi-whatsapp',          'color' => 'success'],
            'pdf_download'        => ['label' => 'PDF Downloads',      'icon' => 'bi-file-earmark-arrow-down','color' => 'secondary'],
            'image_download'      => ['label' => 'Image Downloads',    'icon' => 'bi-cloud-arrow-down',  'color' => 'secondary'],
            'copy_link'           => ['label' => 'Copied Links',       'icon' => 'bi-clipboard-check',   'color' => 'dark'],
        ];
        @endphp
        @foreach($eventLabels as $type => $meta)
        <div class="col-xl col-lg-3 col-md-4 col-sm-6">
            <div class="card analytics-card analytics-kpi h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="analytics-kpi-icon d-flex align-items-center justify-content-center bg-{{ $meta['color'] }}-subtle" style="width:42px;height:42px;min-width:42px;">
                        <i class="bi {{ $meta['icon'] }} text-{{ $meta['color'] }} fs-6"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold" style="font-size:.7rem">{{ $meta['label'] }}</div>
                        <div class="fw-bold fs-5">{{ $engagementByType[$type] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Engagement Charts --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-bar-chart-fill text-warning"></i> Engagement by Type</div>
                <div class="card-body analytics-chart-body pt-0"><canvas id="engagementTypeChart" height="220"></canvas></div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-activity text-warning"></i> Engagement Trend</div>
                <div class="card-body analytics-chart-body pt-0"><canvas id="engagementTrendChart" height="220"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Top Products by Engagement + Recent Engagements --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-trophy-fill text-warning"></i> Top Products by Engagement</div>
                <div class="card-body pt-0 table-responsive">
                    <table class="table table-sm align-middle mb-0 analytics-table">
                        <thead class="table-light small"><tr><th>#</th><th>Product</th><th class="text-center">Events</th></tr></thead>
                        <tbody>
                        @forelse($topEngagedProducts as $i => $ep)
                            <tr>
                                <td class="text-muted fw-bold">{{ $i+1 }}</td>
                                <td class="fw-semibold">{{ $ep['name'] }}</td>
                                <td class="text-center"><span class="badge bg-warning-subtle text-warning">{{ $ep['engagement_count'] }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No engagement events yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-clock-history text-primary"></i> Recent Engagement Events</div>
                <div class="card-body pt-0 table-responsive">
                    <table class="table table-sm align-middle mb-0 analytics-table">
                        <thead class="table-light small"><tr><th>Event</th><th>Subscriber</th><th>Product</th><th>Catalogue</th><th>When</th></tr></thead>
                        <tbody>
                        @forelse($recentEngagements as $eng)
                            @php
                            $eventColors = [
                                'catalogue_open'=>'primary','product_detail_open'=>'info',
                                'whatsapp_click'=>'success','call_click'=>'warning',
                                'email_click'=>'secondary','enquiry_submit'=>'danger','direct_link'=>'dark',
                                'pdf_share'=>'primary','image_share'=>'info',
                                'whatsapp_pdf_share'=>'success','whatsapp_image_share'=>'success',
                                'pdf_download'=>'secondary','image_download'=>'secondary','copy_link'=>'dark'
                            ];
                            $ec = $eventColors[$eng->event_type] ?? 'secondary';
                            @endphp
                            <tr>
                                <td><span class="badge bg-{{ $ec }}-subtle text-{{ $ec }}">{{ str_replace('_', ' ', $eng->event_type) }}</span></td>
                                <td class="small">{{ $eng->user?->name ?? '—' }}</td>
                                <td class="small">
                                    @if($eng->product?->name)
                                        {{ Str::limit($eng->product->name, 20) }}
                                    @elseif(is_array($eng->metadata) && !empty($eng->metadata['product_ids']))
                                        <span class="text-muted">{{ count($eng->metadata['product_ids']) }} product(s)</span>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($eng->shareLink?->title)
                                        {{ Str::limit($eng->shareLink->title, 15) }}
                                    @elseif($eng->shareLink?->token)
                                        <span class="text-muted">{{ Str::limit($eng->shareLink->token, 12) }}</span>
                                    @elseif(is_array($eng->metadata) && !empty($eng->metadata['backfilled_catalogue']))
                                        <span title="Backfilled">{{ Str::limit($eng->metadata['backfilled_catalogue'], 15) }}</span>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $eng->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No events yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Visitor Engagement --}}
    <div class="card analytics-card mb-4">
        <div class="card-header bg-transparent border-0 fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-people text-primary"></i> Recent Visitor Activity</span>
            <div class="d-flex align-items-center gap-2">
                <span class="live-dot"></span>
                <span class="badge bg-primary-subtle text-primary" id="lastUpdated">Live</span>
            </div>
        </div>
        <div class="card-body pt-0 table-responsive">
            <table class="table table-sm align-middle mb-0 analytics-table" id="visitsTable">
                <thead class="table-light small">
                    <tr><th>Visitor</th><th>IP</th><th>Device</th><th>Browser</th><th>Location</th><th>Duration</th><th>Products</th><th>Opened</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($recentVisits as $v)
                    <tr>
                        <td class="small text-truncate" style="max-width:100px" title="{{ $v->visitor_uuid }}">{{ Str::limit($v->visitor_uuid ?? 'Anonymous', 10) }}</td>
                        <td class="small">{{ $v->ip_address ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $v->device_type }}</span></td>
                        <td class="small">{{ $v->browser }}</td>
                        <td class="small">{{ $v->city }}, {{ $v->country }}</td>
                        <td class="small">{{ $v->total_time_spent >= 60 ? floor($v->total_time_spent/60).'m '.($v->total_time_spent%60).'s' : $v->total_time_spent.'s' }}</td>
                        <td class="text-center">{{ $v->productViews->count() }}</td>
                        <td class="small text-muted">{{ $v->opened_at?->diffForHumans() }}</td>
                        <td>
                            @if($v->visitor_uuid)
                                <a href="{{ route('admin.analytics.timeline', $v->visitor_uuid) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="View Journey">
                                    <i class="bi bi-diagram-3"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
function applyFilter(val) {
    window.location.href = '{{ route("admin.analytics") }}?filter=' + val;
}

document.addEventListener('DOMContentLoaded', function() {
    // Chart Data from server
    const chartData         = @json($chartData);
    const topProducts       = @json($topProducts);
    const deviceData        = @json($deviceDistribution);
    const channelData       = @json($channelDistribution);
    const engagementByType  = @json($engagementByType);
    const engagementTrend   = @json($engagementTrend);

    // Color palette
    const colors = {
        primary: '#4F46E5', info: '#06b6d4', success: '#10b981',
        warning: '#f59e0b', danger: '#ef4444', secondary: '#64748b', dark: '#1e293b'
    };

    const engagementColors = [
        colors.primary, colors.info, colors.success,
        colors.warning, colors.secondary, colors.danger, colors.dark
    ];

    // Read theme variables
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const fontFamily = "'Poppins', 'Inter', sans-serif";

    // Chart.js default overrides
    Chart.defaults.font.family = fontFamily;
    Chart.defaults.font.size = 11;
    Chart.defaults.color = textColor;
    Chart.defaults.plugins.tooltip.backgroundColor = isDark ? 'rgba(30, 41, 59, 0.95)' : 'rgba(15, 23, 42, 0.95)';
    Chart.defaults.plugins.tooltip.titleFont = { family: fontFamily, weight: '600', size: 12 };
    Chart.defaults.plugins.tooltip.bodyFont = { family: fontFamily, size: 11 };
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.boxWidth = 8;
    Chart.defaults.plugins.tooltip.boxHeight = 8;

    // Helper for line linear gradients
    function createLineGradient(ctx, baseColor) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 220);
        gradient.addColorStop(0, baseColor + '40'); // 25% opacity
        gradient.addColorStop(1, baseColor + '00'); // 0% opacity
        return gradient;
    }

    // 1. Visits & Product Views Line Chart
    const visitsCanvas = document.getElementById('visitsChart');
    const visitsCtx = visitsCanvas.getContext('2d');
    const visitsGrad = createLineGradient(visitsCtx, colors.primary);
    const viewsGrad = createLineGradient(visitsCtx, colors.warning);

    new Chart(visitsCtx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                { 
                    label: 'Visits', 
                    data: chartData.visits, 
                    borderColor: colors.primary, 
                    backgroundColor: visitsGrad, 
                    fill: true, 
                    tension: 0.3,
                    borderWidth: 2,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: isDark ? '#111827' : '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                { 
                    label: 'Product Views', 
                    data: chartData.views, 
                    borderColor: colors.warning, 
                    backgroundColor: viewsGrad, 
                    fill: true, 
                    tension: 0.3,
                    borderWidth: 2,
                    pointBackgroundColor: colors.warning,
                    pointBorderColor: isDark ? '#111827' : '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 3,
                    pointHoverRadius: 6
                }
            ]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    position: 'top',
                    align: 'end',
                    labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle', font: { weight: '600' } }
                } 
            }, 
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: gridColor, borderDash: [4, 4], drawBorder: false },
                    ticks: { color: textColor, padding: 8 }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: textColor, padding: 8 }
                }
            } 
        }
    });

    // 2. Device Doughnut
    new Chart(document.getElementById('deviceChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(deviceData).length ? Object.keys(deviceData) : ['No Data'],
            datasets: [{ 
                data: Object.keys(deviceData).length ? Object.values(deviceData) : [1], 
                backgroundColor: [colors.primary, colors.info, colors.success, colors.warning, colors.danger],
                borderWidth: isDark ? 2 : 1,
                borderColor: isDark ? '#1e293b' : '#ffffff'
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle', padding: 16 }
                } 
            } 
        }
    });

    // 3. Channel Doughnut
    new Chart(document.getElementById('channelChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(channelData).length ? Object.keys(channelData).map(c => c.replace('_',' ')) : ['No Data'],
            datasets: [{ 
                data: Object.keys(channelData).length ? Object.values(channelData) : [1], 
                backgroundColor: [colors.success, colors.primary, colors.info, colors.warning, colors.secondary],
                borderWidth: isDark ? 2 : 1,
                borderColor: isDark ? '#1e293b' : '#ffffff'
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle', padding: 16 }
                } 
            } 
        }
    });

    // 4. Top Products Bar Chart
    const topProdCanvas = document.getElementById('topProductsChart');
    const topProdCtx = topProdCanvas.getContext('2d');
    const barGrad1 = topProdCtx.createLinearGradient(0, 0, 400, 0);
    barGrad1.addColorStop(0, colors.primary);
    barGrad1.addColorStop(1, colors.info + 'aa');
    const barGrad2 = topProdCtx.createLinearGradient(0, 0, 400, 0);
    barGrad2.addColorStop(0, colors.warning);
    barGrad2.addColorStop(1, '#f97316aa');

    new Chart(topProdCtx, {
        type: 'bar',
        data: {
            labels: topProducts.map(p => p.name.length > 20 ? p.name.slice(0,20)+'...' : p.name),
            datasets: [
                { label: 'Views', data: topProducts.map(p => p.view_count), backgroundColor: barGrad1, borderRadius: 4 },
                { label: 'Avg Duration(s)', data: topProducts.map(p => p.avg_duration), backgroundColor: barGrad2, borderRadius: 4 }
            ]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            indexAxis: 'y', 
            plugins: { 
                legend: { 
                    position: 'top',
                    align: 'end',
                    labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle', font: { weight: '600' } }
                } 
            }, 
            scales: { 
                x: { 
                    beginAtZero: true,
                    grid: { color: gridColor, borderDash: [4, 4], drawBorder: false },
                    ticks: { color: textColor }
                },
                y: {
                    grid: { display: false },
                    ticks: { color: textColor }
                }
            } 
        }
    });

    // 5. Downloads & Enquiries Line
    const downloadsCanvas = document.getElementById('downloadsChart');
    const downloadsCtx = downloadsCanvas.getContext('2d');
    const dlGrad = downloadsCtx.createLinearGradient(0, 0, 0, 180);
    dlGrad.addColorStop(0, colors.secondary + '40');
    dlGrad.addColorStop(1, colors.secondary + '00');
    const enqGrad = downloadsCtx.createLinearGradient(0, 0, 0, 180);
    enqGrad.addColorStop(0, colors.danger + '40');
    enqGrad.addColorStop(1, colors.danger + '00');

    new Chart(downloadsCtx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                { 
                    label: 'Downloads', 
                    data: chartData.downloads, 
                    borderColor: colors.secondary, 
                    backgroundColor: dlGrad, 
                    fill: true, 
                    tension: 0.3,
                    borderWidth: 2,
                    pointBackgroundColor: colors.secondary,
                    pointBorderColor: isDark ? '#111827' : '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                { 
                    label: 'Enquiries', 
                    data: chartData.enquiries, 
                    borderColor: colors.danger, 
                    backgroundColor: enqGrad, 
                    fill: true, 
                    tension: 0.3,
                    borderWidth: 2,
                    pointBackgroundColor: colors.danger,
                    pointBorderColor: isDark ? '#111827' : '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 3,
                    pointHoverRadius: 6
                }
            ]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    position: 'top',
                    align: 'end',
                    labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle', font: { weight: '600' } }
                } 
            }, 
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: gridColor, borderDash: [4, 4], drawBorder: false },
                    ticks: { color: textColor }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: textColor }
                }
            } 
        }
    });

    // 6. Engagement by Type — Horizontal Bar
    const engTypeCanvas = document.getElementById('engagementTypeChart');
    const engTypeCtx = engTypeCanvas.getContext('2d');
    const engLabels = Object.keys(engagementByType).map(k => k.replace(/_/g,' '));
    const engValues = Object.values(engagementByType);
    const engTypeGrad = engTypeCtx.createLinearGradient(0, 0, 400, 0);
    engTypeGrad.addColorStop(0, colors.warning);
    engTypeGrad.addColorStop(1, colors.danger);

    new Chart(engTypeCtx, {
        type: 'bar',
        data: {
            labels: engLabels.length ? engLabels : ['No Events'],
            datasets: [{
                label: 'Count',
                data: engValues.length ? engValues : [0],
                backgroundColor: engTypeGrad,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { 
                x: { 
                    beginAtZero: true, 
                    ticks: { stepSize: 1, color: textColor },
                    grid: { color: gridColor, borderDash: [4, 4], drawBorder: false }
                },
                y: {
                    ticks: { color: textColor },
                    grid: { display: false }
                }
            }
        }
    });

    // 7. Engagement Trend Line
    const engTrendCanvas = document.getElementById('engagementTrendChart');
    const engTrendCtx = engTrendCanvas.getContext('2d');
    const trendGrad = engTrendCtx.createLinearGradient(0, 0, 0, 200);
    trendGrad.addColorStop(0, colors.warning + '40');
    trendGrad.addColorStop(1, colors.warning + '00');

    new Chart(engTrendCtx, {
        type: 'line',
        data: {
            labels: engagementTrend.labels,
            datasets: [{
                label: 'Engagement Events',
                data: engagementTrend.counts,
                borderColor: colors.warning,
                backgroundColor: trendGrad,
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointBackgroundColor: colors.warning,
                pointBorderColor: isDark ? '#111827' : '#ffffff',
                pointBorderWidth: 1.5,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    position: 'top',
                    align: 'end',
                    labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle', font: { weight: '600' } }
                } 
            }, 
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: gridColor, borderDash: [4, 4], drawBorder: false },
                    ticks: { color: textColor }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: textColor }
                }
            } 
        }
    });

    // Auto-Refresh (every 30 seconds)
    let refreshInterval = null;
    const autoRefreshToggle = document.getElementById('autoRefresh');

    function startAutoRefresh() {
        if (refreshInterval) clearInterval(refreshInterval);
        refreshInterval = setInterval(fetchRealtime, 30000);
    }

    function fetchRealtime() {
        fetch('{{ route("admin.analytics.realtime") }}')
            .then(r => r.json())
            .then(data => {
                document.getElementById('lastUpdated').textContent = 'Updated ' + new Date(data.generated_at).toLocaleTimeString();
                // Update KPI values using correct data-keys matching the HTML output
                const kpiMap = { total_opens: 'total_opens', total_views: 'product_views', total_downloads: 'downloads' };
                Object.entries(kpiMap).forEach(([key, el]) => {
                    const elem = document.querySelector(`.kpi-value[data-key="${el}"]`);
                    if (elem && data[key] !== undefined) elem.textContent = data[key];
                });

                // Update recent visits table
                if (data.recent_visits && data.recent_visits.length) {
                    const tbody = document.querySelector('#visitsTable tbody');
                    tbody.innerHTML = '';
                    data.recent_visits.forEach(v => {
                        tbody.innerHTML += `<tr>
                            <td class="small">${v.visitor_uuid ? v.visitor_uuid.slice(0,10) : 'Anonymous'}</td>
                            <td class="small">${v.ip || '-'}</td>
                            <td><span class="badge bg-light text-dark border">${v.device}</span></td>
                            <td class="small">${v.browser}</td>
                            <td class="small">${v.city}, ${v.country}</td>
                            <td class="small">${v.duration >= 60 ? Math.floor(v.duration/60)+'m '+(v.duration%60)+'s' : v.duration+'s'}</td>
                            <td class="text-center">${v.products_viewed}</td>
                            <td class="small text-muted">${v.opened_at}</td>
                            <td>${v.visitor_uuid ? `<a href="/dashboard/analytics/timeline/${v.visitor_uuid}" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="bi bi-diagram-3"></i></a>` : ''}</td>
                        </tr>`;
                    });
                }
            })
            .catch(err => console.warn('Analytics refresh failed:', err));
    }

    autoRefreshToggle.addEventListener('change', function() {
        if (this.checked) { startAutoRefresh(); } else { clearInterval(refreshInterval); }
    });

    if (autoRefreshToggle.checked) startAutoRefresh();
});
</script>
@endpush
