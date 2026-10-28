@extends('layouts.admin')

@section('title', 'Registration requests · DASURECO')

@section('content')
<div class="flex flex-col gap-6">
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.16em] text-emerald-700">System administration</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-slate-900 sm:text-4xl">Registration requests</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Review access requests. Accounts are created only when approved.</p>
        </div>
        <a class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 shadow-sm" href="{{ route('admin.dashboard') }}">Back to admin overview</a>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if ($registrationRequests->isEmpty())
            <div class="px-6 py-16 text-center">
                <h2 class="text-sm font-semibold text-slate-700">No pending requests</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">New registration requests will appear here for review.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($registrationRequests as $registrationRequest)
                    <article class="flex flex-col gap-5 px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between hover:bg-slate-50/50 transition">
                        <div class="min-w-0">
                            <h2 class="text-sm font-semibold text-slate-900">{{ $registrationRequest->name }}</h2>
                            <p class="mt-1 text-xs text-slate-500">{{ $registrationRequest->email }}</p>
                            <div class="mt-3 flex flex-wrap gap-2 text-[10px] font-semibold uppercase tracking-wide">
                                <span class="rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-slate-700">Requested: {{ $registrationRequest->requested_role === 'supervisor' ? 'Supervisor / Dispatcher' : 'Field personnel' }}</span>
                                <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-amber-800">Pending · {{ $registrationRequest->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <form class="flex flex-col gap-2 sm:flex-row sm:items-end" method="POST" action="{{ route('admin.registration-requests.approve', $registrationRequest) }}">
                                @csrf
                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Approve as
                                    <select class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" name="role" required>
                                        <option value="field_personnel" @selected($registrationRequest->requested_role === 'field_personnel')>Field personnel</option>
                                        <option value="supervisor" @selected($registrationRequest->requested_role === 'supervisor')>Supervisor / Dispatcher</option>
                                        <option value="operations">Operations / Engineering Staff</option>
                                    </select>
                                </label>
                                <button class="inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.registration-requests.reject', $registrationRequest) }}" onsubmit="return confirm('Reject this registration request?');">
                                @csrf
                                <button class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-rose-200 bg-white px-4 text-xs font-bold text-rose-600 transition hover:bg-rose-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500 sm:w-auto" type="submit">Reject</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
            @if ($registrationRequests->hasPages())
                <div class="border-t border-slate-100 px-4 py-4 sm:px-6">{{ $registrationRequests->links() }}</div>
            @endif
        @endif
    </section>
</div>
@endsection
