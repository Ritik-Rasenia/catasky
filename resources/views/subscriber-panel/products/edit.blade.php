@extends('subscriber-panel.layouts.app')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')
@section('breadcrumb', '<a href="' . route('subscriber.products.index') . '">Products</a> → Edit ' . $product->name)

@section('content')

<form action="{{ route('subscriber.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="product-form">
@csrf
@method('PUT')

<div class="row g-3">
    {{-- Left: Main Info --}}
    <div class="col-lg-8">

        {{-- Basic Info --}}
        <div class="vp-card mb-3">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-info-circle me-2"></i>Product Information</h6>
            </div>
            <div class="vp-card-body">
                <div class="vp-form-group">
                    <label class="vp-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="vp-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           placeholder="Enter product name" value="{{ old('name', $product->name) }}" required>
                    @error('name') <div class="invalid-feedback d-block" style="color:#EF4444;font-size:0.78rem;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="vp-form-group">
                            <label class="vp-label">SKU / Part Code</label>
                            <input type="text" name="sku" class="vp-input" placeholder="SKU-001" value="{{ old('sku', $product->sku) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="vp-form-group">
                            <label class="vp-label">Category</label>
                            <select name="category_id" class="vp-select" id="category-select">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="vp-form-group">
                            <label class="vp-label">Subcategory</label>
                            <select name="subcategory_id" class="vp-select" id="subcategory-select">
                                <option value="">Select Subcategory</option>
                                @foreach($subcategories as $s)
                                    <option value="{{ $s->id }}" {{ old('subcategory_id', $product->subcategory_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="vp-form-group">
                    <label class="vp-label">Short Description</label>
                    <textarea name="short_description" class="vp-textarea" rows="2"
                              placeholder="Brief product summary (shown on cards and share pages)">{{ old('short_description', $product->short_description) }}</textarea>
                </div>
                <div class="vp-form-group">
                    <label class="vp-label">Full Description</label>
                    <textarea name="full_description" class="vp-textarea" rows="5"
                              placeholder="Detailed product description...">{{ old('full_description', $product->full_description) }}</textarea>
                </div>
                <div class="vp-form-group">
                    <label class="vp-label">Tags <small style="font-weight:400;color:#94A3B8;">(comma separated)</small></label>
                    <input type="text" name="tags" class="vp-input" placeholder="electrical, switches, indoor" value="{{ old('tags', $product->tags ? implode(', ', $product->tags) : '') }}">
                </div>
            </div>
        </div>

        {{-- Images --}}
        <div class="vp-card mb-3">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-images me-2"></i>Product Images</h6>
            </div>
            <div class="vp-card-body">
                <div class="vp-form-group">
                    <label class="vp-label">Thumbnail (Main Image)</label>
                    <input type="file" name="thumbnail" class="vp-input" accept="image/*" style="padding:8px 12px;" id="thumbnail-input" onchange="previewThumbnail(event)">
                    <div id="thumbnail-preview" class="mt-2">
                        @if($product->thumbnail)
                            <img id="thumb-img" src="{{ $product->thumbnail_url }}" alt="Preview" style="height:120px;border-radius:10px;object-fit:cover;border:2px solid #E2E8F0;">
                        @else
                            <img id="thumb-img" src="" alt="Preview" style="height:120px;border-radius:10px;object-fit:cover;border:2px solid #E2E8F0; display:none;">
                        @endif
                    </div>
                </div>
                <div class="vp-form-group">
                    <label class="vp-label">Additional Images <small style="font-weight:400;color:#94A3B8;">(multiple)</small></label>
                    <input type="file" name="images[]" class="vp-input" accept="image/*" multiple style="padding:8px 12px;" id="images-input" onchange="previewImages(event)">
                    
                    {{-- Existing Additional Images --}}
                    @if($product->images->count() > 0)
                    <div class="mt-3">
                        <label class="vp-label" style="font-size:0.75rem;">Existing Images (Click 🗑️ to delete)</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($product->images as $img)
                            <div class="position-relative img-wrap-{{ $img->id }}" style="height:80px;width:80px;">
                                <img src="{{ $img->image_url }}" style="height:80px;width:80px;border-radius:8px;object-fit:cover;border:1px solid #E2E8F0;">
                                <button type="button" onclick="deleteProductImage({{ $img->id }})" 
                                        style="position:absolute;top:4px;right:4px;background:rgba(239,68,68,0.9);color:white;border:none;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-size:0.75rem;cursor:pointer;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div id="images-preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>

        {{-- Dynamic Attributes Template --}}
        <div class="vp-card mb-3 d-none" id="dynamic-attributes-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-sliders me-2 text-primary"></i>Product Specifications (PIM Template)</h6>
            </div>
            <div class="vp-card-body" id="dynamic-attributes-container">
                <!-- Dynamically loaded via category selection AJAX -->
            </div>
        </div>
    </div>

    {{-- Right: Sidebar Options --}}
    <div class="col-lg-4">

        {{-- Pricing --}}
        <div class="vp-card mb-3">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-tag-fill me-2"></i>Pricing</h6>
            </div>
            <div class="vp-card-body">
                <div class="vp-form-group">
                    <label class="vp-label">MRP (₹)</label>
                    <input type="number" name="mrp" class="vp-input" placeholder="0.00" step="0.01" value="{{ old('mrp', $product->mrp) }}">
                </div>
                <div class="vp-form-group">
                    <label class="vp-label">Offer Price (₹)</label>
                    <input type="number" name="offer_price" class="vp-input" placeholder="0.00" step="0.01" value="{{ old('offer_price', $product->offer_price) }}">
                </div>
                <div id="discount-preview" style="display:none;background:#DCFCE7;border-radius:10px;padding:10px 14px;font-size:0.82rem;color:#166534;">
                    <i class="bi bi-arrow-down-circle me-1"></i> <span id="discount-text"></span>
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="vp-card mb-3">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-toggle-on me-2"></i>Status & Visibility</h6>
            </div>
            <div class="vp-card-body">
                <div class="vp-form-group">
                    <label class="vp-label">Product Status</label>
                    <select name="status" class="vp-select">
                        <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <label class="vp-toggle mb-2">
                    <input type="checkbox" name="featured" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                    <span class="vp-toggle-label">Mark as Featured</span>
                </label>
            </div>
        </div>

        {{-- PDF Controls --}}
        <div class="vp-card mb-3">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-file-pdf me-2" style="color:#EF4444;"></i>PDF Visibility</h6>
                <small style="color:#94A3B8;font-size:0.72rem;">What shows in PDF</small>
            </div>
            <div class="vp-card-body">
                @foreach([
                    ['pdf_show_mrp', 'Show MRP'],
                    ['pdf_show_offer_price', 'Show Offer Price'],
                    ['pdf_show_short_desc', 'Show Short Description'],
                    ['pdf_show_description', 'Show Full Description'],
                    ['pdf_show_attributes', 'Show Attributes'],
                    ['pdf_show_images', 'Show Images'],
                ] as [$field, $label])
                <label class="vp-toggle mb-2 d-flex">
                    <input type="checkbox" name="{{ $field }}" {{ old($field, $product->{$field}) ? 'checked' : '' }}>
                    <span class="vp-toggle-label">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Share Controls --}}
        <div class="vp-card mb-3">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-share me-2" style="color:#10B981;"></i>Share Page Visibility</h6>
            </div>
            <div class="vp-card-body">
                @foreach([
                    ['share_show_mrp', 'Show MRP'],
                    ['share_show_offer_price', 'Show Offer Price'],
                    ['share_show_description', 'Show Description'],
                    ['share_show_attributes', 'Show Attributes'],
                ] as [$field, $label])
                <label class="vp-toggle mb-2 d-flex">
                    <input type="checkbox" name="{{ $field }}" {{ old($field, $product->{$field}) ? 'checked' : '' }}>
                    <span class="vp-toggle-label">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Submit --}}
        <div class="d-grid gap-2">
            <button type="submit" class="btn-subscriber">
                <i class="bi bi-check-circle"></i> Save Changes
            </button>
            <a href="{{ route('subscriber.products.index') }}" class="btn-subscriber-outline text-center">
                Cancel
            </a>
        </div>
    </div>
