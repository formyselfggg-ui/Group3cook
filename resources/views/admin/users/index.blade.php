@extends('layouts.admin')

@section('title', 'User accounts · DASURECO')

@section('content')
<div class="flex flex-col gap-6">
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-emerald-700">
                <span class="size-2 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,.15)]" aria-hidden="true"></span>
                System administration
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-slate-900 sm:text-4xl">User accounts</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Create accounts, assign roles, and deactivate access for people who have left the team.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" href="{{ route('admin.dashboard') }}">
                <span aria-hidden="true">←</span> Back to admin overview
            </a>
            <a class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" href="{{ route('admin.users.create') }}">
                <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 4v12m-6-6h12"/></svg>
                New account
            </a>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form class="grid gap-3 border-b border-slate-100 px-5 py-4 sm:grid-cols-2 lg:grid-cols-4" method="GET" action="{{ route('admin.users.index') }}">
            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Search
                <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or email">
            </label>
            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Role
                <select class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" name="role">
                    <option value="">All roles</option>
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Status
                <select class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" name="status">
                    <option value="">All accounts</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button class="inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 shadow-sm" type="submit">Apply filters</button>
                <a class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-xs font-bold text-slate-600 transition hover:bg-slate-50" href="{{ route('admin.users.index') }}">Clear</a>
            </div>
        </form>

        @if ($users->isEmpty())
            <div class="px-6 py-6">
                <x-empty-state
                    icon="inbox"
                    title="No accounts match these filters"
                    description="Clear the filters or create a new account to get started."
                />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[46rem] text-left">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3 font-bold sm:px-6">Account</th>
                            <th class="px-5 py-3 font-bold">Role</th>
                            <th class="px-5 py-3 font-bold">Status</th>
                            <th class="px-5 py-3 font-bold">Extra permissions</th>
                            <th class="px-5 py-3 text-right font-bold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($users as $user)
                            <tr class="transition hover:bg-slate-50/50">
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $roles[$user->role] ?? $user->role }}</td>
                                <td class="px-5 py-4">
                                    @if ($user->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold leading-4 text-emerald-700">
                                            <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-[11px] font-semibold leading-4 text-slate-600">
                                            <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-500">
                                    @if ($user->permissions->isEmpty())
                                        <span class="text-slate-400">Role defaults only</span>
                                    @else
                                        {{ $user->permissions->pluck('label')->join(', ') }}
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                            @csrf
                                            <button class="inline-flex h-9 items-center justify-center rounded-lg border px-3 text-xs font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 {{ $user->is_active ? 'border-rose-200 bg-white text-rose-600 hover:bg-rose-50' : 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50' }}" type="submit">
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t border-slate-100 px-4 py-4 sm:px-6">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
