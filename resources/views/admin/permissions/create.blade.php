@extends('admin.layouts.app')

@section('title', 'Create Permission')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
                    <li class="breadcrumb-item active">Create Permission</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark">Create New System Permission</h3>
            <p class="text-muted">Define a new action that can be assigned to roles and users.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('admin.permissions.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="mb-4">
                                    <label for="name" class="form-label fw-bold text-dark">Permission Identifier <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-key text-primary"></i></span>
                                        <input type="text" class="form-control rounded-end-3 border-start-0 @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name') }}" 
                                               placeholder="e.g., view-products" required>
                                    </div>
                                    <small class="form-text text-muted d-block mt-2">
                                        Use a standardized format like <code>action-resource</code>. Only lowercase letters, hyphens, and underscores are allowed.
                                    </small>
                                    @error('name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label fw-bold text-dark">Description / Purpose</label>
                                    <textarea class="form-control rounded-3 @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" 
                                              placeholder="Briefly explain what access this permission grants..."
                                              maxlength="255">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="bg-primary bg-opacity-10 rounded-4 p-4 h-100 border border-primary border-opacity-10">
                                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-lightbulb me-2"></i> Best Practices</h6>
                                    <div class="mb-3">
                                        <div class="small fw-bold text-dark mb-1">Standard Naming:</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge bg-white text-dark border px-2">view-*</span>
                                            <span class="badge bg-white text-dark border px-2">create-*</span>
                                            <span class="badge bg-white text-dark border px-2">edit-*</span>
                                            <span class="badge bg-white text-dark border px-2">delete-*</span>
                                        </div>
                                    </div>
                                    <div class="small text-muted mb-0">
                                        Permissions are the granular building blocks of security. 
                                        Once created, you can assign this permission to various Roles (e.g., Admin, Editor, Staff).
                                    </div>
                                    <div class="mt-4 p-3 bg-white rounded-3 shadow-sm border border-primary border-opacity-25">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fa-solid fa-shield-halved text-success me-2"></i>
                                            <span class="fw-bold small text-dark">Security Note</span>
                                        </div>
                                        <p class="x-small text-muted mb-0">Avoid creating overly broad permissions. Granular control is more secure.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                        <i class="fa-solid fa-plus-circle me-2"></i>Create Permission
                                    </button>
                                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.75rem; }
</style>
@endsection
