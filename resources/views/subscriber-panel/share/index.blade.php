@extends('subscriber-panel.layouts.app')

@section('title', 'Share Links')
@section('page-title', 'Share Links')
@section('breadcrumb', 'Manage active share links for products and catalogs')

@section('content')

<div class="vp-page-header">
    <div>
        <h1 class="vp-page-title">Share Links</h1>
    </div>
    <div>
        <a href="{{ route('subscriber.share.create') }}" class="btn-subscriber">
            <i class="bi bi-plus-lg"></i> Create Share Link
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-12">
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-share-fill me-2"></i>Active & Past Shares</h6>
            </div>
            <div class="vp-card-body p-0">
                @if($links->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">🔗</div>
                    <div class="empty-state-title">No Share Links Yet</div>
                    <div class="empty-state-text">Generate secure, shareable web links or premium PDFs of your products or whole catalog to send to customers via WhatsApp or email.</div>
                    <a href="{{ route('subscriber.share.create') }}" class="btn-subscriber">
                        <i class="bi bi-plus-lg"></i> Create Share Link
                    </a>
                </div>
                @else
                <div class="table-responsive">
                    <table class="vp-table">
                        <thead>
                            <tr>
                                <th>Share Title</th>
                                <th>Type</th>
                                <th>Linked Product</th>
                                <th>Views / Downloads</th>
                                <th>Expiry Status</th>
                                <th style="width: 200px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($links as $link)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span style="font-weight:600;color:#0F172A;">{{ $link->title }}</span>
                                    </div>
                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        <code style="font-size:0.75rem;color:var(--subscriber-primary);">{{ route('subscriber.share.public', $link->token) }}</code>
                                        <button type="button" class="btn btn-sm btn-link p-0" onclick="copyToClipboard('{{ route('subscriber.share.public', $link->token) }}')" title="Copy Link" style="color:var(--subscriber-primary);">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $typeIcons = [
                                            'pdf' => 'bi-file-pdf-fill text-danger',
                                            'image' => 'bi-image text-success',
                                            'catalog' => 'bi-journal-richtext text-primary',
                                            'whatsapp' => 'bi-whatsapp text-success'
                                        ];
                                        $icon = $typeIcons[$link->type] ?? 'bi-link';
                                    @endphp
                                    <span class="d-inline-flex align-items-center gap-1" style="font-weight:600;font-size:0.78rem;">
                                        <i class="bi {{ $icon }}"></i> {{ strtoupper($link->type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($link->product)
                                        <a href="{{ route('subscriber.products.show', $link->product->id) }}" style="color:var(--subscriber-primary);font-weight:500;">
                                            {{ $link->product->name }}
                                        </a>
                                    @else
                                        <span class="badge" style="background:#F1F5F9;color:#475569;font-weight:600;">Whole Catalog</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column" style="font-size:0.8rem;">
                                        <span><i class="bi bi-eye-fill text-muted me-1"></i>{{ $link->view_count }} views</span>
                                        <span><i class="bi bi-download text-muted me-1"></i>{{ $link->download_count }} downloads</span>
                                    </div>
                                </td>
                                <td>
                                    @if($link->is_expired)
                                        <span class="badge badge-inactive">Expired</span>
                                    @else
                                        <span class="badge badge-active">Active</span>
                                        @if($link->expires_at)
                                            <div style="font-size:0.7rem;color:#94A3B8;margin-top:2px;">Expires: {{ $link->expires_at->format('M d, Y') }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('subscriber.share.show', $link->id) }}" class="btn btn-sm" style="border-radius:8px;background:#F8FAFC;border:1px solid #E2E8F0;color:#475569;" title="View Details">
                                            <i class="bi bi-eye"></i> Details
                                        </a>
                                        <form action="{{ route('subscriber.share.destroy', $link->id) }}" method="POST" id="del-share-{{ $link->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm" style="border-radius:8px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2);color:#EF4444;"
                                                    onclick="confirmDelete('del-share-{{ $link->id }}', 'Delete this share link? Users will no longer be able to access the files via this URL.')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        
        @if($links->hasPages())
        <div class="mt-3">
            {{ $links->links() }}
        </div>
        @endif
    </div>
</div>

@endsection

@push('js')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500
        });
        Toast.fire({
            icon: 'success',
            title: 'Share link copied to clipboard!'
        });
    });
}
</script>
@endpush
