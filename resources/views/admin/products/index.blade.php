@extends('admin.layouts.app')

@section('title', 'Products Management')

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
                <a href="{{ route('admin.products.import') }}" class="btn-premium btn-premium-import">
                    <i class="fa-solid fa-file-import"></i>
                    <span>Import</span>
                </a>
                @endcan
                @can('export-products')
                <a href="{{ route('admin.products.export') }}" class="btn-premium btn-premium-export">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Export</span>
                </a>
                @endcan
                @can('create-products')
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4" style="height:42px;display:inline-flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-plus"></i>Add Product
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="productTable">
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
                                @forelse($products as $product)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            @if($product->thumbnail)
                                                <img src="{{ $product->thumbnail_url }}" width="45" height="45" class="rounded-3 shadow-sm object-fit-cover border me-3" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($product->name) }}&background=f3f4f6&color=6366f1'">
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
                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                            @can('edit-products')
                                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-white btn-sm px-3" title="Edit Product">
                                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                                            </a>
                                            @endcan
                                            
                                            @can('delete-products')
                                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-white btn-sm px-3 btn-delete" title="Delete Product">
                                                    <i class="fa-solid fa-trash-can text-danger"></i>
                                                </button>
                                            </form>
                                            @endcan
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

<style>
    .btn-premium {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        height: 42px;
        padding: 0 18px;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.25s ease;
        border: 1.5px solid transparent;
    }
    .btn-premium-import {
        background: rgba(99, 102, 241, 0.08);
        color: #4F46E5;
        border-color: rgba(99, 102, 241, 0.25);
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.12);
    }
    .btn-premium-import:hover {
        background: rgba(99, 102, 241, 0.15);
        border-color: rgba(99, 102, 241, 0.45);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.2);
        color: #4338CA;
    }
    .btn-premium-export {
        background: rgba(16, 185, 129, 0.08);
        color: #059669;
        border-color: rgba(16, 185, 129, 0.25);
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.12);
    }
    .btn-premium-export:hover {
        background: rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.45);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.2);
        color: #047857;
    }
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
    .smaller { font-size: 0.75rem; }
</style>
@endsection
