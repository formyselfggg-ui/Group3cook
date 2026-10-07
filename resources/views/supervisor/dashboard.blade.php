@extends('layouts.supervisor')

@section('title', 'Supervisor dashboard · DASURECO')

@section('content')
<div class="flex flex-col gap-7">
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-[#708775]">
                <span class="size-2 rounded-full bg-[#55a56c] shadow-[0_0_0_4px_rgba(85,165,108,.12)]" aria-hidden="true"></span>
                Supervisor workspace
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-[#1a2a20] sm:text-4xl">Good day, {{ auth()->user()->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#78857b]">Assign field work, monitor progress, and review completed submissions.</p>
        </div>
        <p class="text-xs font-medium text-[#8a958c]">{{ now()->format('l, F j, Y') }}</p>
    </section>

    @if (session('status'))
        <div class="rounded-xl border border-[#cde2cd] bg-[#f1f8ef] px-4 py-3 text-sm font-medium text-[#356947]" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-[#f0d2cc] bg-[#fff5f2] px-4 py-3 text-sm text-[#984c3d]" role="alert">
            <p class="font-semibold">Please check the following:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6" aria-label="Work order summary">
        @php
            $summaryCards = [
                ['label' => 'Pending', 'count' => $counts['pending'], 'status' => 'pending', 'tone' => 'text-[#69776c] bg-[#f1f4f0]'],
                ['label' => 'Assigned', 'count' => $counts['assigned'], 'status' => 'assigned', 'tone' => 'text-[#316447] bg-[#eaf5eb]'],
                ['label' => 'In progress', 'count' => $counts['in_progress'], 'status' => 'in_progress', 'tone' => 'text-[#386b9d] bg-[#edf4fb]'],
                ['label' => 'For review', 'count' => $counts['for_review'], 'status' => 'for_review', 'tone' => 'text-[#865f9c] bg-[#f4eff8]'],
                ['label' => 'Completed', 'count' => $counts['completed'], 'status' => 'completed', 'tone' => 'text-[#55715c] bg-[#f0f5ef]'],
                ['label' => 'Urgent', 'count' => $counts['urgent'], 'status' => null, 'priority' => 'urgent', 'tone' => 'text-[#a64b39] bg-[#fff0ec]'],
            ];
        @endphp
        @foreach ($summaryCards as $card)
            <a class="rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_4px_18px_rgba(30,54,35,.025)] transition hover:-translate-y-0.5 hover:border-[#cddfce] hover:shadow-[0_10px_24px_rgba(30,54,35,.07)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b] sm:p-5"
               href="{{ route('supervisor.dashboard', $card['status'] ? ['status' => $card['status']] : (isset($card['priority']) ? ['priority' => $card['priority']] : [])) }}">
                <span class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold leading-5 text-[#78857b]">{{ $card['label'] }}</span>
                    <span class="grid size-8 place-items-center rounded-lg {{ $card['tone'] }} text-sm font-bold">{{ $card['label'] === 'Urgent' ? '!' : '•' }}</span>
                </span>
                <span class="mt-4 block text-2xl font-semibold tracking-tight text-[#23352a]">{{ $card['count'] }}</span>
            </a>
        @endforeach
    </section>

    <section class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="flex min-w-0 flex-col gap-6">
            <details class="group overflow-hidden rounded-2xl border border-[#dce8da] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]" @if ($errors->any()) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 marker:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b] sm:px-6">
                    <span>
                        <span class="block text-sm font-semibold text-[#24352a]">Create and assign work order</span>
                        <span class="mt-1 block text-xs text-[#879188]">Set the priority, field personnel, related asset, and schedule.</span>
                    </span>
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-[#176442] text-xl font-medium text-white transition group-open:rotate-45" aria-hidden="true">+</span>
                </summary>
                <form class="grid gap-4 border-t border-[#edf0ec] px-5 py-5 sm:grid-cols-2 sm:px-6" method="POST" action="{{ route('supervisor.work-orders.store') }}">
                    @csrf
                    <label class="grid gap-1.5 text-xs font-semibold text-[#435448] sm:col-span-2" for="title">Work order title
                        <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="title" name="title" value="{{ old('title') }}" maxlength="255" required>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-[#435448] sm:col-span-2" for="description">Instructions / description
                        <textarea class="min-h-20 rounded-lg border border-[#dce4dc] bg-white p-3 text-sm font-normal leading-5 text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="description" name="description" maxlength="10000">{{ old('description') }}</textarea>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="category">Work category
                        <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="category" name="category" value="{{ old('category') }}" placeholder="Maintenance, repair, inspection..." maxlength="255" required>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="priority">Priority
                        <select class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="priority" name="priority" required>
                            @foreach ($priorities as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="assigned_personnel_id">Assign to
                        <select class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="assigned_personnel_id" name="assigned_personnel_id" required>
                            <option value="">Select field personnel</option>
                            @foreach ($fieldPersonnel as $person)
                                <option value="{{ $person->id }}" @selected((string) old('assigned_personnel_id') === (string) $person->id)>{{ $person->name }} · {{ $person->active_work_orders_count }} active</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-[#435448]" for="asset_id">Related asset <span class="font-normal text-[#849087]">Optional</span>
                        <select class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="asset_id" name="asset_id">
                            <option value="">No asset linked</option>
                            @foreach ($assets as $asset)
                                <option value="{{ $asset->id }}" @selected((string) old('asset_id') === (string) $asset->id)>{{ $asset->asset_number }} · {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-semibold text-[#435448] sm:col-span-2" for="scheduled_at">Scheduled date and time <span class="font-normal text-[#849087]">Optional</span>
                        <input class="h-11 rounded-lg border border-[#dce4dc] bg-white px-3 text-sm font-normal text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10 sm:max-w-sm" id="scheduled_at" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at') }}">
                    </label>
                    <div class="sm:col-span-2">
                        <button class="inline-flex h-11 items-center justify-center rounded-xl bg-[#176442] px-5 text-sm font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b] disabled:cursor-not-allowed disabled:bg-[#aebbb0]" type="submit" @disabled($fieldPersonnel->isEmpty())>Create work order</button>
                        @if ($fieldPersonnel->isEmpty())
                            <p class="mt-2 text-xs text-[#a64b39]">There are no active field personnel to assign. Activate a field account first.</p>
                        @endif
                    </div>
                </form>
            </details>

            <section id="work-orders" class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
                <div class="border-b border-[#edf0ec] px-4 py-5 sm:px-6">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="text-lg font-semibold tracking-tight text-[#24352a]">All work orders</h2>
                            <p class="mt-1 text-xs text-[#879188]">Monitor assignments and review submitted field work.</p>
                        </div>
                        <nav class="flex gap-1 overflow-x-auto rounded-xl bg-[#f4f7f3] p-1" aria-label="Filter work orders by status">
                            <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold {{ $filter === null && $priorityFilter === null ? 'bg-white text-[#285c3b] shadow-sm' : 'text-[#758178] hover:text-[#344239]' }}" href="{{ route('supervisor.dashboard') }}" @if ($filter === null && $priorityFilter === null) aria-current="page" @endif>All</a>
                            @foreach ($statuses as $status => $label)
                                <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold {{ $filter === $status && $priorityFilter === null ? 'bg-white text-[#285c3b] shadow-sm' : 'text-[#758178] hover:text-[#344239]' }}" href="{{ route('supervisor.dashboard', ['status' => $status]) }}" @if ($filter === $status && $priorityFilter === null) aria-current="page" @endif>{{ $label }}</a>
                            @endforeach
                            <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold {{ $priorityFilter === 'urgent' ? 'bg-white text-[#a64b39] shadow-sm' : 'text-[#758178] hover:text-[#344239]' }}" href="{{ route('supervisor.dashboard', ['priority' => 'urgent']) }}" @if ($priorityFilter === 'urgent') aria-current="page" @endif>Urgent</a>
                        </nav>
                    </div>
                </div>

                @if ($workOrders->isEmpty())
                    <div class="px-6 py-14 text-center">
                        <h3 class="text-sm font-semibold text-[#344239]">{{ $filter ? 'No work orders in this status' : ($priorityFilter ? 'No urgent work orders' : 'No work orders yet') }}</h3>
                        <p class="mt-1 text-xs leading-5 text-[#879188]">Create an assignment above to get field work moving.</p>
                    </div>
                @else
                    <div class="divide-y divide-[#edf0ec]">
                        @foreach ($workOrders as $workOrder)
                            <article class="px-4 py-5 sm:px-6">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-mono text-[11px] font-semibold tracking-wide text-[#77857b]">{{ $workOrder->work_order_number }}</span>
                                            <span @class([
                                                'rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide',
                                                'bg-[#fff0ec] text-[#a64b39]' => $workOrder->priority === 'urgent',
                                                'bg-[#fff7e8] text-[#956b28]' => $workOrder->priority === 'high',
                                                'bg-[#f1f4f0] text-[#69776c]' => in_array($workOrder->priority, ['normal', 'low'], true),
                                            ])>{{ $workOrder->priority }}</span>
                                            <span @class([
                                                'rounded-full px-2.5 py-1 text-[10px] font-semibold',
                                                'bg-[#eaf5eb] text-[#316447]' => $workOrder->status === 'assigned',
                                                'bg-[#edf4fb] text-[#386b9d]' => $workOrder->status === 'in_progress',
                                                'bg-[#f4eff8] text-[#865f9c]' => $workOrder->status === 'for_review',
                                                'bg-[#f0f5ef] text-[#55715c]' => $workOrder->status === 'completed',
                                                'bg-[#f1f4f0] text-[#69776c]' => $workOrder->status === 'pending',
                                            ])>{{ $workOrder->supervisor_approved_at && $workOrder->status === 'for_review' ? 'Approved · ready to close' : ($statuses[$workOrder->status] ?? $workOrder->status) }}</span>
                                        </div>
                                        <h3 class="mt-2 text-base font-semibold text-[#26372c]">{{ $workOrder->title }}</h3>
                                        @if ($workOrder->description)
                                            <p class="mt-1 max-w-3xl whitespace-pre-line text-sm leading-6 text-[#78857b]">{{ $workOrder->description }}</p>
                                        @endif
                                        <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs text-[#68766d]">
                                            <span><strong class="font-semibold text-[#435448]">Category:</strong> {{ $workOrder->category }}</span>
                                            <span><strong class="font-semibold text-[#435448]">Assigned:</strong> {{ $workOrder->assignedPersonnel?->name ?? 'Unassigned' }}</span>
                                            @if ($workOrder->asset)
                                                <span><strong class="font-semibold text-[#435448]">Asset:</strong> {{ $workOrder->asset->asset_number }} · {{ $workOrder->asset->name }}</span>
                                            @endif
                                            <span><strong class="font-semibold text-[#435448]">Schedule:</strong> {{ $workOrder->scheduled_at?->format('M j, Y · g:i A') ?? 'Not set' }}</span>
                                        </div>
                                        @if ($workOrder->submitted_at)
                                            <div class="mt-4 rounded-xl border border-[#edf0ec] bg-[#fafcf9] p-3">
                                                <p class="text-[10px] font-bold uppercase tracking-[.12em] text-[#78857b]">Field submission · {{ $workOrder->submitted_at->format('M j, Y · g:i A') }}</p>
                                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#435448]">{{ $workOrder->work_performed }}</p>
                                                @if ($workOrder->field_notes)
                                                    <p class="mt-2 whitespace-pre-line text-xs leading-5 text-[#78857b]"><strong class="text-[#536258]">Field notes:</strong> {{ $workOrder->field_notes }}</p>
                                                @endif
                                                @if ($workOrder->photo_paths)
                                                    <div class="mt-3 flex flex-wrap gap-2">
                                                        @foreach ($workOrder->photo_paths as $index => $photoPath)
                                                            <a class="rounded-lg border border-[#dce4dc] bg-white px-3 py-2 text-xs font-semibold text-[#356947] hover:bg-[#f1f8ef]" href="{{ route('supervisor.work-orders.photos.show', [$workOrder, $index]) }}" target="_blank" rel="noopener">View field photo {{ $index + 1 }}</a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if ($workOrder->return_notes)
                                                    <p class="mt-2 rounded-lg bg-[#fff9ed] p-2 text-xs leading-5 text-[#795b25]"><strong>Correction requested:</strong> {{ $workOrder->return_notes }}</p>
                                                @endif
                                                @if ($workOrder->materialUsages->isNotEmpty())
                                                    <p class="mt-2 text-xs text-[#536258]"><strong>Materials:</strong>
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
                                                <form method="POST" action="{{ route('supervisor.work-orders.close', $workOrder) }}">
                                                    @csrf
                                                    <button class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-[#176442] px-4 text-xs font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">Close approved work order</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('supervisor.work-orders.review', $workOrder) }}">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="approve">
                                                    <button class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-[#176442] px-4 text-xs font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">Approve submission</button>
                                                </form>
                                                <form class="grid gap-2 rounded-xl border border-[#f0e4ca] bg-[#fffdf8] p-3" method="POST" action="{{ route('supervisor.work-orders.review', $workOrder) }}">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="return">
                                                    <label class="grid gap-1.5 text-[11px] font-semibold text-[#795b25]" for="return_notes_{{ $workOrder->id }}">Return for correction
                                                        <textarea class="min-h-20 rounded-lg border border-[#eadfca] bg-white p-2.5 text-xs font-normal leading-5 text-[#4b4639] outline-none focus:border-[#b39455] focus:ring-4 focus:ring-[#b39455]/10" id="return_notes_{{ $workOrder->id }}" name="return_notes" maxlength="5000" required>{{ old('return_notes') }}</textarea>
                                                    </label>
                                                    <button class="inline-flex h-9 items-center justify-center rounded-lg border border-[#e2c99c] bg-white px-3 text-xs font-bold text-[#795b25] transition hover:bg-[#fff6e5] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#9b7634]" type="submit">Send back to field personnel</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if ($workOrders->hasPages())
                        <div class="border-t border-[#edf0ec] px-4 py-4 sm:px-6">{{ $workOrders->links() }}</div>
                    @endif
                @endif
            </section>
        </div>

        <aside class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
            <div class="border-b border-[#edf0ec] px-5 py-5">
                <h2 class="text-base font-semibold text-[#24352a]">Field crew workload</h2>
                <p class="mt-1 text-xs leading-5 text-[#879188]">Active assignments by field personnel.</p>
            </div>
            @if ($fieldPersonnel->isEmpty())
                <p class="px-5 py-8 text-center text-xs leading-5 text-[#879188]">No active field personnel accounts are available.</p>
            @else
                <ul class="divide-y divide-[#edf0ec]">
                    @foreach ($fieldPersonnel as $person)
                        <li class="flex items-center justify-between gap-3 px-5 py-4">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-[#344239]">{{ $person->name }}</span>
                                <span class="mt-1 block truncate text-xs text-[#879188]">{{ $person->email }}</span>
                            </span>
                            <span class="shrink-0 rounded-lg bg-[#f1f6ef] px-2.5 py-1.5 text-xs font-bold text-[#356947]">{{ $person->active_work_orders_count }} active</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>
    </section>
</div>
@endsection
