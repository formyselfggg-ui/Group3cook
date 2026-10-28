@extends('layouts.admin')

@section('title', ($user->exists ? 'Edit account' : 'New account').' · DASURECO')

@section('content')
<div class="flex flex-col gap-6">
    <section>
        <a class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 transition hover:text-emerald-800" href="{{ route('admin.users.index') }}">
            <span aria-hidden="true">←</span> Back to user accounts
        </a>
        <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-slate-900 sm:text-4xl">{{ $user->exists ? 'Edit account' : 'Create account' }}</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{{ $user->exists ? 'Update the account details, role and access status.' : 'Create a new account and assign the role that matches the person’s responsibilities.' }}</p>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form class="grid gap-4 px-5 py-5 sm:grid-cols-2 sm:px-6" method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf
            @if ($user->exists)
                @method('PUT')
            @endif

            <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="name">Full name <span class="font-normal text-rose-600">Required</span>
                <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="255" required>
            </label>

            <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="email">Email address <span class="font-normal text-rose-600">Required</span>
                <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" required>
            </label>

            <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="role">Role <span class="font-normal text-rose-600">Required</span>
                <select class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="role" name="role" required>
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="password">{{ $user->exists ? 'New password' : 'Password' }} <span class="font-normal text-slate-400">{{ $user->exists ? 'Optional · leave blank to keep the current password' : 'Required · minimum 8 characters' }}</span>
                <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="password" name="password" type="password" autocomplete="new-password" @if (! $user->exists) required @endif>
            </label>

            <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="password_confirmation">Confirm password
                <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @if (! $user->exists) required @endif>
            </label>

            <label class="flex items-center gap-3 self-end rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-xs font-semibold text-slate-700" for="is_active">
                <input class="size-4 rounded border-slate-300 text-emerald-600 accent-emerald-600 focus:ring-2 focus:ring-emerald-500/30" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $user->exists ? $user->is_active : true))>
                Account is active and can sign in
            </label>

            <div class="flex flex-wrap items-center gap-3 sm:col-span-2">
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">
                    {{ $user->exists ? 'Save account' : 'Create account' }}
                </button>
                <a class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50" href="{{ route('admin.users.index') }}">Cancel</a>
            </div>
        </form>
    </section>
</div>
@endsection
