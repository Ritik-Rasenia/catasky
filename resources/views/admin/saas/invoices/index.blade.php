@extends('admin.layouts.app')

@section('title', 'Generated B2B Invoices —')

@push('css')
<style>
    /* Premium glassmorphic styles */
    .saas-header {
        background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 50%, #1E293B 100%);
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
        background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
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
    .status-paid {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    .status-unpaid {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="saas-header">
        <div style="position: relative; z-index: 2;">
            <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255, 255, 255, 0.5); margin-bottom: 8px;">
                Catasky SaaS Core
            </div>
            <h2 class="fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">Invoice Management</h2>
            <p class="text-white-50 mb-0" style="max-width: 600px;">
                View generated system invoices, track payments status, and export official billing documentation for subscriber records.
            </p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="saas-card mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.saas.invoices.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-8 col-lg-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by invoice number or company name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3 d-grid">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill flex-grow-1 px-4"><i class="bi bi-funnel-fill me-2"></i>Filter</button>
                        @if(request()->filled('search'))
                            <a href="{{ route('admin.saas.invoices.index') }}" class="btn btn-light rounded-pill"><i class="bi bi-x-lg"></i></a>
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
                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Invoice Number</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Subscriber Store</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Billing Plan</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Amount</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Invoice Date</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Status</th>
                            <th class="text-end pe-4 py-3 text-uppercase small fw-bold text-muted border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        @php
                            $profile = $invoice->user->subscriberProfile ?? null;
                            $status = strtolower($invoice->status);
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark font-monospace" style="font-size:0.85rem;">{{ $invoice->invoice_number }}</div>
                                <div class="text-muted small">ID: #{{ $invoice->id }}</div>
                            </td>
                            <td>
                                @if($profile)
                                    <div class="fw-bold text-dark">{{ $profile->company_name }}</div>
                                    <div class="text-muted small">Owner: {{ $invoice->user->name }}</div>
                                @else
                                    <span class="text-muted small">No profile built</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $invoice->subscription->plan->name ?? 'No Plan' }}</span>
                            </td>
                            <td>
                                <span class="fw-extrabold text-dark" style="font-family:'Outfit',sans-serif; font-size:0.95rem;">
                                    ₹{{ number_format($invoice->total, 2) }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $invoice->created_at->format('M d, Y') }}
                                <div style="font-size:0.7rem;">{{ $invoice->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $status }}">
                                    @if($status === 'paid')
                                        <i class="bi bi-check-circle-fill"></i> Paid
                                    @else
                                        <i class="bi bi-exclamation-triangle-fill"></i> Unpaid
                                    @endif
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.saas.invoices.download', $invoice->id) }}" target="_blank" class="btn-ap-action" title="Download PDF Invoice">
                                    <i class="fa-solid fa-file-pdf text-danger" style="font-size: 14px !important;"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt text-muted" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                                No generated billing invoices found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="d-flex justify-content-between align-items-center p-4 border-top">
                    <div class="text-muted small">
                        Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
                    </div>
                    <div>
                        {{ $invoices->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
