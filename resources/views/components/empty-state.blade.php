@props(['title', 'description' => null, 'icon' => 'inbox'])

@php
    $icons = [
        'inbox' => '<path d="M3.5 11.5h3.2l1 2.2h4.6l1-2.2h3.2"/><path d="M5.2 4.5h9.6l1.7 7v3.5a1 1 0 0 1-1 1h-11a1 1 0 0 1-1-1v-3.5l1.7-7Z"/>',
        'clipboard' => '<path d="M8 5.5h8M8 9.5h8M8 13.5h5"/><path d="M6 3.5h8a1 1 0 0 1 1 1v15H5v-15a1 1 0 0 1 1-1Z"/>',
        'cube' => '<path d="M10 2.5 3.5 6v8l6.5 3.5 6.5-3.5V6L10 2.5Z"/><path d="m3.8 6.2 6.2 3.5 6.2-3.5M10 9.7v7.4"/>',
        'chart' => '<path d="M4 16.5h12"/><path d="M6.5 16.5V9.5m3.5 7V5.5m3.5 11v-4"/>',
        'wrench' => '<path d="M13.4 4.6a4 4 0 0 1-5 5L4 14a1.8 1.8 0 0 0 2.5 2.5l4.4-4.4a4 4 0 0 0 5-5l-2.4 2.4-2-.5-.5-2 2.4-2.4Z"/>',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-slate-300 bg-slate-50/60 px-6 py-12 text-center']) }}>
    <span class="grid size-12 place-items-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200">
        <svg class="size-6" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! ($icons[$icon] ?? $icons['inbox']) !!}</svg>
    </span>
    <p class="text-sm font-semibold text-slate-800">{{ $title }}</p>
    @if ($description)
        <p class="max-w-sm text-xs leading-5 text-slate-500">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
