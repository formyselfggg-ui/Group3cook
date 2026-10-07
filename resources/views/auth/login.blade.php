@extends('layouts.guest')

@section('title', 'Sign in · DASURECO Operations')

@section('body')
<main class="auth-shell">
    <aside class="brand-panel">
        <a class="brand-header" href="{{ route('login') }}" style="color:inherit;text-decoration:none">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M13.4 2.8 5.7 13h5l-.5 8.2L18.4 11h-5.1l.1-8.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
            <span><span class="brand-name">DASURECO</span><span class="brand-sub">Field operations</span></span>
        </a>
        <div class="brand-copy">
            <span class="eyebrow">Connected field operations</span>
            <h1>Powering work.<br><span>Supporting people.</span></h1>
            <p>A single workspace for the crews, assets and work orders that keep our communities running.</p>
            <div class="signal-card">
                <span class="signal-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m13 2-9 12h7l-1 8 10-13h-7l.5-7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
                <span><strong>One team. Every operation.</strong><small>Work orders, field updates and maintenance in one place.</small></span>
            </div>
        </div>
        <footer class="brand-footer"><span>Operations management</span><span>DAS - Davao del Sur</span></footer>
    </aside>
    <section class="auth-main">
        <div class="auth-content">
            <div class="auth-topline"><span>New to the operations portal?</span><a href="{{ route('register') }}">Request access</a></div>
            <header class="form-heading">
                <span class="eyebrow">Welcome back</span>
                <h2>Sign in to your account</h2>
                <p>Use your approved work account to continue to the operations portal.</p>
            </header>
            @if (session('status'))
                <div class="alert" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert error" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('login') }}" class="form-fields">
                @csrf
                <div class="field">
                    <label for="email">Work email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@dasurco.com" autocomplete="username" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                </div>
                <div class="form-options">
                    <label class="remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                    <span>Protected access</span>
                </div>
                <button class="submit-button" type="submit">Sign in <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
            </form>
            <p class="form-bottom">Access is limited to approved team members.<br><strong>Need an account?</strong> <a href="{{ route('register') }}">Submit an access request</a></p>
        </div>
    </section>
</main>
@endsection
