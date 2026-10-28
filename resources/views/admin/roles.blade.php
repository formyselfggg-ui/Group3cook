@extends('layouts.admin')

@section('title', 'Roles and permissions · DASURECO')

@section('content')
<div class="flex flex-col gap-6">
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-emerald-700">
                <span class="size-2 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,.15)]" aria-hidden="true"></span>
                System administration
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-slate-900 sm:text-4xl">Roles and permissions</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Permissions are enforced on the backend for every role. Grant an extra permission to an individual account when a person needs wider access than their role provides.</p>
        </div>
        <a class="inline-flex h-10 shrink-0 items-center justify-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" href="{{ route('admin.dashboard') }}">
            <span aria-hidden="true">←</span> Back to admin overview
        </a>
    </section>

    @foreach ($roles as $role => $label)
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col justify-between gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">{{ $label }}</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ count($grantsByRole[$role]) }} of {{ $permissions->count() }} permissions enabled
                        @if ($role !== \App\Models\User::ROLE_ADMIN)
                            · {{ in_array($role, $configuredRoles, true) ? 'custom configuration' : 'built-in defaults' }}
                        @endif
                    </p>
                </div>
                @if ($role !== \App\Models\User::ROLE_ADMIN)
                    <span class="text-[11px] font-semibold text-slate-500">Administrators always keep every permission</span>
                @endif
            </div>
            <form class="px-5 py-4 sm:px-6" method="POST" action="{{ route('admin.roles.update', $role) }}">
                @csrf
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($permissions as $permission)
                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-xs hover:border-emerald-300 transition cursor-pointer">
                            <input class="mt-0.5 size-4 shrink-0 rounded border-slate-300 text-emerald-600 accent-emerald-600 focus:ring-2 focus:ring-emerald-500/30" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->name, $grantsByRole[$role], true)) @disabled($role === \App\Models\User::ROLE_ADMIN)>
                            <span>
                                <span class="block font-semibold text-slate-900">{{ $permission->label }}</span>
                                <span class="mt-0.5 block text-[11px] leading-4 text-slate-500">{{ $permission->description }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @if ($role !== \App\Models\User::ROLE_ADMIN)
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <button class="inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">Save {{ strtolower($label) }} permissions</button>
                        @if (in_array($role, $configuredRoles, true))
                            <button class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" type="submit" formaction="{{ route('admin.roles.reset', $role) }}" formnovalidate>Restore defaults</button>
                        @endif
                    </div>
                @endif
            </form>
        </section>
    @endforeach

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
            <h2 class="text-base font-semibold text-slate-900">Individual account grants</h2>
            <p class="mt-1 text-xs text-slate-500">Extra permissions granted to a single account on top of their role. Solid marks come from the role, outlined marks are explicit account grants.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[44rem] text-left">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3 font-bold sm:px-6">Account</th>
                        <th class="px-5 py-3 font-bold">Role</th>
                        <th class="px-5 py-3 font-bold">Grants</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr class="align-top transition hover:bg-slate-50/50">
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $roles[$user->role] ?? $user->role }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($permissions as $permission)
                                        @php
                                            $explicit = $user->permissions->contains('id', $permission->id);
                                            $fromRole = in_array($permission->name, $grantsByRole[$user->role] ?? [], true);
                                        @endphp
                                        <form method="POST" action="{{ route('admin.roles.user-permission', [$user, $permission]) }}">
                                            @csrf
                                            <button class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 {{ $explicit ? 'border-emerald-700 bg-emerald-700 text-white shadow-sm' : ($fromRole ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300 hover:bg-slate-50') }}" type="submit">
                                                <span class="size-1.5 rounded-full {{ $explicit ? 'bg-current' : 'bg-slate-300' }}" aria-hidden="true"></span>
                                                {{ $permission->label }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
