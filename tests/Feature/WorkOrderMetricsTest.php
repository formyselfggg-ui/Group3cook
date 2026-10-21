<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use App\Services\WorkOrderMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_completion_rate_and_open_pipeline_are_calculated(): void
    {
        WorkOrder::factory()->count(3)->create(['status' => WorkOrder::STATUS_COMPLETED]);
        WorkOrder::factory()->count(2)->create(['status' => WorkOrder::STATUS_IN_PROGRESS]);

        $metrics = WorkOrderMetrics::for(WorkOrder::query());

        $this->assertSame(5, $metrics->total());
        $this->assertSame(3, $metrics->completed());
        $this->assertSame(2, $metrics->open());
        $this->assertSame(60.0, $metrics->completionRate());
    }

    public function test_completion_rate_is_zero_without_work_orders(): void
    {
        $metrics = WorkOrderMetrics::for(WorkOrder::query());

        $this->assertSame(0.0, $metrics->completionRate());
        $this->assertSame(0, $metrics->total());
        $this->assertNull($metrics->averageCycleDays());
        $this->assertNull($metrics->onTimeRate());
    }

    public function test_overdue_work_orders_are_counted_against_the_schedule(): void
    {
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
            'scheduled_at' => now()->subDays(2),
        ]);
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
            'scheduled_at' => now()->addDays(2),
        ]);
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_COMPLETED,
            'scheduled_at' => now()->subDays(5),
            'completion_at' => now()->subDays(4),
        ]);

        $metrics = WorkOrderMetrics::for(WorkOrder::query());

        $this->assertSame(1, $metrics->overdue());
    }

    public function test_on_time_rate_and_average_cycle_days_use_completed_work(): void
    {
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_COMPLETED,
            'created_at' => now()->subDays(6),
            'scheduled_at' => now()->subDays(1),
            'completion_at' => now()->subDays(2),
        ]);
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_COMPLETED,
            'created_at' => now()->subDays(4),
            'scheduled_at' => now()->subDays(6),
            'completion_at' => now()->subDays(3),
        ]);

        $metrics = WorkOrderMetrics::for(WorkOrder::query());

        $this->assertSame(50.0, $metrics->onTimeRate());
        $this->assertSame(2.5, $metrics->averageCycleDays());
    }

    public function test_aging_buckets_group_open_work_orders_by_age(): void
    {
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
            'created_at' => now()->subDays(1),
        ]);
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_ASSIGNED,
            'created_at' => now()->subDays(5),
        ]);
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_PENDING,
            'created_at' => now()->subDays(20),
        ]);
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_COMPLETED,
            'created_at' => now()->subDays(30),
        ]);

        $metrics = WorkOrderMetrics::for(WorkOrder::query());

        $this->assertSame([
            '0-2 days' => 1,
            '3-7 days' => 1,
            '8-14 days' => 0,
            '15+ days' => 1,
        ], $metrics->agingBuckets());
    }

    public function test_priority_mix_only_counts_open_work_orders(): void
    {
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
            'priority' => WorkOrder::PRIORITY_URGENT,
        ]);
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_COMPLETED,
            'priority' => WorkOrder::PRIORITY_URGENT,
        ]);
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_ASSIGNED,
            'priority' => WorkOrder::PRIORITY_NORMAL,
        ]);

        $metrics = WorkOrderMetrics::for(WorkOrder::query());

        $mix = $metrics->priorityMix();

        $this->assertSame(1, $mix[WorkOrder::PRIORITY_URGENT]);
        $this->assertSame(1, $mix[WorkOrder::PRIORITY_NORMAL]);
        $this->assertSame(0, $mix[WorkOrder::PRIORITY_LOW]);
    }

    public function test_weekly_throughput_reports_raised_and_completed_work(): void
    {
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
            'created_at' => now()->subDays(2),
        ]);
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_COMPLETED,
            'created_at' => now()->subDays(40),
            'completion_at' => now()->subDay(),
        ]);

        $throughput = WorkOrderMetrics::for(WorkOrder::query())->weeklyThroughput();

        $this->assertCount(6, $throughput);

        $lastWeek = end($throughput);

        $this->assertSame(1, $lastWeek['raised']);
        $this->assertSame(1, $lastWeek['completed']);
    }

    public function test_approved_work_orders_are_counted_as_ready_to_close(): void
    {
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_FOR_REVIEW,
            'supervisor_approved_at' => now(),
        ]);
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_FOR_REVIEW,
            'supervisor_approved_at' => null,
        ]);
        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_COMPLETED,
            'supervisor_approved_at' => now(),
        ]);

        $metrics = WorkOrderMetrics::for(WorkOrder::query());

        $this->assertSame(1, $metrics->awaitingReview());
        $this->assertSame(1, $metrics->approvedPendingClosure());
    }

    public function test_approval_does_not_complete_a_work_order_until_it_is_closed(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $workOrder = WorkOrder::factory()->create(['status' => WorkOrder::STATUS_FOR_REVIEW]);

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.review', $workOrder), ['decision' => 'approve'])
            ->assertRedirect(route('supervisor.dashboard'));

        $workOrder->refresh();

        $this->assertSame(WorkOrder::STATUS_FOR_REVIEW, $workOrder->status);
        $this->assertNotNull($workOrder->supervisor_approved_at);
        $this->assertNull($workOrder->completion_at);
        $this->assertSame(0, WorkOrderMetrics::for(WorkOrder::query())->completed());

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.close', $workOrder))
            ->assertRedirect(route('supervisor.dashboard'));

        $workOrder->refresh();

        $this->assertSame(WorkOrder::STATUS_COMPLETED, $workOrder->status);
        $this->assertNotNull($workOrder->completion_at);
        $this->assertSame(1, WorkOrderMetrics::for(WorkOrder::query())->completed());
    }

    public function test_admin_dashboard_shows_the_delivery_kpis(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_COMPLETED,
            'created_at' => now()->subDays(3),
            'scheduled_at' => now()->subDay(),
            'completion_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Delivery performance')
            ->assertSee('Completion rate')
            ->assertSee('On-time delivery')
            ->assertSee('Average cycle time')
            ->assertSee('Weekly throughput')
            ->assertSee('Open work aging')
            ->assertSee('Field crew performance')
            ->assertSee('Material risk');
    }

    public function test_supervisor_dashboard_shows_the_performance_kpis(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);

        WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
            'scheduled_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($supervisor)->get(route('supervisor.dashboard'));

        $response->assertOk()
            ->assertSee('Performance KPIs')
            ->assertSee('Completion rate')
            ->assertSee('Crew utilisation')
            ->assertSee('Awaiting review')
            ->assertSee('Weekly throughput')
            ->assertSee('Open work aging');
    }
}
