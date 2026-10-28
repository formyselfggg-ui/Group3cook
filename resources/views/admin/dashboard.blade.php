@extends('layouts.admin')

@section('title', 'Admin dashboard · DASURECO')

@section('content')

<div class="dashboard-section-layout grid gap-6 lg:grid-cols-[13rem_minmax(0,1fr)] lg:items-start">
    <nav class="dashboard-section-nav grid grid-cols-2 gap-2 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:grid-cols-3 lg:sticky lg:top-24 lg:grid-cols-1" aria-label="Admin dashboard sections">
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#admin-overview">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 16.5h14M4.5 13l3.2-3.5 2.5 2 5.3-6"/>
                <path d="M12.5 5.5h3v3"/>
            </svg>
            Overview
        </a>
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#delivery-performance">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 16.5h14M4.5 13l3.2-3.5 2.5 2 5.3-6"/>
                <path d="M12.5 5.5h3v3"/>
            </svg>
            Performance
        </a>
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#weekly-throughput-and-work-aging">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 16.5h14M5 14V9m5 5V5m5 9v-7"/>
            </svg>
            Weekly throughput &amp; work aging
        </a>
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#crew-performance">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M13.5 16.5v-1.2a2.3 2.3 0 0 0-2.3-2.3H5.8a2.3 2.3 0 0 0-2.3 2.3v1.2"/>
                <circle cx="8.5" cy="6.2" r="2.7"/>
                <path d="M14 3.8a2.7 2.7 0 0 1 0 5.2m2.5 7.5v-1.2a2.3 2.3 0 0 0-1.8-2.2"/>
            </svg>
            Field crew
        </a>
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#material-risk">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m10 2.5 6.5 3.7v7.6L10 17.5l-6.5-3.7V6.2L10 2.5Z"/>
                <path d="m3.8 6.4 6.2 3.5 6.2-3.5M10 9.9v7.3"/>
            </svg>
            Material risk
        </a>
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#work-order-pipeline">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3.5" y="4" width="13" height="12" rx="1.5"/>
                <path d="M6.5 7.5h7m-7 3h7m-7 3h4"/>
            </svg>
            Work order pipeline
        </a>
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#team-composition">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="7" cy="7" r="2.5"/>
                <circle cx="14" cy="8" r="2"/>
                <path d="M2.8 16v-1.1A2.9 2.9 0 0 1 5.7 12h2.6a2.9 2.9 0 0 1 2.9 2.9V16m1.1-3.8h1.1a2.5 2.5 0 0 1 2.5 2.5v1.3"/>
            </svg>
            Team composition
        </a>
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#recent-activity">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="10" cy="10" r="7"/>
                <path d="M10 6v4l2.5 1.5"/>
            </svg>
            Recent activity
        </a>
        @if (auth()->user()->hasPermissionTo(\App\Models\Permission::MANAGE_USERS))
            <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="{{ route('admin.users.index') }}">
                <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="7" cy="7" r="2.5"/>
                    <circle cx="14" cy="8" r="2"/>
                    <path d="M2.8 16v-1.1A2.9 2.9 0 0 1 5.7 12h2.6a2.9 2.9 0 0 1 2.9 2.9V16m1.1-3.8h1.1a2.5 2.5 0 0 1 2.5 2.5v1.3"/>
                </svg>
                User accounts
            </a>
        @endif
        @if (auth()->user()->hasPermissionTo(\App\Models\Permission::MANAGE_PERMISSIONS))
            <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="{{ route('admin.roles.index') }}">
                <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10 2.8 16 5v4.4c0 3.6-2.4 6.2-6 7.8-3.6-1.6-6-4.2-6-7.8V5l6-2.2Z"/>
                    <path d="m7.5 9.8 1.7 1.7 3.5-3.7"/>
                </svg>
                Roles &amp; permissions
            </a>
        @endif
        <a class="dashboard-section-link inline-flex min-h-10 items-center justify-between gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="{{ route('admin.registration-requests') }}">
            <span class="inline-flex items-center gap-2">
                <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 3.5h8l4 4v9H4v-13Z"/>
                    <path d="M12 3.5v4h4m-9 3h6m-6 3h6"/>
                </svg>
                Review requests
            </span>
            @if ($pendingRegistrationRequests > 0)
                <span class="grid min-w-5 place-items-center rounded-full bg-emerald-50 px-1.5 py-0.5 text-[10px] font-bold text-emerald-800 border border-emerald-200">{{ $pendingRegistrationRequests }}</span>
            @endif
        </a>
    </nav>
    <div class="grid min-w-0 gap-6 lg:gap-7">
    <section id="admin-overview" class="scroll-mt-24 relative isolate overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-[#0a261e] to-slate-950 px-5 py-6 text-white shadow-md border border-emerald-500/20 sm:px-8 sm:py-8">
        <div class="absolute -right-16 -top-28 -z-10 size-80 rounded-full border border-white/10 shadow-[0_0_0_36px_rgba(255,255,255,.025),0_0_0_72px_rgba(255,255,255,.02)]"></div>
        <div class="relative flex flex-col justify-between gap-6 md:flex-row md:items-end">
            <div class="max-w-2xl">
                <p class="text-[11px] font-bold uppercase tracking-[.2em] text-emerald-300">System administration</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] sm:text-4xl">Admin overview</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">Good day, {{ auth()->user()->name }}. Here’s the latest across your people and field operations.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <p class="text-xs font-medium text-slate-300">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>
    </section>

    <section id="system-summary" class="scroll-mt-24 grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="System summary">
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <h2 class="text-xs font-semibold text-slate-500">Total users</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($totalUsers) }}</p>
            <p class="mt-1 text-[11px] text-slate-500">Across all system roles</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <h2 class="text-xs font-semibold text-slate-500">Active users</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($activeUsers) }}</p>
            <p class="mt-1 text-[11px] text-slate-500">{{ number_format($inactiveUsers) }} inactive</p>
        </article>
        <a class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-emerald-300 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:p-5" href="{{ route('admin.registration-requests') }}">
            <h2 class="text-xs font-semibold text-slate-500">Pending requests</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($pendingRegistrationRequests) }}</p>
            <p class="mt-1 text-[11px] text-slate-500 group-hover:text-emerald-700 font-medium">Review access requests <span aria-hidden="true">→</span></p>
        </a>
        <article class="rounded-2xl border p-4 shadow-sm sm:p-5 {{ $urgentWorkOrders > 0 ? 'border-rose-200 bg-rose-50/40' : 'border-slate-200 bg-white' }}">
            <h2 class="text-xs font-semibold text-slate-500">Urgent work orders</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight {{ $urgentWorkOrders > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ number_format($urgentWorkOrders) }}</p>
            <p class="mt-1 text-[11px] text-slate-500">Still open and needing attention</p>
        </article>
    </section>

    <section id="delivery-performance" class="scroll-mt-24" aria-labelledby="delivery-kpi-heading">
        <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
            <div>
                <h2 id="delivery-kpi-heading" class="text-sm font-semibold text-slate-900">Delivery performance</h2>
                <p class="mt-1 text-xs text-slate-500">How work orders are progressing across every role.</p>
            </div>
            <p class="text-xs font-semibold text-slate-500">{{ number_format($metrics->total()) }} work orders tracked</p>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="text-xs font-semibold text-slate-500">Completion rate</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($metrics->completionRate(), 1) }}<span class="text-lg font-semibold text-slate-500">%</span></p>
                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="Completion rate {{ $metrics->completionRate() }} percent">
                    <div class="h-full rounded-full bg-emerald-600" style="width: {{ $metrics->completionRate() }}%"></div>
                </div>
                <p class="mt-2 text-[11px] text-slate-500">{{ number_format($metrics->completed()) }} of {{ number_format($metrics->total()) }} closed</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="text-xs font-semibold text-slate-500">Open pipeline</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($metrics->open()) }}</p>
                <p class="mt-2 text-[11px] text-slate-500">Work orders still in flight</p>
            </article>
            <article class="rounded-2xl border p-4 shadow-sm sm:p-5 {{ $metrics->overdue() > 0 ? 'border-rose-200 bg-rose-50/40' : 'border-slate-200 bg-white' }}">
                <h3 class="text-xs font-semibold text-slate-500">Overdue</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight {{ $metrics->overdue() > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ number_format($metrics->overdue()) }}</p>
                <p class="mt-2 text-[11px] text-slate-500">Past the scheduled date</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="text-xs font-semibold text-slate-500">On-time delivery</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">
                    {{ $metrics->onTimeRate() === null ? '—' : number_format($metrics->onTimeRate(), 1) }}
                    @if ($metrics->onTimeRate() !== null)<span class="text-lg font-semibold text-slate-500">%</span>@endif
                </p>
                <p class="mt-2 text-[11px] text-slate-500">Completed by the schedule</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="text-xs font-semibold text-slate-500">Average cycle time</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">
                    {{ $metrics->averageCycleDays() === null ? '—' : number_format($metrics->averageCycleDays(), 1) }}
                    @if ($metrics->averageCycleDays() !== null)<span class="text-lg font-semibold text-slate-500">d</span>@endif
                </p>
                <p class="mt-2 text-[11px] text-slate-500">Raised to completed</p>
            </article>
            <article class="rounded-2xl border p-4 shadow-sm sm:p-5 {{ $lowStockCount > 0 ? 'border-amber-200 bg-amber-50/40' : 'border-slate-200 bg-white' }}">
                <h3 class="text-xs font-semibold text-slate-500">Low-stock materials</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight {{ $lowStockCount > 0 ? 'text-amber-800' : 'text-slate-900' }}">{{ number_format($lowStockCount) }}</p>
                <p class="mt-2 text-[11px] text-slate-500">At or below reorder level</p>
            </article>
        </div>
    </section>

    <div id="weekly-throughput-and-work-aging" class="scroll-mt-24 grid gap-5 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="throughput-heading">
            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
                <div>
                    <h2 id="throughput-heading" class="text-sm font-semibold text-slate-900">Weekly throughput</h2>
                    <p class="mt-1 text-xs text-slate-500">Work orders raised against work orders completed.</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-semibold text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-slate-300" aria-hidden="true"></span> Raised</span>
                    <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-emerald-600" aria-hidden="true"></span> Completed</span>
                </div>
            </div>
            @php
                $throughput = $metrics->weeklyThroughput();
                $maxThroughput = max(1, collect($throughput)->max(fn (array $week): int => max($week['raised'], $week['completed'])));
            @endphp
            <div class="mt-6 flex items-end gap-2 sm:gap-4">
                @foreach ($throughput as $week)
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <div class="flex h-32 w-full items-end justify-center gap-1.5">
                            <div class="w-1/2 rounded-t-md bg-slate-200 transition hover:bg-slate-300" style="height: {{ max(4, ($week['raised'] / $maxThroughput) * 100) }}%" title="{{ $week['label'] }}: {{ $week['raised'] }} raised"></div>
                            <div class="w-1/2 rounded-t-md bg-emerald-600 transition hover:bg-emerald-500" style="height: {{ max(4, ($week['completed'] / $maxThroughput) * 100) }}%" title="{{ $week['label'] }}: {{ $week['completed'] }} completed"></div>
                        </div>
                        <p class="text-[10px] font-semibold text-slate-500">{{ $week['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="aging-heading">
            <h2 id="aging-heading" class="text-sm font-semibold text-slate-900">Open work aging</h2>
            <p class="mt-1 text-xs text-slate-500">How long open work orders have been running.</p>
            @php
                $aging = $metrics->agingBuckets();
                $agingMax = max(1, max($aging));
            @endphp
            <dl class="mt-5 grid gap-3">
                @foreach ($aging as $bucket => $count)
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs font-medium text-slate-600">{{ $bucket }}</dt>
                            <dd class="text-xs font-bold tabular-nums text-slate-900">{{ number_format($count) }}</dd>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $bucket === '15+ days' && $count > 0 ? 'bg-rose-500' : 'bg-emerald-600' }}" style="width: {{ ($count / $agingMax) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </dl>
            <h3 class="mt-6 text-xs font-semibold text-slate-500">Open work by priority</h3>
            <ul class="mt-3 grid gap-2">
                @foreach ($metrics->priorityMix() as $priority => $count)
                    <li class="flex items-center justify-between gap-3 text-xs">
                        <span class="flex items-center gap-2 font-medium text-slate-600">
                            <x-priority-badge :priority="$priority" />
                        </span>
                        <span class="font-bold tabular-nums text-slate-900">{{ number_format($count) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <section id="crew-performance" class="scroll-mt-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="crew-performance-heading">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 id="crew-performance-heading" class="text-sm font-semibold text-slate-900">Field crew performance</h2>
                <p class="mt-1 text-xs text-slate-500">Completed and open assignments per field account.</p>
            </div>
            @if ($crewPerformance->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm font-semibold text-slate-700">No field accounts yet</p>
                    <p class="mt-1 text-xs text-slate-500">Crew throughput appears once field personnel are added.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[28rem] text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-3 font-bold sm:px-6">Field account</th>
                                <th class="px-5 py-3 text-right font-bold">Open</th>
                                <th class="px-5 py-3 text-right font-bold">Completed</th>
                                <th class="px-5 py-3 text-right font-bold sm:px-6">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($crewPerformance as $member)
                                <tr class="transition hover:bg-slate-50/50">
                                    <td class="px-5 py-3.5 sm:px-6">
                                        <p class="text-sm font-semibold text-slate-900">{{ $member->name }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $member->email }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-sm tabular-nums text-slate-600">{{ number_format($member->open_total) }}</td>
                                    <td class="px-5 py-3.5 text-right text-sm font-semibold tabular-nums text-emerald-700">{{ number_format($member->completed_total) }}</td>
                                    <td class="px-5 py-3.5 text-right text-sm tabular-nums text-slate-600 sm:px-6">{{ number_format($member->assigned_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section id="material-risk" class="scroll-mt-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="material-risk-heading">
            <div class="flex flex-col justify-between gap-1 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                <div>
                    <h2 id="material-risk-heading" class="text-sm font-semibold text-slate-900">Material risk</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ number_format($materialCount) }} materials tracked · {{ number_format($assetCount) }} assets registered.</p>
                </div>
                <p class="text-xs font-semibold {{ $lowStockCount > 0 ? 'text-amber-800' : 'text-slate-500' }}">{{ number_format($lowStockCount) }} below reorder level</p>
            </div>
            @if ($lowStockMaterials->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm font-semibold text-slate-700">Inventory is healthy</p>
                    <p class="mt-1 text-xs text-slate-500">No materials are at or below their reorder level.</p>
                </div>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($lowStockMaterials as $material)
                        <li class="flex items-center justify-between gap-4 px-5 py-3.5 sm:px-6">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900">{{ $material->name }}</p>
                                <p class="font-mono text-[11px] text-slate-500">{{ $material->sku }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-semibold tabular-nums text-rose-600">{{ rtrim(rtrim((string) $material->quantity_on_hand, '0'), '.') }} {{ $material->unit }}</p>
                                <p class="text-[11px] text-slate-500">reorder at {{ rtrim(rtrim((string) $material->reorder_level, '0'), '.') }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <section id="work-order-pipeline" class="scroll-mt-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="work-order-status-heading">
        <div class="flex flex-col justify-between gap-1 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:px-6">
            <div>
                <h2 id="work-order-status-heading" class="text-sm font-semibold text-slate-900">Work order pipeline</h2>
                <p class="mt-1 text-xs text-slate-500">A live snapshot of work moving through the system.</p>
            </div>
            <p class="text-xs font-semibold text-slate-500">{{ number_format($totalWorkOrders) }} total</p>
        </div>
        <div class="grid grid-cols-2 divide-x divide-y divide-slate-100 sm:grid-cols-3 sm:divide-y-0 lg:grid-cols-5">
            @foreach ($workOrderStatuses as $status => $label)
                <div class="px-5 py-4 sm:px-6">
                    <x-status-badge :status="$status" :label="$label" />
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">{{ number_format($workOrdersByStatus->get($status, 0)) }}</p>
                    @if ($status === \App\Models\WorkOrder::STATUS_FOR_REVIEW)
                        <p class="mt-1 text-[11px] leading-4 text-slate-500">
                            {{ number_format($metrics->awaitingReview()) }} awaiting a decision
                            @if ($metrics->approvedPendingClosure() > 0)
                                <span class="mt-0.5 block font-semibold text-amber-800">{{ number_format($metrics->approvedPendingClosure()) }} approved, ready to close</span>
                            @endif
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,.85fr)_minmax(0,1.5fr)]">
        <section id="team-composition" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="users-by-role-heading">
            <div>
                <h2 id="users-by-role-heading" class="text-sm font-semibold text-slate-900">Team composition</h2>
                <p class="mt-1 text-xs text-slate-500">Users by role · Account distribution across the team.</p>
            </div>
            <dl class="mt-5 grid gap-4">
                @foreach ($roleLabels as $role => $label)
                    @php
                        $roleCount = (int) $usersByRole->get($role, 0);
                        $roleWidth = $totalUsers > 0 ? ($roleCount / $totalUsers) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs font-medium text-slate-600">{{ $label }}</dt>
                            <dd class="text-xs font-bold tabular-nums text-slate-900">{{ number_format($roleCount) }}</dd>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="{{ $label }}: {{ $roleCount }} of {{ $totalUsers }} users">
                            <div class="h-full rounded-full bg-emerald-600" style="width: {{ $roleWidth }}%"></div>
                        </div>
                    </div>
                @endforeach
            </dl>
            <a class="mt-6 inline-flex items-center gap-2 text-xs font-bold text-emerald-700 hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-600" href="{{ route('admin.registration-requests') }}">
                Manage access requests <span aria-hidden="true">→</span>
            </a>
        </section>

        <section id="recent-activity" class="scroll-mt-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="recent-activity-heading">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 id="recent-activity-heading" class="text-sm font-semibold text-slate-900">Recent system activity</h2>
                <p class="mt-1 text-xs text-slate-500">Latest account and operational events.</p>
            </div>
            @if ($recentActivity->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-semibold text-slate-700">No activity recorded yet</p>
                    <p class="mt-1 text-xs text-slate-500">Important system actions will appear here.</p>
                </div>
            @else
                <ol class="divide-y divide-slate-100">
                    @foreach ($recentActivity as $activity)
                        <li class="flex gap-3 px-5 py-3.5 sm:px-6 hover:bg-slate-50/50 transition">
                            <span class="mt-0.5 grid size-9 shrink-0 place-items-center rounded-xl bg-emerald-50 text-xs font-bold text-emerald-700 border border-emerald-100" aria-hidden="true">{{ strtoupper(substr($activity->user?->name ?? 'S', 0, 1)) }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs leading-5 text-slate-700">
                                    <span class="font-semibold text-slate-900">{{ $activity->user?->name ?? 'System' }}</span>
                                    {{ \Illuminate\Support\Str::headline($activity->action) }}
                                    @if ($activity->record_type)
                                        <span class="text-slate-500">· {{ class_basename($activity->record_type) }} #{{ $activity->record_id }}</span>
                                    @endif
                                </p>
                                @if ($activity->details)
                                    <p class="mt-0.5 break-words text-xs leading-5 text-slate-500">{{ $activity->details }}</p>
                                @endif
                                <time class="mt-1 block text-[10px] text-slate-400" datetime="{{ $activity->created_at?->toIso8601String() }}">
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
</div>
@endsection
