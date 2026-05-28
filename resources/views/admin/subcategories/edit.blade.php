@extends('admin.layouts.app')

@section('content')

<div class="container-fluid">

    <div class="card border-0  rounded-4">

        <div class="card-header bg-white py-3">
            <h4 class="mb-0">Edit Subcategory</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('admin.subcategories.update', $subcategory->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">Select Category</option>
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

                <div class="mb-4">
                    <label class="form-label">Subcategory Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $subcategory->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Subcategory Image</label>
                    @if($subcategory->image)
                        <div class="mb-2">
                            <img src="{{ asset('uploads/subcategories/'.$subcategory->image) }}" width="80" height="80" class="rounded-3 object-fit-cover">
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/png, image/jpeg, image/jpg, image/webp">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="1" {{ old('status', $subcategory->status) == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', $subcategory->status) == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="card border-0 bg-light rounded-4 mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-sliders me-2 text-primary"></i>Assign Reusable PIM Attributes</h6>
                        <p class="text-muted small mb-0">Select the global attributes that apply to products in this subcategory.</p>
                    </div>
                    <div class="card-body p-4">
                        @if($attributes->count() > 0)
                            <div class="row g-3">
                                @foreach($attributes->groupBy(function($a) { return $a->group?->name ?? 'General'; }) as $groupName => $groupAttrs)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="p-3 bg-white rounded-3 shadow-sm h-100">
                                            <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#64748B; border-bottom:1px solid #F1F5F9; padding-bottom:6px; margin-bottom:10px;">
                                                {{ $groupName }}
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                @foreach($groupAttrs as $attr)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="attributes[]" value="{{ $attr->id }}" id="attr_{{ $attr->id }}" {{ in_array($attr->id, $selectedAttributeIds) ? 'checked' : '' }}>
                                                        <label class="form-check-label text-dark fs-14 fw-semibold" for="attr_{{ $attr->id }}" style="cursor:pointer; font-size:0.875rem;">
                                                            {{ $attr->name }}
                                                            <span class="text-muted fw-normal" style="font-size:0.75rem;">({{ $attr->type }})</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3 text-muted">
                                <i class="bi bi-info-circle me-1"></i> No global attributes defined. Please create them in the <a href="{{ route('admin.attributes.index') }}">Attributes Panel</a> first.
                            </div>
                        @endif
                    </div>
                </div>

                <button class="btn btn-primary px-4">Update Subcategory</button>
                <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary px-4 ms-2">Cancel</a>

            </form>

        </div>

    </div>

</div>

@endsection
