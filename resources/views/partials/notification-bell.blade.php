@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp

<a class="relative inline-flex size-10 shrink-0 items-center justify-center rounded-lg border border-white/15 bg-white/5 text-slate-200 transition hover:border-emerald-400/40 hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-400"
   href="{{ route('notifications.index') }}"
   aria-label="Notifications{{ $unreadCount > 0 ? ', '.$unreadCount.' unread' : '' }}">
    <svg class="size-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M10 3.2a4.6 4.6 0 0 0-4.6 4.6c0 3.4-1.2 4.6-1.2 4.6h11.6s-1.2-1.2-1.2-4.6A4.6 4.6 0 0 0 10 3.2Z"/>
        <path d="M8.4 15.4a1.7 1.7 0 0 0 3.2 0"/>
    </svg>
    @if ($unreadCount > 0)
        <span class="absolute -right-1.5 -top-1.5 grid min-w-5 place-items-center rounded-full bg-rose-600 px-1.5 py-0.5 text-[10px] font-bold text-white shadow-sm ring-2 ring-slate-950">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
    @endif
</a>
