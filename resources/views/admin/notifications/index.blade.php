@extends('admin.layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a> &nbsp;/&nbsp; <span>Notifications</span>
@endsection

@push('css')
<style>
    /* ===== ADMIN NOTIFICATION CENTER ===== */
    .notif-page-wrap {
        max-width: 100%;
        margin: 0 auto;
    }

    /* Header */
    .notif-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .notif-page-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-color);
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }
    .notif-page-title .title-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(79,70,229,0.12), rgba(124,58,237,0.12));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4F46E5;
        font-size: 1.1rem;
    }

    /* Filter Bar */
    .notif-filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 8px 14px 8px 8px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .notif-filter-tabs {
        display: flex;
        gap: 4px;
    }
    .notif-filter-tabs .filter-btn {
        padding: 7px 18px;
        border-radius: 10px;
        border: none;
        font-size: 0.82rem;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        cursor: pointer;
        transition: all 0.18s;
        text-decoration: none;
    }
    .notif-filter-tabs .filter-btn.active {
        background: #4F46E5;
        color: white;
        box-shadow: 0 2px 10px rgba(79,70,229,0.25);
    }
    .notif-filter-tabs .filter-btn:not(.active) {
        background: transparent;
        color: var(--text-muted);
        border: 1.5px solid var(--border-color);
    }
    .notif-filter-tabs .filter-btn:not(.active):hover {
        background: rgba(79,70,229,0.06);
        color: #4F46E5;
        border-color: rgba(79,70,229,0.2);
    }

    /* Notification List */
    .notif-list-card {
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }
    .notif-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 18px 20px;
        border-bottom: 1px solid var(--border-color);
        transition: background 0.18s;
        position: relative;
    }
    .notif-item:last-child {
        border-bottom: none;
    }
    .notif-item:hover {
        background: rgba(79, 70, 229, 0.02);
    }
    .notif-item.unread {
        background: rgba(79, 70, 229, 0.018);
    }
    .notif-item.unread::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, #4F46E5, #7C3AED);
        border-radius: 0 2px 2px 0;
    }
    .notif-item-link {
        display: flex;
        gap: 14px;
        text-decoration: none;
        flex: 1;
        min-width: 0;
        align-items: flex-start;
    }
    .notif-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: rgba(79, 70, 229, 0.09);
        color: #4F46E5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        transition: transform 0.18s;
    }
    .notif-item:hover .notif-icon-wrap {
        transform: scale(1.06);
    }
    .notif-item.unread .notif-icon-wrap {
        background: rgba(79, 70, 229, 0.12);
        box-shadow: 0 2px 10px rgba(79,70,229,0.15);
    }
    .notif-body {
        min-width: 0;
        flex: 1;
    }
    .notif-title-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }
    .notif-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-color);
        line-height: 1.35;
    }
    .notif-unread-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #EF4444;
        flex-shrink: 0;
        animation: dotPulse 2s infinite;
    }
    @keyframes dotPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .notif-msg {
        font-size: 0.8rem;
        color: var(--text-muted);
        line-height: 1.55;
        margin-bottom: 6px;
    }
    .notif-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.7rem;
        color: var(--text-muted);
        opacity: 0.75;
    }
    /* Mark-read button */
    .btn-mark-read {
        background: none;
        border: none;
        padding: 6px;
        color: var(--text-muted);
        cursor: pointer;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.18s;
        flex-shrink: 0;
        margin-left: 8px;
    }
    .btn-mark-read:hover {
        background: rgba(79, 70, 229, 0.08);
        color: #4F46E5;
    }
    .btn-mark-read i { font-size: 1.15rem; }

    /* Mark all read button */
    .btn-mark-all {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 10px;
        border: 1.5px solid rgba(79,70,229,0.25);
        background: rgba(79,70,229,0.05);
        color: #4F46E5;
        font-size: 0.8rem;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        cursor: pointer;
        transition: all 0.18s;
        white-space: nowrap;
    }
    .btn-mark-all:hover {
        background: #4F46E5;
        color: white;
        border-color: #4F46E5;
        box-shadow: 0 4px 14px rgba(79,70,229,0.25);
    }

    /* Empty State */
    .notif-empty {
        text-align: center;
        padding: 64px 24px;
    }
    .notif-empty-icon {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background: rgba(79,70,229,0.06);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        font-size: 2rem;
        color: #A5B4FC;
    }
    .notif-empty h6 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: var(--text-color);
        margin-bottom: 6px;
    }
    .notif-empty p {
        color: var(--text-muted);
        font-size: 0.82rem;
        margin: 0;
    }

    /* Pagination */
    .notif-pagination {
        padding: 14px 20px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: center;
    }

    /* Dark mode */
    html[data-theme="dark"] .notif-item:hover {
        background: rgba(79, 70, 229, 0.05);
    }
    html[data-theme="dark"] .notif-item.unread {
        background: rgba(79, 70, 229, 0.04);
    }
    html[data-theme="dark"] .notif-icon-wrap {
        background: rgba(79, 70, 229, 0.15);
    }

    @media (max-width: 576px) {
        .notif-page-wrap { max-width: 100%; }
        .notif-item { padding: 14px 16px; }
        .notif-filter-bar { padding: 6px 10px 6px 6px; }
    }
</style>
@endpush

