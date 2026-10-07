<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RegistrationRequest as AccountRegistrationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegistrationRequestController extends Controller
{
    public function index(): View
    {
        return view('admin.registration-requests', [
            'registrationRequests' => AccountRegistrationRequest::query()
                ->where('status', AccountRegistrationRequest::STATUS_PENDING)
                ->oldest()
                ->paginate(15),
        ]);
    }

    public function approve(Request $request, AccountRegistrationRequest $registrationRequest): RedirectResponse
    {
        $validated = $request->validate([
            'role' => [
                'required',
                Rule::in([User::ROLE_SUPERVISOR, User::ROLE_FIELD_PERSONNEL]),
            ],
        ]);

        DB::transaction(function () use ($request, $registrationRequest, $validated): void {
            $registrationRequest = AccountRegistrationRequest::query()
                ->lockForUpdate()
                ->findOrFail($registrationRequest->id);

            if ($registrationRequest->status !== AccountRegistrationRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'registration_request' => 'This registration request has already been reviewed.',
                ]);
            }

            if (User::query()->where('email', $registrationRequest->email)->exists()) {
                throw ValidationException::withMessages([
                    'registration_request' => 'An account with this email already exists.',
                ]);
            }

            $user = User::create([
                'name' => $registrationRequest->name,
                'email' => $registrationRequest->email,
                'password' => $registrationRequest->password,
                'role' => $validated['role'],
                'is_active' => true,
            ]);

            $registrationRequest->update([
                'status' => AccountRegistrationRequest::STATUS_APPROVED,
                'reviewed_by_user_id' => $request->user()->id,
                'reviewed_at' => now(),
                'user_id' => $user->id,
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'registration_approved',
                'record_type' => AccountRegistrationRequest::class,
                'record_id' => $registrationRequest->id,
                'details' => 'Approved '.$user->email.' as '.$validated['role'].'.',
            ]);
        });

        return redirect()->route('admin.registration-requests')
            ->with('status', 'Registration approved. The new account can now sign in.');
    }

    public function reject(Request $request, AccountRegistrationRequest $registrationRequest): RedirectResponse
    {
        DB::transaction(function () use ($request, $registrationRequest): void {
            $registrationRequest = AccountRegistrationRequest::query()
                ->lockForUpdate()
                ->findOrFail($registrationRequest->id);

            if ($registrationRequest->status !== AccountRegistrationRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'registration_request' => 'This registration request has already been reviewed.',
                ]);
            }

            $registrationRequest->update([
                'status' => AccountRegistrationRequest::STATUS_REJECTED,
                'reviewed_by_user_id' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'registration_rejected',
                'record_type' => AccountRegistrationRequest::class,
                'record_id' => $registrationRequest->id,
                'details' => 'Rejected registration request for '.$registrationRequest->email.'.',
            ]);
        });

        return redirect()->route('admin.registration-requests')
            ->with('status', 'Registration request rejected.');
    }
}
