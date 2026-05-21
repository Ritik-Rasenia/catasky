@extends('admin.layouts.app')

@section('title', 'Edit Permission')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
                    <li class="breadcrumb-item active">Edit Permission</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark">Edit Permission: <span class="text-primary">{{ $permission->name }}</span></h3>
            <p class="text-muted">Update the unique identifier and description for this action.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="mb-4">
                                    <label for="name" class="form-label fw-bold text-dark">Permission Identifier <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-primary"></i></span>
                                        <input type="text" class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $permission->name) }}" 
                                               placeholder="e.g., view-products" required>
                                    </div>
                                    @error('name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label for="description" class="form-label fw-bold text-dark">Description / Purpose</label>
                                    <textarea class="form-control rounded-3 @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" 
                                              placeholder="Update purpose of this permission..."
                                              maxlength="255">{{ old('description', $permission->description) }}</textarea>
                                    @error('description')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="h-100 d-flex flex-column gap-4">
                                    @if($permission->roles()->exists())
                                        <div class="card border-0 bg-info bg-opacity-10 rounded-4 shadow-none flex-grow-1">
                                            <div class="card-body p-4">
                                                <h6 class="fw-bold text-info mb-3"><i class="fa-solid fa-users-gear me-2"></i> Role Assignments</h6>
                                                <p class="small text-muted mb-3">This permission is actively granting access to the following roles:</p>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($permission->roles as $role)
                                                        <span class="badge bg-white text-info border border-info border-opacity-25 px-3 py-2 rounded-pill shadow-sm">
                                                            {{ $role->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="card border-0 bg-warning bg-opacity-10 rounded-4 shadow-none flex-grow-1">
                                            <div class="card-body p-4">
                                                <h6 class="fw-bold text-warning mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i> Orphaned Permission</h6>
                                                <p class="small text-muted mb-0">This permission is currently not assigned to any roles. It will not grant any access until assigned.</p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="card border-0 bg-light rounded-4 shadow-none">
                                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-bold text-dark small">Record ID</div>
                                                <div class="text-muted small">#{{ $permission->id }}</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-dark small">Last Updated</div>
                                                <div class="text-muted small">{{ $permission->updated_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                        <i class="fa-solid fa-circle-check me-2"></i>Update Permission
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
@endsection
