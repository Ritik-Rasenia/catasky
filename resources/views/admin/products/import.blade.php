@extends('admin.layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Import Products</h3>
            <p class="text-muted mb-0">Upload a spreadsheet (.xlsx) and optionally a ZIP archive of product images. Images can also be downloaded automatically from remote URLs in the sheet.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.products.import-logs') }}" class="btn btn-outline-info rounded-pill px-4">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> View Import History
            </a>
            <a href="{{ route('admin.products.import.template') }}" class="btn btn-outline-primary rounded-pill px-4">
                <i class="fa-solid fa-download me-1"></i> Download template
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-light border rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to products
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <h5 class="fw-bold mb-4"><i class="fa-solid fa-upload text-primary me-2"></i>Upload Files</h5>
                    <form id="product-import-form" class="flex-grow-1 d-flex flex-column">
                        @csrf
                        <div class="row g-4 flex-grow-1">
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label fw-semibold">Excel file (.xlsx)</label>
                                <div class="drop-zone border border-2 border-dashed rounded-4 p-5 text-center bg-light-subtle flex-grow-1 d-flex flex-column align-items-center justify-content-center" data-target="excel" style="min-height: 250px;">
                                    <i class="fa-solid fa-file-excel fa-3x text-success mb-3"></i>
                                    <p class="mb-1 fw-bold text-dark">Drag & drop spreadsheet</p>
                                    <p class="mb-3 small text-muted">or click to browse files</p>
                                    <div class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm" id="excel-filename">No file selected</div>
                                    <input type="file" name="excel" id="input-excel" class="d-none" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                                </div>
                                <small class="text-muted mt-2">Max 50 MB. Must match the template headers.</small>
                            </div>
                            <div class="col-md-6 d-flex flex-column">
                                <label class="form-label fw-semibold">Images (.zip) <span class="badge bg-secondary-subtle text-secondary fw-normal">Optional</span></label>
                                <div class="drop-zone border border-2 border-dashed rounded-4 p-5 text-center bg-light-subtle flex-grow-1 d-flex flex-column align-items-center justify-content-center" data-target="zip" style="min-height: 250px;">
                                    <i class="fa-solid fa-file-zipper fa-3x text-warning mb-3"></i>
                                    <p class="mb-1 fw-bold text-dark">Drag & drop ZIP archive</p>
                                    <p class="mb-3 small text-muted">or click to browse files</p>
                                    <div class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm" id="zip-filename">No file selected</div>
                                    <input type="file" name="zip" id="input-zip" class="d-none" accept=".zip,application/zip">
                                </div>
                                <small class="text-muted mt-2">Optional if remote URLs are used. Max 500 MB. Filenames must match columns.</small>
                            </div>
                        </div>

                        <div id="client-error" class="alert alert-danger mt-4 d-none" role="alert"></div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-3" id="btn-submit">
                                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Start Processing Import
                            </button>
                        </div>
                    </form>

                    <div id="progress-panel" class="mt-4 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold" id="progress-label">Preparing…</span>
                            <span class="text-muted small" id="progress-count"></span>
                        </div>
                        <div class="progress rounded-pill" style="height: 12px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <span class="fw-bold fs-5"><i class="fa-solid fa-list-check text-primary me-2"></i>How to Import Products</span>
                </div>
                <div class="card-body">
                    <div class="import-steps">
                        <div class="d-flex gap-3 mb-4">
                            <div class="step-num bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">1</div>
                            <div>
                                <h6 class="fw-bold mb-1">Download Template</h6>
                                <p class="small text-muted mb-0">Use our standard format to ensure data maps correctly to the database.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-4">
                            <div class="step-num bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">2</div>
                            <div>
                                <h6 class="fw-bold mb-1">Fill Product Data</h6>
                                <p class="small text-muted mb-0">Brand, Category, and Subcategory names must match exactly. Existing products will be updated by name.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-4">
                            <div class="step-num bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">3</div>
                            <div>
                                <h6 class="fw-bold mb-1">Zip Your Images</h6>
                                <p class="small text-muted mb-0">ZIP images together. Filenames must match the <code>thumbnail</code> and <code>gallery_images</code> columns.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-4">
                            <div class="step-num bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">4</div>
                            <div>
                                <h6 class="fw-bold mb-1">Upload & Import</h6>
                                <p class="small text-muted mb-0">Select both files and start. Processing runs in the background via queues.</p>
                            </div>
                        </div>
                        <div class="alert alert-info border-0 rounded-3 mb-0 small">
                            <i class="fa-solid fa-circle-info me-2"></i> Make sure <code>php artisan queue:work</code> is running on the server to process background jobs.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3 border-0">
                    <span class="fw-bold fs-5"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Import Results & Logs</span>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill" id="imported-badge">0 imported</span>
                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill" id="warning-badge">0 warnings</span>
                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill" id="skipped-badge">0 skipped</span>
                        <span class="badge bg-dark-subtle text-dark px-3 py-2 rounded-pill" id="failed-badge">0 failed</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px;">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 80px;" class="ps-4">Row</th>
                                    <th style="width: 120px;">Status</th>
                                    <th style="width: 150px;">Part Code</th>
                                    <th>Product Name</th>
                                    <th>Message / Error Details</th>
                                </tr>
                            </thead>
                            <tbody id="result-rows">
                                <tr id="result-empty">
                                    <td colspan="5" class="text-muted text-center py-5">
                                        <i class="fa-solid fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                        No import results to display yet. Start an import to see live logs.
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
    const form = document.getElementById('product-import-form');
    const excelInput = document.getElementById('input-excel');
    const zipInput = document.getElementById('input-zip');
    const excelName = document.getElementById('excel-filename');
    const zipName = document.getElementById('zip-filename');
    const clientError = document.getElementById('client-error');
    const submitBtn = document.getElementById('btn-submit');
    const progressPanel = document.getElementById('progress-panel');
    const progressBar = document.getElementById('progress-bar');
    const progressLabel = document.getElementById('progress-label');
    const progressCount = document.getElementById('progress-count');
    const resultRows = document.getElementById('result-rows');
    const resultEmpty = document.getElementById('result-empty');
    const importedBadge = document.getElementById('imported-badge');
    const warningBadge = document.getElementById('warning-badge');
    const skippedBadge = document.getElementById('skipped-badge');
    const failedBadge = document.getElementById('failed-badge');
    const statusUrl = @json(url('/admin/products/import/status'));
    const submitUrl = @json(route('admin.products.import.submit'));
    const csrf = form.querySelector('input[name="_token"]').value;

    let pollTimer = null;

    function showClientError(msg) {
        clientError.textContent = msg;
        clientError.classList.remove('d-none');
    }

    function hideClientError() {
        clientError.classList.add('d-none');
    }

    function bindDropZone(zone, input, labelEl) {
        zone.addEventListener('click', () => input.click());
        zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('border-primary', 'bg-primary-subtle'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('border-primary', 'bg-primary-subtle'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('border-primary', 'bg-primary-subtle');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                labelEl.textContent = input.files[0].name;
            }
        });
        input.addEventListener('change', () => {
            if (input.files.length) {
                labelEl.textContent = input.files[0].name;
            }
        });
    }

    bindDropZone(document.querySelector('[data-target="excel"]'), excelInput, excelName);
    bindDropZone(document.querySelector('[data-target="zip"]'), zipInput, zipName);

    function validateClient() {
        hideClientError();
        if (!excelInput.files.length) {
            showClientError('Please choose an Excel file.');
            return false;
        }
        const ex = excelInput.files[0];
        if (!/\.xlsx$/i.test(ex.name)) {
            showClientError('Excel must be .xlsx');
            return false;
        }
        if (ex.size > 50 * 1024 * 1024) {
            showClientError('Excel file exceeds 50 MB.');
            return false;
        }
        
        if (zipInput.files.length) {
            const z = zipInput.files[0];
            if (!/\.zip$/i.test(z.name)) {
                showClientError('Archive must be .zip');
                return false;
            }
            if (z.size > 500 * 1024 * 1024) {
                showClientError('ZIP file exceeds 500 MB.');
                return false;
            }
        }
        return true;
    }

    function renderResults(data) {
        const logs = data.detailed_logs || [];
        importedBadge.textContent = (data.imported_rows || 0) + ' imported';
        warningBadge.textContent = (data.warning_rows || 0) + ' warnings';
        skippedBadge.textContent = (data.skipped_rows || 0) + ' skipped';
        failedBadge.textContent = (data.failed_rows || 0) + ' failed';

        resultRows.querySelectorAll('tr:not(#result-empty)').forEach((r) => r.remove());
        if (!logs.length) {
            resultEmpty.classList.remove('d-none');
            return;
        }
        resultEmpty.classList.add('d-none');
        logs.slice(-100).forEach((log) => {
            const tr = document.createElement('tr');
            const statusClass = log.status === 'skipped' ? 'text-danger' :
                               log.status === 'failed' ? 'text-dark' :
                               log.status === 'warning' ? 'text-warning' : 'text-muted';
            tr.innerHTML = `
                <td>${log.row || '—'}</td>
                <td><span class="badge ${statusClass}">${log.status}</span></td>
                <td><code>${escapeHtml(log.part_code || '')}</code></td>
                <td>${escapeHtml(log.product_name || '')}</td>
                <td>${escapeHtml(log.message || '')}</td>
            `;
            resultRows.appendChild(tr);
        });
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function setProgress(data) {
        const pct = data.percent != null ? data.percent : (data.status === 'completed' || data.status === 'failed' ? 100 : 0);
        progressBar.style.width = pct + '%';
        progressBar.setAttribute('aria-valuenow', pct);
        const parts = [];
        if (data.total_rows) {
            parts.push((data.imported_rows + data.skipped_rows + (data.failed_rows || 0)) + ' / ' + data.total_rows);
        }
        parts.push(data.imported_rows + ' imported');
        if (data.skipped_rows) parts.push(data.skipped_rows + ' skipped');
        if (data.failed_rows) parts.push(data.failed_rows + ' failed');
        if (data.warning_rows) parts.push(data.warning_rows + ' warnings');
        progressCount.textContent = parts.join(' · ');
        if (data.status === 'pending') progressLabel.textContent = 'Queued…';
        else if (data.status === 'processing') progressLabel.textContent = 'Importing…';
        else if (data.status === 'completed') progressLabel.textContent = 'Completed';
        else if (data.status === 'failed') progressLabel.textContent = 'Failed';
        renderResults(data);
    }

    function poll(importId) {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(async () => {
            try {
                const res = await fetch(statusUrl + '/' + importId, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                setProgress(data);
                if (data.status === 'completed' || data.status === 'failed') {
                    clearInterval(pollTimer);
                    pollTimer = null;
                    submitBtn.disabled = false;
                    progressBar.classList.remove('progress-bar-animated');
                    if (data.status === 'completed') {
                        progressBar.classList.remove('bg-danger');
                        progressBar.classList.add('bg-success');
                        
                        // Redirect to products index after a short delay
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.products.index') }}";
                        }, 2000);
                    } else {
                        progressBar.classList.add('bg-danger');
                    }
                }
            } catch (e) {
                console.error(e);
            }
        }, 2000);
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!validateClient()) return;

        submitBtn.disabled = true;
        hideClientError();
        progressPanel.classList.remove('d-none');
        progressBar.classList.add('progress-bar-animated', 'bg-primary');
        progressBar.classList.remove('bg-success', 'bg-danger');
        progressBar.style.width = '0%';
        progressLabel.textContent = 'Uploading…';
        progressCount.textContent = '';
        renderResults({detailed_logs: []});

        const body = new FormData(form);

        try {
            const res = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || !json.success) {
                let msg = json.message || 'Upload failed.';
                if (json.errors) {
                    msg = Object.values(json.errors).flat().join(' ');
                }
                showClientError(msg);
                submitBtn.disabled = false;
                progressPanel.classList.add('d-none');
                return;
            }
            progressLabel.textContent = 'Queued…';
            poll(json.import_log_id);
        } catch (err) {
            showClientError('Network error while uploading.');
            submitBtn.disabled = false;
            progressPanel.classList.add('d-none');
        }
    });
})();
</script>
@endpush
@endsection
