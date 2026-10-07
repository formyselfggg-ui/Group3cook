<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationRequestController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class),
                Rule::unique(RegistrationRequest::class, 'email')
                    ->where('status', RegistrationRequest::STATUS_PENDING),
            ],
            'role' => [
                'required',
                Rule::in([User::ROLE_SUPERVISOR, User::ROLE_FIELD_PERSONNEL]),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated): void {
            $registrationRequest = RegistrationRequest::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'requested_role' => $validated['role'],
                'status' => RegistrationRequest::STATUS_PENDING,
            ]);

            ActivityLog::create([
                'action' => 'registration_requested',
                'record_type' => RegistrationRequest::class,
                'record_id' => $registrationRequest->id,
                'details' => 'Account registration requested for '.$registrationRequest->email.'.',
            ]);
        });

        return redirect()
            ->route('login')
            ->with('status', 'Your registration request was sent. You can sign in after an administrator approves it.');
    }
}
