@props(['status', 'label' => null])

@php
    $styles = match ($status) {
        'assigned' => ['bg-[#eaf5eb] text-[#2c5f41] border-[#c6e0ca]', 'clock'],
        'in_progress' => ['bg-[#edf4fb] text-[#2f5c86] border-[#c6dbef]', 'play'],
        'for_review' => ['bg-[#f4eff8] text-[#6f4a83] border-[#dccfe6]', 'inbox'],
        'completed' => ['bg-[#eef5ec] text-[#3d6047] border-[#cfe0cb]', 'check'],
        default => ['bg-[#f1f4f0] text-[#455449] border-[#dbe3d8]', 'clock'],
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
