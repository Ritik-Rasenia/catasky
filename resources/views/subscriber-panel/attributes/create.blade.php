@extends('subscriber-panel.layouts.app')

@section('title', 'Add Attribute')
@section('page-title', 'Add Attribute')
@section('breadcrumb', '<a href="' . route('subscriber.attributes.index') . '">Attributes</a> → Add New Attribute')

@section('content')

<form action="{{ route('subscriber.attributes.store') }}" method="POST" id="attribute-form">
@csrf

<div class="row g-3">
    {{-- Left side: Config --}}
    <div class="col-lg-8">
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-gear-fill me-2"></i>Attribute Details</h6>
            </div>
            <div class="vp-card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="vp-form-group">
                            <label class="vp-label">Attribute Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="vp-input @error('name') is-invalid @enderror" 
                                   placeholder="e.g. Cable Length or Switch Type" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback d-block" style="color:#EF4444;">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="vp-form-group">
                            <label class="vp-label">Attribute Type <span class="text-danger">*</span></label>
                            <select name="type" id="attr-type-select" class="vp-select" required>
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
                        <div class="vp-form-group">
                            <label class="vp-label">Attribute Group</label>
                            <select name="attribute_group_id" class="vp-select">
                                <option value="">None (General)</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ old('attribute_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="vp-form-group">
                            <label class="vp-label">Unit of Measure <small style="color:#94A3B8;">(optional)</small></label>
                            <input type="text" name="unit" class="vp-input" placeholder="e.g. meters, kg, V, mm" value="{{ old('unit') }}">
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="vp-form-group">
                            <label class="vp-label">Placeholder <small style="color:#94A3B8;">(optional)</small></label>
                            <input type="text" name="placeholder" class="vp-input" placeholder="e.g. Enter length in meters" value="{{ old('placeholder') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="vp-form-group">
                            <label class="vp-label">Default Value <small style="color:#94A3B8;">(optional)</small></label>
                            <input type="text" name="default_value" class="vp-input" placeholder="Default value for new products" value="{{ old('default_value') }}">
                        </div>
                    </div>
                </div>

                {{-- Option builder for Select-type --}}
                <div id="options-builder-card" class="mt-4" style="display:none; border-top:1px solid #E2E8F0; padding-top:20px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="vp-card-title" style="font-size:0.9rem;"><i class="bi bi-list-stars me-2"></i>Option Values</h6>
                        <button type="button" class="btn-subscriber-outline" style="padding:4px 12px;font-size:0.75rem;" onclick="addOptionRow()">
                            <i class="bi bi-plus-lg"></i> Add Option
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="vp-table">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Value (Optional)</th>
                                    <th>Color Code (Optional)</th>
                                    <th style="width:100px;">Default?</th>
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
        <div class="vp-card mb-3">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-toggle-on me-2"></i>Settings & Visibility</h6>
            </div>
            <div class="vp-card-body">
                <label class="vp-toggle mb-3 d-flex">
                    <input type="checkbox" name="is_required" {{ old('is_required') ? 'checked' : '' }}>
                    <span class="vp-toggle-label">Required Field <span class="text-danger">*</span></span>
                </label>
                <label class="vp-toggle mb-3 d-flex">
                    <input type="checkbox" name="is_searchable" {{ old('is_searchable', true) ? 'checked' : '' }}>
                    <span class="vp-toggle-label">Searchable in filters</span>
                </label>
                <label class="vp-toggle mb-3 d-flex">
                    <input type="checkbox" name="show_in_pdf" {{ old('show_in_pdf', true) ? 'checked' : '' }}>
                    <span class="vp-toggle-label">Show in Generated PDFs</span>
                </label>
                <label class="vp-toggle mb-3 d-flex">
                    <input type="checkbox" name="show_in_share" {{ old('show_in_share', true) ? 'checked' : '' }}>
                    <span class="vp-toggle-label">Show on Public Share Page</span>
                </label>
                <label class="vp-toggle mb-3 d-flex">
                    <input type="checkbox" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span class="vp-toggle-label">Is Active</span>
                </label>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn-subscriber">
                <i class="bi bi-check-circle"></i> Create Attribute
            </button>
            <a href="{{ route('subscriber.attributes.index') }}" class="btn-subscriber-outline text-center">
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
            <input type="text" name="options[${optionIndex}][label]" class="vp-input" placeholder="e.g. Red" required style="padding:6px 12px;font-size:0.82rem;">
        </td>
        <td>
            <input type="text" name="options[${optionIndex}][value]" class="vp-input" placeholder="e.g. red" style="padding:6px 12px;font-size:0.82rem;">
        </td>
        <td>
            <input type="text" name="options[${optionIndex}][color_code]" class="vp-input" placeholder="e.g. #FF0000" style="padding:6px 12px;font-size:0.82rem;">
        </td>
        <td class="text-center">
            <input type="checkbox" name="options[${optionIndex}][is_default]" value="1" style="width:16px;height:16px;accent-color:#4F46E5;">
        </td>
        <td>
            <button type="button" class="btn btn-sm" style="background:rgba(239,68,68,0.07);color:#EF4444;border:none;border-radius:8px;padding:4px 8px;" onclick="this.closest('tr').remove()">
                <i class="bi bi-trash"></i>
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
