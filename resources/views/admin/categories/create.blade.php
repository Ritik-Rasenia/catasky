@extends('admin.layouts.app')

@section('title', 'Create Category')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
                    <li class="breadcrumb-item active">Create Category</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark">Create New Category</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" 
                                       class="form-control rounded-3 @error('name') is-invalid @enderror" 
                                       placeholder="e.g. Electrical Solutions" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Category Cover Image</label>
                                <div class="p-4 border border-dashed rounded-4 text-center bg-light mb-2">
                                    <i class="fa-solid fa-image fa-2x text-muted mb-2"></i>
                                    <p class="small text-muted mb-3">Upload a representative image (PNG, JPG, WebP)</p>
                                    <input type="file" name="image" id="categoryImage" 
                                           class="form-control @error('image') is-invalid @enderror" 
                                           accept="image/*">
                                </div>
                                <div id="imagePreview" class="mt-2 d-none text-center">
                                    <img src="#" alt="Preview" class="rounded-3 shadow-sm border p-1 bg-white" style="max-height: 120px;">
                                </div>
                                @error('image')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Status</label>
                                <select name="status" class="form-select rounded-3 @error('status') is-invalid @enderror" required>
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Save Category
                                </button>
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary bg-opacity-10 mb-4">
                <div class="card-body p-4 text-primary">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-circle-info me-2"></i>Information</h6>
                    <p class="small mb-0">Categories are the top-level containers for your products. A well-organized category structure improves SEO and user navigation on your website.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $('#categoryImage').change(function(){
        const file = this.files[0];
        if (file){
            let reader = new FileReader();
            reader.onload = function(event){
                $('#imagePreview img').attr('src', event.target.result);
                $('#imagePreview').removeClass('d-none');
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush

<style>
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>
@endsection
