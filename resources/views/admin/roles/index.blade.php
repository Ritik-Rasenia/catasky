@extends('admin.layouts.app')

@section('title', 'Roles Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold text-dark mb-1">Roles Management</h3>
            <p class="text-muted">Configure user roles and their associated permissions for system access control.</p>
        </div>
        <div class="col-md-4 text-md-end">
            @can('create-roles')
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="fa-solid fa-plus me-2"></i>Create New Role
            </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="roleTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">#</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Role Name</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Permissions Count</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Created At</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr>
                                    <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-3" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-shield-halved small"></i>
                                            </div>
                                            <span class="fw-bold text-dark">{{ $role->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">
                                            {{ $role->permissions->count() }} Permissions
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $role->created_at->format('M d, Y') }}</td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                            @can('edit-roles')
                                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-white btn-sm px-3" title="Edit Role">
                                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                                            </a>
                                            @endcan
                                            
                                            @if($role->name != 'Super Admin')
                                                @can('delete-roles')
                                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline form-delete">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-white btn-sm px-3 btn-delete" title="Delete Role">
                                                        <i class="fa-solid fa-trash-can text-danger"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No roles found in the system.</td>
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
        $('#roleTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "responsive": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search roles..."
            }
        });

        $(document).on('click', '.btn-delete', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Role?',
                text: "Users with this role may lose access to certain features!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
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
