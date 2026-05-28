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
    <div class="col-lg-8">
        <div class="card  border-0" style="border-radius:16px;">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-sliders me-2 text-primary"></i>My Specifications & Attributes</h6>
                <div style="font-size:0.8rem;color:#64748B;">
                    {{ $attributes->total() }} attributes registered
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
                            <div class="d-inline-flex">
                                <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius:8px;">Filter</button>
                                <a href="{{ route('subscriber.attributes.index') }}" class="btn btn-light btn-sm ms-2 px-3" style="border-radius:8px;">Reset</a>
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
                            <tr data-id="{{ $attr->id }}">
                                <td style="cursor:grab;color:#CBD5E1;text-align:center;">
                                    <i class="bi bi-grip-vertical"></i>
                                </td>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $attr->name }}</div>
                                    <div class="text-muted fs-12">{{ $attr->slug }} @if($attr->unit)<span class="badge bg-light text-dark">Unit: {{ $attr->unit }}</span>@endif</div>
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
                                    <span style="background:{{ $color }}15;color:{{ $color }};border-radius:6px;padding:3px 10px;font-size:0.72rem;font-weight:700;">
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
                                        <span class="badge bg-success-soft text-success rounded-pill px-3">Approved</span>
                                    @elseif($attr->approval_status == 'rejected')
                                        <span class="badge bg-danger-soft text-danger rounded-pill px-3">Rejected</span>
                                    @else
                                        <span class="badge bg-warning-soft text-warning rounded-pill px-3">Pending</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('subscriber.attributes.edit', $attr->id) }}" class="btn btn-sm btn-light border-0" style="border-radius:8px;" title="Edit Attribute">
                                            <i class="bi bi-pencil-fill text-muted" style="font-size:0.8rem;"></i>
                                        </a>
                                        <form action="{{ route('subscriber.attributes.destroy', $attr->id) }}" method="POST" id="delete-form-{{ $attr->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-light border-0 btn-delete" style="border-radius:8px;" title="Delete Attribute">
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
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Add Attribute & Add Group --}}
    <div class="col-lg-4">
        {{-- Add Attribute Form --}}
        <div class="card  border-0 mb-3" style="border-radius:16px;">
            <div class="card-header bg-white py-3 border-0" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle-fill me-2 text-primary"></i>Add Custom Specification</h6>
            </div>
            <form action="{{ route('subscriber.attributes.store') }}" method="POST">
                @csrf
                <div class="card-body py-2">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Attribute Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Fabric Weight" required style="border-radius:10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Field Type</label>
                        <select name="type" class="form-select" id="attribute-type-select" required style="border-radius:10px;">
                            <option value="text">Text Input</option>
                            <option value="number">Number</option>
                            <option value="select">Dropdown (Single Select)</option>
                            <option value="multiselect">Multi Select</option>
                            <option value="checkbox">Checkbox Group</option>
                            <option value="radio">Radio Group</option>
                            <option value="textarea">Textarea (Long Text)</option>
                            <option value="color">Color Picker</option>
                            <option value="date">Date</option>
                            <option value="url">URL</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Attribute Group</label>
                        <select name="attribute_group_id" class="form-select" style="border-radius:10px;">
                            <option value="">General</option>
                            @foreach($groups as $grp)
                            <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Unit (e.g. kg, V)</label>
                            <input type="text" name="unit" class="form-control" placeholder="kg" style="border-radius:10px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Placeholder</label>
                            <input type="text" name="placeholder" class="form-control" placeholder="e.g. Enter details" style="border-radius:10px;">
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="options-textarea-group">
                        <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Dropdown Options (Comma separated)</label>
                        <textarea name="options" class="form-control" rows="2" placeholder="e.g. Small, Medium, Large" style="border-radius:10px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold fs-12 text-uppercase d-block font-sans">Configurations</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_required" value="1" id="reqAdd">
                                    <label class="form-check-label fs-13" for="reqAdd">Required</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_in_pdf" value="1" id="pdfAdd" checked>
                                    <label class="form-check-label fs-13" for="pdfAdd">Show in PDF</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_in_share" value="1" id="shareAdd" checked>
                                    <label class="form-check-label fs-13" for="shareAdd">Show in Share</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeAdd" checked>
                                    <label class="form-check-label fs-13" for="activeAdd">Is Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pb-3" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius:10px; padding:10px;">
                        Create Attribute
                    </button>
                </div>
            </form>
        </div>

        {{-- Add Group --}}
        <div class="card  border-0" style="border-radius:16px;">
            <div class="card-header bg-white py-3 border-0" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-folder-plus me-2 text-primary"></i>Add Attribute Group</h6>
            </div>
            <form action="{{ route('subscriber.attribute-groups.store') }}" method="POST">
                @csrf
                <div class="card-body py-2">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Group Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Technical Specifications" required style="border-radius:10px;">
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pb-3" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                    <button type="submit" class="btn btn-outline-primary w-100" style="border-radius:10px; padding:10px;">
                        Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.getElementById('attribute-type-select').addEventListener('change', function() {
        const value = this.value;
        const optionsGroup = document.getElementById('options-textarea-group');
        if (['select', 'multiselect', 'checkbox', 'radio'].includes(value)) {
            optionsGroup.classList.remove('d-none');
        } else {
            optionsGroup.classList.add('d-none');
        }
    });

    $(document).on('click', '.btn-delete', function() {
        let form = $(this).closest('form');
        Swal.fire({
            title: 'Delete Attribute?',
            text: "This attribute and all its product associations will be permanently removed!",
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
@endpush

{{-- Soft badge utilities are provided by partials/subscriber_styles.blade.php --}}
