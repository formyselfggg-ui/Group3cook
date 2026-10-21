<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\RegistrationRequestController as AdminRegistrationRequestController;
use App\Http\Controllers\Admin\RolePermissionController as AdminRolePermissionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\FieldLoginController;
use App\Http\Controllers\Auth\RegistrationRequestController;
use App\Http\Controllers\Field\WorkOrderDashboardController;
use App\Http\Controllers\Field\WorkOrderProgressController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Operations\AssetController as OperationsAssetController;
use App\Http\Controllers\Operations\EngineeringDashboardController;
use App\Http\Controllers\Operations\MaterialController as OperationsMaterialController;
use App\Http\Controllers\Operations\WorkOrderController as OperationsWorkOrderController;
use App\Http\Controllers\ProfileSettingsController;
use App\Http\Controllers\Supervisor\SupervisorDashboardController;
use App\Http\Controllers\Supervisor\WorkOrderController as SupervisorWorkOrderController;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $dashboardRoute = match (Auth::user()->role) {
        User::ROLE_ADMIN => 'admin.dashboard',
        User::ROLE_OPERATIONS => 'operations.dashboard',
        User::ROLE_SUPERVISOR => 'supervisor.dashboard',
        User::ROLE_FIELD_PERSONNEL => 'field.dashboard',
        default => 'login',
    };

    return redirect()->route($dashboardRoute);
})->name('home');

Route::get('/login', [FieldLoginController::class, 'create'])->name('login');

Route::middleware('guest')->group(function (): void {
    Route::post('/login', [FieldLoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/register', [RegistrationRequestController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationRequestController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [FieldLoginController::class, 'destroy'])->name('logout');
    Route::get('/profile', [ProfileSettingsController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileSettingsController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileSettingsController::class, 'updatePassword'])
        ->name('profile.password.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');

    Route::get('/admin/registration-requests', [AdminRegistrationRequestController::class, 'index'])
        ->name('admin.registration-requests');
    Route::post('/admin/registration-requests/{registrationRequest}/approve', [
        AdminRegistrationRequestController::class,
        'approve',
    ])->name('admin.registration-requests.approve');
    Route::post('/admin/registration-requests/{registrationRequest}/reject', [
        AdminRegistrationRequestController::class,
        'reject',
    ])->name('admin.registration-requests.reject');

    Route::get('/admin/access-requests', [AdminRegistrationRequestController::class, 'index'])
        ->name('admin.access-requests.index');
    Route::post('/admin/access-requests/{registrationRequest}/approve', [
        AdminRegistrationRequestController::class,
        'approve',
    ])->name('admin.access-requests.approve');
    Route::post('/admin/access-requests/{registrationRequest}/reject', [
        AdminRegistrationRequestController::class,
        'reject',
    ])->name('admin.access-requests.reject');

    Route::get('/admin/users', [AdminUserController::class, 'index'])
        ->middleware('permission:'.Permission::MANAGE_USERS)
        ->name('admin.users.index');
    Route::get('/admin/users/create', [AdminUserController::class, 'create'])
        ->middleware('permission:'.Permission::MANAGE_USERS)
        ->name('admin.users.create');
    Route::post('/admin/users', [AdminUserController::class, 'store'])
        ->middleware('permission:'.Permission::MANAGE_USERS)
        ->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])
        ->middleware('permission:'.Permission::MANAGE_USERS)
        ->name('admin.users.edit');
    Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])
        ->middleware('permission:'.Permission::MANAGE_USERS)
        ->name('admin.users.update');
    Route::post('/admin/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])
        ->middleware('permission:'.Permission::MANAGE_USERS)
        ->name('admin.users.toggle-status');

    Route::get('/admin/roles', [AdminRolePermissionController::class, 'index'])
        ->middleware('permission:'.Permission::MANAGE_PERMISSIONS)
        ->name('admin.roles.index');
    Route::post('/admin/roles/{role}/permissions', [
        AdminRolePermissionController::class,
        'updateRole',
    ])->middleware('permission:'.Permission::MANAGE_PERMISSIONS)->name('admin.roles.update');
    Route::post('/admin/roles/{role}/reset', [
        AdminRolePermissionController::class,
        'resetRole',
    ])->middleware('permission:'.Permission::MANAGE_PERMISSIONS)->name('admin.roles.reset');
    Route::post('/admin/users/{user}/permissions/{permission}', [
        AdminRolePermissionController::class,
        'toggleUserPermission',
    ])->middleware('permission:'.Permission::MANAGE_PERMISSIONS)->name('admin.roles.user-permission');
});

