<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'layout' => match ($user->role) {
                User::ROLE_ADMIN => 'layouts.admin',
                User::ROLE_SUPERVISOR => 'layouts.supervisor',
                default => 'layouts.field',
            },
            'roleLabel' => match ($user->role) {
                User::ROLE_ADMIN => 'Administrator',
                User::ROLE_OPERATIONS => 'Operations / Engineering',
                User::ROLE_SUPERVISOR => 'Supervisor / Dispatcher',
                default => 'Field personnel',
            },
            'dashboardRoute' => match ($user->role) {
                User::ROLE_ADMIN => 'admin.dashboard',
                User::ROLE_SUPERVISOR => 'supervisor.dashboard',
                default => 'field.dashboard',
            },
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ]);

        $emailChanged = $user->email !== $validated['email'];

        $user->fill($validated);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'profile_updated',
            'record_type' => User::class,
            'record_id' => $user->id,
            'details' => 'Updated their profile details.',
        ]);

        return redirect()->route('profile.edit')->with('status', 'Profile settings saved.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $user->update(['password' => $validated['password']]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'password_changed',
            'record_type' => User::class,
            'record_id' => $user->id,
            'details' => 'Changed their account password.',
        ]);

        return redirect()->route('profile.edit')->with('status', 'Password changed successfully.');
    }
}
