<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkOrderDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(WorkOrder::STATUSES))],
            'priority' => ['nullable', Rule::in(array_keys(WorkOrder::PRIORITIES))],
        ]);
        $user = $request->user();
        $assignedWorkOrders = WorkOrder::query()
            ->whereBelongsTo($user, 'assignedPersonnel');

        $workOrders = (clone $assignedWorkOrders)
            ->with(['asset', 'materialUsages.material'])
            ->when(
                $filters['status'] ?? null,
                fn ($query, string $status) => $query->where('status', $status),
            )
            ->when(
                $filters['priority'] ?? null,
                fn ($query, string $priority) => $query->where('priority', $priority),
            )
            ->orderByRaw('CASE WHEN scheduled_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('scheduled_at')
            ->paginate(8)
            ->withQueryString();

        return view('field.dashboard', [
            'workOrders' => $workOrders,
            'filter' => $filters['status'] ?? null,
            'priorityFilter' => $filters['priority'] ?? null,
            'counts' => [
                'assigned' => (clone $assignedWorkOrders)->where('status', WorkOrder::STATUS_ASSIGNED)->count(),
                'in_progress' => (clone $assignedWorkOrders)->where('status', WorkOrder::STATUS_IN_PROGRESS)->count(),
                'for_review' => (clone $assignedWorkOrders)->where('status', WorkOrder::STATUS_FOR_REVIEW)->count(),
                'completed' => (clone $assignedWorkOrders)->where('status', WorkOrder::STATUS_COMPLETED)->count(),
                'urgent' => (clone $assignedWorkOrders)
                    ->where('priority', WorkOrder::PRIORITY_URGENT)
                    ->whereNotIn('status', [WorkOrder::STATUS_COMPLETED])
                    ->count(),
            ],
            'statuses' => WorkOrder::STATUSES,
            'priorities' => WorkOrder::PRIORITIES,
            'materials' => Material::query()
                ->where('quantity_on_hand', '>', 0)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
