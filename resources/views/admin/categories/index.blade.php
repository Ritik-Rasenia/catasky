@extends('admin.layouts.app')

@section('title', 'Categories Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold text-dark mb-1">Categories Management</h3>
            <p class="text-muted">Organize your products into logical categories for better discovery.</p>
        </div>
        <div class="col-md-4 text-md-end">
            @can('create-categories')
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary  rounded-pill px-4">
                <i class="fa-solid fa-plus me-2"></i>Add New Category
            </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0  rounded-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="categoryTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">#</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Image</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Category Name</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Subscriber</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Status</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                <tr>
                                    <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        @if($category->image)
                                            <img src="{{ asset('uploads/categories/'.$category->image) }}" width="45" height="45" class="rounded-3  object-fit-cover border">
                                        @else
                                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 45px; height: 45px;">
                                                <i class="fa-solid fa-folder small"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $category->name }}</span>
                                    </td>
                                    <td>
                                        @if($category->subscriber)
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">{{ $category->subscriber->subscriberProfile->company_name ?? $category->subscriber->name }}</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">System (Admin)</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($category->status == '1')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Active</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group  rounded-3 overflow-hidden">
                                            @can('edit-categories')
                                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-white btn-sm px-3" title="Edit Category">
                                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                                            </a>
                                            @endcan
                                            
                                            @can('delete-categories')
                                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-white btn-sm px-3 btn-delete" title="Delete Category">
                                                    <i class="fa-solid fa-trash-can text-danger"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
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
        $('#categoryTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "responsive": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search categories..."
            }
        });

        $(document).on('click', '.btn-delete', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Category?',
                text: "Deleting this will also affect subcategories and products assigned to it!",
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

@endsection
