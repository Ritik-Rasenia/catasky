@extends('admin.layouts.app')

@section('title', 'Payment History —')

@push('css')
<style>
    /* Premium glassmorphic styles */
    .saas-header {
        background: linear-gradient(135deg, #1E1B4B 0%, #115E59 50%, #0F172A 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        padding: 32px 36px;
        color: white;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
    }
    .saas-header::before {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 250px; height: 250px;
        background: radial-gradient(circle, rgba(20, 184, 166, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .saas-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        transition: all 0.25s ease;
    }
    .saas-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.06);
    }
    .table-hover tbody tr:hover {
        background-color: rgba(99, 102, 241, 0.03);
    }
    .status-badge {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 100px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .status-success {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    .status-pending {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    .status-failed {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .stat-mini-card {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 14px;
        padding: 16px 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="saas-header">
        <div class="row align-items-center">
            <div class="col-lg-8" style="position: relative; z-index: 2;">
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255, 255, 255, 0.5); margin-bottom: 8px;">
                    Catasky SaaS Core
                </div>
                <h2 class="fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">Payment Management</h2>
                <p class="text-white-50 mb-0" style="max-width: 600px;">
                    Track subscription receipts, gateway transaction details, invoice logs, and dynamic platform revenue analytics.
                </p>
            </div>
            <div class="col-lg-4 mt-4 mt-lg-0" style="position: relative; z-index: 2;">
                <div class="stat-mini-card">
                    <div class="small fw-semibold text-uppercase text-white-50" style="font-size:0.7rem; letter-spacing:0.5px;">Gross B2B Revenue</div>
                    <div class="fw-extrabold text-white mt-1" style="font-size: 2.2rem; font-family:'Outfit',sans-serif;">
                        ₹{{ number_format($totalRevenue, 2) }}
                    </div>
                    <div class="small text-white-50 mt-1"><i class="bi bi-arrow-up-right text-success me-1"></i>Secure Gateway Settlement</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="saas-card mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.saas.payments.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-8 col-lg-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by transaction ID, gateway, or company name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3 d-grid">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill flex-grow-1 px-4"><i class="bi bi-funnel-fill me-2"></i>Filter</button>
                        @if(request()->filled('search'))
                            <a href="{{ route('admin.saas.payments.index') }}" class="btn btn-light rounded-pill"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Grid Content --}}
    <div class="saas-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-nowrap">
                    <thead>
                        <tr class="bg-light">
                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Transaction ID</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Subscriber Store</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Gateway</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Subscription Plan</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Amount</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Paid At</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        @php
                            $profile = $payment->user->subscriberProfile ?? null;
                            $status = strtolower($payment->status);
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark font-monospace" style="font-size:0.85rem;">{{ $payment->transaction_id }}</div>
                                <div class="text-muted small">ID: #{{ $payment->id }}</div>
                            </td>
                            <td>
                                @if($profile)
                                    <div class="fw-bold text-dark">{{ $profile->company_name }}</div>
                                    <div class="text-muted small">Email: {{ $payment->user->email }}</div>
                                @else
                                    <span class="text-muted small">No profile built</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 small rounded">
                                    @if(strtolower($payment->gateway) === 'razorpay')
                                        <i class="bi bi-wallet2 text-primary me-1"></i> Razorpay 
                                        @if(str_contains(strtolower($payment->notes ?? ''), 'sandbox') || str_contains(strtolower($payment->notes ?? ''), 'test') || str_starts_with($payment->transaction_id ?? '', 'pay_test') || (isset($payment->gateway_response['mode']) && $payment->gateway_response['mode'] === 'test'))
                                            (Sandbox)
                                        @else
                                            (Live)
                                        @endif
                                    @elseif(strtolower($payment->gateway) === 'stripe')
                                        <i class="bi bi-stripe text-info me-1"></i> Stripe
                                    @elseif(strtolower($payment->gateway) === 'paypal')
                                        <i class="bi bi-paypal text-warning me-1"></i> PayPal
                                    @else
                                        <i class="bi bi-credit-card-2-back me-1"></i> {{ $payment->gateway }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $payment->plan->name ?? 'No Plan' }}</span>
                            </td>
                            <td>
                                <span class="fw-extrabold text-dark" style="font-family:'Outfit',sans-serif; font-size:0.95rem;">
                                    ₹{{ number_format($payment->amount, 2) }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                @if($payment->paid_at)
                                    {{ \Carbon\Carbon::parse($payment->paid_at)->format('M d, Y H:i') }}
                                    <div style="font-size:0.7rem;">{{ \Carbon\Carbon::parse($payment->paid_at)->diffForHumans() }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge status-{{ $status }}">
                                    @if($status === 'success')
                                        <i class="bi bi-check-circle-fill"></i> Success
                                    @elseif($status === 'failed')
                                        <i class="bi bi-x-circle-fill"></i> Failed
                                    @else
                                        <i class="bi bi-clock-history"></i> Pending
                                    @endif
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-cash-stack text-muted" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                                No gateway transaction logs found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="d-flex justify-content-between align-items-center p-4 border-top">
                    <div class="text-muted small">
                        Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} transactions
                    </div>
                    <div>
                        {{ $payments->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
