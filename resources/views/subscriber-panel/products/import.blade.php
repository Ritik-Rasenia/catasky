@extends('subscriber-panel.layouts.app')
 
@section('title', 'Product Importer')
@section('page-title', 'Enterprise Product Importer')
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('subscriber.products.index') }}">Products</a></li>
            <li class="breadcrumb-item active">Import</li>
        </ol>
    </nav>
@endsection
 
@section('content')
 
<div class="container-fluid">
 
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-file-import text-primary me-2"></i>Enterprise Product Importer</h3>
            <p class="text-muted mb-0">Upload spreadsheets with cell-embedded images or image URLs. Preview data and correct errors before committing to database.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('subscriber.products.import-logs') }}" class="btn btn-white">
                <i class="fa-solid fa-clock-rotate-left text-primary"></i> Import History
            </a>
            <a href="{{ route('subscriber.products.import.template') }}" class="btn btn-primary">
                <i class="fa-solid fa-download"></i> Download Template
            </a>
            <a href="{{ route('subscriber.products.index') }}" class="btn btn-white">
                <i class="fa-solid fa-arrow-left text-secondary"></i> Products List
            </a>
        </div>
    </div>
 
    <!-- Main Workspace -->
    <div class="row g-4 mb-4">
        <!-- Left Side: Upload / Preview Panel -->
        <div class="col-12" id="panel-container">
            <!-- Upload Card -->
            <div class="card" id="upload-card">
                <div class="card-body p-5 text-center">
                    <form id="product-upload-form" class="m-0">
                        @csrf
                        <div class="drop-zone border border-2 border-dashed rounded-4 p-5 text-center bg-light-subtle cursor-pointer transition" id="excel-drop-zone" style="min-height: 280px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <div class="icon-wrap bg-primary-subtle text-primary rounded-circle p-4 mb-4 " style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-file-excel fa-3x text-indigo"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Drag & Drop Product Excel</h4>
                            <p class="text-muted mb-4 small">Supports (.xlsx) file formats containing cell-embedded images.</p>
                            <button type="button" class="btn btn-outline-primary" id="btn-browse">Browse Files</button>
                            <div class="badge bg-white text-dark border px-3 py-2 rounded-pill mt-3  d-none" id="excel-filename">No file selected</div>
                            <input type="file" name="excel" id="input-excel" class="d-none" accept=".xlsx">
                        </div>
                    </form>
                    <div id="client-error" class="alert alert-danger mt-4 d-none rounded-3" role="alert"></div>
                </div>
            </div>
 
            <!-- Preview Card (Hidden initially) -->
            <div class="card d-none" id="preview-card">
                <div class="card-header bg-transparent border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-magnifying-glass-chart text-indigo me-2"></i>Pre-Import Validation Preview</h5>
                        <p class="text-secondary small mb-0">Confirm the layout and verify there are no validation warnings before inserting.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-soft text-success px-3 py-2" id="preview-valid-count">0 Valid Rows</span>
                        <span class="badge bg-danger-soft text-danger px-3 py-2" id="preview-error-count">0 With Errors</span>
                        <button class="btn btn-white border text-danger" id="btn-cancel-preview"><i class="fa-solid fa-xmark me-1"></i> Cancel</button>
                        <button class="btn btn-primary" id="btn-confirm-import"><i class="fa-solid fa-circle-check me-1"></i> Confirm & Import</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 480px;">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light sticky-top" style="z-index: 1;">
                                <tr>
                                    <th style="width: 80px;" class="ps-4">Row</th>
                                    <th style="width: 120px;">Image</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Category/Subcategory</th>
                                    <th>Offer Price (₹)</th>
                                    <th>Validation Status</th>
                                    <th>Failure Reason</th>
                                </tr>
                            </thead>
                            <tbody id="preview-rows">
                                <!-- Filled by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Live Progress / Worker Panel (Hidden initially) -->
            <div class="card d-none" id="progress-card">
                <div class="card-body p-5 text-center">
                    <div class="spinner-wrap mb-4 position-relative d-inline-block">
                        <div class="spinner-border text-primary" style="width: 5rem; height: 5rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <i class="fa-solid fa-cloud-arrow-up fa-2x text-primary position-absolute start-50 top-50 translate-middle"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2" id="progress-status-title">Uploading...</h4>
                    <p class="text-secondary mb-4 mx-auto" style="max-width: 480px;" id="progress-status-desc">Starting the background queue processor. This may take a minute for large files.</p>
                    
                    <div class="mx-auto" style="max-width: 600px;">
                        <div class="progress rounded-pill mb-3" style="height: 16px; background-color: var(--border);">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-indigo" id="progress-bar-fill" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small px-2">
                            <span id="progress-text-left">Preparing...</span>
                            <span id="progress-text-right">0%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Card (Hidden initially) -->
            <div class="card d-none mb-4" id="summary-card">
                <div class="card-body p-5 text-center">
                    <div class="icon-wrap bg-success-subtle text-success rounded-circle p-4 mb-4 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-circle-check fa-3x"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-2">Import Processed Successfully</h3>
                    <p class="text-secondary mb-4 mx-auto" style="max-width: 500px;">The product import process has finished. Please review the execution summary below.</p>
                    
                    <div class="row g-3 justify-content-center mb-4" style="max-width: 600px; margin: 0 auto;">
                        <div class="col-6 col-sm-3">
                            <div class="border rounded-3 p-3 bg-light-subtle">
                                <div class="text-muted small fw-semibold text-uppercase mb-1">Total Processed</div>
                                <h4 class="fw-bold mb-0 text-dark" id="summary-total-rows">0</h4>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="border rounded-3 p-3 bg-success-subtle border-success-subtle">
                                <div class="text-success small fw-semibold text-uppercase mb-1">Inserted</div>
                                <h4 class="fw-bold mb-0 text-success" id="summary-inserted-rows">0</h4>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="border rounded-3 p-3 bg-info-subtle border-info-subtle">
                                <div class="text-info small fw-semibold text-uppercase mb-1">Updated</div>
                                <h4 class="fw-bold mb-0 text-info" id="summary-updated-rows">0</h4>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="border rounded-3 p-3 bg-danger-subtle border-danger-subtle">
                                <div class="text-danger small fw-semibold text-uppercase mb-1">Failed</div>
                                <h4 class="fw-bold mb-0 text-danger" id="summary-failed-rows">0</h4>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="btn btn-outline-danger d-none" id="btn-download-errors">
                            <i class="fa-solid fa-download me-2"></i>Download Error Report
                        </a>
                        <a href="{{ route('subscriber.products.index') }}" class="btn btn-primary px-4">
                            <i class="fa-solid fa-boxes-stacked me-2"></i>Go to Products
                        </a>
                        <a href="{{ route('subscriber.products.import-logs') }}" class="btn btn-outline-secondary px-4">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>View History
                        </a>
                    </div>
                </div>
            </div>
  
            <!-- Import Logs & Live Logging History -->
            <div class="card" id="logs-card">
                <div class="card-header bg-transparent border-0 py-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list-check text-indigo me-2"></i>Live Import Reports</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success-soft text-success px-3 py-2" id="badge-imported">0 Imported</span>
                        <span class="badge bg-warning-soft text-warning px-3 py-2" id="badge-warning">0 Warnings</span>
                        <span class="badge bg-info-soft text-info px-3 py-2" id="badge-updated">0 Updated</span>
                        <span class="badge bg-danger-soft text-danger px-3 py-2" id="badge-failed">0 Failed</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 80px;" class="ps-4">Row</th>
                                    <th style="width: 120px;">Status</th>
                                    <th style="width: 150px;">SKU</th>
                                    <th>Product Name</th>
                                    <th>Message / Detailed Log</th>
                                </tr>
                            </thead>
                            <tbody id="import-report-rows">
                                <tr id="report-empty-row">
                                    <td colspan="5" class="text-muted text-center py-5">
                                        <i class="fa-solid fa-folder-open fa-3x mb-3 opacity-25 d-block text-indigo"></i>
                                        No active logs to display. Upload a file to get started.
                                    </td>
                                </tr>
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
(function () {
    const excelDropZone = document.getElementById('excel-drop-zone');
    const inputExcel = document.getElementById('input-excel');
    const btnBrowse = document.getElementById('btn-browse');
    const excelFilename = document.getElementById('excel-filename');
    const clientError = document.getElementById('client-error');
    
    const uploadCard = document.getElementById('upload-card');
    const previewCard = document.getElementById('preview-card');
    const progressCard = document.getElementById('progress-card');
    const summaryCard = document.getElementById('summary-card');
    const logsCard = document.getElementById('logs-card');
    
    const previewRows = document.getElementById('preview-rows');
    const previewValidCount = document.getElementById('preview-valid-count');
    const previewErrorCount = document.getElementById('preview-error-count');
    
    const progressStatusTitle = document.getElementById('progress-status-title');
    const progressStatusDesc = document.getElementById('progress-status-desc');
    const progressBarFill = document.getElementById('progress-bar-fill');
    const progressTextLeft = document.getElementById('progress-text-left');
    const progressTextRight = document.getElementById('progress-text-right');
    
    const summaryTotalRows = document.getElementById('summary-total-rows');
    const summaryInsertedRows = document.getElementById('summary-inserted-rows');
    const summaryUpdatedRows = document.getElementById('summary-updated-rows');
    const summaryFailedRows = document.getElementById('summary-failed-rows');
    const btnDownloadErrors = document.getElementById('btn-download-errors');
    
    const importReportRows = document.getElementById('import-report-rows');
    const reportEmptyRow = document.getElementById('report-empty-row');
    const badgeImported = document.getElementById('badge-imported');
    const badgeWarning = document.getElementById('badge-warning');
    const badgeUpdated = document.getElementById('badge-updated');
    const badgeFailed = document.getElementById('badge-failed');
    
    const btnConfirmImport = document.getElementById('btn-confirm-import');
    const btnCancelPreview = document.getElementById('btn-cancel-preview');
    
    const importSubmitUrl = @json(route('subscriber.products.import.submit'));
    const importStatusUrl = @json(url('/dashboard/products-ops/import/status'));
    
    let tempFilePath = null;
    let tempId = null;
    let pollInterval = null;
 
    // Helper functions
    function showClientError(msg) {
        clientError.textContent = msg;
        clientError.classList.remove('d-none');
    }
 
    function hideClientError() {
        clientError.classList.add('d-none');
    }
 
    function escapeHtml(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }
 
    // Drag and Drop hooks
    btnBrowse.addEventListener('click', () => inputExcel.click());
    
    excelDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        excelDropZone.classList.add('border-primary', 'bg-primary-subtle');
    });
    
    excelDropZone.addEventListener('dragleave', () => {
        excelDropZone.classList.remove('border-primary', 'bg-primary-subtle');
    });
    
    excelDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        excelDropZone.classList.remove('border-primary', 'bg-primary-subtle');
        if (e.dataTransfer.files.length) {
            inputExcel.files = e.dataTransfer.files;
            handleExcelSelected();
        }
    });
    
    inputExcel.addEventListener('change', () => {
        if (inputExcel.files.length) {
            handleExcelSelected();
        }
    });
 
    async function handleExcelSelected() {
        hideClientError();
        const file = inputExcel.files[0];
        if (!file) return;
 
        if (!/\.xlsx$/i.test(file.name)) {
            showClientError('Invalid file type. Excel spreadsheet must be a .xlsx file.');
            return;
        }
 
        excelFilename.textContent = file.name;
        excelFilename.classList.remove('d-none');
 
        // Show uploading layout
        uploadCard.classList.add('d-none');
        progressCard.classList.remove('d-none');
        progressStatusTitle.textContent = 'Parsing Excel...';
        progressStatusDesc.textContent = 'Extracting product information and cell-embedded drawings.';
        progressBarFill.style.width = '20%';
        progressTextLeft.textContent = 'Parsing worksheets...';
        progressTextRight.textContent = '20%';
 
        const formData = new FormData();
        formData.append('excel', file);
        formData.append('_token', '{{ csrf_token() }}');
 
        try {
            const res = await fetch(importSubmitUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            });
 
            if (!res.ok) {
                const errorData = await res.json();
                throw new Error(errorData.message || 'Parsing failed.');
            }
 
            const data = await res.json();
            if (data.success) {
                tempFilePath = data.temp_file_path;
                tempId = data.temp_id;
                renderPreview(data);
            } else {
                throw new Error(data.message || 'Parsing spreadsheet failed.');
            }
        } catch (e) {
            showClientError(e.message);
            uploadCard.classList.remove('d-none');
            progressCard.classList.add('d-none');
        }
    }
 
    function renderPreview(data) {
        previewRows.innerHTML = '';
        previewValidCount.textContent = data.summary.valid + ' Valid Rows';
        previewErrorCount.textContent = data.summary.error + ' With Errors';
 
        if (data.summary.valid === 0) {
            btnConfirmImport.disabled = true;
            btnConfirmImport.title = 'There are no valid rows to import.';
        } else {
            btnConfirmImport.disabled = false;
            btnConfirmImport.title = '';
        }
 
        data.rows.forEach((row) => {
            const tr = document.createElement('tr');
            
            // Image Cell
            let imgHtml = '<span class="text-muted small">No Image</span>';
            if (row.featured_image) {
                imgHtml = `<img src="${row.featured_image}" class="preview-thumb" alt="Thumbnail">`;
            }
 
            // Status Badge
            let statusHtml = '';
            if (!row.is_valid) {
                statusHtml = `<span class="badge bg-danger-subtle text-danger py-1 px-3 rounded-pill" title="${escapeHtml(row.errors.join(', '))}"><i class="fa-solid fa-circle-exclamation me-1"></i>${row.errors.length} Errors</span>`;
            } else {
                if (row.action === 'Update') {
                    statusHtml = '<span class="badge bg-info-subtle text-info py-1 px-3 rounded-pill"><i class="fa-solid fa-pen-to-square me-1"></i>Ready (Update)</span>';
                } else {
                    statusHtml = '<span class="badge bg-success-subtle text-success py-1 px-3 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Ready (Insert)</span>';
                }
            }
 
            // Error Reason Cell
            let errorReasonHtml = '<span class="text-muted small">—</span>';
            if (!row.is_valid && row.errors && row.errors.length) {
                errorReasonHtml = `<span class="text-danger small">${escapeHtml(row.errors.join(', '))}</span>`;
            }
 
            tr.innerHTML = `
                <td class="ps-4 fw-bold text-muted">${row.row}</td>
                <td>${imgHtml}</td>
                <td class="fw-bold">${escapeHtml(row.name)}</td>
                <td><code>${escapeHtml(row.sku)}</code></td>
                <td><small class="text-secondary">${escapeHtml(row.category)} &rarr; ${escapeHtml(row.subcategory)}</small></td>
                <td><strong>₹${row.price || '0.00'}</strong></td>
                <td>${statusHtml}</td>
                <td>${errorReasonHtml}</td>
            `;
            previewRows.appendChild(tr);
        });
 
        progressCard.classList.add('d-none');
        previewCard.classList.remove('d-none');
    }
 
    btnCancelPreview.addEventListener('click', () => {
        previewCard.classList.add('d-none');
        uploadCard.classList.remove('d-none');
        inputExcel.value = '';
        excelFilename.classList.add('d-none');
    });
 
    btnConfirmImport.addEventListener('click', async () => {
        if (!tempFilePath || !tempId) return;
 
        btnConfirmImport.disabled = true;
        previewCard.classList.add('d-none');
        progressCard.classList.remove('d-none');
        progressStatusTitle.textContent = 'Queueing Job...';
        progressStatusDesc.textContent = 'Enqueuing products to background queue worker for processing.';
        progressBarFill.style.width = '10%';
        progressBarFill.classList.add('progress-bar-animated', 'progress-bar-striped');
        progressTextLeft.textContent = 'Waiting for queue worker...';
        progressTextRight.textContent = '10%';
 
        const formData = new FormData();
        formData.append('confirm', '1');
        formData.append('temp_file_path', tempFilePath);
        formData.append('temp_id', tempId);
        formData.append('_token', '{{ csrf_token() }}');
 
        try {
            const res = await fetch(importSubmitUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            });
 
            if (!res.ok) {
                const errorData = await res.json();
                throw new Error(errorData.message || 'Queuing failed.');
            }
 
            const data = await res.json();
            if (data.success) {
                pollImportStatus(data.import_log_id);
            } else {
                throw new Error(data.message || 'Queuing job failed.');
            }
        } catch (e) {
            showClientError(e.message);
            uploadCard.classList.remove('d-none');
            progressCard.classList.add('d-none');
        }
    });
 
    function pollImportStatus(logId) {
        if (pollInterval) clearInterval(pollInterval);
        
        pollInterval = setInterval(async () => {
            try {
                const res = await fetch(`${importStatusUrl}/${logId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                updateProgress(data);
 
                if (data.status === 'completed' || data.status === 'failed') {
                    clearInterval(pollInterval);
                    pollInterval = null;
                    progressBarFill.classList.remove('progress-bar-animated', 'progress-bar-striped');
                    
                    if (data.status === 'completed') {
                        progressBarFill.classList.remove('bg-indigo');
                        progressBarFill.classList.add('bg-success');
                        progressStatusTitle.textContent = 'Import Completed!';
                        progressStatusDesc.textContent = `Successfully processed products.`;
                        
                        setTimeout(() => {
                            progressCard.classList.add('d-none');
                            summaryCard.classList.remove('d-none');
                            
                            summaryTotalRows.textContent = data.total_rows || 0;
                            summaryInsertedRows.textContent = data.imported_rows || 0;
                            summaryUpdatedRows.textContent = data.updated_rows || 0;
                            summaryFailedRows.textContent = data.failed_rows || 0;
                            
                            if (data.failed_rows > 0) {
                                btnDownloadErrors.classList.remove('d-none');
                                const downloadUrl = "{{ route('subscriber.products.import-logs.download-errors', ['id' => ':id']) }}".replace(':id', logId);
                                btnDownloadErrors.href = downloadUrl;
                            } else {
                                btnDownloadErrors.classList.add('d-none');
                            }
                        }, 1500);
                    } else {
                        progressBarFill.classList.remove('bg-indigo');
                        progressBarFill.classList.add('bg-danger');
                        progressStatusTitle.textContent = 'Import Failed';
                        progressStatusDesc.textContent = 'Something went wrong during final queue processing.';
                    }
                }
            } catch (e) {
                console.error('Error polling status:', e);
            }
        }, 2000);
    }
 
    function updateProgress(data) {
        const pct = data.percent != null ? data.percent : (data.status === 'completed' || data.status === 'failed' ? 100 : 0);
        progressBarFill.style.width = pct + '%';
        progressTextRight.textContent = pct + '%';
        
        if (data.status === 'pending') {
            progressStatusTitle.textContent = 'Enqueued in Worker Queue';
            progressTextLeft.textContent = 'Job pending in worker queue...';
        } else if (data.status === 'processing') {
            progressStatusTitle.textContent = 'Importing Products...';
            progressTextLeft.textContent = `Processing row ${data.imported_rows + data.updated_rows + data.failed_rows} of ${data.total_rows}`;
        }
 
        // Render Reports
        badgeImported.textContent = (data.imported_rows || 0) + ' Imported';
        badgeWarning.textContent = (data.warning_rows || 0) + ' Warnings';
        badgeUpdated.textContent = (data.updated_rows || 0) + ' Updated';
        badgeFailed.textContent = (data.failed_rows || 0) + ' Failed';
 
        const detailedLogs = data.detailed_logs || [];
        if (detailedLogs.length > 0) {
            reportEmptyRow.classList.add('d-none');
            
            // Clear existing rows
            importReportRows.querySelectorAll('tr:not(#report-empty-row)').forEach(r => r.remove());
            
            detailedLogs.slice(-100).forEach((log) => {
                const tr = document.createElement('tr');
                let badgeClass = 'bg-secondary-subtle text-secondary';
                if (log.status === 'imported') badgeClass = 'bg-success-subtle text-success';
                else if (log.status === 'updated') badgeClass = 'bg-info-subtle text-info';
                else if (log.status === 'warning') badgeClass = 'bg-warning-subtle text-warning';
                else if (log.status === 'failed') badgeClass = 'bg-danger-subtle text-danger';
 
                tr.innerHTML = `
                    <td class="ps-4 fw-bold text-muted">${log.row || '—'}</td>
                    <td><span class="badge ${badgeClass} py-1 px-3 rounded-pill">${log.status}</span></td>
                    <td><code>${escapeHtml(log.part_code || '')}</code></td>
                    <td class="fw-bold">${escapeHtml(log.product_name || '')}</td>
                    <td>${escapeHtml(log.message || '')}</td>
                `;
                importReportRows.appendChild(tr);
            });
        }
    }
})();
</script>
@endpush
 
@endsection
