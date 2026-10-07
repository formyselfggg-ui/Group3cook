@extends('layouts.admin')

@section('title', 'Admin dashboard · DASURECO')

@section('content')
<div class="grid gap-7">
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.16em] text-[#708775]">System administration</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-[#1a2a20] sm:text-4xl">Admin overview</h1>
            <p class="mt-2 text-sm leading-6 text-[#78857b]">A quick view of DASURECO accounts and field operations.</p>
        </div>
        <p class="text-xs font-medium text-[#8a958c]">{{ now()->format('l, F j, Y') }}</p>
    </section>

    <a class="flex flex-col justify-between gap-3 rounded-2xl border border-[#dce8da] bg-white p-5 shadow-[0_8px_28px_rgba(29,55,37,.035)] transition hover:border-[#c3d9c3] hover:bg-[#fbfdfb] sm:flex-row sm:items-center sm:px-6" href="{{ route('admin.registration-requests') }}">
        <span>
            <span class="block text-sm font-semibold text-[#26382c]">Registration requests</span>
            <span class="mt-1 block text-xs text-[#879188]">Review and approve or reject requests before accounts are created.</span>
        </span>
        <span class="inline-flex w-fit items-center gap-2 rounded-xl bg-[#edf6ec] px-3 py-2 text-xs font-bold text-[#356947]">
            {{ $pendingRegistrationRequests }} pending
            <span aria-hidden="true">→</span>
        </span>
    </a>

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="System summary">
        @foreach ([
            ['label' => 'Total users', 'value' => $totalUsers, 'tone' => 'bg-[#edf5ed] text-[#34724a]', 'icon' => 'U'],
            ['label' => 'Active users', 'value' => $activeUsers, 'tone' => 'bg-[#eef5fb] text-[#396b9a]', 'icon' => 'A'],
            ['label' => 'Work orders', 'value' => $totalWorkOrders, 'tone' => 'bg-[#f3eff8] text-[#805f98]', 'icon' => 'W'],
            ['label' => 'Urgent open', 'value' => $urgentWorkOrders, 'tone' => 'bg-[#fff0ec] text-[#a64b39]', 'icon' => '!'],
        ] as $card)
            <article class="rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_4px_18px_rgba(30,54,35,.025)] sm:p-5">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-xs font-semibold text-[#78857b]">{{ $card['label'] }}</h2>
                    <span class="grid size-8 place-items-center rounded-lg {{ $card['tone'] }} text-xs font-bold" aria-hidden="true">{{ $card['icon'] }}</span>
                </div>
                <p class="mt-4 text-2xl font-semibold tracking-tight text-[#23352a]">{{ $card['value'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,.85fr)_minmax(0,1.5fr)]">
        <section class="rounded-2xl border border-[#e2e9e1] bg-white p-5 shadow-[0_8px_28px_rgba(29,55,37,.035)] sm:p-6">
            <div>
                <h2 class="text-base font-semibold text-[#26382c]">Users by role</h2>
                <p class="mt-1 text-xs text-[#879188]">Account distribution across the team.</p>
            </div>
            <dl class="mt-5 grid gap-3">
                @foreach ($roleLabels as $role => $label)
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-[#f7f9f6] px-3 py-3">
                        <dt class="text-xs font-medium text-[#607066]">{{ $label }}</dt>
                        <dd class="min-w-8 rounded-full bg-white px-2 py-1 text-center text-xs font-bold text-[#356947] shadow-sm">{{ $usersByRole->get($role, 0) }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
            <div class="border-b border-[#edf0ec] px-5 py-5 sm:px-6">
                <h2 class="text-base font-semibold text-[#26382c]">Recent system activity</h2>
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
                        <li class="flex gap-3 px-5 py-4 sm:px-6">
                            <span class="mt-1 grid size-8 shrink-0 place-items-center rounded-full bg-[#edf6ec] text-xs font-bold text-[#438055]" aria-hidden="true">
                                {{ strtoupper(substr($activity->user?->name ?? 'S', 0, 1)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs leading-5 text-[#44544a]">
                                    <span class="font-semibold">{{ $activity->user?->name ?? 'System' }}</span>
                                    {{ \Illuminate\Support\Str::headline($activity->action) }}
                                    @if ($activity->record_type)
                                        <span class="text-[#849087]">· {{ class_basename($activity->record_type) }} #{{ $activity->record_id }}</span>
                                    @endif
                                </p>
                                @if ($activity->details)
                                    <p class="mt-1 break-words text-xs leading-5 text-[#7d8980]">{{ $activity->details }}</p>
                                @endif
                                <time class="mt-1 block text-[10px] text-[#9aa49b]" datetime="{{ $activity->created_at?->toIso8601String() }}">
                                    {{ $activity->created_at?->diffForHumans() ?? 'Time unavailable' }}
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
