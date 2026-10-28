@props(['status', 'label' => null])

@php
    $styles = match ($status) {
        'assigned' => ['bg-sky-50 text-sky-700 border-sky-200', 'clock'],
        'in_progress' => ['bg-blue-50 text-blue-700 border-blue-200', 'play'],
        'for_review' => ['bg-purple-50 text-purple-700 border-purple-200', 'inbox'],
        'completed' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'check'],
        default => ['bg-slate-100 text-slate-700 border-slate-200', 'clock'],
    };

    $icons = [
        'clock' => '<circle cx="10" cy="10" r="7"/><path d="M10 6.5V10l2.5 1.5"/>',
        'play' => '<path d="m7.5 4.5 8 5.5-8 5.5v-11Z" fill="currentColor" stroke="none"/>',
        'inbox' => '<path d="M3.5 11.5h3.2l1 2.2h4.6l1-2.2h3.2"/><path d="M5.2 4.5h9.6l1.7 7v3.5a1 1 0 0 1-1 1h-11a1 1 0 0 1-1-1v-3.5l1.7-7Z"/>',
        'check' => '<path d="m5 10.5 3.2 3.2L15 7"/>',
    ];

    $defaultLabels = [
        'pending' => 'Pending',
        'assigned' => 'Assigned',
        'in_progress' => 'In progress',
        'for_review' => 'For review',
        'completed' => 'Completed',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold leading-4 '.$styles[0]]) }}>
    <svg class="size-3.5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$styles[1]] !!}</svg>
    {{ $label ?? ($defaultLabels[$status] ?? str($status)->headline()) }}
</span>
