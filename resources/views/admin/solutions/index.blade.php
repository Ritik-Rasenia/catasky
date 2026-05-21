@extends('admin.layouts.app')

@section('title', 'Solutions Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold text-dark mb-1">Solutions Management</h3>
            <p class="text-muted">Manage industry-specific solutions and product mappings.</p>
        </div>
        <div class="col-md-4 text-md-end">
            @can('create-solutions')
            <a href="{{ route('admin.solutions.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="fa-solid fa-plus me-2"></i>Add New Solution
            </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="solutionTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">#</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Solution</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Products</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Status</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($solutions as $solution)
                                <tr>
                                    <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-primary border me-3 shadow-sm" style="width: 50px; height: 50px; flex-shrink: 0;">
                                                @if($solution->icon)
                                                    <img src="{{ asset('uploads/solutions/'.$solution->icon) }}" alt="{{ $solution->name }}" class="w-100 h-100 object-fit-contain p-2">
                                                @else
                                                    <i class="fa-solid fa-lightbulb"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $solution->name }}</div>
                                                <div class="small text-muted">{{ Str::limit($solution->short_description, 50) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">
                                            <i class="fa-solid fa-box-open me-1 small"></i> {{ $solution->products()->count() }} Products
                                        </span>
                                    </td>
                                    <td>
                                        @if($solution->status == '1')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Active</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                            @can('edit-solutions')
                                            <a href="{{ route('admin.solutions.edit', $solution->id) }}" class="btn btn-white btn-sm px-3" title="Edit Solution">
                                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                                            </a>
                                            @endcan
                                            
                                            @can('delete-solutions')
                                            <form action="{{ route('admin.solutions.destroy', $solution->id) }}" method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-white btn-sm px-3 btn-delete" title="Delete Solution">
                                                    <i class="fa-solid fa-trash-can text-danger"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No solutions found.</td>
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
        $('#solutionTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "responsive": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search solutions..."
            }
        });

        $(document).on('click', '.btn-delete', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Solution?',
                text: "Removing this solution might affect how products are displayed in business contexts!",
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
