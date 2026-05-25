@extends('subscriber-panel.layouts.app')

@section('title', 'Attributes')
@section('page-title', 'Attributes')
@section('breadcrumb', 'Manage product attributes')

@section('content')

<div class="vp-page-header">
    <div>
        <h1 class="vp-page-title">Attributes</h1>
        <div style="font-size:0.8rem;color:#64748B;margin-top:4px;">
            {{ $attributes->total() }} attributes · Drag to reorder
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('subscriber.attribute-groups.index') }}" class="btn-subscriber-outline">
            <i class="bi bi-collection"></i> Groups
        </a>
        <a href="{{ route('subscriber.attributes.create') }}" class="btn-subscriber">
            <i class="bi bi-plus-lg"></i> Add Attribute
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="vp-card mb-4">
    <div class="vp-card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="vp-input" placeholder="Search attributes..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="group_id" class="vp-select">
                    <option value="">All Groups</option>
                    @foreach($groups as $g)
                    <option value="{{ $g->id }}" {{ request('group_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn-subscriber w-100"><i class="bi bi-filter"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

@if($attributes->isEmpty())
<div class="vp-card">
    <div class="empty-state">
        <div class="empty-state-icon">⚙️</div>
        <div class="empty-state-title">No Attributes Yet</div>
        <div class="empty-state-text">Create attributes like Size, Color, Material, etc. to enrich your products. All 12 attribute types supported!</div>
        <a href="{{ route('subscriber.attributes.create') }}" class="btn-subscriber">
            <i class="bi bi-plus-lg"></i> Create First Attribute
        </a>
    </div>
</div>
@else
<div class="vp-card">
    <div class="vp-card-body p-0">
        <table class="vp-table" id="attributes-table">
            <thead>
                <tr>
                    <th style="width:36px;"></th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Group</th>
                    <th>Required</th>
                    <th>PDF</th>
                    <th>Share</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="sortable-attributes">
                @foreach($attributes as $attr)
                <tr data-id="{{ $attr->id }}">
                    <td style="cursor:grab;color:#CBD5E1;text-align:center;">
                        <i class="bi bi-grip-vertical"></i>
                    </td>
                    <td>
                        <div style="font-weight:600;color:#0F172A;">{{ $attr->name }}</div>
                        @if($attr->unit)<div style="font-size:0.72rem;color:#94A3B8;">Unit: {{ $attr->unit }}</div>@endif
                    </td>
                    <td>
                        @php
                            $typeColors = [
                                'text' => '#4F46E5', 'number' => '#0891B2', 'select' => '#7C3AED',
                                'multiselect' => '#9333EA', 'checkbox' => '#DC2626', 'radio' => '#D97706',
                                'textarea' => '#059669', 'image' => '#EC4899', 'file' => '#64748B',
                                'color' => '#F59E0B', 'date' => '#0284C7', 'url' => '#16A34A',
                            ];
                            $color = $typeColors[$attr->type] ?? '#64748B';
                        @endphp
                        <span style="background:{{ $color }}15;color:{{ $color }};border-radius:6px;padding:3px 10px;font-size:0.72rem;font-weight:700;">
                            {{ strtoupper($attr->type) }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size:0.8rem;color:#64748B;">{{ $attr->group?->name ?? '—' }}</span>
                    </td>
                    <td>
                        @if($attr->is_required)
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                            <i class="bi bi-dash" style="color:#CBD5E1;"></i>
                        @endif
                    </td>
                    <td>
                        @if($attr->show_in_pdf)
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                            <i class="bi bi-x-circle-fill" style="color:#EF4444;"></i>
                        @endif
                    </td>
                    <td>
                        @if($attr->show_in_share)
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                            <i class="bi bi-x-circle-fill" style="color:#EF4444;"></i>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $attr->is_active ? 'badge-active' : 'badge-inactive' }}" style="border-radius:20px;padding:4px 10px;font-size:0.68rem;">
                            {{ $attr->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('subscriber.attributes.edit', $attr) }}" class="btn btn-sm" style="border-radius:8px;background:#F8FAFC;border:1px solid #E2E8F0;color:#64748B;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('subscriber.attributes.destroy', $attr) }}" method="POST" id="attr-del-{{ $attr->id }}">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDelete('attr-del-{{ $attr->id }}')"
                                        class="btn btn-sm" style="border-radius:8px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2);color:#EF4444;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($attributes->hasPages())
    <div class="p-4 border-top">
        {{ $attributes->withQueryString()->links() }}
    </div>
    @endif
</div>
@endif

@endsection

@push('js')
<script>
// Drag-drop reorder
const sortable = new Sortable(document.getElementById('sortable-attributes'), {
    handle: 'td:first-child',
    animation: 150,
    ghostClass: 'bg-light',
    onEnd: function() {
        const rows = document.querySelectorAll('#sortable-attributes tr');
        const order = Array.from(rows).map((row, index) => ({
            id: row.dataset.id,
            order: index
        }));
        fetch('{{ route("subscriber.attributes.reorder") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ order })
        });
    }
});
</script>
@endpush
