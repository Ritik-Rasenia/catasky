<!DOCTYPE html>
<html lang="en" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    @php
        $globalSetting = \App\Models\Setting::first();
        $adminTitle = trim($__env->yieldContent('title'));
        $siteTitle = $globalSetting->site_title ?? 'Catasky';
        $metaDescription = $globalSetting->site_description ?? 'Catasky catalogue administration panel.';
        $faviconUrl = ($globalSetting && $globalSetting->favicon)
            ? asset('uploads/settings/' . $globalSetting->favicon)
            : asset('uploads/fav.png');
        $currentUser = auth()->user();
        $currentRole = $currentUser?->roles?->pluck('name')->first() ?? 'User';
    @endphp
    <title>{{ $adminTitle ? $adminTitle . ' | ' : '' }}{{ $siteTitle }} Admin</title>
    <meta name="description" content="{{ Str::limit(strip_tags($metaDescription), 160, '') }}">

    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    <style>
        :root {
            --primary-color: {{ $globalSetting->primary_color ?? '#4F46E5' }};
            --secondary-color: {{ $globalSetting->secondary_color ?? '#7C3AED' }};
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 72px;
            --top-navbar-height: 72px;
            --bg-color: #f5f7fb;
            --surface-color: #ffffff;
            --surface-muted: #f8fafc;
            --text-color: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-color: 0 1px 3px rgba(0, 0, 0, 0.04), 0 4px 20px rgba(0, 0, 0, 0.06);
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 4px 20px rgba(0, 0, 0, 0.06);
            --radius-lg: 18px;
            --radius-md: 14px;
            --radius-sm: 10px;
        }

        html[data-theme="dark"] {
            --bg-color: #0b1220;
            --surface-color: #111827;
            --surface-muted: #0f172a;
            --text-color: #e5e7eb;
            --text-muted: #94a3b8;
            --border-color: #243041;
            --shadow-color: 0 1px 3px rgba(0, 0, 0, 0.25), 0 8px 28px rgba(0, 0, 0, 0.22);
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.25), 0 8px 28px rgba(0, 0, 0, 0.22);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
        }

        h1, h2, h3, h4, h5, h6, .sidebar-logo, .brand-font {
            font-family: 'Outfit', sans-serif;
        }

        a {
            text-decoration: none;
        }

        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1040;
            background: linear-gradient(180deg, #10162a 0%, #0f172a 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.04);
            overflow: hidden;
            transition: width 0.28s ease, transform 0.28s ease;
        }

        html[data-theme="dark"] #sidebar {
            background: linear-gradient(180deg, #0b1220 0%, #0f172a 100%);
        }

        #sidebar .sidebar-header {
            min-height: var(--top-navbar-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 18px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .sidebar-logo {
            color: #fff;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.03em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-logo-badge {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.08);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-scroll {
            height: calc(100vh - var(--top-navbar-height));
            overflow-y: auto;
            overflow-x: hidden;
            padding: 14px 12px 18px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        #sidebar .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            margin: 2px 4px;
            color: rgba(255, 255, 255, 0.68);
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
            white-space: nowrap;
        }

        #sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.07);
            color: #fff;
        }

        #sidebar .nav-link.active {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.95), rgba(124, 58, 237, 0.95));
            color: #fff;
            box-shadow: 0 10px 24px rgba(79, 70, 229, 0.24);
        }

        #sidebar .nav-link i {
            font-size: 1rem;
            width: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        #sidebar .section-title {
            padding: 18px 10px 8px;
            font-size: 0.62rem;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: rgba(255, 255, 255, 0.32);
            font-weight: 700;
        }

        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.28s ease;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        #top-navbar {
            min-height: var(--top-navbar-height);
            position: sticky;
            top: 0;
            z-index: 1030;
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--border-color);
        }

        html[data-theme="dark"] #top-navbar {
            background: rgba(17, 24, 39, 0.86);
        }

        .content-shell {
            flex: 1;
            width: 100%;
        }

        .content-body {
            width: 100%;
            max-width: 1600px;
            margin: 0 auto;
            padding: 24px;
            box-sizing: border-box;
        }

        .content-body > * {
            max-width: 100%;
        }

        .content-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
            padding: 12px 16px;
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            box-shadow: var(--card-shadow);
        }

        .content-breadcrumb .breadcrumb {
            margin: 0;
        }

        .content-breadcrumb .breadcrumb-item,
        .content-breadcrumb .breadcrumb-item a {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .content-breadcrumb .breadcrumb-item.active {
            color: var(--text-color);
            font-weight: 600;
        }

        .card, .stat-card, .dash-card, .metric-card, .vp-card, .skeleton-card {
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--card-shadow);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .card:hover, .stat-card:hover, .dash-card:hover, .metric-card:hover, .vp-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
            color: var(--text-color);
        }

        .card-body {
            color: var(--text-color);
        }

        .table {
            --bs-table-bg: transparent;
            color: var(--text-color);
            margin-bottom: 0;
        }

        .table thead th {
            white-space: nowrap;
            color: var(--text-muted);
            border-color: var(--border-color);
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .table tbody td {
            vertical-align: middle;
            border-color: var(--border-color);
        }

        .form-control,
        .form-select,
        .input-group-text {
            background-color: var(--surface-color);
            color: var(--text-color);
            border-color: var(--border-color);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.12);
        }

        .btn-primary,
        .btn-primary:focus {
            background: var(--primary-color);
            border-color: var(--primary-color);
            box-shadow: none;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
            max-height: 350px;
            overflow: hidden;
        }

        .skeleton {
            position: relative;
            overflow: hidden;
            background: linear-gradient(90deg, rgba(148, 163, 184, 0.10) 25%, rgba(148, 163, 184, 0.18) 37%, rgba(148, 163, 184, 0.10) 63%);
            background-size: 400% 100%;
            animation: skeleton-loading 1.35s ease infinite;
            border-radius: 14px;
        }

        .skeleton-line {
            height: 14px;
        }

        .skeleton-card {
            min-height: 120px;
        }

        .skeleton-chart {
            height: 300px;
        }

        .empty-state {
            display: grid;
            place-items: center;
            text-align: center;
            gap: 10px;
            padding: 40px 18px;
            color: var(--text-muted);
        }

        .empty-state .empty-state-icon {
            width: 68px;
            height: 68px;
            border-radius: 22px;
            display: grid;
            place-items: center;
            background: rgba(79, 70, 229, 0.08);
            color: var(--primary-color);
            font-size: 1.7rem;
        }

        .admin-pagination-wrap .pagination {
            flex-wrap: wrap;
            gap: 6px;
        }

        .admin-pagination-wrap .page-link {
            border-radius: 10px !important;
            min-width: 38px;
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
            border-color: var(--border-color);
            background: var(--surface-color);
        }

        .admin-pagination-wrap .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }

        [data-theme="dark"] .dropdown-menu {
            background: #111827;
            color: #e5e7eb;
            border-color: #243041;
        }

        [data-theme="dark"] .dropdown-item {
            color: #e5e7eb;
        }

        [data-theme="dark"] .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        [data-theme="dark"] .border,
        [data-theme="dark"] .border-top,
        [data-theme="dark"] .border-bottom {
            border-color: var(--border-color) !important;
        }

        body[data-sidebar-collapsed="true"] #sidebar {
            width: var(--sidebar-collapsed-width);
        }

        body[data-sidebar-collapsed="true"] #main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        body[data-sidebar-collapsed="true"] #sidebar .sidebar-title-text,
        body[data-sidebar-collapsed="true"] #sidebar .section-title,
        body[data-sidebar-collapsed="true"] #sidebar .nav-link span,
        body[data-sidebar-collapsed="true"] #sidebar .sidebar-footer-text {
            display: none !important;
        }

        body[data-sidebar-collapsed="true"] #sidebar .sidebar-header {
            justify-content: center;
        }

        body[data-sidebar-collapsed="true"] #sidebar .nav-link {
            justify-content: center;
            padding: 12px 10px;
        }

        body[data-sidebar-collapsed="true"] #sidebar .nav-link i {
            margin: 0;
        }

        body[data-sidebar-collapsed="true"] .sidebar-logo-badge {
            margin: 0 auto;
        }

        @keyframes skeleton-loading {
            0% { background-position: 100% 0; }
            100% { background-position: 0 0; }
        }

        @media (max-width: 991.98px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.active {
                transform: translateX(0);
                width: var(--sidebar-width);
            }

            #main-content {
                margin-left: 0;
            }

            body[data-sidebar-collapsed="true"] #main-content {
                margin-left: 0;
            }
        }

        @media (max-width: 767.98px) {
            .content-body {
                padding: 16px;
            }

            .content-breadcrumb {
                padding: 10px 12px;
            }
        }

        @media (max-width: 575.98px) {
            .content-body {
                padding: 12px;
            }

            .content-breadcrumb {
                display: none;
            }
        }
    </style>

    @include('partials.subscriber_styles')
    @stack('css')
