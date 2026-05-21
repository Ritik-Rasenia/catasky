@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Import Details</h3>
            <p class="text-muted mb-0">Detailed results for import: <code class="text-primary">{{ $log->filename }}</code></p>
        </div>
        <a href="{{ route('admin.products.import-logs') }}" class="btn btn-light border rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Logs
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Rows</div>
                <h2 class="fw-bold mb-0">{{ $log->total_rows }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 border-bottom border-4 border-success">
                <div class="text-success small fw-bold text-uppercase mb-1">Imported</div>
                <h2 class="fw-bold mb-0 text-success">{{ $log->imported_rows }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 border-bottom border-4 border-warning">
                <div class="text-warning small fw-bold text-uppercase mb-1">Skipped</div>
                <h2 class="fw-bold mb-0 text-warning">{{ $log->skipped_rows }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 border-bottom border-4 border-danger">
                <div class="text-danger small fw-bold text-uppercase mb-1">Failed</div>
                <h2 class="fw-bold mb-0 text-danger">{{ $log->failed_rows ?? 0 }}</h2>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-list text-primary me-2"></i>Row Level Logs</h5>
            <div class="small">
                <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill px-3">Red Text = Unmatched in System</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 600px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th class="ps-4" style="width: 80px;">Row</th>
                            <th style="width: 120px;">Status</th>
                            <th>Category Info</th>
                            <th style="width: 150px;">Part Code</th>
                            <th>Product Name</th>
                            <th>Message / Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $detailedLogs = is_array($log->detailed_logs) ? $log->detailed_logs : json_decode($log->detailed_logs, true) ?? []; @endphp
                        @forelse($detailedLogs as $item)
                        <tr>
                            <td class="ps-4">{{ $item['row'] ?? '—' }}</td>
                            <td>
                                @php
                                    $status = $item['status'] ?? 'unknown';
                                    $badgeClass = $status == 'imported' ? 'bg-success-subtle text-success' : 
                                                 ($status == 'skipped' ? 'bg-warning-subtle text-warning' : 
                                                 ($status == 'failed' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary'));
                                @endphp
                                <span class="badge {{ $badgeClass }} rounded-pill px-2">{{ ucfirst($status) }}</span>
                            </td>
                            <td>
                                <div class="small fw-bold {{ ($item['failed_on'] ?? '') == 'category' ? 'text-danger' : 'text-dark' }}">
                                    {{ $item['category'] ?? '—' }}
                                    @if(($item['failed_on'] ?? '') == 'category') <i class="fa-solid fa-circle-xmark ms-1"></i> @endif
                                </div>
                                <div class="small text-muted">
                                    <span class="{{ ($item['failed_on'] ?? '') == 'subcategory' ? 'text-danger fw-bold' : '' }}">
                                        {{ $item['subcategory'] ?? '—' }}
                                        @if(($item['failed_on'] ?? '') == 'subcategory') <i class="fa-solid fa-circle-xmark ms-1"></i> @endif
                                    </span>
                                    @if(!empty($item['child_category']))
                                        <span class="mx-1">></span>
                                        <span class="{{ ($item['failed_on'] ?? '') == 'child_category' ? 'text-danger fw-bold' : '' }}">
                                            {{ $item['child_category'] }}
                                            @if(($item['failed_on'] ?? '') == 'child_category') <i class="fa-solid fa-circle-xmark ms-1"></i> @endif
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td><code>{{ $item['part_code'] ?? '—' }}</code></td>
                            <td class="fw-bold text-dark">{{ $item['product_name'] ?? '—' }}</td>
                            <td><span class="small text-muted">{{ $item['message'] ?? '—' }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No detailed row logs found for this import.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
