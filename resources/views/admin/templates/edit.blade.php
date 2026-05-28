@extends('admin.layouts.app')

@section('title', 'Configure Category Template')
@section('page-title', 'Configure Category Template')
@section('breadcrumb', '<a href="' . route('admin.templates.index') . '">Templates</a> → Edit Schema')

@section('content')
<div class="row g-3">
    <div class="col-lg-9">
        <form action="{{ route('admin.templates.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card  border-0 mb-3" style="border-radius:16px;">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-gear-fill me-2 text-primary"></i>Schema Designer: {{ $category->name }}</h6>
                        <small class="text-muted">Select global attributes to form the dynamic entry template for subscribers</small>
                    </div>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius:10px;">
                        Save Template Changes
                    </button>
                </div>
                <div class="card-body py-2">
                    @if($attributes->count() > 0)
                        @php $groups = $attributes->groupBy('attribute_group_id'); @endphp
                        
                        @foreach($groups as $groupId => $groupAttrs)
                            @php $groupName = $groupAttrs->first()?->group?->name ?? 'General / Basic specifications'; @endphp
                            <div class="mb-4">
                                <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94A3B8;border-bottom:1px solid #F1F5F9;padding-bottom:8px;margin-bottom:16px;">
                                    {{ $groupName }}
                                </div>
                                <div class="row g-3">
                                    @foreach($groupAttrs as $attr)
                                        @php 
                                            $assigned = $assignedAttributes->get($attr->id);
                                            $isChecked = !empty($assigned);
                                            $isRequired = $assigned?->is_required ?? false;
                                            $sortOrder = $assigned?->sort_order ?? 0;
                                        @endphp
                                        <div class="col-md-6 col-lg-4">
                                            <div class="p-3 border rounded-3" style="background:#FAFBFD; border-color:#E2E8F0 !important;">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input attribute-checkbox" type="checkbox" name="attributes[{{ $attr->id }}][checked]" value="1" id="attr{{ $attr->id }}" {{ $isChecked ? 'checked' : '' }} onchange="toggleInputs({{ $attr->id }})">
                                                    <label class="form-check-label fw-bold text-dark fs-14" for="attr{{ $attr->id }}">{{ $attr->name }}</label>
                                                </div>
                                                <div class="text-muted fs-12 mb-3">
                                                    Type: <code class="text-primary">{{ strtoupper($attr->type) }}</code> @if($attr->unit)<span class="badge bg-light text-dark">{{ $attr->unit }}</span>@endif
                                                </div>
                                                <div class="row g-2 d-none config-fields-{{ $attr->id }}" id="config-fields-{{ $attr->id }}">
                                                    <div class="col-6">
                                                        <div class="form-check mt-1">
                                                            <input class="form-check-input" type="checkbox" name="attributes[{{ $attr->id }}][is_required]" value="1" id="req{{ $attr->id }}" {{ $isRequired ? 'checked' : '' }}>
                                                            <label class="form-check-label fs-12 text-muted" for="req{{ $attr->id }}">Is Required?</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="number" name="attributes[{{ $attr->id }}][sort_order]" class="form-control form-control-sm" placeholder="Order" value="{{ $sortOrder }}" style="border-radius:6px; font-size:0.75rem;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-sliders text-muted fs-2"></i>
                            <h6 class="fw-bold text-dark mt-2">No global attributes defined</h6>
                            <p class="text-muted fs-13">Create PIM global attributes first before designing templates.</p>
                            <a href="{{ route('admin.attributes.index') }}" class="btn btn-sm btn-primary">Create Attributes</a>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
    
    <div class="col-lg-3">
        <div class="card  border-0 mb-3" style="border-radius:16px;">
            <div class="card-header bg-white py-3 border-0" style="border-top-left-radius:16px; border-top-right-radius:16px;">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle-fill me-2 text-primary"></i>Platform Template Rules</h6>
            </div>
            <div class="card-body fs-13 text-muted">
                <p><strong>1. Category Specificity</strong><br>Subscribers selecting this category will ONLY be requested to fill attributes assigned to this dynamic template.</p>
                <p><strong>2. Sort Order</strong><br>Items with lower sort order show up first in the subscriber form and sharing catalogues.</p>
                <p><strong>3. Variant Engine</strong><br>Attributes marked as <code>Variant-enabled</code> will automatically be accessible by subscribers in the variant combinations matrix.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function toggleInputs(id) {
        const checkbox = document.getElementById(`attr${id}`);
        const configFields = document.getElementById(`config-fields-${id}`);
        if (checkbox.checked) {
            configFields.classList.remove('d-none');
        } else {
            configFields.classList.add('d-none');
        }
    }
    
    // Run on load
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".attribute-checkbox").forEach(chk => {
            const id = chk.id.replace('attr', '');
            toggleInputs(id);
        });
    });
</script>
@endpush
