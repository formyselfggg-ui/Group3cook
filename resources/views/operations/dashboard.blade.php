@extends('layouts.operations')

@section('title', 'Engineering dashboard · DASURECO')

@section('content')
<div class="flex flex-col gap-7">
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-[#708775]">
                <span class="size-2 rounded-full bg-[#55a56c] shadow-[0_0_0_4px_rgba(85,165,108,.12)]" aria-hidden="true"></span>
                Operations / Engineering
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-[#1a2a20] sm:text-4xl">Engineering dashboard</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#78857b]">Register work requirements, manage electrical assets, and monitor field operations.</p>
        </div>
        <p class="text-xs font-medium text-[#8a958c]">{{ now()->format('l, F j, Y') }}</p>
    </section>

    <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-7" aria-label="Operations summary">
        @foreach ([
            ['Total work orders', $counts['work_orders'], 'text-[#344239]', route('operations.dashboard', ['status' => null])],
            ['Pending', $counts['pending'], 'text-[#69776c]', route('operations.dashboard', ['status' => 'pending'])],
            ['In progress', $counts['in_progress'], 'text-[#386b9d]', route('operations.dashboard', ['status' => 'in_progress'])],
            ['Completed', $counts['completed'], 'text-[#316447]', route('operations.dashboard', ['status' => 'completed'])],
            ['Urgent', $counts['urgent'], 'text-[#a64b39]', route('operations.dashboard', ['priority' => 'urgent'])],
            ['Registered assets', $counts['assets'], 'text-[#344239]', '#assets'],
            ['Under maintenance', $counts['assets_under_maintenance'], 'text-[#956b28]', '#assets'],
        ] as [$label, $count, $tone, $href])
            <a class="rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_4px_18px_rgba(30,54,35,.025)] transition hover:-translate-y-0.5 hover:border-[#cddfce] hover:shadow-[0_10px_24px_rgba(30,54,35,.07)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b] sm:p-5" href="{{ $href }}">
                <span class="block text-xs font-semibold leading-5 text-[#78857b]">{{ $label }}</span>
                <span class="mt-3 block text-2xl font-semibold tracking-tight {{ $tone }}">{{ $count }}</span>
            </a>
        @endforeach
    </section>

    <section class="grid items-start gap-6 xl:grid-cols-2">
        <details class="group overflow-hidden rounded-2xl border border-[#dce8da] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]" @if ($errors->has('title') || $errors->has('category') || $errors->has('priority')) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 marker:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b] sm:px-6">
                <span>
                    <span class="block text-sm font-semibold text-[#24352a]">Register work requirement</span>
                    <span class="mt-1 block text-xs text-[#879188]">New requirements are routed to the supervisor for field crew assignment.</span>
                </span>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-[#176442] text-xl font-medium text-white transition group-open:rotate-45" aria-hidden="true">+</span>
            </summary>
            <form class="grid gap-4 border-t border-[#edf0ec] px-5 py-5 sm:grid-cols-2 sm:px-6" method="POST" action="{{ route('operations.work-orders.store') }}">
                @csrf
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448] sm:col-span-2" for="requirement_title">Work requirement title
                    <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="requirement_title" name="title" value="{{ old('title') }}" maxlength="255" required>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448] sm:col-span-2" for="requirement_description">Description / instructions
                    <textarea class="min-h-20 rounded-lg border border-[#dce4dc] bg-white p-3 text-sm font-normal leading-5 text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="requirement_description" name="description" maxlength="10000">{{ old('description') }}</textarea>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="requirement_category">Work category
                    <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="requirement_category" name="category" value="{{ old('category') }}" placeholder="Maintenance, repair, inspection..." maxlength="255" required>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="requirement_priority">Priority
                    <select class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="requirement_priority" name="priority" required>
                        @foreach ($priorities as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="requirement_asset">Related asset <span class="font-normal text-[#849087]">Optional</span>
                    <select class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="requirement_asset" name="asset_id">
                        <option value="">No asset linked</option>
                        @foreach ($assetOptions as $asset)
                            <option value="{{ $asset->id }}" @selected((string) old('asset_id') === (string) $asset->id)>{{ $asset->asset_number }} · {{ $asset->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="requirement_schedule">Requested schedule <span class="font-normal text-[#849087]">Optional</span>
                    <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="requirement_schedule" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}">
                </label>
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-[#176442] px-5 text-xs font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b] sm:col-span-2" type="submit">Send requirement to supervisor</button>
            </form>
        </details>

        <details id="assets" class="group overflow-hidden rounded-2xl border border-[#dce8da] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]" @if (request()->has('asset_status') || $errors->has('asset_number')) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 marker:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b] sm:px-6">
                <span>
                    <span class="block text-sm font-semibold text-[#24352a]">Register an electrical asset</span>
                    <span class="mt-1 block text-xs text-[#879188]">Track transformers, meters, distribution equipment, cables, and more.</span>
                </span>
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-[#176442] text-xl font-medium text-white transition group-open:rotate-45" aria-hidden="true">+</span>
            </summary>
            <form class="grid gap-4 border-t border-[#edf0ec] px-5 py-5 sm:grid-cols-2 sm:px-6" method="POST" action="{{ route('operations.assets.store') }}">
                @csrf
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="asset_number">Asset ID
                    <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="asset_number" name="asset_number" value="{{ old('asset_number') }}" maxlength="255" required>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="asset_type">Asset type
                    <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="asset_type" name="asset_type" value="{{ old('asset_type') }}" placeholder="Transformer, electric meter..." maxlength="255" required>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448] sm:col-span-2" for="asset_name">Name / description
                    <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="asset_name" name="name" value="{{ old('name') }}" maxlength="255" required>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="asset_serial">Serial number <span class="font-normal text-[#849087]">Optional</span>
                    <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="asset_serial" name="serial_number" value="{{ old('serial_number') }}" maxlength="255">
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="asset_status">Current status
                    <select class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="asset_status" name="current_status" required>
                        @foreach ($assetStatuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('current_status', 'active') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="asset_condition">Condition <span class="font-normal text-[#849087]">Optional</span>
                    <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="asset_condition" name="condition" value="{{ old('condition') }}" placeholder="Good, fair, needs attention..." maxlength="255">
                </label>
                <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="asset_installed_on">Installation / acquisition date <span class="font-normal text-[#849087]">Optional</span>
                    <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="asset_installed_on" type="date" name="installed_on" value="{{ old('installed_on') }}">
                </label>
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-[#176442] px-5 text-xs font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b] sm:col-span-2" type="submit">Register asset</button>
            </form>
        </details>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <article class="rounded-2xl border border-[#e2e9e1] bg-white p-5 shadow-[0_8px_28px_rgba(29,55,37,.035)] sm:p-6">
            <h2 class="text-base font-semibold text-[#24352a]">Work orders by category</h2>
            <p class="mt-1 text-xs text-[#879188]">Registered operational workload.</p>
            @if ($workOrdersByCategory->isEmpty())
                <p class="mt-6 text-sm text-[#879188]">No work order categories yet.</p>
            @else
                <ul class="mt-5 grid gap-3">
                    @foreach ($workOrdersByCategory as $category)
                        <li class="flex items-center justify-between gap-4">
                            <span class="truncate text-sm font-medium text-[#435448]">{{ $category->category }}</span>
                            <span class="shrink-0 rounded-lg bg-[#f1f6ef] px-2.5 py-1.5 text-xs font-bold text-[#356947]">{{ $category->work_orders_count }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>

        <article class="rounded-2xl border border-[#e2e9e1] bg-white p-5 shadow-[0_8px_28px_rgba(29,55,37,.035)] sm:p-6">
            <h2 class="text-base font-semibold text-[#24352a]">Asset condition and status</h2>
            <p class="mt-1 text-xs text-[#879188]">Current operational status and recorded condition for registered assets.</p>
            @if ($assets->isEmpty())
                <p class="mt-6 text-sm text-[#879188]">No assets have been registered.</p>
            @else
                <ul class="mt-5 grid gap-3">
                    @foreach ($assetStatuses as $status => $label)
                        <li class="flex items-center justify-between gap-4">
                            <span class="text-sm font-medium text-[#435448]">{{ $label }}</span>
                            <span class="shrink-0 rounded-lg bg-[#f1f6ef] px-2.5 py-1.5 text-xs font-bold text-[#356947]">{{ $assetsByStatus->get($status)?->assets_count ?? 0 }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-5 border-t border-[#edf0ec] pt-4">
                    <h3 class="text-xs font-semibold text-[#435448]">Condition</h3>
                    <ul class="mt-3 grid gap-3">
                        @forelse ($assetsByCondition as $condition)
                            <li class="flex items-center justify-between gap-4">
                                <span class="text-sm font-medium text-[#435448]">{{ $condition->condition ?: 'Not recorded' }}</span>
                                <span class="shrink-0 rounded-lg bg-[#f1f6ef] px-2.5 py-1.5 text-xs font-bold text-[#356947]">{{ $condition->assets_count }}</span>
                            </li>
                        @empty
                            <li class="text-xs text-[#879188]">No asset conditions recorded.</li>
                        @endforelse
                    </ul>
                </div>
            @endif
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <article id="work-orders" class="min-w-0 overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
            <div class="flex flex-col gap-4 border-b border-[#edf0ec] px-5 py-5 sm:px-6">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                    <div>
                        <h2 class="text-base font-semibold text-[#24352a]">Work orders and field records</h2>
                        <p class="mt-1 text-xs leading-5 text-[#879188]">Review progress, linked assets, crews, and materials used.</p>
                    </div>
                    <a class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg border border-[#dce5dc] bg-white px-3 text-xs font-semibold text-[#356947] transition hover:bg-[#f5faf4] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" href="{{ route('operations.reports.export', request()->query()) }}">Export filtered CSV</a>
                </div>
                <form class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" method="GET" action="{{ route('operations.dashboard') }}">
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Status
                        <select class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" name="status">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Priority
                        <select class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" name="priority">
                            <option value="">All priorities</option>
                            @foreach ($priorities as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['priority'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Category
                        <select class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" name="category">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Asset
                        <select class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" name="asset_id">
                            <option value="">All assets</option>
                            @foreach ($assetOptions as $asset)
                                <option value="{{ $asset->id }}" @selected((string) ($filters['asset_id'] ?? '') === (string) $asset->id)>{{ $asset->asset_number }} · {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Assigned personnel
                        <select class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" name="assigned_personnel_id">
                            <option value="">All personnel</option>
                            @foreach ($fieldPersonnel as $person)
                                <option value="{{ $person->id }}" @selected((string) ($filters['assigned_personnel_id'] ?? '') === (string) $person->id)>{{ $person->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Created from
                        <input class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                    </label>
                    <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Created to
                        <input class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                    </label>
                    <div class="flex items-end gap-2">
                        <button class="inline-flex h-10 flex-1 items-center justify-center rounded-lg bg-[#176442] px-4 text-xs font-bold text-white transition hover:bg-[#105437]" type="submit">Apply filters</button>
                        <a class="inline-flex h-10 items-center justify-center rounded-lg border border-[#dce5dc] px-3 text-xs font-semibold text-[#4b6553]" href="{{ route('operations.dashboard') }}">Clear</a>
                    </div>
                </form>
            </div>

            @if ($workOrders->isEmpty())
                <div class="px-6 py-14 text-center">
                    <h3 class="text-sm font-semibold text-[#344239]">No work orders match these filters</h3>
                    <p class="mt-1 text-xs leading-5 text-[#879188]">Change or clear filters to see more operational records.</p>
                </div>
            @else
                <div class="divide-y divide-[#edf0ec]">
                    @foreach ($workOrders as $workOrder)
                        <article class="px-4 py-5 sm:px-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-[11px] font-semibold tracking-wide text-[#77857b]">{{ $workOrder->work_order_number }}</span>
                                <span class="rounded-full bg-[#f1f4f0] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-[#69776c]">{{ $workOrder->priority }}</span>
                                <span class="rounded-full bg-[#edf4fb] px-2.5 py-1 text-[10px] font-semibold text-[#386b9d]">{{ $statuses[$workOrder->status] ?? $workOrder->status }}</span>
                            </div>
                            <h3 class="mt-2 text-sm font-semibold text-[#26372c]">{{ $workOrder->title }}</h3>
                            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2 text-xs text-[#68766d]">
                                <span><strong class="font-semibold text-[#435448]">Category:</strong> {{ $workOrder->category }}</span>
                                <span><strong class="font-semibold text-[#435448]">Asset:</strong> {{ $workOrder->asset ? $workOrder->asset->asset_number.' · '.$workOrder->asset->name : '—' }}</span>
                                <span><strong class="font-semibold text-[#435448]">Assigned:</strong> {{ $workOrder->assignedPersonnel?->name ?? 'Awaiting assignment' }}</span>
                                <span><strong class="font-semibold text-[#435448]">Created:</strong> {{ $workOrder->created_at->format('M j, Y') }}</span>
                                @if ($workOrder->scheduled_at)
                                    <span><strong class="font-semibold text-[#435448]">Scheduled:</strong> {{ $workOrder->scheduled_at->format('M j, Y · g:i A') }}</span>
                                @endif
                            </div>
                            @if ($workOrder->description)
                                <p class="mt-2 whitespace-pre-line text-xs leading-5 text-[#78857b]">{{ $workOrder->description }}</p>
                            @endif
                            @if ($workOrder->work_performed || $workOrder->field_notes || $workOrder->materialUsages->isNotEmpty())
                                <div class="mt-3 rounded-xl border border-[#edf0ec] bg-[#fafcf9] p-3">
                                    @if ($workOrder->work_performed)
                                        <p class="whitespace-pre-line text-xs leading-5 text-[#435448]"><strong>Work performed:</strong> {{ $workOrder->work_performed }}</p>
                                    @endif
                                    @if ($workOrder->field_notes)
                                        <p class="mt-2 whitespace-pre-line text-xs leading-5 text-[#78857b]"><strong class="text-[#536258]">Field notes:</strong> {{ $workOrder->field_notes }}</p>
                                    @endif
                                    @if ($workOrder->materialUsages->isNotEmpty())
                                        <p class="mt-2 text-xs text-[#536258]"><strong>Materials used:</strong>
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
                    <div class="border-t border-[#edf0ec] px-4 py-4 sm:px-6">{{ $workOrders->links() }}</div>
                @endif
            @endif
        </article>

        <aside class="flex flex-col gap-6">
            <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
                <div class="border-b border-[#edf0ec] px-5 py-5">
                    <h2 class="text-base font-semibold text-[#24352a]">Material usage</h2>
                    <p class="mt-1 text-xs leading-5 text-[#879188]">Top materials recorded against field work orders.</p>
                </div>
                @if ($materialUsage->isEmpty())
                    <p class="px-5 py-8 text-center text-xs leading-5 text-[#879188]">No material usage has been recorded.</p>
                @else
                    <ul class="divide-y divide-[#edf0ec]">
                        @foreach ($materialUsage as $usage)
                            <li class="flex items-center justify-between gap-3 px-5 py-4">
                                <span class="min-w-0 truncate text-sm font-semibold text-[#344239]">{{ $usage->material->name }}</span>
                                <span class="shrink-0 rounded-lg bg-[#f1f6ef] px-2.5 py-1.5 text-xs font-bold text-[#356947]">{{ $usage->quantity_used }} {{ $usage->material->unit }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
                <div class="border-b border-[#edf0ec] px-5 py-5">
                    <h2 class="text-base font-semibold text-[#24352a]">Recent maintenance activities</h2>
                    <p class="mt-1 text-xs leading-5 text-[#879188]">Completed maintenance linked to asset records.</p>
                </div>
                @if ($recentMaintenance->isEmpty())
                    <p class="px-5 py-8 text-center text-xs leading-5 text-[#879188]">No completed maintenance activities yet.</p>
                @else
                    <ul class="divide-y divide-[#edf0ec]">
                        @foreach ($recentMaintenance as $activity)
                            <li class="px-5 py-4">
                                <p class="font-mono text-[10px] font-semibold text-[#77857b]">{{ $activity->work_order_number }} · {{ $activity->completion_at?->format('M j, Y') }}</p>
                                <p class="mt-1 text-sm font-semibold text-[#344239]">{{ $activity->asset->asset_number }} · {{ $activity->asset->name }}</p>
                                <p class="mt-1 text-xs leading-5 text-[#78857b]">{{ $activity->category }} · {{ $activity->assignedPersonnel?->name ?? 'Personnel unavailable' }}</p>
                                @if ($activity->work_performed)
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-[#78857b]">{{ $activity->work_performed }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </aside>
    </section>

    <section class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
        <div class="border-b border-[#edf0ec] px-5 py-5 sm:px-6">
            <h2 class="text-base font-semibold text-[#24352a]">Asset register</h2>
            <p class="mt-1 text-xs leading-5 text-[#879188]">Manage current asset details and link them to work-order maintenance records.</p>
        </div>
        @if ($assets->isEmpty())
            <p class="px-6 py-10 text-center text-sm text-[#879188]">Register an asset above to begin the asset history.</p>
        @else
            <div class="divide-y divide-[#edf0ec]">
                @foreach ($assets as $asset)
                    <details class="group px-5 py-4 sm:px-6">
                        <summary class="flex cursor-pointer list-none flex-col gap-2 marker:hidden sm:flex-row sm:items-center sm:justify-between">
                            <span>
                                <span class="font-mono text-[11px] font-semibold text-[#77857b]">{{ $asset->asset_number }}</span>
                                <span class="ml-2 text-sm font-semibold text-[#344239]">{{ $asset->name }}</span>
                                <span class="ml-2 text-xs text-[#78857b]">{{ $asset->asset_type }}</span>
                            </span>
                            <span class="flex flex-wrap gap-2 text-[10px] font-semibold uppercase tracking-wide">
                                <span class="rounded-full bg-[#f1f6ef] px-2.5 py-1 text-[#356947]">{{ $assetStatuses[$asset->current_status] ?? $asset->current_status }}</span>
                                @if ($asset->condition)
                                    <span class="rounded-full bg-[#f1f4f0] px-2.5 py-1 text-[#69776c]">{{ $asset->condition }}</span>
                                @endif
                            </span>
                        </summary>
                        <form class="mt-4 grid gap-3 rounded-xl border border-[#edf0ec] bg-[#fafcf9] p-4 sm:grid-cols-2 xl:grid-cols-4" method="POST" action="{{ route('operations.assets.update', $asset) }}">
                            @csrf
                            @method('PATCH')
                            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Asset ID
                                <input class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" name="asset_number" value="{{ $asset->asset_number }}" maxlength="255" required>
                            </label>
                            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Asset type
                                <input class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" name="asset_type" value="{{ $asset->asset_type }}" maxlength="255" required>
                            </label>
                            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b] sm:col-span-2">Name / description
                                <input class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" name="name" value="{{ $asset->name }}" maxlength="255" required>
                            </label>
                            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Serial number
                                <input class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" name="serial_number" value="{{ $asset->serial_number }}" maxlength="255">
                            </label>
                            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Status
                                <select class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" name="current_status" required>
                                    @foreach ($assetStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected($asset->current_status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Condition
                                <input class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" name="condition" value="{{ $asset->condition }}" maxlength="255">
                            </label>
                            <label class="grid gap-1 text-[10px] font-bold uppercase tracking-wide text-[#78857b]">Installation date
                                <input class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs font-medium normal-case tracking-normal text-[#344239]" type="date" name="installed_on" value="{{ $asset->installed_on }}">
                            </label>
                            <button class="inline-flex h-10 items-center justify-center self-end rounded-lg bg-[#176442] px-4 text-xs font-bold text-white transition hover:bg-[#105437] sm:col-span-2 xl:col-span-4" type="submit">Save asset changes</button>
                        </form>
                        <div class="mt-4">
                            <h3 class="text-xs font-semibold text-[#435448]">Maintenance history</h3>
                            @if ($asset->workOrders->isEmpty())
                                <p class="mt-1 text-xs text-[#879188]">No work orders linked to this asset.</p>
                            @else
                                <ul class="mt-2 grid gap-1">
                                    @foreach ($asset->workOrders->sortByDesc('completion_at') as $assetWorkOrder)
                                        <li class="text-xs text-[#78857b]">
                                            <span class="font-mono">{{ $assetWorkOrder->work_order_number }}</span> ·
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
                <div class="border-t border-[#edf0ec] px-4 py-4 sm:px-6">{{ $assets->links() }}</div>
            @endif
        @endif
    </section>
</div>
@endsection
