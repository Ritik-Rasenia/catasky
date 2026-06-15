<style>
    /* ─── CATA SKY PREMIUM ANALYTICS ENGINE ─── */
    .analytics-page {
        --analytics-border: var(--border, #e2e8f0);
        --analytics-muted: var(--text-muted, #64748b);
        --analytics-text: var(--text-primary, #0f172a);
        --analytics-bg: var(--surface-color, #ffffff);
        --analytics-bg-hover: var(--surface-muted, #f8fafc);
        --analytics-radius: var(--radius-lg, 16px);
        --analytics-shadow: var(--shadow);
        --analytics-shadow-lg: var(--shadow-lg);
    }
    
    /* Toolbar styling with Glassmorphism */
    .analytics-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 26px;
        background: rgba(255, 255, 255, 0.45) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border: 1px solid var(--analytics-border) !important;
        border-radius: var(--analytics-radius) !important;
        padding: 16px 24px !important;
        box-shadow: var(--analytics-shadow) !important;
        transition: border-color 0.3s ease;
    }

    html[data-theme="dark"] .analytics-toolbar {
        background: rgba(17, 24, 39, 0.45) !important;
    }

    .analytics-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 12px;
    }

    .analytics-actions .form-select,
    .analytics-actions .form-control {
        border-radius: 8px !important;
        border-color: var(--analytics-border) !important;
        background-color: var(--analytics-bg) !important;
        color: var(--analytics-text) !important;
        font-size: 0.82rem !important;
        height: 36px !important;
        transition: all 0.2s ease !important;
    }

    .analytics-actions .form-select:focus,
    .analytics-actions .form-control:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15) !important;
    }

    /* Premium Glassmorphic Cards */
    .analytics-card {
        background: rgba(255, 255, 255, 0.45) !important;
        backdrop-filter: blur(16px) saturate(120%) !important;
        -webkit-backdrop-filter: blur(16px) saturate(120%) !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        border-radius: var(--analytics-radius) !important;
        box-shadow: var(--analytics-shadow) !important;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        margin-bottom: 0 !important; /* Managed by grid row spacing */
    }

    html[data-theme="dark"] .analytics-card {
        background: rgba(17, 24, 39, 0.45) !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2) !important;
    }

    .analytics-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: var(--analytics-shadow-lg) !important;
        border-color: rgba(79, 70, 229, 0.3) !important;
    }

    html[data-theme="dark"] .analytics-card:hover {
        border-color: rgba(99, 102, 241, 0.4) !important;
    }

    .analytics-card .card-header {
        background: rgba(255, 255, 255, 0.2) !important;
        border-bottom: 1px solid var(--analytics-border) !important;
        font-family: 'Outfit', sans-serif !important;
        font-weight: 600 !important;
        color: var(--analytics-text) !important;
        padding: 16px 20px !important;
    }

    html[data-theme="dark"] .analytics-card .card-header {
        background: rgba(0, 0, 0, 0.1) !important;
    }

    /* KPI Metrics with Hover Indicator and Glowing Icons */
    .analytics-kpi {
        position: relative;
    }

    .analytics-kpi::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 1;
    }

    .analytics-kpi:hover::before {
        opacity: 1;
    }

    .analytics-kpi .card-body {
        min-height: 90px;
    }

    .analytics-kpi-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .analytics-kpi:hover .analytics-kpi-icon {
        transform: scale(1.1) rotate(4deg);
    }

    /* Glow Colors */
    .analytics-kpi:hover .bg-primary-subtle {
        box-shadow: 0 0 16px rgba(79, 70, 229, 0.4) !important;
    }
    .analytics-kpi:hover .bg-info-subtle {
        box-shadow: 0 0 16px rgba(6, 182, 212, 0.4) !important;
    }
    .analytics-kpi:hover .bg-success-subtle {
        box-shadow: 0 0 16px rgba(16, 185, 129, 0.4) !important;
    }
    .analytics-kpi:hover .bg-warning-subtle {
        box-shadow: 0 0 16px rgba(245, 158, 11, 0.4) !important;
    }
    .analytics-kpi:hover .bg-secondary-subtle {
        box-shadow: 0 0 16px rgba(100, 116, 139, 0.4) !important;
    }
    .analytics-kpi:hover .bg-danger-subtle {
        box-shadow: 0 0 16px rgba(239, 68, 68, 0.4) !important;
    }
    .analytics-kpi:hover .bg-dark-subtle {
        box-shadow: 0 0 16px rgba(30, 41, 59, 0.4) !important;
    }

    .kpi-value {
        font-family: 'Outfit', sans-serif !important;
        font-weight: 700 !important;
        font-size: 1.45rem !important;
        color: var(--analytics-text) !important;
        letter-spacing: -0.02em;
    }

    /* Chart Canvas Sizing */
    .analytics-chart-body {
        min-height: 270px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px !important;
    }

    .analytics-chart-body canvas {
        width: 100% !important;
        max-height: 250px;
    }

    /* Premium Conversion Funnel */
    .analytics-funnel-step {
        background: rgba(255, 255, 255, 0.45) !important;
        border: 1px solid var(--analytics-border) !important;
        border-radius: 12px !important;
        padding: 16px !important;
        min-height: 125px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02) !important;
    }

    html[data-theme="dark"] .analytics-funnel-step {
        background: rgba(30, 41, 59, 0.3) !important;
    }

    .analytics-funnel-step:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05) !important;
        border-color: rgba(79, 70, 229, 0.25) !important;
    }

    .analytics-funnel-step .progress {
        background-color: rgba(0, 0, 0, 0.05) !important;
        border-radius: 100px;
        overflow: hidden;
    }

    html[data-theme="dark"] .analytics-funnel-step .progress {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    /* Premium Custom Tables */
    .analytics-table {
        min-width: 600px;
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .analytics-table thead th {
        font-family: 'Poppins', sans-serif !important;
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        color: var(--analytics-muted) !important;
        background-color: var(--analytics-bg-hover) !important;
        border-bottom: 1.5px solid var(--analytics-border) !important;
        padding: 12px 16px !important;
        white-space: nowrap;
    }

    .analytics-table tbody td {
        padding: 12px 16px !important;
        border-bottom: 1px solid var(--analytics-border) !important;
        color: var(--analytics-text) !important;
        font-size: 0.82rem !important;
        transition: all 0.2s ease;
    }

    .analytics-table tbody tr {
        transition: all 0.2s ease;
    }

    .analytics-table tbody tr td:first-child {
        border-left: 3px solid transparent;
    }

    .analytics-table tbody tr:hover td {
        background-color: rgba(79, 70, 229, 0.015) !important;
    }
    
    html[data-theme="dark"] .analytics-table tbody tr:hover td {
        background-color: rgba(99, 102, 241, 0.03) !important;
    }

    .analytics-table tbody tr:hover td:first-child {
        border-left-color: var(--primary-color) !important;
    }

    /* Pulsing Live Dot */
    .live-dot {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        display: inline-block;
        position: relative;
    }

    .live-dot::after {
        content: '';
        width: 18px;
        height: 18px;
        border: 2px solid #10b981;
        border-radius: 50%;
        position: absolute;
        top: -5px;
        left: -5px;
        animation: pulse-dot 2s infinite ease-out;
        opacity: 0;
    }

    @keyframes pulse-dot {
        0% {
            transform: scale(0.5);
            opacity: 1;
        }
        100% {
            transform: scale(1.6);
            opacity: 0;
        }
    }

    .analytics-empty {
        min-height: 100px;
        display: grid;
        place-items: center;
        color: var(--analytics-muted);
        font-family: 'Poppins', sans-serif !important;
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .analytics-toolbar {
            flex-direction: column;
            align-items: stretch;
            padding: 16px !important;
        }
        .analytics-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .analytics-actions {
            display: grid;
            grid-template-columns: 1fr;
            width: 100%;
        }
        .analytics-actions .form-select,
        .analytics-actions .form-control,
        .analytics-actions form,
        .analytics-actions a {
            width: 100% !important;
        }
        .analytics-actions form {
            display: grid !important;
            grid-template-columns: 1fr 1fr auto;
            gap: 4px;
        }
        .analytics-chart-body {
            min-height: 230px;
        }
    }
</style>
