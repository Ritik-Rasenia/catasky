@php
    $user = auth()->user();
    $globalSetting = \App\Models\Setting::first();
    $profile = $user ? $user->subscriberProfile : null;
    
    // Dynamically detect role and load primary/secondary color preferences
    $isAdmin = $user && ($user->hasRole('Super Admin') || $user->hasRole('admin') || !$user->hasRole('Subscriber'));
    $primaryColor = $isAdmin 
        ? ($globalSetting->primary_color ?? '#4F46E5') 
        : ($profile?->primary_color ?? $globalSetting->primary_color ?? '#4F46E5');
    $secondaryColor = $isAdmin 
        ? ($globalSetting->secondary_color ?? '#7C3AED') 
        : ($profile?->secondary_color ?? $globalSetting->secondary_color ?? '#7C3AED');
@endphp
<style>
    /* ─── CATA SKY ENTERPRISE GLOBAL DESIGN SYSTEM ENGINE ─── */
    :root {
        /* Color System (Tailored Sleek Indigo HSL Scheme) */
        --primary-color: {{ $primaryColor }};
        --secondary-color: {{ $secondaryColor }};
        --subscriber-primary: {{ $primaryColor }};
        --subscriber-secondary: {{ $secondaryColor }};
        
        --text-primary: #0F172A;
        --text-secondary: #475569;
        --text-muted: #64748B;
        --border: #E2E8F0;
        --border-color: #E2E8F0;
        
        /* Spacing System (Strict 8px Grid) */
        --space-xs: 8px;
        --space-sm: 16px;
        --space-md: 24px;
        --space-lg: 32px;
        --space-xl: 40px;
        
        /* Border Radius Tokens */
        --radius-lg: 16px;
        --radius-md: 12px;
        --radius-sm: 8px;
        --radius-pill: 100px;
        
        /* Premium Soft Shadows & Ambient Glows */
        --: 0 2px 8px rgba(0, 0, 0, 0.03);
        --shadow: 0 4px 20px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
        --shadow-lg: 0 16px 36px rgba(15, 23, 42, 0.07), 0 1px 4px rgba(0, 0, 0, 0.02);
        --shadow-active: 0 10px 24px rgba(79, 70, 229, 0.16);
    }

    /* Dark Mode Core Theme Injection */
    html[data-theme="dark"] {
        --text-primary: #F8FAFC;
        --text-secondary: #CBD5E1;
        --text-muted: #94A3B8;
        --border: #1E293B;
        --border-color: #1E293B;
        --shadow: 0 4px 24px rgba(0, 0, 0, 0.22);
        --shadow-lg: 0 16px 40px rgba(0, 0, 0, 0.35);
    }

    /* ─── Global Reset & Spacing Utilities (8px Grid Rules) ─── */
    .p-8, .p-xs { padding: var(--space-xs) !important; }
    .p-16, .p-sm { padding: var(--space-sm) !important; }
    .p-24, .p-md { padding: var(--space-md) !important; }
    .p-32, .p-lg { padding: var(--space-lg) !important; }
    .p-40, .p-xl { padding: var(--space-xl) !important; }
    
    .m-8, .m-xs { margin: var(--space-xs) !important; }
    .m-16, .m-sm { margin: var(--space-sm) !important; }
    .m-24, .m-md { margin: var(--space-md) !important; }
    .m-32, .m-lg { margin: var(--space-lg) !important; }
    .m-40, .m-xl { margin: var(--space-xl) !important; }
    
    .gap-8, .gap-xs { gap: var(--space-xs) !important; }
    .gap-16, .gap-sm { gap: var(--space-sm) !important; }
    .gap-24, .gap-md { gap: var(--space-md) !important; }
    .gap-32, .gap-lg { gap: var(--space-lg) !important; }
    
    .mb-8 { margin-bottom: var(--space-xs) !important; }
    .mb-16 { margin-bottom: var(--space-sm) !important; }
    .mb-24 { margin-bottom: var(--space-md) !important; }
    .mb-32 { margin-bottom: var(--space-lg) !important; }
    .mb-40 { margin-bottom: var(--space-xl) !important; }

    /* ─── Typography Enforcements (Tighter SaaS Sizing) ─── */
    body {
        font-family: 'Poppins', 'Inter', sans-serif !important;
        font-size: 13.5px !important;
        line-height: 1.5 !important;
        -webkit-font-smoothing: antialiased;
    }
    
    h1, h2, h3, h4, h5, h6, .brand-font, .sidebar-logo, .vp-page-title, .vp-card-title, .stat-value {
        font-family: 'Outfit', sans-serif !important;
        letter-spacing: -0.02em !important;
    }

    /* Premium Compact Sizing Hierarchy */
    .vp-page-title, h1.fw-bold, h2.fw-bold { 
        font-size: 24px !important; 
        font-weight: 700 !important; 
        color: var(--text-primary); 
        margin: 0; 
        letter-spacing: -0.03em !important;
    }
    h3, .section-title-text { 
        font-size: 18px !important; 
        font-weight: 600 !important; 
        color: var(--text-primary); 
    }
    .vp-card-title, .card-title, h5.fw-bold { 
        font-size: 15px !important; 
        font-weight: 600 !important; 
        color: var(--text-primary); 
    }
    .small-label, label, .form-label, .vp-label { 
        font-size: 11.5px !important; 
        font-weight: 600 !important; 
        letter-spacing: 0.04em !important;
    }
    .text-muted, .extra-small { 
        font-size: 12.5px !important; 
        color: var(--text-muted) !important; 
    }
    .smaller { 
        font-size: 11px !important; 
        color: var(--text-muted); 
    }

    /* ─── Base Layout Element Standardisation ─── */
    .content-body {
        padding: var(--space-md) !important;
        max-width: 1600px;
    }
    
    /* Premium Breadcrumb Styling Engine */
    .content-breadcrumb, .vp-breadcrumb {
        padding: 12px 20px !important;
        background: var(--surface-color, #ffffff) !important;
        border: 1px solid var(--border) !important;
        border-radius: var(--radius-md) !important;
        box-shadow: var(--shadow) !important;
        margin-bottom: 28px !important;
        font-size: 13.5px !important;
        display: flex !important;
        align-items: center !important;
    }
    
    /* Nesting cleanup for bootstrap breadcrumb compatibility */
    .content-breadcrumb nav,
    .content-breadcrumb .breadcrumb {
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        box-shadow: none !important;
        display: flex !important;
        align-items: center !important;
        gap: 0 !important;
    }
    
    .content-breadcrumb a, .vp-breadcrumb a, .breadcrumb-item a {
        color: var(--text-muted) !important;
        text-decoration: none !important;
        font-weight: 500 !important;
        transition: color 0.15s ease !important;
        display: inline-flex !important;
        align-items: center !important;
    }
    
    .content-breadcrumb a:hover, .vp-breadcrumb a:hover, .breadcrumb-item a:hover {
        color: var(--primary-color) !important;
    }
    
    .content-breadcrumb span, .vp-breadcrumb span, .breadcrumb-item.active {
        color: var(--text-primary) !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
    }

    /* ─── Premium Cards & Widgets (Stripe/Linear Feel) ─── */
    .card, .vp-card, .stat-card, .product-card, .vp-page-header {
        background: var(--surface-color, #ffffff) !important;
        border: 1px solid var(--border) !important;
        border-radius: var(--radius-lg) !important;
        box-shadow: var(--shadow) !important;
        transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.22s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.22s ease !important;
        overflow: hidden;
        margin-bottom: var(--space-md);
        box-sizing: border-box;
    }

    .card:hover, .vp-card:hover, .stat-card:hover, .product-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: var(--shadow-lg) !important;
        border-color: rgba(79, 70, 229, 0.18) !important;
    }

    .card-header, .vp-card-header {
        background: transparent !important;
        border-bottom: 1px solid var(--border) !important;
        padding: 14px 20px !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-body, .vp-card-body {
        padding: 20px !important;
        color: var(--text-primary) !important;
    }

    /* ─── Standardised Compact Form Inputs ─── */
    .form-control, .form-select, .vp-input, .vp-select, .vp-textarea, .form-control-color {
        height: 40px;
        padding: 8px 14px !important;
        border: 1.5px solid var(--border) !important;
        border-radius: var(--radius-md) !important;
        font-size: 13.5px !important;
        font-family: 'Poppins', sans-serif !important;
        color: var(--text-primary) !important;
        background-color: var(--surface-color, #ffffff) !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .vp-textarea, textarea.form-control {
        height: auto !important;
        min-height: 100px;
        resize: vertical;
    }

    .form-control:focus, .form-select:focus, .vp-input:focus, .vp-select:focus, .vp-textarea:focus {
        border-color: var(--primary-color) !important;
        background-color: var(--surface-color, #ffffff) !important;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1) !important;
    }

    .form-label, .vp-label {
        font-size: 11.5px !important;
        font-weight: 700 !important;
        color: var(--text-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 6px !important;
    }
    
    .vp-form-group {
        margin-bottom: var(--space-sm) !important;
    }

    /* Custom Switch Toggles */
    .vp-toggle {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
    }

    .vp-toggle input[type="checkbox"], input.form-check-input[type="checkbox"] {
        width: 40px; height: 22px;
        border-radius: 11px;
        appearance: none;
        background: #E2E8F0;
        cursor: pointer;
        position: relative;
        transition: background 0.2s;
        border: none !important;
        outline: none !important;
    }
    
    html[data-theme="dark"] .vp-toggle input[type="checkbox"], html[data-theme="dark"] input.form-check-input[type="checkbox"] {
        background: #334155;
    }

    .vp-toggle input[type="checkbox"]:checked, input.form-check-input[type="checkbox"]:checked {
        background: var(--primary-color) !important;
    }

    .vp-toggle input[type="checkbox"]::after, input.form-check-input[type="checkbox"]::after {
        content: '';
        position: absolute;
        width: 16px; height: 16px;
        border-radius: 50%;
        background: white;
        top: 3px; left: 3px;
        transition: left 0.2s;
        box-shadow: 0 1px 4px rgba(0,0,0,0.15);
    }

    .vp-toggle input[type="checkbox"]:checked::after, input.form-check-input[type="checkbox"]:checked::after { 
        left: 21px; 
    }

    /* ─── Premium SaaS Buttons (Consistent 38px/40px Height) ─── */
    .btn, .btn-primary, .btn-secondary, .btn-outline-primary, .btn-subscriber, .btn-subscriber-outline, .btn-premium {
        height: 38px;
        padding: 8px 18px !important;
        font-size: 13.5px !important;
        font-weight: 600 !important;
        font-family: 'Poppins', sans-serif !important;
        border-radius: var(--radius-md) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        cursor: pointer;
        text-decoration: none;
    }

    /* Primary SaaS Button */
    .btn-primary, .btn-subscriber {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
        border: none !important;
        color: #ffffff !important;
        box-shadow: var(--shadow-active) !important;
    }

    .btn-primary:hover, .btn-subscriber:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.22) !important;
        opacity: 0.95;
        color: #ffffff !important;
    }

    .btn-primary:active, .btn-subscriber:active {
        transform: translateY(0) !important;
    }

    /* Outline Button */
    .btn-outline-primary, .btn-subscriber-outline {
        background: transparent !important;
        color: var(--primary-color) !important;
        border: 1.5px solid var(--primary-color) !important;
    }

    .btn-outline-primary:hover, .btn-subscriber-outline:hover {
        background: var(--primary-color) !important;
        color: #ffffff !important;
        transform: translateY(-1px) !important;
        box-shadow: var(--shadow-active) !important;
    }

    .btn-white, .btn-light {
        background: var(--surface-color, #ffffff) !important;
        border: 1.5px solid var(--border) !important;
        color: var(--text-primary) !important;
        box-shadow: var(--);
    }
    .btn-white:hover, .btn-light:hover {
        background: var(--surface-muted, #f8fafc) !important;
        border-color: var(--text-muted) !important;
        transform: translateY(-1px) !important;
    }

    /* Button Icon Sizing */
    .btn i, .btn-subscriber i {
        font-size: 13px;
    }

    /* ─── Premium Soft Badges ─── */
    .badge {
        font-family: 'Poppins', sans-serif !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        padding: 4px 10px !important;
        border-radius: var(--radius-pill) !important;
        text-transform: capitalize !important;
        letter-spacing: 0.02em;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .bg-success-soft, .badge-active, .bg-success.bg-opacity-10 {
        background-color: rgba(16, 185, 129, 0.08) !important;
        color: #059669 !important;
        border: 1px solid rgba(16, 185, 129, 0.15) !important;
    }
    .bg-danger-soft, .badge-inactive, .bg-danger.bg-opacity-10 {
        background-color: rgba(239, 68, 68, 0.06) !important;
        color: #DC2626 !important;
        border: 1px solid rgba(239, 68, 68, 0.12) !important;
    }
    .bg-warning-soft, .badge-trial, .bg-warning.bg-opacity-10 {
        background-color: rgba(245, 158, 11, 0.06) !important;
        color: #D97706 !important;
        border: 1px solid rgba(245, 158, 11, 0.12) !important;
    }
    .bg-info-soft, .badge-draft, .bg-primary.bg-opacity-10 {
        background-color: rgba(14, 165, 233, 0.06) !important;
        color: #0284C7 !important;
        border: 1px solid rgba(14, 165, 233, 0.12) !important;
    }

    /* ─── Compact Tables (Resolving Horizonal Desktop Scrolls) ─── */
    .table-responsive {
        border-radius: var(--radius-md) !important;
        border: 1px solid var(--border) !important;
        overflow-x: auto !important;
        box-shadow: var(--);
        background: var(--surface-color, #ffffff);
        /* Premium custom thin scrollbar */
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.1) transparent;
    }
    
    .table-responsive::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: transparent;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.3);
        border-radius: 100px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, 0.5);
    }

    .table, .vp-table {
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
        table-layout: auto !important;
    }

    .table thead th, .vp-table thead th {
        background-color: var(--surface-muted, #f8fafc) !important;
        color: var(--text-muted) !important;
        font-family: 'Poppins', sans-serif !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 10px 16px !important;
        border-bottom: 1px solid var(--border) !important;
        white-space: nowrap;
    }

    html[data-theme="dark"] .table thead th, html[data-theme="dark"] .vp-table thead th {
        background-color: #0f172a !important;
    }

    .table tbody td, .vp-table tbody td {
        padding: 10px 16px !important;
        border-bottom: 1px solid var(--border) !important;
        color: var(--text-primary) !important;
        vertical-align: middle !important;
        font-size: 13px !important;
        white-space: normal !important; /* Allow normal wrapping on cells */
        word-break: normal !important;
    }

    /* Compact Table Sizing & Layout (Prevent squeeze & wrap on narrow viewports) */
    .table-nowrap th,
    .table-nowrap td {
        white-space: nowrap !important;
    }

    /* Clean compact action buttons */
    .btn-ap-action {
        width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 13px !important;
        border-radius: 8px !important;
        transition: all 0.2s ease !important;
    }
    .btn-ap-action i {
        font-size: 13px !important; /* Smaller icon size */
        margin: 0 !important;
    }

    
    .table td.nowrap, .table td:last-child {
        white-space: nowrap !important; /* Lock action columns/dates only */
    }

    .table tbody tr:last-child td, .vp-table tbody tr:last-child td {
        border-bottom: none !important;
    }

    .table-hover tbody tr:hover, .vp-table tbody tr:hover {
        background-color: rgba(79, 70, 229, 0.02) !important;
    }

    /* ─── Premium Pagination ─── */
    .pagination, .admin-pagination-wrap .pagination {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 4px;
        margin-top: 16px;
        list-style: none;
        padding: 0;
    }

    .page-link {
        border-radius: var(--radius-sm) !important;
        min-width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--text-primary) !important;
        border: 1.5px solid var(--border) !important;
        background: var(--surface-color, #ffffff) !important;
        font-weight: 600;
        font-size: 12px;
        transition: all 0.2s ease;
        padding: 0 8px;
    }

    .page-link:hover {
        background: var(--surface-muted, #f8fafc) !important;
        border-color: var(--text-muted) !important;
    }

    .page-item.active .page-link {
        background: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: #ffffff !important;
        box-shadow: var(--shadow-active);
    }

    .page-item.disabled .page-link {
        opacity: 0.4;
        pointer-events: none;
    }

    /* ─── Modal Glassmorphic Blurs ─── */
    .modal-content {
        background: var(--surface-color, #ffffff) !important;
        border: 1px solid var(--border) !important;
        border-radius: var(--radius-lg) !important;
        box-shadow: var(--shadow-lg) !important;
        overflow: hidden;
    }

    .modal-header {
        border-bottom: 1px solid var(--border) !important;
        padding: 16px 20px !important;
    }

    .modal-footer {
        border-top: 1px solid var(--border) !important;
        padding: 14px 20px !important;
    }

    .modal-backdrop {
        background-color: #0c0f17 !important;
        backdrop-filter: blur(6px) !important;
    }
    
    .modal-backdrop.show {
        opacity: 0.65 !important;
    }

    /* ─── Equal Height Stat Cards ─── */
    .stat-card {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        height: 100%;
        box-sizing: border-box;
    }

    .stat-icon {
        width: auto; height: auto;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem;
        flex-shrink: 0;
        background: transparent !important;
        color: var(--primary-color);
    }

    .stat-label {
        font-size: 10.5px;
        color: var(--text-muted);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .stat-value {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.15;
        margin-top: 2px;
    }

    /* ─── Empty State Standardisation ─── */
    .empty-state {
        text-align: center;
        padding: 48px 24px;
        background: var(--surface-color, #ffffff);
        border: 1.5px dashed var(--border);
        border-radius: var(--radius-lg);
    }

    .empty-state-icon {
        font-size: 2.5rem;
        color: var(--text-muted);
        margin-bottom: var(--space-xs);
        opacity: 0.5;
    }

    .empty-state-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 6px;
    }

    .empty-state-text {
        color: var(--text-muted);
        font-size: 13px;
        max-width: 400px;
        margin: 0 auto var(--space-sm);
    }

    /* ─── Collapsible SaaS Sidebar System ─── */
    #sidebar {
        background: linear-gradient(180deg, #090f1e 0%, #050811 100%) !important;
        border-right: 1px solid rgba(255, 255, 255, 0.05) !important;
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15) !important;
        z-index: 1050 !important; /* Ensure always floats correctly on overlays */
    }
    
    html[data-theme="dark"] #sidebar {
        background: linear-gradient(180deg, #03060c 0%, #010204 100%) !important;
        border-right: 1px solid rgba(255, 255, 255, 0.03) !important;
    }
    
    #sidebar .sidebar-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        background: transparent !important;
        min-height: var(--top-navbar-height);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px !important;
    }
    
    .sidebar-logo {
        font-family: 'Outfit', sans-serif !important;
        font-weight: 800 !important;
        letter-spacing: -0.04em !important;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #FFFFFF !important;
    }

    #sidebar .section-title {
        color: rgba(255, 255, 255, 0.35) !important;
        font-family: 'Outfit', sans-serif !important;
        font-weight: 700 !important;
        font-size: 10px !important;
        letter-spacing: 0.12em !important;
        padding: 14px 14px 6px 20px !important;
        text-transform: uppercase !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }
    
    #sidebar .section-title:hover {
        color: rgba(255, 255, 255, 0.6) !important;
    }

    #sidebar .nav-link {
        font-family: 'Poppins', sans-serif !important;
        color: rgba(255, 255, 255, 0.6) !important;
        border-radius: var(--radius-md) !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        padding: 8px 14px !important;
        margin: 3px 10px !important;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        white-space: nowrap;
        text-decoration: none;
    }

    #sidebar .nav-link:hover {
        background: rgba(255, 255, 255, 0.05) !important;
        color: #ffffff !important;
        transform: translateX(3px);
    }

    #sidebar .nav-link.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3) !important;
        transform: none !important;
    }

    #sidebar .nav-link i {
        font-size: 14px !important;
        width: 16px;
        text-align: center;
        transition: transform 0.2s ease;
    }

    #sidebar .nav-link:hover i {
        transform: scale(1.15);
    }

    /* Collapsed Sidebar */
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
        padding: 16px 0 !important;
    }

    body[data-sidebar-collapsed="true"] #sidebar .nav-link {
        justify-content: center;
        margin: 3px 6px !important;
        padding: 10px !important;
    }

    body[data-sidebar-collapsed="true"] #sidebar .nav-link i {
        margin: 0 !important;
        font-size: 16px !important;
    }

    /* ─── Premium Glass Navbar Header ─── */
    #top-navbar {
        height: var(--top-navbar-height) !important;
        border-bottom: 1px solid var(--border) !important;
        background: rgba(255, 255, 255, 0.75) !important;
        backdrop-filter: blur(20px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
        transition: all 0.3s ease;
        padding: 0 20px !important;
        z-index: 1030 !important;
    }

    html[data-theme="dark"] #top-navbar {
        background: rgba(11, 18, 32, 0.8) !important;
        border-bottom: 1px solid var(--border) !important;
    }

    .pulse-badge {
        background-color: #ef4444 !important;
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5) !important;
        animation: pulse-ring 1.5s cubic-bezier(0.215, 0.610, 0.355, 1) infinite !important;
    }

    @keyframes pulse-ring {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    /* Mobile Sidebar Overlays and Shadows */
    @media (max-width: 991.98px) {
        #sidebar {
            z-index: 1050 !important;
            box-shadow: 16px 0 40px rgba(0, 0, 0, 0.3) !important;
        }
        .content-body {
            padding: var(--space-sm) !important;
        }
    }

    /* ─── Drag & Drop Import Utilities ─── */
    .transition {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .cursor-pointer {
        cursor: pointer !important;
    }
    .text-indigo {
        color: var(--primary-color) !important;
    }
    .bg-indigo {
        background-color: var(--primary-color) !important;
    }
    #excel-drop-zone {
        transition: all 0.25s ease !important;
    }
    #excel-drop-zone:hover {
        border-color: var(--primary-color) !important;
        background-color: rgba(79, 70, 229, 0.04) !important;
    }
    html[data-theme="dark"] #excel-drop-zone:hover {
        background-color: rgba(79, 70, 229, 0.08) !important;
    }
    .preview-thumb {
        width: 60px;
        height: 60px;
        object-fit: contain;
        background-color: var(--surface-muted);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 4px;
    }
    .border-dashed {
        border-style: dashed !important;
        border-width: 2px !important;
    }
    .unread-bg {
        background-color: rgba(79, 70, 229, 0.03) !important;
    }
    html[data-theme="dark"] .unread-bg {
        background-color: rgba(79, 70, 229, 0.08) !important;
    }
    .notification-item {
        transition: background-color 0.2s ease !important;
    }
    .notification-item:hover {
        background-color: var(--surface-muted) !important;
    }
    /* ─── Radar Spinner Animation (Setup Review) ─── */
    .radar-spinner {
        position: relative;
        width: 80px;
        height: 80px;
        margin: 0 auto;
    }
    .radar-circle {
        position: absolute;
        width: 100%;
        height: 100%;
        border: 2px solid var(--primary-color);
        border-radius: 50%;
        opacity: 0;
        animation: radar-pulse-ring 2.5s cubic-bezier(0.215, 0.610, 0.355, 1) infinite;
    }
    .radar-circle:nth-child(2) {
        animation-delay: 0.8s;
        border-color: var(--secondary-color);
    }
    .radar-circle:nth-child(3) {
        animation-delay: 1.6s;
    }
    .radar-center {
        position: absolute;
        width: 44px;
        height: 44px;
        left: 18px;
        top: 18px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: white;
        box-shadow: 0 0 16px rgba(79,70,229,0.3);
        z-index: 2;
    }
    @keyframes radar-pulse-ring {
        0% {
            transform: scale(0.5);
            opacity: 0;
        }
        50% {
            opacity: 0.5;
        }
        100% {
            transform: scale(1.3);
            opacity: 0;
        }
    }
    /* ─── Premium Dashboard Metrics & Toggle Tabs ─── */
    .metric-card {
        background: var(--surface-color) !important;
        border-radius: var(--radius-lg) !important;
        border: 1px solid var(--border) !important;
        box-shadow: var(--shadow) !important;
        transition: transform 0.22s ease, box-shadow 0.22s ease !important;
    }
    .metric-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: var(--shadow-lg) !important;
    }
    .btn-tab-toggle {
        background: transparent !important;
        color: var(--text-muted) !important;
        border: none !important;
        font-weight: 600 !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        height: 32px !important;
        padding: 4px 14px !important;
        font-size: 12.5px !important;
        border-radius: var(--radius-pill) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-tab-toggle:hover {
        color: var(--text-primary) !important;
        background: var(--surface-muted) !important;
    }
    .btn-tab-toggle.active {
        background: var(--primary-color) !important;
        color: #ffffff !important;
    }
    /* ─── Limited Access Shield Animations ─── */
    .shield-wrapper {
        animation: shield-pulse 2.2s infinite !important;
    }
    .shield-icon {
        animation: shield-scale 4s ease infinite !important;
    }
    @keyframes shield-pulse {
        0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
        70% { box-shadow: 0 0 0 14px rgba(79, 70, 229, 0); }
        100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
    }
    @keyframes shield-scale {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }
    /* ─── Premium jQuery Datatables Customizations ─── */
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 16px !important;
    }
    .dataTables_wrapper .dataTables_filter input {
        height: 38px !important;
        padding: 6px 14px !important;
        border: 1.5px solid var(--border) !important;
        border-radius: var(--radius-md) !important;
        background-color: var(--surface-color) !important;
        color: var(--text-primary) !important;
        outline: none !important;
        font-size: 13px !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        margin-left: 8px !important;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12) !important;
    }
    .dataTables_wrapper .dataTables_length select {
        height: 36px !important;
        padding: 4px 24px 4px 10px !important;
        border: 1.5px solid var(--border) !important;
        border-radius: var(--radius-sm) !important;
        background-color: var(--surface-color) !important;
        color: var(--text-primary) !important;
        outline: none !important;
    }
    .dataTables_wrapper .dataTables_info {
        color: var(--text-muted) !important;
        font-size: 12.5px !important;
        margin-top: 14px !important;
    }
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 14px !important;
    }
    /* ─── Premium System Command Box Styles ─── */
    .letter-spacing-1 { letter-spacing: 1px !important; }
    .command-box {
        background-color: var(--surface-color) !important;
        border: 2px solid var(--border) !important;
        border-radius: var(--radius-md) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease !important;
    }
    .command-box:hover {
        border-color: var(--primary-color) !important;
        transform: translateY(-3px) !important;
        box-shadow: var(--shadow-lg) !important;
    }

    /* ─── Unified Premium Shell Foundation ─── */
    .content-body {
        width: 100%;
        max-width: 1680px;
        margin: 0 auto;
        padding: var(--space-lg) !important;
    }

    .content-breadcrumb {
        margin-bottom: var(--space-md) !important;
    }

    .page-shell,
    .vp-page-shell {
        display: flex;
        flex-direction: column;
        gap: var(--space-md);
    }

    .page-header,
    .vp-page-header {
        background: var(--surface-color, #ffffff) !important;
        border: 1px solid var(--border) !important;
        border-radius: var(--radius-lg) !important;
        box-shadow: var(--shadow) !important;
        padding: var(--space-lg) !important;
        margin-bottom: var(--space-md);
    }

    .page-title,
    .vp-page-title {
        font-family: 'Outfit', sans-serif !important;
        font-size: clamp(2.1rem, 3vw, 2.9rem) !important;
        line-height: 1.08 !important;
        letter-spacing: -0.04em !important;
        color: var(--text-primary) !important;
        font-weight: 800 !important;
        margin: 0;
    }

    .page-subtitle,
    .vp-page-subtitle {
        font-size: 15px !important;
        line-height: 1.7 !important;
        color: var(--text-muted) !important;
        margin-top: 8px !important;
        max-width: 760px;
    }

    .shell-toolbar,
    .vp-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-sm);
        flex-wrap: wrap;
    }

    .shell-actions,
    .vp-actions {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-sm);
        align-items: center;
        justify-content: flex-end;
    }

    .panel-card,
    .vp-panel,
    .section-card {
        background: var(--surface-color, #ffffff) !important;
        border: 1px solid var(--border) !important;
        border-radius: var(--radius-lg) !important;
        box-shadow: var(--shadow) !important;
        overflow: hidden;
    }

    .panel-card:hover,
    .vp-panel:hover,
    .section-card:hover {
        box-shadow: var(--shadow-lg) !important;
        transform: translateY(-2px);
    }

    .panel-card .card-header,
    .vp-panel .card-header,
    .section-card .card-header {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(255, 255, 255, 0.98) 100%) !important;
        border-bottom: 1px solid var(--border) !important;
        padding: 16px 20px !important;
    }

    .panel-card .card-body,
    .vp-panel .card-body,
    .section-card .card-body {
        padding: 20px !important;
    }

    .table-shell,
    .vp-table-shell {
        overflow: hidden;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        background: var(--surface-color, #ffffff);
        box-shadow: var(--);
    }

    .table-shell .table,
    .vp-table-shell .table,
    .table-modern,
    .vp-table {
        margin-bottom: 0 !important;
    }

    .table-shell thead th,
    .vp-table-shell thead th,
    .table-modern thead th,
    .vp-table thead th {
        font-size: 12px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        color: var(--text-muted) !important;
        background: var(--surface-muted, #f8fafc) !important;
        border-bottom: 1px solid var(--border) !important;
        padding: 14px 16px !important;
        white-space: nowrap;
    }

    .table-shell tbody td,
    .vp-table-shell tbody td,
    .table-modern tbody td,
    .vp-table tbody td {
        padding: 14px 16px !important;
        border-bottom: 1px solid var(--border) !important;
        color: var(--text-primary) !important;
        vertical-align: middle !important;
        font-size: 13.5px !important;
    }

    .table-shell tbody tr:hover td,
    .vp-table-shell tbody tr:hover td,
    .table-modern tbody tr:hover td,
    .vp-table tbody tr:hover td {
        background-color: rgba(79, 70, 229, 0.02) !important;
    }

    .input-shell,
    .form-shell .form-control,
    .form-shell .form-select,
    .form-shell textarea,
    .vp-input-shell,
    .vp-select-shell {
        min-height: 44px !important;
        border-radius: var(--radius-md) !important;
        border: 1.5px solid var(--border) !important;
        background: var(--surface-color, #ffffff) !important;
        color: var(--text-primary) !important;
        box-shadow: none !important;
        padding: 10px 14px !important;
    }

    .input-shell:focus,
    .form-shell .form-control:focus,
    .form-shell .form-select:focus,
    .form-shell textarea:focus,
    .vp-input-shell:focus,
    .vp-select-shell:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12) !important;
    }

    .btn,
    .btn-primary,
    .btn-secondary,
    .btn-outline-primary,
    .btn-outline-secondary,
    .btn-light,
    .btn-white,
    .btn-premium,
    .btn-subscriber,
    .btn-subscriber-outline {
        min-height: 44px;
        padding: 10px 18px !important;
        border-radius: var(--radius-md) !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-primary,
    .btn-subscriber {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
        border: none !important;
        color: #ffffff !important;
        box-shadow: var(--shadow-active) !important;
    }

    .btn-outline-primary,
    .btn-subscriber-outline {
        border: 1.5px solid var(--primary-color) !important;
        color: var(--primary-color) !important;
        background: transparent !important;
    }

    .btn-light,
    .btn-white {
        background: var(--surface-color, #ffffff) !important;
        border: 1.5px solid var(--border) !important;
        color: var(--text-primary) !important;
    }

    .btn-primary:hover,
    .btn-subscriber:hover,
    .btn-outline-primary:hover,
    .btn-subscriber-outline:hover,
    .btn-light:hover,
    .btn-white:hover {
        transform: translateY(-1px);
    }

    .badge,
    .status-badge,
    .chip,
    .pill {
        font-size: 11px !important;
        font-weight: 700 !important;
        letter-spacing: 0.02em;
        border-radius: var(--radius-pill) !important;
        padding: 5px 10px !important;
    }

    .stat-grid,
    .analytics-grid,
    .quick-actions-grid {
        display: grid;
        gap: var(--space-md);
    }

    .stat-grid {
        grid-template-columns: repeat(12, minmax(0, 1fr));
    }

    .stat-grid > * {
        grid-column: span 3;
    }

    .analytics-grid {
        grid-template-columns: repeat(12, minmax(0, 1fr));
    }

    .analytics-grid > * {
        grid-column: span 6;
    }

    .empty-state,
    .vp-empty-state {
        text-align: center;
        padding: 56px 24px;
        border: 1px dashed var(--border);
        border-radius: var(--radius-lg);
        background: rgba(248, 250, 252, 0.72);
    }

    .empty-state-icon,
    .vp-empty-state-icon {
        font-size: 42px;
        color: var(--text-muted);
        opacity: 0.4;
        margin-bottom: 12px;
    }

    .empty-state-title,
    .vp-empty-state-title {
        font-family: 'Outfit', sans-serif !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        color: var(--text-primary) !important;
        margin-bottom: 8px;
    }

    .empty-state-text,
    .vp-empty-state-text {
        color: var(--text-muted) !important;
        font-size: 13.5px !important;
        line-height: 1.7 !important;
        max-width: 520px;
        margin: 0 auto;
    }

    #sidebar {
        background: linear-gradient(180deg, #09111f 0%, #04070f 100%) !important;
        border-right: 1px solid rgba(255, 255, 255, 0.05) !important;
        box-shadow: 8px 0 30px rgba(2, 6, 23, 0.18) !important;
    }

    #sidebar .sidebar-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
        min-height: var(--top-navbar-height);
    }

    #sidebar .nav-link {
        border-radius: 14px !important;
        margin: 4px 10px !important;
        padding: 10px 14px !important;
    }

    #sidebar .nav-link.active {
        box-shadow: 0 10px 22px rgba(79, 70, 229, 0.28) !important;
    }

    #top-navbar {
        height: var(--top-navbar-height) !important;
        background: rgba(255, 255, 255, 0.76) !important;
        border-bottom: 1px solid var(--border) !important;
    }

    html[data-theme="dark"] #top-navbar {
        background: rgba(11, 18, 32, 0.82) !important;
    }

    .dropdown-menu {
        border-radius: 18px !important;
        border: 1px solid var(--border) !important;
        box-shadow: var(--shadow-lg) !important;
    }

    .modal-content {
        border-radius: 22px !important;
        border: 1px solid var(--border) !important;
        box-shadow: var(--shadow-lg) !important;
        overflow: hidden;
    }

    .modal-header,
    .modal-footer {
        padding: 16px 20px !important;
        border-color: var(--border) !important;
    }

    .table-responsive {
        border-radius: var(--radius-lg) !important;
        border: 1px solid var(--border) !important;
        background: var(--surface-color, #ffffff);
        box-shadow: var(--);
    }

    @media (max-width: 1199.98px) {
        .stat-grid > *,
        .analytics-grid > * {
            grid-column: span 6;
        }
    }

    @media (max-width: 767.98px) {
        .content-body {
            padding: var(--space-sm) !important;
        }

        .page-header,
        .vp-page-header,
        .panel-card .card-body,
        .vp-panel .card-body {
            padding: var(--space-md) !important;
        }

        .page-title,
        .vp-page-title {
            font-size: 2rem !important;
        }

        .stat-grid > *,
        .analytics-grid > * {
            grid-column: span 12;
        }

        .shell-actions,
        .vp-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .btn,
        .btn-primary,
        .btn-secondary,
        .btn-outline-primary,
        .btn-outline-secondary,
        .btn-light,
        .btn-white,
        .btn-premium,
        .btn-subscriber,
        .btn-subscriber-outline {
            width: 100%;
        }
    }

    /* ─── Premium Spacing & Table Padding Overrides ─── */
    .dataTables_wrapper, #productTable_wrapper {
        padding: 16px 20px !important;
    }

    .dataTables_length, .dataTables_filter {
        padding: 4px 8px 16px 8px !important;
    }

    .dataTables_info, .dataTables_paginate {
        padding: 16px 8px 4px 8px !important;
    }

    /* Stop responsive tables from touching parent borders directly */
    .table-responsive {
        margin: 8px 0 !important;
    }

    /* Separate action buttons inside all tables to ensure zero sticking */
    .table td:last-child div, 
    .ap-tbl td:last-child div,
    .d-inline-flex.gap-2,
    .btn-group {
        gap: 10px !important; /* Spacious gap between buttons */
    }

    /* Global Table Action Button Standardization (Borderless & Transparent by default) */
    .table td:last-child a.btn, 
    .table td:last-child button.btn,
    .table td:last-child .btn-group a.btn,
    .table td:last-child .btn-group button.btn,
    .btn-ap-action {
        width: 32px !important;
        height: 32px !important;
        min-height: 32px !important; /* Override min-height: 44px on generic buttons */
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 13px !important;
        border-radius: 8px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border: none !important; /* NO BORDER BY DEFAULT */
        background: transparent !important; /* TRANSPARENT BACKGROUND */
        color: var(--text-muted) !important;
        box-shadow: none !important; /* NO BOX SHADOW */
        margin: 2px !important;
    }
    
    /* Premium Hover Effects for Transparent Action Buttons */
    .table td:last-child a.btn:hover, 
    .table td:last-child button.btn:hover,
    .table td:last-child .btn-group a.btn:hover,
    .table td:last-child .btn-group button.btn:hover,
    .btn-ap-action:hover {
        background: rgba(79, 70, 229, 0.08) !important; /* Soft primary glow */
        color: var(--primary-color) !important;
        transform: translateY(-1px) !important;
    }
    
    /* Highlight delete/danger buttons in soft red on hover */
    .table td:last-child button.btn-delete:hover,
    .table td:last-child .btn-group button.btn-delete:hover,
    .table td:last-child a.btn-delete:hover,
    .table td:last-child .btn-group a.btn-delete:hover,
    .table td:last-child button.btn-danger:hover,
    .btn-ap-reject:hover {
        border-color: transparent !important;
        color: #ef4444 !important;
        background: rgba(239, 68, 68, 0.08) !important; /* Soft red glow */
    }

    /* Highlight success/share buttons in soft green on hover */
    .table td:last-child a.btn-success:hover,
    .table td:last-child button.btn-success:hover,
    .table td:last-child a.btn-share:hover,
    .btn-ap-approve:hover {
        background: rgba(16, 185, 129, 0.08) !important; /* Soft green glow */
        color: #10b981 !important;
    }

    .table td:last-child a.btn i, 
    .table td:last-child button.btn i,
    .table td:last-child .btn-group a.btn i,
    .table td:last-child .btn-group button.btn i,
    .btn-ap-action i {
        font-size: 13px !important;
        margin: 0 !important;
        line-height: 1 !important;
    }

    /* Premium Quick Shortcut Buttons */
    .btn-quick-shortcut {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 16px 20px !important;
        background: var(--surface-color, #ffffff) !important;
        border: 1px solid var(--border) !important;
        border-left: 4px solid var(--primary-color) !important;
        border-radius: var(--radius-md) !important;
        color: var(--text-primary) !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        text-decoration: none !important;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: var(--) !important;
        margin-bottom: 12px;
        min-height: auto !important;
        width: 100% !important;
    }
    
    .btn-quick-shortcut:hover {
        transform: translateX(4px) !important;
        box-shadow: var(--shadow) !important;
        border-color: rgba(79, 70, 229, 0.25) !important;
        background: var(--surface-muted, #f8fafc) !important;
    }
    
    .btn-quick-shortcut .shortcut-icon-wrap {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(79, 70, 229, 0.08);
        color: var(--primary-color);
        font-size: 14px;
        transition: all 0.22s ease;
    }
    
    .btn-quick-shortcut:hover .shortcut-icon-wrap {
        background: var(--primary-color);
        color: #ffffff;
    }
</style>