Route::middleware(['auth', 'operations'])->prefix('operations')->name('operations.')->group(function (): void {
    Route::get('/dashboard', EngineeringDashboardController::class)->name('dashboard');
    Route::get('/reports/export', [EngineeringDashboardController::class, 'export'])
        ->middleware('permission:'.Permission::VIEW_REPORTS)
        ->name('reports.export');
    Route::post('/work-orders', [OperationsWorkOrderController::class, 'store'])
        ->middleware('permission:'.Permission::CREATE_WORK_ORDERS)
        ->name('work-orders.store');
    Route::post('/assets', [OperationsAssetController::class, 'store'])
        ->middleware('permission:'.Permission::MANAGE_ASSETS)
        ->name('assets.store');
    Route::patch('/assets/{asset}', [OperationsAssetController::class, 'update'])
        ->middleware('permission:'.Permission::MANAGE_ASSETS)
        ->name('assets.update');
    Route::post('/materials', [OperationsMaterialController::class, 'store'])
        ->middleware('permission:'.Permission::MANAGE_MATERIALS)
        ->name('materials.store');
    Route::patch('/materials/{material}', [OperationsMaterialController::class, 'update'])
        ->middleware('permission:'.Permission::MANAGE_MATERIALS)
        ->name('materials.update');
});

Route::middleware(['auth', 'supervisor'])->prefix('supervisor')->name('supervisor.')->group(function (): void {
    Route::get('/dashboard', SupervisorDashboardController::class)->name('dashboard');
    Route::post('/work-orders', [SupervisorWorkOrderController::class, 'store'])
        ->middleware('permission:'.Permission::CREATE_WORK_ORDERS)
        ->name('work-orders.store');
    Route::post('/work-orders/{workOrder}/assign', [SupervisorWorkOrderController::class, 'assign'])
        ->middleware('permission:'.Permission::ASSIGN_WORK_ORDERS)
        ->name('work-orders.assign');
    Route::post('/work-orders/{workOrder}/review', [SupervisorWorkOrderController::class, 'review'])
        ->middleware('permission:'.Permission::REVIEW_WORK_ORDERS)
        ->name('work-orders.review');
    Route::post('/work-orders/{workOrder}/close', [SupervisorWorkOrderController::class, 'close'])
        ->middleware('permission:'.Permission::CLOSE_WORK_ORDERS)
        ->name('work-orders.close');
    Route::post('/work-orders/{workOrder}/priority', [SupervisorWorkOrderController::class, 'priority'])
        ->middleware('permission:'.Permission::UPDATE_PRIORITY)
        ->name('work-orders.priority');

    Route::delete('/work-orders/{workOrder}', [SupervisorWorkOrderController::class, 'destroy'])
        ->middleware('permission:'.Permission::DELETE_WORK_ORDERS)
        ->name('work-orders.destroy');
    Route::get('/work-orders/{workOrder}/photos/{photo}', [SupervisorWorkOrderController::class, 'photo'])
        ->whereNumber('photo')
        ->name('work-orders.photos.show');
});

Route::middleware(['auth', 'field'])->group(function (): void {
    Route::get('/field/dashboard', WorkOrderDashboardController::class)->name('field.dashboard');
    Route::post('/field/work-orders/{workOrder}/start', [WorkOrderProgressController::class, 'start'])
        ->name('field.work-orders.start');
    Route::post('/field/work-orders/{workOrder}/submit', [WorkOrderProgressController::class, 'submit'])
        ->name('field.work-orders.submit');
    Route::post('/field/work-orders/{workOrder}/materials', [WorkOrderProgressController::class, 'recordMaterial'])
        ->middleware('permission:'.Permission::RECORD_MATERIALS)
        ->name('field.work-orders.materials.store');
});
