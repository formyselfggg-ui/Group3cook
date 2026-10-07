<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Request access · DASURECO Operations</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f2f6f1] font-sans text-[#1c2b22] antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1fr_1fr]">
        <section class="relative hidden overflow-hidden bg-[#102b23] px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between xl:px-20">
            <div class="absolute -right-40 -bottom-44 size-[34rem] rounded-full border border-white/10 shadow-[0_0_0_52px_rgba(255,255,255,.025),0_0_0_108px_rgba(255,255,255,.02)]"></div>
            <a href="{{ route('login') }}" class="relative flex items-center gap-3 text-sm font-bold tracking-wide">
                <span class="grid size-11 place-items-center rounded-xl border border-white/20 bg-white/10 text-[#b9e3b9]">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M13.4 2.8 5.7 13h5l-.5 8.2L18.4 11h-5.1l.1-8.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                </span>
                DASURECO <span class="font-normal text-[#a9c4b4]">Field operations</span>
            </a>
            <div class="relative max-w-xl">
                <p class="text-xs font-bold uppercase tracking-[.2em] text-[#b9ddb7]">Request team access</p>
                <h1 class="mt-5 text-5xl font-semibold leading-[1.08] tracking-[-.05em]">One team. <span class="text-[#a8d79e]">Every operation.</span></h1>
                <p class="mt-5 max-w-md text-sm leading-7 text-[#b3c7bb]">Submit your details for administrator review. An account is only created after your request is approved.</p>
            </div>
            <p class="relative text-[10px] uppercase tracking-[.14em] text-[#89a799]">Operations management · DAS - Davao del Sur</p>
        </section>

        <section class="flex items-center justify-center px-5 py-12 sm:px-10">
            <div class="w-full max-w-md">
                <a href="{{ route('login') }}" class="mb-10 flex items-center gap-3 text-sm font-bold tracking-wide text-[#1e5739] lg:hidden">
                    <span class="grid size-10 place-items-center rounded-xl bg-[#e2f0e1] text-[#34794e]">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M13.4 2.8 5.7 13h5l-.5 8.2L18.4 11h-5.1l.1-8.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                    </span>
                    DASURECO <span class="font-normal text-[#819087]">Field operations</span>
                </a>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-[#66826d]">Administrator approval required</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-[-.04em] text-[#17251d]">Request an account</h2>
                <p class="mt-2 text-sm leading-6 text-[#78847b]">Choose the workspace you need. An administrator will review your request.</p>

                @if ($errors->any())
                    <div class="mt-6 rounded-xl border border-[#efcfca] bg-[#fff4f1] px-4 py-3 text-sm text-[#9a3c32]" role="alert">
                        <p class="font-semibold">Please check the form:</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="mt-7 grid gap-4" method="POST" action="{{ route('register.store') }}">
                    @csrf
                    <div class="grid gap-2">
                        <label class="text-sm font-semibold text-[#344239]" for="name">Full name</label>
                        <input class="h-11 rounded-xl border border-[#dce4dc] bg-white px-4 text-sm outline-none transition placeholder:text-[#a0aaa1] focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus>
                        @error('name')<span class="text-xs text-[#a13e31]">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-sm font-semibold text-[#344239]" for="email">Work email</label>
                        <input class="h-11 rounded-xl border border-[#dce4dc] bg-white px-4 text-sm outline-none transition placeholder:text-[#a0aaa1] focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" placeholder="you@example.com" required>
                        @error('email')<span class="text-xs text-[#a13e31]">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-sm font-semibold text-[#344239]" for="role">Requested role</label>
                        <select class="h-11 rounded-xl border border-[#dce4dc] bg-white px-4 text-sm outline-none transition focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="role" name="role" required>
                            <option value="">Select a role</option>
                            <option value="field_personnel" @selected(old('role') === 'field_personnel')>Field personnel</option>
                            <option value="supervisor" @selected(old('role') === 'supervisor')>Supervisor / Dispatcher</option>
                        </select>
                        @error('role')<span class="text-xs text-[#a13e31]">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-sm font-semibold text-[#344239]" for="password">Password</label>
                        <input class="h-11 rounded-xl border border-[#dce4dc] bg-white px-4 text-sm outline-none transition focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                        @error('password')<span class="text-xs text-[#a13e31]">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid gap-2">
                        <label class="text-sm font-semibold text-[#344239]" for="password_confirmation">Confirm password</label>
                        <input class="h-11 rounded-xl border border-[#dce4dc] bg-white px-4 text-sm outline-none transition focus:border-[#51906a] focus:ring-4 focus:ring-[#3e8452]/10" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                    </div>
                    <button class="mt-2 inline-flex h-12 items-center justify-center rounded-xl bg-[#176442] px-5 text-sm font-bold text-white shadow-lg shadow-[#176442]/15 transition hover:bg-[#105437] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#32714b]" type="submit">Send registration request</button>
                </form>
                <p class="mt-6 text-center text-xs leading-5 text-[#879188]">Already have an account?
                    <a class="font-semibold text-[#28704a] underline decoration-[#b8d1bb] underline-offset-4 hover:text-[#176442]" href="{{ route('login') }}">Sign in</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
