@extends('admin.layouts.app')

@section('title', 'Pending Approvals')
@section('page-title', 'Pending Approvals')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a> &nbsp;/&nbsp;
    <a href="{{ route('admin.saas.approvals.index') }}">SaaS Management</a> &nbsp;/&nbsp;
    <span>Approvals</span>
@endsection

@push('css')
<style>
/* ============================================================
   APPROVALS CENTER — PREMIUM REDESIGN
   ============================================================ */

/* ── Stat Cards ────────────────────────────────────────────── */
.ap-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}
.ap-stat-card {
    background: var(--surface-color);
    border: 1px solid var(--border-color);
    border-radius: 18px;
    padding: 20px 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--card-shadow);
    transition: transform .22s, box-shadow .22s;
    position: relative;
    overflow: hidden;
}
.ap-stat-card::after {
    content: '';
    position: absolute;
    right: -20px; top: -20px;
    width: 90px; height: 90px;
    border-radius: 50%;
    opacity: .06;
}
.ap-stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.1); }
.ap-stat-icon {
    width: 52px; height: 52px;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.ap-stat-num {
    font-family: 'Outfit', sans-serif;
    font-size: 1.9rem;
    font-weight: 800;
    line-height: 1;
    color: var(--text-color);
}
.ap-stat-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted);
    margin-top: 2px;
    text-transform: uppercase;
    letter-spacing: .06em;
}

