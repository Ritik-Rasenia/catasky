@php
    $user = auth()->user();
    $profile = $user?->subscriberProfile;
    $primaryColor = $profile?->primary_color ?? '#4F46E5';
    $secondaryColor = $profile?->secondary_color ?? '#7C3AED';
@endphp
<style>
    :root {
        --subscriber-primary: {{ $primaryColor }};
        --subscriber-secondary: {{ $secondaryColor }};
        --text-primary: #0F172A;
        --text-muted: #64748B;
        --border: #E5E7EB;
        --radius: 16px;
        --shadow: 0 4px 20px rgba(0,0,0,0.06);
        --shadow-lg: 0 10px 30px rgba(0,0,0,0.08);
    }

    /* ─── Subscriber Layout Elements ─── */
    .vp-content {
        padding: 28px 0;
        max-width: 1600px;
    }

    /* ─── Cards ─── */
    .vp-card {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        max-width: 100%;
        box-sizing: border-box;
        margin-bottom: 24px;
    }

    .vp-card-header {
        background: transparent;
        border-bottom: 1px solid #F3F4F6;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .vp-card-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .vp-card-body { padding: 22px; }

    /* ─── Stats Cards ─── */
    .stat-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 22px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        transition: transform 0.2s, box-shadow 0.2s;
        max-width: 100%;
        overflow: hidden;
        box-sizing: border-box;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .stat-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .stat-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .stat-value {
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
        margin-top: 4px;
    }

    /* ─── Product Cards ─── */
    .product-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .product-card-img {
        width: 100%;
        aspect-ratio: 4/3;
        object-fit: cover;
        background: #F8FAFC;
    }

    .product-card-img-placeholder {
        width: 100%;
        aspect-ratio: 4/3;
        background: linear-gradient(135deg, #F1F5F9, #E2E8F0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #CBD5E1;
    }

    .product-card-body {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-card-name {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-primary);
        margin-bottom: 6px;
        line-height: 1.3;
    }

    .product-card-desc {
        font-size: 0.78rem;
        color: var(--text-muted);
        line-height: 1.5;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-card-pricing {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid var(--border);
    }

    .price-mrp {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-decoration: line-through;
    }

    .price-offer {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--subscriber-primary);
    }

    .price-discount-badge {
        font-size: 0.68rem;
        font-weight: 700;
        background: #DCFCE7;
        color: #166534;
        padding: 2px 8px;
        border-radius: 20px;
    }

    .product-card-actions {
        padding: 12px 16px;
        background: #F8FAFC;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 8px;
    }

    /* ─── Buttons ─── */
    .btn-subscriber {
        background: linear-gradient(135deg, var(--subscriber-primary), var(--subscriber-secondary));
        color: white;
        border: none;
        padding: 10px 22px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-subscriber:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(79,70,229,0.3);
        color: white;
    }

    .btn-subscriber-outline {
        background: transparent;
        color: var(--subscriber-primary);
        border: 1.5px solid var(--subscriber-primary);
        padding: 9px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-subscriber-outline:hover {
        background: var(--subscriber-primary);
        color: white;
        transform: translateY(-1px);
    }

    /* ─── Form Styles ─── */
    .vp-form-group { margin-bottom: 20px; }

    .vp-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .vp-input,
    .vp-select,
    .vp-textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        font-size: 0.875rem;
        color: #1e293b;
        background: #fff;
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
        font-family: 'Poppins', sans-serif;
    }

    .vp-input:focus,
    .vp-select:focus,
    .vp-textarea:focus {
        border-color: var(--subscriber-primary);
        box-shadow: none !important;
    }

    .vp-textarea { min-height: 100px; resize: vertical; }

    /* Toggle Switch */
    .vp-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }

    .vp-toggle input[type="checkbox"] {
        width: 40px; height: 22px;
        border-radius: 11px;
        appearance: none;
        background: #E2E8F0;
        cursor: pointer;
        position: relative;
        transition: background 0.2s;
    }

    .vp-toggle input[type="checkbox"]:checked {
        background: var(--subscriber-primary);
    }

    .vp-toggle input[type="checkbox"]::after {
        content: '';
        position: absolute;
        width: 16px; height: 16px;
        border-radius: 50%;
        background: white;
        top: 3px; left: 3px;
        transition: left 0.2s;
        box-shadow: 0 1px 4px rgba(0,0,0,0.2);
    }

    .vp-toggle input[type="checkbox"]:checked::after { left: 21px; }

    .vp-toggle-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-primary);
    }

    /* ─── Badges & Status ─── */
    .badge-active { background: #DCFCE7; color: #166534; }
    .badge-inactive { background: #FEE2E2; color: #991B1B; }
    .badge-draft { background: #F1F5F9; color: #475569; }
    .badge-trial { background: #FEF3C7; color: #92400E; }

    /* ─── Tables ─── */
    .vp-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .vp-table thead th {
        padding: 12px 16px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        background: #F8FAFC;
        border-bottom: 1px solid var(--border);
    }
    .vp-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }
    .vp-table tbody tr:hover { background: #FAFBFF; }
    .vp-table tbody tr:last-child td { border-bottom: none; }

    /* ─── Skeleton Loader ─── */
    .skeleton {
        background: linear-gradient(90deg, #F0F2F8 25%, #E8EAF0 50%, #F0F2F8 75%);
        background-size: 400% 100%;
        animation: shimmer 1.4s infinite;
        border-radius: 8px;
    }

    /* Chart container utility to match admin layout */
    .chart-container {
        position: relative;
        width: 100%;
        height: 300px;
        max-height: 350px;
        overflow: hidden;
        box-sizing: border-box;
    }

    @keyframes shimmer {
        0% { background-position: 100% 0; }
        100% { background-position: -100% 0; }
    }

    /* ─── Empty State ─── */
    .empty-state {
        text-align: center;
        padding: 64px 32px;
    }

    .empty-state-icon {
        font-size: 4rem;
        color: #CBD5E1;
        margin-bottom: 16px;
    }

    .empty-state-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .empty-state-text {
        color: var(--text-muted);
        font-size: 0.875rem;
        max-width: 400px;
        margin: 0 auto 24px;
    }

    /* ─── Page Header ─── */
    .vp-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .vp-page-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0;
    }

    .vp-breadcrumb {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .vp-breadcrumb a { color: var(--subscriber-primary); text-decoration: none; }
</style>
