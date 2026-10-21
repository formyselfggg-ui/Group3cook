@if (session('status'))
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-[#c9e1ce] bg-[#edf8ef] px-4 py-3 text-sm text-[#28613b]" role="status">
        <svg class="mt-0.5 size-5 shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="m5 10 3.2 3.2L15 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.4"/>
        </svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-[#efcfca] bg-[#fff4f1] px-4 py-3 text-sm text-[#9a3c32]" role="alert">
        <p class="font-semibold">Please check the following:</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
