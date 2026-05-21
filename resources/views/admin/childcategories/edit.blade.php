@extends('admin.layouts.app')

@section('content')

<div class="container-fluid">

    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-header bg-white py-3">
            <h4 class="mb-0">Edit Child Category</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('admin.childcategories.update', $childcategory->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (old('category_id', $childcategory->category_id) == $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Subcategory</label>
                    <select name="subcategory_id" id="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror" required>
                        <option value="">Select Subcategory</option>
                        @foreach($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}" {{ (old('subcategory_id', $childcategory->subcategory_id) == $subcategory->id) ? 'selected' : '' }}>{{ $subcategory->name }}</option>
                        @endforeach
                    </select>
                    @error('subcategory_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Child Category Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $childcategory->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Current Image</label><br>
                    @if($childcategory->image)
                        <img src="{{ asset('uploads/childcategories/'.$childcategory->image) }}" width="100" class="mb-2 rounded shadow-sm">
                    @else
                        <span class="text-muted">No Image</span><br>
                    @endif
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/png, image/jpeg, image/jpg, image/webp">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" {{ old('status', $childcategory->status) == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', $childcategory->status) == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary px-4">Update Child Category</button>
                <a href="{{ route('admin.childcategories.index') }}" class="btn btn-secondary px-4 ms-2">Cancel</a>

            </form>

        </div>

    </div>

</div>

@push('js')
<script>
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
            } else {
                subcategoryDropdown.html('<option value="">Select Subcategory</option>');
            }
        });
    });
</script>
@endpush
@endsection
