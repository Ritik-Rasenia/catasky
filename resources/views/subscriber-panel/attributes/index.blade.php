@extends('subscriber-panel.layouts.app')

@section('title', 'Product Specifications & Attributes')
@section('page-title', 'Specifications & Attributes')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Attributes</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="row g-3">
    {{-- Left: Attributes List --}}
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-sliders me-2 text-primary"></i>My Specifications & Attributes</h6>
                <div class="d-inline-flex gap-2">
                    <a href="{{ route('subscriber.attributes.create') }}" class="btn btn-sm btn-primary" style="border-radius:8px;font-size:0.75rem; display:inline-flex; align-items:center; height:32px;">
                        <i class="bi bi-plus-lg me-1"></i> Add Custom Attribute
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="p-3 border-bottom bg-white">
                    <form method="GET" class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search attributes by name..." style="border-radius:10px;">
                        </div>
                        <div class="col-md-4">
                            <select name="group_id" class="form-select" style="border-radius:10px;">
                                <option value="">All Groups</option>
                                @foreach($groups as $grp)
                                <option value="{{ $grp->id }}" {{ request('group_id') == $grp->id ? 'selected' : '' }}>{{ $grp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 text-end">
                            <div class="d-inline-flex w-100 justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius:8px; height:36px;">Filter</button>
                                <a href="{{ route('subscriber.attributes.index') }}" class="btn btn-light btn-sm ms-2 px-3" style="border-radius:8px; height:36px; display:inline-flex; align-items:center;">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                
                @if($attributes->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="font-size:0.875rem;" id="attributes-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:36px;"></th>
                                <th class="ps-4 py-3 text-uppercase fs-11 text-muted fw-bold">Name</th>
                                <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Group</th>
                                <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Type</th>
                                <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Configuration</th>
                                <th class="py-3 text-uppercase fs-11 text-muted fw-bold text-center">Approval</th>
                                <th class="pe-4 py-3 text-end text-uppercase fs-11 text-muted fw-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-attributes">
                            @foreach($attributes as $attr)
                            <tr data-id="{{ $attr->id }}" class="align-middle">
                                <td style="cursor:grab;color:#CBD5E1;text-align:center;">
                                    <i class="bi bi-grip-vertical"></i>
                                </td>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $attr->name }}</div>
                                    <div class="text-muted fs-12 d-flex align-items-center gap-1.5 mt-0.5">
                                        <code>{{ $attr->slug }}</code>
                                        @if($attr->unit)
                                            <span class="badge bg-light text-secondary border">Unit: {{ $attr->unit }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1" style="font-size:0.75rem;">
                                        {{ $attr->group?->name ?? 'General' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $typeColors = [
                                            'text' => '#4F46E5', 'number' => '#0891B2', 'select' => '#7C3AED',
                                            'multiselect' => '#9333EA', 'checkbox' => '#DC2626', 'radio' => '#D97706',
                                            'textarea' => '#059669', 'image' => '#EC4899', 'file' => '#64748B',
                                            'color' => '#F59E0B', 'date' => '#0284C7', 'url' => '#16A34A',
                                        ];
                                        $color = $typeColors[$attr->type] ?? '#64748B';
                                    @endphp
                                    <span style="background:{{ $color }}12; color:{{ $color }}; border-radius:6px; padding:3px 10px; font-size:0.72rem; font-weight:700;">
                                        {{ strtoupper($attr->type) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($attr->is_required) <span class="badge bg-danger-subtle text-danger" style="font-size:0.68rem;">Required</span> @endif
                                        @if($attr->show_in_pdf) <span class="badge bg-info-subtle text-info" style="font-size:0.68rem;">PDF</span> @endif
                                        @if($attr->show_in_share) <span class="badge bg-success-subtle text-success" style="font-size:0.68rem;">Share</span> @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($attr->approval_status == 'approved')
                                        <span class="badge bg-success-soft text-success rounded-pill px-3 py-1.5 fw-semibold" style="font-size:0.75rem;">Approved</span>
                                    @elseif($attr->approval_status == 'rejected')
                                        <span class="badge bg-danger-soft text-danger rounded-pill px-3 py-1.5 fw-semibold" style="font-size:0.75rem;">Rejected</span>
                                    @else
                                        <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-1.5 fw-semibold" style="font-size:0.75rem;">Pending</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('subscriber.attributes.edit', $attr->id) }}" class="btn btn-sm btn-light border-0" style="border-radius:8px; display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px;" title="Edit Attribute">
                                            <i class="bi bi-pencil-fill text-muted" style="font-size:0.8rem;"></i>
                                        </a>
                                        <form action="{{ route('subscriber.attributes.destroy', $attr->id) }}" method="POST" id="delete-form-{{ $attr->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-light border-0 btn-delete" style="border-radius:8px; display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px;" title="Delete Attribute">
                                                <i class="bi bi-trash-fill text-danger" style="font-size:0.8rem;"></i>
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
                    {{ $attributes->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-sliders text-muted fs-1 mb-3"></i>
                    <h5 class="fw-bold text-dark">No custom attributes defined</h5>
                    <p class="text-muted fs-13 max-width-360 mx-auto">Create custom specification attributes (e.g. Size, Pack Type, Volts, Amps) to form highly precise templates for your products.</p>
                    <a href="{{ route('subscriber.attributes.create') }}" class="btn btn-primary rounded-pill px-4 mt-3 fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i> Add Custom Attribute
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Add Group Quick Manager --}}
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-folder-plus me-2 text-primary"></i>Attribute Groups</h6>
            </div>
            <div class="card-body py-2">
                {{-- Quick Groups List --}}
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold fs-11 text-uppercase">My Groups</label>
                    <div class="d-flex flex-wrap gap-1.5" style="max-height: 180px; overflow-y: auto; padding-right: 5px;">
                        @if($groups->count() > 0)
                            @foreach($groups as $grp)
                                <span class="badge bg-light text-dark border px-2.5 py-1.5" style="font-size:0.75rem; border-radius:6px;">
                                    <i class="bi bi-folder2 me-1 text-primary"></i>{{ $grp->name }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-muted small">No groups added yet.</span>
                        @endif
                    </div>
                </div>

                <hr class="my-3 text-muted" style="opacity: 0.15;">

                <form action="{{ route('subscriber.attribute-groups.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold fs-11 text-uppercase">New Group Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Technical Specs" required style="border-radius:10px;">
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100 py-2 fs-13 fw-semibold" style="border-radius:10px;">
                        <i class="bi bi-plus-lg me-1"></i>Create Group
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    $(document).on('click', '.btn-delete', function() {
        let form = $(this).closest('form');
        Swal.fire({
            title: 'Delete Custom Attribute?',
            text: "This attribute and all product specifications mapped across your store will be permanently removed!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Delete',
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Drag-drop reorder
    const sortable = new Sortable(document.getElementById('sortable-attributes'), {
        handle: 'td:first-child',
        animation: 150,
        ghostClass: 'bg-light',
        onEnd: function() {
            const rows = document.querySelectorAll('#sortable-attributes tr');
            const order = Array.from(rows).map((row, index) => ({
                id: row.dataset.id,
                order: index
            }));
            fetch('{{ route("subscriber.attributes.reorder") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ order })
            });
        }
    });
</script>
<style>
.badge.bg-success-soft {
    background-color: rgba(16, 185, 129, 0.1) !important;
    color: rgb(16, 185, 129) !important;
}
.badge.bg-danger-soft {
    background-color: rgba(239, 68, 68, 0.1) !important;
    color: rgb(239, 68, 68) !important;
}
.badge.bg-warning-soft {
    background-color: rgba(245, 158, 11, 0.1) !important;
    color: rgb(245, 158, 11) !important;
}
</style>
@endpush
