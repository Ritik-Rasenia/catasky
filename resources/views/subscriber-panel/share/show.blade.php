@extends('subscriber-panel.layouts.app')

@section('title', 'Share Link Details')
@section('page-title', 'Share Link Details')
@section('breadcrumb', '<a href="' . route('subscriber.share.index') . '">Share Links</a> → Details')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 mb-4" style="background:#DCFCE7;color:#166534;border-radius:12px;" role="alert">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>
            <strong>Success!</strong> {{ session('success') }}
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row g-3">
    {{-- Left: Link Copy & QR Code --}}
    <div class="col-lg-8">
        <div class="vp-card mb-3">
            <div class="vp-card-body text-center p-4">
                <div style="font-size:3rem;margin-bottom:12px;">🔗</div>
                <h4 style="font-family:'Outfit',sans-serif;font-weight:700;color:var(--text-primary);">Your Share Link is Ready!</h4>
                <p style="color:#64748B;font-size:0.875rem;">Send this link to your clients to view your catalog in a stunning custom layout.</p>

                <div class="my-4" style="background:#F8FAFC;border-radius:12px;border:1px solid #E2E8F0;padding:16px;">
                    <div style="font-weight:600;color:#64748B;font-size:0.75rem;text-transform:uppercase;margin-bottom:8px;">Public Catalog URL</div>
                    <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                        <code style="font-family:monospace;font-size:0.95rem;color:var(--subscriber-primary);font-weight:700;">{{ route('subscriber.share.public', $shareLink->token) }}</code>
                        <button type="button" class="btn-subscriber" style="padding:6px 14px;font-size:0.78rem;" onclick="copyLink('{{ route('subscriber.share.public', $shareLink->token) }}')">
                            <i class="bi bi-copy"></i> Copy Link
                        </button>
                    </div>
                </div>

                {{-- QR Code Section --}}
                <div class="d-flex flex-column align-items-center justify-content-center mt-3">
                    <div style="background:white;padding:12px;border-radius:12px;border:1px solid #E2E8F0;display:inline-block;">
                        {!! QrCode::size(160)->generate(route('subscriber.share.public', $shareLink->token)) !!}
                    </div>
                    <div class="mt-2" style="font-size:0.78rem;color:#94A3B8;">Scan QR code to open catalog</div>
                </div>

                {{-- Quick Actions --}}
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-4 pt-3 border-top" style="border-color:#F1F5F9;">
                    <a href="https://api.whatsapp.com/send?text={{ urlencode('View our product catalog: ' . route('subscriber.share.public', $shareLink->token)) }}" target="_blank" class="btn-subscriber-outline" style="border-color:#25D366;color:#25D366;background:transparent;">
                        <i class="bi bi-whatsapp"></i> Share via WhatsApp
                    </a>
                    <a href="mailto:?subject={{ urlencode($shareLink->title) }}&body={{ urlencode('Check out our catalog: ' . route('subscriber.share.public', $shareLink->token)) }}" class="btn-subscriber-outline" style="border-color:#64748B;color:#64748B;background:transparent;">
                        <i class="bi bi-envelope"></i> Send via Email
                    </a>
                    <a href="{{ route('subscriber.share.public', $shareLink->token) }}" target="_blank" class="btn-subscriber" style="background:var(--subscriber-primary);border:none;">
                        <i class="bi bi-box-arrow-up-right"></i> Open Live Preview
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- Right: Details & Stats --}}
    <div class="col-lg-4">
        {{-- Settings summary --}}
        <div class="vp-card mb-3">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-gear-fill me-2"></i>Link Settings</h6>
            </div>
            <div class="vp-card-body p-0">
                <table class="vp-table">
                    <tbody>
                        <tr>
                            <td>Target Scope</td>
                            <td class="text-end" style="font-weight:600;">
                                @if($shareLink->product)
                                    Single Product
                                @else
                                    Whole Catalog
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Share Type</td>
                            <td class="text-end" style="font-weight:600;text-transform:uppercase;">
                                {{ $shareLink->type }}
                            </td>
                        </tr>
                        <tr>
                            <td>Created At</td>
                            <td class="text-end" style="font-size:0.8rem;">
                                {{ $shareLink->created_at->format('M d, Y H:i') }}
                            </td>
                        </tr>
                        <tr>
                            <td>Expires At</td>
                            <td class="text-end" style="font-size:0.8rem;font-weight:600;">
                                {{ $shareLink->expires_at ? $shareLink->expires_at->format('M d, Y H:i') : 'Permanent' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Visibility Rules --}}
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-eye-fill me-2"></i>Visibility Configuration</h6>
            </div>
            <div class="vp-card-body p-0">
                <table class="vp-table">
                    <tbody>
                        @foreach($shareLink->settings ?? [] as $field => $val)
                        <tr>
                            <td>{{ ucwords(str_replace('_', ' ', $field)) }}</td>
                            <td class="text-end">
                                <span class="badge {{ $val ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $val ? 'Yes' : 'No' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
function copyLink(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Public share URL successfully copied.',
            showConfirmButton: false,
            timer: 1500
        });
        // Track copy link engagement
        _trackEngagement('copy_link');
    });
}

// Share button engagement tracking
(function() {
    const SHARE_TOKEN = '{{ $shareLink->token }}';
    const TRACK_TOKEN = '{{ $trackingToken ?? '' }}';
    const CSRF = '{{ csrf_token() }}';
    const API_BASE = '/api/analytics';

    function _trackEngagement(eventType) {
        fetch(API_BASE + '/engagement', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                token: SHARE_TOKEN,
                track_token: TRACK_TOKEN || null,
                event_type: eventType
            })
        }).catch(function() {});
    }

    // WhatsApp share button
    document.querySelectorAll('a[href*="api.whatsapp.com"]').forEach(function(el) {
        el.addEventListener('click', function() { _trackEngagement('whatsapp_click'); });
    });

    // Email share button
    document.querySelectorAll('a[href^="mailto:"]').forEach(function(el) {
        el.addEventListener('click', function() { _trackEngagement('email_click'); });
    });

    // Open Live Preview (direct link)
    document.querySelectorAll('a[target="_blank"]').forEach(function(el) {
        if (!el.href.includes('whatsapp') && !el.href.startsWith('mailto:')) {
            el.addEventListener('click', function() { _trackEngagement('direct_link'); });
        }
    });

    window._trackEngagement = _trackEngagement;
})();
</script>
@endpush
