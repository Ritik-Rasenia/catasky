<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Curated Selection — {{ config('app.name', 'Catasky') }} B2B</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #4F46E5;
            --primary-dark: #4338CA;
            --primary-gradient: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            --success: #10B981;
            --background: #F8FAFC;
            --text-primary: #111827;
            --text-secondary: #6B7280;
            --border: #E5E7EB;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background);
            color: var(--text-primary);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }

        /* ── Header ─────────────────────────────── */
        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 15px 0;
        }

        /* ── Hero ────────────────────────────────── */
        .hero-curated {
            background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 100%);
            color: white;
            padding: 50px 0;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 30px 30px;
        }

        .hero-curated::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 20%, rgba(6, 182, 212, 0.12) 0%, transparent 50%);
            pointer-events: none;
        }

        .curated-badge {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #38BDF8;
        }

        /* ── Product Cards ───────────────────────── */
        .premium-card {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--border);
            overflow: hidden;
            padding: 15px;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .premium-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px -10px rgba(79, 70, 229, 0.15);
            border-color: rgba(79, 70, 229, 0.2);
        }

        .product-img-container {
            aspect-ratio: 1/1;
            background: #F8FAFC;
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            border: 1px solid #F1F5F9;
        }

        .product-img-container img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        .product-title {
            font-size: 1rem;
            color: var(--text-primary);
            line-height: 1.4;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .price-badge {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--primary);
        }

        .moq-tag {
            font-size: 0.78rem;
            color: var(--text-secondary);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-enquire {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 7px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-enquire:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }

        /* ── Enquiry Modal ───────────────────────── */
        .enquiry-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .enquiry-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .enquiry-modal {
            background: white;
            border-radius: 24px;
            padding: 36px 32px;
            width: 100%;
            max-width: 480px;
            position: relative;
            transform: translateY(20px) scale(0.97);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 25px 60px -10px rgba(15, 23, 42, 0.25);
        }
        .enquiry-overlay.active .enquiry-modal {
            transform: translateY(0) scale(1);
        }

        .modal-close-btn {
            position: absolute;
            top: 16px;
            right: 20px;
            background: #F1F5F9;
            border: none;
            border-radius: 50%;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 1rem;
            color: #64748b;
        }
        .modal-close-btn:hover { background: #E2E8F0; }

        .modal-product-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(79, 70, 229, 0.06);
            border: 1px solid rgba(79, 70, 229, 0.15);
            border-radius: 100px;
            padding: 5px 14px;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .form-control-modal {
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 11px 14px;
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
            outline: none;
            font-family: 'Poppins', sans-serif;
        }
        .form-control-modal:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-label-modal {
            font-size: 0.78rem;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 6px;
            display: block;
        }

        .btn-modal-submit {
            width: 100%;
            padding: 13px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
        }
        .btn-modal-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
        }
        .btn-modal-submit:disabled { opacity: 0.65; cursor: not-allowed; }

        /* ── Toast ───────────────────────────────── */
        .toast-container-custom {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast-custom {
            background: white;
            border-radius: 14px;
            padding: 14px 18px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 280px;
            max-width: 360px;
            animation: toastSlide 0.35s ease forwards;
            border-left: 4px solid var(--success);
        }
        .toast-custom.toast-error { border-left-color: #EF4444; }
        .toast-icon { font-size: 1.25rem; }
        .toast-icon-success { color: var(--success); }
        .toast-icon-error { color: #EF4444; }
        .toast-text { font-size: 0.85rem; font-weight: 500; color: #111827; flex: 1; }
        @keyframes toastSlide {
            from { opacity: 0; transform: translateX(30px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* ── WhatsApp Double-Tick analytics badge ─ */
        .analytics-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 100px;
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #059669;
        }

        /* ── Responsive ──────────────────────────── */
        @media (max-width: 576px) {
            .hero-curated { padding: 35px 0; border-radius: 0 0 20px 20px; }
            .hero-curated h1 { font-size: 1.6rem; }
            .premium-card { padding: 10px; border-radius: 16px; }
            .product-title { font-size: 0.82rem; margin-bottom: 4px; }
            .enquiry-modal { padding: 24px 20px; }
        }
    </style>
</head>
<body>

    <!-- ── Glassmorphic Header ─────────────────────── -->
    <header class="glass-header">
        <div class="container d-flex justify-content-between align-items-center">
            @php
                $settings = \App\Models\Setting::first();
            @endphp
            <a href="{{ url('/catalogue') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                @if($settings && $settings->logo)
                    <img src="{{ asset('uploads/settings/' . $settings->logo) }}" alt="{{ $settings->site_title ?? 'Catasky' }}" style="max-height: 38px; max-width: 120px; object-fit: contain;">
                @else
                    <div style="width:38px;height:38px;background:var(--primary-gradient);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1.25rem;">C</div>
                @endif
                <div>
                    <h5 class="mb-0 text-dark" style="letter-spacing:-0.5px;font-weight:800;line-height:1;">{{ $settings->site_title ?? config('app.name', 'Catasky') }}</h5>
                    <small class="text-secondary" style="font-size:0.65rem;text-transform:uppercase;font-weight:600;letter-spacing:0.5px;">B2B Verified Client</small>
                </div>
            </a>
            <div class="d-flex gap-2 align-items-center">
                <!-- WhatsApp Double-Tick Analytics Badge -->
                <span class="analytics-badge d-none d-sm-inline-flex">
                    <i class="bi bi-check2-all"></i> Delivery Tracked
                </span>
                <a href="{{ url('/catalogue') }}" class="btn btn-sm btn-primary rounded-pill py-2 px-3 fw-bold" style="background:var(--primary-gradient);border:none;">
                    <i class="bi bi-grid-fill me-1"></i><span class="d-none d-sm-inline">Browse Catalogue</span><span class="d-inline d-sm-none">Browse</span>
                </a>
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $share->customer_phone ?? '+919871376205') }}" class="btn btn-sm btn-outline-secondary rounded-pill py-2 px-3 fw-bold">
                    <i class="bi bi-telephone-outbound me-1"></i><span class="d-none d-sm-inline">Call Support</span><span class="d-inline d-sm-none">Call</span>
                </a>
            </div>
        </div>
    </header>

    <!-- ── Hero ───────────────────────────────────── -->
    <section class="hero-curated mb-5">
        <div class="container text-center">
            <div class="curated-badge mb-3">
                <i class="bi bi-shield-check"></i> Exclusively Curated Selection
            </div>
            <h1 class="display-5 fw-bold text-white mb-2" style="letter-spacing:-1px;">Tailored Corporate Proposals</h1>
            <p class="text-white-50 mx-auto" style="max-width:600px;font-size:0.95rem;">
                Review your high-converting product blueprint below. Tap <strong style="color:#a5b4fc;">Enquire</strong> on any item to send a direct inquiry to our dispatch office.
            </p>
            <!-- WhatsApp delivery status indicator -->
            <div class="mt-3 d-flex justify-content-center gap-3 flex-wrap">
                <span class="curated-badge" style="color:#34D399;">
                    <i class="bi bi-check2-all"></i>
                    @if($share->seen_status === 'read')
                        Seen by recipient
                    @elseif($share->delivery_status === 'delivered')
                        Delivered via WhatsApp
                    @else
                        Sent via WhatsApp
                    @endif
                </span>
                @if($share->visit_count > 1)
                <span class="curated-badge" style="color:#FCD34D;">
                    <i class="bi bi-eye-fill"></i> Viewed {{ $share->visit_count }}x
                </span>
                @endif
            </div>
        </div>
    </section>

    <!-- ── Catalogue Grid ─────────────────────────── -->
    <section class="pb-5">
        <div class="container">
            <div class="row g-3 g-sm-4">
                @forelse($products as $product)
                    <div class="col-6 col-sm-6 col-md-6 col-lg-4 col-xl-3">
                        <div class="premium-card">
                            <a href="{{ url('/product/' . $product->slug) }}" class="product-img-container text-decoration-none">
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" loading="lazy">
                            </a>
                            <div class="d-flex flex-column flex-grow-1">
                                <a href="{{ url('/product/' . $product->slug) }}" class="text-decoration-none">
                                    <h6 class="product-title mb-1 text-dark" style="transition:color 0.2s; font-size:0.9rem; font-weight: 700; height: 38px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $product->name }}</h6>
                                </a>
                                <p class="text-secondary small mb-1 flex-grow-1" style="font-size:0.72rem; line-height:1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; max-width: 100%;">
                                    {{ $product->short_description ?? 'Corporate grade customisable item.' }}
                                </p>
                                <div class="moq-tag mb-1.5" style="font-size:0.74rem;">
                                    <i class="bi bi-layers text-primary"></i> {{ $product->part_code ?: 'MOQ: 100 pcs' }}
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-1">
                                    <div class="price-badge">
                                        @if($product->price)
                                            ₹{{ number_format($product->price, 2) }}
                                        @else
                                            <span style="font-size:0.8rem;font-weight:600;color:#6B7280;">On Request</span>
                                        @endif
                                    </div>
                                    <button
                                        class="btn-enquire"
                                        onclick="openEnquiryModal('{{ addslashes($product->name) }}', {{ $product->id }}, '{{ addslashes($product->part_code ?? '') }}')"
                                    >
                                        <i class="bi bi-send-fill"></i> Enquire
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-inbox text-secondary display-1 opacity-25"></i>
                        <h4 class="fw-bold mt-3">Selection Unavailable</h4>
                        <p class="text-secondary">Please check with your B2B representative for a revised catalogue link.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ── Enquiry Modal ──────────────────────────── -->
    <div class="enquiry-overlay" id="enquiryOverlay" onclick="handleOverlayClick(event)">
        <div class="enquiry-modal" id="enquiryModal">
            <button class="modal-close-btn" onclick="closeEnquiryModal()" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>

            <div class="text-center mb-3">
                <div style="width:52px;height:52px;background:var(--primary-gradient);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;color:white;font-size:1.4rem;margin-bottom:12px;">
                    <i class="bi bi-send-fill"></i>
                </div>
                <h5 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">Send Product Inquiry</h5>
                <p class="text-secondary small mb-0">Our corporate team will respond within 24 hours</p>
            </div>

            <div class="modal-product-pill" id="modalProductPill">
                <i class="bi bi-box-seam"></i>
                <span id="modalProductName">Product</span>
            </div>

            <form id="enquiryForm" novalidate>
                @csrf
                <input type="hidden" name="product_id" id="modalProductId">
                <input type="hidden" name="subject" id="modalSubject">

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label-modal">Your Name *</label>
                        <input type="text" name="name" id="modalName" class="form-control-modal" placeholder="Full name" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label-modal">Email *</label>
                        <input type="email" name="email" id="modalEmail" class="form-control-modal" placeholder="your@email.com" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label-modal">Phone *</label>
                        <input type="text" name="phone" id="modalPhone" class="form-control-modal" placeholder="+91 XXXXX XXXXX" value="{{ $share->customer_phone ?? '' }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label-modal">Message *</label>
                        <textarea name="message" id="modalMessage" class="form-control-modal" rows="3" placeholder="Quantity needed, delivery location, special requirements..." required style="resize:vertical;"></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-modal-submit mt-4" id="modalSubmitBtn">
                    <i class="bi bi-send-fill"></i> Submit Inquiry
                </button>
            </form>
        </div>
    </div>

    <!-- ── Toast Container ───────────────────────── -->
    <div class="toast-container-custom" id="toastContainer"></div>

    <!-- ── Scripts ───────────────────────────────── -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── CSRF Token from meta tag ─────────────────
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // ── Enquiry Routes from Laravel ──────────────
        const ENQUIRY_URL = "{{ route('enquiry.submit') }}";

        // ── Heartbeat ───────────────────────────────
        const CATALOGUE_CODE = "{{ $share->catalogue_code }}";
        const HEARTBEAT_URL  = "{{ route('doubletick.heartbeat') }}";

        setInterval(function() {
            fetch(HEARTBEAT_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ code: CATALOGUE_CODE })
            }).catch(() => {});
        }, 5000);

        // ── Toast Helper ─────────────────────────────
        function showToast(message, isError = false) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast-custom' + (isError ? ' toast-error' : '');
            toast.innerHTML = `
                <i class="bi ${isError ? 'bi-x-circle-fill toast-icon toast-icon-error' : 'bi-check-circle-fill toast-icon toast-icon-success'}"></i>
                <span class="toast-text">${message}</span>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(30px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 350);
            }, 4000);
        }

        // ── Modal Open/Close ─────────────────────────
        function openEnquiryModal(productName, productId, partCode) {
            document.getElementById('modalProductName').textContent = productName;
            document.getElementById('modalProductId').value = productId;
            document.getElementById('modalSubject').value = 'Enquiry: ' + productName + (partCode ? ' [' + partCode + ']' : '');
            document.getElementById('modalMessage').value = '';

            const overlay = document.getElementById('enquiryOverlay');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Focus first empty input
            setTimeout(() => {
                const nameInput = document.getElementById('modalName');
                if (!nameInput.value) nameInput.focus();
            }, 300);
        }

        function closeEnquiryModal() {
            const overlay = document.getElementById('enquiryOverlay');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        function handleOverlayClick(e) {
            if (e.target === document.getElementById('enquiryOverlay')) {
                closeEnquiryModal();
            }
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeEnquiryModal();
        });

        // ── Enquiry AJAX Submit ──────────────────────
        document.getElementById('enquiryForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('modalSubmitBtn');
            const name    = document.getElementById('modalName').value.trim();
            const email   = document.getElementById('modalEmail').value.trim();
            const phone   = document.getElementById('modalPhone').value.trim();
            const message = document.getElementById('modalMessage').value.trim();

            if (!name || !email || !phone || !message) {
                showToast('Please fill in all required fields.', true);
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Submitting...';

            const formData = new FormData(this);

            try {
                const res = await fetch(ENQUIRY_URL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: formData
                });

                const data = await res.json();

                if (data.success) {
                    closeEnquiryModal();
                    showToast(data.message || 'Inquiry submitted successfully!');
                    this.reset();
                    // Re-fill phone after reset
                    document.getElementById('modalPhone').value = "{{ $share->customer_phone ?? '' }}";
                } else {
                    showToast(data.message || 'Something went wrong. Please try again.', true);
                }
            } catch (err) {
                showToast('Network error. Please check your connection and try again.', true);
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill"></i> Submit Inquiry';
        });
    </script>

    <style>
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .spin { display: inline-block; animation: spin 0.8s linear infinite; }
    </style>
</body>
</html>
