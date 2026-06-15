@extends('admin.layouts.app')

@section('title', 'Subscriber Details - ' . ($user->subscriberProfile->company_name ?? $user->name))

@section('content')
@php
    $profile = $user->subscriberProfile;
    $activeSub = $user->activeSubscription();
    $status = $profile->status ?? 'pending';
    $badgeClass = match ($status) {
        'active', 'approved' => 'bg-success',
        'suspended', 'rejected' => 'bg-danger',
        default => 'bg-secondary',
    };
    $companyName = $profile->company_name ?? 'No Company Name';
    $initials = strtoupper(substr($companyName !== 'No Company Name' ? $companyName : $user->name, 0, 2));
    $revenue = $user->payments()->where('status', 'success')->sum('amount');
    $attrCount = $user->attributes()->count();
    $sharesCount = $user->shareLinks()->count();
    $primaryColor = $profile->primary_color ?? '#4F46E5';
    $secondaryColor = $profile->secondary_color ?? '#7C3AED';
    $addressParts = array_filter([
        $profile->address ?? null,
        trim(($profile->city ?? '') . (($profile?->state ?? false) ? ', ' . $profile->state : '')),
        trim(($profile->country ?? 'India') . (($profile?->pincode ?? false) ? ' - ' . $profile->pincode : '')),
    ]);
@endphp

