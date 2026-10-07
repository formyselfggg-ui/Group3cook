<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'Supervisor dashboard · DASURECO')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f7f3] font-sans text-[#1c2b22] antialiased">
    <div class="min-h-screen">
        <header class="sticky top-0 z-20 border-b border-[#e4ebe3] bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <a class="flex min-w-0 items-center gap-3 font-semibold tracking-tight text-[#173a2b]" href="{{ route('supervisor.dashboard') }}">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#eaf4e9] text-[#28704a]">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M13.4 2.8 5.7 13h5l-.5 8.2L18.4 11h-5.1l.1-8.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="truncate">DASURECO <span class="font-normal text-[#819087]">/ Field operations</span></span>
                </a>
                <div class="flex shrink-0 items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-[#304136]">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[#849087]">Supervisor / Dispatcher</p>
                    </div>
                    <a class="rounded-lg border border-[#dce5dc] px-3 py-2 text-xs font-semibold text-[#4b6553] transition hover:border-[#b8d1bb] hover:bg-[#f5faf4] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" href="{{ route('profile.edit') }}">Profile settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-[#dce5dc] px-3 py-2 text-xs font-semibold text-[#4b6553] transition hover:border-[#b8d1bb] hover:bg-[#f5faf4] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">Sign out</button>
                    </form>
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-9 lg:px-8">
            @yield('content')
        </main>
        <footer class="mx-auto max-w-7xl px-4 pb-7 text-center text-xs text-[#93a097] sm:px-6 lg:px-8">
            DASURECO operations · Supervisor workspace
        </footer>
    </div>
</body>
</html>
