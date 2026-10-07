@extends('layouts.admin')

@section('title', 'Admin dashboard · DASURECO')

@section('content')
@php
    $statusTones = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'assigned' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'in_progress' => 'bg-violet-50 text-violet-700 ring-violet-100',
        'for_review' => 'bg-orange-50 text-orange-700 ring-orange-100',
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
    ];
@endphp

<div class="grid gap-6 lg:gap-7">
    <section class="relative isolate overflow-hidden rounded-3xl bg-[#12382b] px-5 py-6 text-white shadow-[0_18px_50px_rgba(16,48,35,.12)] sm:px-8 sm:py-8">
        <div class="absolute -right-16 -top-28 -z-10 size-80 rounded-full border border-white/10 shadow-[0_0_0_36px_rgba(255,255,255,.025),0_0_0_72px_rgba(255,255,255,.02)]"></div>
        <div class="relative flex flex-col justify-between gap-6 md:flex-row md:items-end">
            <div class="max-w-2xl">
                <p class="text-[11px] font-bold uppercase tracking-[.2em] text-[#a8d79e]">System administration</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] sm:text-4xl">Admin overview</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-[#c1d3c8]">Good day, {{ auth()->user()->name }}. Here’s the latest across your people and field operations.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <p class="text-xs font-medium text-[#c1d3c8]">{{ now()->format('l, F j, Y') }}</p>
                <a class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-[#b5dda9] px-4 text-xs font-bold text-[#173d2e] transition hover:bg-[#c8e9bd] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" href="{{ route('admin.registration-requests') }}">
                    Review requests
                    @if ($pendingRegistrationRequests > 0)
                        <span class="grid min-w-5 place-items-center rounded-full bg-[#173d2e] px-1.5 py-0.5 text-[10px] text-white">{{ $pendingRegistrationRequests }}</span>
                    @else
                        <span aria-hidden="true">→</span>
                    @endif
                </a>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="System summary">
        <article class="rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] sm:p-5">
            <h2 class="text-xs font-semibold text-[#78857b]">Total users</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-[#23352a]">{{ number_format($totalUsers) }}</p>
            <p class="mt-1 text-[11px] text-[#879188]">Across all system roles</p>
        </article>
        <article class="rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] sm:p-5">
            <h2 class="text-xs font-semibold text-[#78857b]">Active users</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-[#23352a]">{{ number_format($activeUsers) }}</p>
            <p class="mt-1 text-[11px] text-[#879188]">{{ number_format($inactiveUsers) }} inactive</p>
        </article>
        <a class="group rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] transition hover:border-[#c5dec5] hover:shadow-[0_10px_28px_rgba(30,54,35,.07)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b] sm:p-5" href="{{ route('admin.registration-requests') }}">
            <h2 class="text-xs font-semibold text-[#78857b]">Pending requests</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-[#23352a]">{{ number_format($pendingRegistrationRequests) }}</p>
            <p class="mt-1 text-[11px] text-[#879188] group-hover:text-[#356947]">Review access requests <span aria-hidden="true">→</span></p>
        </a>
        <article class="rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] sm:p-5">
            <h2 class="text-xs font-semibold text-[#78857b]">Urgent work orders</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-[#23352a]">{{ number_format($urgentWorkOrders) }}</p>
            <p class="mt-1 text-[11px] text-[#879188]">Still open and needing attention</p>
        </article>
    </section>

    <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]" aria-labelledby="work-order-status-heading">
        <div class="flex flex-col justify-between gap-1 border-b border-[#edf0ec] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
            <div>
                <h2 id="work-order-status-heading" class="text-sm font-semibold text-[#26382c]">Work order pipeline</h2>
                <p class="mt-1 text-xs text-[#879188]">A live snapshot of work moving through the system.</p>
            </div>
            <p class="text-xs font-semibold text-[#607066]">{{ number_format($totalWorkOrders) }} total</p>
        </div>
        <div class="grid grid-cols-2 divide-x divide-y divide-[#edf0ec] sm:grid-cols-3 sm:divide-y-0 lg:grid-cols-5">
            @foreach ($workOrderStatuses as $status => $label)
                <div class="px-5 py-4 sm:px-6">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold ring-1 ring-inset {{ $statusTones[$status] ?? 'bg-[#f4f7f3] text-[#607066] ring-[#e2e9e1]' }}">{{ $label }}</span>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-[#23352a]">{{ number_format($workOrdersByStatus->get($status, 0)) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,.85fr)_minmax(0,1.5fr)]">
        <section class="rounded-2xl border border-[#e2e9e1] bg-white p-5 shadow-[0_8px_28px_rgba(29,55,37,.035)] sm:p-6" aria-labelledby="users-by-role-heading">
            <div>
                <h2 id="users-by-role-heading" class="text-sm font-semibold text-[#26382c]">Team composition</h2>
                <p class="mt-1 text-xs text-[#879188]">Users by role · Account distribution across the team.</p>
            </div>
            <dl class="mt-5 grid gap-4">
                @foreach ($roleLabels as $role => $label)
                    @php
                        $roleCount = (int) $usersByRole->get($role, 0);
                        $roleWidth = $totalUsers > 0 ? ($roleCount / $totalUsers) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs font-medium text-[#607066]">{{ $label }}</dt>
                            <dd class="text-xs font-bold tabular-nums text-[#344a3b]">{{ number_format($roleCount) }}</dd>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-[#edf2ec]" role="img" aria-label="{{ $label }}: {{ $roleCount }} of {{ $totalUsers }} users">
                            <div class="h-full rounded-full bg-[#4c9163]" style="width: {{ $roleWidth }}%"></div>
                        </div>
                    </div>
                @endforeach
            </dl>
            <a class="mt-6 inline-flex items-center gap-2 text-xs font-bold text-[#356947] hover:text-[#214d32] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#32714b]" href="{{ route('admin.registration-requests') }}">
                Manage access requests <span aria-hidden="true">→</span>
            </a>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]" aria-labelledby="recent-activity-heading">
            <div class="border-b border-[#edf0ec] px-5 py-4 sm:px-6">
                <h2 id="recent-activity-heading" class="text-sm font-semibold text-[#26382c]">Recent system activity</h2>
                <p class="mt-1 text-xs text-[#879188]">Latest account and operational events.</p>
            </div>
            @if ($recentActivity->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-semibold text-[#536258]">No activity recorded yet</p>
                    <p class="mt-1 text-xs text-[#879188]">Important system actions will appear here.</p>
                </div>
            @else
                <ol class="divide-y divide-[#edf0ec]">
                    @foreach ($recentActivity as $activity)
                        <li class="flex gap-3 px-5 py-3.5 sm:px-6">
                            <span class="mt-0.5 grid size-9 shrink-0 place-items-center rounded-xl bg-[#edf6ec] text-xs font-bold text-[#438055]" aria-hidden="true">{{ strtoupper(substr($activity->user?->name ?? 'S', 0, 1)) }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs leading-5 text-[#44544a]">
                                    <span class="font-semibold text-[#2f4035]">{{ $activity->user?->name ?? 'System' }}</span>
                                    {{ \Illuminate\Support\Str::headline($activity->action) }}
                                    @if ($activity->record_type)
                                        <span class="text-[#849087]">· {{ class_basename($activity->record_type) }} #{{ $activity->record_id }}</span>
                                    @endif
                                </p>
                                @if ($activity->details)
                                    <p class="mt-0.5 break-words text-xs leading-5 text-[#7d8980]">{{ $activity->details }}</p>
                                @endif
                                <time class="mt-1 block text-[10px] text-[#9aa49b]" datetime="{{ $activity->created_at?->toIso8601String() }}">
                                    {{ $activity->created_at?->diffForHumans() ?? 'Time unavailable' }}
                                    @if ($activity->created_at)
                                        <span aria-hidden="true">·</span> {{ $activity->created_at->format('M j, g:i A') }}
                                    @endif
                                </time>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>
</div>
@endsection
