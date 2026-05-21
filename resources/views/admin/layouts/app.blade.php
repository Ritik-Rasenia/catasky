<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $globalSetting = \App\Models\Setting::first();
    @endphp
    <title>@yield('title') {{ $globalSetting->site_title ?? 'Catasky Admin' }}</title>
    
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
    
    <!-- Google Fonts: Outfit + Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    @if($globalSetting && $globalSetting->favicon)
        <link rel="icon" href="{{ asset('uploads/settings/' . $globalSetting->favicon) }}">
    @else
        <link rel="icon" href="{{ asset('uploads/fav.png') }}">
    @endif

    <style>
        :root {
            --primary-color: #4F46E5;
            --secondary-color: #7C3AED;
            --sidebar-width: 260px;
            --top-navbar-height: 70px;
            --bg-color: #F8FAFC;
            --card-shadow: 0 4px 20px rgba(0,0,0,0.06);
            --radius-lg: 16px;
            --radius-md: 12px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: #1e293b;
            font-size: 14px;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            background: linear-gradient(180deg, #1E1B4B 0%, #1a1754 60%, #0F172A 100%);
            border-right: 1px solid rgba(255,255,255,0.05);
            transition: all 0.3s;
        }

        #sidebar .sidebar-header {
            height: auto;
            min-height: var(--top-navbar-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.4rem;
            color: white;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        #sidebar .nav-link {
            padding: 11px 18px;
            color: rgba(255,255,255,0.55);
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            border-radius: 12px;
            margin: 2px 12px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        #sidebar .nav-link i {
            font-size: 1rem;
            margin-right: 11px;
            flex-shrink: 0;
        }

        #sidebar .nav-link:hover {
            background: rgba(255,255,255,0.07);
            color: rgba(255,255,255,0.9);
        }

        #sidebar .nav-link.active {
            background: linear-gradient(135deg, rgba(79,70,229,0.7), rgba(124,58,237,0.7));
            color: #fff;
            box-shadow: 0 4px 14px rgba(79,70,229,0.3);
        }

        /* Main Content Area */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        #top-navbar {
            height: var(--top-navbar-height);
            background: #fff;
            border-bottom: 1px solid #E5E7EB;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .content-body {
            padding: 30px;
        }

        /* Modernized Cards */
        .card {
            border: 1px solid #E5E7EB;
            border-radius: var(--radius-lg);
            box-shadow: var(--card-shadow);
            transition: transform 0.2s;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #F3F4F6;
            padding: 1.25rem;
            font-weight: 600;
            font-size: 16px;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 24px;
            border-radius: var(--radius-md);
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-1px);
        }

        /* Stats Cards */
        .stats-card {
            padding: 24px;
            border-radius: var(--radius-lg);
            background: #fff;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stats-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #sidebar.active {
                margin-left: 0;
            }
            #main-content {
                margin-left: 0;
            }
        }

        .bg-success-soft { background-color: rgba(16, 185, 129, 0.1) !important; color: #10b981 !important; }
        .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important; }
    </style>
    @stack('css')
</head>
<body>

    <!-- Sidebar -->
    @include('admin.layouts.sidebar')

    <!-- Main Content -->
    <div id="main-content">
        <!-- Top Navbar -->
        @include('admin.layouts.navbar')

        <!-- Content Body -->
        <div class="content-body">
            @yield('content')
        </div>

        <!-- Footer -->
        @include('admin.layouts.footer')
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js for analytics widgets -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar, #main-content').toggleClass('active');
            });

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            @if(session('success'))
                Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
            @endif

            @if(session('error'))
                Toast.fire({ icon: 'error', title: "{{ session('error') }}" });
            @endif
        });
    </script>
    @stack('js')
</body>
</html>
