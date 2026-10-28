@extends('layouts.operations')

@section('title', 'Engineering dashboard · DASURECO')

@section('content')
@php
    $canCreateWorkOrders = auth()->user()->hasPermissionTo(\App\Models\Permission::CREATE_WORK_ORDERS);
    $canManageAssets = auth()->user()->hasPermissionTo(\App\Models\Permission::MANAGE_ASSETS);
    $canManageMaterials = auth()->user()->hasPermissionTo(\App\Models\Permission::MANAGE_MATERIALS);
    $canViewReports = auth()->user()->hasPermissionTo(\App\Models\Permission::VIEW_REPORTS);
@endphp
<div class="flex flex-col gap-7">
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-emerald-700">
                <span class="size-2 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,.15)]" aria-hidden="true"></span>
                Operations / Engineering
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-slate-900 sm:text-4xl">Engineering dashboard</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Register work requirements, manage electrical assets, and monitor field operations.</p>
        </div>
        <p class="text-xs font-medium text-slate-500">{{ now()->format('l, F j, Y') }}</p>
    </section>

    <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-7" aria-label="Operations summary">
        @foreach ([
            ['Total work orders', $counts['work_orders'], 'text-slate-900', route('operations.dashboard', ['status' => null])],
            ['Pending', $counts['pending'], 'text-slate-700', route('operations.dashboard', ['status' => 'pending'])],
            ['In progress', $counts['in_progress'], 'text-blue-700', route('operations.dashboard', ['status' => 'in_progress'])],
            ['Completed', $counts['completed'], 'text-emerald-700', route('operations.dashboard', ['status' => 'completed'])],
            ['Urgent', $counts['urgent'], 'text-rose-700', route('operations.dashboard', ['priority' => 'urgent'])],
            ['Registered assets', $counts['assets'], 'text-slate-900', '#assets'],
            ['Under maintenance', $counts['assets_under_maintenance'], 'text-amber-800', '#assets'],
        ] as [$label, $count, $tone, $href])
            <a class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:p-5" href="{{ $href }}">
                <span class="block text-xs font-semibold leading-5 text-slate-500">{{ $label }}</span>
                <span class="mt-3 block text-2xl font-semibold tracking-tight {{ $tone }}">{{ $count }}</span>
            </a>
        @endforeach
    </section>

    <section class="grid items-start gap-6 xl:grid-cols-2">
        @if ($canCreateWorkOrders)
            <details class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" @if ($errors->has('title') || $errors->has('category') || $errors->has('priority')) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 marker:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:px-6">
                <span>
                    <span class="block text-sm font-semibold text-slate-900">Register work requirement</span>
                    <span class="mt-1 block text-xs text-slate-500">New requirements are routed to the supervisor for field crew assignment.</span>
                </span>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-emerald-600 text-xl font-medium text-white transition group-open:rotate-45 shadow-sm" aria-hidden="true">+</span>
            </summary>
            <form class="grid gap-4 border-t border-slate-100 px-5 py-5 sm:grid-cols-2 sm:px-6" method="POST" action="{{ route('operations.work-orders.store') }}">
                @csrf
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700 sm:col-span-2" for="requirement_title">Work requirement title <span class="font-normal text-rose-600">Required</span>
                    <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="requirement_title" name="title" value="{{ old('title') }}" maxlength="255" required>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700 sm:col-span-2" for="requirement_description">Description / instructions
                    <textarea class="min-h-20 rounded-lg border border-slate-300 bg-white p-3 text-sm font-normal leading-5 text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="requirement_description" name="description" maxlength="10000">{{ old('description') }}</textarea>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="requirement_category">Work category <span class="font-normal text-rose-600">Required</span>
                    <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="requirement_category" name="category" value="{{ old('category') }}" placeholder="Maintenance, repair, inspection..." maxlength="255" required>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="requirement_priority">Priority <span class="font-normal text-rose-600">Required</span>
                    <select class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="requirement_priority" name="priority" required>
                        @foreach ($priorities as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="requirement_asset">Related asset <span class="font-normal text-slate-400">Optional</span>
                    <select class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="requirement_asset" name="asset_id">
                        <option value="">No asset linked</option>
                        @foreach ($assetOptions as $asset)
                            <option value="{{ $asset->id }}" @selected((string) old('asset_id') === (string) $asset->id)>{{ $asset->asset_number }} · {{ $asset->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="requirement_schedule">Requested schedule <span class="font-normal text-slate-400">Optional</span>
                    <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="requirement_schedule" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}">
                </label>
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm sm:col-span-2" type="submit">Send requirement to supervisor</button>
            </form>
            </details>
        @endif

        @if ($canManageAssets)
            <details id="assets" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" @if (request()->has('asset_status') || $errors->has('asset_number')) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 marker:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:px-6">
                <span>
                    <span class="block text-sm font-semibold text-slate-900">Register an electrical asset</span>
                    <span class="mt-1 block text-xs text-slate-500">Track transformers, meters, distribution equipment, cables, and more.</span>
                </span>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-emerald-600 text-xl font-medium text-white transition group-open:rotate-45 shadow-sm" aria-hidden="true">+</span>
            </summary>
            <form class="grid gap-4 border-t border-slate-100 px-5 py-5 sm:grid-cols-2 sm:px-6" method="POST" action="{{ route('operations.assets.store') }}">
                @csrf
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="asset_number">Asset ID
                    <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="asset_number" name="asset_number" value="{{ old('asset_number') }}" maxlength="255" required>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="asset_type">Asset type
                    <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="asset_type" name="asset_type" value="{{ old('asset_type') }}" placeholder="Transformer, electric meter..." maxlength="255" required>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700 sm:col-span-2" for="asset_name">Name / description
                    <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="asset_name" name="name" value="{{ old('name') }}" maxlength="255" required>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="asset_serial">Serial number <span class="font-normal text-slate-400">Optional</span>
                    <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="asset_serial" name="serial_number" value="{{ old('serial_number') }}" maxlength="255">
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="asset_status">Current status
                    <select class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="asset_status" name="current_status" required>
                        @foreach ($assetStatuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('current_status', 'active') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="asset_condition">Condition <span class="font-normal text-slate-400">Optional</span>
                    <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="asset_condition" name="condition" value="{{ old('condition') }}" placeholder="Good, fair, needs attention..." maxlength="255">
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-slate-700" for="asset_installed_on">Installation / acquisition date <span class="font-normal text-slate-400">Optional</span>
                    <input class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="asset_installed_on" type="date" name="installed_on" value="{{ old('installed_on') }}">
                </label>
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm sm:col-span-2" type="submit">Register asset</button>
            </form>
            </details>
        @endif
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-base font-semibold text-slate-900">Work orders by category</h2>
            <p class="mt-1 text-xs text-slate-500">Registered operational workload.</p>
            @if ($workOrdersByCategory->isEmpty())
                <div class="mt-5">
                    <x-empty-state
                        icon="chart"
                        title="No work order categories yet"
                        description="Register a work requirement to start building the operational picture."
                    />
                </div>
            @else
                <ul class="mt-5 grid gap-3">
                    @foreach ($workOrdersByCategory as $category)
                        <li class="flex items-center justify-between gap-4">
                            <span class="truncate text-sm font-medium text-slate-700">{{ $category->category }}</span>
                            <span class="shrink-0 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800 border border-emerald-100">{{ $category->work_orders_count }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-base font-semibold text-slate-900">Asset condition and status</h2>
            <p class="mt-1 text-xs text-slate-500">Current operational status and recorded condition for registered assets.</p>
            @if ($assets->isEmpty())
                <div class="mt-5">
                    <x-empty-state
                        icon="cube"
                        title="No assets have been registered"
                        description="Add an electrical asset above to track its condition and maintenance history."
                    />
                </div>
            @else
                <ul class="mt-5 grid gap-3">
                    @foreach ($assetStatuses as $status => $label)
                        <li class="flex items-center justify-between gap-4">
                            <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                            <span class="shrink-0 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800 border border-emerald-100">{{ $assetsByStatus->get($status)?->assets_count ?? 0 }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-5 border-t border-slate-100 pt-4">
                    <h3 class="text-xs font-semibold text-slate-700">Condition</h3>
                    <ul class="mt-3 grid gap-3">
                        @forelse ($assetsByCondition as $condition)
                            <li class="flex items-center justify-between gap-4">
                                <span class="text-sm font-medium text-slate-700">{{ $condition->condition ?: 'Not recorded' }}</span>
                                <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ $condition->assets_count }}</span>
                            </li>
                        @empty
                            <li class="text-xs text-slate-500">No asset conditions recorded.</li>
                        @endforelse
                    </ul>
                </div>
            @endif
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <article id="work-orders" class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Work orders and field records</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Review progress, linked assets, crews, and materials used.</p>
                    </div>
                    @if ($canViewReports)
                        <a class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="{{ route('operations.reports.export', request()->query()) }}">Export filtered CSV</a>
                    @endif
                </div>
                <form class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" method="GET" action="{{ route('operations.dashboard') }}">
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Status
                        <select class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800" name="status">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Priority
                        <select class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800" name="priority">
                            <option value="">All priorities</option>
                            @foreach ($priorities as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['priority'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Category
                        <select class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800" name="category">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Asset
                        <select class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800" name="asset_id">
                            <option value="">All assets</option>
                            @foreach ($assetOptions as $asset)
                                <option value="{{ $asset->id }}" @selected((string) ($filters['asset_id'] ?? '') === (string) $asset->id)>{{ $asset->asset_number }} · {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Assigned personnel
                        <select class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800" name="assigned_personnel_id">
                            <option value="">All personnel</option>
                            @foreach ($fieldPersonnel as $person)
                                <option value="{{ $person->id }}" @selected((string) ($filters['assigned_personnel_id'] ?? '') === (string) $person->id)>{{ $person->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Created from
                        <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Created to
                        <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                    </label>
                    <div class="flex items-end gap-2">
                        <button class="inline-flex h-10 flex-1 items-center justify-center rounded-lg bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 shadow-sm" type="submit">Apply filters</button>
                        <a class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 hover:bg-slate-50" href="{{ route('operations.dashboard') }}">Clear</a>
                    </div>
                </form>
            </div>

            @if ($workOrders->isEmpty())
                <div class="px-6 py-6">
                    <x-empty-state
                        icon="clipboard"
                        title="No work orders match these filters"
                        description="Change or clear filters to see more operational records."
                    />
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($workOrders as $workOrder)
                        <article class="px-4 py-5 transition hover:bg-slate-50/60 sm:px-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-[11px] font-semibold tracking-wide text-slate-500">{{ $workOrder->work_order_number }}</span>
                                <x-priority-badge :priority="$workOrder->priority" />
                                <x-status-badge :status="$workOrder->status" :label="$statuses[$workOrder->status] ?? null" />
                            </div>
                            <h3 class="mt-2 text-sm font-semibold text-slate-900">{{ $workOrder->title }}</h3>
                            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500">
                                <span><strong class="font-semibold text-slate-700">Category:</strong> {{ $workOrder->category }}</span>
                                <span><strong class="font-semibold text-slate-700">Asset:</strong> {{ $workOrder->asset ? $workOrder->asset->asset_number.' · '.$workOrder->asset->name : '—' }}</span>
                                <span><strong class="font-semibold text-slate-700">Assigned:</strong> {{ $workOrder->assignedPersonnel?->name ?? 'Awaiting assignment' }}</span>
                                <span><strong class="font-semibold text-slate-700">Created:</strong> {{ $workOrder->created_at->format('M j, Y') }}</span>
                                @if ($workOrder->scheduled_at)
                                    <span><strong class="font-semibold text-slate-700">Scheduled:</strong> {{ $workOrder->scheduled_at->format('M j, Y · g:i A') }}</span>
                                @endif
                            </div>
                            @if ($workOrder->description)
                                <p class="mt-2 whitespace-pre-line text-xs leading-5 text-slate-600">{{ $workOrder->description }}</p>
                            @endif
                            @if ($workOrder->work_performed || $workOrder->field_notes || $workOrder->materialUsages->isNotEmpty())
                                <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3.5">
                                    @if ($workOrder->work_performed)
                                        <p class="whitespace-pre-line text-xs leading-5 text-slate-800"><strong>Work performed:</strong> {{ $workOrder->work_performed }}</p>
                                    @endif
                                    @if ($workOrder->field_notes)
                                        <p class="mt-2 whitespace-pre-line text-xs leading-5 text-slate-600"><strong class="text-slate-700">Field notes:</strong> {{ $workOrder->field_notes }}</p>
                                    @endif
                                    @if ($workOrder->materialUsages->isNotEmpty())
                                        <p class="mt-2 text-xs text-slate-600"><strong class="text-slate-700">Materials used:</strong>
                                            @foreach ($workOrder->materialUsages as $usage)
                                                {{ $usage->quantity }} {{ $usage->material->unit }} {{ $usage->material->name }}{{ $loop->last ? '' : ', ' }}
                                            @endforeach
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
                @if ($workOrders->hasPages())
                    <div class="border-t border-slate-100 px-4 py-4 sm:px-6">{{ $workOrders->links() }}</div>
                @endif
            @endif
        </article>

        <aside class="flex flex-col gap-6">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-5">
                    <h2 class="text-base font-semibold text-slate-900">Material usage</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Top materials recorded against field work orders.</p>
                </div>
                @if ($materialUsage->isEmpty())
                    <div class="p-5">
                        <x-empty-state
                            icon="chart"
                            title="No material usage recorded"
                            description="Materials recorded by field crews will be ranked here."
                        />
                    </div>
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach ($materialUsage as $usage)
                            <li class="flex items-center justify-between gap-3 px-5 py-4">
                                <span class="min-w-0 truncate text-sm font-semibold text-slate-900">{{ $usage->material->name }}</span>
                                <span class="shrink-0 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800 border border-emerald-100">{{ $usage->quantity_used }} {{ $usage->material->unit }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-5">
                    <h2 class="text-base font-semibold text-slate-900">Recent maintenance activities</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Completed maintenance linked to asset records.</p>
                </div>
                @if ($recentMaintenance->isEmpty())
                    <div class="p-5">
                        <x-empty-state
                            icon="wrench"
                            title="No completed maintenance yet"
                            description="Closed work orders linked to assets will appear here."
                        />
                    </div>
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach ($recentMaintenance as $activity)
                            <li class="px-5 py-4 hover:bg-slate-50/50 transition">
                                <p class="font-mono text-[10px] font-semibold text-slate-500">{{ $activity->work_order_number }} · {{ $activity->completion_at?->format('M j, Y') }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $activity->asset->asset_number }} · {{ $activity->asset->name }}</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ $activity->category }} · {{ $activity->assignedPersonnel?->name ?? 'Personnel unavailable' }}</p>
                                @if ($activity->work_performed)
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-600">{{ $activity->work_performed }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </aside>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col justify-between gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:px-6">
            <div>
                <h2 class="text-base font-semibold text-slate-900" id="material-inventory-heading">Material inventory</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">Cables, connectors and electrical components available for field work.</p>
            </div>
            @if ($canManageMaterials)
                <button class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" type="button" onclick="document.getElementById('material-form').hidden = ! document.getElementById('material-form').hidden">
                    Register material
                </button>
            @endif
        </div>

        @if ($canManageMaterials)
            <form class="grid gap-4 border-b border-slate-100 px-5 py-5 sm:grid-cols-2 sm:px-6 lg:grid-cols-5" id="material-form" method="POST" action="{{ route('operations.materials.store') }}" hidden>
            @csrf
            <label class="grid gap-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-500" for="material_sku">SKU <span class="font-normal text-rose-600">Required</span>
                <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="material_sku" name="sku" value="{{ old('sku') }}" maxlength="255" required>
            </label>
            <label class="grid gap-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-500" for="material_name">Name <span class="font-normal text-rose-600">Required</span>
                <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="material_name" name="name" value="{{ old('name') }}" maxlength="255" required>
            </label>
            <label class="grid gap-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-500" for="material_unit">Unit <span class="font-normal text-rose-600">Required</span>
                <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="material_unit" name="unit" value="{{ old('unit') }}" placeholder="m, pcs, kg" maxlength="50" required>
            </label>
            <label class="grid gap-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-500" for="material_quantity">Quantity on hand <span class="font-normal text-rose-600">Required</span>
                <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="material_quantity" name="quantity_on_hand" type="number" min="0" step="0.01" value="{{ old('quantity_on_hand', 0) }}" required>
            </label>
            <label class="grid gap-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-500" for="material_reorder">Reorder level <span class="font-normal text-rose-600">Required</span>
                <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="material_reorder" name="reorder_level" type="number" min="0" step="0.01" value="{{ old('reorder_level', 0) }}" required>
            </label>
            <div class="sm:col-span-2 lg:col-span-5">
                <button class="inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">Add to inventory</button>
            </div>
            </form>
        @endif

        @if ($materials->isEmpty())
            <div class="px-6 py-6">
                <x-empty-state
                    icon="cube"
                    title="No materials registered"
                    description="Register cables, connectors and components so field crews can record what they use."
                />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[42rem] text-left">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3 font-bold sm:px-6">Material</th>
                            <th class="px-5 py-3 font-bold">On hand</th>
                            <th class="px-5 py-3 font-bold">Reorder level</th>
                            <th class="px-5 py-3 font-bold">Status</th>
                            <th class="px-5 py-3 text-right font-bold sm:px-6">Adjust</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($materials as $material)
                            <tr class="align-top transition hover:bg-slate-50/50">
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="text-sm font-semibold text-slate-900">{{ $material->name }}</p>
                                    <p class="font-mono text-[11px] text-slate-500">{{ $material->sku }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ rtrim(rtrim((string) $material->quantity_on_hand, '0'), '.') }} {{ $material->unit }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ rtrim(rtrim((string) $material->reorder_level, '0'), '.') }} {{ $material->unit }}</td>
                                <td class="px-5 py-4">
                                    @if ($material->isLowStock())
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-semibold leading-4 text-rose-700">
                                            <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 3.5 17 16.5H3L10 3.5Z"/><path d="M10 8.5v3"/><path d="M10 13.8h.01"/></svg>
                                            Low stock
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold leading-4 text-emerald-700">
                                            <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                            In stock
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right sm:px-6">
                                    @if ($canManageMaterials)
                                        <details class="text-left">
                                            <summary class="inline-flex h-9 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Adjust stock</summary>
                                            <form class="mt-3 grid gap-3 rounded-xl border border-slate-200 bg-slate-50/90 p-4 sm:min-w-72" method="POST" action="{{ route('operations.materials.update', $material) }}">
                                                @csrf
                                                @method('PATCH')
                                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500" for="material_name_{{ $material->id }}">Name
                                                    <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800" id="material_name_{{ $material->id }}" name="name" value="{{ $material->name }}" maxlength="255" required>
                                                </label>
                                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500" for="material_unit_{{ $material->id }}">Unit
                                                    <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800" id="material_unit_{{ $material->id }}" name="unit" value="{{ $material->unit }}" maxlength="50" required>
                                                </label>
                                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500" for="material_quantity_{{ $material->id }}">Quantity on hand
                                                    <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800" id="material_quantity_{{ $material->id }}" name="quantity_on_hand" type="number" min="0" step="0.01" value="{{ $material->quantity_on_hand }}" required>
                                                </label>
                                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500" for="material_reorder_{{ $material->id }}">Reorder level
                                                    <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800" id="material_reorder_{{ $material->id }}" name="reorder_level" type="number" min="0" step="0.01" value="{{ $material->reorder_level }}" required>
                                                </label>
                                                <button class="inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">Save inventory record</button>
                                            </form>
                                        </details>
                                    @else
                                        <span class="text-xs text-slate-400">Read only</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
            <h2 class="text-base font-semibold text-slate-900">Asset register</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">Manage current asset details and link them to work-order maintenance records.</p>
        </div>
        @if ($assets->isEmpty())
            <div class="px-6 py-6">
                <x-empty-state
                    icon="cube"
                    title="Register an asset above to begin the asset history"
                    description="Each asset keeps its own condition, status, and linked maintenance record."
                />
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($assets as $asset)
                    <details class="group px-5 py-4 sm:px-6">
                        <summary class="flex cursor-pointer list-none flex-col gap-2 marker:hidden sm:flex-row sm:items-center sm:justify-between">
                            <span>
                                <span class="font-mono text-[11px] font-semibold text-slate-500">{{ $asset->asset_number }}</span>
                                <span class="ml-2 text-sm font-semibold text-slate-900">{{ $asset->name }}</span>
                                <span class="ml-2 text-xs text-slate-500">{{ $asset->asset_type }}</span>
                            </span>
                            <span class="flex flex-wrap gap-2 text-[10px] font-semibold uppercase tracking-wide">
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-800 border border-emerald-100">{{ $assetStatuses[$asset->current_status] ?? $asset->current_status }}</span>
                                @if ($asset->condition)
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">{{ $asset->condition }}</span>
                                @endif
                            </span>
                        </summary>
                        @if ($canManageAssets)
                            <form class="mt-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50/80 p-4 sm:grid-cols-2 xl:grid-cols-4" method="POST" action="{{ route('operations.assets.update', $asset) }}">
                                @csrf
                                @method('PATCH')
                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Asset ID
                                    <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" name="asset_number" value="{{ $asset->asset_number }}" maxlength="255" required>
                                </label>
                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Asset type
                                    <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" name="asset_type" value="{{ $asset->asset_type }}" maxlength="255" required>
                                </label>
                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500 sm:col-span-2">Name / description
                                    <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" name="name" value="{{ $asset->name }}" maxlength="255" required>
                                </label>
                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Serial number
                                    <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" name="serial_number" value="{{ $asset->serial_number }}" maxlength="255">
                                </label>
                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Status
                                    <select class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" name="current_status" required>
                                        @foreach ($assetStatuses as $value => $label)
                                            <option value="{{ $value }}" @selected($asset->current_status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Condition
                                    <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" name="condition" value="{{ $asset->condition }}" maxlength="255">
                                </label>
                                <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">Installation date
                                    <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium normal-case tracking-normal text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" type="date" name="installed_on" value="{{ $asset->installed_on }}">
                                </label>
                                <button class="inline-flex h-10 items-center justify-center self-end rounded-lg bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 sm:col-span-2 xl:col-span-4 shadow-sm" type="submit">Save asset changes</button>
                            </form>
                        @endif
                        <div class="mt-4">
                            <h3 class="text-xs font-semibold text-slate-700">Maintenance history</h3>
                            @if ($asset->workOrders->isEmpty())
                                <p class="mt-1 text-xs text-slate-500">No work orders linked to this asset.</p>
                            @else
                                <ul class="mt-2 grid gap-1">
                                    @foreach ($asset->workOrders->sortByDesc('completion_at') as $assetWorkOrder)
                                        <li class="text-xs text-slate-600">
                                            <span class="font-mono font-medium">{{ $assetWorkOrder->work_order_number }}</span> ·
                                            {{ $assetWorkOrder->category }} ·
                                            {{ $statuses[$assetWorkOrder->status] ?? $assetWorkOrder->status }}
                                            @if ($assetWorkOrder->completion_at)
                                                · {{ $assetWorkOrder->completion_at->format('M j, Y') }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
            @if ($assets->hasPages())
                <div class="border-t border-slate-100 px-4 py-4 sm:px-6">{{ $assets->links() }}</div>
            @endif
        @endif
    </section>
</div>
@endsection
