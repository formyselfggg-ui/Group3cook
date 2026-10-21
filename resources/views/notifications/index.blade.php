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
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-[#708775]">
                <span class="size-2 rounded-full bg-[#55a56c] shadow-[0_0_0_4px_rgba(85,165,108,.12)]" aria-hidden="true"></span>
                Notification centre
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-[#1a2a20] sm:text-4xl">Your notifications</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#5f6b62]">Assignments, priority changes, review decisions and inventory alerts for your role.</p>
        </div>
        @if (auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="inline-flex h-10 items-center justify-center rounded-xl border border-[#dce5dc] bg-white px-4 text-xs font-bold text-[#41634b] transition hover:bg-[#f5faf4] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">Mark all as read</button>
            </form>
        @endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
        @if ($notifications->isEmpty())
            <div class="px-6 py-6">
                <x-empty-state
                    icon="inbox"
                    title="Nothing to report"
                    description="Notifications about assignments, reviews and inventory will appear here."
                />
            </div>
        @else
                <ul class="divide-y divide-[#edf0ec]">
                    @foreach ($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $isUnread = $notification->read_at === null;
                            $target = \App\Support\NotificationTarget::resolve($data, auth()->user());
                        @endphp
                        <li class="flex flex-col gap-3 px-4 py-4 transition hover:bg-[#fcfdfb] sm:flex-row sm:items-start sm:justify-between sm:px-6 {{ $isUnread ? 'bg-[#f8fbf7]' : '' }}">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($isUnread)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#c6e0ca] bg-[#eaf5eb] px-2.5 py-1 text-[11px] font-semibold leading-4 text-[#2c5f41]">
                                            <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                            New
                                        </span>
                                    @endif
                                    <p class="text-sm font-semibold text-[#26382c]">{{ $data['title'] ?? 'Notification' }}</p>
                                </div>
                                <p class="mt-1 text-sm leading-6 text-[#5f6b62]">{{ $data['message'] ?? '' }}</p>
                                <p class="mt-1 text-xs text-[#647068]">{{ $notification->created_at?->format('M j, Y · g:i A') }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @if ($target)
                                    <a class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-[#dce5dc] bg-white px-3 text-xs font-semibold text-[#41634b] transition hover:bg-[#f5faf4] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" href="{{ $target }}">Open</a>
                                @endif
                                @if ($isUnread)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf
                                        <button class="inline-flex h-9 items-center justify-center rounded-lg bg-[#176442] px-3 text-xs font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">Mark as read</button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

            @if ($notifications->hasPages())
                <div class="border-t border-[#edf0ec] px-4 py-4 sm:px-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
