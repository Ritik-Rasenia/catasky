@extends('subscriber-panel.layouts.app')
 
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
                <a href="{{ route('subscriber.products.import') }}" class="btn btn-white">
                    <i class="fa-solid fa-file-import text-primary"></i>
                    <span>Import</span>
                </a>
                <a href="{{ route('subscriber.products.export') }}" class="btn btn-white">
                    <i class="fa-solid fa-file-excel text-success"></i>
                    <span>Export</span>
                </a>
                <a href="{{ route('subscriber.products.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>Add Product
                </a>
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
                                    <th class="border-0 text-uppercase small fw-bold text-muted">SKU</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Price Range</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted text-center">Status</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
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
                                                <div class="small text-muted">{{ Str::limit($product->short_description, 50) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">{{ $product->category->name ?? 'N/A' }}</div>
                                        <div class="smaller text-muted">{{ $product->subcategory->name ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="badge bg-light text-dark border fw-medium rounded-pill px-3">{{ $product->sku ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary">
                                            @if($product->offer_price)
                                                ₹{{ number_format($product->offer_price, 2) }}
                                                @if($product->mrp)
                                                    <span class="text-muted small text-decoration-line-through ms-1" style="font-size:0.75rem;">₹{{ number_format($product->mrp, 2) }}</span>
                                                @endif
                                            @elseif($product->mrp)
                                                ₹{{ number_format($product->mrp, 2) }}
                                            @else
                                                <span class="text-muted small fst-italic">Price on Request</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($product->status == 'active')
                                            <span class="badge bg-success-soft text-success rounded-pill px-3">Active</span>
                                        @elseif($product->status == 'inactive')
                                            <span class="badge bg-danger-soft text-danger rounded-pill px-3">Inactive</span>
                                        @else
                                            <span class="badge bg-warning-soft text-warning rounded-pill px-3">Draft</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-2 justify-content-end align-items-center">
                                            <a href="{{ route('subscriber.products.edit', $product->id) }}" class="btn-ap-action" title="Edit Product">
                                                <i class="fa-solid fa-pen-to-square text-primary" style="font-size: 13px !important;"></i>
                                            </a>
                                            <a href="{{ route('subscriber.share.create', ['product_id' => $product->id]) }}" class="btn-ap-action" title="Share Product">
                                                <i class="fa-solid fa-share-nodes text-success" style="font-size: 13px !important;"></i>
                                            </a>
                                            <form action="{{ route('subscriber.products.destroy', $product->id) }}" method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn-ap-action btn-delete" title="Delete Product">
                                                    <i class="fa-solid fa-trash-can text-danger" style="font-size: 13px !important;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No products found in the catalogue.</td>
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
