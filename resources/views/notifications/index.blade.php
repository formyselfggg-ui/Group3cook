@extends('layouts.app')

@section('title', 'Notifications · DASURECO')
@section('homeRoute', match (auth()->user()->role) {
    \App\Models\User::ROLE_ADMIN => route('admin.dashboard'),
    \App\Models\User::ROLE_OPERATIONS => route('operations.dashboard'),
    \App\Models\User::ROLE_SUPERVISOR => route('supervisor.dashboard'),
    default => route('field.dashboard'),
})
@section('workspace', 'Notifications')
@section('roleLabel', 'Notifications')
@section('footer', 'DASURECO operations · Notification centre')

@section('content')
<div class="flex flex-col gap-6">
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-emerald-700">
                <span class="size-2 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,.15)]" aria-hidden="true"></span>
                Notification centre
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-slate-900 sm:text-4xl">Your notifications</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Assignments, priority changes, review decisions and inventory alerts for your role.</p>
        </div>
        @if (auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-xs font-bold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">Mark all as read</button>
            </form>
        @endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if ($notifications->isEmpty())
            <div class="px-6 py-6">
                <x-empty-state
                    icon="inbox"
                    title="Nothing to report"
                    description="Notifications about assignments, reviews and inventory will appear here."
                />
            </div>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $isUnread = $notification->read_at === null;
                        $target = \App\Support\NotificationTarget::resolve($data, auth()->user());
                    @endphp
                    <li class="flex flex-col gap-3 px-4 py-4 transition hover:bg-slate-50/70 sm:flex-row sm:items-start sm:justify-between sm:px-6 {{ $isUnread ? 'bg-emerald-50/20' : '' }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($isUnread)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold leading-4 text-emerald-800">
                                        <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                        New
                                    </span>
                                @endif
                                <p class="text-sm font-semibold text-slate-900">{{ $data['title'] ?? 'Notification' }}</p>
                            </div>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $data['message'] ?? '' }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at?->format('M j, Y · g:i A') }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if ($target)
                                <a class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="{{ $target }}">Open</a>
                            @endif
                            @if ($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button class="inline-flex h-9 items-center justify-center rounded-lg bg-emerald-600 px-3 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">Mark as read</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($notifications->hasPages())
                <div class="border-t border-slate-100 px-4 py-4 sm:px-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
