@extends('admin.layouts.app')

@section('title', 'Brands Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold text-dark mb-1">Brands Management</h3>
            <p class="text-muted">Register and manage product brands for your catalogue.</p>
        </div>
        <div class="col-md-4 text-md-end">
            @can('create-brands')
            <a href="{{ route('admin.brands.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="fa-solid fa-plus me-2"></i>Add New Brand
            </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="brandTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">#</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Logo</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Brand Name</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Status</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($brands as $brand)
                                <tr>
                                    <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        @if($brand->image)
                                            <img src="{{ asset('uploads/brands/'.$brand->image) }}" width="45" height="45" class="rounded-3 shadow-sm object-fit-contain bg-white p-1 border">
                                        @else
                                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 45px; height: 45px;">
                                                <i class="fa-solid fa-image small"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $brand->name }}</span>
                                    </td>
                                    <td>
                                        @if($brand->status == '1')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Active</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                            @can('edit-brands')
                                            <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-white btn-sm px-3" title="Edit Brand">
                                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                                            </a>
                                            @endcan
                                            
                                            @can('delete-brands')
                                            <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-white btn-sm px-3 btn-delete" title="Delete Brand">
                                                    <i class="fa-solid fa-trash-can text-danger"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No brands found.</td>
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
        $('#brandTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "responsive": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search brands..."
            }
        });

        $(document).on('click', '.btn-delete', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Brand?',
                text: "This action cannot be undone!",
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
