@if (session('status'))
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm" role="status">
        <svg class="mt-0.5 size-5 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="m5 10 3.2 3.2L15 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.4"/>
        </svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 shadow-sm" role="alert">
        <p class="font-semibold text-rose-950">Please check the following:</p>
        <ul class="mt-1 list-inside list-disc text-rose-800">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
