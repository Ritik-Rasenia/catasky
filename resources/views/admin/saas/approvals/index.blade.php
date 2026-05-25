@extends('admin.layouts.app')

@section('title', 'SaaS Approvals Center —')

@push('css')
<style>
    /* Premium glassmorphic styles */
    .saas-header {
        background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 50%, #311042 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        padding: 32px 36px;
        color: white;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
    }
    .saas-header::before {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 250px; height: 250px;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .saas-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }
    .nav-tabs-premium {
        border-bottom: 1px solid #E2E8F0;
        gap: 8px;
    }
    .nav-tabs-premium .nav-link {
        border: none;
        color: #64748B;
        font-weight: 700;
        font-size: 0.88rem;
        padding: 12px 20px;
        border-radius: 12px 12px 0 0;
        position: relative;
        transition: all 0.2s ease;
        background: transparent;
    }
    .nav-tabs-premium .nav-link:hover {
        color: #4F46E5;
        background: rgba(99, 102, 241, 0.04);
    }
    .nav-tabs-premium .nav-link.active {
        color: #4F46E5;
        background: transparent;
    }
    .nav-tabs-premium .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
        background: #4F46E5;
        border-radius: 3px 3px 0 0;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(99, 102, 241, 0.03);
    }
    .count-badge {
        font-size: 0.7rem;
        padding: 2px 7px;
        border-radius: 100px;
        font-weight: 800;
    }
    .count-badge-active {
        background: #4F46E5;
        color: white;
    }
    .count-badge-inactive {
        background: #E2E8F0;
        color: #64748B;
    }
    .thumb-preview {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #E2E8F0;
    }
    .thumb-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94A3B8;
        font-size: 1.1rem;
        border: 1px solid #E2E8F0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="saas-header">
        <div style="position: relative; z-index: 2;">
            <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255, 255, 255, 0.5); margin-bottom: 8px;">
                Catasky SaaS Core
            </div>
            <h2 class="fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">Approvals Center</h2>
            <p class="text-white-50 mb-0" style="max-width: 600px;">
                Double-gate verification system. Review pending stores, product catalogs, and outbound share links before they go public.
            </p>
        </div>
    </div>

    {{-- Tabs Controller --}}
    <div class="saas-card">
        <div class="card-header bg-transparent border-0 p-4 pb-0">
            <ul class="nav nav-tabs nav-tabs-premium" id="approvalTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="stores-tab" data-bs-toggle="tab" data-bs-target="#stores" type="button" role="tab" aria-controls="stores" aria-selected="true">
                        <i class="bi bi-shop me-2"></i>Stores Queue
                        <span class="count-badge ms-2 {{ count($pendingStores) > 0 ? 'count-badge-active' : 'count-badge-inactive' }}">
                            {{ count($pendingStores) }}
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab" aria-controls="products" aria-selected="false">
                        <i class="bi bi-box-seam me-2"></i>Products Queue
                        <span class="count-badge ms-2 {{ count($pendingProducts) > 0 ? 'count-badge-active' : 'count-badge-inactive' }}">
                            {{ count($pendingProducts) }}
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shares-tab" data-bs-toggle="tab" data-bs-target="#shares" type="button" role="tab" aria-controls="shares" aria-selected="false">
                        <i class="bi bi-link-45deg me-2"></i>Share Links Queue
                        <span class="count-badge ms-2 {{ count($pendingShares) > 0 ? 'count-badge-active' : 'count-badge-inactive' }}">
                            {{ count($pendingShares) }}
                        </span>
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content" id="approvalTabsContent">
                
                {{-- 1. Stores Queue Tab --}}
                <div class="tab-pane fade show active" id="stores" role="tabpanel" aria-labelledby="stores-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Store Details</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Subscriber Owner</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Subdomain/Path</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Registration Date</th>
                                    <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingStores as $profile)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            @if($profile->logo)
                                                <img src="{{ $profile->logo_url }}" class="thumb-preview me-3" alt="Logo">
                                            @else
                                                <div class="thumb-placeholder me-3"><i class="bi bi-shop"></i></div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark">{{ $profile->company_name }}</div>
                                                <div class="text-muted small">{{ $profile->phone }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $profile->user->name ?? 'No Owner' }}</div>
                                        <div class="text-muted small">{{ $profile->user->email ?? '' }}</div>
                                    </td>
                                    <td>
                                        <span class="text-primary small fw-semibold">/store/{{ $profile->company_slug }}</span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $profile->created_at->format('M d, Y') }}
                                        <div style="font-size:0.7rem;">{{ $profile->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('admin.subscribers.show', $profile->user_id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                                <i class="bi bi-eye me-1"></i> View Details
                                            </a>
                                            <form action="{{ route('admin.saas.approvals.store.approve', $profile->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                                    <i class="bi bi-check-lg me-1"></i> Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.saas.approvals.store.reject', $profile->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold text-danger">
                                                    <i class="bi bi-x-lg me-1"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-shop text-muted" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                                        No store approval requests are currently in the queue.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 2. Products Queue Tab --}}
                <div class="tab-pane fade" id="products" role="tabpanel" aria-labelledby="products-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Product</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Category</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Store / Owner</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Date Created</th>
                                    <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingProducts as $prod)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            @if($prod->thumbnail)
                                                <img src="{{ asset('uploads/products/'.$prod->thumbnail) }}" class="thumb-preview me-3" alt="Thumb">
                                            @else
                                                <div class="thumb-placeholder me-3"><i class="bi bi-box"></i></div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark">{{ $prod->name }}</div>
                                                <div class="text-muted small">Part Code: {{ $prod->part_code ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark rounded-pill px-2 border">{{ $prod->category->name ?? 'Uncategorized' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $prod->user->subscriberProfile->company_name ?? 'Subscriber' }}</div>
                                        <div class="text-muted small">{{ $prod->user->name ?? '' }}</div>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $prod->created_at->format('M d, Y') }}
                                        <div style="font-size:0.7rem;">{{ $prod->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <form action="{{ route('admin.saas.approvals.product.approve', $prod->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                                    <i class="bi bi-check-lg me-1"></i> Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.saas.approvals.product.reject', $prod->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold text-danger">
                                                    <i class="bi bi-x-lg me-1"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-box-seam text-muted" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                                        No product approval requests are currently in the queue.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 3. Share Links Queue Tab --}}
                <div class="tab-pane fade" id="shares" role="tabpanel" aria-labelledby="shares-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Share Title</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Share Type</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Store / Owner</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Date Generated</th>
                                    <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingShares as $share)
                                <tr>
                                    <td class="ps-4">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $share->title }}</div>
                                            <a href="{{ route('subscriber.share.public', $share->token) }}" target="_blank" class="text-primary small text-decoration-none">
                                                <i class="bi bi-link-45deg me-1"></i>/s/{{ $share->token }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 border text-capitalize">{{ $share->share_type }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $share->user->subscriberProfile->company_name ?? 'Subscriber' }}</div>
                                        <div class="text-muted small">{{ $share->user->name ?? '' }}</div>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $share->created_at->format('M d, Y') }}
                                        <div style="font-size:0.7rem;">{{ $share->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <form action="{{ route('admin.saas.approvals.share.approve', $share->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                                    <i class="bi bi-check-lg me-1"></i> Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.saas.approvals.share.reject', $share->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold text-danger">
                                                    <i class="bi bi-x-lg me-1"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-link-45deg text-muted" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                                        No share page approval requests are currently in the queue.
                                    </td>
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
@endsection
