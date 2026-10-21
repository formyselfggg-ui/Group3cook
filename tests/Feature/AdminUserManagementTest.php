<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_accounts_and_filter_them(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $supervisor = User::factory()->create([
            'role' => User::ROLE_SUPERVISOR,
            'name' => 'Amina Supervisor',
        ]);
        User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk()->assertSee('Amina Supervisor');
        $this->assertSame(3, $response->viewData('users')->total());

        $filtered = $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => User::ROLE_SUPERVISOR]))
            ->assertOk()
            ->assertSee('Amina Supervisor');

        $this->assertSame(1, $filtered->viewData('users')->total());
    }

    public function test_admin_can_create_an_account_with_a_role(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Kofi Field',
                'email' => 'kofi.field@example.com',
                'role' => User::ROLE_FIELD_PERSONNEL,
                'password' => 'field-password',
                'password_confirmation' => 'field-password',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.users.index'));

        $created = User::query()->where('email', 'kofi.field@example.com')->firstOrFail();

        $this->assertSame(User::ROLE_FIELD_PERSONNEL, $created->role);
        $this->assertTrue($created->is_active);
        $this->assertTrue(Hash::check('field-password', $created->password));
        $this->assertDatabaseHas('activity_logs', ['action' => 'user_account_created']);
    }

    public function test_creating_an_account_validates_the_email_and_password(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Invalid Account',
                'email' => 'not-an-email',
                'role' => User::ROLE_ADMIN,
                'password' => 'short',
                'password_confirmation' => 'mismatch',
            ])
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_admin_can_update_an_account_and_change_its_role(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Renamed Person',
                'email' => $user->email,
                'role' => User::ROLE_SUPERVISOR,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.users.index'));

        $user->refresh();

        $this->assertSame('Renamed Person', $user->name);
        $this->assertSame(User::ROLE_SUPERVISOR, $user->role);
        $this->assertDatabaseHas('activity_logs', ['action' => 'user_role_changed']);
    }

    public function test_admin_can_deactivate_and_reactivate_an_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle-status', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertFalse($user->fresh()->is_active);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle-status', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue($user->fresh()->is_active);
        $this->assertDatabaseHas('activity_logs', ['action' => 'user_account_deactivated']);
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle-status', $admin))
            ->assertSessionHasErrors('user');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_non_administrators_cannot_manage_accounts(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);

        $this->actingAs($supervisor)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($supervisor)->get(route('admin.users.create'))->assertForbidden();

        $this->actingAs($supervisor)
            ->post(route('admin.users.store'), [
                'name' => 'Blocked',
                'email' => 'blocked@example.com',
                'role' => User::ROLE_OPERATIONS,
                'password' => 'blocked-password',
                'password_confirmation' => 'blocked-password',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
    }
}
