@extends('subscriber-panel.layouts.app')

@section('title', 'My Analytics')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-graph-up-arrow text-primary"></i> My Analytics</h1>
            <p class="text-muted mb-0 small">Track your catalogue performance, visitor engagement, and conversions</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <select id="dateFilter" class="form-select form-select-sm" style="width:160px" onchange="applyFilter(this.value)">
                @foreach(['all_time'=>'All Time','today'=>'Today','yesterday'=>'Yesterday','this_week'=>'This Week','last_30_days'=>'Last 30 Days','this_month'=>'This Month','last_month'=>'Last Month','this_year'=>'This Year'] as $val=>$label)
                    <option value="{{ $val }}" {{ $filter===$val?'selected':'' }}>{{ $label }}</option>
                @endforeach
            </select>
            {{-- Custom Date Range --}}
            <form id="dateRangeForm" method="GET" action="{{ route('subscriber.analytics') }}" class="d-flex gap-1 align-items-center">
                <input type="hidden" name="filter" value="custom">
                <input type="date" name="date_from" class="form-control form-control-sm" style="width:130px" value="{{ $dateFrom }}" placeholder="From">
                <span class="small text-muted">–</span>
                <input type="date" name="date_to" class="form-control form-control-sm" style="width:130px" value="{{ $dateTo }}" placeholder="To">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            </form>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                <label class="form-check-label small" for="autoRefresh">Live</label>
            </div>
            <a href="{{ route('subscriber.analytics.export', ['filter'=>$filter]) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Export
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
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
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-{{ $kpi['color'] }}-subtle" style="width:48px;height:48px;min-width:48px;">
                        <i class="bi {{ $kpi['icon'] }} text-{{ $kpi['color'] }} fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">{{ $kpi['label'] }}</div>
                        <div class="fw-bold fs-5 kpi-value">{{ $kpi['value'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Conversion Funnel --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-funnel text-primary"></i> Conversion Funnel</div>
        <div class="card-body pt-0">
            <div class="row text-center g-2">
                @php
                    $funnelSteps = [
                        ['label'=>'Shares','value'=>$funnel['shares'],'color'=>'primary','icon'=>'bi-share'],
                        ['label'=>'Opens','value'=>$funnel['opens'],'color'=>'info','icon'=>'bi-eye'],
                        ['label'=>'Product Views','value'=>$funnel['product_views'],'color'=>'warning','icon'=>'bi-box-seam'],
                        ['label'=>'Downloads','value'=>$funnel['downloads'],'color'=>'secondary','icon'=>'bi-download'],
                        ['label'=>'Enquiries','value'=>$funnel['enquiries'],'color'=>'success','icon'=>'bi-chat-dots'],
                        ['label'=>'Orders','value'=>$funnel['orders'],'color'=>'danger','icon'=>'bi-bag-check'],
                    ];
                    $maxVal = max(array_column($funnelSteps, 'value')) ?: 1;
                @endphp
                @foreach($funnelSteps as $step)
                <div class="col">
                    <div class="p-2 rounded bg-{{ $step['color'] }}-subtle">
                        <i class="bi {{ $step['icon'] }} text-{{ $step['color'] }} fs-4 d-block mb-1"></i>
                        <div class="fw-bold fs-5 text-{{ $step['color'] }}">{{ $step['value'] }}</div>
                        <div class="small text-muted">{{ $step['label'] }}</div>
                        @php $pct = $maxVal > 0 ? round(($step['value'] / $maxVal) * 100) : 0; @endphp
                        <div class="progress mt-2" style="height:6px">
                            <div class="progress-bar bg-{{ $step['color'] }}" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-activity text-primary"></i> Visits & Product Views Trend</div>
                <div class="card-body pt-0"><canvas id="visitsChart" height="90"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-pie-chart text-info"></i> Device Distribution</div>
                <div class="card-body pt-0"><canvas id="deviceChart" height="200"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-share text-success"></i> Channels</div>
                <div class="card-body pt-0"><canvas id="channelChart" height="200"></canvas></div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-bar-chart text-warning"></i> Top Viewed Products</div>
                <div class="card-body pt-0"><canvas id="topProductsChart" height="90"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Top Products Table --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-trophy text-warning"></i> Top Viewed Products Detail</div>
        <div class="card-body pt-0 table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light small">
                    <tr><th>#</th><th>Product</th><th class="text-center">Views</th><th class="text-center">Avg Duration</th></tr>
                </thead>
                <tbody>
                @forelse($topProducts as $i => $p)
                    <tr>
                        <td class="fw-bold text-muted">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $p['name'] }}</td>
                        <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $p['view_count'] }}</span></td>
                        <td class="text-center">{{ $p['avg_duration'] >= 60 ? floor($p['avg_duration']/60).'m '.($p['avg_duration']%60).'s' : $p['avg_duration'].'s' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No product views yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         ENGAGEMENT EVENTS SECTION
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-lightning-charge-fill text-warning"></i> Engagement Events</h5>
        <span class="badge bg-warning-subtle text-warning fs-6 px-3">{{ number_format($totalEngagements) }} total</span>
    </div>

    {{-- Engagement Mini-Cards --}}
    <div class="row g-3 mb-4">
        @php
        $eventMeta = [
            'catalogue_open'      => ['label' => 'Catalogue Opens',    'icon' => 'bi-book-open-fill',     'color' => 'primary'],
            'product_detail_open' => ['label' => 'Product Clicks',     'icon' => 'bi-box-seam-fill',      'color' => 'info'],
            'whatsapp_click'      => ['label' => 'WhatsApp Clicks',    'icon' => 'bi-whatsapp',           'color' => 'success'],
            'call_click'          => ['label' => 'Call Clicks',        'icon' => 'bi-telephone-fill',     'color' => 'warning'],
            'email_click'         => ['label' => 'Email Clicks',       'icon' => 'bi-envelope-fill',      'color' => 'secondary'],
            'enquiry_submit'      => ['label' => 'Enquiry Submits',    'icon' => 'bi-chat-left-dots-fill','color' => 'danger'],
            'direct_link'         => ['label' => 'Link Copies',        'icon' => 'bi-link-45deg',         'color' => 'dark'],
        ];
        @endphp
        @foreach($eventMeta as $type => $meta)
        <div class="col-xl col-lg-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-{{ $meta['color'] }}-subtle" style="width:40px;height:40px;min-width:40px;">
                        <i class="bi {{ $meta['icon'] }} text-{{ $meta['color'] }} fs-6"></i>
                    </div>
                    <div>
                        <div class="text-muted fw-semibold" style="font-size:.68rem">{{ $meta['label'] }}</div>
                        <div class="fw-bold fs-5">{{ $engagementByType[$type] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Engagement Trend Chart --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 fw-bold">
            <i class="bi bi-activity text-warning"></i> Engagement Trend
        </div>
        <div class="card-body pt-0">
            <canvas id="engagementTrendChart" height="70"></canvas>
        </div>
    </div>

    {{-- Recent Engagement Events --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 fw-bold">
            <i class="bi bi-clock-history text-primary"></i> Recent Engagement Events
        </div>
        <div class="card-body pt-0 table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light small">
                    <tr><th>Event</th><th>Product</th><th>Catalogue</th><th>When</th></tr>
                </thead>
                <tbody>
                @forelse($recentEngagements as $eng)
                    @php
                    $ec = [
                        'catalogue_open'=>'primary','product_detail_open'=>'info',
                        'whatsapp_click'=>'success','call_click'=>'warning',
                        'email_click'=>'secondary','enquiry_submit'=>'danger','direct_link'=>'dark'
                    ][$eng->event_type] ?? 'secondary';
                    @endphp
                    <tr>
                        <td><span class="badge bg-{{ $ec }}-subtle text-{{ $ec }}">{{ str_replace('_', ' ', $eng->event_type) }}</span></td>
                        <td class="small">{{ Str::limit($eng->product?->name ?? '—', 25) }}</td>
                        <td class="small">{{ Str::limit($eng->shareLink?->title ?? $eng->shareLink?->token ?? '—', 18) }}</td>
                        <td class="small text-muted">{{ $eng->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No engagement events recorded yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Visitors --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 fw-bold d-flex justify-content-between">
            <span><i class="bi bi-people text-primary"></i> Recent Visitors</span>
            <span class="badge bg-primary-subtle text-primary" id="lastUpdated">Live</span>
        </div>
        <div class="card-body pt-0 table-responsive">
            <table class="table table-sm align-middle mb-0" id="visitsTable">
                <thead class="table-light small">
                    <tr><th>Visitor</th><th>Device</th><th>Browser</th><th>Location</th><th>Duration</th><th>Products</th><th>Opened</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($recentVisits as $v)
                    <tr>
                        <td class="small" title="{{ $v->visitor_uuid }}">{{ Str::limit($v->visitor_uuid ?? 'Anonymous', 10) }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $v->device_type }}</span></td>
                        <td class="small">{{ $v->browser }}</td>
                        <td class="small">{{ $v->city }}, {{ $v->country }}</td>
                        <td class="small">{{ $v->total_time_spent >= 60 ? floor($v->total_time_spent/60).'m '.($v->total_time_spent%60).'s' : $v->total_time_spent.'s' }}</td>
                        <td class="text-center">{{ $v->productViews->count() }}</td>
                        <td class="small text-muted">{{ $v->opened_at?->diffForHumans() }}</td>
                        <td>
                            @if($v->visitor_uuid)
                                <a href="{{ route('subscriber.analytics.timeline', $v->visitor_uuid) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="View Journey"><i class="bi bi-diagram-3"></i></a>
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
    window.location.href = '{{ route("subscriber.analytics") }}?filter=' + val;
}

document.addEventListener('DOMContentLoaded', function() {
    const chartData      = @json($chartData);
    const topProducts    = @json($topProducts);
    const deviceData     = @json($deviceDistribution);
    const channelData    = @json($channelDistribution);
    const engagementTrend = @json($engagementTrend);

    const colors = { primary:'#4F46E5', info:'#06b6d4', success:'#10b981', warning:'#f59e0b', danger:'#ef4444', secondary:'#64748b' };

    // Visits Chart
    new Chart(document.getElementById('visitsChart'), {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                { label: 'Visits', data: chartData.visits, borderColor: colors.primary, backgroundColor: colors.primary+'20', fill: true, tension: 0.3 },
                { label: 'Product Views', data: chartData.views, borderColor: colors.warning, backgroundColor: colors.warning+'20', fill: true, tension: 0.3 }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
    });

    // Device Chart
    new Chart(document.getElementById('deviceChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(deviceData).length ? Object.keys(deviceData) : ['No Data'],
            datasets: [{ data: Object.keys(deviceData).length ? Object.values(deviceData) : [1], backgroundColor: [colors.primary, colors.info, colors.success, colors.warning] }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Channel Chart
    new Chart(document.getElementById('channelChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(channelData).length ? Object.keys(channelData).map(c => c.replace('_',' ')) : ['No Data'],
            datasets: [{ data: Object.keys(channelData).length ? Object.values(channelData) : [1], backgroundColor: [colors.success, colors.primary, colors.info, colors.warning] }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Top Products Chart
    new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: topProducts.map(p => p.name.length > 20 ? p.name.slice(0,20)+'...' : p.name),
            datasets: [
                { label: 'Views', data: topProducts.map(p => p.view_count), backgroundColor: colors.primary+'cc' },
                { label: 'Avg Duration(s)', data: topProducts.map(p => p.avg_duration), backgroundColor: colors.warning+'cc' }
            ]
        },
        options: { responsive: true, indexAxis: 'y', plugins: { legend: { position: 'top' } }, scales: { x: { beginAtZero: true } } }
    });

    // Engagement Trend Chart
    new Chart(document.getElementById('engagementTrendChart'), {
        type: 'line',
        data: {
            labels: engagementTrend.labels,
            datasets: [{
                label: 'Engagement Events',
                data: engagementTrend.counts,
                borderColor: colors.warning,
                backgroundColor: colors.warning + '25',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
    });

    // Auto-Refresh
    let refreshInterval = null;
    const autoRefreshToggle = document.getElementById('autoRefresh');

    function fetchRealtime() {
        fetch('{{ route("subscriber.analytics.realtime") }}')
            .then(r => r.json())
            .then(data => {
                document.getElementById('lastUpdated').textContent = 'Updated ' + new Date(data.generated_at).toLocaleTimeString();
                if (data.recent_visits && data.recent_visits.length) {
                    const tbody = document.querySelector('#visitsTable tbody');
                    tbody.innerHTML = '';
                    data.recent_visits.forEach(v => {
                        tbody.innerHTML += `<tr>
                            <td class="small">${v.visitor_uuid ? v.visitor_uuid.slice(0,10) : 'Anonymous'}</td>
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
            .catch(err => console.warn('Refresh failed:', err));
    }

    function startAutoRefresh() {
        if (refreshInterval) clearInterval(refreshInterval);
        refreshInterval = setInterval(fetchRealtime, 30000);
    }

    autoRefreshToggle.addEventListener('change', function() {
        if (this.checked) startAutoRefresh(); else clearInterval(refreshInterval);
    });
    if (autoRefreshToggle.checked) startAutoRefresh();
});
</script>
@endpush
