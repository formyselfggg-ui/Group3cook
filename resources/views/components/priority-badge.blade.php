@props(['priority', 'label' => null])

@php
    $styles = match ($priority) {
        'urgent' => ['bg-rose-50 text-rose-700 border-rose-200', 'alert'],
        'high' => ['bg-amber-50 text-amber-700 border-amber-200', 'up'],
        'normal' => ['bg-slate-100 text-slate-700 border-slate-200', 'dash'],
        default => ['bg-slate-50 text-slate-600 border-slate-200', 'down'],
    };

    $icons = [
        'alert' => '<path d="M10 3.5 17 16.5H3L10 3.5Z"/><path d="M10 8.5v3"/><path d="M10 13.8h.01"/>',
        'up' => '<path d="M10 16V5.5"/><path d="M10 5.5 5.5 10"/><path d="M10 5.5 14.5 10"/>',
        'down' => '<path d="M10 4v10.5"/><path d="M10 14.5 5.5 10"/><path d="M10 14.5 14.5 10"/>',
        'dash' => '<path d="M5 10h10"/>',
    ];

    $defaultLabels = [
        'urgent' => 'Urgent',
        'high' => 'High',
        'normal' => 'Normal',
        'low' => 'Low',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold leading-4 '.$styles[0]]) }}>
    <svg class="size-3.5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$styles[1]] !!}</svg>
    {{ $label ?? ($defaultLabels[$priority] ?? str($priority)->headline()) }}
</span>
