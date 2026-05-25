@extends('admin.layouts.app')

@section('title', 'Category PIM Templates')
@section('page-title', 'Category PIM Templates')
@section('breadcrumb', 'Catalogue → Templates')

@section('content')
<div class="card shadow-sm border-0" style="border-radius:16px;">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" style="border-top-left-radius:16px; border-top-right-radius:16px;">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-layers-fill me-2 text-primary"></i>Category PIM Templates</h6>
        <span class="badge bg-light text-dark font-sans" style="font-size:0.75rem;">Configure Form Schemas & Rules</span>
    </div>
    <div class="card-body p-0">
        @if($categories->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase fs-11 text-muted fw-bold">Category</th>
                        <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Products Count</th>
                        <th class="py-3 text-uppercase fs-11 text-muted fw-bold">Template Config</th>
                        <th class="pe-4 py-3 text-end text-uppercase fs-11 text-muted fw-bold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($cat->image)
                                <img src="{{ asset('uploads/categories/' . $cat->image) }}" alt="" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1px solid #E2E8F0;">
                                @else
                                <div class="bg-light text-muted d-flex align-items-center justify-content-center" style="width:40px;height:40px;border-radius:10px;font-size:1.1rem;font-weight:700;">
                                    {{ strtoupper(substr($cat->name, 0, 1)) }}
                                </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $cat->name }}</div>
                                    <div class="text-muted fs-12">{{ $cat->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark font-sans px-2.5 py-1">
                                {{ $cat->products_count }} active products
                            </span>
                        </td>
                        <td>
                            @php $count = $categoryAttributeCounts[$cat->id] ?? 0; @endphp
                            @if($count > 0)
                            <span class="badge bg-success-subtle text-success px-2.5 py-1" style="font-size:0.75rem;">
                                <i class="bi bi-check-circle-fill me-1"></i> {{ $count }} attributes assigned
                            </span>
                            @else
                            <span class="badge bg-warning-subtle text-warning px-2.5 py-1" style="font-size:0.75rem;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> No dynamic template
                            </span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('admin.templates.edit', $cat->id) }}" class="btn btn-sm btn-outline-primary px-3" style="border-radius:8px; font-size:0.8rem;">
                                <i class="bi bi-gear-fill"></i> Configure Template
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-layers text-muted fs-1 mb-3"></i>
            <h5 class="fw-bold text-dark">No categories found</h5>
            <p class="text-muted fs-13 max-width-360 mx-auto">Please create categories first to configure PIM templates.</p>
        </div>
        @endif
    </div>
</div>
@endsection
