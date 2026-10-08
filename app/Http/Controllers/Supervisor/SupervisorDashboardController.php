<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupervisorDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(WorkOrder::STATUSES))],
            'priority' => ['nullable', Rule::in(array_keys(WorkOrder::PRIORITIES))],
        ]);

        $workOrderQuery = WorkOrder::query();
        $workOrders = (clone $workOrderQuery)
            ->with(['assignedPersonnel', 'asset', 'materialUsages.material'])
            ->when(
                $filters['status'] ?? null,
                fn ($query, string $status) => $query->where('status', $status),
            )
            ->when(
                $filters['priority'] ?? null,
                fn ($query, string $priority) => $query->where('priority', $priority),
            )
            ->orderByRaw('CASE WHEN priority = ? THEN 0 ELSE 1 END', [WorkOrder::PRIORITY_URGENT])
            ->orderByRaw('CASE WHEN scheduled_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('scheduled_at')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $fieldPersonnel = User::query()
            ->where('role', User::ROLE_FIELD_PERSONNEL)
            ->where('is_active', true)
            ->withCount([
                'assignedWorkOrders as active_work_orders_count' => fn ($query) => $query
                    ->where('status', '!=', WorkOrder::STATUS_COMPLETED),
            ])
            ->orderBy('name')
            ->get();

        return view('supervisor.dashboard', [
            'workOrders' => $workOrders,
            'filter' => $filters['status'] ?? null,
            'priorityFilter' => $filters['priority'] ?? null,
            'statuses' => WorkOrder::STATUSES,
            'priorities' => WorkOrder::PRIORITIES,
            'counts' => [
                'pending' => (clone $workOrderQuery)->where('status', WorkOrder::STATUS_PENDING)->count(),
                'assigned' => (clone $workOrderQuery)->where('status', WorkOrder::STATUS_ASSIGNED)->count(),
                'in_progress' => (clone $workOrderQuery)->where('status', WorkOrder::STATUS_IN_PROGRESS)->count(),
                'for_review' => (clone $workOrderQuery)->where('status', WorkOrder::STATUS_FOR_REVIEW)->count(),
                'completed' => (clone $workOrderQuery)->where('status', WorkOrder::STATUS_COMPLETED)->count(),
                'urgent' => (clone $workOrderQuery)
                    ->where('priority', WorkOrder::PRIORITY_URGENT)
                    ->where('status', '!=', WorkOrder::STATUS_COMPLETED)
                    ->count(),
            ],
            'fieldPersonnel' => $fieldPersonnel,
            'assets' => Asset::query()->orderBy('asset_number')->get(),
            'unassignedWorkOrders' => WorkOrder::query()
                ->with('asset:id,asset_number,name')
                ->where('status', WorkOrder::STATUS_PENDING)
                ->whereNull('assigned_personnel_id')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
