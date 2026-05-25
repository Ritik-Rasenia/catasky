@extends('admin.layouts.app')

@section('title', 'Edit Product')

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
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                    <li class="breadcrumb-item active">Edit Product</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark">Edit Product: <span class="text-primary">{{ $product->name }}</span></h3>
        </div>
    </div>

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 p-4">
                        <h5 class="fw-bold mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control rounded-3 @error('name') is-invalid @enderror" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Part Code <span class="text-danger">*</span></label>
                                <input type="text" name="part_code" value="{{ old('part_code', $product->part_code) }}" class="form-control rounded-3 @error('part_code') is-invalid @enderror" required>
                                @error('part_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Part Number</label>
                                <input type="text" name="part_number" value="{{ old('part_number', $product->part_number) }}" class="form-control rounded-3">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Short Description</label>
                                <textarea name="short_description" class="form-control rounded-3" rows="3">{{ old('short_description', $product->short_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                    <!-- Dynamic Attributes (loaded by subcategory) -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4" id="dynamic-attributes-card" style="display:none;">
                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="fw-bold mb-0">Product Attributes</h5>
                            <small class="text-muted">Attributes for the selected subcategory load here automatically.</small>
                        </div>
                        <div class="card-body p-4" id="dynamic-attributes-body">
                            <!-- JS will inject attribute fields here -->
                        </div>
                    </div>
                <!-- Product Content -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 p-4">
                        <h5 class="fw-bold mb-0">Detailed Content</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div>
                            <label class="form-label fw-semibold">Additional Information</label>
                            <textarea name="additional_info" class="form-control editor">{{ old('additional_info', $product->additional_info) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 p-4">
                        <h5 class="fw-bold mb-0 text-primary">SEO Optimization</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Meta Title</label>
                                <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" class="form-control rounded-3">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Meta Description</label>
                                <textarea name="meta_description" class="form-control rounded-3" rows="3">{{ old('meta_description', $product->meta_description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Meta Keywords</label>
                                <textarea name="meta_keywords" class="form-control rounded-3" rows="2">{{ old('meta_keywords', $product->meta_keywords) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Classification & Pricing -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">Classification & Pricing</h6>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Brand</label>
                            <select name="brand_id" class="form-select rounded-3">
                                <option value="">Select Brand</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}" {{ old('brand_id', $product->brand_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Category *</label>
                            <select name="category_id" id="category_id" class="form-select rounded-3" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id', $product->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Subcategory *</label>
                            <select name="subcategory_id" id="subcategory_id" class="form-select rounded-3" required>
                                <option value="">Select Subcategory</option>
                                @foreach($subcategories as $s)
                                    <option value="{{ $s->id }}" {{ old('subcategory_id', $product->subcategory_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Price (₹)</label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="form-control rounded-3 @error('price') is-invalid @enderror" placeholder="e.g. 276.00">
                            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">Product Media</h6>
                        <div class="mb-4 text-center">
                            <label class="form-label fw-semibold small text-muted text-uppercase d-block text-start">Main Thumbnail</label>
                            @if($product->thumbnail)
                                <img src="{{ asset('uploads/products/'.$product->thumbnail) }}" class="rounded-3 shadow-sm border p-1 bg-white mb-3" style="max-height: 120px;">
                            @endif
                            <div class="p-3 border border-dashed rounded-4 text-center bg-light">
                                <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold small text-muted text-uppercase">Gallery Images</label>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach($product->images as $img)
                                    <div class="position-relative gallery-item-{{ $img->id }}">
                                        <img src="{{ asset('uploads/products/gallery/'.$img->image) }}" class="rounded shadow-sm border" style="width: 60px; height: 60px; object-fit: cover;">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 rounded-circle delete-gallery-img" data-id="{{ $img->id }}" style="width:18px; height:18px; font-size:10px;">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                            <div class="p-3 border border-dashed rounded-4 text-center bg-light">
                                <input type="file" name="images[]" id="gallery-input" class="form-control form-control-sm" accept="image/*" multiple>
                            </div>
                            <div id="gallery-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">Visibility & Settings</h6>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Status</label>
                            <select name="status" class="form-select rounded-3" required>
                                <option value="1" {{ old('status', $product->status) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $product->status) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Featured Product</label>
                            <select name="featured" class="form-select rounded-3">
                                <option value="0" {{ old('featured', $product->featured) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('featured', $product->featured) == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px; z-index: 1;">
                    <div class="card-body p-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 mb-2 shadow">
                            <i class="fa-solid fa-circle-check me-2"></i>Update Product
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary w-100 rounded-pill py-2">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
    document.querySelectorAll('.editor').forEach((el) => {
        ClassicEditor.create(el).catch(error => { console.error(error); });
    });

    $(document).ready(function() {
        $('#category_id').on('change', function() {
            var categoryId = $(this).val();
            var subcategoryDropdown = $('#subcategory_id');
            subcategoryDropdown.html('<option value="">Loading...</option>');
            if(categoryId) {
                $.ajax({
                    url: '{{ url("admin/get-subcategories") }}/' + categoryId,
                    type: 'GET',
                    success: function(data) {
                        subcategoryDropdown.html('<option value="">Select Subcategory</option>');
                        $.each(data, function(key, value) {
                            subcategoryDropdown.append('<option value="'+ value.id +'">'+ value.name +'</option>');
                        });
                    }
                });
            }
        });


        $('#gallery-input').on('change', function() {
            var preview = $('#gallery-preview');
            preview.html('');
            if (this.files) {
                $.each(this.files, function(i, file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        preview.append('<img src="'+e.target.result+'" class="img-thumbnail" style="width:70px; height:70px; object-fit:cover;">');
                    }
                    reader.readAsDataURL(file);
                });
            }
        });

        // Dynamic attributes loader (admin edit)
        function renderAttributeField(attr) {
            const required = attr.is_required ? 'required' : '';
            const badgeFlags = [];
            if (attr.is_searchable) badgeFlags.push('<span class="badge bg-info-subtle text-info ms-1">Search</span>');
            if (attr.is_filterable) badgeFlags.push('<span class="badge bg-success-subtle text-success ms-1">Filter</span>');
            if (attr.is_comparable) badgeFlags.push('<span class="badge bg-warning-subtle text-warning ms-1">Compare</span>');
            if (attr.is_variant_enabled) badgeFlags.push('<span class="badge bg-primary-subtle text-primary ms-1">Variant</span>');

            let html = '<div class="mb-3">';
            html += '<label class="form-label fw-semibold">' + attr.name + (attr.unit ? ' <small class="text-muted">('+attr.unit+')</small>' : '') + '</label>';
            html += '<div class="text-muted small mb-2">' + (attr.group ? attr.group : 'General') + ' ' + badgeFlags.join(' ') + '</div>';

            switch(attr.type) {
                case 'text':
                case 'url':
                    html += `<input type="text" name="attributes[${attr.id}]" class="form-control" placeholder="${attr.placeholder || ''}" ${required}>`;
                    break;
                case 'number':
                    html += `<input type="number" step="any" name="attributes[${attr.id}]" class="form-control" placeholder="${attr.placeholder || ''}" ${required}>`;
                    break;
                case 'textarea':
                    html += `<textarea name="attributes[${attr.id}]" class="form-control" rows="3" placeholder="${attr.placeholder || ''}" ${required}></textarea>`;
                    break;
                case 'color':
                    html += `<input type="color" name="attributes[${attr.id}]" class="form-control form-control-color" ${required}>`;
                    break;
                case 'date':
                    html += `<input type="date" name="attributes[${attr.id}]" class="form-control" ${required}>`;
                    break;
                case 'boolean':
                    html += `<div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="attributes[${attr.id}]" value="1" id="attr_${attr.id}"><label class="form-check-label" for="attr_${attr.id}">Enabled</label></div>`;
                    break;
                case 'multiselect':
                    html += `<select name="attributes[${attr.id}][]" class="form-select" multiple ${required}>`;
                    attr.options.forEach(function(o){ html += `<option value="${o.value}">${o.label}</option>`; });
                    html += `</select>`;
                    break;
                case 'select':
                    html += `<select name="attributes[${attr.id}]" class="form-select" ${required}><option value="">-- Select --</option>`;
                    attr.options.forEach(function(o){ html += `<option value="${o.value}">${o.label}</option>`; });
                    html += `</select>`;
                    break;
                case 'checkbox':
                    attr.options.forEach(function(o){ html += `<div class="form-check"><input class="form-check-input" type="checkbox" name="attributes[${attr.id}][]" value="${o.value}" id="opt_${attr.id}_${o.value}"><label class="form-check-label" for="opt_${attr.id}_${o.value}">${o.label}</label></div>`; });
                    break;
                case 'radio':
                    attr.options.forEach(function(o){ html += `<div class="form-check"><input class="form-check-input" type="radio" name="attributes[${attr.id}]" value="${o.value}" id="opt_${attr.id}_${o.value}"><label class="form-check-label" for="opt_${attr.id}_${o.value}">${o.label}</label></div>`; });
                    break;
                default:
                    html += `<input type="text" name="attributes[${attr.id}]" class="form-control" ${required}>`;
            }

            html += '</div>';
            return html;
        }

        function loadAttributesForSubcategory(subcategoryId) {
            const container = $('#dynamic-attributes-body');
            const card = $('#dynamic-attributes-card');
            container.html('Loading attributes...');
            if (!subcategoryId) { container.html(''); card.hide(); return; }

            $.get(window.baseUrl + '/dashboard/attributes/subcategory/' + subcategoryId)
            .done(function(data){
                if (!data || data.length === 0) { container.html('<div class="text-muted">No attributes assigned to this subcategory.</div>'); card.show(); return; }
                container.html('');
                data.forEach(function(attr){ container.append(renderAttributeField(attr)); });
                if (window.jQuery && $.fn.select2) { container.find('select').select2({ theme: 'bootstrap-5', width: '100%' }); }
                card.show();
            }).fail(function(){ container.html('<div class="text-danger">Failed to load attributes.</div>'); card.show(); });
        }

        $('#subcategory_id').on('change', function(){ loadAttributesForSubcategory($(this).val()); });

        @if($product->subcategory_id)
            loadAttributesForSubcategory('{{ $product->subcategory_id }}');
        @endif

        $('.delete-gallery-img').on('click', function() {
            var id = $(this).data('id');
            var item = $('.gallery-item-' + id);
            Swal.fire({
                title: 'Delete Image?',
                text: "This gallery image will be removed!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("admin/product-images") }}/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function() { item.remove(); }
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection