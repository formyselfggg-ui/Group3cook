@extends('layouts.field')

@section('title', 'My field dashboard · DASURECO')

@section('content')
<div class="flex flex-col gap-7">
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-emerald-700">
                <span class="size-2 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,.15)]" aria-hidden="true"></span>
                Field personnel workspace
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-slate-900 sm:text-4xl">Good day, {{ auth()->user()->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Your assigned work, progress updates, and submissions are all here.</p>
        </div>
        <p class="text-xs font-medium text-slate-500">{{ now()->format('l, F j, Y') }}</p>
    </section>

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-5" aria-label="My work order summary">
        @php
            $summaryCards = [
                ['label' => 'New assignments', 'count' => $counts['assigned'], 'status' => 'assigned', 'icon' => 'N', 'tone' => 'text-sky-700 bg-sky-50'],
                ['label' => 'In progress', 'count' => $counts['in_progress'], 'status' => 'in_progress', 'icon' => '↗', 'tone' => 'text-blue-700 bg-blue-50'],
                ['label' => 'For review', 'count' => $counts['for_review'], 'status' => 'for_review', 'icon' => '✓', 'tone' => 'text-purple-700 bg-purple-50'],
                ['label' => 'Completed', 'count' => $counts['completed'], 'status' => 'completed', 'icon' => '✓', 'tone' => 'text-emerald-700 bg-emerald-50'],
                ['label' => 'Urgent tasks', 'count' => $counts['urgent'], 'filter' => ['priority' => 'urgent'], 'icon' => '!', 'tone' => 'text-rose-700 bg-rose-50'],
            ];
        @endphp
        @foreach ($summaryCards as $card)
            <a class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:p-5"
               href="{{ isset($card['status']) ? route('field.dashboard', ['status' => $card['status']]) : route('field.dashboard', $card['filter']) }}">
                <span class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold leading-5 text-slate-600">{{ $card['label'] }}</span>
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $card['tone'] }} text-sm font-bold">{{ $card['icon'] }}</span>
                </span>
                <span class="mt-4 block text-2xl font-semibold tracking-tight text-slate-900">{{ $card['count'] }}</span>
            </a>
        @endforeach
    </section>

    <section id="work-orders" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-5 sm:px-6">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-lg font-semibold tracking-tight text-slate-900">{{ $canViewAll ? 'Work orders' : 'My tasks' }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $canViewAll ? 'You have permission to view every work order in the system.' : 'Only work orders assigned to you are shown here.' }}</p>
                </div>
                <nav class="flex flex-wrap gap-1 rounded-xl bg-slate-100 p-1" aria-label="Filter work orders by status">
                    <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold transition {{ $filter === null && $priorityFilter === null ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}" href="{{ route('field.dashboard') }}" @if ($filter === null && $priorityFilter === null) aria-current="page" @endif>All tasks</a>
                    @foreach ($statuses as $status => $label)
                        <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold transition {{ $filter === $status && $priorityFilter === null ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}" href="{{ route('field.dashboard', ['status' => $status]) }}" @if ($filter === $status && $priorityFilter === null) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                    <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold transition {{ $priorityFilter === 'urgent' ? 'bg-white text-rose-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}" href="{{ route('field.dashboard', ['priority' => 'urgent']) }}" @if ($priorityFilter === 'urgent') aria-current="page" @endif>Urgent</a>
                </nav>
            </div>
        </div>

        @if ($workOrders->isEmpty())
            <div class="px-6 py-6">
                <x-empty-state
                    icon="clipboard"
                    :title="$filter ? 'No tasks in this status' : 'Nothing assigned right now'"
                    description="New work orders assigned to you will appear here. Check back with your supervisor if you expected a task."
                >
                    <x-slot:action>
                        @if ($filter || $priorityFilter)
                            <a class="inline-flex rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="{{ route('field.dashboard') }}">View all tasks</a>
                        @endif
                    </x-slot:action>
                </x-empty-state>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($workOrders as $workOrder)
                    <article class="px-4 py-5 transition hover:bg-slate-50/60 sm:px-6">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-[11px] font-semibold tracking-wide text-slate-500">{{ $workOrder->work_order_number }}</span>
                                    <x-priority-badge :priority="$workOrder->priority" />
                                    <x-status-badge :status="$workOrder->status" :label="$statuses[$workOrder->status] ?? null" />
                                </div>
                                <h3 class="mt-2 text-base font-semibold leading-6 text-slate-900">{{ $workOrder->title }}</h3>
                                @if ($workOrder->description)
                                    <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">{{ $workOrder->description }}</p>
                                @endif
                                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="size-4 text-slate-400" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 4.5h12v11H4v-11Zm3 0v11m6-11v11M4 8h12m-12 4h12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                                        {{ $workOrder->category }}
                                    </span>
                                    @if ($workOrder->asset)
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="size-4 text-slate-400" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 2.5 3.5 6v8l6.5 3.5 6.5-3.5V6L10 2.5Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/><path d="m3.8 6.2 6.2 3.5 6.2-3.5M10 9.7v7.4" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>
                                            {{ $workOrder->asset->asset_number }} · {{ $workOrder->asset->name }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="size-4 text-slate-400" viewBox="0 0 20 20" fill="none" aria-hidden="true"><rect x="3" y="4.5" width="14" height="12" rx="1.5" stroke="currentColor" stroke-width="1.2"/><path d="M6.5 2.5v4m7-4v4m-10.5 2h14" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                                        @if ($workOrder->scheduled_at)
                                            {{ $workOrder->scheduled_at->format('M j, Y · g:i A') }}
                                        @else
                                            Schedule not set
                                        @endif
                                    </span>
                                    @if ($workOrder->photo_paths)
                                        <span>{{ count($workOrder->photo_paths) }} photo{{ count($workOrder->photo_paths) === 1 ? '' : 's' }} attached</span>
                                    @endif
                                </div>
                                @if ($workOrder->materialUsages->isNotEmpty())
                                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-3">
                                        <p class="text-[10px] font-bold uppercase tracking-[.12em] text-slate-500">Materials recorded</p>
                                        <ul class="mt-2 flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-slate-700">
                                            @foreach ($workOrder->materialUsages as $usage)
                                                <li>{{ $usage->quantity }} {{ $usage->material->unit }} {{ $usage->material->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div class="w-full xl:w-72 xl:shrink-0">
                                @if ($workOrder->return_notes && $workOrder->status === 'in_progress')
                                    <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-3 text-xs leading-5 text-amber-900">
                                        <p class="font-bold">Correction requested by your supervisor</p>
                                        <p class="mt-1">{{ $workOrder->return_notes }}</p>
                                    </div>
                                @endif
                                @if ($workOrder->status === 'assigned' && $workOrder->assigned_personnel_id === auth()->id())
                                    <form method="POST" action="{{ route('field.work-orders.start', $workOrder) }}">
                                        @csrf
                                        <button class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">
                                            <svg class="size-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m7 4.5 8 5.5-8 5.5v-11Z" fill="currentColor"/></svg>
                                            Start work
                                        </button>
                                    </form>
                                @elseif ($workOrder->status === 'in_progress')
                                    <details class="group rounded-xl border border-slate-200 bg-slate-50/50">
                                        <summary class="flex h-10 cursor-pointer list-none items-center justify-center gap-2 rounded-xl px-4 text-xs font-bold text-emerald-700 marker:hidden hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 transition">
                                            <svg class="size-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 4v12m-6-6h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                            Update and submit
                                        </summary>
                                        <form class="grid gap-3 border-t border-slate-200 p-3" method="POST" action="{{ route('field.work-orders.submit', $workOrder) }}" enctype="multipart/form-data">
                                            @csrf
                                            <label class="grid gap-1.5 text-[11px] font-semibold text-slate-700" for="work_performed_{{ $workOrder->id }}">Work performed <span class="font-normal text-rose-600">Required</span></label>
                                            <textarea class="min-h-24 rounded-lg border border-slate-300 bg-white p-3 text-xs leading-5 text-slate-800 outline-none placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="work_performed_{{ $workOrder->id }}" name="work_performed" maxlength="10000" placeholder="Describe the work completed..." required>{{ old('work_performed') }}</textarea>
                                            <label class="grid gap-1.5 text-[11px] font-semibold text-slate-700" for="field_notes_{{ $workOrder->id }}">Field notes <span class="font-normal text-slate-400">Optional</span></label>
                                            <textarea class="min-h-16 rounded-lg border border-slate-300 bg-white p-3 text-xs leading-5 text-slate-800 outline-none placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="field_notes_{{ $workOrder->id }}" name="field_notes" maxlength="5000" placeholder="Safety notes or follow-up needed...">{{ old('field_notes') }}</textarea>
                                            <label class="grid gap-1.5 text-[11px] font-semibold text-slate-700" for="photos_{{ $workOrder->id }}">Photos <span class="font-normal text-slate-400">Up to 5 · JPG, PNG, WebP · 5 MB each</span></label>
                                            <input class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-[11px] file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 transition" id="photos_{{ $workOrder->id }}" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                                            <button class="mt-1 inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-xs font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">
                                                Submit for supervisor review
                                            </button>
                                        </form>
                                    </details>
                                    @if (auth()->user()->hasPermissionTo(\App\Models\Permission::RECORD_MATERIALS))
                                        <details class="group mt-2 rounded-xl border border-slate-200 bg-slate-50/50">
                                            <summary class="flex h-10 cursor-pointer list-none items-center justify-center gap-2 rounded-xl px-4 text-xs font-bold text-emerald-700 marker:hidden hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 transition">
                                                <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 3.5v13m-6.5-6.5h13M5.5 5l9 10m0-10-9 10"/></svg>
                                                Record materials used
                                            </summary>
                                            @if ($materials->isEmpty())
                                                <p class="border-t border-slate-200 px-3 py-3 text-xs leading-5 text-slate-500">No materials are currently available in inventory. Contact your supervisor.</p>
                                            @else
                                                <form class="grid gap-3 border-t border-slate-200 p-3" method="POST" action="{{ route('field.work-orders.materials.store', $workOrder) }}">
                                                    @csrf
                                                    <label class="grid gap-1.5 text-[11px] font-semibold text-slate-700" for="material_{{ $workOrder->id }}">Material</label>
                                                    <select class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="material_{{ $workOrder->id }}" name="material_id" required>
                                                        <option value="">Select material</option>
                                                        @foreach ($materials as $material)
                                                            <option value="{{ $material->id }}">{{ $material->name }} · {{ $material->quantity_on_hand }} {{ $material->unit }} available</option>
                                                        @endforeach
                                                    </select>
                                                    <label class="grid gap-1.5 text-[11px] font-semibold text-slate-700" for="quantity_{{ $workOrder->id }}">Quantity used</label>
                                                    <input class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" id="quantity_{{ $workOrder->id }}" name="quantity" type="number" min="0.01" max="100000" step="0.01" placeholder="Enter quantity" required>
                                                    <button class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shadow-sm" type="submit">
                                                        Save material usage
                                                    </button>
                                                </form>
                                            @endif
                                        </details>
                                    @endif

                                @elseif ($workOrder->status === 'for_review')
                                    <p class="rounded-xl border border-purple-200 bg-purple-50 px-3 py-3 text-center text-xs font-medium text-purple-700">
                                        {{ $workOrder->supervisor_approved_at ? 'Approved · Waiting for supervisor to close' : 'Submitted · Waiting for supervisor review' }}
                                    </p>
                                @elseif ($workOrder->status === 'completed')
                                    <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-3 text-center text-xs font-medium text-emerald-700">Work order completed</p>
                                @else
                                    <p class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-center text-xs font-medium text-slate-600">{{ $statuses[$workOrder->status] ?? 'Not available' }}</p>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            @if ($workOrders->hasPages())
                <div class="border-t border-slate-100 px-4 py-4 sm:px-6">
                    {{ $workOrders->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
