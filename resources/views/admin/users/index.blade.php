@extends('layouts.admin')

@section('title', 'User accounts · DASURECO')

@section('content')
<div class="flex flex-col gap-6">
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-[#708775]">
                <span class="size-2 rounded-full bg-[#55a56c] shadow-[0_0_0_4px_rgba(85,165,108,.12)]" aria-hidden="true"></span>
                System administration
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-[#1a2a20] sm:text-4xl">User accounts</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#5f6b62]">Create accounts, assign roles, and deactivate access for people who have left the team.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-[#dce5dc] bg-white px-4 text-xs font-semibold text-[#4b6553] transition hover:bg-[#f5faf4] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" href="{{ route('admin.dashboard') }}">
                <span aria-hidden="true">←</span> Back to admin overview
            </a>
            <a class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#176442] px-4 text-xs font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" href="{{ route('admin.users.create') }}">
                <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 4v12m-6-6h12"/></svg>
                New account
            </a>
        </div>
    </section>


    <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
        <form class="grid gap-3 border-b border-[#edf0ec] px-5 py-4 sm:grid-cols-2 lg:grid-cols-4" method="GET" action="{{ route('admin.users.index') }}">
            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#5f6b62]">Search
                <input class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or email">
            </label>
            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#5f6b62]">Role
                <select class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" name="role">
                    <option value="">All roles</option>
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#5f6b62]">Status
                <select class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" name="status">
                    <option value="">All accounts</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button class="inline-flex h-10 items-center justify-center rounded-lg bg-[#176442] px-4 text-xs font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">Apply filters</button>
                <a class="inline-flex h-10 items-center justify-center rounded-lg border border-[#dce5dc] px-4 text-xs font-bold text-[#41634b] transition hover:bg-[#f5faf4]" href="{{ route('admin.users.index') }}">Clear</a>
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
                        <tr class="border-b border-[#edf0ec] text-[10px] font-bold uppercase tracking-wide text-[#5f6b62]">
                            <th class="px-5 py-3 font-bold sm:px-6">Account</th>
                            <th class="px-5 py-3 font-bold">Role</th>
                            <th class="px-5 py-3 font-bold">Status</th>
                            <th class="px-5 py-3 font-bold">Extra permissions</th>
                            <th class="px-5 py-3 text-right font-bold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf0ec]">
                        @foreach ($users as $user)
                            <tr class="transition hover:bg-[#fcfdfb]">
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="text-sm font-semibold text-[#26382c]">{{ $user->name }}</p>
                                    <p class="text-xs text-[#5f6b62]">{{ $user->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-[#435448]">{{ $roles[$user->role] ?? $user->role }}</td>
                                <td class="px-5 py-4">
                                    @if ($user->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#c6e0ca] bg-[#eaf5eb] px-2.5 py-1 text-[11px] font-semibold leading-4 text-[#2c5f41]">
                                            <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#dbe3d8] bg-[#f1f4f0] px-2.5 py-1 text-[11px] font-semibold leading-4 text-[#455449]">
                                            <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-[#5f6b62]">
                                    @if ($user->permissions->isEmpty())
                                        <span class="text-[#647068]">Role defaults only</span>
                                    @else
                                        {{ $user->permissions->pluck('label')->join(', ') }}
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a class="inline-flex h-9 items-center justify-center rounded-lg border border-[#dce5dc] bg-white px-3 text-xs font-semibold text-[#41634b] transition hover:bg-[#f5faf4] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                            @csrf
                                            <button class="inline-flex h-9 items-center justify-center rounded-lg border px-3 text-xs font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 {{ $user->is_active ? 'border-[#efd5cf] bg-white text-[#a0442f] hover:bg-[#fff5f2]' : 'border-[#c6e0ca] bg-white text-[#2c5f41] hover:bg-[#edf8ef]' }}" type="submit">
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
                <div class="border-t border-[#edf0ec] px-4 py-4 sm:px-6">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