<div class="container-fluid subscriber-detail-page">
    <div class="subscriber-topbar">
        <a href="{{ route('admin.subscribers.index') }}" class="subscriber-back-link">
            <i class="fa-solid fa-arrow-left-long"></i>
            <span>Back to Subscriber List</span>
        </a>
    </div>

    <section class="subscriber-hero">
        <div class="subscriber-hero-main">
            <div class="subscriber-logo-wrap">
                @if($profile && $profile->logo)
                    <img src="{{ $profile->logo_url }}" alt="Logo" class="subscriber-logo">
                @else
                    <div class="subscriber-logo-fallback">{{ $initials }}</div>
                @endif
            </div>

            <div class="subscriber-identity">
                <div class="subscriber-title-row">
                    <h1>{{ $companyName }}</h1>
                    <span class="badge {{ $badgeClass }} bg-opacity-10 text-{{ str_replace('bg-', '', $badgeClass) }} text-capitalize">{{ $status }}</span>
                    @if($profile?->is_verified)
                        <span class="badge bg-info bg-opacity-10 text-info"><i class="fa-solid fa-circle-check"></i>Verified</span>
                    @endif
                </div>

                <div class="subscriber-contact-grid">
                    <span><i class="fa-solid fa-user-tie"></i>Representative: <strong>{{ $user->name }}</strong></span>
                    <span><i class="fa-regular fa-envelope"></i>{{ $user->email }}</span>
                    @if($profile?->phone)
                        <span><i class="fa-solid fa-phone"></i>{{ $profile->phone }}</span>
                    @endif
                    @if($profile?->website)
                        <a href="{{ $profile->website }}" target="_blank"><i class="fa-solid fa-globe"></i>{{ $profile->website }}</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="subscriber-actions">
            @if($profile && $profile->company_slug)
                <a href="{{ route('subscriber_store', $profile->company_slug) }}" target="_blank" class="btn btn-outline-info">
                    <i class="fa-solid fa-store"></i>View Catalogue
                </a>
            @endif
            <button type="button" class="btn btn-outline-success btn-assign-plan-modal">
                <i class="fa-solid fa-id-card"></i>Assign Plan
            </button>
            @if($status === 'suspended')
                <form action="{{ route('admin.subscribers.unsuspend', $user->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fa-solid fa-user-check"></i>Activate Profile
                    </button>
                </form>
            @else
                <button type="button" class="btn btn-outline-danger btn-suspend-modal">
                    <i class="fa-solid fa-user-slash"></i>Suspend Account
                </button>
            @endif
            <form action="{{ route('admin.subscribers.destroy', $user->id) }}" method="POST" class="d-inline form-delete">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-danger btn-delete">
                    <i class="fa-solid fa-trash-can"></i>Delete Subscriber
                </button>
            </form>
        </div>
    </section>

    <div class="subscriber-stat-grid">
        <div class="subscriber-stat-card">
            <span class="stat-icon stat-blue"><i class="fa-solid fa-box-open"></i></span>
            <div>
                <span class="stat-label">Total Products</span>
                <strong>{{ $productCount }}</strong>
            </div>
        </div>

        <div class="subscriber-stat-card">
            @if($activeSub)
                <span class="stat-icon stat-green"><i class="fa-solid fa-file-contract"></i></span>
                <div>
                    <span class="stat-label">Current Plan</span>
                    <strong class="text-success">{{ $activeSub->plan->name }}</strong>
                    <small>{{ $activeSub->daysRemaining() }} days left</small>
                </div>
            @else
                <span class="stat-icon stat-red"><i class="fa-solid fa-file-circle-xmark"></i></span>
                <div>
                    <span class="stat-label">Current Plan</span>
                    <strong class="text-danger">No Active Plan</strong>
                    <small>Expired/Trial Over</small>
                </div>
            @endif
        </div>

        <div class="subscriber-stat-card">
            <span class="stat-icon stat-amber"><i class="fa-solid fa-wallet"></i></span>
            <div>
                <span class="stat-label">Total Payments</span>
                <strong>{{ number_format($revenue, 2) }} INR</strong>
            </div>
        </div>
    </div>

    <div class="subscriber-layout-grid">
        <aside class="subscriber-info-panel">
            <div class="panel-heading">
                <span><i class="fa-solid fa-circle-info"></i></span>
                <div>
                    <h2>Company Information</h2>
                    <p>Profile, contacts and brand settings</p>
                </div>
            </div>

            <div class="info-list">
                <div class="info-row">
                    <span>GST Number</span>
                    <strong>{{ $profile->gst_number ?? 'N/A' }}</strong>
                </div>
                <div class="info-row">
                    <span>Email (Inquiries)</span>
                    <strong>{{ $profile->email_for_inquiries ?? $user->email }}</strong>
                </div>
                <div class="info-row">
                    <span>WhatsApp No</span>
                    <strong>{{ $profile->whatsapp_number ?? 'N/A' }}</strong>
                </div>
                <div class="info-row">
                    <span>Bio / Note</span>
                    <strong>{{ $profile->bio ?? 'No company bio provided.' }}</strong>
                </div>
                <div class="info-row">
                    <span>Address</span>
                    <strong>{!! count($addressParts) ? e(implode(', ', $addressParts)) : 'N/A' !!}</strong>
                </div>
                <div class="info-row">
                    <span>Brand Colors</span>
                    <strong class="brand-colors">
                        <i style="background-color: {{ $primaryColor }}" title="Primary: {{ $primaryColor }}"></i>
                        <i style="background-color: {{ $secondaryColor }}" title="Secondary: {{ $secondaryColor }}"></i>
                        <small>Primary & Secondary</small>
                    </strong>
                </div>
                @if($profile?->suspended_at)
                    <div class="info-row suspension-row">
                        <span>Suspension Reason</span>
                        <strong>
                            {{ $profile->suspension_reason ?? 'No reason stated.' }}
                            <small>Suspended on: {{ \Carbon\Carbon::parse($profile->suspended_at)->format('d M, Y H:i') }}</small>
                        </strong>
                    </div>
                @endif
            </div>
        </aside>

        <section class="subscriber-tabs-panel">
            <ul class="nav nav-pills subscriber-tabs" id="subscriberTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="limits-tab" data-bs-toggle="tab" data-bs-target="#limits-pane" type="button" role="tab" aria-controls="limits-pane" aria-selected="true">
                                <i class="fa-solid fa-sliders"></i>Plan Limits
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="subscriptions-tab" data-bs-toggle="tab" data-bs-target="#subscriptions-pane" type="button" role="tab" aria-controls="subscriptions-pane" aria-selected="false">
                                <i class="fa-solid fa-list-check"></i>Subscription History
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments-pane" type="button" role="tab" aria-controls="payments-pane" aria-selected="false">
                                <i class="fa-solid fa-indian-rupee-sign"></i>Payments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices-pane" type="button" role="tab" aria-controls="invoices-pane" aria-selected="false">
                                <i class="fa-solid fa-receipt"></i>Invoices
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Panes -->
                    <div class="tab-content" id="subscriberTabsContent">
                        <!-- Limits Pane -->
                        <div class="tab-pane fade show active" id="limits-pane" role="tabpanel" aria-labelledby="limits-tab" tabindex="0">
                            @if($activeSub)
                                <div class="tab-heading">
                                    <h2>Plan Details & Active Limits</h2>
                                    <p>{{ $activeSub->plan->name }} subscription usage snapshot</p>
                                </div>
                                <div class="limit-grid">
                                    <div class="limit-card">
                                        <span>Product Upload Limit</span>
                                        <div><strong>{{ $productCount }}</strong> / {{ $activeSub->plan->product_limit }} <small>Products</small></div>
                                        @php $prog = $activeSub->plan->product_limit > 0 ? min(100, ($productCount / $activeSub->plan->product_limit) * 100) : 0; @endphp
                                        <div class="progress"><div class="progress-bar" role="progressbar" style="width: {{ $prog }}%" aria-valuenow="{{ $prog }}" aria-valuemin="0" aria-valuemax="100"></div></div>
                                    </div>
                                    <div class="limit-card">
                                        <span>Attribute Limit</span>
                                        <div><strong>{{ $attrCount }}</strong> / {{ $activeSub->plan->attribute_limit }} <small>Attributes</small></div>
                                        @php $prog = $activeSub->plan->attribute_limit > 0 ? min(100, ($attrCount / $activeSub->plan->attribute_limit) * 100) : 0; @endphp
                                        <div class="progress"><div class="progress-bar" role="progressbar" style="width: {{ $prog }}%" aria-valuenow="{{ $prog }}" aria-valuemin="0" aria-valuemax="100"></div></div>
                                    </div>
                                    <div class="limit-card">
                                        <span>Share Links Limit</span>
                                        <div><strong>{{ $sharesCount }}</strong> / {{ $activeSub->plan->share_link_limit }} <small>Links</small></div>
                                        @php $prog = $activeSub->plan->share_link_limit > 0 ? min(100, ($sharesCount / $activeSub->plan->share_link_limit) * 100) : 0; @endphp
                                        <div class="progress"><div class="progress-bar" role="progressbar" style="width: {{ $prog }}%" aria-valuenow="{{ $prog }}" aria-valuemin="0" aria-valuemax="100"></div></div>
                                    </div>
                                    <div class="limit-card access-card">
                                        <span>System Access Rights</span>
                                        <div class="access-badges">
                                                @if($activeSub->plan->pdf_sharing) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fa-solid fa-file-pdf me-1"></i>PDF Share</span> @endif
                                                @if($activeSub->plan->image_sharing) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fa-solid fa-images me-1"></i>Image Gallery</span> @endif
                                                @if($activeSub->plan->watermark_removal) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fa-solid fa-scissors me-1"></i>No Watermark</span> @endif
                                                @if($activeSub->plan->custom_branding) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fa-solid fa-palette me-1"></i>Custom Brand</span> @endif
                                                @if($activeSub->plan->analytics) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fa-solid fa-chart-line me-1"></i>Analytics</span> @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="empty-state">
                                    <div class="vp-empty-state-icon"><i class="fa-solid fa-triangle-exclamation text-danger"></i></div>
                                    <div class="empty-state-title">No active subscription</div>
                                    <p class="empty-state-text">This subscriber currently has no portal access rights.</p>
                                    <button class="btn btn-primary mt-3 btn-assign-plan-modal"><i class="fa-solid fa-id-card"></i>Assign Plan Now</button>
                                </div>
                            @endif
                        </div>



                        <!-- Subscriptions History Pane -->
                        <div class="tab-pane fade" id="subscriptions-pane" role="tabpanel" aria-labelledby="subscriptions-tab" tabindex="0">
                            <h6 class="fw-bold text-dark mb-3">Subscription History</h6>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle small">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Plan Name</th>
                                            <th>Status</th>
                                            <th>Starts At</th>
                                            <th>Ends At</th>
                                            <th>Created Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($user->subscriptions as $sub)
                                            <tr>
                                                <td class="fw-bold text-dark">{{ $sub->plan->name }}</td>
                                                <td>
                                                    @php
                                                        $subStatus = $sub->status;
                                                        $subBadgeClass = 'bg-secondary';
                                                        if ($subStatus === 'active') $subBadgeClass = 'bg-success';
                                                        if ($subStatus === 'trial') $subBadgeClass = 'bg-warning';
                                                        if ($subStatus === 'suspended') $subBadgeClass = 'bg-danger';
                                                        if ($subStatus === 'expired') $subBadgeClass = 'bg-dark';
                                                    @endphp
                                                    <span class="badge {{ $subBadgeClass }} bg-opacity-10 text-{{ str_replace('bg-', '', $subBadgeClass) }} rounded text-capitalize">{{ $subStatus }}</span>
                                                </td>
                                                <td>{{ $sub->starts_at ? $sub->starts_at->format('d M, Y') : '-' }}</td>
                                                <td>{{ $sub->ends_at ? $sub->ends_at->format('d M, Y') : '-' }}</td>
                                                <td>{{ $sub->created_at->format('d M, Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No subscription logs found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Payments Pane -->
                        <div class="tab-pane fade" id="payments-pane" role="tabpanel" aria-labelledby="payments-tab" tabindex="0">
                            <h6 class="fw-bold text-dark mb-3">Payment Receipts & Transactions</h6>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle small">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Txn ID</th>
                                            <th>Plan</th>
                                            <th>Gateway</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Paid Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($user->payments as $payment)
                                            <tr>
                                                <td class="fw-bold text-dark">{{ $payment->transaction_id ?? 'N/A' }}</td>
                                                <td>{{ $payment->plan->name ?? 'N/A' }}</td>
                                                <td class="text-capitalize small">{{ $payment->gateway }}</td>
                                                <td class="fw-bold">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</td>
                                                <td>
                                                    @php
                                                        $payStatus = $payment->status;
                                                        $payBadge = 'bg-secondary';
                                                        if ($payStatus === 'success') $payBadge = 'bg-success';
                                                        if ($payStatus === 'failed') $payBadge = 'bg-danger';
                                                        if ($payStatus === 'pending') $payBadge = 'bg-warning';
                                                    @endphp
                                                    <span class="badge {{ $payBadge }} bg-opacity-10 text-{{ str_replace('bg-', '', $payBadge) }} rounded text-capitalize">{{ $payStatus }}</span>
                                                </td>
                                                <td>{{ $payment->paid_at ? $payment->paid_at->format('d M, Y H:i') : '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">No payments found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Invoices Pane -->
                        <div class="tab-pane fade" id="invoices-pane" role="tabpanel" aria-labelledby="invoices-tab" tabindex="0">
                            <h6 class="fw-bold text-dark mb-3">Billing Invoices</h6>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle small">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Invoice No</th>
                                            <th>Subtotal</th>
                                            <th>Tax</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Due Date</th>
                                            <th>Paid Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($user->invoices as $invoice)
                                            <tr>
                                                <td class="fw-bold text-dark">
                                                    <a href="{{ route('subscriber.subscription.invoice', $invoice->id) }}" target="_blank" class="text-decoration-none">
                                                        <i class="fa-solid fa-file-invoice-dollar me-1"></i>{{ $invoice->invoice_number }}
                                                    </a>
                                                </td>
                                                <td>{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
                                                <td>{{ number_format($invoice->tax, 2) }} {{ $invoice->currency }}</td>
                                                <td class="fw-bold">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
                                                <td>
                                                    @php
                                                        $invStatus = $invoice->status;
                                                        $invBadge = 'bg-secondary';
                                                        if ($invStatus === 'paid') $invBadge = 'bg-success';
                                                        if ($invStatus === 'unpaid') $invBadge = 'bg-warning';
                                                        if ($invStatus === 'cancelled') $invBadge = 'bg-danger';
                                                    @endphp
                                                    <span class="badge {{ $invBadge }} bg-opacity-10 text-{{ str_replace('bg-', '', $invBadge) }} rounded text-capitalize">{{ $invStatus }}</span>
                                                </td>
                                                <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M, Y') : '-' }}</td>
                                                <td>{{ $invoice->paid_date ? \Carbon\Carbon::parse($invoice->paid_date)->format('d M, Y') : '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">No invoices found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
        </section>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3 font-outfit" style="font-size: 19px !important; font-weight: 800;">Products Catalogue</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap" id="subscriberProductsTable" style="width:100%;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">Product</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Category</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">SKU / Stock</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Price</th>
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
                                                     <a href="{{ route('product.details', $product->slug) }}" target="_blank" class="fw-bold text-dark text-decoration-none hover-primary">{{ $product->name }}</a>
                                                     <div class="small text-muted">SKU: {{ $product->sku ?? 'N/A' }}</div>
                                                 </div>
                                             </div>
                                         </td>
                                         <td>
                                             <div class="small fw-semibold text-dark">{{ $product->category->name ?? 'N/A' }}</div>
                                             <div class="smaller text-muted">{{ $product->subcategory->name ?? '' }}</div>
                                         </td>
                                         <td>
                                             <div class="small fw-semibold text-dark">{{ $product->sku ?? 'N/A' }}</div>
                                             <div class="smaller text-muted">{{ $product->stock ?? 0 }} pcs left</div>
                                         </td>
                                         <td>
                                             <div class="fw-bold text-primary">
                                                 @if($product->offer_price)
                                                     ₹{{ number_format($product->offer_price, 2) }}
                                                     @if($product->mrp && $product->mrp > $product->offer_price)
                                                         <div class="smaller text-muted text-decoration-line-through text-opacity-50">₹{{ number_format($product->mrp, 2) }}</div>
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
                                                 <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Active</span>
                                             @elseif($product->status == 'draft')
                                                 <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Draft</span>
                                             @else
                                                 <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Inactive</span>
                                             @endif
                                         </td>
                                         <td class="text-end pe-4">
                                             <div class="d-inline-flex gap-2 justify-content-end align-items-center">
                                                 <a href="{{ route('product.details', $product->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="View Public Page">
                                                     <i class="fa-solid fa-eye" style="font-size: 12px;"></i>
                                                 </a>
                                                 @can('delete-products')
                                                 <form action="{{ route('admin.subscriber-products.destroy', $product->id) }}" method="POST" class="d-inline form-delete">
                                                     @csrf
                                                     @method('DELETE')
                                                     <button type="button" class="btn btn-sm btn-outline-danger btn-delete-product rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Delete Subscriber Product">
                                                         <i class="fa-solid fa-trash-can" style="font-size: 12px;"></i>
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

<!-- Assign Plan Modal -->
<div class="modal fade" id="assignPlanModal" tabindex="-1" aria-labelledby="assignPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="assignPlanForm" method="POST" action="{{ route('admin.subscribers.assign-plan', $user->id) }}">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="assignPlanModalLabel">Assign Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">Select a plan and manual subscription period for <strong>{{ $user->name }}</strong>. This will cancel their current active subscription.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Subscription Plan</label>
                        <select name="plan_id" class="form-select bg-light" required>
                            <option value="">-- Choose Plan --</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->price }} INR / {{ $plan->duration_days }} days)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Custom Duration (Optional Days)</label>
                        <input type="number" name="duration" class="form-control bg-light" placeholder="e.g. 30" min="1" max="365">
                        <small class="text-muted">Leave blank to use the plan's default duration.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Assign Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="suspendForm" method="POST" action="{{ route('admin.subscribers.suspend', $user->id) }}">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger" id="suspendModalLabel">Suspend Subscriber Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">Are you sure you want to suspend the account of <strong>{{ $user->subscriberProfile->company_name ?? $user->name }}</strong>? This will block their login and access to the Subscriber Portal.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-danger">Suspension Reason</label>
                        <textarea name="reason" class="form-control bg-light" placeholder="Explain the reason for suspension..." rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Suspend Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        // Modal Trigger - Assign Plan
        $('.btn-assign-plan-modal').on('click', function() {
            $('#assignPlanModal').modal('show');
        });

        // Modal Trigger - Suspend
        $('.btn-suspend-modal').on('click', function() {
            $('#suspendModal').modal('show');
        });

        // Delete confirmation
        $('.btn-delete').on('click', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Subscriber Account?',
                text: "This is IRREVERSIBLE! All their products, sharing links, and subscription logs will be completely deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete Permanently',
                borderRadius: '15px'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        if ($('#subscriberProductsTable').length) {
            $('#subscriberProductsTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "responsive": true,
                "language": {
                    "search": "_INPUT_",
                    "searchPlaceholder": "Search products..."
                }
            });
            // Adjust columns on tab switch
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
            });
        }

        // Delete product confirmation
        $(document).on('click', '.btn-delete-product', function() {
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
            });
        });
    });
