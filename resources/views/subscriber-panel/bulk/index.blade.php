@extends('subscriber-panel.layouts.app')

@section('title', 'PIM Bulk Product Uploader')
@section('page-title', 'PIM Bulk Product Uploader')
@section('breadcrumb', 'Workspace → Bulk Uploader')

@section('content')
<div class="row g-3">
    {{-- Left: Upload & Progress Panels --}}
    <div class="col-lg-8">
        {{-- Bulk Upload Form --}}
        <div class="vp-card mb-3" id="upload-panel">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-cloud-arrow-up-fill me-2 text-primary"></i>PIM Bulk Import products & variants</h6>
            </div>
            <div class="vp-card-body">
                <form id="bulk-upload-form" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="vp-form-group">
                        <label class="vp-label">Target Category Template <span class="text-danger">*</span></label>
                        <select name="category_id" class="vp-select" required id="upload-category-id">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted" style="font-size:0.7rem;">Make sure you upload data matching this specific category template.</small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="vp-form-group">
                                <label class="vp-label">Excel Spreadsheet <span class="text-danger">*</span></label>
                                <input type="file" name="excel" class="vp-input" accept=".xlsx" required style="padding:8px 12px;">
                                <small class="text-muted" style="font-size:0.7rem;">Only .xlsx spreadsheets are supported.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="vp-form-group">
                                <label class="vp-label">Images Archive ZIP <small style="font-weight:400;color:#94A3B8;">(Optional)</small></label>
                                <input type="file" name="zip" class="vp-input" accept=".zip" style="padding:8px 12px;">
                                <small class="text-muted" style="font-size:0.7rem;">ZIP containing product thumbnails named matching your Excel rows.</small>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-subscriber w-100 py-2.5 justify-content-center" style="border-radius:12px;">
                        <i class="bi bi-box-arrow-in-down"></i> Start Import Processing
                    </button>
                </form>
            </div>
        </div>

        {{-- Import Progress & Logging Dashboard --}}
        <div class="vp-card mb-3 d-none" id="progress-panel">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-cpu-fill me-2 text-warning"></i>Live Import Status</h6>
                <span class="badge bg-warning text-dark px-2.5 py-1" id="log-status-badge">Processing</span>
            </div>
            <div class="vp-card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-dark fs-14">Parsing rows & media...</span>
                    <span class="fw-bold text-primary fs-14" id="progress-percent">0%</span>
                </div>
                <div class="progress mb-4" style="height:10px; border-radius:5px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%; background:#4F46E5;" id="progress-bar-el"></div>
                </div>

                <div class="row g-2 mb-4 text-center">
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <h3 class="fw-bold text-success mb-1" id="count-imported">0</h3>
                            <small class="text-muted uppercase fs-10 fw-bold">Imported</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <h3 class="fw-bold text-warning mb-1" id="count-skipped">0</h3>
                            <small class="text-muted uppercase fs-10 fw-bold">Skipped</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <h3 class="fw-bold text-danger mb-1" id="count-failed">0</h3>
                            <small class="text-muted uppercase fs-10 fw-bold">Failed</small>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-2.5">Row-by-Row Execution Logs</h6>
                <div class="border rounded-3" style="max-height:250px; overflow-y:auto; font-family:monospace; font-size:0.75rem; background:#FAFBFD; padding:12px;" id="detailed-logs-container">
                    <div class="text-muted">Import log session starting...</div>
                </div>
                
                <div class="mt-4 d-flex justify-content-end d-none" id="progress-actions">
                    <button type="button" class="btn-subscriber" onclick="window.location.reload()">
                        <i class="bi bi-arrow-repeat"></i> Start Another Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Template Downloads --}}
    <div class="col-lg-4">
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-file-earmark-spreadsheet-fill me-2 text-success"></i>PIM Template download</h6>
            </div>
            <div class="vp-card-body">
                <p class="text-muted fs-13">Choose a category template to download its dynamic Excel spreadsheet. The template columns automatically match your category specs!</p>
                
                <form action="{{ route('subscriber.bulk.template') }}" method="GET">
                    <div class="vp-form-group">
                        <label class="vp-label">Target Category</label>
                        <select name="category_id" class="vp-select" required>
                            <option value="">-- Choose Category --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn-subscriber-outline w-100 justify-content-center py-2">
                        <i class="bi bi-download"></i> Download Dynamic Template
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    let pollInterval = null;

    $('#bulk-upload-form').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const formData = new FormData(this);

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Uploading Files...');

        $.ajax({
            url: `{{ route('subscriber.bulk.import') }}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $('#upload-panel').addClass('d-none');
                    $('#progress-panel').removeClass('d-none');
                    startPolling(res.import_log_id);
                } else {
                    Swal.fire('Error!', res.message || 'Failed to upload files.', 'error');
                    submitBtn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-down"></i> Start Import Processing');
                }
            },
            error: function(xhr) {
                let msg = 'Failed to upload files.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Error!', msg, 'error');
                submitBtn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-down"></i> Start Import Processing');
            }
        });
    });

    function startPolling(logId) {
        pollInterval = setInterval(function() {
            $.ajax({
                url: `/subscriber/bulk/status/${logId}`,
                type: 'GET',
                success: function(log) {
                    $('#count-imported').text(log.imported_rows);
                    $('#count-skipped').text(log.skipped_rows);
                    $('#count-failed').text(log.failed_rows);

                    let percent = log.percent !== null ? log.percent : 0;
                    $('#progress-percent').text(percent + '%');
                    $('#progress-bar-el').css('width', percent + '%');

                    // Set badge status
                    const badge = $('#log-status-badge');
                    badge.text(log.status.toUpperCase());
                    if (log.status === 'completed') {
                        badge.removeClass('bg-warning').addClass('bg-success text-white');
                        clearInterval(pollInterval);
                        $('#progress-bar-el').removeClass('progress-bar-animated progress-bar-striped');
                        $('#progress-actions').removeClass('d-none');
                    } else if (log.status === 'failed') {
                        badge.removeClass('bg-warning').addClass('bg-danger text-white');
                        clearInterval(pollInterval);
                        $('#progress-bar-el').removeClass('progress-bar-animated progress-bar-striped').css('background', '#EF4444');
                        $('#progress-actions').removeClass('d-none');
                    }

                    // Render execution logs
                    const logsContainer = $('#detailed-logs-container');
                    logsContainer.empty();
                    if (log.detailed_logs && log.detailed_logs.length > 0) {
                        log.detailed_logs.forEach(item => {
                            let color = '#10B981'; // Succeeded
                            if (item.status === 'failed') color = '#EF4444';
                            if (item.status === 'skipped') color = '#F59E0B';
                            
                            logsContainer.append(`
                                <div style="margin-bottom:6px; border-left:3px solid ${color}; padding-left:8px;">
                                    <span style="color:${color}; font-weight:bold;">[${item.status.toUpperCase()}]</span> 
                                    Row ${item.row}: <strong>${item.product_name || 'N/A'}</strong> (SKU: ${item.part_code || 'N/A'}) - ${item.message}
                                </div>
                            `);
                        });
                        // Scroll to bottom
                        logsContainer.scrollTop(logsContainer[0].scrollHeight);
                    } else {
                        logsContainer.html('<div class="text-muted">Import log session starting...</div>');
                    }
                },
                error: function() {
                    clearInterval(pollInterval);
                }
            });
        }, 1500);
    }
</script>
@endpush
