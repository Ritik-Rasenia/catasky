@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Solution Details</h4>
                <small class="text-muted">{{ $solution->name }}</small>
            </div>
            <a href="{{ route('admin.solutions.index') }}" class="btn btn-secondary">Back to Solutions</a>
        </div>
        <div class="card-body">
            <div class="row g-4">
                @if($solution->image)
                    <div class="col-md-4">
                        <img src="{{ asset('uploads/solutions/'.$solution->image) }}" alt="{{ $solution->name }}" class="img-fluid rounded">
                    </div>
                @endif
                <div class="col-md-8">
                    @if($solution->icon)
                        <div class="mb-3">
                            <img src="{{ asset('uploads/solutions/'.$solution->icon) }}" width="80" height="80" class="rounded-circle border">
                        </div>
                    @endif
                    <h3>{{ $solution->name }}</h3>
                    <p class="text-muted">Status: {{ $solution->status ? 'Active' : 'Inactive' }}</p>
                    <div class="mb-4">
                        {!! nl2br(e($solution->short_description)) !!}
                    </div>
                    <div>{!! $solution->description !!}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
