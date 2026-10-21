<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\Material;
use App\Models\RegistrationRequest;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\WorkOrderMetrics;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $usersByRole = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $workOrdersByStatus = WorkOrder::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $metrics = WorkOrderMetrics::for(WorkOrder::query());

        $lowStockMaterials = Material::query()
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('quantity_on_hand')
            ->limit(6)
            ->get();

        $crewPerformance = User::query()
            ->where('role', User::ROLE_FIELD_PERSONNEL)
            ->withCount([
                'assignedWorkOrders as assigned_total',
                'assignedWorkOrders as completed_total' => fn ($query) => $query
                    ->where('status', WorkOrder::STATUS_COMPLETED),
                'assignedWorkOrders as open_total' => fn ($query) => $query
                    ->where('status', '!=', WorkOrder::STATUS_COMPLETED),
            ])
            ->orderByDesc('completed_total')
            ->orderBy('name')
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'totalUsers' => User::query()->count(),
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'inactiveUsers' => User::query()->where('is_active', false)->count(),
            'pendingRegistrationRequests' => RegistrationRequest::query()
                ->where('status', RegistrationRequest::STATUS_PENDING)
                ->count(),
            'usersByRole' => $usersByRole,
            'workOrdersByStatus' => $workOrdersByStatus,
            'workOrderStatuses' => WorkOrder::STATUSES,
            'roleLabels' => [
                User::ROLE_ADMIN => 'Administrators',
                User::ROLE_OPERATIONS => 'Operations / Engineering',
                User::ROLE_SUPERVISOR => 'Supervisors',
                User::ROLE_FIELD_PERSONNEL => 'Field Personnel',
            ],
            'totalWorkOrders' => WorkOrder::query()->count(),
            'urgentWorkOrders' => WorkOrder::query()
                ->where('priority', WorkOrder::PRIORITY_URGENT)
                ->where('status', '!=', WorkOrder::STATUS_COMPLETED)
                ->count(),
            'recentActivity' => ActivityLog::query()
                ->with('user')
                ->latest('created_at')
                ->limit(8)
                ->get(),
            'metrics' => $metrics,
            'assetCount' => Asset::query()->count(),
            'materialCount' => Material::query()->count(),
            'lowStockCount' => Material::query()
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->count(),
            'lowStockMaterials' => $lowStockMaterials,
            'crewPerformance' => $crewPerformance,
        ]);
    }
}
