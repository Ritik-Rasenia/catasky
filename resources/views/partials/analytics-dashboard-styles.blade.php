<style>
    .analytics-page {
        --analytics-border: #e2e8f0;
        --analytics-muted: #64748b;
        --analytics-text: #0f172a;
    }
    .analytics-toolbar {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) auto;
        gap: 16px;
        align-items: center;
        margin-bottom: 22px;
    }
    .analytics-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }
    .analytics-card {
        border: 1px solid var(--analytics-border) !important;
        border-radius: 8px !important;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .04) !important;
        overflow: hidden;
    }
    .analytics-card .card-header {
        min-height: 46px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-bottom: 1px solid var(--analytics-border) !important;
    }
    .analytics-kpi .card-body {
        min-height: 92px;
    }
    .analytics-kpi-icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 50%;
    }
    .analytics-chart-body {
        min-height: 270px;
        display: flex;
        align-items: center;
    }
    .analytics-chart-body canvas {
        width: 100% !important;
        max-height: 250px;
    }
    .analytics-funnel-step {
        min-height: 112px;
        border: 1px solid rgba(15, 23, 42, .06);
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .analytics-table {
        min-width: 680px;
    }
    .analytics-table thead th {
        font-size: .68rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #475569;
        white-space: nowrap;
    }
    .analytics-empty {
        min-height: 86px;
        display: grid;
        place-items: center;
        color: var(--analytics-muted);
    }
    @media (max-width: 991.98px) {
        .analytics-toolbar {
            grid-template-columns: 1fr;
        }
        .analytics-actions {
            justify-content: flex-start;
        }
    }
    @media (max-width: 575.98px) {
        .analytics-actions .form-select,
        .analytics-actions input[type="date"],
        .analytics-actions form,
        .analytics-actions a {
            width: 100% !important;
        }
        .analytics-actions form {
            display: grid !important;
            grid-template-columns: 1fr 1fr auto;
        }
        .analytics-chart-body {
            min-height: 230px;
        }
    }
</style>
