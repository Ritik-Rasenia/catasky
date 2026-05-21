@extends('admin.layouts.app')

@section('title', 'Permissions')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold text-dark mb-1">System Permissions</h3>
            <p class="text-muted">Granular access control points for various system modules and actions.</p>
        </div>
        <div class="col-md-4 text-md-end">
            @can('create-permissions')
            <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="fa-solid fa-plus me-2"></i>New Permission
            </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="permissionTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">Permission Name</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Module</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Description</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Assigned Roles</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $permission)
                                @php
                                    $parts = explode('-', $permission->name);
                                    $module = count($parts) > 1 ? $parts[1] : 'other';
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <code class="text-primary fw-medium px-2 py-1 bg-primary bg-opacity-10 rounded">{{ $permission->name }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark text-capitalize rounded-pill px-3 border">
                                            {{ $module }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $permission->description ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($permission->roles as $role)
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2" style="font-size: 0.7rem;">
                                                    {{ $role->name }}
                                                </span>
                                            @empty
                                                <span class="text-muted smaller">None</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                            @can('edit-permissions')
                                            <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-white btn-sm px-3" title="Edit">
                                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                                            </a>
                                            @endcan
                                            
                                            @can('delete-permissions')
                                            <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-white btn-sm px-3 btn-delete" title="Delete">
                                                    <i class="fa-solid fa-trash-can text-danger"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No permissions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        $('#permissionTable').DataTable({
            "pageLength": 25,
            "ordering": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Filter permissions..."
            }
        });

        $(document).on('click', '.btn-delete', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Permission?',
                text: "Removing this might break access for several roles!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete',
                borderRadius: '15px'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        });
    });
</script>
@endpush

<style>
    .btn-white {
        background: #fff;
        border: 1px solid #e2e8f0;
    }
    .btn-white:hover {
        background: #f8fafc;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(99, 102, 241, 0.02);
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 5px 15px;
        margin-left: 10px;
        outline: none;
    }
</style>
@endsection
