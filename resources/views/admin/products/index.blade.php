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

    <!-- Tabs Nav -->
    <ul class="nav nav-pills nav-custom mb-4 gap-2" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-2 shadow-sm" id="master-tab" data-bs-toggle="tab" data-bs-target="#master-pane" type="button" role="tab" aria-controls="master-pane" aria-selected="true" style="font-family:'Outfit',sans-serif; font-weight:700;">
                <i class="fa-solid fa-box me-2"></i>Master Catalogue
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 shadow-sm" id="subscriber-tab" data-bs-toggle="tab" data-bs-target="#subscriber-pane" type="button" role="tab" aria-controls="subscriber-pane" aria-selected="false" style="font-family:'Outfit',sans-serif; font-weight:700;">
                <i class="fa-solid fa-people-carry-box me-2"></i>Subscriber Products
            </button>
        </li>
    </ul>

    <div class="tab-content" id="productTabsContent">
        <!-- Master Products Tab Pane -->
        <div class="tab-pane fade show active" id="master-pane" role="tabpanel" aria-labelledby="master-tab" tabindex="0">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap" id="productTable" style="width:100%;">
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
                                @foreach($products as $product)
                                <tr>
                                     <td class="ps-4">
                                         <div class="d-flex align-items-center">
                                             @if($product->thumbnail)
                                                 <img src="{{ $product->thumbnail_url }}" width="45" height="45" class="rounded-3 object-fit-cover border me-3" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($product->name) }}&background=f3f4f6&color=6366f1'">
                                             @else
                                                 <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border me-3" style="width: 45px; height: 45px;">
                                                     <i class="fa-solid fa-box small"></i>
                                                 </div>
                                             @endif
                                             <div>
                                                 <div class="fw-bold text-dark">{{ $product->name }}</div>
                                                 <div class="small text-muted">SKU: {{ $product->sku ?? 'N/A' }}</div>
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

        <!-- Subscriber Products Tab Pane -->
        <div class="tab-pane fade" id="subscriber-pane" role="tabpanel" aria-labelledby="subscriber-tab" tabindex="0">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap" id="subscriberProductTable" style="width:100%;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">Product</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Subscriber / Shop</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Category</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">SKU / Stock</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Price Range</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted text-center">Status</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subscriberProducts as $subProduct)
                                <tr>
                                     <td class="ps-4">
                                         <div class="d-flex align-items-center">
                                             @if($subProduct->thumbnail)
                                                 <img src="{{ $subProduct->thumbnail_url }}" width="45" height="45" class="rounded-3 object-fit-cover border me-3" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($subProduct->name) }}&background=f3f4f6&color=6366f1'">
                                             @else
                                                 <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border me-3" style="width: 45px; height: 45px;">
                                                     <i class="fa-solid fa-box small"></i>
                                                 </div>
                                             @endif
                                             <div>
                                                 <a href="{{ route('product.details', $subProduct->slug) }}" target="_blank" class="fw-bold text-dark text-decoration-none hover-primary">{{ $subProduct->name }}</a>
                                                 <div class="small text-muted">SKU: {{ $subProduct->sku ?? 'N/A' }}</div>
                                             </div>
                                         </div>
                                     </td>
                                     <td>
                                         <div class="fw-bold text-primary">
                                             <a href="{{ route('admin.subscribers.show', $subProduct->user_id) }}" class="text-decoration-none fw-semibold">
                                                 {{ $subProduct->user->subscriberProfile->company_name ?? $subProduct->user->name }}
                                             </a>
                                         </div>
                                         <div class="smaller text-muted">Rep: {{ $subProduct->user->name }}</div>
                                     </td>
                                     <td>
                                         <div class="small fw-semibold">{{ $subProduct->category->name ?? 'N/A' }}</div>
                                         <div class="smaller text-muted">{{ $subProduct->subcategory->name ?? '' }}</div>
                                     </td>
                                     <td>
                                         <div class="small fw-semibold text-dark">{{ $subProduct->sku ?? 'N/A' }}</div>
                                         <div class="smaller text-muted">{{ $subProduct->stock ?? 0 }} pcs left</div>
                                     </td>
                                     <td>
                                         <div class="fw-bold text-primary">
                                             @if($subProduct->offer_price)
                                                 ₹{{ number_format($subProduct->offer_price, 2) }}
                                                 @if($subProduct->mrp && $subProduct->mrp > $subProduct->offer_price)
                                                     <div class="smaller text-muted text-decoration-line-through">₹{{ number_format($subProduct->mrp, 2) }}</div>
                                                 @endif
                                             @elseif($subProduct->mrp)
                                                 ₹{{ number_format($subProduct->mrp, 2) }}
                                             @else
                                                 <span class="text-muted small fst-italic">Price on Request</span>
                                             @endif
                                         </div>
                                     </td>
                                     <td class="text-center">
                                         @if($subProduct->status == 'active')
                                             <span class="badge bg-success-soft text-success rounded-pill px-3">Active</span>
                                         @elseif($subProduct->status == 'draft')
                                             <span class="badge bg-warning-soft text-warning rounded-pill px-3">Draft</span>
                                         @else
                                             <span class="badge bg-secondary-soft text-secondary rounded-pill px-3">Inactive</span>
                                         @endif
                                     </td>
                                     <td class="text-end pe-4">
                                         <div class="d-inline-flex gap-2 justify-content-end align-items-center">
                                             <a href="{{ route('product.details', $subProduct->slug) }}" target="_blank" class="btn-ap-action btn-ap-view" title="View Public Page">
                                                 <i class="fa-solid fa-eye text-primary" style="font-size: 13px !important;"></i>
                                             </a>
                                             @can('delete-products')
                                             <form action="{{ route('admin.subscriber-products.destroy', $subProduct->id) }}" method="POST" class="d-inline form-delete">
                                                 @csrf
                                                 @method('DELETE')
                                                 <button type="button" class="btn-ap-action btn-ap-reject btn-delete" title="Delete Subscriber Product">
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

@push('css')
<style>
    .nav-custom .nav-link {
        color: var(--text-muted, #64748b) !important;
        background: transparent !important;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        border: 1px solid transparent !important;
        transition: all 0.2s ease;
    }
    .nav-custom .nav-link:hover {
        background: rgba(99, 102, 241, 0.06) !important;
        color: #4F46E5 !important;
    }
    .nav-custom .nav-link.active {
        background: white !important;
        color: #4F46E5 !important;
        border-color: var(--border-color, #e2e8f0) !important;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.08) !important;
    }
    html[data-theme="dark"] .nav-custom .nav-link.active {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #818cf8 !important;
    }
    .hover-primary:hover {
        color: #4F46E5 !important;
    }
</style>
@endpush
 
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

        $('#subscriberProductTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "responsive": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Filter subscriber products..."
            }
        });

        // Auto adjust datatable columns when switching tabs
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
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
