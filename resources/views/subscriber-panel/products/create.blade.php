@extends('subscriber-panel.layouts.app')

@section('title', 'Add Product — Subscriber Panel')
@section('page-title', 'Add New Product')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('subscriber.products.index') }}">Products</a></li>
            <li class="breadcrumb-item active">Add New Product</li>
        </ol>
    </nav>
@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    body { background-color: #F8FAFC; }
    .form-section-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        transition: all 0.3s ease;
    }
    .form-section-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
    }
    .select2-container--bootstrap-5 .select2-selection {
        border: 1.5px solid #E2E8F0;
        border-radius: 10px;
        min-height: 44px;
        padding-top: 4px;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: #1E293B;
        font-weight: 500;
    }
    .form-control, .form-select {
        border: 1.5px solid #E2E8F0;
        border-radius: 10px;
        padding: 10px 14px;
        font-weight: 500;
        color: #1E293B;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #4F46E5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }
    .sticky-publish-card {
        position: sticky;
        top: 100px;
        z-index: 10;
        border: 1px solid rgba(79, 70, 229, 0.15);
        background: linear-gradient(145deg, #ffffff, #F8FAFC);
    }
    .upload-dropzone {
        border: 2px dashed #CBD5E1;
        background: #F8FAFC;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .upload-dropzone:hover {
        border-color: #4F46E5;
        background: rgba(79, 70, 229, 0.02);
    }
    .preview-thumbnail-container img {
        height: 90px;
        width: 90px;
        object-fit: cover;
        border-radius: 10px;
        border: 2px solid #E2E8F0;
    }
    .btn-premium {
        background: linear-gradient(135deg, #4F46E5, #6366F1);
        color: white;
        font-weight: 600;
        border: none;
        transition: all 0.2s ease;
    }
    .btn-premium:hover {
        background: linear-gradient(135deg, #4338CA, #4F46E5);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <form action="{{ route('subscriber.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form" class="needs-validation" novalidate>
        @csrf
        <div class="row">
            {{-- Left Content Area --}}
            <div class="col-lg-8">
                {{-- 1. Basic Information --}}
                <div class="card form-section-card border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-info-circle-fill me-2 text-primary"></i>Basic Information</h5>
                        <p class="text-muted small mb-0">Fill in core details regarding the product identifier and description.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-secondary">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       placeholder="e.g. Acme Premium Patch Panel" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-secondary">SKU / Part Code</label>
                                <input type="text" name="sku" class="form-control"
                                       placeholder="Unique SKU or part identifier" value="{{ old('sku') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary">Short Description</label>
                                <textarea name="short_description" class="form-control" rows="2"
                                          placeholder="A concise, high-impact overview (ideal for catalog views)">{{ old('short_description') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary">Full Description</label>
                                <textarea name="full_description" class="form-control editor">{{ old('full_description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Dynamic Product Attributes (PIM Template) --}}
                <div class="card form-section-card border-0 rounded-4 mb-4 d-none" id="dynamic-attributes-card">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><i class="bi bi-sliders me-2 text-primary"></i>Dynamic Product Attributes</h5>
                            <p class="text-muted small mb-0">Category-specific custom features loaded from template.</p>
                        </div>
                    </div>
                    <div class="card-body p-4" id="dynamic-attributes-container">
                        {{-- AJAX injected fields --}}
                    </div>
                </div>

                {{-- 3. SEO Optimization --}}
                <div class="card form-section-card border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-search me-2 text-primary"></i>SEO Strategy</h5>
                        <p class="text-muted small mb-0">Help customers locate this product quickly on public search engines.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary">Meta Title</label>
                                <input type="text" name="meta_title" class="form-control"
                                       placeholder="Page Title for Google search" value="{{ old('meta_title') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary">Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="3"
                                          placeholder="Brief description summarizing the product page">{{ old('meta_description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Sidebar Area --}}
            <div class="col-lg-4">
                {{-- 4. Classification & Pricing --}}
                <div class="card form-section-card border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-tag-fill me-2 text-primary"></i>Classification & Pricing</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Brand</label>
                            <select name="brand_id" class="form-select search-select">
                                <option value="">Select Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Category *</label>
                            <select name="category_id" id="category-select" class="form-select search-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Subcategory *</label>
                            <select name="subcategory_id" id="subcategory-select" class="form-select search-select"
                                    data-attributes-url="{{ route('subscriber.api.subcategory-attributes', '') }}" required>
                                <option value="">Select Subcategory</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Price (₹)</label>
                            <input type="number" step="0.01" name="price" class="form-control"
                                   placeholder="0.00" value="{{ old('price') }}">
                        </div>
                    </div>
                </div>

                {{-- 5. Product Media --}}
                <div class="card form-section-card border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-images me-2 text-primary"></i>Product Media</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Main Thumbnail *</label>
                            <div class="upload-dropzone" onclick="document.getElementById('thumbnail-input').click()">
                                <i class="bi bi-cloud-arrow-up text-primary fs-2"></i>
                                <p class="small text-muted mb-0 mt-2">Click to upload Main Image</p>
                                <input type="file" name="thumbnail" class="d-none" accept="image/*" id="thumbnail-input" onchange="previewThumbnail(event)" required>
                            </div>
                            <div id="thumbnail-preview" class="mt-2 text-center" style="display:none;">
                                <img id="thumb-img" src="" alt="Thumbnail" style="max-height:110px;border-radius:10px;object-fit:cover;" class="p-1 bg-white border">
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Gallery Images</label>
                            <div class="upload-dropzone" onclick="document.getElementById('gallery-input').click()">
                                <i class="bi bi-plus-circle-dotted text-secondary fs-3"></i>
                                <p class="small text-muted mb-0 mt-1">Upload multiple photos</p>
                                <input type="file" name="images[]" class="d-none" accept="image/*" multiple id="gallery-input" onchange="previewImages(event)">
                            </div>
                            <div id="gallery-preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </div>

                {{-- 6. Inventory --}}
                <div class="card form-section-card border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-boxes me-2 text-primary"></i>Inventory</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Stock Quantity</label>
                            <input type="number" name="stock" class="form-control" placeholder="0" value="{{ old('stock', 0) }}">
                        </div>
                        <div>
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Stock Status</label>
                            <select name="stock_status" class="form-select">
                                <option value="in_stock" {{ old('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                <option value="out_of_stock" {{ old('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 7. Settings --}}
                <div class="card form-section-card border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-toggle-on me-2 text-primary"></i>Visibility & Settings</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="featured" id="featuredCheck" value="1" {{ old('featured') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-secondary small ms-1" for="featuredCheck">Featured Product</label>
                        </div>
                    </div>
                </div>

                {{-- Sticky Publish Bar --}}
                <div class="card border-0 rounded-4 sticky-publish-card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <button type="submit" class="btn btn-premium w-100 rounded-pill py-2.5 mb-2">
                            <i class="bi bi-check-circle me-2"></i>Publish Product
                        </button>
                        <a href="{{ route('subscriber.products.index') }}" class="btn btn-light w-100 rounded-pill py-2.5 border text-secondary fw-semibold">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
    // CKEditor Initialization
    document.querySelectorAll('.editor').forEach((el) => {
        ClassicEditor.create(el).catch(error => { console.error(error); });
    });

    // Select2 Initialization
    $(document).ready(function() {
        $('.search-select').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });

    // Media Previews
    function previewThumbnail(e) {
        const file = e.target.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            document.getElementById('thumb-img').src = url;
            document.getElementById('thumbnail-preview').style.display = 'block';
        }
    }

    function previewImages(e) {
        const container = document.getElementById('gallery-preview');
        container.innerHTML = '';
        Array.from(e.target.files).forEach(file => {
            const url = URL.createObjectURL(file);
            const wrapper = document.createElement('div');
            wrapper.style.cssText = 'position:relative;height:70px;width:70px;';
            wrapper.innerHTML = `
                <img src="${url}" style="height:70px;width:70px;border-radius:8px;object-fit:cover;border:1px solid #E2E8F0;" class="p-0.5">
            `;
            container.appendChild(wrapper);
        });
    }

    // Dynamic Attribute loading with Skeleton loader
    $(document).ready(function() {
        const categorySelect = $('#category-select');
        const subcategorySelect = $('#subcategory-select');
        const attrCard = $('#dynamic-attributes-card');
        const container = $('#dynamic-attributes-container');

        categorySelect.on('change', function() {
            const catId = $(this).val();
            subcategorySelect.html('<option>Loading subcategories...</option>').trigger('change.select2');
            
            if (!catId) {
                subcategorySelect.html('<option value="">Select Subcategory</option>').trigger('change.select2');
                container.empty();
                attrCard.addClass('d-none');
                return;
            }

            $.get('{{ route("subscriber.get-subcategories") }}', { category_id: catId }, function(data) {
                let opts = '<option value="">Select Subcategory</option>';
                data.forEach(function(s) {
                    opts += `<option value="${s.id}">${s.name}</option>`;
                });
                subcategorySelect.html(opts).trigger('change.select2');
            }).fail(function() {
                subcategorySelect.html('<option value="">Select Subcategory</option>').trigger('change.select2');
            });
        });

        function getAttributeInputHtml(attr) {
            const requiredStar = attr.is_required ? '<span class="text-danger">*</span>' : '';
            const requiredAttr = attr.is_required ? 'required' : '';
            const unitLabel = attr.unit ? ` <small style="font-weight:400;">(${attr.unit})</small>` : '';

            const isFullWidth = ['textarea', 'image', 'file', 'rich_text'].includes(attr.type);
            const colClass = isFullWidth ? 'col-12' : 'col-md-6';

            let inputHtml = '';
            switch(attr.type) {
                case 'text':
                case 'url':
                    inputHtml = `<input type="${attr.type === 'url' ? 'url' : 'text'}" name="attributes[${attr.id}]" class="form-control" placeholder="${attr.placeholder || ''}" value="${attr.default_value || ''}" ${requiredAttr}>`;
                    break;
                case 'number':
                    inputHtml = `<input type="number" name="attributes[${attr.id}]" class="form-control" placeholder="${attr.placeholder || '0'}" value="${attr.default_value || ''}" ${requiredAttr}>`;
                    break;
                case 'decimal':
                    inputHtml = `<input type="number" name="attributes[${attr.id}]" class="form-control" step="any" placeholder="${attr.placeholder || '0.00'}" value="${attr.default_value || ''}" ${requiredAttr}>`;
                    break;
                case 'date':
                    inputHtml = `<input type="date" name="attributes[${attr.id}]" class="form-control" value="${attr.default_value || ''}" ${requiredAttr}>`;
                    break;
                case 'color':
                    const defaultColor = attr.default_value || '#4F46E5';
                    inputHtml = `
                        <div class="d-flex gap-2">
                             <input type="color" name="attributes[${attr.id}]" value="${defaultColor}" style="width:50px;height:42px;border-radius:8px;border:1.5px solid #E2E8F0;cursor:pointer;padding:2px;" oninput="$('#color-text-${attr.id}').val(this.value)">
                             <input type="text" class="form-control" readonly style="flex:1;" value="${defaultColor}" id="color-text-${attr.id}">
                        </div>
                    `;
                    break;
                case 'textarea':
                    inputHtml = `<textarea name="attributes[${attr.id}]" class="form-control" rows="3" placeholder="${attr.placeholder || ''}" ${requiredAttr}>${attr.default_value || ''}</textarea>`;
                    break;
                case 'boolean':
                    inputHtml = `
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="attributes[${attr.id}]" value="1" class="form-check-input" id="switch-${attr.id}" style="width:2.5em; height:1.25em; cursor:pointer;" ${attr.default_value == '1' || attr.default_value == 'yes' ? 'checked' : ''}>
                            <label class="form-check-label text-secondary small ms-2" for="switch-${attr.id}" style="text-transform:none;letter-spacing:0;cursor:pointer;">Yes</label>
                        </div>
                    `;
                    break;
                case 'select':
                    let selectOptions = `<option value="">-- Select --</option>`;
                    if (attr.options) {
                        attr.options.forEach(opt => {
                            const selected = opt.is_default ? 'selected' : '';
                            selectOptions += `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
                        });
                    }
                    inputHtml = `<select name="attributes[${attr.id}]" class="form-select" ${requiredAttr}>${selectOptions}</select>`;
                    break;
                case 'multiselect':
                    let multiOptions = '';
                    if (attr.options) {
                        attr.options.forEach(opt => {
                            multiOptions += `<option value="${opt.value}">${opt.label}</option>`;
                        });
                    }
                    inputHtml = `<select name="attributes[${attr.id}][]" class="form-select search-select" multiple style="height:auto;min-height:80px;" ${requiredAttr}>${multiOptions}</select>`;
                    break;
                case 'checkbox':
                    let checkOptions = '<div class="d-flex flex-wrap gap-3 mt-1">';
                    if (attr.options) {
                        attr.options.forEach(opt => {
                            const checked = opt.is_default ? 'checked' : '';
                            checkOptions += `
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:#475569;font-size:0.875rem;text-transform:none;letter-spacing:0;">
                                    <input type="checkbox" name="attributes[${attr.id}][]" value="${opt.value}" ${checked} style="accent-color:#4F46E5;width:16px;height:16px;">
                                    ${opt.label}
                                </label>
                            `;
                        });
                    }
                    checkOptions += '</div>';
                    inputHtml = checkOptions;
                    break;
                default:
                    inputHtml = `<input type="text" name="attributes[${attr.id}]" class="form-control" placeholder="${attr.placeholder || ''}" value="${attr.default_value || ''}" ${requiredAttr}>`;
            }

            return `
                <div class="${colClass}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-muted d-flex align-items-center flex-wrap">${attr.name}${unitLabel}${requiredStar}</label>
                        ${inputHtml}
                    </div>
                </div>
            `;
        }

        subcategorySelect.on('change', function() {
            const subcatId = $(this).val();
            container.empty();
            if (!subcatId) {
                attrCard.addClass('d-none');
                return;
            }

            // Skeleton Loader state
            const skeletonHtml = `
                <div class="row g-3 placeholder-glow">
                    <div class="col-md-6 mb-3">
                        <span class="placeholder col-4 mb-2 bg-secondary opacity-25 rounded" style="height: 15px;"></span>
                        <span class="placeholder col-12 rounded-3 py-3 bg-secondary opacity-10" style="height: 44px;"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <span class="placeholder col-3 mb-2 bg-secondary opacity-25 rounded" style="height: 15px;"></span>
                        <span class="placeholder col-12 rounded-3 py-3 bg-secondary opacity-10" style="height: 44px;"></span>
                    </div>
                </div>
            `;
            container.html(skeletonHtml);
            attrCard.removeClass('d-none');

            const attrUrlBase = subcategorySelect.data('attributes-url');

            $.get(attrUrlBase + '/' + subcatId, function(groups) {
                container.empty();
                if (groups.length === 0) {
                    container.html('<div class="text-secondary small py-2"><i class="bi bi-info-circle me-1"></i> No attributes mapped for this category.</div>');
                    return;
                }

                groups.forEach(group => {
                    const safeName = group.group_name.replace(/\s+/g, '-');
                    let groupHtml = `
                        <div class="mb-4" id="section-${safeName}">
                            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94A3B8;border-bottom:1.5px solid #F1F5F9;padding-bottom:6px;margin-bottom:16px;">
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

                // Reinits select2 inside dynamic inputs if present
                container.find('.search-select').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }).fail(function() {
                container.html('<div class="text-danger small py-2"><i class="bi bi-x-circle me-1"></i> Failed to load attributes.</div>');
            });
        });
    });
</script>
@endpush
