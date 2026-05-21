@extends('admin.layouts.app')

@section('title', 'Create Product')

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
                    <li class="breadcrumb-item active">Create Product</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark">Add New Product</h3>
        </div>
    </div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
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
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control rounded-3 @error('name') is-invalid @enderror" placeholder="Enter product name" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Part Code <span class="text-danger">*</span></label>
                                <input type="text" name="part_code" value="{{ old('part_code') }}" class="form-control rounded-3 @error('part_code') is-invalid @enderror" placeholder="Unique identification code" required>
                                @error('part_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Part Number</label>
                                <input type="text" name="part_number" value="{{ old('part_number') }}" class="form-control rounded-3" placeholder="Manufacturer part number">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Short Description</label>
                                <textarea name="short_description" class="form-control rounded-3" rows="3" placeholder="Brief overview for snippets">{{ old('short_description') }}</textarea>
                            </div>
                        </div>
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
                            <textarea name="additional_info" class="form-control editor">{{ old('additional_info') }}</textarea>
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
                                <input type="text" name="meta_title" value="{{ old('meta_title') }}" class="form-control rounded-3" placeholder="Page title for search engines">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Meta Description</label>
                                <textarea name="meta_description" class="form-control rounded-3" rows="3">{{ old('meta_description') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Meta Keywords</label>
                                <textarea name="meta_keywords" class="form-control rounded-3" rows="2" placeholder="keyword1, keyword2, ...">{{ old('meta_keywords') }}</textarea>
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
                                    <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Category *</label>
                            <select name="category_id" id="category_id" class="form-select rounded-3" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Subcategory *</label>
                            <select name="subcategory_id" id="subcategory_id" class="form-select rounded-3" required>
                                <option value="">Select Subcategory</option>
                                @foreach($subcategories as $s)
                                    <option value="{{ $s->id }}" {{ old('subcategory_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Price (₹)</label>
                            <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="form-control rounded-3 @error('price') is-invalid @enderror" placeholder="e.g. 276.00">
                            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">Product Media</h6>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Main Thumbnail *</label>
                            <div class="p-3 border border-dashed rounded-4 text-center bg-light">
                                <input type="file" name="image" class="form-control form-control-sm" accept="image/*" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold small text-muted text-uppercase">Gallery Images</label>
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
                                <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Featured Product</label>
                            <select name="featured" class="form-select rounded-3">
                                <option value="0" {{ old('featured') == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('featured') == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px; z-index: 1;">
                    <div class="card-body p-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 mb-2 shadow">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i>Publish Product
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
            } else { subcategoryDropdown.html('<option value="">Select Subcategory</option>'); }
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
    });
</script>
@endpush
@endsection