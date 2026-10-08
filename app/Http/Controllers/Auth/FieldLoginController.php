<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FieldLoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route($this->dashboardRoute(Auth::user()));
        }

        return view('auth.field-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These credentials do not match an active account.'])
                ->onlyInput('email');
        }

        if (! in_array(Auth::user()->role, [
            User::ROLE_ADMIN,
            User::ROLE_OPERATIONS,
            User::ROLE_SUPERVISOR,
            User::ROLE_FIELD_PERSONNEL,
        ], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'A workspace for this account role is not available yet. Please contact your administrator.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route($this->dashboardRoute(Auth::user()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function dashboardRoute(User $user): string
    {
        return match ($user->role) {
            User::ROLE_ADMIN => 'admin.dashboard',
            User::ROLE_OPERATIONS => 'operations.dashboard',
            User::ROLE_SUPERVISOR => 'supervisor.dashboard',
            User::ROLE_FIELD_PERSONNEL => 'field.dashboard',
            default => 'login',
        };
    }
}
