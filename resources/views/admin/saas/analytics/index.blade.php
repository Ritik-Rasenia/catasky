@extends('admin.layouts.app')

@section('title', 'SaaS Platform Analytics —')

@push('css')
<style>
    /* Premium glassmorphic styles */
    .saas-header {
        background: linear-gradient(135deg, #1E1B4B 0%, #311042 50%, #4C1D95 100%);
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
        background: radial-gradient(circle, rgba(139, 92, 246, 0.2) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    
    /* Stat Cards */
    .stat-card {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.02);
        transition: all 0.25s ease;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.05);
        border-color: #CBD5E1;
    }
    .stat-icon-wrap {
        width: 50px; height: 50px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; margin-bottom: 16px;
    }
    .stat-label { 
        font-size: 0.75rem; 
        font-weight: 700; 
        color: #94A3B8; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        margin-bottom: 4px; 
    }
    .stat-value { 
        font-size: 1.8rem; 
        font-weight: 800; 
        color: #1E293B; 
        line-height: 1; 
        font-family: 'Outfit', sans-serif; 
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
        height: 100%;
    }
    .saas-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .saas-card-header h5 {
        font-size: 0.95rem;
        font-weight: 800;
        color: #1E293B;
        margin: 0;
    }

    /* Distribution Chart bars */
    .distribution-bar-wrap {
        background: #F1F5F9;
        height: 8px;
        border-radius: 100px;
        overflow: hidden;
        margin-top: 8px;
    }
    .distribution-bar {
        height: 100%;
        border-radius: 100px;
    }
    
    .plan-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #F8FAFC;
    }
    .plan-info-row:last-child {
        border-bottom: none;
    }

    .payment-feed-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 24px;
        border-bottom: 1px solid #F8FAFC;
        transition: background 0.15s ease;
    }
    .payment-feed-item:last-child {
        border-bottom: none;
    }
    .payment-feed-item:hover {
        background: #F8FAFC;
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
            <h2 class="fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">Subscription Analytics</h2>
            <p class="text-white-50 mb-0" style="max-width: 600px;">
                Platform health telemetry. Check aggregate registration velocity, active paid user metrics, subscription distributions, and payment settlement flows.
            </p>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background: rgba(99, 102, 241, 0.08);">
                    <i class="bi bi-people-fill" style="color: #6366F1;"></i>
                </div>
                <div class="stat-label">Total B2B Subscribers</div>
                <div class="stat-value">{{ number_format($totalSubscribers) }}</div>
                <div class="text-muted small mt-2" style="font-size:0.75rem;">Registered Subscribers</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background: rgba(16, 185, 129, 0.08);">
                    <i class="bi bi-check-circle-fill" style="color: #10B981;"></i>
                </div>
                <div class="stat-label">Active Paid Plans</div>
                <div class="stat-value">{{ number_format($activeSubscriptions) }}</div>
                <div class="text-muted small mt-2" style="font-size:0.75rem;">Current active tenants</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background: rgba(245, 158, 11, 0.08);">
                    <i class="bi bi-cash-stack" style="color: #F59E0B;"></i>
                </div>
                <div class="stat-label">Gross Platform Rev</div>
                <div class="stat-value">₹{{ number_format($totalRevenue, 2) }}</div>
                <div class="text-muted small mt-2" style="font-size:0.75rem;">Total secure settlements</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background: rgba(20, 184, 166, 0.08);">
                    <i class="bi bi-graph-up-arrow" style="color: #20B8A6;"></i>
                </div>
                <div class="stat-label">Last 30 Days Revenue</div>
                <div class="stat-value">₹{{ number_format($monthlyRevenue, 2) }}</div>
                <div class="text-muted small mt-2" style="font-size:0.75rem;">Settlements this month</div>
            </div>
        </div>
    </div>

    {{-- Detailed analytics grid --}}
    <div class="row g-4 mb-4">
        
        {{-- Plan Distribution --}}
        <div class="col-lg-5">
            <div class="saas-card">
                <div class="saas-card-header">
                    <h5><i class="bi bi-pie-chart-fill text-primary me-2"></i>Plan Tier Distribution</h5>
                    <span class="badge bg-light text-dark border">Live</span>
                </div>
                <div class="card-body p-4">
                    @php
                        $totalActive = $starterCount + $businessCount + $enterpriseCount ?: 1;
                        $starterPct = round(($starterCount / $totalActive) * 100);
                        $businessPct = round(($businessCount / $totalActive) * 100);
                        $enterprisePct = round(($enterpriseCount / $totalActive) * 100);
                    @endphp

                    {{-- Starter --}}
                    <div class="plan-info-row flex-column align-items-stretch">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small"><i class="bi bi-circle-fill text-indigo me-2" style="color: #6366F1;"></i>Starter Plan</span>
                            <span class="fw-extrabold text-indigo" style="color: #6366F1;">{{ $starterCount }} active ({{ $starterPct }}%)</span>
                        </div>
                        <div class="distribution-bar-wrap">
                            <div class="distribution-bar" style="width: {{ $starterPct }}%; background: #6366F1;"></div>
                        </div>
                    </div>

                    {{-- Business --}}
                    <div class="plan-info-row flex-column align-items-stretch">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small"><i class="bi bi-circle-fill text-pink me-2" style="color: #EC4899;"></i>Business Plan</span>
                            <span class="fw-extrabold text-pink" style="color: #EC4899;">{{ $businessCount }} active ({{ $businessPct }}%)</span>
                        </div>
                        <div class="distribution-bar-wrap">
                            <div class="distribution-bar" style="width: {{ $businessPct }}%; background: #EC4899;"></div>
                        </div>
                    </div>

                    {{-- Enterprise --}}
                    <div class="plan-info-row flex-column align-items-stretch">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small"><i class="bi bi-circle-fill text-amber me-2" style="color: #F59E0B;"></i>Enterprise Plan</span>
                            <span class="fw-extrabold text-amber" style="color: #F59E0B;">{{ $enterpriseCount }} active ({{ $enterprisePct }}%)</span>
                        </div>
                        <div class="distribution-bar-wrap">
                            <div class="distribution-bar" style="width: {{ $enterprisePct }}%; background: #F59E0B;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Payments Feed --}}
        <div class="col-lg-7">
            <div class="saas-card">
                <div class="saas-card-header">
                    <h5><i class="bi bi-activity text-success me-2"></i>Recent System Activity</h5>
                    <a href="{{ route('admin.saas.payments.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">View Payments</a>
                </div>
                <div class="card-body p-0">
                    @forelse($recentPayments as $payment)
                        @php
                            $profile = $payment->user->subscriberProfile ?? null;
                            $status = strtolower($payment->status);
                        @endphp
                        <div class="payment-feed-item">
                            <div>
                                <div class="fw-bold text-dark small">{{ $profile->company_name ?? 'Active Subscriber' }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    ID: {{ $payment->transaction_id }} · Plan: {{ $payment->plan->name ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-extrabold text-dark" style="font-family:'Outfit',sans-serif; font-size:0.92rem;">
                                    ₹{{ number_format($payment->amount, 2) }}
                                </div>
                                <div class="text-muted" style="font-size:0.7rem;">
                                    @if($payment->paid_at)
                                        {{ \Carbon\Carbon::parse($payment->paid_at)->diffForHumans() }}
                                    @else
                                        Pending
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-activity text-muted" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                            No recent transaction settlement feeds available.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
