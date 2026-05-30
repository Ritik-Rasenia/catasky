@extends('admin.layouts.app')

@section('title', 'Create Brand')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.brands.index') }}">Brands</a></li>
                    <li class="breadcrumb-item active">Create Brand</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark">Create New Brand</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0  rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Brand Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" 
                                       class="form-control rounded-3 @error('name') is-invalid @enderror" 
                                       placeholder="Enter brand name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Brand Logo</label>
                                <div class="p-4 border border-dashed rounded-4 text-center bg-light mb-2">
                                    <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2"></i>
                                    <p class="small text-muted mb-3">Upload brand logo (PNG, JPG, WebP)</p>
                                    <input type="file" name="image" id="brandImage" 
                                           class="form-control @error('image') is-invalid @enderror" 
                                           accept="image/*">
                                </div>
                                <div id="imagePreview" class="mt-2 d-none text-center">
                                    <img src="#" alt="Preview" class="rounded-3  border p-1 bg-white" style="max-height: 100px;">
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
                                <button type="submit" class="btn btn-primary rounded-pill px-4 ">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Save Brand
                                </button>
                                <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        
    </div>
</div>

@push('js')
<script>
    $('#brandImage').change(function(){
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

@endsection