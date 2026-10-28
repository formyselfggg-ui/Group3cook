<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Request access · DASURECO Operations</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1fr_1fr]">
        <section class="relative hidden overflow-hidden bg-gradient-to-br from-slate-950 via-[#09221b] to-slate-900 px-12 py-10 text-white border-r border-emerald-500/10 lg:flex lg:flex-col lg:justify-between xl:px-20">
            <div class="absolute -right-40 -bottom-44 size-[34rem] rounded-full border border-emerald-500/10 shadow-[0_0_0_52px_rgba(16,185,129,.02),0_0_0_108px_rgba(16,185,129,.015)]"></div>
            <a href="{{ route('login') }}" class="relative flex items-center gap-3 text-sm font-bold tracking-wide">
                <span class="grid size-11 place-items-center rounded-xl border border-emerald-400/30 bg-emerald-500/20 text-emerald-300">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M13.4 2.8 5.7 13h5l-.5 8.2L18.4 11h-5.1l.1-8.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                </span>
                DASURECO <span class="font-normal text-emerald-200/80">Field operations</span>
            </a>
            <div class="relative max-w-xl">
                <p class="text-xs font-bold uppercase tracking-[.2em] text-emerald-400">Request team access</p>
                <h1 class="mt-5 text-5xl font-semibold leading-[1.08] tracking-[-.05em]">One team. <span class="text-emerald-400">Every operation.</span></h1>
                <p class="mt-5 max-w-md text-sm leading-7 text-slate-300">Submit your details for administrator review. An account is only created after your request is approved.</p>
            </div>
            <p class="relative text-[10px] uppercase tracking-[.14em] text-emerald-300/60">Operations management · DAS - Davao del Sur</p>
        </section>

        <section class="flex items-center justify-center px-5 py-12 sm:px-10">
            <div class="w-full max-w-md">
                <a href="{{ route('login') }}" class="mb-10 flex items-center gap-3 text-sm font-bold tracking-wide text-emerald-800 lg:hidden">
                    <span class="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M13.4 2.8 5.7 13h5l-.5 8.2L18.4 11h-5.1l.1-8.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                    </span>
                    DASURECO <span class="font-normal text-slate-500">Field operations</span>
                </a>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-emerald-700">Administrator approval required</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-[-.04em] text-slate-900">Request an account</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Choose the workspace you need. An administrator will review your request.</p>

                @if ($errors->any())
                    <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm" role="alert">
                        <p class="font-semibold text-rose-950">Please check the form:</p>
                        <ul class="mt-1 list-inside list-disc text-rose-800">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="mt-7 grid gap-4" method="POST" action="{{ route('register.store') }}">
                    @csrf
                    <div class="grid gap-2">
                        <label class="text-sm font-semibold text-slate-700" for="name">Full name</label>
                        <input class="h-11 rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 shadow-sm" id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus>
                        @error('name')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-sm font-semibold text-slate-700" for="email">Work email</label>
                        <input class="h-11 rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 shadow-sm" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" placeholder="you@example.com" required>
                        @error('email')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-sm font-semibold text-slate-700" for="role">Requested role</label>
                        <select class="h-11 rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 shadow-sm" id="role" name="role" required>
                            <option value="">Select a role</option>
                            <option value="field_personnel" @selected(old('role') === 'field_personnel')>Field personnel</option>
                            <option value="supervisor" @selected(old('role') === 'supervisor')>Supervisor / Dispatcher</option>
                        </select>
                        @error('role')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-sm font-semibold text-slate-700" for="password">Password</label>
                        <input class="h-11 rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 shadow-sm" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                        @error('password')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-sm font-semibold text-slate-700" for="password_confirmation">Confirm password</label>
                        <input class="h-11 rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 shadow-sm" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                    </div>
                    <button class="mt-2 inline-flex h-12 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white shadow-md shadow-emerald-900/15 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" type="submit">Send registration request</button>
                </form>
                <p class="mt-6 text-center text-xs leading-5 text-slate-500">Already have an account?
                    <a class="font-semibold text-emerald-700 underline decoration-emerald-300 underline-offset-4 hover:text-emerald-800" href="{{ route('login') }}">Sign in</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
