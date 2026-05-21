@extends('admin.layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold text-dark mb-1">Users Management</h3>
            <p class="text-muted">Manage system administrators, staff, and their respective access levels.</p>
        </div>
        <div class="col-md-4 text-md-end">
            @can('create-users')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="fa-solid fa-user-plus me-2"></i>Add New User
            </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="userTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">User</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Email</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Roles</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Joined Date</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                                                @if($user->id === auth()->id())
                                                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.65rem;">You</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($user->roles as $role)
                                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2">
                                                    {{ $role->name }}
                                                </span>
                                            @empty
                                                <span class="text-muted small">No Role</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="text-muted small">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                            @can('edit-users')
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-white btn-sm px-3" title="Edit">
                                                <i class="fa-solid fa-user-pen text-primary"></i>
                                            </a>
                                            @endcan
                                            
                                            @if($user->id !== auth()->id())
                                                @can('delete-users')
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline form-delete">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-white btn-sm px-3 btn-delete" title="Delete">
                                                        <i class="fa-solid fa-user-xmark text-danger"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No users found.</td>
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
        $('#userTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search users..."
            }
        });

        $(document).on('click', '.btn-delete', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete User?',
                text: "This user will no longer be able to access the admin panel!",
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
</style>
@endsection
