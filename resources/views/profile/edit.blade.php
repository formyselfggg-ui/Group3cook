@extends($layout)

@section('title', 'Profile settings · DASURECO')

@section('content')
<div class="mx-auto flex max-w-5xl flex-col gap-6">
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.16em] text-emerald-700">Your account</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-slate-900 sm:text-4xl">Profile settings</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Update your personal information and keep your sign-in secure.</p>
        </div>
        <a class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" href="{{ route($dashboardRoute) }}">Back to dashboard</a>
    </section>

    <section class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(17rem,.8fr)]">
        <div class="flex flex-col gap-6">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                    <h2 class="text-base font-semibold text-slate-900">Personal information</h2>
                    <p class="mt-1 text-xs text-slate-500">Update the name and email associated with your account.</p>
                </div>
                <form class="grid gap-5 px-5 py-5 sm:px-6" method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-slate-700" for="name">Full name</label>
                        <input class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
                        @error('name')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-slate-700" for="email">Work email</label>
                        <input class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>
                        @error('email')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <button class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">Save profile</button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                    <h2 class="text-base font-semibold text-slate-900">Change password</h2>
                    <p class="mt-1 text-xs text-slate-500">Confirm your current password before choosing a new one.</p>
                </div>
                <form class="grid gap-5 px-5 py-5 sm:px-6" method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-slate-700" for="current_password">Current password</label>
                        <input class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                        @error('current_password')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-slate-700" for="password">New password</label>
                        <input class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                        @error('password')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-slate-700" for="password_confirmation">Confirm new password</label>
                        <input class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                    </div>
                    <div>
                        <button class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-emerald-700 transition hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">Change password</button>
                    </div>
                </form>
            </section>
        </div>

        <aside class="h-fit overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-4 border-b border-slate-100 px-5 py-5">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-lg font-bold text-emerald-700 border border-emerald-100" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold text-slate-900">{{ $user->name }}</span>
                    <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $user->email }}</span>
                </span>
            </div>
            <dl class="grid gap-3 px-5 py-5">
                <div class="grid gap-1">
                    <dt class="text-[10px] font-bold uppercase tracking-[.12em] text-slate-500">Workspace role</dt>
                    <dd class="text-sm font-semibold text-slate-800">{{ $roleLabel }}</dd>
                </div>
                <div class="grid gap-1">
                    <dt class="text-[10px] font-bold uppercase tracking-[.12em] text-slate-500">Account status</dt>
                    <dd class="text-sm font-semibold {{ $user->is_active ? 'text-emerald-700' : 'text-rose-600' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</dd>
                </div>
                <p class="border-t border-slate-100 pt-4 text-xs leading-5 text-slate-500">Your role and account status are managed by an administrator and cannot be changed from profile settings.</p>
            </dl>
        </aside>
    </section>
</div>
@endsection
