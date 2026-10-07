<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'profile' => $request->user(),
            'roleLabel' => User::ROLES[$request->user()->role] ?? 'Team Member',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        DB::transaction(function () use ($user, $validated): void {
            $user->fill($validated);
            $changedFields = array_keys($user->getDirty());

            if ($changedFields === []) {
                return;
            }

            if (in_array('email', $changedFields, true)) {
                $user->email_verified_at = null;
            }

            $user->save();

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'user_profile_updated',
                'record_type' => User::class,
                'record_id' => $user->id,
                'details' => 'Updated profile fields: '.implode(', ', $changedFields).'.',
                'created_at' => now(),
            ]);
        });

        return redirect()->route('profile.edit')->with('status', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $user = $request->user();
            $user->password = $validated['password'];
            $user->save();

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'user_password_changed',
                'record_type' => User::class,
                'record_id' => $user->id,
                'details' => 'Changed account password.',
                'created_at' => now(),
            ]);
        });

        return redirect()->route('profile.edit')->with('status', 'Your password has been changed.');
    }
}
