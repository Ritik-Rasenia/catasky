@extends('admin.layouts.app')

@section('title', 'Edit Brand')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.brands.index') }}">Brands</a></li>
                    <li class="breadcrumb-item active">Edit Brand</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark">Edit Brand: <span class="text-primary">{{ $brand->name }}</span></h3>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Brand Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $brand->name) }}" 
                                       class="form-control rounded-3 @error('name') is-invalid @enderror" 
                                       placeholder="Enter brand name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Brand Logo</label>
                                <div class="d-flex align-items-start gap-4 mb-3">
                                    <div class="p-1 border rounded-3 bg-white shadow-sm" style="width: 100px; height: 100px;">
                                        @if($brand->image)
                                            <img src="{{ asset('uploads/brands/'.$brand->image) }}" id="currentLogo" class="w-100 h-100 object-fit-contain rounded-2">
                                        @else
                                            <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-muted">No Logo</div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="p-3 border border-dashed rounded-4 text-center bg-light mb-2">
                                            <p class="small text-muted mb-2">Change logo (PNG, JPG, WebP)</p>
                                            <input type="file" name="image" id="brandImage" 
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
                                    <option value="1" {{ old('status', $brand->status) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', $brand->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    <i class="fa-solid fa-circle-check me-2"></i>Update Brand
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
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary bg-opacity-10 mb-4">
                <div class="card-body p-4 text-primary">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-circle-info me-2"></i>Editing Record</h6>
                    <p class="small mb-0">You are currently modifying an existing brand. Updates will be reflected across all associated products immediately upon saving.</p>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Quick Stats</h6>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small text-muted">Created:</span>
                        <span class="small fw-bold text-dark">{{ $brand->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="small text-muted">Last Updated:</span>
                        <span class="small fw-bold text-dark">{{ $brand->updated_at->diffForHumans() }}</span>
                    </div>
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

<style>
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>
@endsection