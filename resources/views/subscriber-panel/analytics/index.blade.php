@extends('subscriber-panel.layouts.app')

@section('title', 'My Analytics')

@push('css')
@include('partials.analytics-dashboard-styles')
@endpush

@section('content')
<div class="container-fluid analytics-page">
    {{-- Header --}}
    <div class="analytics-toolbar">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-graph-up-arrow text-primary"></i> My Analytics</h1>
            <p class="text-muted mb-0 small">Track your catalogue performance, visitor engagement, and conversions</p>
        </div>
        <div class="analytics-actions">
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
                        <div class="fw-bold fs-5 kpi-value">{{ $kpi['value'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Conversion Funnel --}}
    <div class="card analytics-card mb-4">
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
                    <div class="analytics-funnel-step p-2 bg-{{ $step['color'] }}-subtle">
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
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-activity text-primary"></i> Visits & Product Views Trend</div>
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
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-share text-success"></i> Channels</div>
                <div class="card-body analytics-chart-body pt-0"><canvas id="channelChart" height="200"></canvas></div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card analytics-card h-100">
                <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-bar-chart text-warning"></i> Top Viewed Products</div>
                <div class="card-body analytics-chart-body pt-0"><canvas id="topProductsChart" height="90"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Top Products Table --}}
    <div class="card analytics-card mb-4">
        <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-trophy text-warning"></i> Top Viewed Products Detail</div>
        <div class="card-body pt-0 table-responsive">
            <table class="table table-sm align-middle mb-0 analytics-table">
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

    <div class="row g-3 mb-4">
        @php
        $eventMeta = [
            'whatsapp_image_share'=> ['label' => 'WhatsApp Images',    'icon' => 'bi-whatsapp',           'color' => 'success'],
            'pdf_download'        => ['label' => 'PDF Downloads',      'icon' => 'bi-file-earmark-arrow-down','color' => 'secondary'],
            'image_download'      => ['label' => 'Image Downloads',    'icon' => 'bi-cloud-arrow-down',   'color' => 'secondary'],
            'copy_link'           => ['label' => 'Copied Links',       'icon' => 'bi-clipboard-check',    'color' => 'dark'],
        ];
        @endphp
        @foreach($eventMeta as $type => $meta)
        <div class="col-xl col-lg-3 col-md-4 col-sm-6">
            <div class="card analytics-card analytics-kpi h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="analytics-kpi-icon d-flex align-items-center justify-content-center bg-{{ $meta['color'] }}-subtle" style="width:40px;height:40px;min-width:40px;">
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
    <div class="card analytics-card mb-4">
        <div class="card-header bg-transparent border-0 fw-bold">
            <i class="bi bi-activity text-warning"></i> Engagement Trend
        </div>
        <div class="card-body analytics-chart-body pt-0">
            <canvas id="engagementTrendChart" height="70"></canvas>
        </div>
    </div>

    {{-- Recent Engagement Events --}}
    <div class="card analytics-card mb-4">
        <div class="card-header bg-transparent border-0 fw-bold">
            <i class="bi bi-clock-history text-primary"></i> Recent Engagement Events
        </div>
        <div class="card-body pt-0 table-responsive">
            <table class="table table-sm align-middle mb-0 analytics-table">
                <thead class="table-light small">
                    <tr><th>Event</th><th>Product</th><th>When</th><th class="text-end pe-3">Actions</th></tr>
                </thead>
                <tbody>
                @forelse($recentEngagements as $eng)
                    @php
                    $ec = [
                        'catalogue_open'=>'primary','product_detail_open'=>'info',
                        'whatsapp_click'=>'success','call_click'=>'warning',
                        'email_click'=>'secondary','enquiry_submit'=>'danger','direct_link'=>'dark',
                        'pdf_share'=>'primary','image_share'=>'info',
                        'whatsapp_pdf_share'=>'success','whatsapp_image_share'=>'success',
                        'pdf_download'=>'secondary','image_download'=>'secondary','copy_link'=>'dark'
                    ][$eng->event_type] ?? 'secondary';
                    @endphp
                    <tr>
                        <td><span class="badge bg-{{ $ec }}-subtle text-{{ $ec }}">{{ str_replace('_', ' ', $eng->event_type) }}</span></td>
                        <td class="small">
                            @if($eng->associated_products && $eng->associated_products->isNotEmpty())
                                <span title="{{ $eng->associated_products->pluck('name')->implode(', ') }}">
                                    {{ Str::limit($eng->associated_products->pluck('name')->implode(', '), 35) }}
                                </span>
                            @else
                                <span class="text-muted">&mdash;</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $eng->created_at->diffForHumans() }}</td>
                        <td class="text-end pe-3">
                            <button class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" style="font-size:0.75rem; border-radius:6px;" 
                                    data-event="{{ $eng->event_type }}" 
                                    data-products="{{ json_encode($eng->associated_products->map(fn($p) => ['name' => $p->name, 'sku' => $p->sku, 'price' => $p->offer_price ?: $p->mrp]) ?? []) }}" 
                                    data-catalogue="{{ $eng->shareLink?->title ?? ($eng->shareLink?->token ?? (is_array($eng->metadata) && !empty($eng->metadata['backfilled_catalogue']) ? $eng->metadata['backfilled_catalogue'] : '')) }}" 
                                    data-time="{{ $eng->created_at->diffForHumans() }}" 
                                    data-meta="{{ json_encode($eng->metadata ?? []) }}" 
                                    onclick="showEventDetails(this)">
                                <i class="bi bi-eye"></i> Details
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No engagement events recorded yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{-- Event Details Modal --}}
    <div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="eventDetailsModalLabel">
                        <i class="bi bi-info-circle text-primary me-2"></i> Event Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-bold text-muted small" style="width: 120px; padding: 6px 0;">Event Type</td>
                                    <td style="padding: 6px 0;"><span id="modalEventBadge" class="badge"></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted small" style="padding: 6px 0;">Product</td>
                                    <td id="modalEventProduct" class="fw-semibold text-dark" style="padding: 6px 0;"></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted small" style="padding: 6px 0;">Catalogue</td>
                                    <td id="modalEventCatalogue" class="text-dark" style="padding: 6px 0;"></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted small" style="padding: 6px 0;">Triggered At</td>
                                    <td id="modalEventTime" class="text-dark" style="padding: 6px 0;"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr class="my-3 opacity-10">
                    <div class="fw-bold text-dark mb-2 small"><i class="bi bi-database me-1"></i> Technical Metadata</div>
                    <div class="bg-light rounded-3 p-3 font-monospace small text-secondary overflow-auto text-start" style="max-height: 200px; font-size: 0.75rem;" id="modalEventMeta">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
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

});

