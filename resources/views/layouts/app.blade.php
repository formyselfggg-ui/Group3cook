<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'DASURECO Operations')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-20 border-b border-emerald-500/20 bg-gradient-to-r from-slate-950 via-[#0a261e] to-slate-950 text-white shadow-[0_4px_20px_rgba(15,23,42,0.15)] backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <a class="flex min-w-0 items-center gap-3 font-semibold tracking-tight text-white" href="{{ $__env->yieldContent('homeRoute', route('field.dashboard')) }}">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-emerald-500/20 text-emerald-300 ring-1 ring-emerald-400/30">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M13.4 2.8 5.7 13h5l-.5 8.2L18.4 11h-5.1l.1-8.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="truncate">DASURECO <span class="font-normal text-emerald-200/80">/ {{ $__env->yieldContent('workspace', 'Field operations') }}</span></span>
                </a>
                <div class="flex shrink-0 items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-emerald-300/80">@yield('roleLabel', 'Field personnel')</p>
                    </div>
                    @yield('avatar')
                    @include('partials.notification-bell')
                    <a class="rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:border-emerald-400/40 hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-400" href="{{ route('profile.edit') }}">Profile settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:border-emerald-400/40 hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-400" type="submit">Sign out</button>
                    </form>
                </div>
            </div>
        </header>
        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-7 sm:px-6 sm:py-9 lg:px-8">
            @include('partials.flash')
            @yield('content')
        </main>
        <footer class="mx-auto max-w-7xl px-4 pb-7 text-center text-xs text-slate-500 sm:px-6 lg:px-8">
            @yield('footer', 'DASURECO operations · Field operations workspace')
        </footer>
    </div>
</body>
</html>
