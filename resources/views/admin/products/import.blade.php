@extends('admin.layouts.app')

@section('content')

<div class="container-fluid py-4" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh;">

    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark" style="font-family: 'Outfit', sans-serif;"><i class="fa-solid fa-file-import text-primary me-2"></i>Enterprise Product Importer</h3>
            <p class="text-muted mb-0">Upload spreadsheets with cell-embedded images or image URLs. Preview data and correct errors before committing to database.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.products.import-logs') }}" class="btn btn-outline-dark rounded-pill px-4" style="border-radius: 12px; backdrop-filter: blur(10px);">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> Import History
            </a>
            <a href="{{ route('admin.products.import.template') }}" class="btn btn-primary rounded-pill px-4" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border: none;">
                <i class="fa-solid fa-download me-1"></i> Download Template
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-light border rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-1"></i> Products List
            </a>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="row g-4 mb-4">
        <!-- Left Side: Upload / Preview Panel -->
        <div class="col-12" id="panel-container">
            <!-- Upload Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" id="upload-card" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.4);">
                <div class="card-body p-5 text-center">
                    <form id="product-upload-form" class="m-0">
                        @csrf
                        <div class="drop-zone border border-2 border-dashed rounded-4 p-5 text-center bg-light-subtle cursor-pointer transition" id="excel-drop-zone" style="min-height: 280px; display: flex; flex-direction: column; align-items: center; justify-content: center; border-color: #cbd5e1 !important;">
                            <div class="icon-wrap bg-primary-subtle text-primary rounded-circle p-4 mb-4 shadow-sm" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-file-excel fa-3x text-indigo"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Drag & Drop Product Excel</h4>
                            <p class="text-muted mb-4 small">Supports (.xlsx) file formats containing cell-embedded images.</p>
                            <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2" id="btn-browse">Browse Files</button>
                            <div class="badge bg-white text-dark border px-3 py-2 rounded-pill mt-3 shadow-sm d-none" id="excel-filename">No file selected</div>
                            <input type="file" name="excel" id="input-excel" class="d-none" accept=".xlsx">
                        </div>
                    </form>
                    <div id="client-error" class="alert alert-danger mt-4 d-none rounded-3" role="alert"></div>
                </div>
            </div>

            <!-- Preview Card (Hidden initially) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 d-none" id="preview-card" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.5);">
                <div class="card-header bg-transparent border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-magnifying-glass-chart text-indigo me-2"></i>Pre-Import Validation Preview</h5>
                        <p class="text-secondary small mb-0">Confirm the layout and verify there are no validation warnings before inserting.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill" id="preview-valid-count">0 Valid Rows</span>
                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill" id="preview-error-count">0 With Errors</span>
                        <button class="btn btn-danger rounded-pill px-4" id="btn-cancel-preview"><i class="fa-solid fa-xmark me-1"></i> Cancel</button>
                        <button class="btn btn-success rounded-pill px-4" id="btn-confirm-import"><i class="fa-solid fa-circle-check me-1"></i> Confirm & Import</button>
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
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Validation Status</th>
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
            <div class="card border-0 shadow-sm rounded-4 mb-4 d-none" id="progress-card" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px);">
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
                        <div class="progress rounded-pill mb-3" style="height: 16px; background-color: #e2e8f0;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-indigo" id="progress-bar-fill" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small px-2">
                            <span id="progress-text-left">Preparing...</span>
                            <span id="progress-text-right">0%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Logs & Live Logging History -->
            <div class="card border-0 shadow-sm rounded-4" id="logs-card" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.4);">
                <div class="card-header bg-transparent border-0 py-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list-check text-indigo me-2"></i>Live Import Reports</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill" id="badge-imported">0 Imported</span>
                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill" id="badge-warning">0 Warnings</span>
                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill" id="badge-skipped">0 Skipped</span>
                        <span class="badge bg-dark-subtle text-dark px-3 py-2 rounded-pill" id="badge-failed">0 Failed</span>
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

<style>
    .transition {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .bg-indigo {
        background-color: #4f46e5 !important;
    }
    .text-indigo {
        color: #4f46e5 !important;
    }
    #excel-drop-zone:hover {
        border-color: #4f46e5 !important;
        background-color: rgba(79, 70, 229, 0.04) !important;
    }
    .preview-thumb {
        width: 60px;
        height: 60px;
        object-fit: contain;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 4px;
    }
</style>

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
    const logsCard = document.getElementById('logs-card');
    
    const previewRows = document.getElementById('preview-rows');
    const previewValidCount = document.getElementById('preview-valid-count');
    const previewErrorCount = document.getElementById('preview-error-count');
    
    const progressStatusTitle = document.getElementById('progress-status-title');
    const progressStatusDesc = document.getElementById('progress-status-desc');
    const progressBarFill = document.getElementById('progress-bar-fill');
    const progressTextLeft = document.getElementById('progress-text-left');
    const progressTextRight = document.getElementById('progress-text-right');
    
    const importReportRows = document.getElementById('import-report-rows');
    const reportEmptyRow = document.getElementById('report-empty-row');
    const badgeImported = document.getElementById('badge-imported');
    const badgeWarning = document.getElementById('badge-warning');
    const badgeSkipped = document.getElementById('badge-skipped');
    const badgeFailed = document.getElementById('badge-failed');
    
    const btnConfirmImport = document.getElementById('btn-confirm-import');
    const btnCancelPreview = document.getElementById('btn-cancel-preview');
    
    const importSubmitUrl = @json(route('admin.products.import.submit'));
    const importStatusUrl = @json(url('/admin/products/import/status'));
    
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

        if (data.summary.error > 0) {
            btnConfirmImport.disabled = true;
            btnConfirmImport.title = 'Please fix errors in the Excel file before importing.';
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

            // Error Badge
            let statusHtml = '<span class="badge bg-success-subtle text-success py-1 px-3 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Ready</span>';
            if (!row.is_valid) {
                statusHtml = `<span class="badge bg-danger-subtle text-danger py-1 px-3 rounded-pill" title="${escapeHtml(row.errors.join(', '))}"><i class="fa-solid fa-circle-exclamation me-1"></i>${row.errors.length} Errors</span>`;
            }

            tr.innerHTML = `
                <td class="ps-4 fw-bold text-muted">${row.row}</td>
                <td>${imgHtml}</td>
                <td class="fw-bold">${escapeHtml(row.name)}</td>
                <td><code>${escapeHtml(row.sku)}</code></td>
                <td><small class="text-secondary">${escapeHtml(row.category)} &rarr; ${escapeHtml(row.subcategory)}</small></td>
                <td><strong>$${row.price || '0.00'}</strong></td>
                <td>${row.stock}</td>
                <td>${statusHtml}</td>
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
                        progressStatusDesc.textContent = `Successfully imported ${data.imported_rows} products.`;
                        
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.products.index') }}";
                        }, 2500);
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
            progressTextLeft.textContent = `Processing row ${data.imported_rows + data.skipped_rows + data.failed_rows} of ${data.total_rows}`;
        }

        // Render Reports
        badgeImported.textContent = (data.imported_rows || 0) + ' Imported';
        badgeWarning.textContent = (data.warning_rows || 0) + ' Warnings';
        badgeSkipped.textContent = (data.skipped_rows || 0) + ' Skipped';
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
                else if (log.status === 'skipped') badgeClass = 'bg-danger-subtle text-danger';
                else if (log.status === 'warning') badgeClass = 'bg-warning-subtle text-warning';
                else if (log.status === 'failed') badgeClass = 'bg-dark-subtle text-dark';

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
