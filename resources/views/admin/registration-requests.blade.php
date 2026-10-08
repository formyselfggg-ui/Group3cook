@extends('layouts.admin')

@section('title', 'Registration requests · DASURECO')

@section('content')
<div class="flex flex-col gap-6">
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.16em] text-[#708775]">System administration</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-[#1a2a20] sm:text-4xl">Registration requests</h1>
            <p class="mt-2 text-sm leading-6 text-[#78857b]">Review access requests. Accounts are created only when approved.</p>
        </div>
        <a class="inline-flex h-10 items-center justify-center rounded-xl border border-[#dce5dc] bg-white px-4 text-xs font-semibold text-[#4b6553] transition hover:bg-[#f5faf4]" href="{{ route('admin.dashboard') }}">Back to admin overview</a>
    </section>

    @if (session('status'))
        <div class="rounded-xl border border-[#cde2cd] bg-[#f1f8ef] px-4 py-3 text-sm font-medium text-[#356947]" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-[#f0d2cc] bg-[#fff5f2] px-4 py-3 text-sm text-[#984c3d]" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
        @if ($registrationRequests->isEmpty())
            <div class="px-6 py-16 text-center">
                <h2 class="text-sm font-semibold text-[#344239]">No pending requests</h2>
                <p class="mt-1 text-xs leading-5 text-[#879188]">New registration requests will appear here for review.</p>
            </div>
        @else
            <div class="divide-y divide-[#edf0ec]">
                @foreach ($registrationRequests as $registrationRequest)
                    <article class="flex flex-col gap-5 px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-sm font-semibold text-[#26382c]">{{ $registrationRequest->name }}</h2>
                            <p class="mt-1 text-xs text-[#68766d]">{{ $registrationRequest->email }}</p>
                            <div class="mt-3 flex flex-wrap gap-2 text-[10px] font-semibold uppercase tracking-wide">
                                <span class="rounded-full bg-[#f1f4f0] px-2.5 py-1 text-[#69776c]">Requested: {{ $registrationRequest->requested_role === 'supervisor' ? 'Supervisor / Dispatcher' : 'Field personnel' }}</span>
                                <span class="rounded-full bg-[#fff7e8] px-2.5 py-1 text-[#956b28]">Pending · {{ $registrationRequest->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <form class="flex flex-col gap-2 sm:flex-row sm:items-end" method="POST" action="{{ route('admin.registration-requests.approve', $registrationRequest) }}">
                                @csrf
                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Approve as
                                    <select class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" name="role" required>
                                        <option value="field_personnel" @selected($registrationRequest->requested_role === 'field_personnel')>Field personnel</option>
                                        <option value="supervisor" @selected($registrationRequest->requested_role === 'supervisor')>Supervisor / Dispatcher</option>
                                        <option value="operations">Operations / Engineering Staff</option>
                                    </select>
                                </label>
                                <button class="inline-flex h-10 items-center justify-center rounded-lg bg-[#176442] px-4 text-xs font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.registration-requests.reject', $registrationRequest) }}" onsubmit="return confirm('Reject this registration request?');">
                                @csrf
                                <button class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#efd5cf] bg-white px-4 text-xs font-bold text-[#9a4c3d] transition hover:bg-[#fff5f2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a64b39] sm:w-auto" type="submit">Reject</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
            @if ($registrationRequests->hasPages())
                <div class="border-t border-[#edf0ec] px-4 py-4 sm:px-6">{{ $registrationRequests->links() }}</div>
            @endif
        @endif
    </section>
</div>
@endsection
