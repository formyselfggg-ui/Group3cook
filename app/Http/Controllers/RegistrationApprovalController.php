<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationApprovalController extends Controller
{
    public function index(): View
    {
        return view('admin.access-requests', [
            'requests' => RegistrationRequest::query()
                ->where('status', 'pending')
                ->oldest()
                ->paginate(10),
            'roles' => User::ROLES,
        ]);
    }

    public function approve(Request $request, RegistrationRequest $registrationRequest): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ]);

        DB::transaction(function () use ($request, $registrationRequest, $validated): void {
            $pendingRequest = RegistrationRequest::query()
                ->whereKey($registrationRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($pendingRequest->status === 'pending', 409, 'This request has already been reviewed.');
            abort_if(
                User::query()->where('email', $pendingRequest->email)->exists(),
                409,
                'An account already exists for this email address.',
            );

            $user = User::create([
                'name' => $pendingRequest->name,
                'email' => $pendingRequest->email,
                'password' => $pendingRequest->password,
                'role' => $validated['role'],
                'is_active' => true,
            ]);

            $pendingRequest->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'account_approved',
                'record_type' => User::class,
                'record_id' => $user->id,
                'details' => 'Approved access request and assigned role: '.User::ROLES[$user->role].'.',
                'created_at' => now(),
            ]);
        });

        return back()->with('status', 'The account has been approved and created.');
    }

    public function reject(Request $request, RegistrationRequest $registrationRequest): RedirectResponse
    {
        DB::transaction(function () use ($request, $registrationRequest): void {
            $pendingRequest = RegistrationRequest::query()
                ->whereKey($registrationRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($pendingRequest->status === 'pending', 409, 'This request has already been reviewed.');

            $pendingRequest->update([
                'status' => 'rejected',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'account_request_rejected',
                'record_type' => RegistrationRequest::class,
                'record_id' => $pendingRequest->id,
                'details' => 'Rejected access request for '.$pendingRequest->email.'.',
                'created_at' => now(),
            ]);
        });

        return back()->with('status', 'The access request has been rejected. No account was created.');
    }
}