</div>

</form>

@endsection

@push('js')
<script>
function previewThumbnail(e) {
    const file = e.target.files[0];
    if (file) {
        const url = URL.createObjectURL(file);
        document.getElementById('thumb-img').src = url;
        document.getElementById('thumb-img').style.display = 'block';
    }
}

function previewImages(e) {
    const container = document.getElementById('images-preview');
    container.innerHTML = '';
    Array.from(e.target.files).forEach(file => {
        const url = URL.createObjectURL(file);
        const img = document.createElement('img');
        img.src = url;
        img.style.cssText = 'height:80px;border-radius:8px;object-fit:cover;border:2px solid #E2E8F0;';
        container.appendChild(img);
    });
}

function deleteProductImage(imageId) {
    Swal.fire({
        title: 'Delete image?',
        text: "You can't undo this action!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ url('/subscriber/product-images') }}/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`.img-wrap-${imageId}`).remove();
                    Swal.fire('Deleted!', 'Image deleted successfully.', 'success');
                } else {
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            });
        }
    });
}

// Discount calculator
const mrpInput = document.querySelector('[name="mrp"]');
const offerInput = document.querySelector('[name="offer_price"]');
const preview = document.getElementById('discount-preview');
const discountText = document.getElementById('discount-text');

function calcDiscount() {
    const mrp = parseFloat(mrpInput.value);
    const offer = parseFloat(offerInput.value);
    if (mrp > 0 && offer > 0 && mrp > offer) {
        const pct = Math.round(((mrp - offer) / mrp) * 100);
        discountText.textContent = `${pct}% OFF — Customer saves ₹${(mrp - offer).toFixed(2)}`;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

mrpInput?.addEventListener('input', calcDiscount);
offerInput?.addEventListener('input', calcDiscount);

// Run discount calc initially
document.addEventListener('DOMContentLoaded', calcDiscount);

// Dynamic PIM Template Loader & Pre-population
$(document).ready(function() {
    const categorySelect = $('#category-select');
    const attrCard = $('#dynamic-attributes-card');
    const container = $('#dynamic-attributes-container');
    
    // Existing values from blade variables
    const existingValues = @json($existingValues->mapWithKeys(function($val) {
        return [$val->attribute_id => $val->value];
    }));

    function loadAttributesForSubcategory(subcategoryId) {
        if (!subcategoryId) {
            attrCard.addClass('d-none');
            container.empty();
            return;
        }

        container.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Loading PIM template...</div></div>');
        attrCard.removeClass('d-none');

        const attrUrlBase = "{{ url('dashboard/api/subcategory-attributes') }}";

        $.ajax({
            url: attrUrlBase + '/' + subcategoryId,
            type: 'GET',
            success: function(groups) {
                container.empty();
                if (groups.length === 0) {
                    attrCard.addClass('d-none');
                    return;
                }

                groups.forEach(group => {
                    let groupHtml = `
                        <div class="mb-4">
                            <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94A3B8;border-bottom:1px solid #F1F5F9;padding-bottom:8px;margin-bottom:16px;">
                                ${group.group_name}
                            </div>
                            <div class="row g-3">
                    `;

                    group.attributes.forEach(attr => {
                        const requiredStar = attr.is_required ? '<span class="text-danger">*</span>' : '';
                        const requiredAttr = attr.is_required ? 'required' : '';
                        const unitLabel = attr.unit ? ` <small style="font-weight:400;">(${attr.unit})</small>` : '';
                        const isFullWidth = ['textarea', 'image', 'file'].includes(attr.type);
                        const colClass = isFullWidth ? 'col-12' : 'col-md-6';
                        
                        // Parse existing value
                        let rawVal = existingValues[attr.id];
                        let parsedVal = null;
                        if (rawVal !== undefined && rawVal !== null) {
                            try {
                                parsedVal = JSON.parse(rawVal);
                            } catch(e) {
                                parsedVal = rawVal;
                            }
                        }
                        
                        let inputHtml = '';
                        
                        switch(attr.type) {
                            case 'text':
                            case 'url':
                                const textVal = rawVal !== undefined ? rawVal : (attr.default_value || '');
                                inputHtml = `<input type="${attr.type === 'url' ? 'url' : 'text'}" name="attributes[${attr.id}]" class="vp-input" placeholder="${attr.placeholder || ''}" value="${textVal}" ${requiredAttr}>`;
                                break;
                            case 'number':
                                const numVal = rawVal !== undefined ? rawVal : (attr.default_value || '');
                                inputHtml = `<input type="number" name="attributes[${attr.id}]" class="vp-input" placeholder="${attr.placeholder || '0'}" value="${numVal}" ${requiredAttr}>`;
                                break;
                            case 'date':
                                const dateVal = rawVal !== undefined ? rawVal : (attr.default_value || '');
                                inputHtml = `<input type="date" name="attributes[${attr.id}]" class="vp-input" value="${dateVal}" ${requiredAttr}>`;
                                break;
                            case 'color':
                                const defaultColor = rawVal !== undefined ? rawVal : (attr.default_value || '#4F46E5');
                                inputHtml = `
                                    <div class="d-flex gap-2">
                                        <input type="color" name="attributes[${attr.id}]" value="${defaultColor}" style="width:50px;height:42px;border-radius:8px;border:1.5px solid #E2E8F0;cursor:pointer;padding:2px;" oninput="$('#color-text-${attr.id}').val(this.value)">
                                        <input type="text" class="vp-input" readonly style="flex:1;" value="${defaultColor}" id="color-text-${attr.id}">
                                    </div>
                                `;
                                break;
                            case 'textarea':
                                const textareaVal = rawVal !== undefined ? rawVal : (attr.default_value || '');
                                inputHtml = `<textarea name="attributes[${attr.id}]" class="vp-textarea" rows="3" placeholder="${attr.placeholder || ''}" ${requiredAttr}>${textareaVal}</textarea>`;
                                break;
                            case 'select':
                                let selectOptions = `<option value="">-- Select --</option>`;
                                attr.options.forEach(opt => {
                                    const isSelected = (rawVal !== undefined && String(opt.value) === String(rawVal)) || (rawVal === undefined && opt.is_default);
                                    const selected = isSelected ? 'selected' : '';
                                    selectOptions += `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
                                });
                                inputHtml = `<select name="attributes[${attr.id}]" class="vp-select" ${requiredAttr}>${selectOptions}</select>`;
                                break;
                            case 'multiselect':
                                let multiOptions = '';
                                attr.options.forEach(opt => {
                                    const isSelected = Array.isArray(parsedVal) && parsedVal.map(String).includes(String(opt.value));
                                    multiOptions += `<option value="${opt.value}" ${isSelected ? 'selected' : ''}>${opt.label}</option>`;
                                });
                                inputHtml = `<select name="attributes[${attr.id}][]" class="vp-select" multiple style="height:auto;min-height:80px;" ${requiredAttr}>${multiOptions}</select>`;
                                break;
                            case 'radio':
                                let radioOptions = '<div class="d-flex flex-wrap gap-3 mt-1">';
                                attr.options.forEach(opt => {
                                    const isChecked = (rawVal !== undefined && String(opt.value) === String(rawVal)) || (rawVal === undefined && opt.is_default);
                                    radioOptions += `
                                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:#475569;font-size:0.875rem;text-transform:none;letter-spacing:0;">
                                            <input type="radio" name="attributes[${attr.id}]" value="${opt.value}" ${isChecked ? 'checked' : ''} style="accent-color:#4F46E5;width:16px;height:16px;">
                                            ${opt.label}
                                        </label>
                                    `;
                                });
                                radioOptions += '</div>';
                                inputHtml = radioOptions;
                                break;
                            case 'checkbox':
                                let checkOptions = '<div class="d-flex flex-wrap gap-3 mt-1">';
                                attr.options.forEach(opt => {
                                    const isChecked = Array.isArray(parsedVal) && parsedVal.map(String).includes(String(opt.value));
                                    checkOptions += `
                                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:#475569;font-size:0.875rem;text-transform:none;letter-spacing:0;">
                                            <input type="checkbox" name="attributes[${attr.id}][]" value="${opt.value}" ${isChecked ? 'checked' : ''} style="accent-color:#4F46E5;width:16px;height:16px;">
                                            ${opt.label}
                                        </label>
                                    `;
                                });
                                checkOptions += '</div>';
                                inputHtml = checkOptions;
                                break;
                            case 'image':
                            case 'file':
                                let currentFileHtml = '';
                                if (rawVal) {
                                    currentFileHtml = `
                                        <div class="mb-2" style="font-size:0.82rem;color:#4F46E5;">
                                            <i class="bi bi-file-earmark-check"></i> Current: 
                                            <a href="/uploads/subscriber-products/${rawVal}" target="_blank">${rawVal}</a>
                                        </div>
                                    `;
                                }
                                inputHtml = `
                                    ${currentFileHtml}
                                    <input type="${attr.type}" name="attributes[${attr.id}]" class="vp-input" style="padding:8px 12px;" accept="${attr.type === 'image' ? 'image/*' : '*'}" ${requiredAttr && !rawVal ? 'required' : ''}>
                                `;
                                break;
                        }

                        groupHtml += `
                            <div class="${colClass}">
                                <div class="vp-form-group mb-0">
                                    <label class="vp-label">${attr.name}${unitLabel}${requiredStar}</label>
                                    ${inputHtml}
                                </div>
                            </div>
                        `;
                    });

                    groupHtml += `
                            </div>
                        </div>
                    `;

                    container.append(groupHtml);
                });
            },
            error: function() {
                container.html('<div class="text-danger py-4"><i class="bi bi-x-circle me-1"></i> Failed to load template.</div>');
            }
        });
    }

    const subcategorySelect = $('#subcategory-select');
    const subcatUrl = "{{ url('dashboard/get-subcategories') }}";

    categorySelect.on('change', function() {
        const catId = $(this).val();
        subcategorySelect.html('<option>Loading...</option>');
        if (!catId) {
            subcategorySelect.html('<option value="">Select Subcategory</option>');
            container.empty();
            attrCard.addClass('d-none');
            return;
        }

        $.get(subcatUrl, { category_id: catId }, function(data) {
            let opts = '<option value="">Select Subcategory</option>';
            data.forEach(function(s) {
                opts += `<option value="${s.id}">${s.name}</option>`;
            });
            subcategorySelect.html(opts);
        }).fail(function() {
            subcategorySelect.html('<option value="">Select Subcategory</option>');
        });
    });

    subcategorySelect.on('change', function() {
        loadAttributesForSubcategory($(this).val());
    });

    if (subcategorySelect.val()) {
        loadAttributesForSubcategory(subcategorySelect.val());
    } else if (categorySelect.val()) {
        categorySelect.trigger('change');
    }
});
</script>
@endpush
