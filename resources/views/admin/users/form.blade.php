@extends('layouts.admin')

@section('title', ($user->exists ? 'Edit account' : 'New account').' · DASURECO')

@section('content')
<div class="flex flex-col gap-6">
    <section>
        <a class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#41634b] transition hover:text-[#214d32]" href="{{ route('admin.users.index') }}">
            <span aria-hidden="true">←</span> Back to user accounts
        </a>
        <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-[#1a2a20] sm:text-4xl">{{ $user->exists ? 'Edit account' : 'Create account' }}</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-[#5f6b62]">{{ $user->exists ? 'Update the account details, role and access status.' : 'Create a new account and assign the role that matches the person’s responsibilities.' }}</p>
    </section>

    <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
        <form class="grid gap-4 px-5 py-5 sm:grid-cols-2 sm:px-6" method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf
            @if ($user->exists)
                @method('PUT')
            @endif

            <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="name">Full name <span class="font-normal text-[#a0442f]">Required</span>
                <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="255" required>
            </label>

            <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="email">Email address <span class="font-normal text-[#a0442f]">Required</span>
                <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" required>
            </label>

            <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="role">Role <span class="font-normal text-[#a0442f]">Required</span>
                <select class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="role" name="role" required>
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="password">{{ $user->exists ? 'New password' : 'Password' }} <span class="font-normal text-[#647068]">{{ $user->exists ? 'Optional · leave blank to keep the current password' : 'Required · minimum 8 characters' }}</span>
                <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="password" name="password" type="password" autocomplete="new-password" @if (! $user->exists) required @endif>
            </label>

            <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="password_confirmation">Confirm password
                <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @if (! $user->exists) required @endif>
            </label>

            <label class="flex items-center gap-3 self-end rounded-xl border border-[#e3eae1] bg-[#fafcf9] px-4 py-3 text-xs font-semibold text-[#435448]" for="is_active">
                <input class="size-4 rounded border-[#cbd8c9] text-[#176442] focus:ring-2 focus:ring-[#3e8452]/30" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $user->exists ? $user->is_active : true))>
                Account is active and can sign in
            </label>

            <div class="flex flex-wrap items-center gap-3 sm:col-span-2">
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-[#176442] px-5 text-sm font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">
                    {{ $user->exists ? 'Save account' : 'Create account' }}
                </button>
                <a class="inline-flex h-11 items-center justify-center rounded-xl border border-[#dce5dc] bg-white px-5 text-sm font-bold text-[#41634b] transition hover:bg-[#f5faf4]" href="{{ route('admin.users.index') }}">Cancel</a>
            </div>
        </form>
    </section>
</div>
@endsection
