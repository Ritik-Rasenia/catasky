@extends('subscriber-panel.layouts.app')

@section('title', 'Add Attribute')
@section('page-title', 'Add Attribute')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('subscriber.attributes.index') }}">Attributes</a></li>
            <li class="breadcrumb-item active">Add New Attribute</li>
        </ol>
    </nav>
@endsection

@section('content')

<form action="{{ route('subscriber.attributes.store') }}" method="POST" id="attribute-form">
@csrf

<div class="row g-3">
    {{-- Left side: Config --}}
    <div class="col-lg-8">
        <div class="card border-0  rounded-4 mb-3">
            <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-gear-fill me-2 text-primary"></i>Attribute Details</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Attribute Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                                   placeholder="e.g. Cable Length or Switch Type" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Attribute Type <span class="text-danger">*</span></label>
                            <select name="type" id="attr-type-select" class="form-select rounded-3" required>
                                <option value="">-- Choose Type --</option>
                                @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Target Product Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select rounded-3" required>
                                <option value="">-- Choose Category --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Attribute Group</label>
                            <select name="attribute_group_id" class="form-select rounded-3">
                                <option value="">None (General)</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ old('attribute_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Unit of Measure <small style="font-weight:400;color:#94A3B8;">(optional)</small></label>
                            <input type="text" name="unit" class="form-control rounded-3" placeholder="e.g. meters, kg, V, mm" value="{{ old('unit') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Placeholder <small style="font-weight:400;color:#94A3B8;">(optional)</small></label>
                            <input type="text" name="placeholder" class="form-control rounded-3" placeholder="e.g. Enter length in meters" value="{{ old('placeholder') }}">
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Default Value <small style="font-weight:400;color:#94A3B8;">(optional)</small></label>
                            <input type="text" name="default_value" class="form-control rounded-3" placeholder="Default value for new products" value="{{ old('default_value') }}">
                        </div>
                    </div>
                </div>

                {{-- Option builder for Select-type --}}
                <div id="options-builder-card" class="mt-4" style="display:none; border-top:1px solid var(--border-color, #e2e8f0); padding-top:20px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold text-dark mb-0" style="font-size:0.9rem;"><i class="bi bi-list-stars me-2 text-primary"></i>Option Values</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-3" onclick="addOptionRow()">
                            <i class="bi bi-plus-lg"></i> Add Option
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="font-size:0.875rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2 text-uppercase fs-11 text-muted fw-bold">Label</th>
                                    <th class="py-2 text-uppercase fs-11 text-muted fw-bold">Value <small class="text-muted fw-normal">(Optional)</small></th>
                                    <th class="py-2 text-uppercase fs-11 text-muted fw-bold">Color Code <small class="text-muted fw-normal">(Optional)</small></th>
                                    <th class="py-2 text-uppercase fs-11 text-muted fw-bold text-center" style="width:100px;">Default?</th>
                                    <th style="width:60px;"></th>
                                </tr>
                            </thead>
                            <tbody id="options-tbody">
                                {{-- Rows added dynamically via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right side: Visibility & Actions --}}
    <div class="col-lg-4">
        <div class="card border-0  rounded-4 mb-3">
            <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-toggle-on me-2 text-primary"></i>Settings & Visibility</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_required" id="is_required" value="1" {{ old('is_required') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold small" for="is_required">Required Field <span class="text-danger">*</span></label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_searchable" id="is_searchable" value="1" {{ old('is_searchable', true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold small" for="is_searchable">Searchable in filters</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="show_in_pdf" id="show_in_pdf" value="1" {{ old('show_in_pdf', true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold small" for="show_in_pdf">Show in Generated PDFs</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="show_in_share" id="show_in_share" value="1" {{ old('show_in_share', true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold small" for="show_in_share">Show on Public Share Page</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold small" for="is_active">Is Active</label>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold ">
                <i class="bi bi-check-circle me-1"></i> Create Attribute
            </button>
            <a href="{{ route('subscriber.attributes.index') }}" class="btn btn-outline-secondary rounded-pill py-2 fw-semibold text-center">
                Cancel
            </a>
        </div>
    </div>
</div>

</form>

@endsection

@push('js')
<script>
let optionIndex = 0;

function checkType() {
    const type = document.getElementById('attr-type-select').value;
    const builder = document.getElementById('options-builder-card');
    const selectTypes = ['select', 'multiselect', 'checkbox', 'radio'];

    if (selectTypes.includes(type)) {
        builder.style.display = 'block';
        if (document.getElementById('options-tbody').children.length === 0) {
            addOptionRow();
            addOptionRow();
        }
    } else {
        builder.style.display = 'none';
    }
}

function addOptionRow() {
    const tbody = document.getElementById('options-tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <input type="text" name="options[${optionIndex}][label]" class="form-control rounded-3" placeholder="e.g. Red" required style="font-size:0.82rem;">
        </td>
        <td>
            <input type="text" name="options[${optionIndex}][value]" class="form-control rounded-3" placeholder="e.g. red" style="font-size:0.82rem;">
        </td>
        <td>
            <input type="text" name="options[${optionIndex}][color_code]" class="form-control rounded-3" placeholder="e.g. #FF0000" style="font-size:0.82rem;">
        </td>
        <td class="text-center">
            <input type="checkbox" name="options[${optionIndex}][is_default]" value="1" style="width:16px;height:16px;accent-color:#4F46E5;">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-light border-0" style="color:#EF4444;border-radius:8px;" onclick="this.closest('tr').remove()">
                <i class="bi bi-trash-fill"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    optionIndex++;
}

document.getElementById('attr-type-select').addEventListener('change', checkType);
document.addEventListener('DOMContentLoaded', checkType);
</script>
@endpush
