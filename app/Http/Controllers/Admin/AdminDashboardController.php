<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RegistrationRequest;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $userMetrics = User::query()
            ->select('role')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN is_active = ? THEN 1 END) as active_total', [true])
            ->selectRaw('COUNT(CASE WHEN is_active = ? THEN 1 END) as inactive_total', [false])
            ->groupBy('role')
            ->get();
        $usersByRole = $userMetrics->pluck('total', 'role');
        $workOrderMetrics = WorkOrder::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'COUNT(CASE WHEN priority = ? AND status != ? THEN 1 END) as urgent_total',
                [WorkOrder::PRIORITY_URGENT, WorkOrder::STATUS_COMPLETED],
            )
            ->groupBy('status')
            ->get();
        $workOrdersByStatus = $workOrderMetrics->pluck('total', 'status');

        return view('admin.dashboard', [
            'totalUsers' => (int) $userMetrics->sum('total'),
            'activeUsers' => (int) $userMetrics->sum('active_total'),
            'inactiveUsers' => (int) $userMetrics->sum('inactive_total'),
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
            'totalWorkOrders' => (int) $workOrderMetrics->sum('total'),
            'urgentWorkOrders' => (int) $workOrderMetrics->sum('urgent_total'),
            'recentActivity' => ActivityLog::query()
                ->with('user:id,name')
                ->latest('created_at')
                ->limit(8)
                ->get(),
        ]);
    }
}