// Event details modal display helper
window.showEventDetails = function(btn) {
    const eventType = btn.getAttribute('data-event');
    const productsStr = btn.getAttribute('data-products') || '[]';
    const catalogue = btn.getAttribute('data-catalogue') || 'N/A';
    const time = btn.getAttribute('data-time');
    const metaStr = btn.getAttribute('data-meta');

    const badge = document.getElementById('modalEventBadge');
    badge.textContent = eventType.replace(/_/g, ' ');
    badge.className = 'badge';

    const ecClass = {
        'catalogue_open': 'bg-primary-subtle text-primary',
        'product_detail_open': 'bg-info-subtle text-info',
        'whatsapp_click': 'bg-success-subtle text-success',
        'call_click': 'bg-warning-subtle text-warning',
        'email_click': 'bg-secondary-subtle text-secondary',
        'enquiry_submit': 'bg-danger-subtle text-danger',
        'direct_link': 'bg-dark-subtle text-dark',
        'pdf_share': 'bg-primary-subtle text-primary',
        'image_share': 'bg-info-subtle text-info',
        'whatsapp_pdf_share': 'bg-success-subtle text-success',
        'whatsapp_image_share': 'bg-success-subtle text-success',
        'pdf_download': 'bg-secondary-subtle text-secondary',
        'image_download': 'bg-secondary-subtle text-secondary',
        'copy_link': 'bg-dark-subtle text-dark'
    }[eventType] || 'bg-secondary-subtle text-secondary';

    badge.classList.add(...ecClass.split(' '));

    // Decode and display associated products nicely
    let products = [];
    try {
        products = JSON.parse(productsStr);
    } catch(e) {}

    const productContainer = document.getElementById('modalEventProduct');
    productContainer.innerHTML = '';
    if (products.length > 0) {
        let prodHtml = '<div class="d-flex flex-column gap-2 text-start w-100">';
        products.forEach(function(p) {
            prodHtml += `
                <div class="p-2 rounded-3 bg-light d-flex flex-column gap-1 border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                    <div class="fw-semibold text-dark small" style="font-size: 0.85rem;">${p.name}</div>
                    <div class="d-flex align-items-center gap-2">
                        ${p.sku ? `<span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.65rem;">SKU: ${p.sku}</span>` : ''}
                        ${p.price ? `<span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 0.65rem;">₹${Number(p.price).toLocaleString('en-IN')}</span>` : ''}
                    </div>
                </div>`;
        });
        prodHtml += '</div>';
        productContainer.innerHTML = prodHtml;
    } else {
        productContainer.innerHTML = '<span class="text-muted">&mdash;</span>';
    }

    document.getElementById('modalEventCatalogue').textContent = catalogue;
    document.getElementById('modalEventTime').textContent = time;

    let meta = {};
    try {
        meta = JSON.parse(metaStr);
    } catch(e) {}

    const metaContainer = document.getElementById('modalEventMeta');
    metaContainer.innerHTML = '';
    if (Object.keys(meta).length > 0) {
        let metaHtml = '<ul class="list-unstyled mb-0 d-flex flex-column gap-1 text-start">';
        for (const [key, value] of Object.entries(meta)) {
            let displayVal = typeof value === 'object' ? JSON.stringify(value) : value;
            metaHtml += `<li><strong class="text-dark">${key}:</strong> ${displayVal}</li>`;
        }
        metaHtml += '</ul>';
        metaContainer.innerHTML = metaHtml;
    } else {
        metaContainer.textContent = 'No additional metadata available';
    }

    const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
    modal.show();
};
</script>
@endpush
