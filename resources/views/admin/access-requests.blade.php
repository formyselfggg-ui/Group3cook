@extends('layouts.guest')

@section('title', 'Access requests · DASURECO Operations')

@section('body')
<div class="workspace">
    <header class="workspace-header">
        <a class="workspace-brand" href="{{ route('admin.access-requests.index') }}">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M13.4 2.8 5.7 13h5l-.5 8.2L18.4 11h-5.1l.1-8.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
            DASURECO <span style="color:#8c978f;font-weight:500">/ Admin</span>
        </a>
        <div class="workspace-user">
            <a class="text-button" href="{{ route('profile.edit') }}">Profile settings</a>
            <span>{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-button" type="submit">Sign out</button></form>
        </div>
    </header>
    <main class="workspace-main">
        @if (session('status'))
            <div class="alert success-banner" role="status">{{ session('status') }}</div>
        @endif
        <div class="workspace-heading">
            <div>
                <span class="eyebrow">User administration</span>
                <h1>Access requests</h1>
                <p>Review requests before creating an account and granting system access.</p>
            </div>
            <span class="count-chip">{{ $requests->total() }} pending</span>
        </div>
        @if ($requests->isEmpty())
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m5 12 4.5 4.5L19 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <strong>You're all caught up</strong>
                <p>New access requests will appear here for review.</p>
            </div>
        @else
            <div class="request-list">
                @foreach ($requests as $accessRequest)
                    <article class="request-card">
                        <div class="request-person">
                            <strong>{{ $accessRequest->name }}</strong>
                            <span>{{ $accessRequest->email }}</span>
                        </div>
                        <div class="request-meta">
                            <small>Requested role</small>
                            <span>{{ $roles[$accessRequest->requested_role] ?? 'Unknown role' }}</span>
                            <span>Submitted {{ $accessRequest->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="request-actions">
                            <form class="inline-form" method="POST" action="{{ route('admin.access-requests.approve', $accessRequest) }}">
                                @csrf
                                <select name="role" aria-label="Assign role to {{ $accessRequest->name }}" required>
                                    @foreach ($roles as $value => $label)
                                        <option value="{{ $value }}" @selected($value === $accessRequest->requested_role)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit">Approve &amp; create</button>
                            </form>
                            <form class="inline-form" method="POST" action="{{ route('admin.access-requests.reject', $accessRequest) }}">
                                @csrf
                                <button class="reject-button" type="submit">Reject</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
            <div style="margin-top:20px">{{ $requests->links() }}</div>
        @endif
    </main>
</div>
@endsection
