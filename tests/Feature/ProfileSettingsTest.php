<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_sign_in_from_profile_settings(): void
    {
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_supervisor_and_field_personnel_can_view_profile_settings_from_their_workspace(): void
    {
        $dashboardRoutes = [
            User::ROLE_ADMIN => 'admin.dashboard',
            User::ROLE_SUPERVISOR => 'supervisor.dashboard',
            User::ROLE_FIELD_PERSONNEL => 'field.dashboard',
        ];

        foreach ($dashboardRoutes as $role => $dashboardRoute) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route($dashboardRoute))
                ->assertSee(route('profile.edit'));

            $this->get(route('profile.edit'))
                ->assertSee('Profile settings')
                ->assertSee($user->email)
                ->assertSee('Workspace role')
                ->assertSee('Change password');
        }
    }

    public function test_user_can_update_their_name_and_email_without_changing_their_role(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SUPERVISOR,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Updated Supervisor',
                'email' => 'updated.supervisor@example.com',
                'role' => User::ROLE_ADMIN,
                'is_active' => false,
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'Profile settings saved.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Supervisor',
            'email' => 'updated.supervisor@example.com',
            'role' => User::ROLE_SUPERVISOR,
            'is_active' => true,
            'email_verified_at' => null,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'profile_updated',
            'record_type' => User::class,
            'record_id' => $user->id,
        ]);
    }

    public function test_profile_update_rejects_an_email_owned_by_another_account(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);
        $otherUser = User::factory()->create(['email' => 'already-used@example.com']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => 'Changed Name',
                'email' => $otherUser->email,
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('email');

        $this->assertSame('Original Name', $user->fresh()->name);
        $this->assertSame(0, ActivityLog::query()->where('user_id', $user->id)->count());
    }

    public function test_user_can_change_password_after_confirming_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'Password changed successfully.');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'password_changed',
            'record_type' => User::class,
            'record_id' => $user->id,
        ]);
    }

    public function test_password_change_rejects_an_incorrect_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.password.update'), [
                'current_password' => 'incorrect-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertSame(0, ActivityLog::query()->where('user_id', $user->id)->count());
    }
}
