<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'role' => ['nullable', Rule::in(array_keys(User::ROLES))],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $users = User::query()
            ->with('permissions')
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('is_active', $status === 'active'))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => User::ROLES,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['is_active' => true]),
            'roles' => User::ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', 'confirmed', Password::min(8)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = DB::transaction(function () use ($request, $validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => $validated['password'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'user_account_created',
                'record_type' => User::class,
                'record_id' => $user->id,
                'details' => 'Created account '.$user->email.' with the '.$user->role.' role.',
            ]);

            return $user;
        });

        return redirect()->route('admin.users.index')->with('status', 'Account created for '.$user->email.'.');
    }

    public function edit(User $user): View
    {
        $user->load('permissions');

        return view('admin.users.form', [
            'user' => $user,
            'roles' => User::ROLES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $user, $validated): void {
            $previousRole = $user->role;

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'is_active' => $request->boolean('is_active'),
                'password' => $validated['password'] ?? $user->password,
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => $previousRole === $user->role ? 'user_account_updated' : 'user_role_changed',
                'record_type' => User::class,
                'record_id' => $user->id,
                'details' => $previousRole === $user->role
                    ? 'Updated account '.$user->email.'.'
                    : 'Changed role of '.$user->email.' from '.$previousRole.' to '.$user->role.'.',
            ]);
        });

        return redirect()->route('admin.users.index')->with('status', 'Account updated.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $user->is_active ? 'user_account_activated' : 'user_account_deactivated',
            'record_type' => User::class,
            'record_id' => $user->id,
            'details' => ($user->is_active ? 'Activated' : 'Deactivated').' account '.$user->email.'.',
        ]);

        return redirect()->route('admin.users.index')->with(
            'status',
            $user->is_active ? 'Account activated.' : 'Account deactivated.',
        );
    }
}
