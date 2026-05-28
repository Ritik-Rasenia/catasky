@extends('admin.layouts.app')

@section('title', 'Subscriber Custom Fields Approvals')
@section('page-title', 'Subscriber Custom Fields Approvals')
@section('breadcrumb', 'SaaS Management → Pending Approvals → Custom Fields')

@section('content')
<div class="card  border-0" style="border-radius:16px;">
    <div class="card-header bg-white py-3 border-0" style="border-top-left-radius:16px; border-top-right-radius:16px;">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-check-fill me-2 text-warning"></i>Pending Custom Fields Reviews</h6>
    </div>
    <div class="card-body p-0">
        @if($pendingAttributes->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase fs-11 text-muted fw-bold">Subscriber</th>
                        <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Attribute Details</th>
                        <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Field Type</th>
                        <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Group</th>
                        <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Date Submitted</th>
                        <th class="pe-4 py-3 text-end text-uppercase fs-11 text-muted fw-bold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingAttributes as $attr)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $attr->subscriber?->name ?? 'Unknown Subscriber' }}</div>
                            <div class="text-muted fs-12">{{ $attr->subscriber?->email ?? '' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $attr->name }}</div>
                            <div class="text-muted fs-12">{{ $attr->slug }} @if($attr->unit)<span class="badge bg-light text-dark">Unit: {{ $attr->unit }}</span>@endif</div>
                        </td>
                        <td>
                            <code class="text-primary">{{ strtoupper($attr->type) }}</code>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1" style="font-size:0.75rem;">
                                {{ $attr->group?->name ?? 'General' }}
                            </span>
                        </td>
                        <td>
                            {{ $attr->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-inline-flex gap-2">
                                <form action="{{ route('admin.attributes.approve', $attr->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success px-3" style="border-radius:8px; font-size:0.8rem;">
                                        <i class="bi bi-check-lg"></i> Approve & Globalize
                                    </button>
                                </form>
                                <form action="{{ route('admin.attributes.reject', $attr->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3" style="border-radius:8px; font-size:0.8rem;">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
            {{ $pendingAttributes->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-shield-check text-muted fs-1 mb-3" style="color:#10B981 !important;"></i>
            <h5 class="fw-bold text-dark">No custom fields pending review</h5>
            <p class="text-muted fs-13 max-width-360 mx-auto">Whenever subscribers request limited custom specifications, their requests will appear here for review and global promotion to prevent attribute chaos.</p>
        </div>
        @endif
    </div>
</div>
@endsection
