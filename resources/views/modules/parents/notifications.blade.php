@extends('layouts.parent')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('parent-portal.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Notifications</h5>
        </div>
        <div class="card-body">
            @if($notifications->count() > 0)
                @foreach($notifications as $notification)
                    <div class="mb-3 p-3 border rounded">
                        <h6 class="mb-2">{{ $notification->title }}</h6>
                        <p class="mb-2">{{ $notification->message }}</p>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                @endforeach

                {{ $notifications->links() }}
            @else
                <p class="text-muted mb-0">No notifications found.</p>
            @endif
        </div>
    </div>
@endsection