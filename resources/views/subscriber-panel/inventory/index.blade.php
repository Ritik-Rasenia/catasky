@extends('subscriber-panel.layouts.app')

@section('title', 'Inventory Stock Manager')
@section('page-title', 'Inventory Stock Manager')
@section('breadcrumb', 'Workspace → Inventory')

@section('content')
<div class="vp-page-header">
    <h5 class="vp-page-title"><i class="bi bi-box-seam-fill me-2 text-primary"></i>Stock & Inventory Management</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('subscriber.inventory.index') }}?stock_status=low" class="btn btn-sm btn-danger d-flex align-items-center gap-1.5 px-3" style="border-radius:10px;">
            <i class="bi bi-exclamation-octagon-fill"></i> View Low Stock Alert
        </a>
        @if(request('stock_status') || request('search'))
        <a href="{{ route('subscriber.inventory.index') }}" class="btn btn-sm btn-light border" style="border-radius:10px;">
            Clear Filters
        </a>
        @endif
    </div>
</div>

<div class="vp-card">
    <div class="vp-card-header">
        <h6 class="vp-card-title">Live Stock Quantities</h6>
        <form action="{{ route('subscriber.inventory.index') }}" method="GET" class="d-flex gap-2" style="max-width:300px;">
            <input type="text" name="search" class="vp-input py-1.5" placeholder="Search SKU or name..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-subscriber py-1.5 px-3"><i class="bi bi-search"></i></button>
        </form>
    </div>
    <div class="vp-card-body p-0">
        @if($products->count() > 0)
        <div class="table-responsive">
            <table class="vp-table">
                <thead>
                    <tr>
                        <th class="ps-4">Product details</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>SKUs & Stock levels (Live editing)</th>
                        <th class="pe-4 text-end">Quick Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td class="ps-4" style="width:30%;">
                            <div class="d-flex align-items-center gap-2.5">
                                @if($product->thumbnail)
                                <img src="{{ $product->thumbnail_url }}" alt="" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1px solid #E2E8F0;">
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $product->name }}</div>
                                    <div class="text-muted fs-12">Main SKU: {{ $product->sku ?: 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2.5 py-1">
                                {{ $product->category?->name ?? 'General' }}
                            </span>
                        </td>
                        <td>
                            @if($product->variants->count() > 0)
                            <span class="badge bg-primary-subtle text-primary px-2.5 py-1">
                                <i class="bi bi-tags-fill"></i> Multi-Variant
                            </span>
                            @else
                            <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">
                                Simple Product
                            </span>
                            @endif
                        </td>
                        <td>
                            @if($product->variants->count() > 0)
                                <div class="d-flex flex-column gap-2 py-2">
                                    @foreach($product->variants as $variant)
                                    <div class="d-flex align-items-center gap-3 p-2 rounded border bg-light-subtle" style="max-width:450px;">
                                        <div style="flex:1;">
                                            <code class="text-primary fw-bold" style="font-size:0.8rem;">{{ $variant->variant_sku }}</code>
                                            <div class="text-muted fs-11" style="margin-top:2px;">
                                                @foreach($variant->variantAttributes as $varAttr)
                                                {{ $varAttr->attribute?->name }}: {{ $varAttr->attribute_value }}@if(!$loop->last), @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5">
                                            <input type="number" id="stock-var-{{ $variant->id }}" class="vp-input py-1 px-2 text-center" value="{{ $variant->stock }}" style="width:75px; font-size:0.82rem;" min="0">
                                            <button type="button" class="btn btn-sm btn-subscriber py-1 px-2.5" onclick="updateStockLevel('variant', {{ $variant->id }}, $('#stock-var-{{ $variant->id }}').val())" style="font-size:0.75rem;">
                                                Update
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                @php
                                    // Get default/fallback variant if it exists, or display 0
                                    $defaultVar = $product->variants->first();
                                    $stockVal = $defaultVar ? $defaultVar->stock : 0;
                                    $varId = $defaultVar ? $defaultVar->id : null;
                                @endphp
                                <div class="d-flex align-items-center gap-1.5 py-2">
                                    <input type="number" id="stock-prod-{{ $product->id }}" class="vp-input py-1 px-2 text-center" value="{{ $stockVal }}" style="max-width:100px; font-size:0.82rem;" min="0">
                                    <button type="button" class="btn btn-sm btn-subscriber py-1 px-2.5" onclick="updateStockLevel('product', {{ $product->id }}, $('#stock-prod-{{ $product->id }}').val())" style="font-size:0.75rem;">
                                        Update Stock
                                    </button>
                                </div>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('subscriber.products.edit', $product->id) }}" class="btn btn-sm btn-light border-0" style="border-radius:8px;" title="Edit Product">
                                <i class="bi bi-pencil-fill text-muted"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $products->links() }}
        </div>
        @else
        <div class="empty-state">
            <i class="bi bi-box-seam-fill empty-state-icon"></i>
            <h5 class="empty-state-title">No products in inventory</h5>
            <p class="empty-state-text">Create products first to manage stock quantities, inventory thresholds, and low-level alerts.</p>
            <a href="{{ route('subscriber.products.create') }}" class="btn-subscriber">
                <i class="bi bi-plus-lg"></i> Add Product
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@push('js')
<script>
    function updateStockLevel(type, id, stock) {
        if (stock === '' || isNaN(stock) || stock < 0) {
            Swal.fire('Error!', 'Please enter a valid stock quantity.', 'error');
            return;
        }

        $.ajax({
            url: `{{ route('subscriber.inventory.update-stock') }}`,
            type: 'POST',
            data: {
                type: type,
                id: id,
                stock: stock
            },
            success: function(res) {
                if (res.success) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Stock quantity updated successfully.'
                    });
                } else {
                    Swal.fire('Error!', res.message || 'Failed to update stock.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'An unexpected error occurred. Please try again.', 'error');
            }
        });
    }
</script>
@endpush
