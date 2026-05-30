@extends('admin.layouts.app')

@section('title', 'Edit Global Attribute')
@section('page-title', 'Edit Global Attribute')
@section('breadcrumb', 'Catalogue → Attributes → Edit')

@section('content')
<!-- Include Outfit Font for premium typography -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<div class="row justify-content-center" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="col-lg-12 mb-5">
        {{-- Back Link --}}
        <div class="mb-4">
            <a href="{{ route('admin.attributes.index') }}" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold text-secondary transition-all" style="font-size: 0.9rem; font-family:'Outfit', sans-serif;">
                <i class="bi bi-arrow-left me-1 text-primary"></i> Back to Global Attributes
            </a>
        </div>

        <form action="{{ route('admin.attributes.update', $attribute->id) }}" method="POST" id="attribute-form">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- Left side: Config --}}
                <div class="col-lg-8">
                    {{-- Attribute Details Card --}}
                    <div class="card premium-card border-0 mb-4 position-relative overflow-hidden">
                        <div class="card-gradient-overlay"></div>
                        <div class="card-header premium-card-header d-flex align-items-center gap-3 border-0 bg-transparent pt-4 px-4 pb-3">
                            <div class="icon-badge rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width:48px; height:48px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(129, 140, 248, 0.1) 100%); color: #4F46E5;">
                                <i class="bi bi-gear-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0 card-title-outfit">Attribute Details</h5>
                                <p class="text-muted small mb-0 mt-0.5">Modify the specifications, group, and options for this global attribute template.</p>
                            </div>
                        </div>
                        <div class="card-body p-4 pt-2">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label premium-label">Attribute Name <span class="text-danger">*</span></label>
                                    <div class="input-group-premium">
                                        <span class="input-icon"><i class="bi bi-bookmark-fill text-muted"></i></span>
                                        <input type="text" name="name" class="form-control premium-input @error('name') is-invalid @enderror"
                                               placeholder="e.g. Volts, Color, Switch Type" value="{{ old('name', $attribute->name) }}" required>
                                    </div>
                                    @error('name') <div class="invalid-feedback d-block mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label premium-label">Attribute Type</label>
                                    <div class="input-group-premium">
                                        <span class="input-icon" style="color: #64748B;"><i class="bi bi-lock-fill"></i></span>
                                        <input type="text" class="form-control premium-input bg-light text-muted fw-bold" value="{{ strtoupper($attribute->type) }}" readonly disabled style="cursor: not-allowed; padding-left:44px !important;">
                                    </div>
                                    <input type="hidden" name="type" id="attr-type-select" value="{{ $attribute->type }}">
                                    <small class="text-muted fs-11 mt-1.5 d-block"><i class="bi bi-info-circle me-1"></i>Attribute type cannot be modified after creation.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label premium-label">Attribute Group</label>
                                    <div class="input-group-premium">
                                        <span class="input-icon"><i class="bi bi-folder-fill text-muted"></i></span>
                                        <select name="attribute_group_id" class="form-select premium-input" style="cursor: pointer; -webkit-appearance: none;">
                                            <option value="">None (General)</option>
                                            @foreach($groups as $group)
                                            <option value="{{ $group->id }}" {{ old('attribute_group_id', $attribute->attribute_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label premium-label">Unit <small class="text-muted fw-normal">(e.g. kg, V)</small></label>
                                    <input type="text" name="unit" class="form-control premium-input" placeholder="optional" value="{{ old('unit', $attribute->unit) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label premium-label">Placeholder</label>
                                    <input type="text" name="placeholder" class="form-control premium-input" placeholder="optional" value="{{ old('placeholder', $attribute->placeholder) }}">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label premium-label">Default Value <small class="text-muted fw-normal">(Fallback value during product entry)</small></label>
                                    <div class="input-group-premium">
                                        <span class="input-icon"><i class="bi bi-arrow-return-right text-muted"></i></span>
                                        <input type="text" name="default_value" class="form-control premium-input" placeholder="optional fallback value" value="{{ old('default_value', $attribute->default_value) }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Option builder for Select-type --}}
                            <div id="options-builder-card" class="mt-4 pt-4 border-top" style="display:{{ $attribute->isSelectType() ? 'block' : 'none' }}; border-top: 1.5px dashed #E2E8F0 !important;">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-badge bg-primary-indigo-light text-indigo rounded-3 d-flex align-items-center justify-content-center" style="width:32px; height:32px; background: rgba(79, 70, 229, 0.08); color: #4F46E5;">
                                            <i class="bi bi-list-nested fs-5"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-0 card-title-outfit" style="font-size: 1.05rem;">Option Values</h6>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 transition-all hover-translate-y" style="font-size:0.85rem; border-color: #4F46E5; color: #4F46E5;" onclick="addOptionRow()">
                                        <i class="bi bi-plus-lg"></i> Add Option Row
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 options-table border-0">
                                        <thead>
                                            <tr>
                                                <th class="py-2 text-uppercase text-muted fw-bold ps-0" style="font-size:0.75rem; letter-spacing:0.8px;">Label *</th>
                                                <th class="py-2 text-uppercase text-muted fw-bold" style="font-size:0.75rem; letter-spacing:0.8px;">Value <small class="text-muted fw-normal">(Auto-generated)</small></th>
                                                <th class="py-2 text-uppercase text-muted fw-bold" style="font-size:0.75rem; letter-spacing:0.8px;">Color Code <small class="text-muted fw-normal">(Optional)</small></th>
                                                <th class="py-2 text-uppercase text-muted fw-bold text-center" style="font-size:0.75rem; letter-spacing:0.8px; width:100px;">Default?</th>
                                                <th style="width:60px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="options-tbody">
                                            @foreach($attribute->options as $index => $opt)
                                            <tr class="align-middle option-row-premium">
                                                <td class="ps-0">
                                                    <input type="text" name="options[{{ $index }}][label]" class="form-control premium-input px-3 py-2 text-dark fw-medium" value="{{ $opt->label }}" required style="font-size:0.85rem;" oninput="generateOptionValue(this, {{ $index }})">
                                                </td>
                                                <td>
                                                    <input type="text" name="options[{{ $index }}][value]" id="option_val_{{ $index }}" class="form-control premium-input px-3 py-2 text-muted" value="{{ $opt->value }}" style="font-size:0.85rem; background-color:#F1F5F9 !important;">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="color-picker-wrapper">
                                                            <input type="color" class="color-picker-input" id="color_picker_{{ $index }}" value="{{ $opt->color_code ?: '#4F46E5' }}" oninput="syncColorText({{ $index }})">
                                                        </div>
                                                        <input type="text" name="options[{{ $index }}][color_code]" id="color_text_{{ $index }}" class="form-control premium-input px-3 py-2" placeholder="e.g. #FF0000" value="{{ $opt->color_code ?: '#4F46E5' }}" style="font-size:0.85rem;" oninput="syncColorPicker({{ $index }})">
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="checkbox-circle-premium d-inline-block">
                                                        <input type="checkbox" name="options[{{ $index }}][is_default]" value="1" {{ $opt->is_default ? 'checked' : '' }} class="form-check-input default-check" style="width:20px;height:20px;accent-color:#4F46E5;cursor:pointer;" onclick="onlyOneDefault(this)">
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-trash-premium" title="Remove option" onclick="this.closest('tr').remove(); showSticky();">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Target Subcategory Mapping Card --}}
                    <div class="card premium-card border-0 mb-4 position-relative overflow-hidden">
                        <div class="card-gradient-overlay"></div>
                        <div class="card-header premium-card-header d-flex align-items-center gap-3 border-0 bg-transparent pt-4 px-4 pb-3">
                            <div class="icon-badge rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width:48px; height:48px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%); color: #6366F1;">
                                <i class="bi bi-diagram-3-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0 card-title-outfit">Subcategory Mappings</h5>
                                <p class="text-muted small mb-0 mt-0.5">Assign this global attribute directly to subcategories to auto-populate product specifications templates.</p>
                            </div>
                        </div>
                        <div class="card-body p-4 pt-2">
                            @if($subcategories->count() > 0)
                                @foreach($subcategories->groupBy('category_id') as $catId => $subcats)
                                    @php
                                        $cat = $subcats->first()?->category;
                                    @endphp
                                    @if($cat)
                                        <div class="mb-4 pb-3 border-bottom border-light-premium">
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <span class="d-inline-block rounded-pill" style="width: 5px; height: 18px; background: linear-gradient(to bottom, #4F46E5, #818CF8);"></span>
                                                <span class="fw-bold text-dark text-uppercase card-title-outfit" style="font-size:0.8rem; letter-spacing: 1px; color:#1E293B;">{{ $cat->name }}</span>
                                                <span class="badge rounded-pill bg-light text-secondary small fw-medium px-2.5 py-1">{{ $subcats->count() }} subcategories</span>
                                            </div>
                                            <div class="row g-3">
                                                @foreach($subcats as $subcat)
                                                    @php
                                                        $isMapped = in_array($subcat->id, $selectedSubcategoryIds);
                                                    @endphp
                                                    <div class="col-md-4 col-sm-6">
                                                        <div class="subcat-card d-flex align-items-center justify-content-between p-3 position-relative overflow-hidden {{ $isMapped ? 'active' : '' }}" id="card_{{ $subcat->id }}" onclick="toggleCheckbox('subcat_{{ $subcat->id }}')">
                                                            <div class="subcat-glow"></div>
                                                            <div class="d-flex align-items-center z-index-1">
                                                                <input class="form-check-input ms-0 me-2.5 subcat-checkbox" type="checkbox" name="subcategories[]" value="{{ $subcat->id }}" id="subcat_{{ $subcat->id }}" {{ $isMapped ? 'checked' : '' }} style="width:20px;height:20px;accent-color:#4F46E5; cursor:pointer;" onclick="event.stopPropagation()">
                                                                <label class="form-check-label text-dark fw-semibold mb-0" for="subcat_{{ $subcat->id }}" style="cursor:pointer; font-size:0.88rem;" onclick="event.stopPropagation()">{{ $subcat->name }}</label>
                                                            </div>
                                                            <div class="check-icon-wrapper z-index-1">
                                                                <i class="bi bi-check-circle-fill fs-5 check-icon {{ $isMapped ? '' : 'd-none' }}" style="color:#4F46E5;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="text-center py-5 text-muted border rounded-4 bg-light bg-opacity-50">
                                    <i class="bi bi-folder-x fs-1 d-block mb-3 text-secondary"></i>
                                    <p class="fw-semibold mb-0 text-secondary">No subcategories available to map.</p>
                                    <p class="text-muted small mt-1">Please create category/subcategory first under the catalogue manager.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right side: Configuration & Actions --}}
                <div class="col-lg-4">
                    <div class="card premium-card border-0 mb-4 position-relative overflow-hidden">
                        <div class="card-gradient-overlay"></div>
                        <div class="card-header premium-card-header d-flex align-items-center gap-3 border-0 bg-transparent pt-4 px-4 pb-3">
                            <div class="icon-badge rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width:48px; height:48px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(52, 211, 153, 0.1) 100%); color: #10B981;">
                                <i class="bi bi-sliders fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0 card-title-outfit">PIM Configurations</h5>
                            </div>
                        </div>
                        <div class="card-body p-4 pt-2 d-flex flex-column gap-3">
                            <div class="switch-row position-relative overflow-hidden">
                                <div class="switch-row-glow"></div>
                                <div class="z-index-1">
                                    <label class="fw-bold text-dark d-block mb-0.5" style="font-size:0.88rem; cursor:pointer;" for="is_required">Required Spec</label>
                                    <span class="text-muted d-block" style="font-size:0.75rem;">Force field during product upload</span>
                                </div>
                                <div class="form-check form-switch ps-0 mb-0 z-index-1">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_required" id="is_required" value="1" {{ old('is_required', $attribute->is_required) ? 'checked' : '' }} style="width: 44px; height: 22px; cursor: pointer;">
                                </div>
                            </div>

                            <div class="switch-row position-relative overflow-hidden">
                                <div class="switch-row-glow"></div>
                                <div class="z-index-1">
                                    <label class="fw-bold text-dark d-block mb-0.5" style="font-size:0.88rem; cursor:pointer;" for="is_searchable">Searchable Filter</label>
                                    <span class="text-muted d-block" style="font-size:0.75rem;">Searchable field in catalogs</span>
                                </div>
                                <div class="form-check form-switch ps-0 mb-0 z-index-1">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_searchable" id="is_searchable" value="1" {{ old('is_searchable', $attribute->is_searchable) ? 'checked' : '' }} style="width: 44px; height: 22px; cursor: pointer;">
                                </div>
                            </div>

                            <div class="switch-row position-relative overflow-hidden">
                                <div class="switch-row-glow"></div>
                                <div class="z-index-1">
                                    <label class="fw-bold text-dark d-block mb-0.5" style="font-size:0.88rem; cursor:pointer;" for="is_filterable">Filterable Facet</label>
                                    <span class="text-muted d-block" style="font-size:0.75rem;">Visible on filter sidebars</span>
                                </div>
                                <div class="form-check form-switch ps-0 mb-0 z-index-1">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_filterable" id="is_filterable" value="1" {{ old('is_filterable', $attribute->is_filterable) ? 'checked' : '' }} style="width: 44px; height: 22px; cursor: pointer;">
                                </div>
                            </div>

                            <div class="switch-row position-relative overflow-hidden">
                                <div class="switch-row-glow"></div>
                                <div class="z-index-1">
                                    <label class="fw-bold text-dark d-block mb-0.5" style="font-size:0.88rem; cursor:pointer;" for="is_comparable">Comparable Attribute</label>
                                    <span class="text-muted d-block" style="font-size:0.75rem;">Enable on comparisons</span>
                                </div>
                                <div class="form-check form-switch ps-0 mb-0 z-index-1">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_comparable" id="is_comparable" value="1" {{ old('is_comparable', $attribute->is_comparable) ? 'checked' : '' }} style="width: 44px; height: 22px; cursor: pointer;">
                                </div>
                            </div>

                            <div class="switch-row position-relative overflow-hidden">
                                <div class="switch-row-glow"></div>
                                <div class="z-index-1">
                                    <label class="fw-bold text-dark d-block mb-0.5" style="font-size:0.88rem; cursor:pointer;" for="is_variant_enabled">Variant Enabled</label>
                                    <span class="text-muted d-block" style="font-size:0.75rem;">Generates size/color SKUs</span>
                                </div>
                                <div class="form-check form-switch ps-0 mb-0 z-index-1">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_variant_enabled" id="is_variant_enabled" value="1" {{ old('is_variant_enabled', $attribute->is_variant_enabled) ? 'checked' : '' }} style="width: 44px; height: 22px; cursor: pointer;">
                                </div>
                            </div>

                            <div class="switch-row position-relative overflow-hidden">
                                <div class="switch-row-glow"></div>
                                <div class="z-index-1">
                                    <label class="fw-bold text-dark d-block mb-0.5" style="font-size:0.88rem; cursor:pointer;" for="is_active">Status (Active)</label>
                                    <span class="text-muted d-block" style="font-size:0.75rem;">Disable to temporarily hide attribute</span>
                                </div>
                                <div class="form-check form-switch ps-0 mb-0 z-index-1">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $attribute->is_active) ? 'checked' : '' }} style="width: 44px; height: 22px; cursor: pointer;">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sticky Action Box --}}
                    <div class="card premium-card border-0 mb-4 bg-light position-relative overflow-hidden text-center shadow-sm" style="background: linear-gradient(135deg, #1E1B4B 0%, #312E81 100%) !important;">
                        <div class="subcat-glow" style="opacity: 0.15;"></div>
                        <div class="card-body p-4 position-relative z-index-1">
                            <div class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-10 rounded-pill p-2 mb-3 text-white-50" style="width:60px; height:60px;">
                                <i class="bi bi-check-all fs-1 text-indigo-light" style="color: #A5B4FC;"></i>
                            </div>
                            <h5 class="fw-bold mb-2 text-white card-title-outfit">Confirm Updates?</h5>
                            <p class="text-indigo-light small mb-4 opacity-75" style="font-size:0.85rem; color:#E0E7FF;">Saving changes will immediately update PIM specification templates for all mapped subcategories.</p>
                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-semibold shadow transition-all hover-translate-y" style="background:#4F46E5; border-color:#4F46E5; font-family:'Outfit', sans-serif;">
                                    <i class="bi bi-check-circle me-1.5"></i> Save Changes
                                </button>
                                <a href="{{ route('admin.attributes.index') }}" class="btn btn-outline-light rounded-pill py-2.5 fw-semibold transition-all" style="border-color: rgba(255,255,255,0.2); color:#fff; font-family:'Outfit', sans-serif;" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Dynamic Sticky Save Bar --}}
