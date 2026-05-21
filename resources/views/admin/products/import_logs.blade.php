@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Product Import Logs</h3>
            <p class="text-muted mb-0">History of all product import activities and their results.</p>
        </div>
        <a href="{{ route('admin.products.import') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-cloud-arrow-up me-1"></i> New Import
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Filename</th>
                            <th>Status</th>
                            <th>Imported</th>
                            <th>Skipped</th>
                            <th>Failed</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="small text-muted">{{ $log->created_at->format('h:i A') }}</div>
                            </td>
                            <td><code class="text-primary">{{ $log->filename }}</code></td>
                            <td>
                                @if($log->status == 'completed')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">Completed</span>
                                @elseif($log->status == 'processing')
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Processing</span>
                                @elseif($log->status == 'failed')
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Failed</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">{{ ucfirst($log->status) }}</span>
                                @endif
                            </td>
                            <td class="fw-bold text-success">{{ $log->imported_rows }}</td>
                            <td class="text-warning">{{ $log->skipped_rows }}</td>
                            <td class="text-danger">{{ $log->failed_rows ?? 0 }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.products.import-logs.show', $log->id) }}" class="btn btn-sm btn-light-primary rounded-pill px-3">
                                    <i class="fa-solid fa-eye me-1"></i> View Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fa-solid fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                <p class="text-muted">No import logs found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="p-4 border-top">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