</script>
@endpush

<style>
    .subscriber-detail-page {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .subscriber-topbar {
        display: flex;
        align-items: center;
    }

    .subscriber-back-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: var(--text-muted);
        font-weight: 600;
        text-decoration: none;
        transition: color 0.18s ease;
    }

    .subscriber-back-link:hover {
        color: var(--primary-color);
    }

    .subscriber-hero,
    .subscriber-info-panel,
    .subscriber-tabs-panel,
    .subscriber-stat-card {
        background: var(--surface-color, #ffffff);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
    }

    .subscriber-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 24px;
        align-items: flex-start;
        padding: 24px;
        overflow: hidden;
        position: relative;
    }

    .subscriber-hero::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), #0ea5e9, #10b981);
    }

    .subscriber-hero-main {
        display: flex;
        align-items: center;
        gap: 22px;
        min-width: 0;
    }

    .subscriber-logo-wrap {
        flex: 0 0 auto;
    }

    .subscriber-logo,
    .subscriber-logo-fallback {
        width: 104px;
        height: 104px;
        border-radius: 18px;
        object-fit: cover;
        border: 1px solid rgba(14, 165, 233, 0.18);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.55);
    }

    .subscriber-logo-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0284c7;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.12), rgba(16, 185, 129, 0.08));
        font-family: 'Outfit', sans-serif;
        font-size: 34px;
        font-weight: 800;
    }

    .subscriber-identity {
        min-width: 0;
    }

    .subscriber-title-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .subscriber-title-row h1 {
        color: var(--text-primary);
        font-size: 28px !important;
        line-height: 1.1;
        font-weight: 800;
        margin: 0;
        letter-spacing: 0 !important;
    }

    .subscriber-contact-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 18px;
        color: var(--text-muted);
        max-width: 860px;
    }

    .subscriber-contact-grid span,
    .subscriber-contact-grid a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
        text-decoration: none;
        white-space: nowrap;
    }

    .subscriber-contact-grid i {
        color: #64748b;
        width: 14px;
    }

    .subscriber-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .subscriber-actions .btn {
        white-space: nowrap;
    }

    .subscriber-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
    }

    .subscriber-stat-card {
        min-height: 120px;
        padding: 22px;
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .subscriber-stat-card .stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex: 0 0 auto;
    }

    .stat-blue { background: rgba(37, 99, 235, 0.10); color: #2563eb; }
    .stat-green { background: rgba(5, 150, 105, 0.10); color: #059669; }
    .stat-red { background: rgba(220, 38, 38, 0.10); color: #dc2626; }
    .stat-amber { background: rgba(217, 119, 6, 0.12); color: #d97706; }

    .subscriber-stat-card div {
        min-width: 0;
    }

    .subscriber-stat-card .stat-label,
    .limit-card > span,
    .info-row > span {
        color: var(--text-muted);
        display: block;
        font-size: 11px !important;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .subscriber-stat-card strong {
        color: var(--text-primary);
        display: block;
        font-family: 'Outfit', sans-serif;
        font-size: 23px;
        font-weight: 800;
        line-height: 1.15;
        overflow-wrap: anywhere;
    }

    .subscriber-stat-card small {
        color: var(--text-muted);
        display: block;
        margin-top: 3px;
    }

    .subscriber-layout-grid {
        display: grid;
        grid-template-columns: minmax(320px, 0.9fr) minmax(0, 1.9fr);
        gap: 20px;
        align-items: stretch;
    }

    .subscriber-info-panel,
    .subscriber-tabs-panel {
        overflow: hidden;
    }

    .panel-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--border);
    }

    .panel-heading > span {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.10);
        color: #2563eb;
        flex: 0 0 auto;
    }

    .panel-heading h2,
    .tab-heading h2 {
        color: var(--text-primary);
        font-size: 17px !important;
        font-weight: 800;
        margin: 0;
        letter-spacing: 0 !important;
    }

    .panel-heading p,
    .tab-heading p {
        color: var(--text-muted);
        margin: 2px 0 0;
    }

    .info-list {
        padding: 8px 20px 20px;
    }

    .info-row {
        display: grid;
        grid-template-columns: 132px minmax(0, 1fr);
        gap: 14px;
        padding: 16px 0;
        border-bottom: 1px solid var(--border);
    }

    .info-row:last-child {
        border-bottom: 0;
    }

    .info-row strong {
        color: var(--text-primary);
        font-size: 13.5px;
        font-weight: 600;
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .brand-colors {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .brand-colors i {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid var(--surface-color);
        box-shadow: 0 0 0 1px var(--border);
    }

    .brand-colors small,
    .suspension-row small {
        color: var(--text-muted);
        display: block;
        font-weight: 500;
    }

    .suspension-row {
        background: rgba(239, 68, 68, 0.04);
        margin: 0 -12px;
        padding-left: 12px;
        padding-right: 12px;
        border-radius: 12px;
    }

    .suspension-row strong,
    .suspension-row span {
        color: #dc2626;
    }

    .subscriber-tabs-panel {
        padding: 20px;
    }

    .subscriber-tabs {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        background: var(--surface-muted, #f8fafc);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 8px;
        margin-bottom: 24px;
    }

    .subscriber-tabs .nav-item,
    .subscriber-tabs .nav-link {
        width: 100%;
    }

    .subscriber-tabs .nav-link {
        min-height: 42px;
        border-radius: 10px !important;
        color: var(--text-secondary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: 700;
        padding: 8px 10px;
        white-space: normal;
        text-align: center;
    }

    .subscriber-tabs .nav-link.active {
        background: #0f766e !important;
        color: #ffffff !important;
        box-shadow: 0 10px 20px rgba(15, 118, 110, 0.16);
    }

    .tab-heading {
        margin-bottom: 18px;
    }

    .limit-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .limit-card {
        min-height: 128px;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 18px;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.82), rgba(255, 255, 255, 0.94));
    }

    html[data-theme="dark"] .limit-card,
    html[data-theme="dark"] .subscriber-tabs {
        background: rgba(15, 23, 42, 0.72);
    }

    .limit-card div {
        color: var(--text-muted);
        margin-top: 8px;
    }

    .limit-card strong {
        color: var(--text-primary);
        font-family: 'Outfit', sans-serif;
        font-size: 30px;
        line-height: 1;
        font-weight: 800;
    }

    .limit-card small {
        float: right;
        margin-top: 9px;
    }

    .limit-card .progress {
        height: 7px;
        margin-top: 14px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }

    .limit-card .progress-bar {
        background: linear-gradient(90deg, var(--primary-color), #0ea5e9);
    }

    .access-card {
        min-height: 128px;
    }

    .access-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px !important;
    }

    .subscriber-tabs-panel .table-responsive {
        margin: 0 !important;
    }

    .subscriber-tabs-panel h6 {
        color: var(--text-primary) !important;
        font-size: 17px !important;
        margin-bottom: 16px !important;
    }

    .subscriber-tabs-panel .table td,
    .subscriber-tabs-panel .table th {
        white-space: nowrap;
    }

    @media (max-width: 1399.98px) {
        .subscriber-hero {
            grid-template-columns: 1fr;
        }

        .subscriber-actions {
            justify-content: flex-start;
        }

        .subscriber-layout-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991.98px) {
        .subscriber-stat-grid {
            grid-template-columns: 1fr;
        }

        .subscriber-tabs {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .subscriber-hero,
        .subscriber-info-panel,
        .subscriber-tabs-panel,
        .subscriber-stat-card {
            border-radius: 14px;
        }

        .subscriber-hero-main {
            align-items: flex-start;
            flex-direction: column;
        }

        .subscriber-logo,
        .subscriber-logo-fallback {
            width: 88px;
            height: 88px;
            border-radius: 16px;
        }

        .subscriber-title-row h1 {
            font-size: 23px !important;
        }

        .subscriber-actions,
        .subscriber-actions form,
        .subscriber-actions .btn {
            width: 100%;
        }

        .info-row {
            grid-template-columns: 1fr;
            gap: 6px;
        }

        .subscriber-tabs,
        .limit-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
