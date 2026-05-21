@extends('admin.layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">Newsletter Subscribers</h3>
            <p class="text-muted mb-0">
                Manage all newsletter subscribers
            </p>
        </div>

    </div>

    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table align-middle" id="newsletterTable">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Email</th>
                            <th>Subscribed At</th>
                            <th>Status</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($newsletters as $item)

                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $item->email }}</td>

                                <td>{{ $item->created_at->format('d M Y, h:i A') }}</td>

                                <td>

                                    @if($item->status == '1')

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    @else

                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    <form action="{{ route('admin.newsletters.destroy',$item->id) }}"
                                          method="POST"
                                          class="d-inline form-delete">

                                        @csrf
                                        @method('DELETE')

                                        <button type="button" class="btn btn-sm btn-danger btn-delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No Subscribers Found
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

                <div class="mt-3">
                    {{ $newsletters->links() }}
                </div>

            </div>

        </div>

    </div>

</div>

@push('js')
<script>
    $(document).ready(function() {
        // If DataTable is not initialized by layout
        if ( ! $.fn.DataTable.isDataTable( '#newsletterTable' ) ) {
            // $('#newsletterTable').DataTable();
        }

        $('.btn-delete').on('click', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to remove this subscriber?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        });
    });
</script>
@endpush

@endsection