@section('content')
<div class="notif-page-wrap">

    {{-- Header --}}
    <div class="notif-page-header">
        <h5 class="notif-page-title">
            <span class="title-icon"><i class="bi bi-bell-fill"></i></span>
            Notification Center
        </h5>
        <button class="btn-mark-all" onclick="markAllRead()">
            <i class="bi bi-check-all"></i> Mark All as Read
        </button>
    </div>

    {{-- Filter Bar --}}
    <div class="notif-filter-bar">
        <div class="notif-filter-tabs">
            <a href="{{ route('admin.notifications.index', ['filter' => 'all']) }}"
               class="filter-btn {{ $filter === 'all' ? 'active' : '' }}">
                All
                @php $totalCount = auth()->user()->notifications()->count(); @endphp
                @if($totalCount > 0)
                    <span style="margin-left:4px; opacity:0.7; font-weight:600;">({{ $totalCount }})</span>
                @endif
            </a>
            <a href="{{ route('admin.notifications.index', ['filter' => 'unread']) }}"
               class="filter-btn {{ $filter === 'unread' ? 'active' : '' }}">
                Unread
                @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                @if($unreadCount > 0)
                    <span style="margin-left:4px; font-size:0.7rem; background:rgba(239,68,68,0.12); color:#DC2626; padding:1px 6px; border-radius:100px; font-weight:800;">{{ $unreadCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.notifications.index', ['filter' => 'read']) }}"
               class="filter-btn {{ $filter === 'read' ? 'active' : '' }}">
                Read
            </a>
        </div>
        <span class="text-muted" style="font-size:0.75rem;">Latest first</span>
    </div>

    {{-- Notification List --}}
    <div class="notif-list-card">
        <div id="notifications-list">
            @forelse($notifications as $notif)
                @php
                    $notifData = $notif->data;
                    $icon      = $notifData['icon']    ?? 'bi-bell';
                    $title     = $notifData['title']   ?? 'System Notification';
                    $msg       = $notifData['message'] ?? '';
                    $redirectUrl = route('admin.notifications.redirect', $notif->id);
                    $isRead    = !is_null($notif->read_at);
                @endphp
                <div class="notif-item {{ $isRead ? '' : 'unread' }}" id="notif-{{ $notif->id }}">
                    <a href="{{ $redirectUrl }}" class="notif-item-link">
                        <div class="notif-icon-wrap">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <div class="notif-body">
                            <div class="notif-title-row">
                                <span class="notif-title">{{ $title }}</span>
                                @if(!$isRead)
                                    <span class="notif-unread-dot"></span>
                                @endif
                            </div>
                            <div class="notif-msg">{{ $msg }}</div>
                            <div class="notif-meta">
                                <i class="bi bi-clock"></i>
                                {{ $notif->created_at->format('M d, Y · h:i A') }}
                                <span>•</span>
                                <span>{{ $notif->created_at->diffForHumans() }}</span>
                                @if($isRead)
                                    <span>•</span>
                                    <span style="color:#10B981;"><i class="bi bi-check2-all me-1"></i>Read</span>
                                @endif
                            </div>
                        </div>
                    </a>
                    @if(!$isRead)
                        <button class="btn-mark-read"
                                onclick="markSingleRead('{{ $notif->id }}')"
                                title="Mark as Read">
                            <i class="bi bi-check-lg"></i>
                        </button>
                    @endif
                </div>
            @empty
                <div class="notif-empty">
                    <div class="notif-empty-icon"><i class="bi bi-bell-slash"></i></div>
                    <h6>No Notifications Found</h6>
                    <p>
                        @if($filter === 'unread')
                            You're all caught up — no unread notifications.
                        @elseif($filter === 'read')
                            No read notifications to display.
                        @else
                            No notification updates to show yet.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="notif-pagination admin-pagination-wrap">
                {{ $notifications->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('js')
<script>
    function markSingleRead(id) {
        event.stopPropagation();
        event.preventDefault();

        const url = `{{ url('dashboard/admin/notifications') }}/${id}/read`;

        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                if (res.success) {
                    const item = document.getElementById('notif-' + id);
                    if (item) {
                        item.classList.remove('unread');
                        // Remove unread dot
                        const dot = item.querySelector('.notif-unread-dot');
                        if (dot) dot.remove();
                        // Remove mark-read button
                        const btn = item.querySelector('.btn-mark-read');
                        if (btn) btn.remove();
                        // Add "Read" meta tag
                        const meta = item.querySelector('.notif-meta');
                        if (meta && !meta.querySelector('.read-label')) {
                            meta.insertAdjacentHTML('beforeend', `
                                <span>•</span>
                                <span class="read-label" style="color:#10B981;"><i class="bi bi-check2-all me-1"></i>Read</span>
                            `);
                        }
                    }
                    // Update header bell badge
                    const badge = document.querySelector('.pulse-badge');
                    if (badge) {
                        let count = parseInt(badge.textContent.trim());
                        count = Math.max(0, count - 1);
                        if (count === 0) {
                            badge.remove();
                        } else {
                            badge.textContent = count;
                        }
                    }
                    // Update filter tab unread count
                    window.alertService.toastSuccess('Notification marked as read.');
                }
            }
        });
    }

    function markAllRead() {
        const url = `{{ route('admin.notifications.markAllRead') }}`;

        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                if (res.success) {
                    document.querySelectorAll('.notif-item.unread').forEach(item => {
                        item.classList.remove('unread');
                        const dot = item.querySelector('.notif-unread-dot');
                        if (dot) dot.remove();
                        const btn = item.querySelector('.btn-mark-read');
                        if (btn) btn.remove();
                    });
                    // Remove header badge
                    const badge = document.querySelector('.pulse-badge');
                    if (badge) badge.remove();
                    window.alertService.toastSuccess('All notifications marked as read.');
                }
            }
        });
    }
</script>
@endpush
