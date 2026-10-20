<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterialUsage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EngineeringDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $workOrders = $this->filteredWorkOrders($filters)
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
                'created_at',
            ])
            ->with([
                'asset:id,asset_number,name',
                'assignedPersonnel:id,name',
                'materialUsages:id,work_order_id,material_id,quantity',
                'materialUsages.material:id,name,unit',
            ])
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $workOrderMetrics = WorkOrder::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'COUNT(CASE WHEN priority = ? AND status != ? THEN 1 END) as urgent_total',
                [WorkOrder::PRIORITY_URGENT, WorkOrder::STATUS_COMPLETED],
            )
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        $workOrdersByCategory = WorkOrder::query()
            ->select('category')
            ->selectRaw('COUNT(*) as work_orders_count')
            ->groupBy('category')
            ->orderByDesc('work_orders_count')
            ->limit(6)
            ->get();
        $categories = WorkOrder::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
        $assetsByStatus = Asset::query()
            ->select('current_status')
            ->selectRaw('COUNT(*) as assets_count')
            ->groupBy('current_status')
            ->get()
            ->keyBy('current_status');

        return view('operations.dashboard', [
            'workOrders' => $workOrders,
            'filters' => $filters,
            'statuses' => WorkOrder::STATUSES,
            'priorities' => WorkOrder::PRIORITIES,
            'assetStatuses' => Asset::STATUSES,
            'assets' => Asset::query()
                ->with('workOrders:id,asset_id,work_order_number,category,status,completion_at')
                ->orderBy('asset_number')
                ->paginate(10, [
                    'id',
                    'asset_number',
                    'asset_type',
                    'name',
                    'serial_number',
                    'current_status',
                    'condition',
                    'installed_on',
                ], 'asset_page')
                ->withQueryString(),
            'assetOptions' => Asset::query()
                ->orderBy('asset_number')
                ->get(['id', 'asset_number', 'name']),
            'fieldPersonnel' => User::query()
                ->where('role', User::ROLE_FIELD_PERSONNEL)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'categories' => $categories,
            'counts' => [
                'work_orders' => (int) $workOrderMetrics->sum('total'),
                'pending' => (int) ($workOrderMetrics->get(WorkOrder::STATUS_PENDING)->total ?? 0),
                'in_progress' => (int) ($workOrderMetrics->get(WorkOrder::STATUS_IN_PROGRESS)->total ?? 0),
                'completed' => (int) ($workOrderMetrics->get(WorkOrder::STATUS_COMPLETED)->total ?? 0),
                'urgent' => (int) $workOrderMetrics->sum('urgent_total'),
                'assets' => (int) $assetsByStatus->sum('assets_count'),
                'assets_under_maintenance' => (int) ($assetsByStatus->get('under_maintenance')->assets_count ?? 0),
            ],
            'workOrdersByCategory' => $workOrdersByCategory,
            'assetsByStatus' => $assetsByStatus,
            'assetsByCondition' => Asset::query()
                ->select('condition')
                ->selectRaw('COUNT(*) as assets_count')
                ->groupBy('condition')
                ->orderBy('condition')
                ->get(),
            'materialUsage' => WorkOrderMaterialUsage::query()
                ->select('material_id')
                ->selectRaw('SUM(quantity) as quantity_used')
                ->with('material:id,name,unit')
                ->groupBy('material_id')
                ->orderByDesc('quantity_used')
                ->limit(5)
                ->get(),
            'recentMaintenance' => WorkOrder::query()
                ->select([
                    'id',
                    'work_order_number',
                    'category',
                    'status',
                    'asset_id',
                    'assigned_personnel_id',
                    'completion_at',
                    'work_performed',
                ])
                ->with([
                    'asset:id,asset_number,name',
                    'assignedPersonnel:id,name',
                ])
                ->where('status', WorkOrder::STATUS_COMPLETED)
                ->whereNotNull('asset_id')
                ->latest('completion_at')
                ->limit(5)
                ->get(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);

        return response()->streamDownload(function () use ($filters): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                throw new \RuntimeException('Unable to open the work order report stream.');
            }

            fputcsv($output, [
                'Work order number',
                'Title',
                'Category',
                'Priority',
                'Status',
                'Asset',
                'Assigned personnel',
                'Created date',
                'Scheduled date',
                'Completion date',
                'Work performed',
                'Field notes',
                'Materials used',
            ], ',', '"', '', "\r\n");

            $workOrders = $this->filteredWorkOrders($filters)
                ->select([
                    'id',
                    'work_order_number',
                    'title',
                    'category',
                    'priority',
                    'status',
                    'asset_id',
                    'assigned_personnel_id',
                    'created_at',
                    'scheduled_at',
                    'completion_at',
                    'work_performed',
                    'field_notes',
                ])
                ->with([
                    'asset:id,asset_number',
                    'assignedPersonnel:id,name',
                    'materialUsages:id,work_order_id,material_id,quantity',
                    'materialUsages.material:id,name,unit',
                ])
                ->orderBy('created_at')
                ->lazy(200);

            foreach ($workOrders as $workOrder) {
                $materialsUsed = $workOrder->materialUsages
                    ->map(fn (WorkOrderMaterialUsage $usage): string => $usage->quantity.' '.$usage->material->unit.' '.$usage->material->name)
                    ->implode('; ');

                fputcsv($output, [
                    $this->spreadsheetSafe($workOrder->work_order_number),
                    $this->spreadsheetSafe($workOrder->title),
                    $this->spreadsheetSafe($workOrder->category),
                    $workOrder->priority,
                    $workOrder->status,
                    $this->spreadsheetSafe($workOrder->asset?->asset_number ?? ''),
                    $this->spreadsheetSafe($workOrder->assignedPersonnel?->name ?? ''),
                    $workOrder->created_at?->toDateString(),
                    $workOrder->scheduled_at?->toDateTimeString(),
                    $workOrder->completion_at?->toDateTimeString(),
                    $this->spreadsheetSafe($workOrder->work_performed ?? ''),
                    $this->spreadsheetSafe($workOrder->field_notes ?? ''),
                    $this->spreadsheetSafe($materialsUsed),
                ], ',', '"', '', "\r\n");
            }

            fclose($output);
        }, 'engineering-work-orders.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{
     *     status?: string,
     *     priority?: string,
     *     category?: string,
     *     asset_id?: int,
     *     assigned_personnel_id?: int,
     *     from?: string,
     *     to?: string
     * }
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'status' => ['nullable', Rule::in(array_keys(WorkOrder::STATUSES))],
            'priority' => ['nullable', Rule::in(array_keys(WorkOrder::PRIORITIES))],
            'category' => ['nullable', 'string', 'max:255'],
            'asset_id' => ['nullable', 'integer', Rule::exists('assets', 'id')],
            'assigned_personnel_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', User::ROLE_FIELD_PERSONNEL),
            ],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
    }

    /**
     * @param  array{
     *     status?: string,
     *     priority?: string,
     *     category?: string,
     *     asset_id?: int,
     *     assigned_personnel_id?: int,
     *     from?: string,
     *     to?: string
     * }  $filters
     */
    private function filteredWorkOrders(array $filters): Builder
    {
        return WorkOrder::query()
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn (Builder $query, string $priority): Builder => $query->where('priority', $priority))
            ->when($filters['category'] ?? null, fn (Builder $query, string $category): Builder => $query->where('category', $category))
            ->when($filters['asset_id'] ?? null, fn (Builder $query, int $assetId): Builder => $query->where('asset_id', $assetId))
            ->when($filters['assigned_personnel_id'] ?? null, fn (Builder $query, int $personnelId): Builder => $query->where('assigned_personnel_id', $personnelId))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from): Builder => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to): Builder => $query->whereDate('created_at', '<=', $to));
    }

    private function spreadsheetSafe(string $value): string
    {
        return preg_match('/^\s*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }
}
