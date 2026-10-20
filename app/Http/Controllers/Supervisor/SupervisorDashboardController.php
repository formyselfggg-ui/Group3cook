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
            ->select([
                'id',
                'work_order_number',
                'title',
                'description',
                'category',
                'priority',
                'assigned_personnel_id',
                'asset_id',
                'scheduled_at',
                'status',
                'work_performed',
                'field_notes',
                'photo_paths',
                'submitted_at',
                'return_notes',
                'supervisor_approved_at',
                'created_at',
            ])
            ->with([
                'assignedPersonnel:id,name',
                'asset:id,asset_number,name',
                'materialUsages:id,work_order_id,material_id,quantity',
                'materialUsages.material:id,name,unit',
            ])
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

        $workOrderCounts = WorkOrder::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'COUNT(CASE WHEN priority = ? AND status != ? THEN 1 END) as urgent_total',
                [WorkOrder::PRIORITY_URGENT, WorkOrder::STATUS_COMPLETED],
            )
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        $fieldPersonnel = User::query()
            ->where('role', User::ROLE_FIELD_PERSONNEL)
            ->where('is_active', true)
            ->select(['id', 'name', 'email'])
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
                'pending' => (int) ($workOrderCounts->get(WorkOrder::STATUS_PENDING)->total ?? 0),
                'assigned' => (int) ($workOrderCounts->get(WorkOrder::STATUS_ASSIGNED)->total ?? 0),
                'in_progress' => (int) ($workOrderCounts->get(WorkOrder::STATUS_IN_PROGRESS)->total ?? 0),
                'for_review' => (int) ($workOrderCounts->get(WorkOrder::STATUS_FOR_REVIEW)->total ?? 0),
                'completed' => (int) ($workOrderCounts->get(WorkOrder::STATUS_COMPLETED)->total ?? 0),
                'urgent' => (int) $workOrderCounts->sum('urgent_total'),
            ],
            'fieldPersonnel' => $fieldPersonnel,
            'assets' => Asset::query()->orderBy('asset_number')->get(['id', 'asset_number', 'name']),
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
