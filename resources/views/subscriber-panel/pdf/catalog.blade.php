<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $brandColor = $template?->brand_color ?? $profile?->primary_color ?? '#4F46E5';
        $accentColor = $template?->accent_color ?? $profile?->secondary_color ?? '#7C3AED';
        $companyName = $profile?->company_name ?? ($subscriber?->name ?? 'Catalog PDF');
        
        $logoPath = null;
        if (($template?->show_logo ?? true) && $profile?->logo && file_exists(public_path('uploads/subscriber-logos/' . $profile->logo))) {
            $logoPath = public_path('uploads/subscriber-logos/' . $profile->logo);
        }

        $watermarkText = $template?->watermark_text ?: $companyName;
        $showWatermark = $template?->show_watermark ?? false;
        
        $publicUrl = route('subscriber.share.public', $link->token);
        $qrCodeUrl = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=" . urlencode($publicUrl);
    @endphp
    <title>{{ $link->title }} | {{ $companyName }}</title>
    
    <style>
        @page {
            margin: 80px 45px 70px 45px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.5;
            position: relative;
        }

        /* ─── Watermark ─── */
        @if($showWatermark)
        .watermark {
            position: fixed;
            top: 40%;
            left: 5%;
            width: 90%;
            text-align: center;
            opacity: 0.04;
            transform: rotate(-35deg);
            transform-origin: 50% 50%;
            font-size: 55px;
            font-weight: bold;
            color: #000000;
            z-index: -1000;
        }
        @endif

        /* ─── Header & Footer (DomPDF Fixed Elements) ─── */
        .pdf-header {
            position: fixed;
            top: -60px;
            left: 0px;
            right: 0px;
            height: 50px;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 10px;
        }

        .pdf-header table {
            width: 100%;
        }

        .pdf-header-title {
            font-size: 10px;
            color: #64748B;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.05em;
        }

        .pdf-footer {
            position: fixed;
            bottom: -50px;
            left: 0px;
            right: 0px;
            height: 40px;
            border-top: 1px solid #E2E8F0;
            padding-top: 10px;
            font-size: 10px;
            color: #64748B;
        }

        .pdf-footer table {
            width: 100%;
        }

        .page-number:after {
            content: counter(page);
        }

        /* ─── Typography & Utilities ─── */
        h1, h2, h3, h4, h5, h6 {
            color: #0F172A;
            margin-top: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        .text-primary { color: {{ $brandColor }}; }
        .text-muted { color: #64748B; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .w-100 { width: 100%; }
        .page-break { page-break-after: always; }
        
        table {
            border-collapse: collapse;
        }

        /* ─── Cover Page ─── */
        .cover-container {
            padding-top: 120px;
            text-align: center;
        }

        .cover-logo-box {
            margin-bottom: 30px;
        }

        .cover-logo {
            max-height: 80px;
            max-width: 250px;
        }

        .cover-fallback-logo {
            width: 70px;
            height: 70px;
            border-radius: 14px;
            background-color: {{ $brandColor }};
            color: white;
            font-size: 36px;
            font-weight: bold;
            line-height: 70px;
            margin: 0 auto;
            text-align: center;
        }

        .cover-title {
            font-size: 32px;
            font-weight: bold;
            color: #0F172A;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .cover-subtitle {
            font-size: 16px;
            color: #64748B;
            margin-bottom: 60px;
        }

        .cover-info-box {
            border-top: 2px solid {{ $brandColor }};
            background-color: #F8FAFC;
            padding: 30px;
            border-radius: 12px;
            margin: 0 auto;
            max-width: 500px;
            text-align: left;
        }

        .cover-info-box td {
            padding: 6px 0;
            font-size: 12px;
        }

        .cover-qr-box {
            margin-top: 60px;
            text-align: center;
        }

        .cover-qr-img {
            width: 100px;
            height: 100px;
            border: 1px solid #CBD5E1;
            padding: 6px;
            border-radius: 8px;
            background: white;
        }

        .cover-qr-text {
            font-size: 10px;
            color: #64748B;
            margin-top: 8px;
        }

        /* ─── Product Catalog Elements ─── */
        .product-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #0F172A;
            border-bottom: 2px solid {{ $brandColor }};
            padding-bottom: 8px;
        }

        .product-sku {
            font-size: 11px;
            color: #64748B;
            margin-bottom: 15px;
        }

        .product-price-box {
            background-color: #F1F5F9;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: inline-block;
        }

        .mrp-price {
            font-size: 12px;
            color: #64748B;
            text-decoration: line-through;
            margin-right: 15px;
        }

        .offer-price {
            font-size: 18px;
            font-weight: bold;
            color: {{ $brandColor }};
        }

        .product-thumbnail {
            width: 100%;
            max-height: 240px;
            object-fit: contain;
            border: 1px solid #E2E8F0;
        }

        .spec-table {
            width: 100%;
            margin-top: 15px;
        }

        .spec-table th {
            text-align: left;
            background-color: #F8FAFC;
            color: #0F172A;
            font-size: 11px;
            padding: 8px 12px;
            border-bottom: 2px solid #E2E8F0;
            text-transform: uppercase;
        }

        .spec-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 12px;
        }

        .spec-label {
            color: #64748B;
            font-weight: 500;
            width: 40%;
        }

        .spec-value {
            color: #0F172A;
            font-weight: bold;
        }

        .color-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 3px;
            border: 1px solid #CBD5E1;
            margin-right: 6px;
            vertical-align: middle;
        }

        .image-gallery-grid {
            margin-top: 20px;
        }

        .gallery-thumb {
            width: 30%;
            height: 88px;
            object-fit: cover;
            border: 1px solid #E2E8F0;
            margin-right: 3%;
            display: inline-block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

{{-- Watermark --}}
@if($showWatermark)
    <div class="watermark">{{ $watermarkText }}</div>
@endif

{{-- Header & Footer --}}
<div class="pdf-header">
    <table>
        <tr>
            <td class="pdf-header-title">
                {{ $template?->header_text ?: $companyName . ' — Product Catalogue' }}
            </td>
            <td class="text-right pdf-header-title">
                {{ date('d-M-Y') }}
            </td>
        </tr>
    </table>
</div>

<div class="pdf-footer">
    <table>
        <tr>
            <td>
                {{ $template?->footer_text ?: 'Generated via CataSky. All rights reserved.' }}
            </td>
            @if($template?->show_page_numbers ?? true)
                <td class="text-right" style="width: 100px;">
                    Page <span class="page-number"></span>
                </td>
            @endif
        </tr>
    </table>
</div>

{{-- ─── COVER PAGE (Only for Catalog Shares) ─── --}}
@if(!$product)
    <div class="cover-container">
        <div class="cover-logo-box">
            @if($logoPath)
                <img class="cover-logo" src="{{ $logoPath }}" alt="Logo">
            @else
                <div class="cover-fallback-logo">{{ strtoupper(substr($companyName, 0, 1)) }}</div>
            @endif
        </div>

        <h1 class="cover-title">{{ $link->title }}</h1>
        <div class="cover-subtitle">Product Catalogue</div>

        <div class="cover-info-box">
            <h3 style="margin-bottom: 15px; font-size: 14px; border-bottom: 1px solid #CBD5E1; padding-bottom: 6px; color: {{ $brandColor }}">SUBSCRIBER PROFILE</h3>
            <table class="w-100">
                <tr>
                    <td style="color:#64748B; width: 120px;">Company:</td>
                    <td style="font-weight:bold;">{{ $companyName }}</td>
                </tr>
                @if($profile?->email_for_inquiries || $subscriber?->email)
                <tr>
                    <td style="color:#64748B;">Email:</td>
                    <td>{{ $profile?->email_for_inquiries ?: $subscriber->email }}</td>
                </tr>
                @endif
                @if($profile?->phone)
                <tr>
                    <td style="color:#64748B;">Phone:</td>
                    <td>{{ $profile->phone }}</td>
                </tr>
                @endif
                @if($profile?->website)
                <tr>
                    <td style="color:#64748B;">Website:</td>
                    <td>{{ $profile->website }}</td>
                </tr>
                @endif
                @if($profile?->address)
                <tr>
                    <td style="color:#64748B; vertical-align: top;">Address:</td>
                    <td>{{ $profile->address }}, {{ $profile->city }}, {{ $profile->state }}</td>
                </tr>
                @endif
            </table>
        </div>

        @if($template?->show_qr_code ?? true)
            <div class="cover-qr-box">
                <img src="{{ $qrCodeUrl }}" class="cover-qr-img" alt="QR Code">
                <div class="cover-qr-text">Scan to view active interactive details and pricing online</div>
            </div>
        @endif
    </div>

    {{-- Insert page break to start list --}}
    <div class="page-break"></div>
@endif


{{-- ─── PRODUCT ENTRIES ─── --}}
@php
    $productsList = $product ? collect([$product]) : $catalogProducts;
@endphp

@foreach($productsList as $index => $prod)
    @php
        $prodSettings = $settings; // Respect sharing settings
        $showMrp = $prodSettings['show_mrp'] ?? true;
        $showOffer = $prodSettings['show_offer_price'] ?? true;
        $showDesc = $prodSettings['show_description'] ?? true;
        $showAttrs = $prodSettings['show_attributes'] ?? true;
        $showImages = $prodSettings['show_images'] ?? true;
        
        $prodLogoPath = null;
        if (($template?->show_logo ?? true) && $profile?->logo && file_exists(public_path('uploads/subscriber-logos/' . $profile->logo))) {
            $prodLogoPath = public_path('uploads/subscriber-logos/' . $profile->logo);
        }

        $prodThumbPath = null;
        if ($prod->thumbnail && filter_var($prod->thumbnail, FILTER_VALIDATE_URL)) {
            $prodThumbPath = $prod->share_image_url;
        } elseif ($prod->thumbnail && file_exists(public_path('uploads/subscriber-products/' . $prod->thumbnail))) {
            $prodThumbPath = public_path('uploads/subscriber-products/' . $prod->thumbnail);
        } elseif ($prod->thumbnail && str_starts_with($prod->thumbnail, 'uploads/') && file_exists(public_path($prod->thumbnail))) {
            $prodThumbPath = public_path($prod->thumbnail);
        }
    @endphp

    {{-- Each product page layout --}}
    <div class="product-page" style="margin-top: 15px;">
        
        {{-- Branded header for single pages --}}
        @if($product)
        <table class="w-100" style="margin-bottom: 25px;">
            <tr>
                <td style="vertical-align: middle;">
                    @if($prodLogoPath)
                        <img src="{{ $prodLogoPath }}" style="max-height: 45px;" alt="Logo">
                    @else
                        <span style="font-size: 18px; font-weight: bold; color: {{ $brandColor }}">{{ $companyName }}</span>
                    @endif
                </td>
                <td class="text-right" style="vertical-align: middle; font-size: 11px; color:#64748B;">
                    <strong>{{ $companyName }}</strong><br>
                    {{ $profile?->phone }} | {{ $profile?->email_for_inquiries ?: $subscriber->email }}
                </td>
            </tr>
        </table>
        @endif

        <table class="w-100" style="margin-bottom: 20px;">
            <tr>
                {{-- Product Information (Left) --}}
                <td style="width: 55%; vertical-align: top; padding-right: 25px;">
                    <div class="product-title">{{ $prod->name }}</div>
                    @if($prod->sku)
                        <div class="product-sku">SKU: <strong>{{ $prod->sku }}</strong></div>
                    @endif

                    {{-- Pricing --}}
                    @if(($prod->offer_price && $showOffer) || ($prod->mrp && $showMrp))
                        <div class="product-price-box">
                            @if($prod->offer_price && $showOffer)
                                <span class="offer-price">₹{{ number_format($prod->offer_price, 2) }}</span>
                                @if($prod->mrp && $showMrp)
                                    <span class="mrp-price">₹{{ number_format($prod->mrp, 2) }}</span>
                                @endif
                            @elseif($prod->mrp && $showMrp)
                                <span class="offer-price" style="color: #0F172A;">₹{{ number_format($prod->mrp, 2) }}</span>
                            @endif
                        </div>
                    @else
                        <div style="font-style: italic; color:#64748B; margin-bottom: 15px;">Contact subscriber for pricing</div>
                    @endif

                    {{-- Short description --}}
                    @if($prod->short_description && $showDesc)
                        <h4 style="margin-top: 15px; margin-bottom: 6px; font-size: 13px; color: {{ $brandColor }}">Overview</h4>
                        <p style="margin-bottom: 15px; color:#475569; font-size:12px; line-height: 1.6;">{{ $prod->short_description }}</p>
                    @endif

                    {{-- Full description --}}
                    @if($prod->full_description && $showDesc)
                        <h4 style="margin-top: 15px; margin-bottom: 6px; font-size: 13px; color: {{ $brandColor }}">Description</h4>
                        <p style="margin-bottom: 20px; color:#475569; font-size:12px; line-height: 1.6;">{!! nl2br(e($prod->full_description)) !!}</p>
                    @endif
                </td>

                {{-- Product Thumbnail (Right) --}}
                <td style="width: 45%; vertical-align: top; text-align: center;">
                    @if($prodThumbPath)
                        <img class="product-thumbnail" src="{{ $prodThumbPath }}" alt="Thumbnail">
                    @else
                        <div style="width:100%; height:220px; background-color:#F1F5F9; border:1px solid #CBD5E1; line-height:220px; font-size:22px; color:#94A3B8;">No image</div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- Technical Specifications (Attributes) --}}
        @if($prod->attributeValues->count() > 0 && $showAttrs)
            @php
                $validAttrs = $prod->attributeValues->filter(fn($val) => $val->attribute?->show_in_pdf);
            @endphp
            
            @if($validAttrs->count() > 0)
                <div style="margin-top: 10px;">
                    <table class="spec-table">
                        <thead>
                            <tr>
                                <th colspan="2" style="background-color: {{ $brandColor }}; color: white;">Product Specifications</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($validAttrs as $val)
                                @php
                                    $displayVal = $val->value;
                                    if ($val->attribute?->type === 'multiselect' || $val->attribute?->type === 'checkbox') {
                                        $arr = json_decode($val->value, true) ?? [$val->value];
                                        $displayVal = implode(', ', $arr);
                                    }
                                @endphp
                                <tr>
                                    <td class="spec-label">{{ $val->attribute?->name }}</td>
                                    <td class="spec-value">
                                        @if($val->attribute?->type === 'color')
                                            <span class="color-dot" style="background-color: {{ $displayVal }};"></span>
                                            {{ $displayVal }}
                                        @elseif($val->attribute?->type === 'url')
                                            {{ $displayVal }}
                                        @else
                                            {{ $displayVal }} {{ $val->attribute?->unit }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif

        {{-- Product Images Grid --}}
        @if($prod->images->count() > 0 && $showImages)
            <div style="margin-top: 25px;">
                <h4 style="font-size: 13px; color: {{ $brandColor }}; margin-bottom: 10px;">Product Gallery</h4>
                <div class="image-gallery-grid">
                    @foreach($prod->images as $img)
                        @php
                            $imgLocalPath = null;
                            if (filter_var($img->image_path, FILTER_VALIDATE_URL)) {
                                $imgLocalPath = $img->preview_url;
                            } elseif (file_exists(public_path('uploads/subscriber-products/' . $img->image_path))) {
                                $imgLocalPath = public_path('uploads/subscriber-products/' . $img->image_path);
                            } elseif (str_starts_with($img->image_path, 'uploads/') && file_exists(public_path($img->image_path))) {
                                $imgLocalPath = public_path($img->image_path);
                            }
                        @endphp
                        @if($imgLocalPath)
                            <img class="gallery-thumb" src="{{ $imgLocalPath }}" alt="">
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    {{-- Insert page break between products, except the last one --}}
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif

@endforeach

</body>
</html>
