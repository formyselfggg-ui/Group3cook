@extends('layouts.admin')

@section('title', 'Roles and permissions · DASURECO')

@section('content')
<div class="flex flex-col gap-6">
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-[#708775]">
                <span class="size-2 rounded-full bg-[#55a56c] shadow-[0_0_0_4px_rgba(85,165,108,.12)]" aria-hidden="true"></span>
                System administration
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-[#1a2a20] sm:text-4xl">Roles and permissions</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#5f6b62]">Permissions are enforced on the backend for every role. Grant an extra permission to an individual account when a person needs wider access than their role provides.</p>
        </div>
        <a class="inline-flex h-10 shrink-0 items-center justify-center gap-1.5 rounded-xl border border-[#dce5dc] bg-white px-4 text-xs font-semibold text-[#4b6553] transition hover:bg-[#f5faf4] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" href="{{ route('admin.dashboard') }}">
            <span aria-hidden="true">←</span> Back to admin overview
        </a>
    </section>

    @foreach ($roles as $role => $label)
        <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
            <div class="flex flex-col justify-between gap-2 border-b border-[#edf0ec] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-[#24352a]">{{ $label }}</h2>
                    <p class="mt-1 text-xs text-[#5f6b62]">
                        {{ count($grantsByRole[$role]) }} of {{ $permissions->count() }} permissions enabled
                        @if ($role !== \App\Models\User::ROLE_ADMIN)
                            · {{ in_array($role, $configuredRoles, true) ? 'custom configuration' : 'built-in defaults' }}
                        @endif
                    </p>
                </div>
                @if ($role !== \App\Models\User::ROLE_ADMIN)
                    <span class="text-[11px] font-semibold text-[#647068]">Administrators always keep every permission</span>
                @endif
            </div>
            <form class="px-5 py-4 sm:px-6" method="POST" action="{{ route('admin.roles.update', $role) }}">
                @csrf
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($permissions as $permission)
                        <label class="flex items-start gap-3 rounded-xl border border-[#edf0ec] bg-[#fafcf9] p-3 text-xs">
                            <input class="mt-0.5 size-4 shrink-0 rounded border-[#cbd8c9] text-[#176442] focus:ring-2 focus:ring-[#3e8452]/30" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->name, $grantsByRole[$role], true)) @disabled($role === \App\Models\User::ROLE_ADMIN)>
                            <span>
                                <span class="block font-semibold text-[#344239]">{{ $permission->label }}</span>
                                <span class="mt-0.5 block text-[11px] leading-4 text-[#5f6b62]">{{ $permission->description }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @if ($role !== \App\Models\User::ROLE_ADMIN)
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <button class="inline-flex h-10 items-center justify-center rounded-lg bg-[#176442] px-4 text-xs font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">Save {{ strtolower($label) }} permissions</button>
                        @if (in_array($role, $configuredRoles, true))
                            <button class="inline-flex h-10 items-center justify-center rounded-lg border border-[#dce5dc] bg-white px-4 text-xs font-semibold text-[#41634b] transition hover:bg-[#f5faf4] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit" formaction="{{ route('admin.roles.reset', $role) }}" formnovalidate>Restore defaults</button>
                        @endif
                    </div>
                @endif
            </form>
        </section>
    @endforeach

    <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
        <div class="border-b border-[#edf0ec] px-5 py-4 sm:px-6">
            <h2 class="text-base font-semibold text-[#24352a]">Individual account grants</h2>
            <p class="mt-1 text-xs text-[#5f6b62]">Extra permissions granted to a single account on top of their role. Solid marks come from the role, outlined marks are explicit account grants.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[44rem] text-left">
                <thead>
                    <tr class="border-b border-[#edf0ec] text-[10px] font-bold uppercase tracking-wide text-[#5f6b62]">
                        <th class="px-5 py-3 font-bold sm:px-6">Account</th>
                        <th class="px-5 py-3 font-bold">Role</th>
                        <th class="px-5 py-3 font-bold">Grants</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#edf0ec]">
                    @foreach ($users as $user)
                        <tr class="align-top transition hover:bg-[#fcfdfb]">
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-sm font-semibold text-[#26382c]">{{ $user->name }}</p>
                                <p class="text-xs text-[#5f6b62]">{{ $user->email }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-[#435448]">{{ $roles[$user->role] ?? $user->role }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($permissions as $permission)
                                        @php
                                            $explicit = $user->permissions->contains('id', $permission->id);
                                            $fromRole = in_array($permission->name, $grantsByRole[$user->role] ?? [], true);
                                        @endphp
                                        <form method="POST" action="{{ route('admin.roles.user-permission', [$user, $permission]) }}">
                                            @csrf
                                            <button class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 {{ $explicit ? 'border-[#176442] bg-[#176442] text-white' : ($fromRole ? 'border-[#c6e0ca] bg-[#eaf5eb] text-[#2c5f41]' : 'border-[#e3eae1] bg-white text-[#647068] hover:border-[#cddfce]') }}" type="submit">
                                                <span class="size-1.5 rounded-full {{ $explicit ? 'bg-current' : 'bg-[#c6d1c7]' }}" aria-hidden="true"></span>
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
