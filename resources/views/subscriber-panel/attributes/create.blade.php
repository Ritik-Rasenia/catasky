@extends('subscriber-panel.layouts.app')

@section('title', 'Add Attribute')
@section('page-title', 'Add Attribute')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0" style="font-size:0.88rem;">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('subscriber.attributes.index') }}" class="text-decoration-none text-muted">Attributes</a></li>
            <li class="breadcrumb-item active text-indigo fw-semibold" aria-current="page">Add New Attribute</li>
        </ol>
    </nav>
@endsection

@section('content')

<form action="{{ route('subscriber.attributes.store') }}" method="POST" id="attribute-form">
    @csrf

    <div class="row g-4">
        {{-- Left side: Config --}}
        <div class="col-lg-8">
            <div class="card premium-card mb-4">
                <div class="card-header premium-card-header d-flex align-items-center gap-3">
                    <div class="icon-badge bg-primary-indigo-light text-indigo rounded-3 d-flex align-items-center justify-content-center" style="width:42px; height:42px; background: rgba(79, 70, 229, 0.08); color: #4F46E5;">
                        <i class="bi bi-gear-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0" style="font-family:'Outfit', sans-serif;">Attribute Details</h5>
                        <p class="text-muted small mb-0">Specify the basic details for your custom product attribute.</p>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label premium-label">Attribute Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control premium-input @error('name') is-invalid @enderror"
                                   placeholder="e.g. Cable Length or Switch Type" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label premium-label">Attribute Type <span class="text-danger">*</span></label>
                            <select name="type" id="attr-type-select" class="form-select premium-input" required style="cursor: pointer;">
                                <option value="">-- Choose Type --</option>
                                @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label premium-label">Target Product Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select premium-input" required style="cursor: pointer;">
                                <option value="">-- Choose Category --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label premium-label">Attribute Group</label>
                            <select name="attribute_group_id" class="form-select premium-input" style="cursor: pointer;">
                                <option value="">None (General)</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ old('attribute_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label premium-label">Unit of Measure <small class="text-muted fw-normal">(optional)</small></label>
                            <input type="text" name="unit" class="form-control premium-input" placeholder="e.g. meters, kg, V, mm" value="{{ old('unit') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label premium-label">Placeholder <small class="text-muted fw-normal">(optional)</small></label>
                            <input type="text" name="placeholder" class="form-control premium-input" placeholder="e.g. Enter length in meters" value="{{ old('placeholder') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label premium-label">Default Value <small class="text-muted fw-normal">(optional)</small></label>
                            <input type="text" name="default_value" class="form-control premium-input" placeholder="Default value for new products" value="{{ old('default_value') }}">
                        </div>
                    </div>

                    {{-- Option builder for Select-type --}}
                    <div id="options-builder-card" class="mt-4 pt-4 border-top" style="display:none;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-list-stars text-primary fs-5"></i>
                                <h6 class="fw-bold text-dark mb-0" style="font-family:'Outfit', sans-serif;">Option Values</h6>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2" style="font-size:0.85rem;" onclick="addOptionRow()">
                                <i class="bi bi-plus-lg"></i> Add Option
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 options-table">
                                <thead>
                                    <tr>
                                        <th class="py-2 text-uppercase">Label</th>
                                        <th class="py-2 text-uppercase">Value <small class="text-muted fw-normal">(Optional)</small></th>
                                        <th class="py-2 text-uppercase">Color Code <small class="text-muted fw-normal">(Optional)</small></th>
                                        <th class="py-2 text-uppercase text-center" style="width:100px;">Default?</th>
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
            <div class="card premium-card mb-4">
                <div class="card-header premium-card-header d-flex align-items-center gap-3">
                    <div class="icon-badge bg-primary-indigo-light text-indigo rounded-3 d-flex align-items-center justify-content-center" style="width:42px; height:42px; background: rgba(79, 70, 229, 0.08); color: #4F46E5;">
                        <i class="bi bi-toggle-on fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0" style="font-family:'Outfit', sans-serif;">Settings & Visibility</h5>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <div class="switch-row">
                        <div>
                            <label class="fw-bold text-dark d-block mb-1" style="font-size:0.88rem; cursor:pointer;" for="is_required">Required Field <span class="text-danger">*</span></label>
                            <span class="text-muted d-block" style="font-size:0.75rem;">Force field during product upload</span>
                        </div>
                        <div class="form-check form-switch ps-0 mb-0">
                            <input class="form-check-input ms-0" type="checkbox" name="is_required" id="is_required" value="1" {{ old('is_required') ? 'checked' : '' }} style="width: 42px; height: 22px; cursor: pointer;">
                        </div>
                    </div>

                    <div class="switch-row">
                        <div>
                            <label class="fw-bold text-dark d-block mb-1" style="font-size:0.88rem; cursor:pointer;" for="is_searchable">Searchable Filter</label>
                            <span class="text-muted d-block" style="font-size:0.75rem;">Searchable field in filters</span>
                        </div>
                        <div class="form-check form-switch ps-0 mb-0">
                            <input class="form-check-input ms-0" type="checkbox" name="is_searchable" id="is_searchable" value="1" {{ old('is_searchable', true) ? 'checked' : '' }} style="width: 42px; height: 22px; cursor: pointer;">
                        </div>
                    </div>

                    <div class="switch-row">
                        <div>
                            <label class="fw-bold text-dark d-block mb-1" style="font-size:0.88rem; cursor:pointer;" for="show_in_pdf">Show in PDFs</label>
                            <span class="text-muted d-block" style="font-size:0.75rem;">Render in generated PDF brochures</span>
                        </div>
                        <div class="form-check form-switch ps-0 mb-0">
                            <input class="form-check-input ms-0" type="checkbox" name="show_in_pdf" id="show_in_pdf" value="1" {{ old('show_in_pdf', true) ? 'checked' : '' }} style="width: 42px; height: 22px; cursor: pointer;">
                        </div>
                    </div>

                    <div class="switch-row">
                        <div>
                            <label class="fw-bold text-dark d-block mb-1" style="font-size:0.88rem; cursor:pointer;" for="show_in_share">Show on Share Page</label>
                            <span class="text-muted d-block" style="font-size:0.75rem;">Show on public B2B share link</span>
                        </div>
                        <div class="form-check form-switch ps-0 mb-0">
                            <input class="form-check-input ms-0" type="checkbox" name="show_in_share" id="show_in_share" value="1" {{ old('show_in_share', true) ? 'checked' : '' }} style="width: 42px; height: 22px; cursor: pointer;">
                        </div>
                    </div>

                    <div class="switch-row">
                        <div>
                            <label class="fw-bold text-dark d-block mb-1" style="font-size:0.88rem; cursor:pointer;" for="is_active">Is Active</label>
                            <span class="text-muted d-block" style="font-size:0.75rem;">Enable this attribute immediately</span>
                        </div>
                        <div class="form-check form-switch ps-0 mb-0">
                            <input class="form-check-input ms-0" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width: 42px; height: 22px; cursor: pointer;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Card --}}
            <div class="card premium-card mb-4 bg-light border-0" style="background-color: #F8FAFC !important;">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold mb-2 text-dark" style="font-family:'Outfit', sans-serif;">Ready to Request?</h5>
                    <p class="text-muted small mb-4">This attribute will go to the Admin dashboard for verification and approval.</p>
                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-semibold shadow-sm transition-all" style="background:#4F46E5; border-color:#4F46E5;">
                            <i class="bi bi-check-circle me-1"></i> Create Attribute
                        </button>
                        <a href="{{ route('subscriber.attributes.index') }}" class="btn btn-outline-secondary rounded-pill py-2.5 fw-semibold transition-all">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Dynamic Sticky Save Bar --}}
