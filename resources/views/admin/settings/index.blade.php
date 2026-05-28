@extends('admin.layouts.app')

@section('title', 'General Settings')

@section('content')
@php
    $logoUrl = $setting->logo ? asset('uploads/settings/'.$setting->logo) : null;
    $footerLogoUrl = $setting->footer_logo ? asset('uploads/settings/'.$setting->footer_logo) : $logoUrl;
    $faviconUrl = $setting->favicon ? asset('uploads/settings/'.$setting->favicon) : asset('uploads/fav.png');
    $primary = $setting->primary_color ?? '#4F46E5';
@endphp

<div class="settings-page">
    <div class="settings-hero mb-4">
        <div>
            <span class="settings-kicker">System Configuration</span>
            <h1>General Settings</h1>
            <p>Manage SEO, contact details, social links, logos, favicon, and PDF branding from one place.</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" class="btn btn-light rounded-pill px-4 fw-bold ">
            <i class="bi bi-box-arrow-up-right me-2"></i> View Site
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4 border-0 ">
            <div class="fw-bold mb-1">Please fix these fields</div>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-4 align-items-start">
            <div class="col-xl-8">
                <div class="settings-card">
                    <div class="settings-card-head">
                        <div>
                            <h5>Brand Identity</h5>
                            <p>These assets are used across admin, frontend, PDFs, login, and browser tabs.</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Site Title</label>
                            <input type="text" name="site_title" class="form-control settings-input" value="{{ old('site_title', $setting->site_title) }}" placeholder="Catasky">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Admin Email</label>
                            <input type="email" name="admin_email" class="form-control settings-input" value="{{ old('admin_email', $setting->admin_email) }}" placeholder="admin@example.com">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label">Main Logo</label>
                            <div class="asset-box">
                                <div class="asset-preview">
                                    @if($logoUrl)
                                        <img src="{{ $logoUrl }}" alt="Main logo">
                                    @else
                                        <i class="bi bi-cloud-upload"></i>
                                    @endif
                                </div>
                                <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Footer Logo</label>
                            <div class="asset-box">
                                <div class="asset-preview">
                                    @if($footerLogoUrl)
                                        <img src="{{ $footerLogoUrl }}" alt="Footer logo">
                                    @else
                                        <i class="bi bi-image"></i>
                                    @endif
                                </div>
                                <input type="file" name="footer_logo" class="form-control form-control-sm" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Favicon</label>
                            <div class="asset-box">
                                <div class="asset-preview favicon-preview">
                                    <img src="{{ $faviconUrl }}" alt="Favicon">
                                </div>
                                <input type="file" name="favicon" class="form-control form-control-sm" accept="image/*,.ico">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="settings-card-head">
                        <div>
                            <h5>SEO Description</h5>
                            <p>Controls homepage meta description and keywords used by search engines.</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta / Site Description</label>
                        <textarea name="site_description" rows="4" class="form-control settings-input" placeholder="Premium B2B catalogue platform...">{{ old('site_description', $setting->site_description) }}</textarea>
                    </div>
                    <div>
                        <label class="form-label">Meta Keywords</label>
                        <textarea name="meta_keywords" rows="2" class="form-control settings-input" placeholder="catalogue, corporate gifts, b2b, whatsapp sharing">{{ old('meta_keywords', $setting->meta_keywords) }}</textarea>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="settings-card-head">
                        <div>
                            <h5>Contact Details</h5>
                            <p>Used in footer, contact surfaces, and catalogue communication.</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Support Email</label>
                            <input type="email" name="email" class="form-control settings-input" value="{{ old('email', $setting->email) }}" placeholder="support@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="phone" class="form-control settings-input" value="{{ old('phone', $setting->phone) }}" placeholder="+91 98765 43210">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Office Address</label>
                            <textarea name="address" rows="3" class="form-control settings-input" placeholder="Office address">{{ old('address', $setting->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="settings-card-head">
                        <div>
                            <h5>Social Media Links</h5>
                            <p>Only filled links appear in the frontend footer.</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        @foreach([
                            'facebook' => ['Facebook', 'bi-facebook'],
                            'twitter' => ['Twitter / X', 'bi-twitter-x'],
                            'instagram' => ['Instagram', 'bi-instagram'],
                            'linkedin' => ['LinkedIn', 'bi-linkedin'],
                            'youtube' => ['YouTube', 'bi-youtube'],
                        ] as $field => [$label, $icon])
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</label>
                                <input type="url" name="{{ $field }}" class="form-control settings-input" value="{{ old($field, $setting->{$field}) }}" placeholder="https://">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="settings-card sticky-xl-top" style="top: 92px;">
                    <div class="settings-card-head">
                        <div>
                            <h5>Theme & PDF</h5>
                            <p>Brand colors and PDF watermark assets.</p>
                        </div>
                    </div>

                    <label class="form-label">Brand Color</label>
                    <div class="color-control mb-3">
                        <input type="color" name="primary_color" value="{{ old('primary_color', $primary) }}">
                        <div>
                            <div class="fw-bold">{{ old('primary_color', $primary) }}</div>
                            <div class="text-muted small">Primary buttons and accents</div>
                        </div>
                    </div>

                    <label class="form-label">Secondary Color</label>
                    <div class="color-control mb-3">
                        <input type="color" name="secondary_color" value="{{ old('secondary_color', $setting->secondary_color ?? '#7C3AED') }}">
                        <div>
                            <div class="fw-bold">{{ old('secondary_color', $setting->secondary_color ?? '#7C3AED') }}</div>
                            <div class="text-muted small">Gradients and secondary accents</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Typography</label>
                        <select name="font_family" class="form-select settings-input">
                            @foreach(['Poppins' => 'Poppins (Modern)', 'Inter' => 'Inter (Corporate)', 'Outfit' => 'Outfit (Premium)'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('font_family', $setting->font_family) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Watermark Logo</label>
                        <div class="asset-box">
                            <div class="asset-preview">
                                @if($setting->watermark)
                                    <img src="{{ asset('uploads/settings/'.$setting->watermark) }}" alt="Watermark">
                                @else
                                    <i class="bi bi-droplet-half"></i>
                                @endif
                            </div>
                            <input type="file" name="watermark" class="form-control form-control-sm" accept="image/*">
                        </div>
                    </div>

                    <label class="form-label">PDF Cover Style</label>
                    <div class="cover-style-grid mb-4">
                        @foreach([
                            'minimal' => ['Minimal', 'Clean white'],
                            'professional' => ['Professional', 'Classic blue'],
                            'modern' => ['Modern', 'Gradients'],
                        ] as $value => [$label, $sub])
                            <label class="cover-style-option">
                                <input type="radio" name="pdf_cover_style" value="{{ $value }}" @checked(old('pdf_cover_style', $setting->pdf_cover_style ?? 'modern') === $value)>
                                <span>
                                    <strong>{{ $label }}</strong>
                                    <small>{{ $sub }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="preview-card mb-4">
                        <div class="preview-top">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Logo">
                            @else
                                <span>{{ Str::substr($siteTitle ?? 'C', 0, 1) }}</span>
                            @endif
                            <strong>{{ $setting->site_title ?? 'Catasky' }}</strong>
                        </div>
                        <div class="preview-cover" style="background: linear-gradient(135deg, {{ $primary }}, {{ $setting->secondary_color ?? '#7C3AED' }});">
                            PDF Cover Preview
                        </div>
                        <p>{{ Str::limit($setting->site_description ?? 'Premium catalogue preview', 78) }}</p>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 fw-bold">
                        <i class="bi bi-check2-circle me-2"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .settings-page { max-width: 1280px; }
    .settings-hero {
        border-radius: 22px;
        padding: 26px 30px;
        color: #fff;
        background: linear-gradient(135deg, #4F46E5, #7C3AED);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        box-shadow: 0 18px 40px rgba(79,70,229,0.22);
    }
    .settings-hero h1 { margin: 4px 0; font-weight: 900; letter-spacing: -0.03em; }
    .settings-hero p { margin: 0; color: rgba(255,255,255,0.78); }
    .settings-kicker { font-size: 0.72rem; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.65); }
    .settings-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 22px;
        box-shadow: 0 8px 26px rgba(15,23,42,0.05);
    }
    .settings-card-head { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
    .settings-card-head h5 { margin: 0; font-weight: 900; letter-spacing: -0.02em; }
    .settings-card-head p { margin: 4px 0 0; color: #64748B; font-size: 0.86rem; }
    .settings-page .form-label { color: #475569; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 800; }
    .settings-input { border: 0; background: #F8FAFC; border-radius: 14px; padding: 13px 15px; font-weight: 600; }
    .asset-box { border: 1px solid #E5E7EB; background: #F8FAFC; border-radius: 16px; padding: 14px; }
    .asset-preview { height: 82px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; color: #94A3B8; }
    .asset-preview img { max-width: 100%; max-height: 62px; object-fit: contain; }
    .asset-preview i { font-size: 1.8rem; }
    .favicon-preview img { max-height: 42px; }
    .color-control { display: flex; align-items: center; gap: 14px; border: 1px solid #E5E7EB; background: #F8FAFC; border-radius: 16px; padding: 12px; }
    .color-control input { width: 54px; height: 54px; padding: 0; border: 0; background: transparent; }
    .cover-style-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .cover-style-option input { display: none; }
    .cover-style-option span { min-height: 82px; border: 1px solid #E5E7EB; border-radius: 14px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; cursor: pointer; transition: 0.18s ease; }
    .cover-style-option small { color: #64748B; font-size: 0.72rem; margin-top: 4px; }
    .cover-style-option input:checked + span { border-color: #4F46E5; background: rgba(79,70,229,0.06); color: #4F46E5; }
    .preview-card { border: 1px solid #E5E7EB; border-radius: 18px; padding: 16px; }
    .preview-top { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .preview-top img { max-width: 92px; max-height: 34px; object-fit: contain; }
    .preview-top span { width: 34px; height: 34px; border-radius: 10px; background: #4F46E5; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 900; }
    .preview-cover { min-height: 150px; border-radius: 16px; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 900; }
    .preview-card p { margin: 12px 0 0; color: #64748B; font-size: 0.82rem; }
    @media (max-width: 767.98px) {
        .settings-hero { align-items: flex-start; flex-direction: column; padding: 22px; }
        .cover-style-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection
