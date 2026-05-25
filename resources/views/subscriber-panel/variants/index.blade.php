@extends('subscriber-panel.layouts.app')

@section('title', 'Product Variants')
@section('page-title', 'Product Variants')
@section('breadcrumb', 'Workspace → Variants')

@section('content')
<div class="vp-page-header">
    <h5 class="vp-page-title"><i class="bi bi-tags-fill me-2 text-primary"></i>Product Variants Management</h5>
    <a href="{{ route('subscriber.variants.create') }}" class="btn-subscriber">
        <i class="bi bi-plus-lg"></i> Create Product Variant
    </a>
</div>

<div class="vp-card">
    <div class="vp-card-header">
        <h6 class="vp-card-title">All Dynamic Variations</h6>
        <form action="{{ route('subscriber.variants.index') }}" method="GET" class="d-flex gap-2" style="max-width:300px;">
            <input type="text" name="search" class="vp-input py-1.5" placeholder="Search SKU or product..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-subscriber py-1.5 px-3"><i class="bi bi-search"></i></button>
        </form>
    </div>
    <div class="vp-card-body p-0">
        @if($variants->count() > 0)
        <div class="table-responsive">
            <table class="vp-table">
                <thead>
                    <tr>
                        <th class="ps-4">Product</th>
                        <th>Variant SKU</th>
                        <th>Attributes</th>
                        <th>Price Overrides (₹)</th>
                        <th>Stock / Inventory</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($variants as $variant)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2.5">
                                @if($variant->product->thumbnail)
                                <img src="{{ $variant->product->thumbnail_url }}" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;border:1px solid #E2E8F0;">
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $variant->product->name }}</div>
                                    <div class="text-muted fs-11">Code: {{ $variant->product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <code class="text-primary fw-bold" style="font-size:0.82rem;">{{ $variant->variant_sku }}</code>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1.5">
                                @foreach($variant->variantAttributes as $varAttr)
                                <span class="badge bg-light text-dark border px-2.5 py-1" style="font-size:0.75rem;">
                                    <strong>{{ $varAttr->attribute?->name }}:</strong> {{ $varAttr->attribute_value }}
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <form action="{{ route('subscriber.variants.update', $variant->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <td>
                                <input type="number" name="price" step="0.01" class="vp-input py-1 px-2" value="{{ $variant->price }}" style="max-width:100px; font-size:0.82rem;" placeholder="Inherited">
                            </td>
                            <td>
                                <input type="number" name="stock" class="vp-input py-1 px-2" value="{{ $variant->stock }}" style="max-width:90px; font-size:0.82rem;" required>
                            </td>
                            <td>
                                <select name="status" class="vp-select py-1 px-2" style="max-width:100px; font-size:0.82rem;">
                                    <option value="1" {{ $variant->status ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$variant->status ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex gap-1.5">
                                    <button type="submit" class="btn btn-sm btn-subscriber-outline py-1 px-2.5" style="font-size:0.75rem;">
                                        <i class="bi bi-save"></i> Save
                                    </button>
                                </div>
                            </td>
                        </form>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $variants->links() }}
        </div>
        @else
        <div class="empty-state">
            <i class="bi bi-tags-fill empty-state-icon"></i>
            <h5 class="empty-state-title">No variants defined</h5>
            <p class="empty-state-text">Build sellable variant combinations (e.g. distinct sizes, pack sizes, or color models) to manage exact stock overrides.</p>
            <a href="{{ route('subscriber.variants.create') }}" class="btn-subscriber">
                <i class="bi bi-plus-lg"></i> Create First Variant
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
