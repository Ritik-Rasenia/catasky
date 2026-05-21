@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-1">Edit Solution</h3>
            <p class="text-muted mb-0">Update solution details, banner imagery, and visibility settings.</p>
        </div>
        <a href="{{ route('admin.solutions.index') }}" class="btn btn-light border rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.solutions.update', $solution->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Solution Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $solution->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Data Center Solutions" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Short Description</label>
                            <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" rows="3" placeholder="Brief summary of the solution...">{{ old('short_description', $solution->short_description) }}</textarea>
                            @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Full Description</label>
                            <div class="editor-wrap">
                                <textarea name="description" class="form-control editor @error('description') is-invalid @enderror">{{ old('description', $solution->description) }}</textarea>
                            </div>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card bg-light border-0 rounded-4 p-4 mb-4">
                            <h6 class="fw-bold mb-3">Media & Status</h6>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Banner Image</label>
                                @if($solution->image)
                                    <div class="mb-2 position-relative group">
                                        <img src="{{ asset('uploads/solutions/'.$solution->image) }}" alt="Banner" class="w-100 rounded shadow-sm border" style="max-height: 120px; object-fit: cover;">
                                        <div class="badge bg-dark position-absolute bottom-0 end-0 m-2">Current</div>
                                    </div>
                                @endif
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                <small class="text-muted">Upload new to replace</small>
                                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small">Icon (SVG/PNG)</label>
                                @if($solution->icon)
                                    <div class="mb-2 d-flex align-items-center gap-2">
                                        <div class="p-2 bg-white rounded border">
                                            <img src="{{ asset('uploads/solutions/'.$solution->icon) }}" alt="Icon" width="40" height="40" class="object-fit-contain">
                                        </div>
                                        <span class="small text-muted">Current Icon</span>
                                    </div>
                                @endif
                                <input type="file" name="icon" class="form-control @error('icon') is-invalid @enderror" accept="image/*">
                                @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-bold small">Visibility Status</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="1" {{ old('status', $solution->status) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', $solution->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-3 rounded-3">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Update Solution
                            </button>
                            <a href="{{ route('admin.solutions.index') }}" class="btn btn-light py-3 rounded-3">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.querySelectorAll('.editor').forEach(function(el) {
        ClassicEditor.create(el).catch(function(error) { console.error(error); });
    });
</script>
@endpush
@endsection
