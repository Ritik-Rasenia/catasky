<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }} | CataSky Billing</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: #1E293B;
            background-color: #F8FAFC;
            padding: 40px 0;
        }

        .invoice-card {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            padding: 45px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            max-width: 850px;
            margin: 0 auto;
        }

        .invoice-header {
            border-bottom: 2px solid #F1F5F9;
            padding-bottom: 30px;
            margin-bottom: 40px;
        }

        .invoice-logo-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.6rem;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .invoice-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: #0F172A;
            line-height: 1;
            margin: 0;
            text-align: right;
        }

        .table-invoice th {
            background-color: #F8FAFC;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            border-bottom: 2px solid #E2E8F0;
        }

        .table-invoice td {
            padding: 16px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
        }

        .invoice-meta-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748B;
        }

        .invoice-meta-value {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0F172A;
            margin-top: 2px;
        }

        .total-box {
            background-color: #F8FAFC;
            border-radius: 12px;
            padding: 20px;
            width: 320px;
            margin-left: auto;
        }

        .btn-print {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(79,70,229,0.2);
            transition: all 0.2s;
        }

        .btn-print:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79,70,229,0.3);
            color: white;
        }

        @media print {
            body {
                background-color: #FFFFFF;
                padding: 0;
            }
            .invoice-card {
                border: none;
                padding: 0;
                box-shadow: none;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container mb-4 no-print text-center">
    <div class="d-flex justify-content-center gap-3">
        <button onclick="window.print()" class="btn-print">
            <i class="bi bi-printer-fill me-1"></i> Print / Download PDF
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary" style="border-radius: 10px; padding: 10px 20px; font-weight:600;">
            Close Tab
        </button>
    </div>
</div>

<div class="invoice-card">
    {{-- Header block --}}
    <div class="invoice-header">
        <div class="row align-items-center">
            <div class="col-6">
                <div class="invoice-logo-title">CATASKY SaaS</div>
                <div class="text-muted mt-1" style="font-size:0.8rem;">Attribute Product Sharing Platform</div>
            </div>
            <div class="col-6 text-end">
                <h1 class="invoice-title">INVOICE</h1>
                <div class="text-muted mt-1" style="font-size:0.9rem; font-weight: 500;">Invoice #{{ $invoice->invoice_number }}</div>
            </div>
        </div>
    </div>

    {{-- Meta specs --}}
    <div class="row mb-5">
        <div class="col-3">
            <div class="invoice-meta-label">Invoice Date</div>
            <div class="invoice-meta-value">{{ $invoice->paid_date ? $invoice->paid_date->format('d M Y') : 'N/A' }}</div>
        </div>
        <div class="col-3">
            <div class="invoice-meta-label">Due Date</div>
            <div class="invoice-meta-value">Paid on Receipt</div>
        </div>
        <div class="col-3">
            <div class="invoice-meta-label">Payment Status</div>
            <div class="invoice-meta-value text-success">
                <i class="bi bi-check-circle-fill me-1"></i>PAID
            </div>
        </div>
        <div class="col-3">
            <div class="invoice-meta-label">Transaction ID</div>
            <div class="invoice-meta-value text-muted" style="font-size:0.82rem; font-weight:normal;">{{ $invoice->payment?->transaction_id ?? 'N/A' }}</div>
        </div>
    </div>

    {{-- Billing Addresses --}}
    <div class="row mb-5">
        <div class="col-6">
            <div class="invoice-meta-label" style="margin-bottom:8px;">Billed From</div>
            <h6 class="fw-bold mb-1 text-dark">SAKSHAM MARKETING</h6>
            <div class="text-muted" style="font-size:0.85rem; line-height:1.5;">
                Legal Name: PUNEESH NAGPAL<br>
                279B, Sant Nagar, East of Kailash,<br>
                New Delhi - 110065<br>
                PAN: ABOPN8619H<br>
                GSTIN: 07ABOPN8619H1Z5
            </div>
        </div>
        <div class="col-6">
            <div class="invoice-meta-label" style="margin-bottom:8px;">Billed To</div>
            <h6 class="fw-bold mb-1 text-dark">{{ $profile?->company_name ?: ($user?->name ?? 'Subscriber Profile') }}</h6>
            <div class="text-muted" style="font-size:0.85rem; line-height:1.5;">
                {{ $profile?->address ?: 'Subscriber Account Address' }}<br>
                @if($profile?->city){{ $profile->city }}, {{ $profile->state }} @endif {{ $profile?->pincode }}<br>
                {{ $profile?->email_for_inquiries ?: $user->email }}
                @if($profile?->gst_number)
                    <br><strong>GSTIN:</strong> {{ $profile->gst_number }}
                @endif
            </div>
        </div>
    </div>

    {{-- Items breakdown --}}
    <div class="table-responsive mb-4">
        <table class="table table-invoice w-100 mb-0">
            <thead>
                <tr>
                    <th style="width: 60%;">Subscription Item & Term</th>
                    <th class="text-center" style="width: 15%;">Qty</th>
                    <th class="text-end" style="width: 25%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="fw-bold text-dark">{{ $invoice->subscription?->plan?->name ?? 'SaaS Upgrade' }} Subscription Plan</div>
                        <div class="text-muted mt-1" style="font-size:0.78rem;">Full subscriber dashboard features for {{ $invoice->subscription?->plan?->duration_days ?? 30 }} days term. Includes premium PDF generation and dynamic attribute templates.</div>
                    </td>
                    <td class="text-center fw-bold">1</td>
                    <td class="text-end fw-bold text-dark">₹{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pricing block calculations --}}
    <div class="total-box mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:0.88rem;">
            <span class="text-muted">Subtotal:</span>
            <span class="text-dark fw-bold">₹{{ number_format($invoice->subtotal, 2) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3" style="font-size:0.88rem;">
            <span class="text-muted">Taxes & Fees ({{ $invoice->tax > 0 ? '18% GST' : '0%' }}):</span>
            <span class="text-{{ $invoice->tax > 0 ? 'danger' : 'success' }} fw-bold">₹{{ number_format($invoice->tax, 2) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="border-color:#E2E8F0;">
            <span class="fw-bold text-dark fs-5">Total Paid:</span>
            <span class="fw-bold text-primary fs-5" style="font-family:'Outfit',sans-serif;">₹{{ number_format($invoice->total, 2) }}</span>
        </div>
    </div>

    {{-- Footer/Receipt note --}}
    <hr class="mt-5" style="border-color:#F1F5F9;">
    <div class="text-center mt-4">
        <p class="text-muted" style="font-size:0.8rem; line-height: 1.5;">Thank you for your business! If you have any questions or require custom assistance regarding your subscription quotas, contact us at <strong>support@catasky.com</strong>.</p>
    </div>
</div>

</body>
</html>
