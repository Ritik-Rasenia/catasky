@extends('admin.layouts.app')

@section('title', 'Product Importer')
@section('page-title', 'Enterprise Product Importer')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
            <li class="breadcrumb-item active">Import</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="page-shell">
    <div class="page-header">
        <div class="shell-toolbar">
            <div>
                <h1 class="page-title"><i class="fa-solid fa-file-import me-2 text-indigo"></i>Enterprise Product Importer</h1>
                <p class="page-subtitle mb-0">
                    Upload spreadsheets with cell-embedded images or remote image URLs. Validate rows before the final commit.
                </p>
            </div>
            <div class="shell-actions">
                <a href="{{ route('admin.products.import-logs') }}" class="btn btn-white">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Import History</span>
                </a>
                <a href="{{ route('admin.products.import.template') }}" class="btn btn-primary">
                    <i class="fa-solid fa-download"></i>
                    <span>Download Template</span>
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-light">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Products List</span>
                </a>
            </div>
        </div>
    </div>

    <div class="panel-card" id="upload-card">
        <div class="card-body text-center p-4 p-md-5">
            <form id="product-upload-form" class="m-0">
                @csrf
                <div class="empty-state border-dashed cursor-pointer transition" id="excel-drop-zone" style="min-height: 300px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <div class="radar-spinner mb-4">
                        <div class="radar-circle"></div>
                        <div class="radar-circle"></div>
                        <div class="radar-circle"></div>
                        <div class="radar-center">
                            <i class="fa-solid fa-file-excel"></i>
                        </div>
                    </div>
                    <div class="empty-state-title">Drag & Drop Product Excel</div>
                    <div class="empty-state-text">
                        Supports <strong>.xlsx</strong> spreadsheets with embedded images and URL-based previews.
                    </div>
                    <button type="button" class="btn btn-outline-primary mt-3" id="btn-browse">
                        <i class="fa-solid fa-folder-open"></i>
                        <span>Browse Files</span>
                    </button>
                    <div class="badge bg-white text-dark border px-3 py-2 rounded-pill mt-3  d-none" id="excel-filename">
                        No file selected
                    </div>
                    <input type="file" name="excel" id="input-excel" class="d-none" accept=".xlsx">
                </div>
            </form>
            <div id="client-error" class="alert alert-danger mt-4 d-none rounded-3 text-start mb-0" role="alert"></div>
        </div>
    </div>

    <div class="panel-card d-none" id="progress-card">
        <div class="card-body p-4 p-md-5 text-center">
            <div class="radar-spinner mb-4">
                <div class="radar-circle"></div>
                <div class="radar-circle"></div>
                <div class="radar-circle"></div>
                <div class="radar-center">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
            </div>
            <h4 class="fw-bold mb-2" id="progress-status-title">Uploading...</h4>
            <p class="page-subtitle mx-auto" style="max-width: 560px;" id="progress-status-desc">
                Starting the background queue processor. Large files may take a minute.
            </p>

            <div class="mx-auto" style="max-width: 620px;">
                <div class="progress rounded-pill mb-3" style="height: 14px; background-color: var(--surface-muted);">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-indigo" id="progress-bar-fill" role="progressbar" style="width: 0%;"></div>
                </div>
                <div class="d-flex justify-content-between text-muted small px-2">
                    <span id="progress-text-left">Preparing...</span>
                    <span id="progress-text-right">0%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-card d-none" id="preview-card">
        <div class="card-header">
            <div>
                <h5 class="mb-1 fw-bold"><i class="fa-solid fa-magnifying-glass-chart text-indigo me-2"></i>Pre-Import Validation Preview</h5>
                <p class="text-muted small mb-0">Verify rows and fix issues before inserting into the catalogue.</p>
            </div>
            <div class="shell-actions">
                <span class="badge bg-success-soft text-success" id="preview-valid-count">0 Valid Rows</span>
                <span class="badge bg-danger-soft text-danger" id="preview-error-count">0 With Errors</span>
                <button class="btn btn-light text-danger" id="btn-cancel-preview" type="button">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cancel</span>
                </button>
                <button class="btn btn-primary" id="btn-confirm-import" type="button">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Confirm & Import</span>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-shell">
                <div class="table-responsive" style="max-height: 480px;">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;" class="ps-4">Row</th>
                                <th style="width: 120px;">Image</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Category / Subcategory</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Validation Status</th>
                                <th>Failure Reason</th>
                            </tr>
                        </thead>
                        <tbody id="preview-rows"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-card d-none" id="logs-card">
        <div class="card-header">
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-list-check text-indigo me-2"></i>Live Import Reports</h5>
            <div class="shell-actions">
                <span class="badge bg-success-soft text-success" id="badge-imported">0 Imported</span>
                <span class="badge bg-warning-soft text-warning" id="badge-warning">0 Warnings</span>
                <span class="badge bg-danger-soft text-danger" id="badge-skipped">0 Skipped</span>
                <span class="badge bg-info-soft text-dark" id="badge-failed">0 Failed</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-shell">
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-modern align-middle">
                        <thead>
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
@endsection

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
    const importStatusUrl = @json(url('/dashboard/admin/products-ops/import/status'));

    let tempFilePath = null;
    let tempId = null;
    let pollInterval = null;

    function showClientError(msg) {
        clientError.textContent = msg;
        clientError.classList.remove('d-none');
    }

    function hideClientError() {
        clientError.classList.add('d-none');
        clientError.textContent = '';
    }

    function clearChildren(node) {
        while (node.firstChild) {
            node.removeChild(node.firstChild);
        }
    }

    function buildBadge(text, classes, iconClass) {
        const badge = document.createElement('span');
        badge.className = `badge ${classes} rounded-pill`;
        if (iconClass) {
            const icon = document.createElement('i');
            icon.className = iconClass;
            badge.appendChild(icon);
            badge.appendChild(document.createTextNode(` ${text}`));
            return badge;
        }
        badge.textContent = text;
        return badge;
    }

    function buildPreviewRow(row) {
        const tr = document.createElement('tr');

        const rowCell = document.createElement('td');
        rowCell.className = 'ps-4 fw-bold text-muted';
        rowCell.textContent = row.row;

        const imgCell = document.createElement('td');
        if (row.featured_image) {
            const img = document.createElement('img');
            img.src = row.featured_image;
            img.alt = 'Thumbnail';
            img.className = 'preview-thumb';
            imgCell.appendChild(img);
        } else {
            const empty = document.createElement('span');
            empty.className = 'text-muted small';
            empty.textContent = 'No Image';
            imgCell.appendChild(empty);
        }

        const nameCell = document.createElement('td');
        nameCell.className = 'fw-bold';
        nameCell.textContent = row.name || '';

        const skuCell = document.createElement('td');
        const code = document.createElement('code');
        code.textContent = row.sku || '';
        skuCell.appendChild(code);

        const catCell = document.createElement('td');
        const small = document.createElement('small');
        small.className = 'text-secondary';
        small.textContent = `${row.category || ''} → ${row.subcategory || ''}`;
        catCell.appendChild(small);

        const priceCell = document.createElement('td');
        const price = document.createElement('strong');
        price.textContent = `₹${row.price || '0.00'}`;
        priceCell.appendChild(price);

        const stockCell = document.createElement('td');
        stockCell.textContent = row.stock ?? 0;

        const statusCell = document.createElement('td');
        if (row.is_valid) {
            if (row.action === 'Update') {
                statusCell.appendChild(buildBadge('Ready (Update)', 'bg-info-soft text-info', 'fa-solid fa-pen-to-square me-1'));
            } else {
                statusCell.appendChild(buildBadge('Ready (Insert)', 'bg-success-soft text-success', 'fa-solid fa-circle-check me-1'));
            }
        } else {
            const badge = buildBadge(`${row.errors.length} Errors`, 'bg-danger-soft text-danger', 'fa-solid fa-circle-exclamation me-1');
            badge.title = (row.errors || []).join(', ');
            statusCell.appendChild(badge);
        }

        const errorCell = document.createElement('td');
        if (!row.is_valid && row.errors && row.errors.length) {
            errorCell.className = 'text-danger small';
            errorCell.textContent = row.errors.join(', ');
        } else {
            errorCell.className = 'text-muted small';
            errorCell.textContent = '—';
        }

        tr.append(rowCell, imgCell, nameCell, skuCell, catCell, priceCell, stockCell, statusCell, errorCell);
        return tr;
    }

    function buildLogRow(log) {
        const tr = document.createElement('tr');

        const rowCell = document.createElement('td');
        rowCell.className = 'ps-4 fw-bold text-muted';
        rowCell.textContent = log.row || '—';

        const statusCell = document.createElement('td');
        let statusBadgeClass = 'bg-secondary-subtle text-secondary';
        if (log.status === 'imported') statusBadgeClass = 'bg-success-soft text-success';
        else if (log.status === 'skipped') statusBadgeClass = 'bg-danger-soft text-danger';
        else if (log.status === 'warning') statusBadgeClass = 'bg-warning-soft text-warning';
        else if (log.status === 'failed') statusBadgeClass = 'bg-info-soft text-dark';

        const statusBadge = document.createElement('span');
        statusBadge.className = `badge ${statusBadgeClass} rounded-pill`;
        statusBadge.textContent = log.status || 'pending';
        statusCell.appendChild(statusBadge);

        const skuCell = document.createElement('td');
        const code = document.createElement('code');
        code.textContent = log.part_code || '';
        skuCell.appendChild(code);

        const nameCell = document.createElement('td');
        nameCell.className = 'fw-bold';
        nameCell.textContent = log.product_name || '';

        const messageCell = document.createElement('td');
        messageCell.textContent = log.message || '';

        tr.append(rowCell, statusCell, skuCell, nameCell, messageCell);
        return tr;
    }

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

        uploadCard.classList.add('d-none');
        progressCard.classList.remove('d-none');
        progressStatusTitle.textContent = 'Parsing Excel...';
        progressStatusDesc.textContent = 'Extracting product information and validating rows.';
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

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.message || 'Parsing failed.');
            }

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
        clearChildren(previewRows);
        previewValidCount.textContent = `${data.summary.valid} Valid Rows`;
        previewErrorCount.textContent = `${data.summary.error} With Errors`;

        btnConfirmImport.disabled = data.summary.error > 0;
        btnConfirmImport.title = data.summary.error > 0
            ? 'Please fix errors in the Excel file before importing.'
            : '';

        data.rows.forEach((row) => {
            previewRows.appendChild(buildPreviewRow(row));
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
        progressStatusDesc.textContent = 'Enqueuing products for background processing.';
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

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.message || 'Queuing failed.');
            }

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
                        }, 2200);
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
        progressBarFill.style.width = `${pct}%`;
        progressTextRight.textContent = `${pct}%`;

        if (data.status === 'pending') {
            progressStatusTitle.textContent = 'Enqueued in Worker Queue';
            progressTextLeft.textContent = 'Job pending in worker queue...';
        } else if (data.status === 'processing') {
            progressStatusTitle.textContent = 'Importing Products...';
            progressTextLeft.textContent = `Processing row ${(data.imported_rows || 0) + (data.skipped_rows || 0) + (data.failed_rows || 0)} of ${data.total_rows || 0}`;
        }

        badgeImported.textContent = `${data.imported_rows || 0} Imported`;
        badgeWarning.textContent = `${data.warning_rows || 0} Warnings`;
        badgeSkipped.textContent = `${data.skipped_rows || 0} Skipped`;
        badgeFailed.textContent = `${data.failed_rows || 0} Failed`;

        const detailedLogs = data.detailed_logs || [];
        if (detailedLogs.length > 0) {
            reportEmptyRow.classList.add('d-none');
            clearChildren(importReportRows);
            detailedLogs.slice(-100).forEach((log) => {
                importReportRows.appendChild(buildLogRow(log));
            });
        }
    }
})();
</script>
@endpush
