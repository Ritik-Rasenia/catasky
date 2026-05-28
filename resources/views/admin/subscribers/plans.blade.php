@extends('admin.layouts.app')

@section('title', 'Subscription Plans Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold text-dark mb-1">Subscription Plans</h3>
            <p class="text-muted">Configure and manage multi-subscriber SaaS subscription pricing tiers, feature lists, and limit thresholds.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <button type="button" class="btn btn-primary  rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                <i class="fa-solid fa-plus me-2"></i>Add New Plan
            </button>
        </div>
    </div>

    <!-- Plans Cards -->
    <div class="row mb-5 g-4">
        @foreach($plans as $plan)
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0  rounded-4 overflow-hidden position-relative {{ !$plan->is_active ? 'opacity-75' : '' }}" style="border-top: 5px solid {{ $plan->price > 1000 ? 'var(--secondary-color)' : 'var(--primary-color)' }} !important;">
                @if(!$plan->is_active)
                    <span class="badge bg-danger rounded-0 position-absolute end-0 top-0 px-3 py-1 text-uppercase small">Inactive</span>
                @endif
                <div class="card-body p-4 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="fw-bold text-dark mb-1">{{ $plan->name }}</h5>
                        <small class="text-muted">{{ Str::limit($plan->description, 60) }}</small>
                    </div>
                    <div class="my-3 bg-light p-3 rounded-3 text-center">
                        <span class="h2 fw-bold text-dark mb-0">{{ number_format($plan->price, 0) }}</span>
                        <span class="text-muted small"> {{ $plan->currency }} / {{ $plan->duration_days }} Days</span>
                    </div>
                    
                    <ul class="list-unstyled small text-muted mb-4 flex-grow-1">
                        <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i><strong>{{ $plan->product_limit }}</strong> Product Limit</li>
                        <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i><strong>{{ $plan->attribute_limit }}</strong> Attribute Limit</li>
                        <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i><strong>{{ $plan->share_link_limit }}</strong> Share Links</li>
                        
                        @if($plan->pdf_sharing) <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i>PDF sharing enabled</li> @endif
                        @if($plan->image_sharing) <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i>Image sharing enabled</li> @endif
                        @if($plan->watermark_removal) <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i>No PDF Watermark</li> @endif
                        @if($plan->custom_branding) <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i>Custom Branding</li> @endif
                        @if($plan->analytics) <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i>Traffic Analytics</li> @endif
                    </ul>

                    <div class="d-grid mt-auto gap-2">
                        <button type="button" class="btn btn-outline-primary rounded-pill btn-edit-plan" data-plan="{{ json_encode($plan) }}">
                            <i class="fa-solid fa-pen-to-square me-2"></i>Edit Config
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Table List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0  rounded-4">
                <div class="card-header border-0 pb-0">
                    <h5 class="fw-bold text-dark mb-0">Plans Reference Directory</h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">Plan</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Price</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Period</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Product Limit</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Active Users</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Status</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plans as $plan)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $plan->name }}</div>
                                        <span class="small text-muted">{{ Str::limit($plan->description, 40) }}</span>
                                    </td>
                                    <td class="fw-bold text-dark">{{ number_format($plan->price, 2) }} {{ $plan->currency }}</td>
                                    <td>{{ $plan->duration_days }} Days</td>
                                    <td>{{ $plan->product_limit }} Products</td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 rounded">
                                            {{ $plan->subscriptions_count }} Subscriptions
                                        </span>
                                    </td>
                                    <td>
                                        @if($plan->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">Active</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-pill btn-edit-plan" data-plan="{{ json_encode($plan) }}">
                                            <i class="fa-solid fa-pen me-1"></i>Edit
                                        </button>
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

<!-- Create Plan Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('admin.subscription-plans.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="createPlanModalLabel">Add Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Plan Name</label>
                            <input type="text" name="name" class="form-control bg-light" placeholder="e.g. Starter Plan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Price (INR)</label>
                            <input type="number" name="price" class="form-control bg-light" placeholder="e.g. 499" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Duration (Days)</label>
                            <input type="number" name="duration_days" class="form-control bg-light" placeholder="e.g. 30" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control bg-light" value="0" min="0">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold">Short Description</label>
                            <textarea name="description" class="form-control bg-light" rows="2" placeholder="Describe this pricing tier..."></textarea>
                        </div>

                        <!-- System Limits -->
                        <div class="col-12">
                            <div class="px-2 py-1 bg-light rounded-3 fw-bold small text-muted text-uppercase mb-2"><i class="fa-solid fa-gears me-2"></i>Tier Limits</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Product Upload Limit</label>
                            <input type="number" name="product_limit" class="form-control bg-light" placeholder="e.g. 50" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Attribute Limit</label>
                            <input type="number" name="attribute_limit" class="form-control bg-light" placeholder="e.g. 20" min="1" value="20">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Share Link Limit</label>
                            <input type="number" name="share_link_limit" class="form-control bg-light" placeholder="e.g. 100" min="1" value="100">
                        </div>

                        <!-- System Features Checkboxes -->
                        <div class="col-12">
                            <div class="px-2 py-1 bg-light rounded-3 fw-bold small text-muted text-uppercase mb-2"><i class="fa-solid fa-circle-check me-2"></i>Tier Features</div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="pdf_sharing" value="1" id="pdfShare" checked>
                                <label class="form-check-label fw-semibold" for="pdfShare">PDF Catalogue Sharing</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="image_sharing" value="1" id="imgShare" checked>
                                <label class="form-check-label fw-semibold" for="imgShare">Image Gallery Sharing</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="watermark_removal" value="1" id="watermarkRem">
                                <label class="form-check-label fw-semibold" for="watermarkRem">Watermark Removal</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="custom_branding" value="1" id="branding">
                                <label class="form-check-label fw-semibold" for="branding">Custom Branding Layout</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="analytics" value="1" id="analytics">
                                <label class="form-check-label fw-semibold" for="analytics">Traffic Analytics</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                                <label class="form-check-label fw-semibold" for="isActive">Plan Active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Features Checklist (One per line)</label>
                            <textarea name="features" class="form-control bg-light" rows="3" placeholder="e.g.&#10;50 Products&#10;20 Attributes&#10;PDF & Image Sharing"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Plan Modal -->
<div class="modal fade" id="editPlanModal" tabindex="-1" aria-labelledby="editPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="editPlanForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editPlanModalLabel">Edit Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Plan Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control bg-light" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Price (INR)</label>
                            <input type="number" name="price" id="edit_price" class="form-control bg-light" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Duration (Days)</label>
                            <input type="number" name="duration_days" id="edit_duration_days" class="form-control bg-light" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control bg-light" min="0">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold">Short Description</label>
                            <textarea name="description" id="edit_description" class="form-control bg-light" rows="2"></textarea>
                        </div>

                        <!-- System Limits -->
                        <div class="col-12">
                            <div class="px-2 py-1 bg-light rounded-3 fw-bold small text-muted text-uppercase mb-2"><i class="fa-solid fa-gears me-2"></i>Tier Limits</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Product Upload Limit</label>
                            <input type="number" name="product_limit" id="edit_product_limit" class="form-control bg-light" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Attribute Limit</label>
                            <input type="number" name="attribute_limit" id="edit_attribute_limit" class="form-control bg-light" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Share Link Limit</label>
                            <input type="number" name="share_link_limit" id="edit_share_link_limit" class="form-control bg-light" min="1">
                        </div>

                        <!-- System Features Checkboxes -->
                        <div class="col-12">
                            <div class="px-2 py-1 bg-light rounded-3 fw-bold small text-muted text-uppercase mb-2"><i class="fa-solid fa-circle-check me-2"></i>Tier Features</div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="pdf_sharing" value="1" id="edit_pdf_sharing">
                                <label class="form-check-label fw-semibold" for="edit_pdf_sharing">PDF Catalogue Sharing</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="image_sharing" value="1" id="edit_image_sharing">
                                <label class="form-check-label fw-semibold" for="edit_image_sharing">Image Gallery Sharing</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="watermark_removal" value="1" id="edit_watermark_removal">
                                <label class="form-check-label fw-semibold" for="edit_watermark_removal">Watermark Removal</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="custom_branding" value="1" id="edit_custom_branding">
                                <label class="form-check-label fw-semibold" for="edit_custom_branding">Custom Branding Layout</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="analytics" value="1" id="edit_analytics">
                                <label class="form-check-label fw-semibold" for="edit_analytics">Traffic Analytics</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch py-1">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
                                <label class="form-check-label fw-semibold" for="edit_is_active">Plan Active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Features Checklist (One per line)</label>
                            <textarea name="features" id="edit_features" class="form-control bg-light" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        // Modal Trigger - Edit Plan
        $('.btn-edit-plan').on('click', function() {
            let plan = $(this).data('plan');
            
            $('#edit_name').val(plan.name);
            $('#edit_price').val(plan.price);
            $('#edit_duration_days').val(plan.duration_days);
            $('#edit_sort_order').val(plan.sort_order);
            $('#edit_description').val(plan.description);
            $('#edit_product_limit').val(plan.product_limit);
            $('#edit_attribute_limit').val(plan.attribute_limit);
            $('#edit_share_link_limit').val(plan.share_link_limit);
            
            // Set switches
            $('#edit_pdf_sharing').prop('checked', plan.pdf_sharing == 1);
            $('#edit_image_sharing').prop('checked', plan.image_sharing == 1);
            $('#edit_watermark_removal').prop('checked', plan.watermark_removal == 1);
            $('#edit_custom_branding').prop('checked', plan.custom_branding == 1);
            $('#edit_analytics').prop('checked', plan.analytics == 1);
            $('#edit_is_active').prop('checked', plan.is_active == 1);
            
            // Features
            if (plan.features) {
                if (Array.isArray(plan.features)) {
                    $('#edit_features').val(plan.features.join('\n'));
                } else {
                    $('#edit_features').val(plan.features);
                }
            } else {
                $('#edit_features').val('');
            }

            $('#editPlanForm').attr('action', window.baseUrl + '/admin/subscription-plans/' + plan.id);
            $('#editPlanModal').modal('show');
        });
    });
</script>
@endpush
@endsection
