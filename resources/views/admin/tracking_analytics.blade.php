@extends('admin.layouts.app')

@section('title', 'WhatsApp Share Tracking Analytics - Catasky')

@section('content')
<div class="container-fluid">
        <!-- Top Title & Navigation Bar -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-md-items-center mb-5 gap-3 border-bottom pb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">DoubleTick.io Live Tracker</span>
                <h1 class="h2 fw-bold text-gradient mb-1">WhatsApp Engagement Analytics</h1>
                <p class="text-secondary small mb-0">Track B2B flyer opens, deliverability states, repeat customer visits, and real-time viewing sessions.</p>
            </div>
            <div>
                <a href="{{ route('catalogue') }}" class="btn btn-premium btn-premium-outline py-2.5 px-4 rounded-pill">
                    <i class="bi bi-arrow-left"></i> Back to Catalogue
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row g-4 mb-5">
            <!-- 1. Total Dispatches -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="premium-card p-4 bg-white border rounded-4  text-center">
                    <div class="d-inline-flex p-3 bg-primary-subtle rounded-pill text-primary mb-3">
                        <i class="bi bi-send-fill fs-4"></i>
                    </div>
                    <h6 class="text-secondary fw-semibold mb-1">Total Shares</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalShares }}</h3>
                </div>
            </div>

            <!-- 2. Delivery Rate -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="premium-card p-4 bg-white border rounded-4  text-center">
                    <div class="d-inline-flex p-3 bg-success-subtle rounded-pill text-success mb-3">
                        <i class="bi bi-check2-all fs-4"></i>
                    </div>
                    <h6 class="text-secondary fw-semibold mb-1">Delivery Success Rate</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $deliveryRate }}%</h3>
                </div>
            </div>

            <!-- 3. Message Seen Rate -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="premium-card p-4 bg-white border rounded-4  text-center">
                    <div class="d-inline-flex p-3 bg-info-subtle rounded-pill text-info mb-3">
                        <i class="bi bi-eye-fill fs-4"></i>
                    </div>
                    <h6 class="text-secondary fw-semibold mb-1">Read/Seen Rate</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $seenRate }}%</h3>
                </div>
            </div>

            <!-- 4. Click CTR -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="premium-card p-4 bg-white border rounded-4  text-center">
                    <div class="d-inline-flex p-3 bg-warning-subtle rounded-pill text-warning mb-3">
                        <i class="bi bi-cursor-fill fs-4"></i>
                    </div>
                    <h6 class="text-secondary fw-semibold mb-1">Catalogue Click CTR</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $clickRate }}%</h3>
                </div>
            </div>
        </div>

        <!-- Shares Detailed Log List -->
        <div class="premium-card p-4 bg-white border rounded-4 ">
            <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-table text-primary"></i> Shared Catalogue Dispatch Registry</h5>
            
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase fw-bold text-secondary">
                            <th class="py-3 px-3">Catalogue Code</th>
                            <th class="py-3">Recipient Phone</th>
                            <th class="py-3 text-center">Delivery Status</th>
                            <th class="py-3 text-center">Read Status</th>
                            <th class="py-3 text-center">Clicked</th>
                            <th class="py-3 text-center">Views Count</th>
                            <th class="py-3 text-center">Avg Session duration</th>
                            <th class="py-3 text-end px-3">Shared Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shares as $share)
                            <tr>
                                <td class="px-3">
                                    <a href="{{ route('doubletick.view', $share->catalogue_code) }}" target="_blank" class="fw-bold text-primary">
                                        {{ $share->catalogue_code }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-dark fw-semibold">{{ $share->customer_phone }}</span>
                                </td>
                                <td class="text-center">
                                    @if($share->delivery_status === 'delivered')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Delivered</span>
                                    @elseif($share->delivery_status === 'sent')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">Sent</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1">Pending</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($share->seen_status === 'read')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1"><i class="bi bi-eye"></i> Seen</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">Unread</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($share->clicked_status === 'yes')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1"><i class="bi bi-check-lg"></i> Clicked</span>
                                    @else
                                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">No</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold text-dark">
                                    {{ $share->visit_count }}
                                </td>
                                <td class="text-center">
                                    <span class="text-dark fw-semibold">
                                        @if($share->total_view_time >= 60)
                                            {{ floor($share->total_view_time / 60) }}m {{ $share->total_view_time % 60 }}s
                                        @else
                                            {{ $share->total_view_time }}s
                                        @endif
                                    </span>
                                </td>
                                <td class="text-end px-3 text-secondary small">
                                    {{ $share->created_at->format('d-M-Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-secondary">
                                    <i class="bi bi-send-x display-2 opacity-25 d-block mb-3"></i>
                                    <h5 class="fw-bold text-dark">No Catalogue Dispatches Tracked Yet</h5>
                                    <p class="small">Share your first catalogue link on WhatsApp to see live metrics.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Grid -->
            <div class="mt-4 admin-pagination-wrap">
                {{ $shares->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
</div>
@endsection
