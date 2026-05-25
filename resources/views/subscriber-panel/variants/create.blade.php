@extends('subscriber-panel.layouts.app')

@section('title', 'Create Product Variant')
@section('page-title', 'Create Product Variant')
@section('breadcrumb', '<a href="' . route('subscriber.variants.index') . '">Variants</a> → Add Variant')

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-tag-fill me-2 text-primary"></i>Variant Details</h6>
            </div>
            <div class="vp-card-body">
                <form action="{{ route('subscriber.variants.store') }}" method="POST">
                    @csrf
                    
                    {{-- Select Parent Product --}}
                    <div class="vp-form-group">
                        <label class="vp-label">Parent Product <span class="text-danger">*</span></label>
                        <select name="subscriber_product_id" class="vp-select" id="product-select" required>
                            <option value="">-- Select Product --</option>
                            @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ (request('product_id') == $prod->id) ? 'selected' : '' }}>
                                {{ $prod->name }} (Category: {{ $prod->category?->name ?? 'General' }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    @if($selectedProduct)
                        @if($variantAttributes->count() > 0)
                            <div class="mb-4 p-3 rounded-3" style="background:#FAFBFD; border:1px solid #E2E8F0;">
                                <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#64748B; margin-bottom:12px;">
                                    Category template variant-enabled attributes:
                                </div>
                                <div class="row g-3">
                                    @foreach($variantAttributes as $attr)
                                    <div class="col-md-6">
                                        <div class="vp-form-group mb-0">
                                            <label class="vp-label">{{ $attr->name }} <span class="text-danger">*</span></label>
                                            
                                            @if($attr->isSelectType())
                                                <select name="attributes[{{ $attr->id }}]" class="vp-select" required>
                                                    <option value="">-- Select option --</option>
                                                    @foreach($attr->options as $opt)
                                                    <option value="{{ $opt->label }}">{{ $opt->label }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" name="attributes[{{ $attr->id }}]" class="vp-input" placeholder="e.g. {{ $attr->placeholder ?? 'Value' }}" required>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Variant SKU <span class="text-danger">*</span></label>
                                        <input type="text" name="variant_sku" class="vp-input" placeholder="e.g. PROD-RED-XL" required value="{{ old('variant_sku') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Price Override (₹)</label>
                                        <input type="number" name="price" step="0.01" class="vp-input" placeholder="Inherited: ₹{{ $selectedProduct->offer_price ?: $selectedProduct->mrp }}" value="{{ old('price') }}">
                                        <small class="text-muted" style="font-size:0.7rem;">Leave blank to inherit the main product's price.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="vp-form-group">
                                        <label class="vp-label">Stock Quantity <span class="text-danger">*</span></label>
                                        <input type="number" name="stock" class="vp-input" placeholder="100" required value="{{ old('stock', 0) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn-subscriber px-4">
                                    Create Variant
                                </button>
                                <a href="{{ route('subscriber.variants.index') }}" class="btn-subscriber-outline">
                                    Cancel
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning rounded-3 fs-13 d-flex align-items-center gap-2 mb-0">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                <div>
                                    This product's category (<strong>{{ $selectedProduct->category?->name }}</strong>) has no variant-enabled attributes mapped in the PIM template. Please contact the administrator.
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-muted fs-13">
                            <i class="bi bi-arrow-up-circle fs-3 d-block mb-2"></i>
                            Select a parent product above to load its PIM category variant template.
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $('#product-select').on('change', function() {
        const val = $(this).val();
        if (val) {
            window.location.href = `{{ route('subscriber.variants.create') }}?product_id=${val}`;
        }
    });
</script>
@endpush
