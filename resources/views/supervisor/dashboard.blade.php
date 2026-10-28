@extends('layouts.supervisor')

@section('title', 'Supervisor dashboard · DASURECO')

@section('content')
@php
    $canCreateWorkOrders = auth()->user()->hasPermissionTo(\App\Models\Permission::CREATE_WORK_ORDERS);
    $canAssignWorkOrders = auth()->user()->hasPermissionTo(\App\Models\Permission::ASSIGN_WORK_ORDERS);
    $canReviewWorkOrders = auth()->user()->hasPermissionTo(\App\Models\Permission::REVIEW_WORK_ORDERS);
    $canCloseWorkOrders = auth()->user()->hasPermissionTo(\App\Models\Permission::CLOSE_WORK_ORDERS);
    $canDeleteWorkOrders = auth()->user()->hasPermissionTo(\App\Models\Permission::DELETE_WORK_ORDERS);
    $canUpdatePriority = auth()->user()->hasPermissionTo(\App\Models\Permission::UPDATE_PRIORITY);
@endphp
<div class="dashboard-section-layout grid gap-6 lg:grid-cols-[13rem_minmax(0,1fr)] lg:items-start">
    <nav class="dashboard-section-nav grid grid-cols-2 gap-2 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:grid-cols-4 lg:sticky lg:top-24 lg:grid-cols-1" aria-label="Supervisor dashboard sections">
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#performance">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 16.5h14M4.5 13l3.2-3.5 2.5 2 5.3-6"/>
                <path d="M12.5 5.5h3v3"/>
            </svg>
            Performance
        </a>
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#assign-work">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M10 3.5v13M3.5 10h13"/>
                <circle cx="10" cy="10" r="7"/>
            </svg>
            Assign work
        </a>
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#weekly-throughput">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 16.5h14M5 14V9m5 5V5m5 9v-7"/>
            </svg>
            Weekly throughput
        </a>
        <a class="dashboard-section-link inline-flex min-h-10 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="#field-crew-workers">
            <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M13.5 16.5v-1.2a2.3 2.3 0 0 0-2.3-2.3H5.8a2.3 2.3 0 0 0-2.3 2.3v1.2"/>
                <circle cx="8.5" cy="6.2" r="2.7"/>
                <path d="M14 3.8a2.7 2.7 0 0 1 0 5.2m2.5 7.5v-1.2a2.3 2.3 0 0 0-1.8-2.2"/>
            </svg>
            Field crew workers
        </a>
    </nav>
    <div class="flex min-w-0 flex-col gap-7">
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-emerald-700">
                <span class="size-2 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,.15)]" aria-hidden="true"></span>
                Supervisor workspace
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-slate-900 sm:text-4xl">Good day, {{ auth()->user()->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Assign field work, monitor progress, and review completed submissions.</p>
        </div>
        <p class="text-xs font-medium text-slate-500">{{ now()->format('l, F j, Y') }}</p>
    </section>

    <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6" aria-label="Work order summary">
        @php
            $summaryCards = [
                ['label' => 'Pending', 'count' => $counts['pending'], 'status' => 'pending', 'tone' => 'text-slate-700 bg-slate-100'],
                ['label' => 'Assigned', 'count' => $counts['assigned'], 'status' => 'assigned', 'tone' => 'text-sky-700 bg-sky-50'],
                ['label' => 'In progress', 'count' => $counts['in_progress'], 'status' => 'in_progress', 'tone' => 'text-blue-700 bg-blue-50'],
                ['label' => 'For review', 'count' => $counts['for_review'], 'status' => 'for_review', 'tone' => 'text-purple-700 bg-purple-50'],
                ['label' => 'Completed', 'count' => $counts['completed'], 'status' => 'completed', 'tone' => 'text-emerald-700 bg-emerald-50'],
                ['label' => 'Urgent', 'count' => $counts['urgent'], 'status' => null, 'priority' => 'urgent', 'tone' => 'text-rose-700 bg-rose-50'],
            ];
        @endphp
        @foreach ($summaryCards as $card)
            <a class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:p-5"
               href="{{ route('supervisor.dashboard', $card['status'] ? ['status' => $card['status']] : (isset($card['priority']) ? ['priority' => $card['priority']] : [])) }}">
                <span class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold leading-5 text-slate-600">{{ $card['label'] }}</span>
                    <span class="grid size-8 place-items-center rounded-lg {{ $card['tone'] }} text-sm font-bold">{{ $card['label'] === 'Urgent' ? '!' : '•' }}</span>
                </span>
                <span class="mt-4 block text-2xl font-semibold tracking-tight text-slate-900">{{ $card['count'] }}</span>
            </a>
        @endforeach
    </section>

    <section id="performance" class="scroll-mt-24" aria-labelledby="kpi-heading">
        <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
            <div>
                <h2 id="kpi-heading" class="text-sm font-semibold text-slate-900">Performance KPIs</h2>
                <p class="mt-1 text-xs text-slate-500">How quickly assigned work is being delivered and closed.</p>
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
                <h3 class="text-xs font-semibold text-slate-500">Average cycle time</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">
                    {{ $metrics->averageCycleDays() === null ? '—' : number_format($metrics->averageCycleDays(), 1) }}
                    @if ($metrics->averageCycleDays() !== null)<span class="text-lg font-semibold text-slate-500">d</span>@endif
                </p>
                <p class="mt-2 text-[11px] text-slate-500">Raised to completed</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="text-xs font-semibold text-slate-500">On-time delivery</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">
                    {{ $metrics->onTimeRate() === null ? '—' : number_format($metrics->onTimeRate(), 1) }}
                    @if ($metrics->onTimeRate() !== null)<span class="text-lg font-semibold text-slate-500">%</span>@endif
                </p>
                <p class="mt-2 text-[11px] text-slate-500">Completed by the schedule</p>
            </article>
            <article class="rounded-2xl border p-4 shadow-sm sm:p-5 {{ $metrics->overdue() > 0 ? 'border-rose-200 bg-rose-50/40' : 'border-slate-200 bg-white' }}">
                <h3 class="text-xs font-semibold text-slate-500">Overdue</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight {{ $metrics->overdue() > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ number_format($metrics->overdue()) }}</p>
                <p class="mt-2 text-[11px] text-slate-500">Past the scheduled date</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="text-xs font-semibold text-slate-500">Awaiting review</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($metrics->awaitingReview()) }}</p>
                <p class="mt-2 text-[11px] leading-4 text-slate-500">
                    Submitted and not yet decided
                    @if ($metrics->approvedPendingClosure() > 0)
                        <span class="mt-0.5 block font-semibold text-amber-700">{{ number_format($metrics->approvedPendingClosure()) }} approved, ready to close</span>
                    @endif
                </p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="text-xs font-semibold text-slate-500">Crew utilisation</h3>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">
                    {{ $fieldPersonnel->isEmpty() ? '—' : number_format($fieldPersonnel->sum('active_work_orders_count') / max(1, $fieldPersonnel->count()), 1) }}
                </p>
                <p class="mt-2 text-[11px] text-slate-500">Open assignments per field account</p>
            </article>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <section id="weekly-throughput" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="throughput-heading">
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

    @if ($unassignedWorkOrders->isNotEmpty())
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="text-sm font-semibold text-slate-900">Engineering work requirements · ready to assign</h2>
                <p class="mt-1 text-xs text-slate-500">Assign a field crew and schedule for each requirement registered by Engineering.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($unassignedWorkOrders as $workOrder)
                    <article class="flex flex-col gap-4 px-5 py-4 lg:flex-row lg:items-center lg:justify-between sm:px-6">
                        <div class="min-w-0">
                            <p class="font-mono text-[11px] font-semibold text-slate-500">{{ $workOrder->work_order_number }} · {{ $workOrder->category }} · {{ $workOrder->priority }}</p>
                            <h3 class="mt-1 text-sm font-semibold text-slate-900">{{ $workOrder->title }}</h3>
                            @if ($workOrder->asset)
                                <p class="mt-1 text-xs text-slate-500">Asset: {{ $workOrder->asset->asset_number }} · {{ $workOrder->asset->name }}</p>
                            @endif
                        </div>
                        <div class="flex w-full flex-col gap-2 lg:max-w-3xl lg:flex-row lg:items-end">
                            @if ($canAssignWorkOrders)
                                <form class="grid w-full gap-2 sm:grid-cols-[minmax(12rem,1fr)_minmax(12rem,1fr)_auto]" method="POST" action="{{ route('supervisor.work-orders.assign', $workOrder) }}">
                                    @csrf
                                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Field personnel
                                        <select class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" name="assigned_personnel_id" required>
                                            <option value="">Select a crew member</option>
                                            @foreach ($fieldPersonnel as $person)
                                                <option value="{{ $person->id }}">{{ $person->name }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Schedule
                                        <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" type="datetime-local" name="scheduled_at">
                                    </label>
                                    <button class="mt-auto inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">Assign</button>
                                </form>
                            @endif
                            @if ($canDeleteWorkOrders && $workOrder->canBeDeleted())
                                <form method="POST" action="{{ route('supervisor.work-orders.destroy', $workOrder) }}" onsubmit="return confirm('Delete work order {{ $workOrder->work_order_number }}? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-rose-200 bg-white px-4 text-xs font-bold text-rose-600 transition hover:bg-rose-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500 lg:w-auto" type="submit">Delete</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section id="assign-work" class="scroll-mt-24 grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="flex min-w-0 flex-col gap-6">
            @if ($canCreateWorkOrders)
                <details class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" @if ($errors->any()) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 marker:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:px-6">
                    <span>
                        <span class="block text-sm font-semibold text-slate-900">Create and assign work order</span>
                        <span class="mt-1 block text-xs text-slate-500">Set the priority, field personnel, related asset, and schedule.</span>
                    </span>
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-emerald-600 text-xl font-medium text-white transition group-open:rotate-45 shadow-sm" aria-hidden="true">+</span>
                </summary>
                <form class="grid gap-4 border-t border-slate-100 px-5 py-5 sm:grid-cols-2 sm:px-6" method="POST" action="{{ route('supervisor.work-orders.store') }}">
                    @csrf
                    <label class="grid gap-1.5 text-xs font-semibold text-slate-700 sm:col-span-2" for="title">Work order title
                        <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="title" name="title" value="{{ old('title') }}" maxlength="255" required>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-slate-700 sm:col-span-2" for="description">Instructions / description
                        <textarea class="min-h-20 rounded-lg border border-slate-300 bg-white p-3 text-sm font-normal leading-5 text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="description" name="description" maxlength="10000">{{ old('description') }}</textarea>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="category">Work category
                        <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="category" name="category" value="{{ old('category') }}" placeholder="Maintenance, repair, inspection..." maxlength="255" required>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="priority">Priority
                        <select class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="priority" name="priority" required>
                            @foreach ($priorities as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="assigned_personnel_id">Assign to
                        <select class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="assigned_personnel_id" name="assigned_personnel_id" required>
                            <option value="">Select field personnel</option>
                            @foreach ($fieldPersonnel as $person)
                                <option value="{{ $person->id }}" @selected((string) old('assigned_personnel_id') === (string) $person->id)>{{ $person->name }} · {{ $person->active_work_orders_count }} active</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="asset_id">Related asset <span class="font-normal text-slate-500">Optional</span>
                        <select class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="asset_id" name="asset_id">
                            <option value="">No asset linked</option>
                            @foreach ($assets as $asset)
                                <option value="{{ $asset->id }}" @selected((string) old('asset_id') === (string) $asset->id)>{{ $asset->asset_number }} · {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-slate-700 sm:col-span-2" for="scheduled_at">Scheduled date and time <span class="font-normal text-slate-500">Optional</span>
                        <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 sm:max-w-sm" id="scheduled_at" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at') }}">
                    </label>
                    <div class="sm:col-span-2">
                        <button class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 disabled:cursor-not-allowed disabled:bg-slate-300 shadow-sm" type="submit" @disabled($fieldPersonnel->isEmpty())>Create work order</button>
                        @if ($fieldPersonnel->isEmpty())
                            <p class="mt-2 text-xs text-rose-600">There are no active field personnel to assign. Activate a field account first.</p>
                        @endif
                    </div>
                </form>
                </details>
            @endif

            <section id="work-orders" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-5 sm:px-6">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="text-lg font-semibold tracking-tight text-slate-900">All work orders</h2>
                            <p class="mt-1 text-xs text-slate-500">Monitor assignments and review submitted field work.</p>
                        </div>
                        <nav class="flex flex-wrap gap-1 rounded-xl bg-slate-100 p-1" aria-label="Filter work orders by status">
                            <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold transition {{ $filter === null && $priorityFilter === null ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}" href="{{ route('supervisor.dashboard') }}" @if ($filter === null && $priorityFilter === null) aria-current="page" @endif>All</a>
                            @foreach ($statuses as $status => $label)
                                <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold transition {{ $filter === $status && $priorityFilter === null ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}" href="{{ route('supervisor.dashboard', ['status' => $status]) }}" @if ($filter === $status && $priorityFilter === null) aria-current="page" @endif>{{ $label }}</a>
                            @endforeach
                            <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold transition {{ $priorityFilter === 'urgent' ? 'bg-white text-rose-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}" href="{{ route('supervisor.dashboard', ['priority' => 'urgent']) }}" @if ($priorityFilter === 'urgent') aria-current="page" @endif>Urgent</a>
                        </nav>
                    </div>
                </div>

                @if ($workOrders->isEmpty())
                    <div class="px-6 py-6">
                        <x-empty-state
                            icon="clipboard"
                            :title="$filter ? 'No work orders in this status' : ($priorityFilter ? 'No urgent work orders' : 'No work orders yet')"
                            description="Create an assignment above to get field work moving."
                        />
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($workOrders as $workOrder)
                            <article class="px-4 py-5 transition hover:bg-slate-50/70 sm:px-6">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-mono text-[11px] font-semibold tracking-wide text-slate-500">{{ $workOrder->work_order_number }}</span>
                                            <x-priority-badge :priority="$workOrder->priority" />
                                            <x-status-badge :status="$workOrder->status" :label="$workOrder->supervisor_approved_at && $workOrder->status === 'for_review' ? 'Approved · ready to close' : null" />
                                        </div>
                                        <h3 class="mt-2 text-base font-semibold text-slate-900">{{ $workOrder->title }}</h3>
                                        @if ($workOrder->description)
                                            <p class="mt-1 max-w-3xl whitespace-pre-line text-sm leading-6 text-slate-600">{{ $workOrder->description }}</p>
                                        @endif
                                        <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500">
                                            <span><strong class="font-semibold text-slate-700">Category:</strong> {{ $workOrder->category }}</span>
                                            <span><strong class="font-semibold text-slate-700">Assigned:</strong> {{ $workOrder->assignedPersonnel?->name ?? 'Unassigned' }}</span>
                                            @if ($workOrder->asset)
                                                <span><strong class="font-semibold text-slate-700">Asset:</strong> {{ $workOrder->asset->asset_number }} · {{ $workOrder->asset->name }}</span>
                                            @endif
                                            <span><strong class="font-semibold text-slate-700">Schedule:</strong> {{ $workOrder->scheduled_at?->format('M j, Y · g:i A') ?? 'Not set' }}</span>
                                        </div>
                                        @if ($workOrder->submitted_at)
                                            <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 p-3.5">
                                                <p class="text-[10px] font-bold uppercase tracking-[.12em] text-slate-500">Field submission · {{ $workOrder->submitted_at->format('M j, Y · g:i A') }}</p>
                                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-800">{{ $workOrder->work_performed }}</p>
                                                @if ($workOrder->field_notes)
                                                    <p class="mt-2 whitespace-pre-line text-xs leading-5 text-slate-600"><strong class="text-slate-700">Field notes:</strong> {{ $workOrder->field_notes }}</p>
                                                @endif
                                                @if ($workOrder->photo_paths)
                                                    <div class="mt-3 flex flex-wrap gap-2">
                                                        @foreach ($workOrder->photo_paths as $index => $photoPath)
                                                            <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 hover:border-emerald-300" href="{{ route('supervisor.work-orders.photos.show', [$workOrder, $index]) }}" target="_blank" rel="noopener">View field photo {{ $index + 1 }}</a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if ($workOrder->return_notes)
                                                    <p class="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-2.5 text-xs leading-5 text-amber-900"><strong>Correction requested:</strong> {{ $workOrder->return_notes }}</p>
                                                @endif
                                                @if ($workOrder->materialUsages->isNotEmpty())
                                                    <p class="mt-2 text-xs text-slate-600"><strong class="text-slate-700">Materials:</strong>
                                                        @foreach ($workOrder->materialUsages as $usage)
                                                            {{ $usage->quantity }} {{ $usage->material->unit }} {{ $usage->material->name }}{{ $loop->last ? '' : ', ' }}
                                                        @endforeach
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    @if ($workOrder->status === 'for_review')
                                        <div class="grid w-full gap-2 lg:w-72 lg:shrink-0">
                                            @if ($workOrder->supervisor_approved_at)
                                                @if ($canCloseWorkOrders)
                                                    <form method="POST" action="{{ route('supervisor.work-orders.close', $workOrder) }}">
                                                        @csrf
                                                        <button class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-slate-900 px-4 text-xs font-bold text-white transition hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 shadow-sm" type="submit">Close approved work order</button>
                                                    </form>
                                                @endif
                                            @elseif ($canReviewWorkOrders)
                                                <form method="POST" action="{{ route('supervisor.work-orders.review', $workOrder) }}">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="approve">
                                                    <button class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">Approve submission</button>
                                                </form>
                                                <form class="grid gap-2 rounded-xl border border-amber-200 bg-amber-50/40 p-3" method="POST" action="{{ route('supervisor.work-orders.review', $workOrder) }}">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="return">
                                                    <label class="grid gap-1.5 text-[11px] font-semibold text-amber-900" for="return_notes_{{ $workOrder->id }}">Return for correction
                                                        <textarea class="min-h-20 rounded-lg border border-amber-300 bg-white p-2.5 text-xs font-normal leading-5 text-slate-800 outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10" id="return_notes_{{ $workOrder->id }}" name="return_notes" maxlength="5000" required>{{ old('return_notes') }}</textarea>
                                                    </label>
                                                    <button class="inline-flex h-9 items-center justify-center rounded-lg border border-amber-300 bg-white px-3 text-xs font-bold text-amber-900 transition hover:bg-amber-100/60 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600" type="submit">Send back to field personnel</button>
                                                </form>
                                            @endif
                                        </div>
                                    @elseif (in_array($workOrder->status, \App\Models\WorkOrder::DELETABLE_STATUSES, true))
                                        <div class="flex w-full flex-wrap items-center justify-end gap-2 lg:w-72 lg:shrink-0">
                                            @if ($canUpdatePriority)
                                                <form class="flex items-center gap-2" method="POST" action="{{ route('supervisor.work-orders.priority', $workOrder) }}">
                                                    @csrf
                                                    <label class="sr-only" for="priority_{{ $workOrder->id }}">Priority for work order {{ $workOrder->work_order_number }}</label>
                                                    <select class="h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-xs font-semibold text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="priority_{{ $workOrder->id }}" name="priority">
                                                        @foreach ($priorities as $value => $label)
                                                            <option value="{{ $value }}" @selected($workOrder->priority === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" type="submit">Set priority</button>
                                                </form>
                                            @endif
                                            @if ($canDeleteWorkOrders && $workOrder->canBeDeleted())
                                                <form method="POST" action="{{ route('supervisor.work-orders.destroy', $workOrder) }}" onsubmit="return confirm('Delete work order {{ $workOrder->work_order_number }}? This cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-white px-3 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500" type="submit">
                                                        <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h12M8.5 6V4.5h3V6m-5 0 .8 9.5a1 1 0 0 0 1 .9h3.4a1 1 0 0 0 1-.9L12 6"/></svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if ($workOrders->hasPages())
                        <div class="border-t border-slate-100 px-4 py-4 sm:px-6">{{ $workOrders->links() }}</div>
                    @endif
                @endif
            </section>
        </div>

        <aside id="field-crew-workers" class="scroll-mt-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-5">
                <h2 class="text-base font-semibold text-slate-900">Field crew workload</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">Active assignments by field personnel.</p>
            </div>
            @if ($fieldPersonnel->isEmpty())
                <p class="px-5 py-8 text-center text-xs leading-5 text-slate-500">No active field personnel accounts are available.</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($fieldPersonnel as $person)
                        <li class="flex items-center justify-between gap-3 px-5 py-4 hover:bg-slate-50/50 transition">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $person->name }}</span>
                                <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $person->email }}</span>
                                <span class="mt-0.5 block text-[11px] text-slate-400">{{ $person->completed_work_orders_count }} completed</span>
                            </span>
                            <span class="shrink-0 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800 border border-emerald-100">{{ $person->active_work_orders_count }} active</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>
    </section>
</div>
</div>
@endsection
