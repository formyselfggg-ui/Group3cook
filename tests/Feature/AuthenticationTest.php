<?php

namespace Tests\Feature;

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_homepage_displays_the_sign_in_page(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertSee('Sign in to your account');
    }

    public function test_registration_creates_a_pending_request_but_not_a_user_account(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Jamie Crew',
            'email' => 'jamie@example.com',
            'role' => User::ROLE_FIELD_PERSONNEL,
            'password' => 'field-password-123',
            'password_confirmation' => 'field-password-123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseHas('registration_requests', [
            'email' => 'jamie@example.com',
            'status' => 'pending',
            'requested_role' => User::ROLE_FIELD_PERSONNEL,
        ]);
        $this->assertTrue(password_verify(
            'field-password-123',
            RegistrationRequest::query()->firstOrFail()->password,
        ));

        $this->from(route('login'))->post(route('login'), [
            'email' => 'jamie@example.com',
            'password' => 'field-password-123',
        ])->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_registration_cannot_request_an_admin_role(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Jamie Crew',
                'email' => 'jamie@example.com',
                'role' => 'admin',
                'password' => 'field-password-123',
                'password_confirmation' => 'field-password-123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseCount('registration_requests', 0);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_only_an_admin_can_approve_requests_and_approval_creates_a_sign_in_ready_user(): void
    {
        $registration = $this->createRegistrationRequest();
        $staff = User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($staff)
            ->post(route('admin.access-requests.approve', $registration), ['role' => User::ROLE_SUPERVISOR])
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.access-requests.index'))
            ->assertOk()
            ->assertSee('jamie@example.com')
            ->assertSee('Approve');

        $this->actingAs($admin)
            ->post(route('admin.access-requests.approve', $registration), ['role' => User::ROLE_SUPERVISOR])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'email' => $registration->email,
            'role' => User::ROLE_SUPERVISOR,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('registration_requests', [
            'id' => $registration->id,
            'status' => 'approved',
            'reviewed_by_user_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'registration_approved',
        ]);

        auth()->logout();
        $this->post(route('login'), [
            'email' => $registration->email,
            'password' => 'field-password-123',
        ])->assertRedirect(route('supervisor.dashboard'));
    }

    public function test_admin_can_approve_an_operations_and_engineering_account(): void
    {
        $registration = $this->createRegistrationRequest();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.registration-requests.approve', $registration), [
                'role' => User::ROLE_OPERATIONS,
            ])
            ->assertRedirect(route('admin.registration-requests'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'email' => $registration->email,
            'role' => User::ROLE_OPERATIONS,
            'is_active' => true,
        ]);

        auth()->logout();
        $this->post(route('login'), [
            'email' => $registration->email,
            'password' => 'field-password-123',
        ])->assertRedirect(route('operations.dashboard'));
    }

    public function test_rejection_does_not_create_a_user_and_is_logged(): void
    {
        $registration = $this->createRegistrationRequest();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.access-requests.reject', $registration))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('registration_requests', [
            'id' => $registration->id,
            'status' => 'rejected',
            'reviewed_by_user_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'registration_rejected',
        ]);
    }

    public function test_admin_pages_reject_non_admin_users(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'field_personnel']))
            ->get(route('admin.access-requests.index'))
            ->assertForbidden();
    }

    private function createRegistrationRequest(): RegistrationRequest
    {
        return RegistrationRequest::create([
            'name' => 'Jamie Crew',
            'email' => 'jamie@example.com',
            'password' => 'field-password-123',
            'requested_role' => 'field_personnel',
            'status' => 'pending',
        ]);
    }
}
