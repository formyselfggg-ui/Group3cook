<?php

namespace Tests\Feature;

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

    public function test_authenticated_user_can_view_profile_and_assigned_role(): void
    {
        $user = User::factory()->create([
            'name' => 'Jamie Crew',
            'email' => 'jamie@example.com',
            'role' => 'field_personnel',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Jamie Crew')
            ->assertSee('jamie@example.com')
            ->assertSee('Field Personnel')
            ->assertSee('Change password');
    }

    public function test_user_can_update_profile_details_without_changing_role(): void
    {
        $user = User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Jamie Updated',
                'email' => 'jamie.updated@example.com',
                'role' => 'admin',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'Your profile has been updated.');

        $user->refresh();
        $this->assertSame('Jamie Updated', $user->name);
        $this->assertSame('jamie.updated@example.com', $user->email);
        $this->assertSame('supervisor', $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'user_profile_updated',
            'record_type' => User::class,
            'record_id' => $user->id,
            'details' => 'Updated profile fields: name, email.',
        ]);
    }

    public function test_profile_update_rejects_an_email_already_in_use(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'jamie@example.com']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => 'Jamie Crew',
                'email' => 'taken@example.com',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('email');

        $this->assertSame('jamie@example.com', $user->fresh()->email);
        $this->assertDatabaseMissing('activity_logs', [
            'user_id' => $user->id,
            'action' => 'user_profile_updated',
        ]);
    }

    public function test_user_can_change_password_after_confirming_current_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'current-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'Your password has been changed.');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'user_password_changed',
            'record_type' => User::class,
            'record_id' => $user->id,
            'details' => 'Changed account password.',
        ]);
        $this->assertDatabaseMissing('activity_logs', [
            'details' => 'new-password-123',
        ]);
    }

    public function test_password_change_rejects_an_incorrect_current_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.password.update'), [
                'current_password' => 'incorrect-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
        $this->assertDatabaseMissing('activity_logs', [
            'user_id' => $user->id,
            'action' => 'user_password_changed',
        ]);
    }
}
