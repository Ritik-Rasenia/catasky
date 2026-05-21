@extends('admin.layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active">Edit Role</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="fw-bold text-dark mb-1">Edit Role: <span class="text-primary">{{ $role->name }}</span></h3>
                    <p class="text-muted mb-0">Modify the capabilities and access levels for this role.</p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Left Column: Role Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg rounded-3 @error('name') is-invalid @enderror" 
                                   required value="{{ old('name', $role->name) }}" {{ $role->name == 'Super Admin' ? 'readonly' : '' }}>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($role->name == 'Super Admin')
                                <div class="form-text mt-2 text-warning small"><i class="fa-solid fa-lock me-1"></i> System roles cannot be renamed.</div>
                            @endif
                        </div>

                        <div class="p-4 bg-light rounded-4 mb-4 border border-white">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted">ID:</span>
                                <span class="small fw-bold text-dark">#{{ $role->id }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted">Created:</span>
                                <span class="small fw-bold text-dark">{{ $role->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm py-3">
                                <i class="fa-solid fa-circle-check me-2"></i>Update Role
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary rounded-pill py-2">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Permissions -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0">Modify Permissions</h5>
                            <small class="text-muted">Currently active: <span class="text-primary fw-bold">{{ count($rolePermissions) }}</span> capabilities</small>
                        </div>
                        <div class="bg-light p-1 rounded-pill">
                            <div class="form-check form-switch mb-0 px-5 py-2">
                                <input class="form-check-input cursor-pointer" type="checkbox" id="selectAllPermissions">
                                <label class="form-check-label fw-bold text-primary ms-2 cursor-pointer" for="selectAllPermissions">Select All</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @php
                            $permissionGroups = $permissions->groupBy(function($permission) {
                                $parts = explode('-', $permission->name);
                                return count($parts) > 1 ? $parts[1] : 'other';
                            })->sort();
                        @endphp

                        @foreach($permissionGroups as $group => $groupPermissions)
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3">
                                <div class="p-2 rounded-3 bg-primary-soft me-3">
                                    <i class="fa-solid fa-folder-tree text-primary"></i>
                                </div>
                                <h6 class="fw-bold text-dark text-uppercase mb-0 letter-spacing-1">{{ $group }} Management</h6>
                                <div class="flex-grow-1 ms-3 border-bottom opacity-10"></div>
                            </div>
                            
                            <div class="row g-3">
                                @foreach($groupPermissions as $permission)
                                <div class="col-md-6">
                                    <div class="permission-card h-100 p-3 rounded-4 border border-light bg-white transition-all cursor-pointer @if(in_array($permission->name, $rolePermissions)) selected @endif" 
                                         onclick="togglePermission('perm_{{ $permission->name }}')">
                                        <div class="d-flex align-items-center">
                                            <div class="form-check custom-check mb-0">
                                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                                       name="permissions[]" value="{{ $permission->name }}" 
                                                       id="perm_{{ $permission->name }}"
                                                       {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}
                                                       onclick="event.stopPropagation(); updateCardStyle(this)">
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-bold text-dark permission-label">{{ Str::title(str_replace('-', ' ', $permission->name)) }}</div>
                                                @if($permission->description)
                                                    <div class="small text-muted opacity-75 mt-1">{{ $permission->description }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('js')
<script>
    function togglePermission(id) {
        const checkbox = document.getElementById(id);
        checkbox.checked = !checkbox.checked;
        updateCardStyle(checkbox);
        updateSelectAllState();
    }

    function updateCardStyle(checkbox) {
        const card = checkbox.closest('.permission-card');
        if (checkbox.checked) {
            card.classList.add('selected');
        } else {
            card.classList.remove('selected');
        }
    }

    function updateSelectAllState() {
        const all = document.querySelectorAll('.permission-checkbox');
        const checked = document.querySelectorAll('.permission-checkbox:checked');
        document.getElementById('selectAllPermissions').checked = all.length === checked.length;
    }

    $(document).ready(function() {
        $('#selectAllPermissions').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.permission-checkbox').prop('checked', isChecked).each(function() {
                updateCardStyle(this);
            });
        });

        $('.permission-checkbox').on('change', function() {
            updateSelectAllState();
        });

        updateSelectAllState();
    });
</script>
@endpush

<style>
    .letter-spacing-1 { letter-spacing: 1px; }
    .permission-card {
        border-width: 2px !important;
        position: relative;
    }
    .permission-card:hover {
        border-color: #4f46e5 !important;
        background-color: #f8fafc;
        transform: translateY(-2px);
    }
    .permission-card.selected {
        border-color: #4f46e5 !important;
        background-color: #eef2ff !important;
    }
    .permission-card.selected .permission-label {
        color: #4f46e5 !important;
    }
    .custom-check .form-check-input {
        width: 1.4em;
        height: 1.4em;
        margin-top: 0;
        cursor: pointer;
    }
    .cursor-pointer { cursor: pointer; }
</style>
@endsection
