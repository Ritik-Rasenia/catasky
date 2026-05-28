@extends('subscriber-panel.layouts.app')

@section('title', 'Edit Subcategory')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('subscriber.subcategories.index') }}">Subcategories</a></li>
                    <li class="breadcrumb-item active">Edit Subcategory</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark">Edit Subcategory: <span class="text-primary">{{ $subcategory->name }}</span></h3>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0  rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('subscriber.subcategories.update', $subcategory->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Parent Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select rounded-3 @error('category_id') is-invalid @enderror" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $subcategory->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Subcategory Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $subcategory->name) }}" 
                                       class="form-control rounded-3 @error('name') is-invalid @enderror" 
                                       placeholder="Enter subcategory name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark">Subcategory Image</label>
                                <div class="d-flex align-items-start gap-4 mb-3">
                                    <div class="p-1 border rounded-3 bg-white " style="width: 100px; height: 100px;">
                                        @if($subcategory->image)
                                            <img src="{{ asset('uploads/subcategories/'.$subcategory->image) }}" id="currentImage" class="w-100 h-100 object-fit-cover rounded-2">
                                        @else
                                            <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-muted">No Image</div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="p-3 border border-dashed rounded-4 text-center bg-light mb-2">
                                            <p class="small text-muted mb-2">Change image (PNG, JPG, WebP)</p>
                                            <input type="file" name="image" id="subcategoryImage" 
                                                   class="form-control form-control-sm @error('image') is-invalid @enderror" 
                                                   accept="image/*">
                                        </div>
                                        <div id="imagePreview" class="mt-2 d-none">
                                            <span class="badge bg-info-soft text-info mb-1">New Selection Preview:</span>
                                            <img src="#" alt="Preview" class="d-block rounded-3  border p-1 bg-white" style="max-height: 80px;">
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
                                    <option value="1" {{ old('status', $subcategory->status) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', $subcategory->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 ">
                                    <i class="fa-solid fa-circle-check me-2"></i>Update Subcategory
                                </button>
                                <a href="{{ route('subscriber.subcategories.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0  rounded-4 bg-primary bg-opacity-10 mb-4">
                <div class="card-body p-4 text-primary">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-circle-info me-2"></i>Editing Record</h6>
                    <p class="small mb-0">You are modifying an existing subcategory. This will update child groupings immediately across the portal.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $('#subcategoryImage').change(function(){
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
