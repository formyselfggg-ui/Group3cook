<?php

namespace App\Services;

use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Computes delivery KPIs for a set of work orders.
 *
 * The calculations run in PHP so the same figures are produced on every
 * database driver the application supports.
 */
class WorkOrderMetrics
{
    /**
     * @param  Collection<int, WorkOrder>  $workOrders
     */
    public function __construct(private readonly Collection $workOrders) {}

    /**
     * Build the metrics for a work order query.
     */
    public static function for(Builder $query): self
    {
        return new self(
            (clone $query)
                ->select(['id', 'status', 'priority', 'created_at', 'date_assigned', 'scheduled_at', 'submitted_at', 'supervisor_approved_at', 'completion_at'])
                ->get(),
        );
    }

    public function total(): int
    {
        return $this->workOrders->count();
    }

    public function open(): int
    {
        return $this->workOrders
            ->where('status', '!=', WorkOrder::STATUS_COMPLETED)
            ->count();
    }

    public function completed(): int
    {
        return $this->workOrders
            ->where('status', WorkOrder::STATUS_COMPLETED)
            ->count();
    }

    /**
     * The share of work orders that reached completion, as a percentage.
     */
    public function completionRate(): float
    {
        $total = $this->total();

        if ($total === 0) {
            return 0.0;
        }

        return round(($this->completed() / $total) * 100, 1);
    }

    /**
     * Open work orders whose scheduled date has already passed.
     */
    public function overdue(): int
    {
        return $this->workOrders
            ->where('status', '!=', WorkOrder::STATUS_COMPLETED)
            ->whereNotNull('scheduled_at')
            ->filter(fn (WorkOrder $workOrder): bool => $workOrder->scheduled_at->isPast())
            ->count();
    }

    /**
     * The share of completed work delivered on or before the scheduled date.
     */
    public function onTimeRate(): ?float
    {
        $scheduled = $this->workOrders->filter(
            fn (WorkOrder $workOrder): bool => $workOrder->status === WorkOrder::STATUS_COMPLETED
                && $workOrder->scheduled_at !== null
                && $workOrder->completion_at !== null,
        );

        if ($scheduled->isEmpty()) {
            return null;
        }

        $onTime = $scheduled->filter(
            fn (WorkOrder $workOrder): bool => $workOrder->completion_at->lessThanOrEqualTo($workOrder->scheduled_at),
        )->count();

        return round(($onTime / $scheduled->count()) * 100, 1);
    }

    /**
     * Average number of days between raising and completing a work order.
     */
    public function averageCycleDays(): ?float
    {
        $cycles = $this->workOrders->filter(
            fn (WorkOrder $workOrder): bool => $workOrder->status === WorkOrder::STATUS_COMPLETED
                && $workOrder->created_at !== null
                && $workOrder->completion_at !== null,
        )->map(
            fn (WorkOrder $workOrder): float => $workOrder->created_at->diffInDays($workOrder->completion_at, true),
        );

        if ($cycles->isEmpty()) {
            return null;
        }

        return round($cycles->avg(), 1);
    }

    /**
     * Average number of days a work order has been waiting for review.
     */
    public function averageReviewDays(): ?float
    {
        $waits = $this->workOrders->filter(
            fn (WorkOrder $workOrder): bool => $workOrder->status === WorkOrder::STATUS_FOR_REVIEW
                && $workOrder->submitted_at !== null,
        )->map(
            fn (WorkOrder $workOrder): float => $workOrder->submitted_at->diffInDays(now(), true),
        );

        if ($waits->isEmpty()) {
            return null;
        }

        return round($waits->avg(), 1);
    }

    /**
     * Work orders submitted by the field and still waiting for a supervisor decision.
     */
    public function awaitingReview(): int
    {
        return $this->workOrders
            ->where('status', WorkOrder::STATUS_FOR_REVIEW)
            ->whereNull('supervisor_approved_at')
            ->count();
    }

    /**
     * Work orders the supervisor approved that are still waiting to be closed.
     */
    public function approvedPendingClosure(): int
    {
        return $this->workOrders
            ->where('status', WorkOrder::STATUS_FOR_REVIEW)
            ->whereNotNull('supervisor_approved_at')
            ->count();
    }

    /**
     * Work orders grouped by how long they have been open.
     *
     * @return array<string, int>
     */
    public function agingBuckets(): array
    {
        $buckets = [
            '0-2 days' => 0,
            '3-7 days' => 0,
            '8-14 days' => 0,
            '15+ days' => 0,
        ];

        foreach ($this->workOrders->where('status', '!=', WorkOrder::STATUS_COMPLETED) as $workOrder) {
            $age = $workOrder->created_at?->diffInDays(now(), true) ?? 0;

            match (true) {
                $age <= 2 => $buckets['0-2 days']++,
                $age <= 7 => $buckets['3-7 days']++,
                $age <= 14 => $buckets['8-14 days']++,
                default => $buckets['15+ days']++,
            };
        }

        return $buckets;
    }

    /**
     * Open work orders grouped by priority.
     *
     * @return array<string, int>
     */
    public function priorityMix(): array
    {
        $mix = array_fill_keys(array_keys(WorkOrder::PRIORITIES), 0);

        foreach ($this->workOrders->where('status', '!=', WorkOrder::STATUS_COMPLETED) as $workOrder) {
            if (array_key_exists($workOrder->priority, $mix)) {
                $mix[$workOrder->priority]++;
            }
        }

        return $mix;
    }

    /**
     * Weekly work orders raised and completed.
     *
     * @return array<int, array{label: string, raised: int, completed: int}>
     */
    public function weeklyThroughput(int $weeks = 6): array
    {
        $currentWeek = Carbon::now()->startOfWeek();
        $firstWeek = $currentWeek->copy()->subWeeks($weeks - 1);

        $raised = [];
        $completed = [];

        for ($index = 0; $index < $weeks; $index++) {
            $raised[$index] = 0;
            $completed[$index] = 0;
        }

        foreach ($this->workOrders as $workOrder) {
            $raisedIndex = $this->weekIndex($workOrder->created_at, $firstWeek, $currentWeek);

            if ($raisedIndex !== null) {
                $raised[$raisedIndex]++;
            }

            $completedIndex = $this->weekIndex($workOrder->completion_at, $firstWeek, $currentWeek);

            if ($completedIndex !== null) {
                $completed[$completedIndex]++;
            }
        }

        $throughput = [];

        for ($index = 0; $index < $weeks; $index++) {
            $throughput[] = [
                'label' => $firstWeek->copy()->addWeeks($index)->format('M j'),
                'raised' => $raised[$index],
                'completed' => $completed[$index],
            ];
        }

        return $throughput;
    }

    private function weekIndex(?Carbon $date, Carbon $firstWeek, Carbon $currentWeek): ?int
    {
        if ($date === null || $date->lt($firstWeek) || $date->gt($currentWeek->copy()->endOfWeek())) {
            return null;
        }

        return (int) $firstWeek->diffInWeeks($date->copy()->startOfWeek());
    }
}
