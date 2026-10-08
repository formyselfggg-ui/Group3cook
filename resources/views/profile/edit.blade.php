@extends($layout)

@section('title', 'Profile settings · DASURECO')

@section('content')
<div class="mx-auto flex max-w-5xl flex-col gap-6">
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.16em] text-[#708775]">Your account</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-[#1a2a20] sm:text-4xl">Profile settings</h1>
            <p class="mt-2 text-sm leading-6 text-[#78857b]">Update your personal information and keep your sign-in secure.</p>
        </div>
        <a class="inline-flex h-10 items-center justify-center rounded-xl border border-[#dce5dc] bg-white px-4 text-xs font-semibold text-[#4b6553] transition hover:border-[#b8d1bb] hover:bg-[#f5faf4] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" href="{{ route($dashboardRoute) }}">Back to dashboard</a>
    </section>

    @if (session('status'))
        <div class="rounded-xl border border-[#cde2cd] bg-[#f1f8ef] px-4 py-3 text-sm font-medium text-[#356947]" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-[#f0d2cc] bg-[#fff5f2] px-4 py-3 text-sm text-[#984c3d]" role="alert">
            <p class="font-semibold">Please check the highlighted fields.</p>
        </div>
    @endif

    <section class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(17rem,.8fr)]">
        <div class="flex flex-col gap-6">
            <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
                <div class="border-b border-[#edf0ec] px-5 py-5 sm:px-6">
                    <h2 class="text-base font-semibold text-[#26382c]">Personal information</h2>
                    <p class="mt-1 text-xs text-[#879188]">Update the name and email associated with your account.</p>
                </div>
                <form class="grid gap-5 px-5 py-5 sm:px-6" method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-[#435448]" for="name">Full name</label>
                        <input class="h-11 rounded-xl border border-[#dce4dc] bg-white px-3 text-sm text-[#344239] outline-none transition placeholder:text-[#a0aaa1] focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
                        @error('name')<span class="text-xs text-[#a13e31]">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-[#435448]" for="email">Work email</label>
                        <input class="h-11 rounded-xl border border-[#dce4dc] bg-white px-3 text-sm text-[#344239] outline-none transition placeholder:text-[#a0aaa1] focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>
                        @error('email')<span class="text-xs text-[#a13e31]">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <button class="inline-flex h-11 items-center justify-center rounded-xl bg-[#176442] px-5 text-sm font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">Save profile</button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
                <div class="border-b border-[#edf0ec] px-5 py-5 sm:px-6">
                    <h2 class="text-base font-semibold text-[#26382c]">Change password</h2>
                    <p class="mt-1 text-xs text-[#879188]">Confirm your current password before choosing a new one.</p>
                </div>
                <form class="grid gap-5 px-5 py-5 sm:px-6" method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-[#435448]" for="current_password">Current password</label>
                        <input class="h-11 rounded-xl border border-[#dce4dc] bg-white px-3 text-sm text-[#344239] outline-none transition focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                        @error('current_password')<span class="text-xs text-[#a13e31]">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-[#435448]" for="password">New password</label>
                        <input class="h-11 rounded-xl border border-[#dce4dc] bg-white px-3 text-sm text-[#344239] outline-none transition focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                        @error('password')<span class="text-xs text-[#a13e31]">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-[#435448]" for="password_confirmation">Confirm new password</label>
                        <input class="h-11 rounded-xl border border-[#dce4dc] bg-white px-3 text-sm text-[#344239] outline-none transition focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                    </div>
                    <div>
                        <button class="inline-flex h-11 items-center justify-center rounded-xl border border-[#cbdcca] bg-white px-5 text-sm font-bold text-[#356947] transition hover:bg-[#f1f8ef] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">Change password</button>
                    </div>
                </form>
            </section>
        </div>

        <aside class="h-fit overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
            <div class="flex items-center gap-4 border-b border-[#edf0ec] px-5 py-5">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-[#eaf5eb] text-lg font-bold text-[#316447]" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold text-[#26382c]">{{ $user->name }}</span>
                    <span class="mt-1 block truncate text-xs text-[#879188]">{{ $user->email }}</span>
                </span>
            </div>
            <dl class="grid gap-3 px-5 py-5">
                <div class="grid gap-1">
                    <dt class="text-[10px] font-bold uppercase tracking-[.12em] text-[#879188]">Workspace role</dt>
                    <dd class="text-sm font-semibold text-[#344239]">{{ $roleLabel }}</dd>
                </div>
                <div class="grid gap-1">
                    <dt class="text-[10px] font-bold uppercase tracking-[.12em] text-[#879188]">Account status</dt>
                    <dd class="text-sm font-semibold {{ $user->is_active ? 'text-[#356947]' : 'text-[#a64b39]' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</dd>
                </div>
                <p class="border-t border-[#edf0ec] pt-4 text-xs leading-5 text-[#78857b]">Your role and account status are managed by an administrator and cannot be changed from profile settings.</p>
            </dl>
        </aside>
    </section>
</div>
@endsection
