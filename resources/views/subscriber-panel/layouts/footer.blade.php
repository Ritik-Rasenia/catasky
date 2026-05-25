<footer class="py-4 mt-auto border-top" style="background: var(--surface-color); border-color: var(--border-color) !important;">
    @php $footerSetting = \App\Models\Setting::first(); @endphp
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between small">
            <div class="text-muted">Copyright &copy; {{ $footerSetting->site_title ?? 'Catasky' }} {{ date('Y') }}</div>
            <div>
                <a href="#" class="text-decoration-none text-muted me-3">Privacy Policy</a>
                <a href="#" class="text-decoration-none text-muted">Terms &amp; Conditions</a>
            </div>
        </div>
    </div>
</footer>
