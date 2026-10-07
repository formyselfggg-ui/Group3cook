@extends('layouts.field')

@section('title', 'My field dashboard · DASURECO')

@section('content')
<div class="flex flex-col gap-7">
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[.16em] text-[#708775]">
                <span class="size-2 rounded-full bg-[#55a56c] shadow-[0_0_0_4px_rgba(85,165,108,.12)]" aria-hidden="true"></span>
                Field personnel workspace
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.045em] text-[#1a2a20] sm:text-4xl">Good day, {{ auth()->user()->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#78857b]">Your assigned work, progress updates, and submissions are all here.</p>
        </div>
        <p class="text-xs font-medium text-[#8a958c]">{{ now()->format('l, F j, Y') }}</p>
    </section>

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-5" aria-label="My work order summary">
        @php
            $summaryCards = [
                ['label' => 'New assignments', 'count' => $counts['assigned'], 'status' => 'assigned', 'icon' => 'N', 'tone' => 'text-[#316447] bg-[#eaf5eb]'],
                ['label' => 'In progress', 'count' => $counts['in_progress'], 'status' => 'in_progress', 'icon' => '↗', 'tone' => 'text-[#386b9d] bg-[#edf4fb]'],
                ['label' => 'For review', 'count' => $counts['for_review'], 'status' => 'for_review', 'icon' => '✓', 'tone' => 'text-[#865f9c] bg-[#f4eff8]'],
                ['label' => 'Completed', 'count' => $counts['completed'], 'status' => 'completed', 'icon' => '✓', 'tone' => 'text-[#55715c] bg-[#f0f5ef]'],
                ['label' => 'Urgent tasks', 'count' => $counts['urgent'], 'filter' => ['priority' => 'urgent'], 'icon' => '!', 'tone' => 'text-[#a64b39] bg-[#fff0ec]'],
            ];
        @endphp
        @foreach ($summaryCards as $card)
            <a class="group rounded-2xl border border-[#e3eae1] bg-white p-4 shadow-[0_4px_18px_rgba(30,54,35,.025)] transition hover:-translate-y-0.5 hover:border-[#cddfce] hover:shadow-[0_10px_24px_rgba(30,54,35,.07)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b] sm:p-5"
               href="{{ isset($card['status']) ? route('field.dashboard', ['status' => $card['status']]) : route('field.dashboard', $card['filter']) }}">
                <span class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold leading-5 text-[#78857b]">{{ $card['label'] }}</span>
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $card['tone'] }} text-sm font-bold">{{ $card['icon'] }}</span>
                </span>
                <span class="mt-4 block text-2xl font-semibold tracking-tight text-[#23352a]">{{ $card['count'] }}</span>
            </a>
        @endforeach
    </section>

    <section id="work-orders" class="overflow-hidden rounded-2xl border border-[#e2e9e1] bg-white shadow-[0_8px_28px_rgba(29,55,37,.035)]">
        <div class="border-b border-[#edf0ec] px-4 py-5 sm:px-6">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-lg font-semibold tracking-tight text-[#24352a]">My tasks</h2>
                    <p class="mt-1 text-xs text-[#879188]">Only work orders assigned to you are shown here.</p>
                </div>
                <nav class="flex gap-1 overflow-x-auto rounded-xl bg-[#f4f7f3] p-1" aria-label="Filter work orders by status">
                    <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold transition {{ $filter === null && $priorityFilter === null ? 'bg-white text-[#285c3b] shadow-sm' : 'text-[#758178] hover:text-[#344239]' }}" href="{{ route('field.dashboard') }}" @if ($filter === null && $priorityFilter === null) aria-current="page" @endif>All tasks</a>
                    @foreach ($statuses as $status => $label)
                        <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold transition {{ $filter === $status && $priorityFilter === null ? 'bg-white text-[#285c3b] shadow-sm' : 'text-[#758178] hover:text-[#344239]' }}" href="{{ route('field.dashboard', ['status' => $status]) }}" @if ($filter === $status && $priorityFilter === null) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                    <a class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold transition {{ $priorityFilter === 'urgent' ? 'bg-white text-[#a64b39] shadow-sm' : 'text-[#758178] hover:text-[#344239]' }}" href="{{ route('field.dashboard', ['priority' => 'urgent']) }}" @if ($priorityFilter === 'urgent') aria-current="page" @endif>Urgent</a>
                </nav>
            </div>
        </div>

        @if ($workOrders->isEmpty())
            <div class="px-6 py-16 text-center">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-[#edf6ec] text-[#438055]">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 5h8m-8 4h8m-8 4h5m-8-9h14a1 1 0 0 1 1 1v14H4V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="m9 17 1.5 1.5L14 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <h3 class="mt-4 text-sm font-semibold text-[#344239]">{{ $filter ? 'No tasks in this status' : 'Nothing assigned right now' }}</h3>
                <p class="mx-auto mt-1 max-w-sm text-xs leading-5 text-[#879188]">New work orders assigned to you will appear here. Check back with your supervisor if you expected a task.</p>
                @if ($filter || $priorityFilter)
                    <a class="mt-4 inline-flex rounded-lg border border-[#dce5dc] px-3 py-2 text-xs font-semibold text-[#41634b] hover:bg-[#f7faf6]" href="{{ route('field.dashboard') }}">View all tasks</a>
                @endif
            </div>
        @else
            <div class="divide-y divide-[#edf0ec]">
                @foreach ($workOrders as $workOrder)
                    <article class="px-4 py-5 transition hover:bg-[#fcfdfb] sm:px-6">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
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
                                    ])>{{ $statuses[$workOrder->status] ?? $workOrder->status }}</span>
                                </div>
                                <h3 class="mt-2 text-base font-semibold leading-6 text-[#26382c]">{{ $workOrder->title }}</h3>
                                @if ($workOrder->description)
                                    <p class="mt-1 max-w-3xl text-sm leading-6 text-[#78857b]">{{ $workOrder->description }}</p>
                                @endif
                                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs text-[#758178]">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="size-4 text-[#98a49a]" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 4.5h12v11H4v-11Zm3 0v11m6-11v11M4 8h12m-12 4h12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                                        {{ $workOrder->category }}
                                    </span>
                                    @if ($workOrder->asset)
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="size-4 text-[#98a49a]" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 2.5 3.5 6v8l6.5 3.5 6.5-3.5V6L10 2.5Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/><path d="m3.8 6.2 6.2 3.5 6.2-3.5M10 9.7v7.4" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>
                                            {{ $workOrder->asset->asset_number }} · {{ $workOrder->asset->name }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="size-4 text-[#98a49a]" viewBox="0 0 20 20" fill="none" aria-hidden="true"><rect x="3" y="4.5" width="14" height="12" rx="1.5" stroke="currentColor" stroke-width="1.2"/><path d="M6.5 2.5v4m7-4v4m-10.5 2h14" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
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
                                    <div class="mt-4 rounded-xl border border-[#edf0ec] bg-[#fafcf9] px-3 py-3">
                                        <p class="text-[10px] font-bold uppercase tracking-[.12em] text-[#78857b]">Materials recorded</p>
                                        <ul class="mt-2 flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-[#536258]">
                                            @foreach ($workOrder->materialUsages as $usage)
                                                <li>{{ $usage->quantity }} {{ $usage->material->unit }} {{ $usage->material->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div class="w-full xl:w-72 xl:shrink-0">
                                @if ($workOrder->return_notes && $workOrder->status === 'in_progress')
                                    <div class="mb-3 rounded-xl border border-[#f0d8a8] bg-[#fff9ed] px-3 py-3 text-xs leading-5 text-[#795b25]">
                                        <p class="font-bold">Correction requested by your supervisor</p>
                                        <p class="mt-1">{{ $workOrder->return_notes }}</p>
                                    </div>
                                @endif
                                @if ($workOrder->status === 'assigned')
                                    <form method="POST" action="{{ route('field.work-orders.start', $workOrder) }}">
                                        @csrf
                                        <button class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-[#176442] px-4 text-xs font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">
                                            <svg class="size-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m7 4.5 8 5.5-8 5.5v-11Z" fill="currentColor"/></svg>
                                            Start work
                                        </button>
                                    </form>
                                @elseif ($workOrder->status === 'in_progress')
                                    <details class="group rounded-xl border border-[#dfe8dd] bg-[#fbfdf9]">
                                        <summary class="flex h-10 cursor-pointer list-none items-center justify-center gap-2 rounded-xl px-4 text-xs font-bold text-[#356947] marker:hidden hover:bg-[#f4faf2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]">
                                            <svg class="size-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 4v12m-6-6h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                            Update and submit
                                        </summary>
                                        <form class="grid gap-3 border-t border-[#e7eee5] p-3" method="POST" action="{{ route('field.work-orders.submit', $workOrder) }}" enctype="multipart/form-data">
                                            @csrf
                                            <label class="grid gap-1.5 text-[11px] font-semibold text-[#435448]" for="work_performed_{{ $workOrder->id }}">Work performed <span class="font-normal text-[#a64b39]">Required</span></label>
                                            <textarea class="min-h-24 rounded-lg border border-[#dce4dc] bg-white p-3 text-xs leading-5 text-[#344239] outline-none placeholder:text-[#a0aaa1] focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="work_performed_{{ $workOrder->id }}" name="work_performed" maxlength="10000" placeholder="Describe the work completed..." required>{{ old('work_performed') }}</textarea>
                                            <label class="grid gap-1.5 text-[11px] font-semibold text-[#435448]" for="field_notes_{{ $workOrder->id }}">Field notes <span class="font-normal text-[#849087]">Optional</span></label>
                                            <textarea class="min-h-16 rounded-lg border border-[#dce4dc] bg-white p-3 text-xs leading-5 text-[#344239] outline-none placeholder:text-[#a0aaa1] focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="field_notes_{{ $workOrder->id }}" name="field_notes" maxlength="5000" placeholder="Safety notes or follow-up needed...">{{ old('field_notes') }}</textarea>
                                            <label class="grid gap-1.5 text-[11px] font-semibold text-[#435448]" for="photos_{{ $workOrder->id }}">Photos <span class="font-normal text-[#849087]">Up to 5 · JPG, PNG, WebP · 5 MB each</span></label>
                                            <input class="block w-full text-xs text-[#68766d] file:mr-3 file:rounded-lg file:border-0 file:bg-[#edf6ec] file:px-3 file:py-2 file:text-[11px] file:font-semibold file:text-[#356947] hover:file:bg-[#e2f0e1]" id="photos_{{ $workOrder->id }}" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                                            <button class="mt-1 inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#176442] px-4 text-xs font-bold text-white transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">
                                                Submit for supervisor review
                                            </button>
                                        </form>
                                    </details>
                                    <details class="group mt-2 rounded-xl border border-[#dfe8dd] bg-[#fbfdf9]">
                                            <summary class="flex h-10 cursor-pointer list-none items-center justify-center gap-2 rounded-xl px-4 text-xs font-bold text-[#356947] marker:hidden hover:bg-[#f4faf2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]">
                                                <svg class="size-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 3.5v13m-6.5-6.5h13M5.5 5l9 10m0-10-9 10" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/></svg>
                                                Record materials used
                                            </summary>
                                            @if ($materials->isEmpty())
                                                <p class="border-t border-[#e7eee5] px-3 py-3 text-xs leading-5 text-[#78857b]">No materials are currently available in inventory. Contact your supervisor.</p>
                                            @else
                                                <form class="grid gap-3 border-t border-[#e7eee5] p-3" method="POST" action="{{ route('field.work-orders.materials.store', $workOrder) }}">
                                                    @csrf
                                                    <label class="grid gap-1.5 text-[11px] font-semibold text-[#435448]" for="material_{{ $workOrder->id }}">Material</label>
                                                    <select class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="material_{{ $workOrder->id }}" name="material_id" required>
                                                        <option value="">Select material</option>
                                                        @foreach ($materials as $material)
                                                            <option value="{{ $material->id }}">{{ $material->name }} · {{ $material->quantity_on_hand }} {{ $material->unit }} available</option>
                                                        @endforeach
                                                    </select>
                                                    <label class="grid gap-1.5 text-[11px] font-semibold text-[#435448]" for="quantity_{{ $workOrder->id }}">Quantity used</label>
                                                    <input class="h-10 rounded-lg border border-[#dce4dc] bg-white px-3 text-xs text-[#344239] outline-none focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="quantity_{{ $workOrder->id }}" name="quantity" type="number" min="0.01" max="100000" step="0.01" placeholder="Enter quantity" required>
                                                    <button class="inline-flex h-10 items-center justify-center rounded-xl border border-[#cbdcca] bg-white px-4 text-xs font-bold text-[#356947] transition hover:bg-[#f1f8ef] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">
                                                        Save material usage
                                                    </button>
                                                </form>
                                            @endif
                                    </details>
                                @elseif ($workOrder->status === 'for_review')
                                    <p class="rounded-xl border border-[#e8dfed] bg-[#faf7fc] px-3 py-3 text-center text-xs font-medium text-[#765b89]">
                                        {{ $workOrder->supervisor_approved_at ? 'Approved · Waiting for supervisor to close' : 'Submitted · Waiting for supervisor review' }}
                                    </p>
                                @elseif ($workOrder->status === 'completed')
                                    <p class="rounded-xl border border-[#e1e9df] bg-[#f6f9f4] px-3 py-3 text-center text-xs font-medium text-[#55715c]">Work order completed</p>
                                @else
                                    <p class="rounded-xl border border-[#e6eae4] bg-[#f7f8f6] px-3 py-3 text-center text-xs font-medium text-[#758178]">{{ $statuses[$workOrder->status] ?? 'Not available' }}</p>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            @if ($workOrders->hasPages())
                <div class="border-t border-[#edf0ec] px-4 py-4 sm:px-6">
                    {{ $workOrders->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
