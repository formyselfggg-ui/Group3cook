@extends('layouts.guest')

@section('title', 'Operations portal · DASURECO')

@section('body')
<div class="workspace">
    <header class="workspace-header">
        <a class="workspace-brand" href="{{ route('dashboard') }}">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M13.4 2.8 5.7 13h5l-.5 8.2L18.4 11h-5.1l.1-8.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
            DASURECO <span style="color:#8c978f;font-weight:500">/ Operations</span>
        </a>
        <div class="workspace-user">
            <span>{{ auth()->user()->name }} · {{ $roleLabel }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-button" type="submit">Sign out</button></form>
        </div>
    </header>
    <main class="workspace-main">
        <div class="workspace-heading">
            <div>
                <span class="eyebrow">DASURECO field operations</span>
                <h1>Welcome, {{ auth()->user()->name }}</h1>
                <p>Your approved account is ready.</p>
            </div>
        </div>
        <section class="dashboard-card">
            <h2>You're signed in</h2>
            <p>You're signed in as <strong>{{ $roleLabel }}</strong>. Work orders and role-specific tools will be available here as the operations workspace is built out.</p>
        </section>
    </main>
</div>
@endsection
