@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.enquiries.index') }}">Enquiries</a></li>
                    <li class="breadcrumb-item active">View Details</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">Enquiry Detail</h1>
        </div>
        <a href="{{ route('admin.enquiries.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Message from {{ $enquiry->name }}</h5>
                </div>
                <div class="card-body p-4">
                    <div class="bg-light p-4 rounded mb-4" style="font-size: 1.05rem; line-height: 1.7;">
                        {{ $enquiry->message }}
                    </div>

                    <div class="d-flex gap-2">
                        @if(!$enquiry->is_read)
                        <form action="{{ route('admin.enquiries.mark-as-read', $enquiry->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">Mark as Read</button>
                        </form>
                        @endif
                        <a href="mailto:{{ $enquiry->email }}?subject=Reply to your enquiry - {{ config('app.name') }}" class="btn btn-outline-primary">
                            <i class="fa-solid fa-reply me-1"></i> Reply via Email
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Sender Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Name</label>
                        <div class="fw-bold">{{ $enquiry->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Email</label>
                        <div class="fw-bold text-primary">{{ $enquiry->email }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Phone</label>
                        <div class="fw-bold">{{ $enquiry->phone ?? 'Not provided' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Date Received</label>
                        <div class="fw-bold">{{ $enquiry->created_at->format('d M, Y h:i A') }}</div>
                    </div>
                    @if($enquiry->subject)
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Subject</label>
                        <div class="fw-bold">{{ $enquiry->subject }}</div>
                    </div>
                    @endif
                </div>
            </div>

            @if($enquiry->product)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Inquired Product</h5>
                </div>
                <div class="card-body p-4 text-center">
                    @if($enquiry->product->thumbnail)
                    <img src="{{ asset('uploads/products/' . $enquiry->product->thumbnail) }}" alt="Product" class="img-fluid rounded mb-3" style="max-height: 150px;">
                    @endif
                    <h6 class="fw-bold mb-2">{{ $enquiry->product->name }}</h6>
                    <a href="{{ route('product.details', $enquiry->product->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                        View on Website
                    </a>
                </div>
            </div>
            @elseif($enquiry->brand)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Inquired Brand</h5>
                </div>
                <div class="card-body p-4 text-center">
                    @if($enquiry->brand->image)
                    <img src="{{ asset('uploads/brands/' . $enquiry->brand->image) }}" alt="Brand" class="img-fluid rounded mb-3" style="max-height: 100px;">
                    @endif
                    <h6 class="fw-bold mb-2">{{ $enquiry->brand->name }}</h6>
                    <a href="{{ route('brand.details', $enquiry->brand->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                        View on Website
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
