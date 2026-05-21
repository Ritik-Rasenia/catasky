@extends('admin.layouts.app')

@section('title', 'Customer Enquiries')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="fw-bold text-dark mb-1">Customer Enquiries</h3>
            <p class="text-muted">Manage and respond to customer queries from the website.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="enquiryTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">Status</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Type</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Date</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Customer</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted">Inquiry About</th>
                                    <th class="text-end pe-4 border-0 text-uppercase small fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enquiries as $enquiry)
                                <tr class="{{ $enquiry->is_read ? '' : 'bg-primary bg-opacity-10' }}" style="transition: background 0.3s;">
                                    <td class="ps-4">
                                        @if($enquiry->is_read)
                                            <span class="badge bg-light text-muted border rounded-pill px-3">Read</span>
                                        @else
                                            <span class="badge bg-primary rounded-pill px-3 shadow-sm">New</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($enquiry->product_id)
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 small">Product</span>
                                        @elseif($enquiry->brand_id)
                                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 small">Brand</span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 small">Contact</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $enquiry->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $enquiry->name }}</div>
                                        <div class="small text-muted">{{ $enquiry->email }}</div>
                                    </td>
                                    <td>
                                        @if($enquiry->product)
                                            <div class="small fw-bold text-primary">{{ Str::limit($enquiry->product->name, 30) }}</div>
                                        @elseif($enquiry->brand)
                                            <div class="small fw-bold text-primary">{{ $enquiry->brand->name }}</div>
                                        @elseif($enquiry->subject)
                                            <div class="small fw-bold text-dark">{{ Str::limit($enquiry->subject, 30) }}</div>
                                        @else
                                            <span class="text-muted small italic">General Inquiry</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                            @can('view-enquiries')
                                            <a href="{{ route('admin.enquiries.show', $enquiry->id) }}" class="btn btn-white btn-sm px-3" title="View Enquiry">
                                                <i class="fa-solid fa-eye text-primary"></i>
                                            </a>
                                            @endcan
                                            
                                            @if(!$enquiry->is_read)
                                                @can('mark-enquiries-read')
                                                <form action="{{ route('admin.enquiries.mark-as-read', $enquiry->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-white btn-sm px-3" title="Mark as Read">
                                                        <i class="fa-solid fa-check text-success"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            @endif

                                            @can('delete-enquiries')
                                            <form action="{{ route('admin.enquiries.destroy', $enquiry->id) }}" method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-white btn-sm px-3 btn-delete" title="Delete Enquiry">
                                                    <i class="fa-solid fa-trash-can text-danger"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No enquiries found.</td>
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

@push('js')
<script>
    $(document).ready(function() {
        $('#enquiryTable').DataTable({
            "pageLength": 15,
            "ordering": true,
            "responsive": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Filter enquiries..."
            }
        });

        $(document).on('click', '.btn-delete', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Delete Enquiry?',
                text: "This message will be permanently removed from the records!",
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
            })
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
</style>
@endsection
