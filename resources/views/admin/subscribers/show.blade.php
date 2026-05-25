@extends('admin.layouts.app')

@section('title', 'Subscriber Details - ' . ($user->subscriberProfile->company_name ?? $user->name))

@section('content')
<div class="container-fluid">
    <!-- Back & Header Button -->
    <div class="mb-4">
        <a href="{{ route('admin.subscribers.index') }}" class="text-decoration-none text-muted small">
            <i class="fa-solid fa-arrow-left-long me-2"></i>Back to Subscriber List
        </a>
    </div>

    <!-- Subscriber Main Identity Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-auto text-center mb-3 mb-md-0">
                    @if($user->subscriberProfile && $user->subscriberProfile->logo)
                        <img src="{{ asset('uploads/subscriber-products/' . $user->subscriberProfile->logo) }}" alt="Logo" class="rounded-4 img-thumbnail" style="width: 110px; height: 110px; object-fit: cover;">
                    @else
                        <div class="rounded-4 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 110px; height: 110px; font-size: 2.2rem; border: 1px solid rgba(79,70,229,0.15)">
                            {{ strtoupper(substr($user->subscriberProfile->company_name ?? $user->name, 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div class="col-md mb-3 mb-md-0 text-center text-md-start">
                    <div class="d-flex flex-column flex-md-row align-items-center gap-2 mb-2">
                        <h4 class="fw-bold text-dark mb-0">{{ $user->subscriberProfile->company_name ?? 'No Company Name' }}</h4>
                        @php
                            $status = $user->subscriberProfile->status ?? 'pending';
                            $badgeClass = 'bg-secondary';
                            if ($status === 'active') $badgeClass = 'bg-success';
                            if ($status === 'suspended') $badgeClass = 'bg-danger';
                        @endphp
                        <span class="badge {{ $badgeClass }} bg-opacity-10 text-{{ str_replace('bg-', '', $badgeClass) }} rounded-pill px-3 py-1 text-capitalize">
                            {{ $status }}
                        </span>
                        @if($user->subscriberProfile->is_verified)
                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Verified</span>
                        @endif
                    </div>
                    <p class="text-muted mb-2"><i class="fa-solid fa-user-tie me-2 text-muted"></i>Representative: <strong>{{ $user->name }}</strong></p>
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-3 text-muted small">
                        <span><i class="fa-regular fa-envelope me-1"></i>{{ $user->email }}</span>
                        @if($user->subscriberProfile->phone)
                            <span><i class="fa-solid fa-phone me-1"></i>{{ $user->subscriberProfile->phone }}</span>
                        @endif
                        @if($user->subscriberProfile->website)
                            <span><i class="fa-solid fa-globe me-1"></i><a href="{{ $user->subscriberProfile->website }}" target="_blank" class="text-decoration-none text-muted">{{ $user->subscriberProfile->website }}</a></span>
                        @endif
                    </div>
                </div>
                <div class="col-md-auto text-center text-md-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-success rounded-pill px-3 py-2 btn-assign-plan-modal">
                            <i class="fa-solid fa-id-card me-1"></i>Assign Plan
                        </button>
                        @if($status === 'suspended')
                            <form action="{{ route('admin.subscribers.unsuspend', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary rounded-pill px-3 py-2">
                                    <i class="fa-solid fa-user-check me-1"></i>Activate Profile
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-outline-danger rounded-pill px-3 py-2 btn-suspend-modal">
                                <i class="fa-solid fa-user-slash me-1"></i>Suspend Account
                            </button>
                        @endif
                        <form action="{{ route('admin.subscribers.destroy', $user->id) }}" method="POST" class="d-inline form-delete">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger rounded-pill px-3 py-2 btn-delete">
                                <i class="fa-solid fa-trash-can me-1"></i>Delete Subscriber
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats row -->
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="p-3 rounded-4 bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-box-open fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold mb-1 text-uppercase">Total Products</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ $productCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    @php $activeSub = $user->activeSubscription(); @endphp
                    @if($activeSub)
                        <div class="p-3 rounded-4 bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-file-contract fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small fw-bold mb-1 text-uppercase">Current Plan</h6>
                            <h5 class="fw-bold mb-0 text-success">{{ $activeSub->plan->name }}</h5>
                            <span class="small text-muted">{{ $activeSub->daysRemaining() }} days left</span>
                        </div>
                    @else
                        <div class="p-3 rounded-4 bg-danger bg-opacity-10 text-danger">
                            <i class="fa-solid fa-file-circle-xmark fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small fw-bold mb-1 text-uppercase">Current Plan</h6>
                            <h5 class="fw-bold mb-0 text-danger">No Active Plan</h5>
                            <span class="small text-muted">Expired/Trial Over</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    @php $revenue = $user->payments()->where('status', 'success')->sum('amount'); @endphp
                    <div class="p-3 rounded-4 bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-wallet fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold mb-1 text-uppercase">Total Payments</h6>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($revenue, 2) }} INR</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Details Row -->
    <div class="row">
        <!-- Profile Column (Left) -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4 h-100">
                <div class="card-header border-0 pb-0">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-circle-info me-2 text-primary"></i>Company Information</h5>
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless align-middle small mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-semibold text-muted text-uppercase" style="width: 120px; font-size: 0.72rem;">GST Number</td>
                                <td class="text-dark fw-medium">{{ $user->subscriberProfile->gst_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Email (Inquiries)</td>
                                <td class="text-dark">{{ $user->subscriberProfile->email_for_inquiries ?? $user->email }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">WhatsApp No</td>
                                <td class="text-dark">{{ $user->subscriberProfile->whatsapp_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Bio / Note</td>
                                <td class="text-dark">{{ $user->subscriberProfile->bio ?? 'No company bio provided.' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Address</td>
                                <td class="text-dark">
                                    {{ $user->subscriberProfile->address ?? '' }}<br>
                                    {{ $user->subscriberProfile->city ?? '' }}{{ $user->subscriberProfile->state ? ', ' . $user->subscriberProfile->state : '' }}<br>
                                    {{ $user->subscriberProfile->country ?? 'India' }} - {{ $user->subscriberProfile->pincode ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Brand Colors</td>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="rounded-circle border" style="width: 20px; height: 20px; background-color: {{ $user->subscriberProfile->primary_color ?? '#4F46E5' }}" title="Primary: {{ $user->subscriberProfile->primary_color ?? '#4F46E5' }}"></span>
                                        <span class="rounded-circle border" style="width: 20px; height: 20px; background-color: {{ $user->subscriberProfile->secondary_color ?? '#7C3AED' }}" title="Secondary: {{ $user->subscriberProfile->secondary_color ?? '#7C3AED' }}"></span>
                                        <span class="text-muted small">Primary & Secondary</span>
                                    </div>
                                </td>
                            </tr>
                            @if($user->subscriberProfile->suspended_at)
                                <tr class="bg-danger-soft rounded">
                                    <td class="fw-semibold text-danger text-uppercase" style="font-size: 0.72rem;">Suspension Reason</td>
                                    <td class="text-danger fw-bold">
                                        {{ $user->subscriberProfile->suspension_reason ?? 'No reason stated.' }}
                                        <div class="small text-muted mt-1 fw-normal">Suspended on: {{ \Carbon\Carbon::parse($user->subscriberProfile->suspended_at)->format('d M, Y H:i') }}</div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Details Column (Right) -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-pills nav-justified mb-4 bg-light p-1 rounded-3 small" id="subscriberTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-3 py-2 fw-semibold" id="limits-tab" data-bs-toggle="tab" data-bs-target="#limits-pane" type="button" role="tab" aria-controls="limits-pane" aria-selected="true">
                                <i class="fa-solid fa-sliders me-1"></i>Plan Limits
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-3 py-2 fw-semibold" id="subscriptions-tab" data-bs-toggle="tab" data-bs-target="#subscriptions-pane" type="button" role="tab" aria-controls="subscriptions-pane" aria-selected="false">
                                <i class="fa-solid fa-list-check me-1"></i>Subscription History
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-3 py-2 fw-semibold" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments-pane" type="button" role="tab" aria-controls="payments-pane" aria-selected="false">
                                <i class="fa-solid fa-indian-rupee-sign me-1"></i>Payments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-3 py-2 fw-semibold" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices-pane" type="button" role="tab" aria-controls="invoices-pane" aria-selected="false">
                                <i class="fa-solid fa-receipt me-1"></i>Invoices
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Panes -->
                    <div class="tab-content" id="subscriberTabsContent">
                        <!-- Limits Pane -->
                        <div class="tab-pane fade show active" id="limits-pane" role="tabpanel" aria-labelledby="limits-tab" tabindex="0">
                            @if($activeSub)
                                <h6 class="fw-bold text-dark mb-3">Plan Details & Active Limits</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                                            <div class="small fw-semibold text-muted text-uppercase mb-1">Product Upload Limit</div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="h4 fw-bold mb-0">{{ $productCount }} / {{ $activeSub->plan->product_limit }}</div>
                                                <div class="small text-muted">Products</div>
                                            </div>
                                            @php $prog = $activeSub->plan->product_limit > 0 ? min(100, ($productCount / $activeSub->plan->product_limit) * 100) : 0; @endphp
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $prog }}%" aria-valuenow="{{ $prog }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                                            <div class="small fw-semibold text-muted text-uppercase mb-1">Attribute Limit</div>
                                            @php $attrCount = $user->attributes()->count(); @endphp
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="h4 fw-bold mb-0">{{ $attrCount }} / {{ $activeSub->plan->attribute_limit }}</div>
                                                <div class="small text-muted">Attributes</div>
                                            </div>
                                            @php $prog = $activeSub->plan->attribute_limit > 0 ? min(100, ($attrCount / $activeSub->plan->attribute_limit) * 100) : 0; @endphp
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $prog }}%" aria-valuenow="{{ $prog }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                                            <div class="small fw-semibold text-muted text-uppercase mb-1">Share Links Limit</div>
                                            @php $sharesCount = $user->shareLinks()->count(); @endphp
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="h4 fw-bold mb-0">{{ $sharesCount }} / {{ $activeSub->plan->share_link_limit }}</div>
                                                <div class="small text-muted">Links</div>
                                            </div>
                                            @php $prog = $activeSub->plan->share_link_limit > 0 ? min(100, ($sharesCount / $activeSub->plan->share_link_limit) * 100) : 0; @endphp
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $prog }}%" aria-valuenow="{{ $prog }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100">
                                            <div class="small fw-semibold text-muted text-uppercase mb-1">System Access Rights</div>
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                @if($activeSub->plan->pdf_sharing) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fa-solid fa-file-pdf me-1"></i>PDF Share</span> @endif
                                                @if($activeSub->plan->image_sharing) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fa-solid fa-images me-1"></i>Image Gallery</span> @endif
                                                @if($activeSub->plan->watermark_removal) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fa-solid fa-scissors me-1"></i>No Watermark</span> @endif
                                                @if($activeSub->plan->custom_branding) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fa-solid fa-palette me-1"></i>Custom Brand</span> @endif
                                                @if($activeSub->plan->analytics) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fa-solid fa-chart-line me-1"></i>Analytics</span> @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-triangle-exclamation text-danger fa-2x mb-2"></i>
                                    <div>No active subscription plan found. The subscriber has no portal access rights.</div>
                                    <button class="btn btn-primary rounded-pill mt-3 btn-assign-plan-modal"><i class="fa-solid fa-id-card me-2"></i>Assign Sub Plan Now</button>
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
    });
</script>
@endpush

<style>
    .nav-pills .nav-link {
        color: #475569;
    }
    .nav-pills .nav-link.active {
        background-color: var(--primary-color);
        color: #fff;
    }
    .progress-bar {
        background-color: var(--primary-color);
    }
</style>
@endsection
