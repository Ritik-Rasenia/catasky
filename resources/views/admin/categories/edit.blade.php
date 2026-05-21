@extends('admin.layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
                    <li class="breadcrumb-item active">Edit Category</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark">Edit Category: <span class="text-primary">{{ $category->name }}</span></h3>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $category->name) }}" 
                                       class="form-control rounded-3 @error('name') is-invalid @enderror" 
                                       placeholder="Enter category name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Category Cover Image</label>
                                <div class="d-flex align-items-start gap-4 mb-3">
                                    <div class="p-1 border rounded-3 bg-white shadow-sm" style="width: 120px; height: 120px;">
                                        @if($category->image)
                                            <img src="{{ asset('uploads/categories/'.$category->image) }}" id="currentImg" class="w-100 h-100 object-fit-cover rounded-2">
                                        @else
                                            <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-muted">No Image</div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="p-3 border border-dashed rounded-4 text-center bg-light mb-2">
                                            <p class="small text-muted mb-2">Change category image (PNG, JPG, WebP)</p>
                                            <input type="file" name="image" id="categoryImage" 
                                                   class="form-control form-control-sm @error('image') is-invalid @enderror" 
                                                   accept="image/*">
                                        </div>
                                        <div id="imagePreview" class="mt-2 d-none">
                                            <span class="badge bg-info-soft text-info mb-1">New Selection Preview:</span>
                                            <img src="#" alt="Preview" class="d-block rounded-3 shadow-sm border p-1 bg-white" style="max-height: 80px;">
                                        </div>
                                    </div>
                                </div>
                                @error('image')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Status</label>
                                <select name="status" class="form-select rounded-3 @error('status') is-invalid @enderror" required>
                                    <option value="1" {{ old('status', $category->status) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', $category->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    <i class="fa-solid fa-circle-check me-2"></i>Update Category
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
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-circle-info me-2"></i>Editing Record</h6>
                    <p class="small mb-0">Changes here will reflect on the main navigation and product filtering system.</p>
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
