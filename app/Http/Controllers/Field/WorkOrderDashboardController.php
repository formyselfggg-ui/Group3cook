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
            ->with([
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
            ->orderByRaw('CASE WHEN scheduled_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('scheduled_at')
            ->paginate(8)
            ->withQueryString();
        $workOrderCounts = (clone $assignedWorkOrders)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'COUNT(CASE WHEN priority = ? AND status != ? THEN 1 END) as urgent_total',
                [WorkOrder::PRIORITY_URGENT, WorkOrder::STATUS_COMPLETED],
            )
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('field.dashboard', [
            'workOrders' => $workOrders,
            'filter' => $filters['status'] ?? null,
            'priorityFilter' => $filters['priority'] ?? null,
            'counts' => [
                'assigned' => (int) ($workOrderCounts->get(WorkOrder::STATUS_ASSIGNED)->total ?? 0),
                'in_progress' => (int) ($workOrderCounts->get(WorkOrder::STATUS_IN_PROGRESS)->total ?? 0),
                'for_review' => (int) ($workOrderCounts->get(WorkOrder::STATUS_FOR_REVIEW)->total ?? 0),
                'completed' => (int) ($workOrderCounts->get(WorkOrder::STATUS_COMPLETED)->total ?? 0),
                'urgent' => (int) $workOrderCounts->sum('urgent_total'),
            ],
            'statuses' => WorkOrder::STATUSES,
            'priorities' => WorkOrder::PRIORITIES,
            'materials' => Material::query()
                ->where('quantity_on_hand', '>', 0)
                ->orderBy('name')
                ->get(['id', 'name', 'quantity_on_hand', 'unit']),
        ]);
    }
}
