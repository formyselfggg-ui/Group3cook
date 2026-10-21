@extends('layouts.admin')

@section('title', 'Admin dashboard · DASURECO')

@section('content')

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
                @if (auth()->user()->hasPermissionTo(\App\Models\Permission::MANAGE_USERS))
                    <a class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-white/20 px-4 text-xs font-bold text-[#e7f3e4] transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" href="{{ route('admin.users.index') }}">
                        User accounts
                        <span aria-hidden="true">→</span>
                    </a>
                @endif
                @if (auth()->user()->hasPermissionTo(\App\Models\Permission::MANAGE_PERMISSIONS))
                    <a class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-white/20 px-4 text-xs font-bold text-[#e7f3e4] transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" href="{{ route('admin.roles.index') }}">
                        Roles &amp; permissions
                        <span aria-hidden="true">→</span>
                    </a>
                @endif
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
            <h2 class="text-xs font-semibold text-[#5f6b62]">Total users</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-[#23352a]">{{ number_format($totalUsers) }}</p>
            <p class="mt-1 text-[11px] text-[#5f6b62]">Across all system roles</p>
        </article>
        <article class="rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] sm:p-5">
            <h2 class="text-xs font-semibold text-[#5f6b62]">Active users</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-[#23352a]">{{ number_format($activeUsers) }}</p>
            <p class="mt-1 text-[11px] text-[#5f6b62]">{{ number_format($inactiveUsers) }} inactive</p>
        </article>
        <a class="group rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] transition hover:border-[#c5dec5] hover:shadow-[0_10px_28px_rgba(30,54,35,.07)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b] sm:p-5" href="{{ route('admin.registration-requests') }}">
            <h2 class="text-xs font-semibold text-[#5f6b62]">Pending requests</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-[#23352a]">{{ number_format($pendingRegistrationRequests) }}</p>
            <p class="mt-1 text-[11px] text-[#5f6b62] group-hover:text-[#356947]">Review access requests <span aria-hidden="true">→</span></p>
        </a>
        <article class="rounded-2xl border p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] sm:p-5 {{ $urgentWorkOrders > 0 ? 'border-[#efd5cf] bg-[#fff7f4]' : 'border-[#e3eae1] bg-white' }}">
            <h2 class="text-xs font-semibold text-[#5f6b62]">Urgent work orders</h2>
            <p class="mt-4 text-3xl font-semibold tracking-tight {{ $urgentWorkOrders > 0 ? 'text-[#a0442f]' : 'text-[#23352a]' }}">{{ number_format($urgentWorkOrders) }}</p>
            <p class="mt-1 text-[11px] text-[#5f6b62]">Still open and needing attention</p>
        </article>
    </section>

    <section aria-labelledby="delivery-kpi-heading">
        <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
            <div>
                <h2 id="delivery-kpi-heading" class="text-sm font-semibold text-[#26382c]">Delivery performance</h2>
                <p class="mt-1 text-xs text-[#5f6b62]">How work orders are progressing across every role.</p>
            </div>
            <p class="text-xs font-semibold text-[#55635a]">{{ number_format($metrics->total()) }} work orders tracked</p>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
            <article class="rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] sm:p-5">
                <h3 class="text-xs font-semibold text-[#5f6b62]">Completion rate</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-[#23352a]">{{ number_format($metrics->completionRate(), 1) }}<span class="text-lg font-semibold text-[#647068]">%</span></p>
                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-[#edf2ec]" role="img" aria-label="Completion rate {{ $metrics->completionRate() }} percent">
                    <div class="h-full rounded-full bg-[#4c9163]" style="width: {{ $metrics->completionRate() }}%"></div>
                </div>
                <p class="mt-2 text-[11px] text-[#5f6b62]">{{ number_format($metrics->completed()) }} of {{ number_format($metrics->total()) }} closed</p>
            </article>
            <article class="rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] sm:p-5">
                <h3 class="text-xs font-semibold text-[#5f6b62]">Open pipeline</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-[#23352a]">{{ number_format($metrics->open()) }}</p>
                <p class="mt-2 text-[11px] text-[#5f6b62]">Work orders still in flight</p>
            </article>
            <article class="rounded-2xl border p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] sm:p-5 {{ $metrics->overdue() > 0 ? 'border-[#efd5cf] bg-[#fff7f4]' : 'border-[#e3eae1] bg-white' }}">
                <h3 class="text-xs font-semibold text-[#5f6b62]">Overdue</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight {{ $metrics->overdue() > 0 ? 'text-[#a0442f]' : 'text-[#23352a]' }}">{{ number_format($metrics->overdue()) }}</p>
                <p class="mt-2 text-[11px] text-[#5f6b62]">Past the scheduled date</p>
            </article>
            <article class="rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] sm:p-5">
                <h3 class="text-xs font-semibold text-[#5f6b62]">On-time delivery</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-[#23352a]">
                    {{ $metrics->onTimeRate() === null ? '—' : number_format($metrics->onTimeRate(), 1) }}
                    @if ($metrics->onTimeRate() !== null)<span class="text-lg font-semibold text-[#647068]">%</span>@endif
                </p>
                <p class="mt-2 text-[11px] text-[#5f6b62]">Completed by the schedule</p>
            </article>
            <article class="rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] sm:p-5">
                <h3 class="text-xs font-semibold text-[#5f6b62]">Average cycle time</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-[#23352a]">
                    {{ $metrics->averageCycleDays() === null ? '—' : number_format($metrics->averageCycleDays(), 1) }}
                    @if ($metrics->averageCycleDays() !== null)<span class="text-lg font-semibold text-[#647068]">d</span>@endif
                </p>
                <p class="mt-2 text-[11px] text-[#5f6b62]">Raised to completed</p>
            </article>
            <article class="rounded-2xl border p-4 shadow-[0_5px_20px_rgba(30,54,35,.025)] sm:p-5 {{ $lowStockCount > 0 ? 'border-[#f0e0c4] bg-[#fffdf7]' : 'border-[#e3eae1] bg-white' }}">
                <h3 class="text-xs font-semibold text-[#5f6b62]">Low-stock materials</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight {{ $lowStockCount > 0 ? 'text-[#8a6a1f]' : 'text-[#23352a]' }}">{{ number_format($lowStockCount) }}</p>
                <p class="mt-2 text-[11px] text-[#5f6b62]">At or below reorder level</p>
            </article>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <section class="rounded-2xl border border-[#e2e9e1] bg-white p-5 shadow-[0_8px_28px_rgba(29,55,37,.035)] sm:p-6" aria-labelledby="throughput-heading">
            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
                <div>
                    <h2 id="throughput-heading" class="text-sm font-semibold text-[#26382c]">Weekly throughput</h2>
                    <p class="mt-1 text-xs text-[#5f6b62]">Work orders raised against work orders completed.</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-semibold text-[#5f6b62]">
                    <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-[#cfe3d3]" aria-hidden="true"></span> Raised</span>
                    <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-[#176442]" aria-hidden="true"></span> Completed</span>
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
                            <div class="w-1/2 rounded-t-md bg-[#cfe3d3]" style="height: {{ max(4, ($week['raised'] / $maxThroughput) * 100) }}%" title="{{ $week['label'] }}: {{ $week['raised'] }} raised"></div>
                            <div class="w-1/2 rounded-t-md bg-[#176442]" style="height: {{ max(4, ($week['completed'] / $maxThroughput) * 100) }}%" title="{{ $week['label'] }}: {{ $week['completed'] }} completed"></div>
                        </div>
                        <p class="text-[10px] font-semibold text-[#647068]">{{ $week['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-[#e2e9e1] bg-white p-5 shadow-[0_8px_28px_rgba(29,55,37,.035)] sm:p-6" aria-labelledby="aging-heading">
            <h2 id="aging-heading" class="text-sm font-semibold text-[#26382c]">Open work aging</h2>
            <p class="mt-1 text-xs text-[#5f6b62]">How long open work orders have been running.</p>
            @php
                $aging = $metrics->agingBuckets();
                $agingMax = max(1, max($aging));
            @endphp
            <dl class="mt-5 grid gap-3">
                @foreach ($aging as $bucket => $count)
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs font-medium text-[#55635a]">{{ $bucket }}</dt>
                            <dd class="text-xs font-bold tabular-nums text-[#344a3b]">{{ number_format($count) }}</dd>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-[#edf2ec]">
                            <div class="h-full rounded-full {{ $bucket === '15+ days' && $count > 0 ? 'bg-[#c96a4a]' : 'bg-[#4c9163]' }}" style="width: {{ ($count / $agingMax) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </dl>
            <h3 class="mt-6 text-xs font-semibold text-[#5f6b62]">Open work by priority</h3>
            <ul class="mt-3 grid gap-2">
                @foreach ($metrics->priorityMix() as $priority => $count)
                    <li class="flex items-center justify-between gap-3 text-xs">
                        <span class="flex items-center gap-2 font-medium text-[#55635a]">
                            <x-priority-badge :priority="$priority" />
                        </span>
                        <span class="font-bold tabular-nums text-[#344a3b]">{{ number_format($count) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]" aria-labelledby="crew-performance-heading">
            <div class="border-b border-[#edf0ec] px-5 py-4 sm:px-6">
                <h2 id="crew-performance-heading" class="text-sm font-semibold text-[#26382c]">Field crew performance</h2>
                <p class="mt-1 text-xs text-[#5f6b62]">Completed and open assignments per field account.</p>
            </div>
            @if ($crewPerformance->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm font-semibold text-[#536258]">No field accounts yet</p>
                    <p class="mt-1 text-xs text-[#5f6b62]">Crew throughput appears once field personnel are added.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[28rem] text-left">
                        <thead>
                            <tr class="border-b border-[#edf0ec] text-[10px] font-bold uppercase tracking-wide text-[#5f6b62]">
                                <th class="px-5 py-3 font-bold sm:px-6">Field account</th>
                                <th class="px-5 py-3 text-right font-bold">Open</th>
                                <th class="px-5 py-3 text-right font-bold">Completed</th>
                                <th class="px-5 py-3 text-right font-bold sm:px-6">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#edf0ec]">
                            @foreach ($crewPerformance as $member)
                                <tr class="transition hover:bg-[#fcfdfb]">
                                    <td class="px-5 py-3.5 sm:px-6">
                                        <p class="text-sm font-semibold text-[#344239]">{{ $member->name }}</p>
                                        <p class="text-[11px] text-[#5f6b62]">{{ $member->email }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-sm tabular-nums text-[#435448]">{{ number_format($member->open_total) }}</td>
                                    <td class="px-5 py-3.5 text-right text-sm font-semibold tabular-nums text-[#2c5f41]">{{ number_format($member->completed_total) }}</td>
                                    <td class="px-5 py-3.5 text-right text-sm tabular-nums text-[#435448] sm:px-6">{{ number_format($member->assigned_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]" aria-labelledby="material-risk-heading">
            <div class="flex flex-col justify-between gap-1 border-b border-[#edf0ec] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                <div>
                    <h2 id="material-risk-heading" class="text-sm font-semibold text-[#26382c]">Material risk</h2>
                    <p class="mt-1 text-xs text-[#5f6b62]">{{ number_format($materialCount) }} materials tracked · {{ number_format($assetCount) }} assets registered.</p>
                </div>
                <p class="text-xs font-semibold {{ $lowStockCount > 0 ? 'text-[#8a6a1f]' : 'text-[#55635a]' }}">{{ number_format($lowStockCount) }} below reorder level</p>
            </div>
            @if ($lowStockMaterials->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm font-semibold text-[#536258]">Inventory is healthy</p>
                    <p class="mt-1 text-xs text-[#5f6b62]">No materials are at or below their reorder level.</p>
                </div>
            @else
                <ul class="divide-y divide-[#edf0ec]">
                    @foreach ($lowStockMaterials as $material)
                        <li class="flex items-center justify-between gap-4 px-5 py-3.5 sm:px-6">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#344239]">{{ $material->name }}</p>
                                <p class="font-mono text-[11px] text-[#5f6b62]">{{ $material->sku }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-semibold tabular-nums text-[#a0442f]">{{ rtrim(rtrim((string) $material->quantity_on_hand, '0'), '.') }} {{ $material->unit }}</p>
                                <p class="text-[11px] text-[#5f6b62]">reorder at {{ rtrim(rtrim((string) $material->reorder_level, '0'), '.') }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]" aria-labelledby="work-order-status-heading">
        <div class="flex flex-col justify-between gap-1 border-b border-[#edf0ec] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
            <div>
                <h2 id="work-order-status-heading" class="text-sm font-semibold text-[#26382c]">Work order pipeline</h2>
                <p class="mt-1 text-xs text-[#5f6b62]">A live snapshot of work moving through the system.</p>
            </div>
            <p class="text-xs font-semibold text-[#55635a]">{{ number_format($totalWorkOrders) }} total</p>
        </div>
        <div class="grid grid-cols-2 divide-x divide-y divide-[#edf0ec] sm:grid-cols-3 sm:divide-y-0 lg:grid-cols-5">
            @foreach ($workOrderStatuses as $status => $label)
                <div class="px-5 py-4 sm:px-6">
                    <x-status-badge :status="$status" :label="$label" />
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-[#23352a]">{{ number_format($workOrdersByStatus->get($status, 0)) }}</p>
                    @if ($status === \App\Models\WorkOrder::STATUS_FOR_REVIEW)
                        <p class="mt-1 text-[11px] leading-4 text-[#5f6b62]">
                            {{ number_format($metrics->awaitingReview()) }} awaiting a decision
                            @if ($metrics->approvedPendingClosure() > 0)
                                <span class="mt-0.5 block font-semibold text-[#8a6a1f]">{{ number_format($metrics->approvedPendingClosure()) }} approved, ready to close</span>
                            @endif
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,.85fr)_minmax(0,1.5fr)]">
        <section class="rounded-2xl border border-[#e2e9e1] bg-white p-5 shadow-[0_8px_28px_rgba(29,55,37,.035)] sm:p-6" aria-labelledby="users-by-role-heading">
            <div>
                <h2 id="users-by-role-heading" class="text-sm font-semibold text-[#26382c]">Team composition</h2>
                <p class="mt-1 text-xs text-[#5f6b62]">Users by role · Account distribution across the team.</p>
            </div>
            <dl class="mt-5 grid gap-4">
                @foreach ($roleLabels as $role => $label)
                    @php
                        $roleCount = (int) $usersByRole->get($role, 0);
                        $roleWidth = $totalUsers > 0 ? ($roleCount / $totalUsers) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs font-medium text-[#55635a]">{{ $label }}</dt>
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
                <p class="mt-1 text-xs text-[#5f6b62]">Latest account and operational events.</p>
            </div>
            @if ($recentActivity->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-semibold text-[#536258]">No activity recorded yet</p>
                    <p class="mt-1 text-xs text-[#5f6b62]">Important system actions will appear here.</p>
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
                                        <span class="text-[#647068]">· {{ class_basename($activity->record_type) }} #{{ $activity->record_id }}</span>
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
