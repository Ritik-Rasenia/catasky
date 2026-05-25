<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $product->name }} Specifications</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #334155;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        .header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .logo-text {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
        }
        .meta-date {
            float: right;
            font-size: 12px;
            color: #64748b;
            margin-top: 10px;
        }
        .product-title {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 5px;
        }
        .sku-badge {
            display: inline-block;
            background-color: #f1f5f9;
            color: #475569;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .main-content {
            margin-bottom: 30px;
        }
        .product-image-container {
            float: left;
            width: 40%;
            text-align: center;
        }
        .product-image {
            max-width: 100%;
            max-height: 250px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .product-details {
            float: right;
            width: 55%;
        }
        .price-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .price-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
        }
        .price-value {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
        }
        .clear {
            clear: both;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .spec-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .spec-table th, .spec-table td {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            text-align: left;
        }
        .spec-table th {
            background-color: #f8fafc;
            font-weight: bold;
            width: 30%;
        }
        .footer {
            margin-top: 50px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            text-align: center;
        }
        .qr-section {
            float: right;
            text-align: right;
            width: 30%;
        }
        .qr-code {
            width: 100px;
            height: 100px;
        }
        .qr-text {
            font-size: 10px;
            color: #64748b;
            margin-top: 5px;
            text-align: right;
        }
        .disclaimer-section {
            float: left;
            width: 65%;
            text-align: left;
            font-size: 11px;
            color: #94a3b8;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <span class="meta-date">Date: {{ date('d-m-Y') }}</span>
        <span class="logo-text">CATASKY</span>
    </div>

    <div class="main-content">
        <div class="product-image-container">
            @if($product->thumbnail_url)
                <!-- DomPDF requires absolute image paths or public urls. Our model provides full URL links. -->
                <img src="{{ $product->thumbnail_url }}" class="product-image" alt="{{ $product->name }}">
            @else
                <div style="width:100%; height:200px; background-color:#f1f5f9; line-height:200px; color:#94a3b8; font-weight:bold; border-radius:8px;">No Product Image</div>
            @endif
        </div>

        <div class="product-details">
            <h1 class="product-title">{{ $product->name }}</h1>
            <div class="sku-badge">SKU: {{ $product->sku ?: $product->part_code }}</div>

            <div class="price-box">
                <div class="price-label">Corporate B2B Quotation</div>
                @if($product->price)
                    <div class="price-value">
                        INR {{ number_format($product->price, 2) }} 
                        @if($product->sale_price)
                            <span style="font-size:14px; text-decoration:line-through; color:#94a3b8; font-weight:normal; margin-left:8px;">INR {{ number_format($product->sale_price, 2) }}</span>
                        @endif
                    </div>
                @else
                    <div class="price-value" style="color: #64748b; font-size: 16px;">Price on Request</div>
                @endif
                <div style="font-size: 11px; color:#64748b; margin-top:5px;">
                    @if($product->tax)
                        Includes {{ $product->tax }}% B2B Tax. 
                    @endif
                    Stock Available: {{ $product->stock }} units.
                </div>
            </div>

            @if($product->short_description)
                <p style="font-style: italic; color:#475569;">{{ $product->short_description }}</p>
            @endif
        </div>
        <div class="clear"></div>
    </div>

    @if($product->description)
        <div class="section-title">Product Description</div>
        <p style="color:#475569; white-space: pre-wrap;">{{ $product->description }}</p>
    @endif

    @php
        $specs = [];
        if($product->specifications) {
            $specs = json_decode($product->specifications, true) ?: [];
        }
    @endphp

    @if(!empty($specs))
        <div class="section-title">Technical Specifications</div>
        <table class="spec-table">
            <tbody>
                @foreach($specs as $key => $value)
                    <tr>
                        <th>{{ $key }}</th>
                        <td>{{ $value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <div class="disclaimer-section">
            <p><strong>Disclaimer:</strong> This B2B datasheet is auto-generated by the Catasky platform. Product packaging, colors, custom brand mockup styles, and volume bulk discounts are subject to confirmation. Scan the QR code to connect directly with our dispatch desk.</p>
        </div>
        <div class="qr-section">
            @if($qrCodeBase64)
                <img src="data:image/png;base64,{{ $qrCodeBase64 }}" class="qr-code" alt="QR Code">
                <div class="qr-text">Scan to view live specification</div>
            @endif
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>
