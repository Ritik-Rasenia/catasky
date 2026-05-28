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

        /* Essential Shell Positioning Layout Grid Rules */
        html, body {
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
            margin: 0;
            background: var(--bg-color);
            color: var(--text-color);
        }

        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.28s ease;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        .content-shell {
            flex: 1;
            width: 100%;
        }

        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1040;
            overflow: hidden;
            transition: width 0.28s ease, transform 0.28s ease;
        }

        .sidebar-scroll {
            height: calc(100vh - var(--top-navbar-height));
            overflow-y: auto;
            overflow-x: hidden;
            padding: 14px 12px 18px;
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

        /* Premium Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.25);
            border-radius: 10px;
            transition: background 0.2s ease;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.45);
        }
        html[data-theme="dark"] ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.18);
        }
        html[data-theme="dark"] ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.38);
        }

        /* Dedicated sidebar scrollbar design */
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 10px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.28);
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
