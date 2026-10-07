@extends('layouts.guest')

@section('title', 'Request access · DASURECO Operations')

@section('body')
<main class="auth-shell">
    <aside class="brand-panel">
        <a class="brand-header" href="{{ route('login') }}" style="color:inherit;text-decoration:none">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M13.4 2.8 5.7 13h5l-.5 8.2L18.4 11h-5.1l.1-8.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
            <span><span class="brand-name">DASURECO</span><span class="brand-sub">Field operations</span></span>
        </a>
        <div class="brand-copy">
            <span class="eyebrow">Join the operations team</span>
            <h1>Every role.<br><span>One mission.</span></h1>
            <p>Request access to the shared workspace for field operations, assets and work orders.</p>
            <div class="signal-card">
                <span class="signal-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m13 2-9 12h7l-1 8 10-13h-7l.5-7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
                <span><strong>Admin-reviewed access</strong><small>Your account is only created after an administrator approves your request.</small></span>
            </div>
        </div>
        <footer class="brand-footer"><span>Operations management</span><span>DAS - Davao del Sur</span></footer>
    </aside>
    <section class="auth-main">
        <div class="auth-content">
            <div class="auth-topline"><span>Already approved?</span><a href="{{ route('login') }}">Sign in</a></div>
            <header class="form-heading">
                <span class="eyebrow">Access request</span>
                <h2>Create your request</h2>
                <p>Tell us who you are. An administrator will review your details and assign access.</p>
            </header>
            @if ($errors->any())
                <div class="alert error" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('register.store') }}" class="form-fields">
                @csrf
                <div class="field">
                    <label for="name">Full name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Your name" autocomplete="name" maxlength="255" required autofocus>
                </div>
                <div class="field">
                    <label for="email">Work email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@dasurco.com" autocomplete="email" maxlength="255" required>
                </div>
                <div class="field">
                    <label for="role">Requested team role</label>
                    <select id="role" name="role" required>
                        <option value="" disabled @selected(! old('role'))>Choose the role that fits your work</option>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="password">Create password</label>
                    <input id="password" type="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required>
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Enter your password again" autocomplete="new-password" minlength="8" required>
                </div>
                <div class="request-note">Your account will not be created until an administrator approves this request. You can sign in after approval.</div>
                <button class="submit-button" type="submit">Submit access request <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
            </form>
            <p class="form-bottom">Your password is stored securely and is never shown to reviewers.</p>
        </div>
    </section>
</main>
@endsection
