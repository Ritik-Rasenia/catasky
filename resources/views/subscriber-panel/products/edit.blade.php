@extends('subscriber-panel.layouts.app')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('subscriber.products.index') }}">Products</a></li>
            <li class="breadcrumb-item active">Edit {{ $product->name }}</li>
        </ol>
    </nav>
@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #4f46e5;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 2px 8px;
    }
    .ck-editor__editable { min-height: 200px; }
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    <form action="{{ route('subscriber.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="product-form">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Left: Main Content Column --}}
            <div class="col-lg-8">
                
                {{-- Basic Information --}}
                <div class="card border-0  rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Basic Information</h6>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3 {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                       placeholder="Enter product name" value="{{ old('name', $product->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">SKU / Part Code</label>
                                <input type="text" name="sku" class="form-control rounded-3" placeholder="Unique SKU / Part code" value="{{ old('sku', $product->sku) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Short Description</label>
                                <textarea name="short_description" class="form-control rounded-3" rows="3"
                                          placeholder="Brief product summary (shown on cards and share pages)">{{ old('short_description', $product->short_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dynamic Attributes Template --}}
                <div class="card border-0  rounded-4 mb-4 d-none" id="dynamic-attributes-card">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-sliders me-2 text-primary"></i>Product Specifications (PIM Template)</h6>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="add-custom-spec-btn" style="font-size:0.78rem; font-weight:600; display:inline-flex; align-items:center; gap:5px;">
                            <i class="bi bi-plus-circle"></i> Add Custom Specification
                        </button>
                    </div>
                    <div class="card-body p-4 pt-0" id="dynamic-attributes-container">
                        <!-- Dynamically loaded via category/subcategory selection AJAX -->
                    </div>
                </div>

                {{-- Detailed Content --}}
                <div class="card border-0  rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-text-paragraph me-2 text-primary"></i>Detailed Content</h6>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Full Description</label>
                            <textarea name="full_description" class="form-control editor">{{ old('full_description', $product->full_description) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Tags <small class="text-muted fw-normal">(comma separated)</small></label>
                            <input type="text" name="tags" class="form-control rounded-3" placeholder="electrical, switches, indoor" value="{{ old('tags', $product->tags ? implode(', ', $product->tags) : '') }}">
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right: Sidebar Controls Column --}}
            <div class="col-lg-4">
                
                {{-- Classification & Pricing --}}
                <div class="card border-0  rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-tag-fill me-2 text-primary"></i>Classification & Pricing</h6>
                    </div>
                    <div class="card-body px-4 pb-4 pt-0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Category *</label>
                            <select name="category_id" class="form-select rounded-3" id="category-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Subcategory *</label>
                            <select name="subcategory_id" class="form-select rounded-3" id="subcategory-select" required>
                                <option value="">Select Subcategory</option>
                                @foreach($subcategories as $s)
                                    <option value="{{ $s->id }}" {{ old('subcategory_id', $product->subcategory_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 {{ $productTypes->isEmpty() ? 'd-none' : '' }}" id="product-type-group">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Product Type</label>
                            <select name="child_category_id" class="form-select rounded-3" id="product-type-select">
                                <option value="">Select Product Type</option>
                                @foreach($productTypes as $pt)
                                    <option value="{{ $pt->id }}" {{ old('child_category_id', $product->child_category_id) == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">MRP (₹)</label>
                            <input type="number" name="mrp" class="form-control rounded-3" placeholder="0.00" step="0.01" value="{{ old('mrp', $product->mrp) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Offer Price (₹)</label>
                            <input type="number" name="offer_price" class="form-control rounded-3" placeholder="0.00" step="0.01" value="{{ old('offer_price', $product->offer_price) }}">
                        </div>
                        <div id="discount-preview" style="display:none;background:#DCFCE7;border-radius:10px;padding:10px 14px;font-size:0.82rem;color:#166534;">
                            <i class="bi bi-arrow-down-circle me-1"></i> <span id="discount-text"></span>
                        </div>
                    </div>
                </div>

                {{-- Product Media --}}
                <div class="card border-0  rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-images me-2 text-primary"></i>Product Media</h6>
                    </div>
                    <div class="card-body px-4 pb-4 pt-0">
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Main Thumbnail (Main Image)</label>
                            <div class="p-3 border border-dashed rounded-4 text-center bg-light">
                                <input type="file" name="thumbnail" class="form-control form-control-sm" accept="image/*" id="thumbnail-input" onchange="previewThumbnail(event)">
                            </div>
                            <div id="thumbnail-preview" class="mt-2 text-center">
                                @if($product->thumbnail)
                                    <img id="thumb-img" src="{{ $product->thumbnail_url }}" alt="Preview" style="max-height:120px;border-radius:10px;object-fit:cover;border:1px solid #E2E8F0;" class="p-1 bg-white ">
                                @else
                                    <img id="thumb-img" src="" alt="Preview" style="max-height:120px;border-radius:10px;object-fit:cover;border:1px solid #E2E8F0; display:none;" class="p-1 bg-white ">
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold small text-muted text-uppercase">Additional Images <small class="text-muted fw-normal">(multiple)</small></label>
                            
                            {{-- Existing Additional Images --}}
                            @if($product->images->count() > 0)
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted">Existing Images <small class="text-muted fw-normal">(Click trash to delete)</small></label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($product->images as $img)
                                    <div class="position-relative img-wrap-{{ $img->id }}" style="height:70px;width:70px;">
                                        <img src="{{ $img->image_url }}" style="height:70px;width:70px;border-radius:8px;object-fit:cover;border:1px solid #E2E8F0;" class=" p-0.5 bg-white">
                                        <button type="button" onclick="deleteProductImage({{ $img->id }})"
                                                style="position:absolute;top:4px;right:4px;background:rgba(239,68,68,0.9);color:white;border:none;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:0.7rem;cursor:pointer;" title="Delete Image">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div class="p-3 border border-dashed rounded-4 text-center bg-light">
                                <input type="file" name="images[]" class="form-control form-control-sm" accept="image/*" multiple id="images-input" onchange="previewImages(event)">
                            </div>
                            <div id="images-preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </div>

                {{-- Status & Visibility --}}
                <div class="card border-0  rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-toggle-on me-2 text-primary"></i>Status & Visibility</h6>
                    </div>
                    <div class="card-body px-4 pb-4 pt-0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Product Status</label>
                            <select name="status" class="form-select rounded-3">
                                <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="featured" id="featuredCheck" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold small" for="featuredCheck">Mark as Featured</label>
                        </div>
                    </div>
                </div>

                {{-- PDF Controls --}}
                <div class="card border-0  rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-file-pdf me-2 text-danger"></i>PDF Visibility</h6>
                    </div>
                    <div class="card-body px-4 pb-4 pt-0">
                        @foreach([
                            ['pdf_show_mrp', 'Show MRP'],
                            ['pdf_show_offer_price', 'Show Offer Price'],
                            ['pdf_show_short_desc', 'Show Short Description'],
                            ['pdf_show_description', 'Show Full Description'],
                            ['pdf_show_attributes', 'Show Attributes'],
                            ['pdf_show_images', 'Show Images'],
                        ] as [$field, $label])
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}_edit" {{ old($field, $product->{$field}) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold small" for="{{ $field }}_edit">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Share Controls --}}
                <div class="card border-0  rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-share me-2 text-success"></i>Share Page Visibility</h6>
                    </div>
                    <div class="card-body px-4 pb-4 pt-0">
                        @foreach([
                            ['share_show_mrp', 'Show MRP'],
                            ['share_show_offer_price', 'Show Offer Price'],
                            ['share_show_description', 'Show Description'],
                            ['share_show_attributes', 'Show Attributes'],
                        ] as [$field, $label])
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}_edit" {{ old($field, $product->{$field}) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold small" for="{{ $field }}_edit">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Submit Actions (Sticky Card) --}}
                <div class="card border-0  rounded-4 sticky-top" style="top: 100px; z-index: 1;">
                    <div class="card-body p-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 mb-2 shadow fw-bold">
                            <i class="bi bi-check-circle me-2"></i>Save Changes
                        </button>
                        <a href="{{ route('subscriber.products.index') }}" class="btn btn-outline-secondary w-100 rounded-pill py-2">
                            Cancel
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </form>
</div>

<!-- Add Custom Specification Modal -->
<div class="modal fade" id="customSpecModal" tabindex="-1" aria-labelledby="customSpecModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light py-3 px-4 rounded-top-4">
                <h5 class="modal-title fw-bold text-dark" id="customSpecModalLabel"><i class="bi bi-plus-circle-fill text-primary me-2"></i>New Custom Specification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border-0 rounded-3 small py-2 px-3 mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-warning fs-5"></i>
                    <span>This specification will be saved as <strong>Pending Admin Approval</strong> but you can immediately populate and save it on this product.</span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-muted">Specification Name *</label>
                    <input type="text" id="custom-spec-name" class="form-control rounded-3" placeholder="e.g. Fabric Weight, Material Grade">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-muted">Section Group *</label>
                    <select id="custom-spec-group" class="form-select rounded-3">
                        <option value="Basic Details">Basic Details</option>
                        <option value="Technical Specifications" selected>Technical Specifications</option>
                        <option value="Packaging Details">Packaging Details</option>
                        <option value="Compliance & Safety">Compliance & Safety</option>
                        <option value="Commercial Details">Commercial Details</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase text-muted">Field Type *</label>
                    <select id="custom-spec-type" class="form-select rounded-3">
                        <option value="text" selected>Text (Single Line)</option>
                        <option value="textarea">Long Text / Textarea</option>
                        <option value="rich_text">Rich Text (WYSIWYG Editor)</option>
                        <option value="number">Integer Number</option>
                        <option value="decimal">Decimal Number</option>
                        <option value="boolean">Yes / No Toggle</option>
                        <option value="date">Date</option>
                        <option value="url">URL Link</option>
                        <option value="select">Dropdown List (Select)</option>
                        <option value="multiselect">Multi-select List</option>
                        <option value="image">Image Upload</option>
                        <option value="file">File Upload</option>
                    </select>
                </div>

                <div class="mb-3 d-none" id="custom-options-group">
                    <label class="form-label fw-semibold small text-uppercase text-muted">Dropdown/List Options <small class="text-muted">(comma-separated)</small></label>
                    <input type="text" id="custom-spec-options" class="form-control rounded-3" placeholder="e.g. Red, Blue, Green">
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-light rounded-3 px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-3 px-4 py-2" id="submit-custom-spec">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="submit-spec-spinner" role="status"></span>
                    Add to Form
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
    document.querySelectorAll('.editor').forEach((el) => {
        ClassicEditor.create(el).catch(error => { console.error(error); });
    });

    function formatText(command, attrId) {
        document.execCommand(command, false, null);
        updateRichTextValue(attrId);
    }

    function updateRichTextValue(attrId) {
        const editorBody = document.getElementById('editor-body-' + attrId);
        const hiddenInput = document.getElementById('editor-hidden-' + attrId);
        if (editorBody && hiddenInput) {
            hiddenInput.value = editorBody.innerHTML;
        }
    }

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
        const subcategorySelect = $('#subcategory-select');

        // Existing values from blade variables
        const existingValues = @json($existingValues->mapWithKeys(function($val) {
            return [$val->attribute_id => $val->value];
        }));

        function getAttributeInputHtml(attr) {
            const requiredStar = attr.is_required ? '<span class="text-danger">*</span>' : '';
            const requiredAttr = attr.is_required ? 'required' : '';
            const unitLabel = attr.unit ? ` <small style="font-weight:400;">(${attr.unit})</small>` : '';

            const isFullWidth = ['textarea', 'image', 'file', 'rich_text'].includes(attr.type);
            const colClass = isFullWidth ? 'col-12' : 'col-md-6';

            let pendingBadge = '';
            if (attr.approval_status === 'pending') {
                pendingBadge = `<span class="badge ms-2" style="font-size:0.65rem; font-weight:600; padding:3px 8px; border-radius:50px; background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); color: #D97706 !important;"><i class="bi bi-clock-history me-1"></i>Pending Admin Approval</span>`;
            }

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
                    inputHtml = `<input type="${attr.type === 'url' ? 'url' : 'text'}" name="attributes[${attr.id}]" class="form-control rounded-3" placeholder="${attr.placeholder || ''}" value="${textVal}" ${requiredAttr}>`;
                    break;
                case 'number':
                    const numVal = rawVal !== undefined ? rawVal : (attr.default_value || '');
                    inputHtml = `<input type="number" name="attributes[${attr.id}]" class="form-control rounded-3" placeholder="${attr.placeholder || '0'}" value="${numVal}" ${requiredAttr}>`;
                    break;
                case 'decimal':
                    const decVal = rawVal !== undefined ? rawVal : (attr.default_value || '');
                    inputHtml = `<input type="number" name="attributes[${attr.id}]" class="form-control rounded-3" step="any" placeholder="${attr.placeholder || '0.00'}" value="${decVal}" ${requiredAttr}>`;
                    break;
                case 'date':
                    const dateVal = rawVal !== undefined ? rawVal : (attr.default_value || '');
                    inputHtml = `<input type="date" name="attributes[${attr.id}]" class="form-control rounded-3" value="${dateVal}" ${requiredAttr}>`;
                    break;
                case 'color':
                    const defaultColor = rawVal !== undefined ? rawVal : (attr.default_value || '#4F46E5');
                    inputHtml = `
                        <div class="d-flex gap-2">
                             <input type="color" name="attributes[${attr.id}]" value="${defaultColor}" style="width:50px;height:42px;border-radius:8px;border:1.5px solid #E2E8F0;cursor:pointer;padding:2px;" oninput="$('#color-text-${attr.id}').val(this.value)">
                             <input type="text" class="form-control rounded-3" readonly style="flex:1;" value="${defaultColor}" id="color-text-${attr.id}">
                        </div>
                    `;
                    break;
                case 'textarea':
                    const textareaVal = rawVal !== undefined ? rawVal : (attr.default_value || '');
                    inputHtml = `<textarea name="attributes[${attr.id}]" class="form-control rounded-3" rows="3" placeholder="${attr.placeholder || ''}" ${requiredAttr}>${textareaVal}</textarea>`;
                    break;
                case 'rich_text':
                    const richVal = rawVal !== undefined ? rawVal : (attr.default_value || '');
                    inputHtml = `
                        <div class="rich-text-editor-container" style="border:1.5px solid #E2E8F0; border-radius:10px; overflow:hidden;">
                            <div class="editor-toolbar" style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; padding:6px 12px; display:flex; gap:8px; align-items:center;">
                                <button type="button" class="btn btn-sm btn-light border  py-1 px-2" style="font-weight:bold; font-size:0.75rem;" onclick="formatText('bold', '${attr.id}')">B</button>
                                <button type="button" class="btn btn-sm btn-light border  py-1 px-2" style="font-style:italic; font-size:0.75rem;" onclick="formatText('italic', '${attr.id}')">I</button>
                                <button type="button" class="btn btn-sm btn-light border  py-1 px-2" style="text-decoration:underline; font-size:0.75rem;" onclick="formatText('underline', '${attr.id}')">U</button>
                                <button type="button" class="btn btn-sm btn-light border  py-1 px-2" onclick="formatText('insertUnorderedList', '${attr.id}')"><i class="bi bi-list-ul"></i></button>
                                <button type="button" class="btn btn-sm btn-light border  py-1 px-2" onclick="formatText('insertOrderedList', '${attr.id}')"><i class="bi bi-list-ol"></i></button>
                            </div>
                            <div id="editor-body-${attr.id}" contenteditable="true" class="p-3 bg-white" style="min-height:120px; outline:none; font-size:0.875rem;" oninput="updateRichTextValue('${attr.id}')">${richVal}</div>
                            <input type="hidden" name="attributes[${attr.id}]" id="editor-hidden-${attr.id}" value="${richVal}" ${requiredAttr}>
                        </div>
                    `;
                    break;
                case 'boolean':
                case 'yes_no':
                    const isChecked = rawVal == '1' || rawVal == 'yes' || (rawVal === undefined && (attr.default_value == '1' || attr.default_value == 'yes'));
                    inputHtml = `
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="attributes[${attr.id}]" value="1" class="form-check-input" id="switch-${attr.id}" style="width:2.5em; height:1.25em; cursor:pointer;" ${isChecked ? 'checked' : ''}>
                            <label class="form-check-label text-secondary small ms-2" for="switch-${attr.id}" style="text-transform:none;letter-spacing:0;cursor:pointer;">Enabled / Yes</label>
                        </div>
                    `;
                    break;
                case 'select':
                    let selectOptions = `<option value="">-- Select --</option>`;
                    if (attr.options) {
                        attr.options.forEach(opt => {
                            const isSelected = (rawVal !== undefined && String(opt.value) === String(rawVal)) || (rawVal === undefined && opt.is_default);
                            const selected = isSelected ? 'selected' : '';
                            selectOptions += `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
                        });
                    }
                    inputHtml = `<select name="attributes[${attr.id}]" class="form-select rounded-3" ${requiredAttr}>${selectOptions}</select>`;
                    break;
                case 'multiselect':
                    let multiOptions = '';
                    if (attr.options) {
                        attr.options.forEach(opt => {
                            const isSelected = Array.isArray(parsedVal) && parsedVal.map(String).includes(String(opt.value));
                            multiOptions += `<option value="${opt.value}" ${isSelected ? 'selected' : ''}>${opt.label}</option>`;
                        });
                    }
                    inputHtml = `<select name="attributes[${attr.id}][]" class="form-select rounded-3" multiple style="height:auto;min-height:80px;" ${requiredAttr}>${multiOptions}</select>`;
                    break;
                case 'radio':
                    let radioOptions = '<div class="d-flex flex-wrap gap-3 mt-1">';
                    if (attr.options) {
                        attr.options.forEach(opt => {
                            const isChecked = (rawVal !== undefined && String(opt.value) === String(rawVal)) || (rawVal === undefined && opt.is_default);
                            radioOptions += `
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:#475569;font-size:0.875rem;text-transform:none;letter-spacing:0;">
                                    <input type="radio" name="attributes[${attr.id}]" value="${opt.value}" ${isChecked ? 'checked' : ''} style="accent-color:#4F46E5;width:16px;height:16px;">
                                    ${opt.label}
                                 </label>
                            `;
                        });
                    }
                    radioOptions += '</div>';
                    inputHtml = radioOptions;
                    break;
                case 'checkbox':
                    let checkOptions = '<div class="d-flex flex-wrap gap-3 mt-1">';
                    if (attr.options) {
                        attr.options.forEach(opt => {
                            const isChecked = Array.isArray(parsedVal) && parsedVal.map(String).includes(String(opt.value));
                            checkOptions += `
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:#475569;font-size:0.875rem;text-transform:none;letter-spacing:0;">
                                    <input type="checkbox" name="attributes[${attr.id}][]" value="${opt.value}" ${isChecked ? 'checked' : ''} style="accent-color:#4F46E5;width:16px;height:16px;">
                                    ${opt.label}
                                </label>
                            `;
                        });
                    }
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
                        <input type="${attr.type}" name="attributes[${attr.id}]" class="form-control rounded-3" accept="${attr.type === 'image' ? 'image/*' : '*'}" ${requiredAttr && !rawVal ? 'required' : ''}>
                    `;
                    break;
            }

            return `
                <div class="${colClass}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-muted d-flex align-items-center flex-wrap">${attr.name}${unitLabel}${requiredStar}${pendingBadge}</label>
                        ${inputHtml}
                    </div>
                </div>
            `;
        }

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
                        const safeName = group.group_name.replace(/\s+/g, '-');
                        let groupHtml = `
                            <div class="mb-4" id="section-${safeName}">
                                <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94A3B8;border-bottom:1px solid #F1F5F9;padding-bottom:8px;margin-bottom:16px;">
                                    ${group.group_name}
                                </div>
                                <div class="row g-3" id="row-${safeName}">
                        `;

                        group.attributes.forEach(attr => {
                            groupHtml += getAttributeInputHtml(attr);
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
            const subcatId = $(this).val();
            loadAttributesForSubcategory(subcatId);

            const productTypeSelect = $('#product-type-select');
            const productTypeGroup = $('#product-type-group');

            productTypeSelect.html('<option value="">Loading...</option>');
            if (!subcatId) {
                productTypeGroup.addClass('d-none');
                productTypeSelect.html('<option value="">Select Product Type</option>');
                return;
            }

            const productTypesUrl = "{{ url('dashboard/get-product-types') }}";
            $.get(productTypesUrl, { subcategory_id: subcatId }, function(types) {
                if (types.length === 0) {
                    productTypeGroup.addClass('d-none');
                    productTypeSelect.val('');
                } else {
                    let opts = '<option value="">Select Product Type</option>';
                    types.forEach(function(t) {
                        opts += `<option value="${t.id}">${t.name}</option>`;
                      });
                    productTypeSelect.html(opts);
                    productTypeGroup.removeClass('d-none');
                }
            }).fail(function() {
                productTypeGroup.addClass('d-none');
                productTypeSelect.html('<option value="">Select Product Type</option>');
            });
        });

        if (subcategorySelect.val()) {
            loadAttributesForSubcategory(subcategorySelect.val());
        } else if (categorySelect.val()) {
            categorySelect.trigger('change');
        }

        // Modal specifications handling
        const customSpecModal = new bootstrap.Modal(document.getElementById('customSpecModal'));
        const addCustomSpecBtn = $('#add-custom-spec-btn');
        const submitCustomSpec = $('#submit-custom-spec');
        const specTypeSelect = $('#custom-spec-type');
        const optionsGroup = $('#custom-options-group');

        addCustomSpecBtn.on('click', function() {
            const subcatId = subcategorySelect.val();
            if (!subcatId) {
                alert('Please select a Category and Subcategory first to assign this custom specification.');
                return;
            }
            customSpecModal.show();
        });

        specTypeSelect.on('change', function() {
            const val = $(this).val();
            if (['select', 'multiselect', 'radio', 'checkbox'].includes(val)) {
                optionsGroup.removeClass('d-none');
            } else {
                optionsGroup.addClass('d-none');
            }
        });

        submitCustomSpec.on('click', function() {
            const subcatId = subcategorySelect.val();
            const specName = $('#custom-spec-name').val().trim();
            const specGroup = $('#custom-spec-group').val();
            const specType = $('#custom-spec-type').val();
            const specOptions = $('#custom-spec-options').val().trim();
            const spinner = $('#submit-spec-spinner');

            if (!specName) {
                alert('Please enter a specification name.');
                return;
            }

            spinner.removeClass('d-none');
            submitCustomSpec.prop('disabled', true);

            $.ajax({
                url: "{{ route('subscriber.attributes.storeCustom') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    name: specName,
                    type: specType,
                    group_section: specGroup,
                    subcategory_id: subcatId,
                    options: specOptions
                },
                success: function(res) {
                    spinner.addClass('d-none');
                    submitCustomSpec.prop('disabled', false);

                    if (res.success) {
                        customSpecModal.hide();

                        $('#custom-spec-name').val('');
                        $('#custom-spec-options').val('');
                        optionsGroup.addClass('d-none');
                        specTypeSelect.val('text');

                        const safeGroupName = res.group_name.replace(/\s+/g, '-');
                        let sectionDiv = $(`#section-${safeGroupName}`);

                        if (sectionDiv.length === 0) {
                            const sectionHtml = `
                                <div class="mb-4" id="section-${safeGroupName}">
                                    <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94A3B8;border-bottom:1px solid #F1F5F9;padding-bottom:8px;margin-bottom:16px;">
                                        ${res.group_name}
                                    </div>
                                    <div class="row g-3" id="row-${safeGroupName}">
                                    </div>
                                </div>
                            `;
                            container.append(sectionHtml);
                            sectionDiv = $(`#section-${safeGroupName}`);
                        }

                        const rowGrid = $(`#row-${safeGroupName}`);
                        const inputFieldHtml = getAttributeInputHtml(res.attribute);
                        rowGrid.append(inputFieldHtml);

                        attrCard.removeClass('d-none');
                        alert('Specification added successfully! You can now populate it below.');
                    }
                },
                error: function(xhr) {
                    spinner.addClass('d-none');
                    submitCustomSpec.prop('disabled', false);
                    const errors = xhr.responseJSON?.errors;
                    let errMsg = 'Failed to create specification.';
                    if (errors) {
                        errMsg = Object.values(errors).flat().join('\n');
                    }
                    alert(errMsg);
                }
            });
        });
    });
</script>
@endpush