/* colour themes */
.ap-stat-card.purple .ap-stat-icon { background: rgba(99,102,241,.12); color: #4F46E5; }
.ap-stat-card.purple::after         { background: #4F46E5; }
.ap-stat-card.emerald .ap-stat-icon { background: rgba(16,185,129,.12); color: #059669; }
.ap-stat-card.emerald::after        { background: #059669; }
.ap-stat-card.amber .ap-stat-icon   { background: rgba(245,158,11,.12); color: #D97706; }
.ap-stat-card.amber::after          { background: #D97706; }
.ap-stat-card.rose .ap-stat-icon    { background: rgba(239,68,68,.12); color: #DC2626; }
.ap-stat-card.rose::after           { background: #DC2626; }

/* ── Tab Bar ───────────────────────────────────────────────── */
.ap-tab-bar {
    display: flex;
    gap: 6px;
    background: var(--surface-color);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 6px;
    margin-bottom: 20px;
    overflow-x: auto;
    scrollbar-width: none;
}
.ap-tab-bar::-webkit-scrollbar { display: none; }
.ap-tab-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 20px;
    border-radius: 12px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-weight: 700;
    font-size: .85rem;
    font-family: 'Outfit', sans-serif;
    cursor: pointer;
    white-space: nowrap;
    transition: all .2s;
}
.ap-tab-btn:hover { background: rgba(99,102,241,.06); color: #4F46E5; }
.ap-tab-btn.active {
    background: white;
    color: #4F46E5;
    box-shadow: 0 2px 14px rgba(99,102,241,.15), 0 1px 4px rgba(0,0,0,.06);
}
html[data-theme="dark"] .ap-tab-btn.active { background: #1e293b; }
.ap-tab-icon {
    width: 32px; height: 32px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem;
    transition: transform .2s;
}
.ap-tab-btn.active .ap-tab-icon { transform: scale(1.1); }
.ti-purple { background: rgba(99,102,241,.1);  color: #4F46E5; }
.ti-emerald { background: rgba(16,185,129,.1); color: #059669; }
.ti-amber  { background: rgba(245,158,11,.1);  color: #D97706; }
.ti-rose   { background: rgba(239,68,68,.1);   color: #DC2626; }
.ap-tab-badge {
    font-size: .65rem;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 100px;
    background: rgba(99,102,241,.1);
    color: #4F46E5;
    border: 1px solid rgba(99,102,241,.2);
    min-width: 20px;
    text-align: center;
}
.ap-tab-badge.zero { background: var(--surface-muted); color: var(--text-muted); border-color: transparent; }

/* ── Panel Card ────────────────────────────────────────────── */
.ap-panel {
    background: var(--surface-color);
    border: 1px solid var(--border-color);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
}
.ap-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-color);
    background: var(--surface-muted);
    flex-wrap: wrap;
    gap: 10px;
}
.ap-panel-title {
    display: flex; align-items: center; gap: 12px;
}
.ap-panel-title-text { font-size: .95rem; font-weight: 800; color: var(--text-color); font-family:'Outfit',sans-serif; }
.ap-panel-subtitle   { font-size: .72rem; color: var(--text-muted); margin-top: 1px; }

/* ── Table ─────────────────────────────────────────────────── */
.ap-tbl { width: 100%; border-collapse: collapse; }
.ap-tbl thead th {
    padding: 11px 16px;
    background: var(--surface-muted);
    font-size: .7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border-color);
    white-space: nowrap;
}
.ap-tbl tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
    font-size: .875rem;
    color: var(--text-color);
}
.ap-tbl tbody tr:last-child td { border-bottom: none; }
.ap-tbl tbody tr:hover td { background: rgba(99,102,241,.02); }

/* ── Thumbnail ─────────────────────────────────────────────── */
.ap-thumb {
    width: 44px; height: 44px;
    border-radius: 11px;
    object-fit: cover;
    border: 1px solid var(--border-color);
    flex-shrink: 0;
}
.ap-thumb-ph {
    width: 44px; height: 44px;
    border-radius: 11px;
    background: var(--surface-muted);
    border: 1px solid var(--border-color);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-muted);
    font-size: 1.1rem;
    flex-shrink: 0;
}

/* ── Status badges ─────────────────────────────────────────── */
.sb { padding: 4px 10px; border-radius: 100px; font-size: .72rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
.sb-pending  { background: rgba(245,158,11,.1);  color: #D97706; border: 1px solid rgba(245,158,11,.2); }
.sb-approved { background: rgba(16,185,129,.1);  color: #059669; border: 1px solid rgba(16,185,129,.2); }
.sb-otp      { background: rgba(99,102,241,.1);  color: #4F46E5; border: 1px solid rgba(99,102,241,.2); }

/* ── Action buttons ────────────────────────────────────────── */
.btn-ap-view {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 13px; border-radius: 8px; font-size: .78rem; font-weight: 700;
    border: 1.5px solid var(--border-color); background: var(--surface-color);
    color: var(--text-muted); cursor: pointer; transition: all .18s; white-space: nowrap;
}
.btn-ap-view:hover { border-color: #4F46E5; color: #4F46E5; background: rgba(99,102,241,.05); }
.btn-ap-approve {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px; border-radius: 8px; font-size: .78rem; font-weight: 700;
    border: none; background: linear-gradient(135deg,#10B981,#059669);
    color: white; cursor: pointer; transition: all .18s; white-space: nowrap;
    box-shadow: 0 2px 8px rgba(16,185,129,.25);
}
.btn-ap-approve:hover { box-shadow: 0 4px 16px rgba(16,185,129,.4); transform: translateY(-1px); }
.btn-ap-reject {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 13px; border-radius: 8px; font-size: .78rem; font-weight: 700;
    border: 1.5px solid rgba(239,68,68,.3); background: rgba(239,68,68,.05);
    color: #DC2626; cursor: pointer; transition: all .18s; white-space: nowrap;
}
.btn-ap-reject:hover { background: rgba(239,68,68,.12); border-color: #DC2626; }

/* ── Empty State ───────────────────────────────────────────── */
.ap-empty {
    text-align: center; padding: 64px 24px;
}
.ap-empty-icon {
    width: 72px; height: 72px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
    font-size: 1.8rem;
}
.ap-empty h6  { font-family:'Outfit',sans-serif; font-weight:800; color:var(--text-color); margin-bottom:5px; }
.ap-empty p   { color: var(--text-muted); font-size: .82rem; margin: 0; }

/* ── View Detail Modal ─────────────────────────────────────── */
.vd-modal .modal-content { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,.18); }
.vd-modal-header {
    padding: 24px 26px 0;
    display: flex; align-items: center; gap: 14px;
    border: none;
}
.vd-modal-body { padding: 20px 26px 26px; }
.vd-modal-footer { padding: 0 26px 24px; border: none; display: flex; gap: 10px; justify-content: flex-end; }
.vd-row {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 11px 0; border-bottom: 1px solid var(--border-color);
    font-size: .875rem;
}
.vd-row:last-child { border-bottom: none; }
.vd-label { font-weight: 700; color: var(--text-muted); width: 130px; flex-shrink: 0; font-size:.8rem; }
.vd-val   { color: var(--text-color); flex: 1; word-break: break-word; }

@media (max-width: 900px) {
    .ap-stat-grid { grid-template-columns: repeat(2,1fr); }
}
@media (max-width: 560px) {
    .ap-stat-grid { grid-template-columns: 1fr 1fr; }
    .ap-tab-btn   { padding: 9px 14px; font-size: .8rem; }
    .ap-tbl thead th, .ap-tbl tbody td { padding: 10px 12px; }
}
</style>
@endpush

@section('content')

{{-- ── Stat Summary Cards ───────────────────────────────────── --}}
<div class="ap-stat-grid">
    <div class="ap-stat-card purple">
        <div class="ap-stat-icon"><i class="bi bi-shield-lock-fill"></i></div>
        <div>
            <div class="ap-stat-num">{{ count($pendingAccounts) }}</div>
            <div class="ap-stat-label">Account Approvals</div>
        </div>
    </div>
    <div class="ap-stat-card emerald">
        <div class="ap-stat-icon"><i class="bi bi-shop"></i></div>
        <div>
            <div class="ap-stat-num">{{ count($pendingStores) }}</div>
            <div class="ap-stat-label">Store Approvals</div>
        </div>
    </div>
    <div class="ap-stat-card amber">
        <div class="ap-stat-icon"><i class="bi bi-sliders"></i></div>
        <div>
            <div class="ap-stat-num">{{ count($pendingAttributes) }}</div>
            <div class="ap-stat-label">Attribute Approvals</div>
        </div>
    </div>
</div>

{{-- ── Tab Bar ───────────────────────────────────────────────── --}}
<div class="ap-tab-bar" id="approvalTabBar">
    <button type="button" class="ap-tab-btn active" data-tab="accounts">
        <span class="ap-tab-icon ti-purple"><i class="bi bi-shield-lock-fill"></i></span>
        Account
        <span class="ap-tab-badge {{ !count($pendingAccounts) ? 'zero' : '' }}">{{ count($pendingAccounts) }}</span>
    </button>
    <button type="button" class="ap-tab-btn" data-tab="stores">
        <span class="ap-tab-icon ti-emerald"><i class="bi bi-shop"></i></span>
        Store
        <span class="ap-tab-badge {{ !count($pendingStores) ? 'zero' : '' }}">{{ count($pendingStores) }}</span>
    </button>
    <button type="button" class="ap-tab-btn" data-tab="attributes">
        <span class="ap-tab-icon ti-amber"><i class="bi bi-sliders"></i></span>
        Custom Attribute
        <span class="ap-tab-badge {{ !count($pendingAttributes) ? 'zero' : '' }}">{{ count($pendingAttributes) }}</span>
    </button>
</div>

{{-- ╔══════════════════════════════════════════════════════════╗
     ║  TAB 1 — Account Approvals                              ║
     ╚══════════════════════════════════════════════════════════╝ --}}
<div class="ap-panel" id="tab-accounts">
    <div class="ap-panel-header">
        <div class="ap-panel-title">
            <span class="ap-tab-icon ti-purple" style="width:40px;height:40px;border-radius:12px;font-size:1.1rem;"><i class="bi bi-shield-lock-fill"></i></span>
            <div>
                <div class="ap-panel-title-text">Compliance Accounts Queue</div>
                <div class="ap-panel-subtitle">Stage 1 — OTP-verified subscribers awaiting compliance review</div>
            </div>
        </div>
        <span class="sb sb-pending"><i class="bi bi-hourglass-split"></i> {{ count($pendingAccounts) }} Pending</span>
    </div>

    @if(count($pendingAccounts))
    <div class="table-responsive">
        <table class="ap-tbl">
            <thead>
                <tr>
                    <th class="ps-4">#</th>
                    <th>Company</th>
                    <th>Owner</th>
                    <th>OTP</th>
                    <th>Registered</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingAccounts as $i => $profile)
                <tr>
                    <td class="ps-4 text-muted" style="font-size:.75rem;">{{ $i + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="ap-thumb-ph"><i class="bi bi-building"></i></div>
                            <div>
                                <div class="fw-bold">{{ $profile->company_name }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $profile->phone ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $profile->user->name ?? '—' }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $profile->user->email ?? '' }}</div>
                    </td>
                    <td><span class="sb sb-otp"><i class="bi bi-shield-fill-check"></i>Verified</span></td>
                    <td class="text-muted" style="font-size:.8rem;">
                        {{ $profile->created_at->format('M d, Y') }}
                        <div style="font-size:.68rem;">{{ $profile->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                            <button class="btn-ap-action btn-ap-view" title="View Details"
                                onclick="openAccountModal(
                                    '{{ $profile->id }}',
                                    '{{ addslashes($profile->company_name) }}',
                                    '{{ addslashes($profile->user->name ?? '') }}',
                                    '{{ addslashes($profile->user->email ?? '') }}',
                                    '{{ addslashes($profile->phone ?? '') }}',
                                    '{{ addslashes(($profile->address ?? '').", ".($profile->city ?? '').", ".($profile->state ?? '')) }}',
                                    '{{ $profile->created_at->format("M d, Y") }}'
                                )">
                                <i class="bi bi-eye"></i>
                            </button>
                            <form action="{{ route('admin.saas.approvals.account.approve', $profile->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-ap-action btn-ap-approve" title="Approve"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <form action="{{ route('admin.saas.approvals.account.reject', $profile->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-ap-action btn-ap-reject" title="Reject"><i class="bi bi-x-lg"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="ap-empty">
        <div class="ap-empty-icon" style="background:rgba(99,102,241,.07);color:#A5B4FC;"><i class="bi bi-shield-check"></i></div>
        <h6>All Accounts Cleared</h6>
        <p>No pending account compliance reviews at this time.</p>
    </div>
    @endif
</div>

{{-- ╔══════════════════════════════════════════════════════════╗
     ║  TAB 2 — Store Approvals                                ║
     ╚══════════════════════════════════════════════════════════╝ --}}
<div class="ap-panel" id="tab-stores" style="display:none;">
    <div class="ap-panel-header">
        <div class="ap-panel-title">
            <span class="ap-tab-icon ti-emerald" style="width:40px;height:40px;border-radius:12px;font-size:1.1rem;"><i class="bi bi-shop"></i></span>
            <div>
                <div class="ap-panel-title-text">Store Configuration Queue</div>
                <div class="ap-panel-subtitle">Stage 2 — Approved accounts awaiting store setup verification</div>
            </div>
        </div>
        <span class="sb sb-pending"><i class="bi bi-hourglass-split"></i> {{ count($pendingStores) }} Pending</span>
    </div>

    @if(count($pendingStores))
    <div class="table-responsive">
        <table class="ap-tbl">
            <thead>
                <tr>
                    <th class="ps-4">#</th>
                    <th>Store</th>
                    <th>GSTIN</th>
                    <th>Website</th>
                    <th>Address</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingStores as $i => $profile)
                <tr>
                    <td class="ps-4 text-muted" style="font-size:.75rem;">{{ $i + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            @if($profile->logo)
                                <img src="{{ $profile->logo_url }}" class="ap-thumb" alt="Logo">
                            @else
                                <div class="ap-thumb-ph"><i class="bi bi-shop"></i></div>
                            @endif
                            <div>
                                <div class="fw-bold">{{ $profile->company_name }}</div>
                                <div class="text-muted" style="font-size:.72rem;">/store/{{ $profile->company_slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="sb sb-pending">{{ $profile->gst_number ?? 'N/A' }}</span>
                    </td>
                    <td style="font-size:.8rem;">
                        @if($profile->website)
                            <a href="{{ $profile->website }}" target="_blank" class="text-primary"><i class="bi bi-link-45deg"></i> Visit</a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted" style="font-size:.8rem;">
                        {{ $profile->city ?? '' }}{{ $profile->city && $profile->state ? ', ' : '' }}{{ $profile->state ?? '' }}
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                            <button class="btn-ap-action btn-ap-view" title="View Details"
                                onclick="openStoreModal(
                                    '{{ $profile->id }}',
                                    '{{ addslashes($profile->company_name) }}',
                                    '{{ addslashes($profile->company_slug) }}',
                                    '{{ addslashes($profile->gst_number ?? "") }}',
                                    '{{ addslashes($profile->website ?? "") }}',
                                    '{{ addslashes(($profile->address ?? "").($profile->city ? ", ".$profile->city : "").($profile->state ? ", ".$profile->state : "")) }}',
                                    '{{ addslashes($profile->primary_color ?? "#6366f1") }}',
                                    '{{ addslashes($profile->secondary_color ?? "#10b981") }}'
                                )">
                                <i class="bi bi-eye"></i>
                            </button>
                            <form action="{{ route('admin.saas.approvals.store.approve', $profile->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-ap-action btn-ap-approve" title="Approve"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <form action="{{ route('admin.saas.approvals.store.reject', $profile->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-ap-action btn-ap-reject" title="Reject"><i class="bi bi-x-lg"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="ap-empty">
        <div class="ap-empty-icon" style="background:rgba(16,185,129,.07);color:#6EE7B7;"><i class="bi bi-shop"></i></div>
        <h6>All Stores Cleared</h6>
        <p>No pending store configuration reviews at this time.</p>
    </div>
    @endif
</div>

{{-- ╔══════════════════════════════════════════════════════════╗
     ║  TAB 3 — Custom Attribute Approvals                     ║
     ╚══════════════════════════════════════════════════════════╝ --}}
<div class="ap-panel" id="tab-attributes" style="display:none;">
    <div class="ap-panel-header">
        <div class="ap-panel-title">
            <span class="ap-tab-icon ti-amber" style="width:40px;height:40px;border-radius:12px;font-size:1.1rem;"><i class="bi bi-sliders"></i></span>
            <div>
                <div class="ap-panel-title-text">Custom Attributes Queue</div>
                <div class="ap-panel-subtitle">Subscriber-created custom attributes awaiting approval to promote to global</div>
            </div>
        </div>
        <span class="sb sb-pending"><i class="bi bi-hourglass-split"></i> {{ count($pendingAttributes) }} Pending</span>
    </div>

    @if(count($pendingAttributes))
    <div class="table-responsive">
        <table class="ap-tbl">
            <thead>
                <tr>
                    <th class="ps-4">#</th>
                    <th>Attribute</th>
                    <th>Type</th>
                    <th>Group</th>
                    <th>Subscriber / Store</th>
                    <th>Created</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingAttributes as $i => $attr)
                <tr>
                    <td class="ps-4 text-muted" style="font-size:.75rem;">{{ $i + 1 }}</td>
                    <td>
                        <div class="fw-bold">{{ $attr->name }}</div>
                        <div class="text-muted" style="font-size:.72rem;">Slug: {{ $attr->slug }}</div>
                    </td>
                    <td>
                        <span class="badge bg-info-soft">{{ \App\Models\Attribute::TYPES[$attr->type] ?? $attr->type }}</span>
                    </td>
                    <td>
                        <span class="text-muted">{{ $attr->group->name ?? 'None' }}</span>
                    </td>
                    <td>
                        <div class="fw-semibold" style="font-size:.85rem;">{{ $attr->subscriber->subscriberProfile->company_name ?? 'Subscriber' }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $attr->subscriber->name ?? '' }}</div>
                    </td>
                    <td class="text-muted" style="font-size:.8rem;">
                        {{ $attr->created_at->format('M d, Y') }}
                        <div style="font-size:.68rem;">{{ $attr->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                            <form action="{{ route('admin.attributes.approve', $attr->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-ap-action btn-ap-approve" title="Approve"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <form action="{{ route('admin.attributes.reject', $attr->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-ap-action btn-ap-reject" title="Reject"><i class="bi bi-x-lg"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="ap-empty">
        <div class="ap-empty-icon" style="background:rgba(245,158,11,.07);color:#FCD34D;"><i class="bi bi-sliders"></i></div>
        <h6>All Attributes Cleared</h6>
        <p>No custom attribute requests in the queue.</p>
    </div>
    @endif
</div>


{{-- ╔══════════════════════════════════════════════════════════╗
     ║  VIEW MODALS                                             ║
     ╚══════════════════════════════════════════════════════════╝ --}}

{{-- Account Modal --}}
<div class="modal fade vd-modal" id="modalAccount" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content">
            <div class="vd-modal-header">
                <span class="ap-tab-icon ti-purple" style="width:46px;height:46px;border-radius:14px;font-size:1.2rem;flex-shrink:0;"><i class="bi bi-shield-lock-fill"></i></span>
                <div>
                    <h5 class="fw-bold mb-0" id="modal-acct-heading" style="font-family:'Outfit',sans-serif;">Account Details</h5>
                    <div class="text-muted" style="font-size:.75rem;">Stage 1 Compliance Review</div>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="vd-modal-body">
                <div class="vd-row"><span class="vd-label"><i class="bi bi-building me-1"></i>Company</span><span class="vd-val fw-bold" id="ma-company">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-person me-1"></i>Owner</span><span class="vd-val" id="ma-owner">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-envelope me-1"></i>Email</span><span class="vd-val" id="ma-email">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-telephone me-1"></i>Phone</span><span class="vd-val" id="ma-phone">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-geo-alt me-1"></i>Address</span><span class="vd-val" id="ma-address">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-calendar me-1"></i>Registered</span><span class="vd-val" id="ma-date">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-shield-check me-1"></i>OTP Status</span><span class="vd-val"><span class="sb sb-otp"><i class="bi bi-shield-fill-check"></i>Verified</span></span></div>
            </div>
            <div class="vd-modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <form id="ma-approve-form" method="POST" class="d-inline">@csrf
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="bi bi-check-lg me-1"></i>Approve Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Store Modal --}}
<div class="modal fade vd-modal" id="modalStore" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content">
            <div class="vd-modal-header">
                <span class="ap-tab-icon ti-emerald" style="width:46px;height:46px;border-radius:14px;font-size:1.2rem;flex-shrink:0;"><i class="bi bi-shop"></i></span>
                <div>
                    <h5 class="fw-bold mb-0" id="modal-store-heading" style="font-family:'Outfit',sans-serif;">Store Details</h5>
                    <div class="text-muted" style="font-size:.75rem;">Stage 2 Store Configuration Review</div>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="vd-modal-body">
                <div class="vd-row"><span class="vd-label"><i class="bi bi-shop me-1"></i>Store Name</span><span class="vd-val fw-bold" id="ms-name">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-link me-1"></i>Slug</span><span class="vd-val" id="ms-slug">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-receipt me-1"></i>GSTIN</span><span class="vd-val" id="ms-gst">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-globe me-1"></i>Website</span><span class="vd-val"><a id="ms-web" href="#" target="_blank" class="text-primary">—</a></span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-geo-alt me-1"></i>Address</span><span class="vd-val" id="ms-address">—</span></div>
                <div class="vd-row">
                    <span class="vd-label"><i class="bi bi-palette me-1"></i>Brand Colors</span>
                    <span class="vd-val d-flex gap-2 align-items-center">
                        <span id="ms-primary-chip" class="badge" style="font-size:.75rem;">—</span>
                        <span id="ms-secondary-chip" class="badge" style="font-size:.75rem;">—</span>
                    </span>
                </div>
            </div>
            <div class="vd-modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <form id="ms-approve-form" method="POST" class="d-inline">@csrf
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="bi bi-check-lg me-1"></i>Approve Store</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Product Modal --}}
<div class="modal fade vd-modal" id="modalProduct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content">
            <div class="vd-modal-header">
                <span class="ap-tab-icon ti-amber" style="width:46px;height:46px;border-radius:14px;font-size:1.2rem;flex-shrink:0;"><i class="bi bi-box-seam-fill"></i></span>
                <div>
                    <h5 class="fw-bold mb-0" id="modal-prod-heading" style="font-family:'Outfit',sans-serif;">Product Details</h5>
                    <div class="text-muted" style="font-size:.75rem;">Catalogue Approval Review</div>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="vd-modal-body">
                <div id="mp-thumb-wrap" class="mb-3 text-center d-none">
                    <img id="mp-thumb" src="" alt="Product" style="height:130px;border-radius:14px;object-fit:cover;border:1px solid var(--border-color);">
                </div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-tag me-1"></i>Product Name</span><span class="vd-val fw-bold" id="mp-name">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-upc-scan me-1"></i>Part Code</span><span class="vd-val" id="mp-code">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-layers me-1"></i>Category</span><span class="vd-val" id="mp-cat">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-shop me-1"></i>Store</span><span class="vd-val" id="mp-store">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-person me-1"></i>Owner</span><span class="vd-val" id="mp-owner">—</span></div>
                <div class="vd-row"><span class="vd-label"><i class="bi bi-calendar me-1"></i>Created</span><span class="vd-val" id="mp-date">—</span></div>
            </div>
            <div class="vd-modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
/* ── Tab switching ───────────────────────────────────────── */
document.querySelectorAll('.ap-tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const target = this.dataset.tab;

        // deactivate all buttons
        document.querySelectorAll('.ap-tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // hide all panels
        ['accounts','stores','attributes'].forEach(t => {
            const el = document.getElementById('tab-' + t);
            if (el) el.style.display = 'none';
        });

        // show selected
        const panel = document.getElementById('tab-' + target);
        if (panel) panel.style.display = '';

        // persist to sessionStorage
        sessionStorage.setItem('approvals-tab', target);
    });
});

// Restore last active tab on page load
const saved = sessionStorage.getItem('approvals-tab');
if (saved) {
    const btn = document.querySelector(`.ap-tab-btn[data-tab="${saved}"]`);
    if (btn) btn.click();
}

/* ── Account Modal ──────────────────────────────────────── */
function openAccountModal(id, company, owner, email, phone, address, date) {
    document.getElementById('modal-acct-heading').textContent = company;
    document.getElementById('ma-company').textContent  = company;
    document.getElementById('ma-owner').textContent    = owner   || '—';
    document.getElementById('ma-email').textContent    = email   || '—';
    document.getElementById('ma-phone').textContent    = phone   || '—';
    document.getElementById('ma-address').textContent  = address || '—';
    document.getElementById('ma-date').textContent     = date;
    document.getElementById('ma-approve-form').action  =
        '{{ url("dashboard/saas/approvals/account") }}/' + id + '/approve';
    new bootstrap.Modal(document.getElementById('modalAccount')).show();
}

/* ── Store Modal ────────────────────────────────────────── */
function openStoreModal(id, name, slug, gst, website, address, primary, secondary) {
    document.getElementById('modal-store-heading').textContent = name;
    document.getElementById('ms-name').textContent    = name;
    document.getElementById('ms-slug').textContent    = '/store/' + slug;
    document.getElementById('ms-gst').textContent     = gst     || 'N/A';
    document.getElementById('ms-address').textContent = address || '—';
    const wa = document.getElementById('ms-web');
    wa.textContent = website || '—';
    wa.href = website || '#';

    const pc = document.getElementById('ms-primary-chip');
    pc.style.background = primary;
    pc.style.color = 'white';
    pc.textContent = primary;

    const sc = document.getElementById('ms-secondary-chip');
    sc.style.background = secondary;
    sc.style.color = 'white';
    sc.textContent = secondary;

    document.getElementById('ms-approve-form').action =
        '{{ url("dashboard/saas/approvals/store") }}/' + id + '/approve';
    new bootstrap.Modal(document.getElementById('modalStore')).show();
}

/* ── Product Modal ──────────────────────────────────────── */
function openProductModal(name, code, cat, store, owner, thumb, date) {
    document.getElementById('modal-prod-heading').textContent = name;
    document.getElementById('mp-name').textContent  = name;
    document.getElementById('mp-code').textContent  = code  || '—';
    document.getElementById('mp-cat').textContent   = cat   || '—';
    document.getElementById('mp-store').textContent = store || '—';
    document.getElementById('mp-owner').textContent = owner || '—';
    document.getElementById('mp-date').textContent  = date;

    const wrap = document.getElementById('mp-thumb-wrap');
    const img  = document.getElementById('mp-thumb');
    if (thumb) {
        img.src = thumb;
        wrap.classList.remove('d-none');
    } else {
        wrap.classList.add('d-none');
    }
    new bootstrap.Modal(document.getElementById('modalProduct')).show();
}
</script>
@endpush
