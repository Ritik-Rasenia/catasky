@extends('admin.layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-info text-white shadow-lg overflow-hidden" style="border-radius: 20px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h2 class="fw-bold mb-2">Staff Workspace 👋</h2>
                            <p class="lead opacity-75 mb-0">Managing the day-to-day operations. You have {{ $enquiriesCount }} enquiries to review.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 border-start border-4 border-primary">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Products</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $productsCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 border-start border-4 border-success">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Categories</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $categoriesCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 border-start border-4 border-warning">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Enquiries</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $enquiriesCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 border-start border-4 border-info">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Brands</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $brandsCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Enquiries -->
        <div class="col-xl-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Recent Enquiries</h5>
                    <a href="{{ route('admin.enquiries.index') }}" class="btn btn-sm btn-light rounded-pill px-3">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0">Name</th>
                                    <th class="border-0">Date</th>
                                    <th class="text-end pe-4 border-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentEnquiries as $e)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $e->name }}</td>
                                    <td class="small text-muted">{{ $e->created_at->format('M d, Y') }}</td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.enquiries.show', $e->id) }}" class="btn btn-icon btn-sm btn-light rounded-circle"><i class="fa-solid fa-eye"></i></a>
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
@endsection
