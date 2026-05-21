@extends('admin.layouts.app')

@section('title', '— Dashboard |')

@push('css')
<style>
    /* ── Dashboard hero banner ── */
    .dash-hero {
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 60%, #6D28D9 100%);
        border-radius: 20px;
        padding: 32px 36px;
        position: relative;
        overflow: hidden;
        color: white;
        margin-bottom: 28px;
    }
    .dash-hero::before {
        content: '';
        position: absolute;
        top: -80px; right: -80px;
        width: 320px; height: 320px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .dash-hero::after {
        content: '';
        position: absolute;
        bottom: -60px; left: 40%;
        width: 240px; height: 240px;
        background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .dash-hero h2 { font-size: 1.5rem; font-weight: 800; margin-bottom: 6px; }
    .dash-hero p { font-size: 0.9rem; color: rgba(255,255,255,0.75); margin: 0; }

    .hero-quick-actions {
        display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px;
    }
    .hero-action-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 18px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 10px;
        color: white; font-size: 0.82rem; font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .hero-action-btn:hover {
        background: rgba(255,255,255,0.22);
        color: white;
        transform: translateY(-1px);
    }

    /* ── Stat Cards ── */
    .stat-card {
        background: white;
        border: 1px solid #F1F5F9;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.25s ease;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.08);
        border-color: #E2E8F0;
    }
    .stat-icon-wrap {
        width: 52px; height: 52px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; margin-bottom: 16px;
    }
    .stat-label { font-size: 0.75rem; font-weight: 600; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .stat-value { font-size: 2rem; font-weight: 800; color: #1E293B; line-height: 1; font-family: 'Outfit', sans-serif; }
    .stat-trend { display: inline-flex; align-items: center; gap: 4px; font-size: 0.75rem; font-weight: 700; padding: 3px 8px; border-radius: 100px; margin-top: 10px; }
    .stat-trend.up { background: rgba(16,185,129,0.1); color: #10B981; }
    .stat-trend.down { background: rgba(239,68,68,0.1); color: #EF4444; }
    .stat-trend.neutral { background: rgba(100,116,139,0.1); color: #64748B; }

    /* Sparkline placeholder */
    .sparkline-wrap {
        margin-top: 12px; height: 40px;
    }

    /* ── Section cards ── */
    .dash-card {
        background: white;
        border: 1px solid #F1F5F9;
        border-radius: 18px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        overflow: hidden;
        height: 100%;
    }
    .dash-card-header {
        padding: 20px 24px 16px;
        display: flex; justify-content: space-between; align-items: center;
        border-bottom: 1px solid #F8FAFC;
    }
    .dash-card-header h5 { font-size: 1rem; font-weight: 700; color: #1E293B; margin: 0; }
    .dash-card-body { padding: 8px 0; }

    /* Quick Actions Grid */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        padding: 16px;
    }
    .quick-action-item {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 16px;
        background: #F8FAFC;
        border: 1px solid #F1F5F9;
        border-radius: 14px;
        text-decoration: none;
        color: #1E293B;
        font-size: 0.84rem;
        font-weight: 600;
        transition: all 0.22s ease;
    }
    .quick-action-item:hover {
        background: white;
        border-color: #E2E8F0;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        color: #4F46E5;
        transform: translateY(-2px);
    }
    .quick-action-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }

    /* Table styles */
    .table-row-hover tr:hover td { background: #F8FAFC; }
    .enquiry-badge { font-size: 0.7rem; padding: 3px 10px; border-radius: 100px; font-weight: 700; }
    .product-list-item {
        display: flex; align-items: center; gap: 14px;
        padding: 12px 24px;
        border-bottom: 1px solid #F8FAFC;
        transition: background 0.15s ease;
    }
    .product-list-item:last-child { border-bottom: none; }
    .product-list-item:hover { background: #F8FAFC; }
    .product-thumb {
        width: 46px; height: 46px; border-radius: 12px;
        object-fit: cover; flex-shrink: 0;
        border: 1px solid #F1F5F9;
    }
    .product-thumb-placeholder {
        width: 46px; height: 46px; border-radius: 12px;
        background: #F1F5F9; display: flex; align-items: center; justify-content: center;
        color: #CBD5E1; flex-shrink: 0; font-size: 1.1rem;
    }

    /* Chart container */
    .chart-wrap { padding: 16px 24px; }

    /* Catalogue share summary card */
    .share-summary { padding: 16px; display: flex; flex-direction: column; gap: 10px; }
    .share-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 16px; background: #F8FAFC; border-radius: 12px;
        border: 1px solid #F1F5F9;
    }
    .share-row-label { font-size: 0.83rem; font-weight: 600; color: #374151; display: flex; align-items: center; gap: 8px; }
    .share-row-val { font-size: 0.9rem; font-weight: 800; color: #1E293B; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Dashboard Hero Banner ── --}}
    <div class="dash-hero">
        <div style="position:relative; z-index:2;">
            <div style="font-size:0.72rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,0.55);margin-bottom:8px;">
                📅 {{ now()->format('l, d F Y') }}
            </div>
            <h2>Welcome back, {{ auth()->user()->name }}! 👋</h2>
            <p>Here's your catalogue platform overview for today.</p>

            <div class="hero-quick-actions">
                <a href="{{ route('admin.products.create') }}" class="hero-action-btn">
                    <i class="bi bi-plus-circle-fill"></i> Add Product
                </a>
                <a href="{{ route('catalogue') }}" target="_blank" class="hero-action-btn">
                    <i class="bi bi-eye-fill"></i> View Catalogue
                </a>
                @can('view-enquiries')
                <a href="{{ route('admin.enquiries.index') }}" class="hero-action-btn">
                    <i class="bi bi-chat-left-dots-fill"></i>
                    Enquiries
                    @php $unreadNow = \App\Models\Enquiry::where('is_read', false)->count(); @endphp
                    @if($unreadNow > 0)
                        <span style="background:rgba(255,255,255,0.25);padding:1px 8px;border-radius:100px;font-size:0.7rem;">{{ $unreadNow }} new</span>
                    @endif
                </a>
                @endcan
                <a href="{{ route('admin.settings.index') }}" class="hero-action-btn">
                    <i class="bi bi-gear-fill"></i> Settings
                </a>
            </div>
        </div>
    </div>

    {{-- ── Stats Row ── --}}
    <div class="row g-3 mb-4">

        @can('view-products')
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background:rgba(79,70,229,0.08);">
                    <i class="bi bi-box-seam-fill" style="color:#4F46E5;"></i>
                </div>
                <div class="stat-label">Total Products</div>
                <div class="stat-value">{{ number_format($productsCount) }}</div>
                <span class="stat-trend up"><i class="bi bi-arrow-up-short"></i>Active</span>
            </div>
        </div>
        @endcan

        @can('view-brands')
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background:rgba(6,182,212,0.08);">
                    <i class="bi bi-patch-check-fill" style="color:#06B6D4;"></i>
                </div>
                <div class="stat-label">Total Brands</div>
                <div class="stat-value">{{ number_format($brandsCount) }}</div>
                <span class="stat-trend up"><i class="bi bi-arrow-up-short"></i>Growing</span>
            </div>
        </div>
        @endcan

        @can('view-enquiries')
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background:rgba(245,158,11,0.08);">
                    <i class="bi bi-envelope-fill" style="color:#F59E0B;"></i>
                </div>
                <div class="stat-label">Enquiries</div>
                <div class="stat-value">{{ number_format($enquiriesCount) }}</div>
                @php $unread = \App\Models\Enquiry::where('is_read', false)->count(); @endphp
                <span class="stat-trend {{ $unread > 0 ? 'up' : 'neutral' }}">
                    <i class="bi bi-{{ $unread > 0 ? 'circle-fill' : 'check-circle' }}"></i>
                    {{ $unread > 0 ? $unread . ' Unread' : 'All Read' }}
                </span>
            </div>
        </div>
        @endcan

        @can('view-users')
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background:rgba(124,58,237,0.08);">
                    <i class="bi bi-people-fill" style="color:#7C3AED;"></i>
                </div>
                <div class="stat-label">Admin Users</div>
                <div class="stat-value">{{ number_format($usersCount) }}</div>
                <span class="stat-trend neutral"><i class="bi bi-person-check"></i>Active</span>
            </div>
        </div>
        @endcan

    </div>

    {{-- ── Middle Row: Quick Actions + Chart + Recent Products ── --}}
    <div class="row g-3 mb-4">

        {{-- Quick Actions --}}
        <div class="col-lg-4 col-xl-3">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h5><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Actions</h5>
                </div>
                <div class="quick-actions-grid">
                    @can('create-products')
                    <a href="{{ route('admin.products.create') }}" class="quick-action-item">
                        <div class="quick-action-icon" style="background:rgba(79,70,229,0.08);"><i class="bi bi-plus-lg" style="color:#4F46E5;"></i></div>
                        Add Product
                    </a>
                    @endcan
                    @can('view-categories')
                    <a href="{{ route('admin.categories.index') }}" class="quick-action-item">
                        <div class="quick-action-icon" style="background:rgba(6,182,212,0.08);"><i class="bi bi-layers-fill" style="color:#06B6D4;"></i></div>
                        Categories
                    </a>
                    @endcan
                    @can('view-brands')
                    <a href="{{ route('admin.brands.index') }}" class="quick-action-item">
                        <div class="quick-action-icon" style="background:rgba(245,158,11,0.08);"><i class="bi bi-patch-check-fill" style="color:#F59E0B;"></i></div>
                        Brands
                    </a>
                    @endcan
                    @can('import-products')
                    <a href="{{ route('admin.products.import') }}" class="quick-action-item">
                        <div class="quick-action-icon" style="background:rgba(16,185,129,0.08);"><i class="bi bi-file-earmark-arrow-up-fill" style="color:#10B981;"></i></div>
                        Import CSV
                    </a>
                    @endcan
                    <a href="{{ route('catalogue') }}" target="_blank" class="quick-action-item">
                        <div class="quick-action-icon" style="background:rgba(239,68,68,0.08);"><i class="bi bi-file-earmark-pdf-fill" style="color:#EF4444;"></i></div>
                        View PDF
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="quick-action-item">
                        <div class="quick-action-icon" style="background:rgba(100,116,139,0.08);"><i class="bi bi-gear-fill" style="color:#64748B;"></i></div>
                        Settings
                    </a>
                </div>
            </div>
        </div>

        {{-- Catalogue Share Overview --}}
        <div class="col-lg-4 col-xl-3">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h5><i class="bi bi-send-fill text-primary me-2"></i>Catalogue Sharing</h5>
                </div>
                <div class="share-summary">
                    @foreach([
                        ['icon'=>'bi-file-earmark-pdf-fill','color'=>'#EF4444','bg'=>'rgba(239,68,68,0.08)','label'=>'PDFs Generated','val'=>'—'],
                        ['icon'=>'bi-whatsapp','color'=>'#25D366','bg'=>'rgba(37,211,102,0.08)','label'=>'WhatsApp Shares','val'=>'—'],
                        ['icon'=>'bi-link-45deg','color'=>'#4F46E5','bg'=>'rgba(79,70,229,0.08)','label'=>'Catalogue Views','val'=>'—'],
                        ['icon'=>'bi-images','color'=>'#7C3AED','bg'=>'rgba(124,58,237,0.08)','label'=>'Image Cards Sent','val'=>'—'],
                    ] as $row)
                    <div class="share-row">
                        <div class="share-row-label">
                            <div style="width:30px;height:30px;border-radius:8px;background:{{ $row['bg'] }};display:flex;align-items:center;justify-content:center;">
                                <i class="bi {{ $row['icon'] }}" style="color:{{ $row['color'] }};font-size:0.85rem;"></i>
                            </div>
                            {{ $row['label'] }}
                        </div>
                        <div class="share-row-val">{{ $row['val'] }}</div>
                    </div>
                    @endforeach
                    <a href="{{ route('admin.tracking.analytics') }}" style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;background:#F8FAFC;border:1px solid #F1F5F9;border-radius:12px;text-decoration:none;font-size:0.8rem;font-weight:600;color:#4F46E5;transition:all 0.2s;" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='#F8FAFC'">
                        <i class="bi bi-bar-chart-line-fill"></i> Full Analytics
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent Products ── --}}
        @can('view-products')
        <div class="col-lg-4 col-xl-6">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h5><i class="bi bi-box-seam-fill text-success me-2"></i>Recently Added Products</h5>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-light rounded-pill px-3" style="font-size:0.78rem;font-weight:600;">View All</a>
                </div>
                <div class="dash-card-body">
                    @forelse($recentProducts as $product)
                    <div class="product-list-item">
                        @if($product->thumbnail)
                            <img src="{{ asset('uploads/products/'.$product->thumbnail) }}" class="product-thumb" alt="{{ $product->name }}">
                        @else
                            <div class="product-thumb-placeholder"><i class="bi bi-box-seam"></i></div>
                        @endif
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:0.88rem;font-weight:700;color:#1E293B;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $product->name }}</div>
                            <div style="font-size:0.75rem;color:#94A3B8;margin-top:2px;">{{ $product->category->name ?? 'Uncategorized' }} {{ $product->part_code ? '· '.$product->part_code : '' }}</div>
                        </div>
                        <div style="flex-shrink:0;text-align:right;">
                            <span class="badge rounded-pill" style="font-size:0.68rem;padding:4px 10px;font-weight:700;background:{{ $product->status == 1 ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)' }};color:{{ $product->status == 1 ? '#10B981' : '#EF4444' }};">
                                {{ $product->status == 1 ? 'Active' : 'Draft' }}
                            </span>
                            <div style="font-size:0.72rem;color:#CBD5E1;margin-top:3px;">{{ $product->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-box-seam" style="font-size:2.5rem;color:#E2E8F0;"></i>
                        <p style="font-size:0.85rem;color:#94A3B8;margin-top:12px;">No products yet. <a href="{{ route('admin.products.create') }}" style="color:#4F46E5;font-weight:600;">Add your first</a></p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endcan

    </div>

    {{-- ── Bottom Row: Enquiries + Categories Overview ── --}}
    <div class="row g-3">

        {{-- Recent Enquiries --}}
        @can('view-enquiries')
        <div class="col-lg-8">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h5><i class="bi bi-chat-left-dots-fill text-warning me-2"></i>Recent Enquiries</h5>
                    <a href="{{ route('admin.enquiries.index') }}" class="btn btn-sm btn-light rounded-pill px-3" style="font-size:0.78rem;font-weight:600;">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-row-hover">
                        <thead>
                            <tr style="background:#F8FAFC;">
                                <th class="ps-4 border-0 py-3" style="font-size:0.72rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.5px;">Name</th>
                                <th class="border-0 py-3" style="font-size:0.72rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.5px;">Email</th>
                                <th class="border-0 py-3" style="font-size:0.72rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.5px;">Date</th>
                                <th class="border-0 py-3" style="font-size:0.72rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.5px;">Status</th>
                                <th class="border-0 py-3 text-end pe-4" style="font-size:0.72rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.5px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentEnquiries as $enquiry)
                            <tr>
                                <td class="ps-4">
                                    <div style="font-size:0.88rem;font-weight:700;color:#1E293B;">{{ $enquiry->name }}</div>
                                    <div style="font-size:0.75rem;color:#94A3B8;">{{ Str::limit($enquiry->subject, 35) }}</div>
                                </td>
                                <td style="font-size:0.85rem;color:#64748B;">{{ $enquiry->email }}</td>
                                <td style="font-size:0.82rem;color:#94A3B8;">{{ $enquiry->created_at->format('d M Y') }}</td>
                                <td>
                                    <span class="enquiry-badge" style="background:{{ $enquiry->is_read ? 'rgba(100,116,139,0.1)' : 'rgba(79,70,229,0.1)' }};color:{{ $enquiry->is_read ? '#94A3B8' : '#4F46E5' }};">
                                        {{ $enquiry->is_read ? 'Read' : 'New' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.enquiries.show', $enquiry->id) }}" class="btn btn-sm" style="background:#F1F5F9;border:none;border-radius:8px;color:#4F46E5;font-size:0.78rem;font-weight:600;padding:5px 12px;">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5" style="color:#94A3B8;font-size:0.88rem;">
                                    <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:10px;color:#E2E8F0;"></i>
                                    No enquiries found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endcan

        {{-- Categories Overview --}}
        @can('view-categories')
        <div class="col-lg-4">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h5><i class="bi bi-layers-fill text-info me-2"></i>Categories</h5>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-light rounded-pill px-3" style="font-size:0.78rem;font-weight:600;">Manage</a>
                </div>
                <div style="padding: 12px 0;">
                    @forelse(\App\Models\Category::withCount('products')->where('status', 1)->take(6)->get() as $cat)
                    <div style="display:flex;align-items:center;gap:12px;padding:11px 20px;border-bottom:1px solid #F8FAFC;transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(79,70,229,0.07);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-tag-fill" style="color:#4F46E5;font-size:0.9rem;"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.85rem;font-weight:700;color:#1E293B;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $cat->name }}</div>
                        </div>
                        <div style="flex-shrink:0;">
                            <span style="background:#F1F5F9;color:#64748B;font-size:0.72rem;font-weight:700;padding:3px 10px;border-radius:100px;">{{ $cat->products_count }} products</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4" style="color:#94A3B8;font-size:0.85rem;">No categories found</div>
                    @endforelse
                </div>
            </div>
        </div>
        @endcan

    </div>

</div>
@endsection
