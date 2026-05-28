@extends('subscriber-panel.layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a> &nbsp;/&nbsp; <span>Notifications</span>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        
        {{-- Header Actions --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark" style="font-family:'Outfit', sans-serif;"><i class="bi bi-bell-fill me-2 text-warning"></i>Notification Center</h5>
            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="markAllRead()">
                <i class="bi bi-check-all me-1"></i> Mark All as Read
            </button>
        </div>

        {{-- Filters Tab --}}
        <div class="vp-card mb-3 p-2 bg-white d-flex align-items-center justify-content-between border rounded-4">
            <div class="d-flex gap-2">
                <a href="{{ route('subscriber.notifications.index', ['filter' => 'all']) }}" class="btn btn-sm rounded-pill px-3 fw-bold {{ $filter === 'all' ? 'btn-primary' : 'btn-light border' }}">
                    All
                </a>
                <a href="{{ route('subscriber.notifications.index', ['filter' => 'unread']) }}" class="btn btn-sm rounded-pill px-3 fw-bold {{ $filter === 'unread' ? 'btn-primary' : 'btn-light border' }}">
                    Unread
                </a>
                <a href="{{ route('subscriber.notifications.index', ['filter' => 'read']) }}" class="btn btn-sm rounded-pill px-3 fw-bold {{ $filter === 'read' ? 'btn-primary' : 'btn-light border' }}">
                    Read
                </a>
            </div>
            <span class="text-muted extra-small pe-2">Showing latest items first</span>
        </div>

        {{-- Notifications List --}}
        <div class="vp-card">
            <div class="vp-card-body p-0">
                <div class="d-flex flex-column" id="notifications-list">
                    @forelse($notifications as $notif)
                        @php
                            $notifData = $notif->data;
                            $icon = $notifData['icon'] ?? 'bi-info-circle';
                            $title = $notifData['title'] ?? 'System Update';
                            $msg = $notifData['message'] ?? '';
                            $redirectUrl = route('subscriber.notifications.redirect', $notif->id);
                            $isRead = !is_null($notif->read_at);
                        @endphp
                        <div class="d-flex align-items-start justify-content-between p-4 border-bottom position-relative notification-item {{ $isRead ? '' : 'unread-bg' }}" id="notif-{{ $notif->id }}" style="transition:background 0.2s;">
                            <a href="{{ $redirectUrl }}" class="d-flex gap-3 text-decoration-none w-100 min-w-0 align-items-start">
                                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width:42px; height:42px; background: rgba(79, 70, 229, 0.08); color: var(--subscriber-primary);">
                                    <i class="bi {{ $icon }} fs-5"></i>
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-bold text-dark small d-flex align-items-center gap-2">
                                        {{ $title }}
                                        @if(!$isRead)
                                            <span class="badge bg-danger rounded-circle p-1" style="width:7px; height:7px;"></span>
                                        @endif
                                    </div>
                                    <div class="text-muted extra-small mt-1 text-wrap" style="line-height:1.5;">{{ $msg }}</div>
                                    <div class="text-muted mt-2 d-flex align-items-center gap-1" style="font-size: 0.68rem;">
                                        <i class="bi bi-clock"></i> {{ $notif->created_at->format('M d, Y · h:i A') }}
                                        <span class="mx-1">•</span>
                                        <span>{{ $notif->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </a>
                            @if(!$isRead)
                                <button class="btn btn-link btn-sm text-secondary p-0 ms-2 text-decoration-none" onclick="markSingleRead('{{ $notif->id }}')" title="Mark as Read">
                                    <i class="bi bi-check-lg fs-5"></i>
                                </button>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted empty-state my-4">
                            <div class="empty-state-icon bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 72px; height: 72px; font-size: 2rem;">
                                <i class="bi bi-bell-slash"></i>
                            </div>
                            <h6 class="fw-bold text-dark">No Notifications Found</h6>
                            <p class="text-muted extra-small">No notification updates matched the selected filter.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if($notifications->hasPages())
                <div class="p-3 border-top d-flex justify-content-center admin-pagination-wrap">
                    {{ $notifications->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>

    </div>
</div>


@endsection

@push('js')
<script>
    function markSingleRead(id) {
        event.stopPropagation();
        event.preventDefault();
        
        const url = `{{ url('dashboard/notifications') }}/${id}/read`;
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.success) {
                    $(`#notif-${id}`).removeClass('unread-bg');
                    $(`#notif-${id} .badge`).remove();
                    $(`#notif-${id} button`).remove();
                    window.alertService.toastSuccess('Notification marked as read.');
                    
                    // Update header bell counter
                    const badge = $('.pulse-badge');
                    if (badge.length) {
                        let count = parseInt(badge.text().trim());
                        count = Math.max(0, count - 1);
                        if (count === 0) {
                            badge.remove();
                            $('#notifDropdown .pulse-badge').remove();
                        } else {
                            badge.text(count);
                        }
                    }
                }
            }
        });
    }

    function markAllRead() {
        const url = `{{ route('subscriber.notifications.markAllRead') }}`;
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.success) {
                    $('.notification-item').removeClass('unread-bg');
                    $('.notification-item .badge').remove();
                    $('.notification-item button').remove();
                    $('.pulse-badge').remove();
                    $('#notifDropdown .pulse-badge').remove();
                    window.alertService.toastSuccess('All notifications marked as read.');
                }
            }
        });
    }
</script>
@endpush
