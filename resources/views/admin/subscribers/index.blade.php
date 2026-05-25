@extends('admin.layouts.app')

@section('title', 'Subscriber Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold text-dark mb-1">Subscriber Management</h3>
            <p class="text-muted">Monitor subscriber registrations, subscriptions, invoices, and suspend/reactivate subscriber accounts.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="fa-solid fa-tags me-2"></i>Subscription Plans
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4 g-3">
        <!-- Total Subscribers -->
        <div class="col-xl-3 col-sm-6">
            <div class="stats-card border-0 shadow-sm">
                <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fa-solid fa-users-viewfinder"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total Subscribers</h6>
                    <h3 class="fw-extrabold mb-0">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <!-- Active Subscribers -->
        <div class="col-xl-3 col-sm-6">
            <div class="stats-card border-0 shadow-sm">
                <div class="stats-icon bg-success bg-opacity-10 text-success">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small fw-bold text-uppercase">Active Profiles</h6>
                    <h3 class="fw-extrabold mb-0">{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>
        <!-- Suspended Subscribers -->
        <div class="col-xl-3 col-sm-6">
            <div class="stats-card border-0 shadow-sm">
                <div class="stats-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fa-solid fa-user-slash"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small fw-bold text-uppercase">Suspended</h6>
                    <h3 class="fw-extrabold mb-0">{{ $stats['suspended'] }}</h3>
                </div>
            </div>
        </div>
        <!-- Trial Subscribers -->
        <div class="col-xl-3 col-sm-6">
            <div class="stats-card border-0 shadow-sm">
                <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small fw-bold text-uppercase">Trial Period</h6>
                    <h3 class="fw-extrabold mb-0">{{ $stats['trial'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <!-- Filters Form -->
                    <form action="{{ route('admin.subscribers.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted text-uppercase">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Search by name, email or company..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                            <select name="status" class="form-select bg-light">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary px-4 py-2 me-2">
                                <i class="fa-solid fa-filter me-2"></i>Filter
                            </button>
                            @if(request()->anyFilled(['search', 'status']))
                            <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3">
                                <i class="fa-solid fa-rotate-left me-2"></i>Reset
                            </a>
                            @endif
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">Company & Subscriber</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Contacts</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Active Subscription</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Profile Status</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Joined Date</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscribers as $subscriber)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            @if($subscriber->subscriberProfile && $subscriber->subscriberProfile->logo)
                                                <img src="{{ asset('uploads/subscriber-products/' . $subscriber->subscriberProfile->logo) }}" alt="Logo" class="rounded-3 me-3" style="width: 48px; height: 48px; object-fit: cover; border: 1px solid #e2e8f0;">
                                            @else
                                                <div class="rounded-3 bg-indigo bg-opacity-10 text-indigo p-2 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; color: var(--primary-color);">
                                                    {{ strtoupper(substr($subscriber->subscriberProfile->company_name ?? $subscriber->name, 0, 2)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark mb-0">{{ $subscriber->subscriberProfile->company_name ?? 'No Company Registered' }}</div>
                                                <span class="small text-muted"><i class="fa-solid fa-user me-1"></i>{{ $subscriber->name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="text-dark"><i class="fa-regular fa-envelope me-1 text-muted"></i>{{ $subscriber->email }}</div>
                                            @if($subscriber->subscriberProfile && $subscriber->subscriberProfile->phone)
                                                <div class="text-muted mt-1"><i class="fa-solid fa-phone me-1 text-muted"></i>{{ $subscriber->subscriberProfile->phone }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($subscriber->subscription)
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill align-self-start px-3 py-1 mb-1">
                                                    {{ $subscriber->subscription->plan->name }}
                                                </span>
                                                <span class="small text-muted" style="font-size: 0.78rem;">
                                                    Ends: {{ \Carbon\Carbon::parse($subscriber->subscription->ends_at)->format('d M, Y') }}
                                                    @if($subscriber->subscription->status == 'trial')
                                                        <span class="badge bg-warning bg-opacity-10 text-warning px-1 rounded">Trial</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-1">No Active Plan</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $status = $subscriber->subscriberProfile->status ?? 'pending';
                                            $badgeClass = 'bg-secondary';
                                            if ($status === 'active') $badgeClass = 'bg-success';
                                            if ($status === 'suspended') $badgeClass = 'bg-danger';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} bg-opacity-10 text-{{ str_replace('bg-', '', $badgeClass) }} rounded-pill px-3 py-1 text-capitalize">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $subscriber->created_at->format('d M, Y') }}</td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                            <a href="{{ route('admin.subscribers.show', $subscriber->id) }}" class="btn btn-white btn-sm px-3" title="View Detail Profile">
                                                <i class="fa-solid fa-eye text-primary"></i>
                                            </a>
                                            <button type="button" class="btn btn-white btn-sm px-3 btn-assign-plan" data-id="{{ $subscriber->id }}" data-name="{{ $subscriber->name }}" title="Assign Plan">
                                                <i class="fa-solid fa-id-card text-success"></i>
                                            </button>
                                            @if($status === 'suspended')
                                                <form action="{{ route('admin.subscribers.unsuspend', $subscriber->id) }}" method="POST" class="d-inline form-unsuspend">
                                                    @csrf
                                                    <button type="button" class="btn btn-white btn-sm px-3 btn-action-unsuspend" title="Unsuspend/Reactivate Subscriber">
                                                        <i class="fa-solid fa-user-check text-success"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-white btn-sm px-3 btn-action-suspend" data-id="{{ $subscriber->id }}" data-name="{{ $subscriber->name }}" title="Suspend Subscriber">
                                                    <i class="fa-solid fa-user-slash text-danger"></i>
                                                </button>
                                            @endif
                                            <form action="{{ route('admin.subscribers.destroy', $subscriber->id) }}" method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-white btn-sm px-3 btn-delete" title="Delete Account Permanently">
                                                    <i class="fa-solid fa-trash-can text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="mb-3 text-muted opacity-50"><i class="fa-solid fa-folder-open fa-3x"></i></div>
                                        No subscribers found matching your criteria.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="admin-pagination-wrap mt-4 d-flex justify-content-center">
                        {{ $subscribers->links() }}
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
            <form id="assignPlanForm" method="POST" action="">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="assignPlanModalLabel">Assign Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">Select a plan and manual subscription period for <strong id="assign-subscriber-name"></strong>. This will cancel their current active subscription.</p>
                    
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
            <form id="suspendForm" method="POST" action="">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger" id="suspendModalLabel">Suspend Subscriber Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">Are you sure you want to suspend the account of <strong id="suspend-subscriber-name"></strong>? This will block their login and access to the Subscriber Portal.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-danger">Suspension Reason</label>
                        <textarea name="reason" class="form-control bg-light" placeholder="Explain the reason for suspension (e.g. policy violations, expired trial)..." rows="4" required></textarea>
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
        $('.btn-assign-plan').on('click', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            $('#assign-subscriber-name').text(name);
            $('#assignPlanForm').attr('action', window.baseUrl + '/admin/subscribers/' + id + '/assign-plan');
            $('#assignPlanModal').modal('show');
        });

        // Modal Trigger - Suspend
        $('.btn-action-suspend').on('click', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            $('#suspend-subscriber-name').text(name);
            $('#suspendForm').attr('action', window.baseUrl + '/admin/subscribers/' + id + '/suspend');
            $('#suspendModal').modal('show');
        });

        // Unsuspend confirmation
        $('.btn-action-unsuspend').on('click', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Reactivate Subscriber Account?',
                text: "This will restore their access to the subscriber dashboard.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Reactivate',
                borderRadius: '15px'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
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
    .btn-white {
        background: #fff;
        border: 1px solid #e2e8f0;
    }
    .btn-white:hover {
        background: #f8fafc;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(79, 70, 229, 0.02);
    }
    .stats-card {
        border-radius: var(--radius-lg);
        background: #fff;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .stats-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .fw-extrabold {
        font-weight: 800;
    }
    .bg-indigo {
        background-color: rgba(99, 102, 241, 0.1);
    }
    .text-indigo {
        color: #6366F1;
    }
</style>
@endsection