<div class="glass-save-bar d-flex justify-content-end align-items-center gap-3">
    <div class="text-muted small me-auto d-none d-md-flex align-items-center gap-2">
        <div class="spinner-pulse"></div>
        <i class="bi bi-info-circle-fill text-indigo fs-5" style="color:#4F46E5;"></i>
        <span class="fw-semibold text-dark-slate" style="font-size:0.88rem;">You have unsaved changes in this global attribute template.</span>
    </div>
    <a href="{{ route('admin.attributes.index') }}" class="btn btn-light rounded-pill px-4 py-2 fw-semibold text-secondary shadow-sm transition-all" style="background:#fff; border:1px solid #E2E8F0; font-family:'Outfit', sans-serif; font-size:0.85rem;">Cancel</a>
    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm transition-all hover-translate-y" style="background:#4F46E5; border-color:#4F46E5; font-family:'Outfit', sans-serif; font-size:0.85rem;" onclick="document.getElementById('attribute-form').submit()">Save Changes</button>
</div>

@endsection

@push('js')
<script>
let optionIndex = {{ $attribute->options->count() }};

function checkType() {
    const type = document.getElementById('attr-type-select').value;
    const builder = document.getElementById('options-builder-card');
    const selectTypes = ['select', 'multiselect', 'checkbox', 'radio'];

    if (selectTypes.includes(type)) {
        builder.style.display = 'block';
    } else {
        builder.style.display = 'none';
    }
}

