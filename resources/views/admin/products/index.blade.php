@extends('admin.layouts.app')

@section('title', 'Products Management')
@section('page-title', 'Products Management')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Products</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark mb-1">Products Management</h3>
            <p class="text-muted">Catalogue of all products, inventory, and specifications.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="d-flex justify-content-md-end gap-2 flex-wrap">
                @can('import-products')
                <a href="{{ route('admin.products.import') }}" class="btn btn-white">
                    <i class="fa-solid fa-file-import"></i>
                    <span>Import</span>
                </a>
                @endcan
                @can('export-products')
                <a href="{{ route('admin.products.export') }}" class="btn btn-white">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Export</span>
                </a>
                @endcan
                @can('create-products')
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary  rounded-pill px-4" style="height:42px;display:inline-flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-plus"></i>Add Product
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0  rounded-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap" id="productTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">Product</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Category</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">MOQ</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Price Range</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted text-center">Status</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                <tr>
                                     <td class="ps-4">
                                         <div class="d-flex align-items-center">
                                             @if($product->thumbnail)
                                                 <img src="{{ $product->thumbnail_url }}" width="45" height="45" class="rounded-3  object-fit-cover border me-3" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($product->name) }}&background=f3f4f6&color=6366f1'">
                                             @else
                                                 <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border me-3" style="width: 45px; height: 45px;">
                                                     <i class="fa-solid fa-box small"></i>
                                                 </div>
                                             @endif
                                             <div>
                                                 <div class="fw-bold text-dark">{{ $product->name }}</div>
                                                 <div class="small text-muted">Code: {{ $product->part_code }}</div>
                                             </div>
                                         </div>
                                     </td>
                                     <td>
                                         <div class="small fw-semibold">{{ $product->category->name ?? 'N/A' }}</div>
                                         <div class="smaller text-muted">{{ $product->subcategory->name ?? '' }}</div>
                                     </td>
                                     <td>
                                         <div class="badge bg-light text-dark border fw-medium rounded-pill px-3">{{ $product->part_code ?? '50' }} pcs</div>
                                     </td>
                                     <td>
                                         <div class="fw-bold text-primary">
                                             @if($product->price)
                                                 ₹{{ number_format($product->price, 2) }}
                                             @else
                                                 <span class="text-muted small fst-italic">Price on Request</span>
                                             @endif
                                         </div>
                                     </td>
                                     <td class="text-center">
                                         @if($product->status == '1')
                                             <span class="badge bg-success-soft text-success rounded-pill px-3">Active</span>
                                         @else
                                             <span class="badge bg-danger-soft text-danger rounded-pill px-3">Inactive</span>
                                         @endif
                                     </td>
                                     <td class="text-end pe-4">
                                         <div class="d-inline-flex gap-2 justify-content-end align-items-center">
                                             @can('edit-products')
                                             <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-ap-action btn-ap-view" title="Edit Product">
                                                 <i class="fa-solid fa-pen-to-square text-primary" style="font-size: 13px !important;"></i>
                                             </a>
                                             @endcan
                                             
                                             @can('delete-products')
                                             <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline form-delete">
                                                 @csrf
                                                 @method('DELETE')
                                                 <button type="button" class="btn-ap-action btn-ap-reject btn-delete" title="Delete Product">
                                                     <i class="fa-solid fa-trash-can text-danger" style="font-size: 13px !important;"></i>
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
        $('#productTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "responsive": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Filter products..."
            }
        });

        $(document).on('click', '.btn-delete', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Product?',
                text: "This product and all its related data will be permanently removed!",
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