<div class="glass-save-bar d-flex justify-content-end align-items-center gap-3">
    <div class="text-muted small me-auto d-none d-md-flex align-items-center gap-2">
        <i class="bi bi-info-circle-fill text-indigo fs-5" style="color:#4F46E5;"></i>
        <span class="fw-semibold">You have unsaved changes in this attribute request form.</span>
    </div>
    <a href="{{ route('subscriber.attributes.index') }}" class="btn btn-light rounded-pill px-4 py-2 fw-semibold text-secondary shadow-sm transition-all" style="background:#fff; border:1px solid #E2E8F0;">Cancel</a>
    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm transition-all" style="background:#4F46E5; border-color:#4F46E5;" onclick="document.getElementById('attribute-form').submit()">Publish Attribute</button>
</div>

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
    tr.className = 'align-middle';
    
    const colorVal = '#4F46E5';
    
    tr.innerHTML = `
        <td>
            <input type="text" name="options[${optionIndex}][label]" class="form-control premium-input px-3 py-2" placeholder="e.g. Red" required style="font-size:0.85rem;">
        </td>
        <td>
            <input type="text" name="options[${optionIndex}][value]" class="form-control premium-input px-3 py-2" placeholder="e.g. red" style="font-size:0.85rem;">
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="color-picker-wrapper">
                    <input type="color" class="color-picker-input" id="color_picker_${optionIndex}" value="${colorVal}" oninput="syncColorText(${optionIndex})">
                </div>
                <input type="text" name="options[${optionIndex}][color_code]" id="color_text_${optionIndex}" class="form-control premium-input px-3 py-2" placeholder="e.g. #FF0000" value="${colorVal}" style="font-size:0.85rem;" oninput="syncColorPicker(${optionIndex})">
            </div>
        </td>
        <td class="text-center">
            <input type="checkbox" name="options[${optionIndex}][is_default]" value="1" class="form-check-input" style="width:20px;height:20px;accent-color:#4F46E5;cursor:pointer;">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-light border-0" style="color:var(--trash-red);background:var(--trash-red-light);border-radius:10px;padding:8px 12px;transition:all 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='none'" onclick="this.closest('tr').remove()">
                <i class="bi bi-trash-fill"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    
    // Attach event listeners for sticky bar to new inputs
    tr.querySelectorAll('input').forEach(el => {
        el.addEventListener('change', showSticky);
        el.addEventListener('keyup', showSticky);
    });
    
    optionIndex++;
}

function syncColorText(index) {
    const picker = document.getElementById(`color_picker_${index}`);
    const text = document.getElementById(`color_text_${index}`);
    if (picker && text) {
        text.value = picker.value.toUpperCase();
        // Trigger changes for save bar
        const event = new Event('change');
        text.dispatchEvent(event);
    }
}

function syncColorPicker(index) {
    const picker = document.getElementById(`color_picker_${index}`);
    const text = document.getElementById(`color_text_${index}`);
    if (picker && text) {
        let val = text.value.trim();
        if (/^#[0-9A-F]{6}$/i.test(val)) {
            picker.value = val;
        }
    }
}

const showSticky = () => {
    const stickyBar = document.querySelector('.glass-save-bar');
    if (stickyBar) {
        stickyBar.classList.add('show');
    }
};

// Sticky footer logic on input changes
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('attribute-form');
    
    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('change', showSticky);
        el.addEventListener('keyup', showSticky);
    });

    document.getElementById('attr-type-select').addEventListener('change', checkType);
    checkType();
});
</script>

<style>
/* Color tokens */
:root {
    --primary-indigo: #4F46E5;
    --primary-indigo-light: rgba(79, 70, 229, 0.06);
    --primary-indigo-glow: rgba(79, 70, 229, 0.12);
    --border-color-soft: rgba(226, 232, 240, 0.8);
    --input-focus-border: #818CF8;
    --trash-red: #EF4444;
    --trash-red-light: rgba(239, 68, 68, 0.08);
}

/* Glassmorphic Cards */
.premium-card {
    background: #ffffff;
    border: 1px solid var(--border-color-soft) !important;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.01) !important;
    border-radius: 18px !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.premium-card:hover {
    box-shadow: 0 15px 35px -8px rgba(0, 0, 0, 0.05), 0 2px 5px rgba(0, 0, 0, 0.02) !important;
}
.premium-card-header {
    border-bottom: 1px solid var(--border-color-soft) !important;
    padding: 20px 24px !important;
    background: transparent !important;
}

/* Form Styling */
.premium-label {
    font-family: 'Outfit', 'Inter', sans-serif;
    font-size: 0.72rem !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #475569 !important;
    margin-bottom: 8px;
}
.premium-input {
    border: 1.5px solid #E2E8F0 !important;
    border-radius: 12px !important;
    padding: 10px 16px !important;
    font-size: 0.9rem !important;
    color: #1E293B !important;
    background-color: #F8FAFC !important;
    transition: all 0.2s ease-in-out !important;
}
.premium-input::placeholder {
    color: #94A3B8 !important;
    font-size: 0.85rem;
}
.premium-input:focus {
    background-color: #ffffff !important;
    border-color: var(--input-focus-border) !important;
    box-shadow: 0 0 0 4px var(--primary-indigo-glow) !important;
    outline: none !important;
}

/* Switches panel */
.switch-row {
    padding: 16px 20px;
    border-radius: 14px;
    border: 1px solid var(--border-color-soft);
    background: #F8FAFC;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.switch-row:hover {
    border-color: #CBD5E1;
    background: #ffffff;
}

/* Styled color picker */
.color-picker-wrapper {
    position: relative;
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #E2E8F0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}
.color-picker-wrapper:hover {
    transform: scale(1.08);
    border-color: var(--primary-indigo);
}
.color-picker-input {
    position: absolute;
    top: -5px;
    left: -5px;
    width: 46px;
    height: 46px;
    padding: 0;
    border: 0;
    cursor: pointer;
    background: transparent;
}

/* Floating Save Bar */
.glass-save-bar {
    position: fixed;
    bottom: 24px;
    left: 24px;
    right: 24px;
    background: rgba(255, 255, 255, 0.85) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.6) !important;
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.04) !important;
    border-radius: 20px !important;
    padding: 16px 28px !important;
    z-index: 1050;
    transform: translateY(150px);
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.glass-save-bar.show {
    transform: translateY(0);
}

/* Table styling for options */
.options-table th {
    font-family: 'Outfit', sans-serif;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #64748B;
    border-bottom: 2px solid #E2E8F0 !important;
}
.options-table td {
    padding: 12px 8px !important;
    border-bottom: 1px solid #F1F5F9 !important;
}

/* Hover scales and transitions */
.transition-all {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
}
</style>
@endpush