function addOptionRow() {
    const tbody = document.getElementById('options-tbody');
    const tr = document.createElement('tr');
    tr.className = 'align-middle option-row-premium';
    
    const colorVal = '#4F46E5';
    
    // Fixed: Template variables interpolated without the backslash escaping!
    tr.innerHTML = `
        <td class="ps-0">
            <input type="text" name="options[${optionIndex}][label]" class="form-control premium-input px-3 py-2 text-dark fw-medium" placeholder="e.g. Red, XL, 220V" required style="font-size:0.85rem;" oninput="generateOptionValue(this, ${optionIndex})">
        </td>
        <td>
            <input type="text" name="options[${optionIndex}][value]" id="option_val_${optionIndex}" class="form-control premium-input px-3 py-2 text-muted" placeholder="e.g. red, xl, 220v" style="font-size:0.85rem; background-color:#F1F5F9 !important;">
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
            <div class="checkbox-circle-premium d-inline-block">
                <input type="checkbox" name="options[${optionIndex}][is_default]" value="1" class="form-check-input default-check" style="width:20px;height:20px;accent-color:#4F46E5;cursor:pointer;" onclick="onlyOneDefault(this)">
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-trash-premium" title="Remove option" onclick="this.closest('tr').remove(); showSticky();">
                <i class="bi bi-trash3-fill"></i>
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

// Auto-generates slugs for options label as values
function generateOptionValue(input, index) {
    const valInput = document.getElementById(`option_val_${index}`);
    if (valInput) {
        valInput.value = input.value.trim().toLowerCase()
            .replace(/[^a-z0-9 -]/g, '') // remove invalid chars
            .replace(/\s+/g, '-')        // collapse whitespace and replace by -
            .replace(/-+/g, '-');        // collapse dashes
    }
}

// Keep only one default checkbox checked
function onlyOneDefault(chk) {
    if (chk.checked) {
        document.querySelectorAll('.default-check').forEach(c => {
            if (c !== chk) c.checked = false;
        });
    }
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

function toggleCheckbox(id) {
    const checkbox = document.getElementById(id);
    if (checkbox) {
        checkbox.checked = !checkbox.checked;
        checkbox.dispatchEvent(new Event('change'));
    }
}

const showSticky = () => {
    const stickyBar = document.querySelector('.glass-save-bar');
    if (stickyBar) {
        stickyBar.classList.add('show');
    }
};

// Hook visual toggling on actual checkbox click too
document.addEventListener('change', function(e) {
    if (e.target && e.target.classList.contains('subcat-checkbox')) {
        const chk = e.target;
        const card = chk.closest('.subcat-card');
        if (card) {
            const checkIcon = card.querySelector('.check-icon');
            if (chk.checked) {
                card.classList.add('active');
                if (checkIcon) checkIcon.classList.remove('d-none');
            } else {
                card.classList.remove('active');
                if (checkIcon) checkIcon.classList.add('d-none');
            }
        }
    }
});

// Sticky footer logic on input changes
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('attribute-form');
    
    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('change', showSticky);
        el.addEventListener('keyup', showSticky);
    });

    document.getElementById('attr-type-select').addEventListener('change', checkType);
    
    // Handle initial checkbox state
    document.querySelectorAll('.subcat-checkbox:checked').forEach(chk => {
        const card = chk.closest('.subcat-card');
        if (card) {
            card.classList.add('active');
            const checkIcon = card.querySelector('.check-icon');
            if (checkIcon) checkIcon.classList.remove('d-none');
        }
    });

    checkType();
});
</script>

<style>
/* Design Tokens & CSS resets */
:root {
    --primary-indigo: #4F46E5;
    --primary-indigo-light: rgba(79, 70, 229, 0.05);
    --primary-indigo-glow: rgba(79, 70, 229, 0.15);
    --border-color-soft: rgba(226, 232, 240, 0.7);
    --input-focus-border: #6366F1;
    --trash-red: #EF4444;
    --trash-red-light: rgba(239, 68, 68, 0.08);
    --dark-slate: #0F172A;
}

/* Premium Card Overrides */
.premium-card {
    background: #ffffff;
    border: 1px solid var(--border-color-soft) !important;
    box-shadow: 0 12px 34px -10px rgba(0, 0, 0, 0.04), 0 2px 8px rgba(0, 0, 0, 0.01) !important;
    border-radius: 20px !important;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.premium-card:hover {
    box-shadow: 0 20px 45px -12px rgba(0, 0, 0, 0.06), 0 4px 12px rgba(0, 0, 0, 0.02) !important;
    transform: translateY(-2px);
}
.card-gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(to right, #4F46E5 0%, #818CF8 50%, #C084FC 100%);
    opacity: 0.85;
}

.card-title-outfit {
    font-family: 'Outfit', sans-serif !important;
    font-weight: 700 !important;
    color: #1E293B;
}

/* Input design system */
.premium-label {
    font-family: 'Outfit', sans-serif;
    font-size: 0.72rem !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 0.9px;
    color: #475569 !important;
    margin-bottom: 8px;
}
.input-group-premium {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}
.input-icon {
    position: absolute;
    left: 16px;
    z-index: 10;
    pointer-events: none;
    display: flex;
    align-items: center;
    font-size: 0.92rem;
    color: #94A3B8;
}
.input-icon + .premium-input {
    padding-left: 44px !important;
}
.premium-input {
    border: 1.5px solid #E2E8F0 !important;
    border-radius: 14px !important;
    padding: 11px 18px !important;
    font-size: 0.9rem !important;
    color: #0F172A !important;
    background-color: #F8FAFC !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    font-weight: 500;
    width: 100%;
}
.premium-input::placeholder {
    color: #94A3B8 !important;
    font-size: 0.85rem;
    font-weight: 400;
}
.premium-input:focus {
    background-color: #ffffff !important;
    border-color: var(--input-focus-border) !important;
    box-shadow: 0 0 0 4px var(--primary-indigo-glow) !important;
    outline: none !important;
}

/* Mapping Cards */
.subcat-card {
    background: #F8FAFC;
    border: 1.5px solid #E2E8F0;
    border-radius: 16px;
    padding: 16px 20px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
}
.subcat-glow {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.03) 0%, rgba(129, 140, 248, 0.03) 100%);
    opacity: 0;
    transition: opacity 0.25s ease;
    pointer-events: none;
}
.subcat-card:hover {
    border-color: var(--input-focus-border);
    background-color: #ffffff;
    transform: translateY(-2.5px) scale(1.01);
    box-shadow: 0 10px 24px -8px rgba(79, 70, 229, 0.12);
}
.subcat-card:hover .subcat-glow {
    opacity: 1;
}
.subcat-card.active {
    border-color: var(--primary-indigo) !important;
    background-color: #EEF2FF !important;
    box-shadow: 0 8px 24px -6px rgba(79, 70, 229, 0.18) !important;
}
.subcat-card.active .subcat-glow {
    opacity: 0.6;
}

/* Switches Panel */
.switch-row {
    padding: 18px 22px;
    border-radius: 16px;
    border: 1px solid var(--border-color-soft);
    background: #F8FAFC;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.switch-row-glow {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to right, rgba(79, 70, 229, 0.01), rgba(129, 140, 248, 0.01));
    pointer-events: none;
}
.switch-row:hover {
    border-color: #CBD5E1;
    background: #ffffff;
    transform: translateY(-1.5px);
    box-shadow: 0 6px 16px -6px rgba(0, 0, 0, 0.04);
}

/* Styled color picker */
.color-picker-wrapper {
    position: relative;
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #E2E8F0;
    box-shadow: 0 3px 6px rgba(0,0,0,0.06);
    transition: all 0.2s ease;
}
.color-picker-wrapper:hover {
    transform: scale(1.1);
    border-color: var(--primary-indigo);
}
.color-picker-input {
    position: absolute;
    top: -5px;
    left: -5px;
    width: 48px;
    height: 48px;
    padding: 0;
    border: 0;
    cursor: pointer;
    background: transparent;
}

/* Glass Floating Save Bar */
.glass-save-bar {
    position: fixed;
    bottom: 24px;
    left: 24px;
    right: 24px;
    background: rgba(255, 255, 255, 0.8) !important;
    backdrop-filter: blur(20px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
    border: 1px solid rgba(255, 255, 255, 0.5) !important;
    box-shadow: 0 30px 60px -15px rgba(15, 23, 42, 0.15), 0 0 0 1px rgba(15, 23, 42, 0.02) !important;
    border-radius: 24px !important;
    padding: 16px 32px !important;
    z-index: 1050;
    transform: translateY(180px);
    transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.glass-save-bar.show {
    transform: translateY(0);
}

.spinner-pulse {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: var(--primary-indigo);
    display: inline-block;
    animation: pulse 1.6s infinite ease-in-out;
    margin-right: 4px;
}
@keyframes pulse {
    0% { transform: scale(0.8); opacity: 0.5; }
    50% { transform: scale(1.3); opacity: 1; }
    100% { transform: scale(0.8); opacity: 0.5; }
}

/* Premium Trash button */
.btn-trash-premium {
    color: var(--trash-red);
    background: var(--trash-red-light);
    border: 0;
    border-radius: 12px;
    padding: 9px 12px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.95rem;
}
.btn-trash-premium:hover {
    transform: scale(1.15) rotate(4deg);
    background-color: var(--trash-red);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
}

/* Tables and option rows */
.options-table th {
    font-family: 'Outfit', sans-serif;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.9px;
    text-transform: uppercase;
    color: #64748B;
    border-bottom: 2px solid #F1F5F9 !important;
}
.options-table td {
    padding: 14px 10px !important;
    border-bottom: 1.5px solid #F1F5F9 !important;
}
.option-row-premium {
    animation: rowSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes rowSlideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.border-light-premium {
    border-bottom: 1.5px solid #EEF2FF !important;
}
.text-indigo-light {
    color: #C7D2FE;
}
.z-index-1 {
    z-index: 1;
}

/* Utility Animations */
.transition-all {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.hover-translate-y:hover {
    transform: translateY(-2px) !important;
}
</style>
@endpush
