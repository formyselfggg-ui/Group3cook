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
        $usersByRole = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('admin.dashboard', [
            'totalUsers' => User::query()->count(),
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'pendingRegistrationRequests' => RegistrationRequest::query()
                ->where('status', RegistrationRequest::STATUS_PENDING)
                ->count(),
            'usersByRole' => $usersByRole,
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
        ]);
    }
}
