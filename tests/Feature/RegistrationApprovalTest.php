<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_registration_form_and_login_page_links_to_it(): void
    {
        $this->get(route('login'))
            ->assertSee(route('register'))
            ->assertSee('Request access');

        $this->get(route('register'))
            ->assertSee('Request an account')
            ->assertSee('Administrator approval required')
            ->assertSee('Field personnel')
            ->assertSee('Supervisor / Dispatcher');
    }

    public function test_registration_creates_only_a_pending_request_with_a_hashed_password(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Casey Crew',
            'email' => 'casey@example.com',
            'role' => User::ROLE_FIELD_PERSONNEL,
            'password' => 'crew-password',
            'password_confirmation' => 'crew-password',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your registration request was sent. You can sign in after an administrator approves it.');

        $registrationRequest = RegistrationRequest::query()->where('email', 'casey@example.com')->firstOrFail();

        $this->assertSame(RegistrationRequest::STATUS_PENDING, $registrationRequest->status);
        $this->assertSame(User::ROLE_FIELD_PERSONNEL, $registrationRequest->requested_role);
        $this->assertTrue(Hash::check('crew-password', $registrationRequest->password));
        $this->assertDatabaseMissing('users', ['email' => 'casey@example.com']);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'registration_requested',
            'record_type' => RegistrationRequest::class,
            'record_id' => $registrationRequest->id,
        ]);
        $this->assertGuest();
    }

    public function test_registration_cannot_request_an_administrator_account(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Unsafe Request',
                'email' => 'unsafe@example.com',
                'role' => User::ROLE_ADMIN,
                'password' => 'crew-password',
                'password_confirmation' => 'crew-password',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'unsafe@example.com']);
        $this->assertDatabaseMissing('registration_requests', ['email' => 'unsafe@example.com']);
    }

    public function test_admin_can_approve_a_request_and_create_an_active_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $registrationRequest = RegistrationRequest::create([
            'name' => 'Riley Supervisor',
            'email' => 'riley@example.com',
            'password' => Hash::make('requested-password'),
            'requested_role' => User::ROLE_FIELD_PERSONNEL,
            'status' => RegistrationRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.registration-requests'))
            ->assertSee('Riley Supervisor')
            ->assertSee('riley@example.com')
            ->assertSee('Approve');

        $this->post(route('admin.registration-requests.approve', $registrationRequest), [
            'role' => User::ROLE_SUPERVISOR,
        ])
            ->assertRedirect(route('admin.registration-requests'))
            ->assertSessionHas('status', 'Registration approved. The new account can now sign in.');

        $user = User::query()->where('email', 'riley@example.com')->firstOrFail();
        $this->assertSame(User::ROLE_SUPERVISOR, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('requested-password', $user->password));
        $this->assertSame(RegistrationRequest::STATUS_APPROVED, $registrationRequest->fresh()->status);
        $this->assertSame($admin->id, $registrationRequest->fresh()->reviewed_by_user_id);
        $this->assertSame($user->id, $registrationRequest->fresh()->user_id);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'registration_approved',
            'record_type' => RegistrationRequest::class,
            'record_id' => $registrationRequest->id,
        ]);

        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => 'riley@example.com',
            'password' => 'requested-password',
        ])->assertRedirect(route('supervisor.dashboard'));
    }

    public function test_admin_can_reject_a_request_without_creating_a_user(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $registrationRequest = RegistrationRequest::create([
            'name' => 'Jordan Field',
            'email' => 'jordan@example.com',
            'password' => Hash::make('requested-password'),
            'requested_role' => User::ROLE_FIELD_PERSONNEL,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.registration-requests.reject', $registrationRequest))
            ->assertRedirect(route('admin.registration-requests'))
            ->assertSessionHas('status', 'Registration request rejected.');

        $this->assertSame(RegistrationRequest::STATUS_REJECTED, $registrationRequest->fresh()->status);
        $this->assertDatabaseMissing('users', ['email' => 'jordan@example.com']);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'registration_rejected',
            'record_id' => $registrationRequest->id,
        ]);
    }

    public function test_non_admin_cannot_review_registration_requests(): void
    {
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $registrationRequest = RegistrationRequest::create([
            'name' => 'Requested Account',
            'email' => 'requested@example.com',
            'password' => Hash::make('requested-password'),
            'requested_role' => User::ROLE_FIELD_PERSONNEL,
        ]);

        $this->actingAs($fieldUser)
            ->get(route('admin.registration-requests'))
            ->assertForbidden();

        $this->post(route('admin.registration-requests.approve', $registrationRequest), [
            'role' => User::ROLE_FIELD_PERSONNEL,
        ])->assertForbidden();

        $this->assertSame(RegistrationRequest::STATUS_PENDING, $registrationRequest->fresh()->status);
        $this->assertDatabaseMissing('users', ['email' => 'requested@example.com']);
    }

    public function test_email_already_used_by_a_user_or_pending_request_cannot_register_again(): void
    {
        $existingUser = User::factory()->create(['email' => 'user@example.com']);
        RegistrationRequest::create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'password' => Hash::make('requested-password'),
            'requested_role' => User::ROLE_FIELD_PERSONNEL,
        ]);

        foreach ([$existingUser->email, 'pending@example.com'] as $email) {
            $this->from(route('register'))
                ->post(route('register.store'), [
                    'name' => 'Duplicate User',
                    'email' => $email,
                    'role' => User::ROLE_FIELD_PERSONNEL,
                    'password' => 'crew-password',
                    'password_confirmation' => 'crew-password',
                ])
                ->assertRedirect(route('register'))
                ->assertSessionHasErrors('email');
        }

        $this->assertSame(0, ActivityLog::query()->where('action', 'registration_requested')->count());
    }
}
