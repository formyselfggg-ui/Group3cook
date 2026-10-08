<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_can_sign_in_and_is_redirected_to_the_admin_overview(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'password' => 'admin-password',
        ]);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'admin-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertSee('Admin overview')
            ->assertSee('Users by role')
            ->assertSee('Recent system activity');
    }

    public function test_admin_can_sign_out_and_return_to_the_sign_in_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_admin_overview_shows_role_counts_and_recent_activity(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        User::factory()->create([
            'role' => User::ROLE_SUPERVISOR,
            'is_active' => false,
        ]);
        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'test_admin_activity',
            'record_type' => User::class,
            'record_id' => $admin->id,
            'details' => 'Admin overview activity test.',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertSee('Total users')
            ->assertSee('Active users')
            ->assertSee('Administrators')
            ->assertSee('Field Personnel')
            ->assertSee('Team composition')
            ->assertSee('Work order pipeline')
            ->assertSee('Recent system activity')
            ->assertSee('Admin overview activity test.')
            ->assertSee('Test Admin Activity');
    }

    public function test_admin_overview_summarizes_work_orders_by_status(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        WorkOrder::factory()->create(['status' => WorkOrder::STATUS_ASSIGNED]);
        WorkOrder::factory()->create(['status' => WorkOrder::STATUS_IN_PROGRESS]);
        WorkOrder::factory()->create(['status' => WorkOrder::STATUS_IN_PROGRESS]);
        WorkOrder::factory()->create(['status' => WorkOrder::STATUS_COMPLETED]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $this->assertSame(4, $response->viewData('totalWorkOrders'));
        $this->assertSame(1, $response->viewData('workOrdersByStatus')->get(WorkOrder::STATUS_ASSIGNED));
        $this->assertSame(2, $response->viewData('workOrdersByStatus')->get(WorkOrder::STATUS_IN_PROGRESS));
        $this->assertSame(1, $response->viewData('workOrdersByStatus')->get(WorkOrder::STATUS_COMPLETED));
        $response->assertSee('Work order pipeline')
            ->assertSee('Assigned')
            ->assertSee('In Progress')
            ->assertSee('Completed');
    }

    public function test_guest_is_redirected_to_sign_in_from_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_field_personnel_is_forbidden_from_admin_dashboard(): void
    {
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);

        $this->actingAs($fieldUser)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_inactive_admin_cannot_sign_in(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'password' => 'admin-password',
            'is_active' => false,
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => $admin->email,
                'password' => 'admin-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_roles_without_a_dashboard_are_not_signed_in(): void
    {
        $operationsUser = User::factory()->create([
            'role' => User::ROLE_OPERATIONS,
            'password' => 'supervisor-password',
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => $operationsUser->email,
                'password' => 'supervisor-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
