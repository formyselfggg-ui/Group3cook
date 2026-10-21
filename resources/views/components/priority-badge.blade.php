@props(['priority', 'label' => null])

@php
    $styles = match ($priority) {
        'urgent' => ['bg-[#fff0ec] text-[#a0442f] border-[#f2d5cd]', 'alert'],
        'high' => ['bg-[#fff7e8] text-[#8a6220] border-[#efdfbe]', 'up'],
        'normal' => ['bg-[#f1f4f0] text-[#455449] border-[#dbe3d8]', 'dash'],
        default => ['bg-[#f4f7f3] text-[#55655a] border-[#e3eae0]', 'down'],
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
