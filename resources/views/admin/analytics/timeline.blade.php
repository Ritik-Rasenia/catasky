@extends(isset($isAdmin) && $isAdmin === false ? 'subscriber-panel.layouts.app' : 'admin.layouts.app')

@section('title', 'Visitor Activity Timeline')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-diagram-3 text-primary"></i> Visitor Journey Timeline</h1>
            <p class="text-muted mb-0 small">
                Visitor ID: <code>{{ $visitorUuid }}</code>
                &mdash; Complete journey from Share to Order
            </p>
        </div>
        <div>
            @if(isset($isAdmin) && $isAdmin === false)
                <a href="{{ route('subscriber.analytics') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Analytics</a>
            @else
                <a href="{{ route('admin.analytics') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Analytics</a>
            @endif
        </div>
    </div>

    {{-- Journey Flow Summary --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="badge bg-secondary-subtle text-secondary fs-6 px-3 py-2"><i class="bi bi-share-fill"></i> Share</span>
                <i class="bi bi-arrow-right text-muted"></i>
                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2"><i class="bi bi-eye-fill"></i> Open</span>
                <i class="bi bi-arrow-right text-muted"></i>
                <span class="badge bg-info-subtle text-info fs-6 px-3 py-2"><i class="bi bi-box-seam-fill"></i> Product View</span>
                <i class="bi bi-arrow-right text-muted"></i>
                <span class="badge bg-success-subtle text-success fs-6 px-3 py-2"><i class="bi bi-download"></i> Download</span>
                <i class="bi bi-arrow-right text-muted"></i>
                <span class="badge bg-warning-subtle text-warning fs-6 px-3 py-2"><i class="bi bi-chat-left-dots-fill"></i> Enquiry</span>
                <i class="bi bi-arrow-right text-muted"></i>
                <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2"><i class="bi bi-bag-check-fill"></i> Order</span>
            </div>
        </div>
    </div>

    {{-- Event Count Summary --}}
    <div class="row g-3 mb-4">
        @php
            $counts = [
                'Shares' => $events->where('type','share')->count(),
                'Opens' => $events->where('type','visit')->count(),
                'Product Views' => $events->where('type','product_view')->count(),
                'Downloads' => $events->where('type','download')->count(),
                'Enquiries' => $events->where('type','enquiry')->count(),
                'Orders' => $events->where('type','order')->count(),
            ];
            $colorMap = ['Shares'=>'secondary','Opens'=>'primary','Product Views'=>'info','Downloads'=>'success','Enquiries'=>'warning','Orders'=>'danger'];
        @endphp
        @foreach($counts as $label => $count)
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fw-bold fs-4 text-{{ $colorMap[$label] }}">{{ $count }}</div>
                <div class="small text-muted">{{ $label }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Timeline --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 fw-bold"><i class="bi bi-clock-history text-primary"></i> Chronological Events</div>
        <div class="card-body">
            @if($events->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox display-1 d-block mb-3 opacity-25"></i>
                    <h5>No activity recorded for this visitor</h5>
                    <p class="small">This visitor may not have interacted with any shared catalogue.</p>
                </div>
            @else
                <div class="position-relative">
                    {{-- Vertical line --}}
                    <div class="position-absolute" style="left:24px;top:0;bottom:0;width:2px;background:linear-gradient(to bottom, var(--bs-primary), var(--bs-danger));opacity:0.2;"></div>

                    @foreach($events as $event)
                    <div class="d-flex gap-3 mb-4 position-relative" style="padding-left:0;">
                        {{-- Icon Circle --}}
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-{{ $event['color'] }}-subtle border border-2 border-{{ $event['color'] }}"
                             style="width:48px;height:48px;min-width:48px;z-index:1;">
                            <i class="bi {{ $event['icon'] }} text-{{ $event['color'] }}"></i>
                        </div>

                        {{-- Content --}}
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-{{ $event['color'] }}-subtle text-{{ $event['color'] }} me-2">{{ ucfirst(str_replace('_',' ',$event['type'])) }}</span>
                                    <strong>{{ $event['title'] }}</strong>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i>
                                    {{ $event['time'] ? \Carbon\Carbon::parse($event['time'])->format('d M Y, h:i A') : 'N/A' }}
                                    @if($event['time'])
                                        <span class="ms-1 text-muted">({{ \Carbon\Carbon::parse($event['time'])->diffForHumans() }})</span>
                                    @endif
                                </small>
                            </div>
                            <p class="mb-0 mt-1 small text-secondary">{{ $event['description'] }}</p>

                            {{-- Extra details for visit events --}}
                            @if($event['type'] === 'visit' && isset($event['data']))
                                @php $visit = $event['data']; @endphp
                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    <span class="badge bg-light text-dark border"><i class="bi bi-globe"></i> {{ $visit->country }}, {{ $visit->city }}</span>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-laptop"></i> {{ $visit->device_type }}</span>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-window"></i> {{ $visit->browser }} / {{ $visit->os }}</span>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-clock"></i> {{ $visit->total_time_spent }}s</span>
                                    @if($visit->bounce)
                                        <span class="badge bg-danger-subtle text-danger">Bounced</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">Engaged</span>
                                    @endif
                                    @if($visit->referrer)
                                        <span class="badge bg-light text-dark border"><i class="bi bi-link-45deg"></i> {{ Str::limit($visit->referrer, 40) }}</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Extra details for share events --}}
                            @if($event['type'] === 'share' && isset($event['data']))
                                @php $share = $event['data']; @endphp
                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    <span class="badge bg-light text-dark border"><i class="bi bi-share"></i> {{ ucfirst(str_replace('_',' ',$share->channel)) }}</span>
                                    @if($share->product)
                                        <span class="badge bg-light text-dark border"><i class="bi bi-box-seam"></i> {{ Str::limit($share->product->name ?? '', 30) }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
