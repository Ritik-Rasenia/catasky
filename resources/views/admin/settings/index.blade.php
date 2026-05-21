@extends('admin.layouts.app')

@section('title', 'Catalogue Theme & Settings')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold mb-1">Catalogue Customization</h3>
            <p class="text-muted">Configure the visual identity and experience of your premium catalogue.</p>
        </div>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">
            <!-- Left Column: Theme & Identity -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-4">General Settings</h5>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Site Title</label>
                        <input type="text" name="site_title" class="form-control border-0 bg-light p-3 rounded-4" value="{{ $setting->site_title }}">
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted">Main Logo</label>
                            <div class="border rounded-4 p-3 text-center bg-light mb-2">
                                @if($setting->logo)
                                    <img src="{{ asset('uploads/settings/'.$setting->logo) }}" class="mb-3" style="max-height: 40px;">
                                @else
                                    <i class="bi bi-image fs-4 text-muted mb-2 d-block"></i>
                                @endif
                                <input type="file" name="logo" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted">Favicon</label>
                            <div class="border rounded-4 p-3 text-center bg-light mb-2">
                                @if($setting->favicon)
                                    <img src="{{ asset('uploads/settings/'.$setting->favicon) }}" class="mb-3" style="max-height: 40px;">
                                @else
                                    <i class="bi bi-star fs-4 text-muted mb-2 d-block"></i>
                                @endif
                                <input type="file" name="favicon" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Brand Color</label>
                        <div class="d-flex align-items-center gap-3 bg-light p-3 rounded-4">
                            <input type="color" name="primary_color" class="form-control form-control-color border-0 bg-transparent" value="{{ $setting->primary_color ?? '#4F46E5' }}">
                            <div class="flex-grow-1">
                                <div class="fw-bold small">{{ $setting->primary_color ?? '#4F46E5' }}</div>
                                <div class="text-muted extra-small">Primary brand color for buttons and highlights</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Typography</label>
                        <select name="font_family" class="form-select border-0 bg-light p-3 rounded-4 fw-medium">
                            <option value="Poppins" {{ ($setting->font_family ?? '') == 'Poppins' ? 'selected' : '' }}>Poppins (Modern & Round)</option>
                            <option value="Inter" {{ ($setting->font_family ?? '') == 'Inter' ? 'selected' : '' }}>Inter (Clean & Corporate)</option>
                            <option value="Outfit" {{ ($setting->font_family ?? '') == 'Outfit' ? 'selected' : '' }}>Outfit (Premium & Sleek)</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-4 mb-4">
                        <div>
                            <div class="fw-bold small">Enable Watermark</div>
                            <div class="text-muted extra-small">Apply watermark to product images in PDF</div>
                        </div>
                        <div class="form-check form-switch fs-4">
                            <input class="form-check-input" type="checkbox" name="use_watermark" value="1" {{ ($setting->use_watermark ?? 0) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-4">Contact Details</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Support Email</label>
                            <input type="email" name="email" class="form-control border-0 bg-light p-3 rounded-4" value="{{ $setting->email }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Contact Phone</label>
                            <input type="text" name="phone" class="form-control border-0 bg-light p-3 rounded-4" value="{{ $setting->phone }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Office Address</label>
                            <textarea name="address" rows="2" class="form-control border-0 bg-light p-3 rounded-4">{{ $setting->address }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-4">Social Media Links</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Facebook</label>
                            <input type="url" name="facebook" class="form-control border-0 bg-light p-3 rounded-4" value="{{ $setting->facebook }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Twitter / X</label>
                            <input type="url" name="twitter" class="form-control border-0 bg-light p-3 rounded-4" value="{{ $setting->twitter }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Instagram</label>
                            <input type="url" name="instagram" class="form-control border-0 bg-light p-3 rounded-4" value="{{ $setting->instagram }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">LinkedIn</label>
                            <input type="url" name="linkedin" class="form-control border-0 bg-light p-3 rounded-4" value="{{ $setting->linkedin }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">YouTube</label>
                            <input type="url" name="youtube" class="form-control border-0 bg-light p-3 rounded-4" value="{{ $setting->youtube }}">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-4">Home Page SEO</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Meta Description</label>
                        <textarea name="site_description" rows="3" class="form-control border-0 bg-light p-3 rounded-4">{{ $setting->site_description }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Meta Keywords</label>
                        <textarea name="meta_keywords" rows="2" class="form-control border-0 bg-light p-3 rounded-4" placeholder="keyword1, keyword2, ...">{{ $setting->meta_keywords }}</textarea>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-4">PDF Cover Style</h5>
                    <div class="row g-3">
                        <div class="col-4">
                            <label class="cover-style-option">
                                <input type="radio" name="pdf_cover_style" value="minimal" {{ ($setting->pdf_cover_style ?? '') == 'minimal' ? 'checked' : '' }}>
                                <div class="style-box p-3 text-center rounded-3 border">
                                    <div class="fw-bold small mb-1">Minimalist</div>
                                    <div class="text-muted extra-small">Clean & white</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-4">
                            <label class="cover-style-option">
                                <input type="radio" name="pdf_cover_style" value="professional" {{ ($setting->pdf_cover_style ?? '') == 'professional' ? 'checked' : '' }}>
                                <div class="style-box p-3 text-center rounded-3 border">
                                    <div class="fw-bold small mb-1">Professional</div>
                                    <div class="text-muted extra-small">Classic blue</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-4">
                            <label class="cover-style-option">
                                <input type="radio" name="pdf_cover_style" value="modern" {{ ($setting->pdf_cover_style ?? '') == 'modern' ? 'checked' : '' }}>
                                <div class="style-box p-3 text-center rounded-3 border">
                                    <div class="fw-bold small mb-1">Modern</div>
                                    <div class="text-muted extra-small">Gradients</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Preview -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 20px;">
                    <h5 class="fw-bold mb-4">Live Preview</h5>
                    <div class="preview-mockup border rounded-4 overflow-hidden shadow-sm bg-white">
                        <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="fw-bold" style="color: {{ $setting->primary_color ?? '#4F46E5' }}">Catalogue</div>
                            <div class="d-flex gap-2">
                                <div class="bg-light rounded-circle" style="width: 12px; height: 12px;"></div>
                                <div class="bg-light rounded-circle" style="width: 12px; height: 12px;"></div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="ratio ratio-4x3 bg-light rounded-4 mb-3 d-flex align-items-center justify-content-center">
                                <img src="https://placehold.co/400x300/4F46E5/FFF?text=PDF+Cover+Preview" class="img-fluid rounded-4">
                            </div>
                            <div class="h6 fw-bold mb-2">Corporate Collection 2024</div>
                            <div class="text-muted extra-small mb-4">Curated for excellence and brand impact.</div>
                            <button class="btn w-100 py-3 rounded-pill fw-bold text-white shadow-sm" style="background: {{ $setting->primary_color ?? '#4F46E5' }}">Download Preview</button>
                        </div>
                    </div>
                    
                    <div class="mt-5 d-grid">
                        <button type="submit" class="btn btn-primary py-3 rounded-pill fw-bold shadow">
                            <i class="bi bi-save me-2"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .cover-style-option input { display: none; }
    .cover-style-option .style-box { cursor: pointer; transition: 0.2s; background: #fff; }
    .cover-style-option input:checked + .style-box {
        background: rgba(79, 70, 229, 0.05);
        border-color: #4F46E5 !important;
        color: #4F46E5;
    }
    .extra-small { font-size: 0.75rem; }
    .form-control-color { width: 50px; height: 50px; padding: 0; }
</style>
@endsection