</head>
<body data-sidebar-collapsed="false">
    @include('admin.layouts.sidebar')

    <div id="main-content">
        @include('admin.layouts.navbar')

        <main class="content-shell">
            <div class="content-body">
                @hasSection('breadcrumb')
                    <div class="content-breadcrumb">
                        @yield('breadcrumb')
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        @include('admin.layouts.footer')
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        (function () {
            const root = document.documentElement;
            const storageKey = 'catasky-theme';
            const sidebarKey = 'catasky-sidebar-collapsed';

            const savedTheme = localStorage.getItem(storageKey);
            if (savedTheme === 'dark' || savedTheme === 'light') {
                root.setAttribute('data-theme', savedTheme);
            }

            const savedSidebar = localStorage.getItem(sidebarKey);
            if (savedSidebar === 'true') {
                document.body.setAttribute('data-sidebar-collapsed', 'true');
            }

            window.toggleCataskyTheme = function () {
                const current = root.getAttribute('data-theme') || 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                localStorage.setItem(storageKey, next);
                const toggle = document.getElementById('themeToggle');
                if (toggle) {
                    toggle.innerHTML = next === 'dark'
                        ? '<i class="bi bi-sun-fill"></i>'
                        : '<i class="bi bi-moon-stars-fill"></i>';
                }
            };

            window.toggleSidebarState = function () {
                const collapsed = document.body.getAttribute('data-sidebar-collapsed') === 'true';
                const next = (!collapsed).toString();
                document.body.setAttribute('data-sidebar-collapsed', next);
                localStorage.setItem(sidebarKey, next);
            };

            window.alertService = window.alertService || {
                successAlert(title, message = '') {
                    return Swal.fire({ icon: 'success', title, text: message, timer: 2600, timerProgressBar: true, showConfirmButton: false });
                },
                errorAlert(title, message = '') {
                    return Swal.fire({ icon: 'error', title, text: message, confirmButtonText: 'Got it' });
                },
                warningAlert(title, message = '') {
                    return Swal.fire({ icon: 'warning', title, text: message, confirmButtonText: 'Continue' });
                },
                toastSuccess(message) {
                    return Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true }).fire({ icon: 'success', title: message });
                },
                toastError(message) {
                    return Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true }).fire({ icon: 'error', title: message });
                },
                confirmDelete(title = 'Are you sure?', message = 'This action cannot be undone.') {
                    return Swal.fire({
                        icon: 'warning',
                        title,
                        text: message,
                        showCancelButton: true,
                        confirmButtonText: 'Delete',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        focusCancel: true,
                        confirmButtonColor: '#dc2626'
                    });
                }
            };

            $(document).ready(function () {
                $('#sidebarCollapse').on('click', function () {
                    if (window.innerWidth < 992) {
                        $('#sidebar').toggleClass('active');
                    } else {
                        window.toggleSidebarState();
                    }
                });

                $('[data-confirm-delete]').on('submit', function (event) {
                    event.preventDefault();
                    const form = this;
                    window.alertService.confirmDelete('Are you sure?', $(form).data('confirm-delete')).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });

                @if(session('success'))
                    window.alertService.toastSuccess(@json(session('success')));
                @endif

                @if(session('error'))
                    window.alertService.toastError(@json(session('error')));
                @endif
            });
        })();
    </script>

    @stack('js')
</body>
</html>
