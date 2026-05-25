@extends('admin.layouts.app')

@section('title', 'Global PIM Attributes')
@section('page-title', 'Global PIM Attributes')
@section('breadcrumb', 'Catalogue → Attributes')

@section('content')
<div class="row g-3">
    {{-- Left: Attributes List --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0" style="border-radius:16px;">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-sliders me-2 text-primary"></i>Global PIM Attributes</h6>
                <a href="{{ route('admin.saas.approvals.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:0.75rem;">
                    <i class="bi bi-clock-history"></i> Pending Approvals
                </a>
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
                                <button type="submit" class="btn btn-primary btn-sm" style="border-radius:8px;">Filter</button>
                                <a href="{{ route('admin.attributes.index') }}" class="btn btn-light btn-sm ms-2" style="border-radius:8px;">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                @if($attributes->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="font-size:0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase fs-11 text-muted fw-bold">Name</th>
                                <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Group</th>
                                <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Type</th>
                                <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Configuration</th>
                                <th class="pe-4 py-3 text-end text-uppercase fs-11 text-muted fw-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attributes as $attr)
                            <tr>
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
                                    <code class="text-primary">{{ strtoupper($attr->type) }}</code>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($attr->is_required) <span class="badge bg-danger-subtle text-danger" style="font-size:0.68rem;">Required</span> @endif
                                        @if($attr->is_searchable) <span class="badge bg-info-subtle text-info" style="font-size:0.68rem;">Search</span> @endif
                                        @if($attr->is_filterable) <span class="badge bg-success-subtle text-success" style="font-size:0.68rem;">Filter</span> @endif
                                        @if($attr->is_comparable) <span class="badge bg-warning-subtle text-warning" style="font-size:0.68rem;">Compare</span> @endif
                                        @if($attr->is_variant_enabled) <span class="badge bg-primary-subtle text-primary" style="font-size:0.68rem;">Variant</span> @endif
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-sm btn-light border-0" data-bs-toggle="modal" data-bs-target="#editModal{{ $attr->id }}" style="border-radius:8px;">
                                            <i class="bi bi-pencil-fill text-muted" style="font-size:0.8rem;"></i>
                                        </button>
                                        <form action="{{ route('admin.attributes.destroy', $attr->id) }}" method="POST" id="delete-form-{{ $attr->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-light border-0" onclick="confirmDelete('delete-form-{{ $attr->id }}', 'Delete this attribute? This will remove all values associated with products.')" style="border-radius:8px;">
                                                <i class="bi bi-trash-fill text-danger" style="font-size:0.8rem;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Edit Attribute Modal --}}
                            <div class="modal fade" id="editModal{{ $attr->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content" style="border-radius:16px; border:0;">
                                        <div class="modal-header border-0 px-4 pt-4">
                                            <h5 class="fw-bold text-dark mb-0">Edit Attribute</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.attributes.update', $attr->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body px-4 py-3">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Attribute Name</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $attr->name }}" required style="border-radius:10px;">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Attribute Group</label>
                                                    <select name="attribute_group_id" class="form-select" style="border-radius:10px;">
                                                        <option value="">General</option>
                                                        @foreach($groups as $grp)
                                                        <option value="{{ $grp->id }}" {{ $attr->attribute_group_id === $grp->id ? 'selected' : '' }}>{{ $grp->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Unit (e.g. kg, cm)</label>
                                                        <input type="text" name="unit" class="form-control" value="{{ $attr->unit }}" style="border-radius:10px;">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Placeholder</label>
                                                        <input type="text" name="placeholder" class="form-control" value="{{ $attr->placeholder }}" style="border-radius:10px;">
                                                    </div>
                                                </div>
                                                @if($attr->isSelectType())
                                                <div class="mb-3">
                                                    <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Options (Comma separated)</label>
                                                    <textarea name="options" class="form-control" rows="2" style="border-radius:10px;" placeholder="Option 1, Option 2, Option 3">{{ implode(', ', $attr->options->pluck('label')->toArray()) }}</textarea>
                                                </div>
                                                @endif
                                                <div class="mb-3">
                                                    <label class="form-label text-muted fw-semibold fs-12 text-uppercase d-block">Default Value</label>
                                                    <input type="text" name="default_value" class="form-control" value="{{ $attr->default_value }}" style="border-radius:10px;">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label text-muted fw-semibold fs-12 text-uppercase d-block">Flags</label>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="is_required" value="1" id="req{{ $attr->id }}" {{ $attr->is_required ? 'checked' : '' }}>
                                                                <label class="form-check-label fs-13" for="req{{ $attr->id }}">Required</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="is_searchable" value="1" id="sea{{ $attr->id }}" {{ $attr->is_searchable ? 'checked' : '' }}>
                                                                <label class="form-check-label fs-13" for="sea{{ $attr->id }}">Searchable</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="is_filterable" value="1" id="filt{{ $attr->id }}" {{ $attr->is_filterable ? 'checked' : '' }}>
                                                                <label class="form-check-label fs-13" for="filt{{ $attr->id }}">Filterable</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="is_comparable" value="1" id="comp{{ $attr->id }}" {{ $attr->is_comparable ? 'checked' : '' }}>
                                                                <label class="form-check-label fs-13" for="comp{{ $attr->id }}">Comparable</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="is_variant_enabled" value="1" id="var{{ $attr->id }}" {{ $attr->is_variant_enabled ? 'checked' : '' }}>
                                                                <label class="form-check-label fs-13" for="var{{ $attr->id }}">Variant-enabled</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:10px; padding:10px 18px;">Cancel</button>
                                                <button type="submit" class="btn btn-primary" style="border-radius:10px; padding:10px 18px;">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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
                    <h5 class="fw-bold text-dark">No global attributes defined</h5>
                    <p class="text-muted fs-13 max-width-360 mx-auto">Create global attributes (e.g., color, size, pack size, technical specifications) and assign them to categories to form PIM templates.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Add Attribute & Add Group --}}
    <div class="col-lg-4">
        {{-- Add Global Attribute --}}
        <div class="card shadow-sm border-0 mb-3" style="border-radius:16px;">
            <div class="card-header bg-white py-3 border-0" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle-fill me-2 text-primary"></i>Add Global Attribute</h6>
            </div>
            <form action="{{ route('admin.attributes.store') }}" method="POST">
                @csrf
                <div class="card-body py-2">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Attribute Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Packaging details" required style="border-radius:10px;">
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
                            <input type="text" name="placeholder" class="form-control" placeholder="e.g. Enter stock details" style="border-radius:10px;">
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="options-textarea-group">
                        <label class="form-label text-muted fw-semibold fs-12 text-uppercase">Dropdown Options (Comma separated)</label>
                        <textarea name="options" class="form-control" rows="2" placeholder="e.g. Small, Medium, Large" style="border-radius:10px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold fs-12 text-uppercase d-block font-sans">PIM Configurations</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_required" value="1" id="reqAdd">
                                    <label class="form-check-label fs-13" for="reqAdd">Required</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_searchable" value="1" id="seaAdd">
                                    <label class="form-check-label fs-13" for="seaAdd">Searchable</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_filterable" value="1" id="filtAdd">
                                    <label class="form-check-label fs-13" for="filtAdd">Filterable</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_comparable" value="1" id="compAdd">
                                    <label class="form-check-label fs-13" for="compAdd">Comparable</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_variant_enabled" value="1" id="varAdd">
                                    <label class="form-check-label fs-13" for="varAdd">Variant-enabled</label>
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

        {{-- Add Attribute Group --}}
        <div class="card shadow-sm border-0" style="border-radius:16px;">
            <div class="card-header bg-white py-3 border-0" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-folder-plus me-2 text-primary"></i>Add Attribute Group</h6>
            </div>
            <form action="{{ route('admin.attributes.storeGroup') }}" method="POST">
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
</script>
@endpush
