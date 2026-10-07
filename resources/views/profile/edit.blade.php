@extends('layouts.guest')

@section('title', 'Profile settings · DASURECO Operations')

@section('body')
<div class="workspace">
    <header class="workspace-header">
        <a class="workspace-brand" href="{{ route('dashboard') }}">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M13.4 2.8 5.7 13h5l-.5 8.2L18.4 11h-5.1l.1-8.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
            DASURECO <span style="color:#8c978f;font-weight:500">/ Profile settings</span>
        </a>
        <div class="workspace-user">
            <a class="text-button" href="{{ route('profile.edit') }}" aria-current="page">Profile settings</a>
            <span>{{ $profile->name }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-button" type="submit">Sign out</button></form>
        </div>
    </header>
    <main class="workspace-main">
        @if (session('status'))
            <div class="alert success-banner" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert error" role="alert">{{ $errors->first() }}</div>
        @endif
        <div class="workspace-heading">
            <div>
                <span class="eyebrow">Account settings</span>
                <h1>Profile settings</h1>
                <p>Keep your contact details current and manage your sign-in password.</p>
            </div>
        </div>
        <div class="settings-grid">
            <section class="settings-card">
                <div class="settings-card-heading">
                    <span class="settings-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.4" stroke="currentColor" stroke-width="1.6"/><path d="M5.5 20c.5-3.1 3-5.2 6.5-5.2s6 2.1 6.5 5.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span>
                    <span><h2>Personal information</h2><p>These details identify your account.</p></span>
                </div>
                <form method="POST" action="{{ route('profile.update') }}" class="form-fields settings-form">
                    @csrf
                    @method('PUT')
                    <div class="field">
                        <label for="name">Full name</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $profile->name) }}" autocomplete="name" maxlength="255" required>
                        @error('name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="email">Work email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $profile->email) }}" autocomplete="email" maxlength="255" required>
                        @error('email')<span class="field-error">{{ $message }}</span>@enderror
                        @if ($profile->email_verified_at)
                            <span class="field-hint">Verified work email</span>
                        @else
                            <span class="field-hint">Email address not verified</span>
                        @endif
                    </div>
                    <div class="field">
                        <label for="role">Assigned role</label>
                        <input id="role" type="text" value="{{ $roleLabel }}" readonly aria-readonly="true">
                        <span class="field-hint">Only an administrator can change your role.</span>
                    </div>
                    <button class="submit-button" type="submit">Save profile</button>
                </form>
            </section>
            <section class="settings-card">
                <div class="settings-card-heading">
                    <span class="settings-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="11" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 10V7a4 4 0 1 1 8 0v3m-4 4v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span>
                    <span><h2>Change password</h2><p>Confirm your current password before choosing a new one.</p></span>
                </div>
                <form method="POST" action="{{ route('profile.password.update') }}" class="form-fields settings-form">
                    @csrf
                    @method('PUT')
                    <div class="field">
                        <label for="current_password">Current password</label>
                        <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
                        @error('current_password')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="password">New password</label>
                        <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
                        @error('password')<span class="field-error">{{ $message }}</span>@enderror
                        <span class="field-hint">Use at least 8 characters.</span>
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Confirm new password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                    </div>
                    <button class="submit-button" type="submit">Update password</button>
                </form>
            </section>
        </div>
    </main>
</div>
@endsection
