@extends('subscriber-panel.layouts.app')

@section('title', 'Attribute Groups')
@section('page-title', 'Attribute Groups')
@section('breadcrumb', 'Organize your product attributes into groups')

@section('content')

<div class="vp-page-header">
    <div>
        <h1 class="vp-page-title">Attribute Groups</h1>
    </div>
    <div>
        <button type="button" class="btn-subscriber" data-bs-toggle="modal" data-bs-target="#createGroupModal">
            <i class="bi bi-plus-lg"></i> Create Group
        </button>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-12">
        <div class="vp-card">
            <div class="vp-card-header">
                <h6 class="vp-card-title"><i class="bi bi-collection-fill me-2"></i>All Groups <small style="font-weight:400;color:#94A3B8;">(Drag ☰ to reorder)</small></h6>
            </div>
            <div class="vp-card-body p-0">
                @if($groups->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">📂</div>
                    <div class="empty-state-title">No Groups Found</div>
                    <div class="empty-state-text">Create groups like "Technical Specs", "Dimensions", or "Efficacy" to organize your custom attributes.</div>
                    <button class="btn-subscriber" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                        <i class="bi bi-plus-lg"></i> Create First Group
                    </button>
                </div>
                @else
                <div class="table-responsive">
                    <table class="vp-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th>Group Name</th>
                                <th>Description</th>
                                <th>Attributes Count</th>
                                <th style="width: 150px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-groups">
                            @foreach($groups as $group)
                            <tr data-id="{{ $group->id }}">
                                <td class="sort-handle" style="cursor: grab; color: #94A3B8;"><i class="bi bi-list fs-5"></i></td>
                                <td>
                                    <span style="font-weight:600;color:#0F172A;">{{ $group->name }}</span>
                                </td>
                                <td>
                                    <span style="color:#64748B;">{{ $group->description ?: '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge" style="background: rgba(79,70,229,0.1); color: #4F46E5; font-weight: 600;">
                                        {{ $group->attributes_count }} attributes
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <button type="button" class="btn btn-sm" style="border-radius:8px;background:#F8FAFC;border:1px solid #E2E8F0;color:#475569;"
                                                onclick="openEditModal({{ $group->id }}, '{{ addslashes($group->name) }}', '{{ addslashes($group->description) }}')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('subscriber.attribute-groups.destroy', $group->id) }}" method="POST" id="del-group-{{ $group->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm" style="border-radius:8px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2);color:#EF4444;"
                                                    onclick="confirmDelete('del-group-{{ $group->id }}', 'Delete {{ addslashes($group->name) }}? All attributes in this group will be moved to general.')">
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
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius);border:none;box-shadow:var(--shadow-lg);">
            <div class="modal-header border-bottom-0" style="padding:20px 24px 10px;">
                <h5 class="modal-title" style="font-family:'Outfit',sans-serif;font-weight:700;color:var(--text-primary);">Create Attribute Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('subscriber.attribute-groups.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:10px 24px 24px;">
                    <div class="vp-form-group">
                        <label class="vp-label">Group Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="vp-input" placeholder="e.g. Electrical Specifications" required>
                    </div>
                    <div class="vp-form-group mb-0">
                        <label class="vp-label">Description</label>
                        <textarea name="description" class="vp-textarea" rows="3" placeholder="Brief explanation of what attributes belong here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 d-flex gap-2" style="padding:10px 24px 24px;">
                    <button type="button" class="btn-subscriber-outline w-50" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-subscriber w-50 justify-content-center">Create Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius);border:none;box-shadow:var(--shadow-lg);">
            <div class="modal-header border-bottom-0" style="padding:20px 24px 10px;">
                <h5 class="modal-title" style="font-family:'Outfit',sans-serif;font-weight:700;color:var(--text-primary);">Edit Attribute Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGroupForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding:10px 24px 24px;">
                    <div class="vp-form-group">
                        <label class="vp-label">Group Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-group-name" class="vp-input" required>
                    </div>
                    <div class="vp-form-group mb-0">
                        <label class="vp-label">Description</label>
                        <textarea name="description" id="edit-group-description" class="vp-textarea" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 d-flex gap-2" style="padding:10px 24px 24px;">
                    <button type="button" class="btn-subscriber-outline w-50" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-subscriber w-50 justify-content-center">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
function openEditModal(id, name, description) {
    const form = document.getElementById('editGroupForm');
    form.action = `{{ url('/subscriber/attribute-groups') }}/${id}`;
    document.getElementById('edit-group-name').value = name;
    document.getElementById('edit-group-description').value = description;
    
    const modal = new bootstrap.Modal(document.getElementById('editGroupModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('sortable-groups');
    if (el) {
        Sortable.create(el, {
            handle: '.sort-handle',
            animation: 150,
            onEnd: function() {
                const order = [];
                el.querySelectorAll('tr').forEach((tr, index) => {
                    order.push({
                        id: tr.dataset.id,
                        order: index + 1
                    });
                });

                fetch("{{ route('subscriber.attribute-groups.reorder') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: order })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Order updated successfully'
                        });
                    }
                });
            }
        });
    }
});
</script>
@endpush
