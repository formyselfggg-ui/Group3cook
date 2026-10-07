<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\RegistrationRequestController as AdminRegistrationRequestController;
use App\Http\Controllers\Auth\FieldLoginController;
use App\Http\Controllers\Auth\RegistrationRequestController;
use App\Http\Controllers\Field\WorkOrderDashboardController;
use App\Http\Controllers\Field\WorkOrderProgressController;
use App\Http\Controllers\ProfileSettingsController;
use App\Http\Controllers\Supervisor\SupervisorDashboardController;
use App\Http\Controllers\Supervisor\WorkOrderController as SupervisorWorkOrderController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $dashboardRoute = match (Auth::user()->role) {
        User::ROLE_ADMIN => 'admin.dashboard',
        User::ROLE_SUPERVISOR => 'supervisor.dashboard',
        default => 'field.dashboard',
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
});

Route::middleware(['auth', 'supervisor'])->prefix('supervisor')->name('supervisor.')->group(function (): void {
    Route::get('/dashboard', SupervisorDashboardController::class)->name('dashboard');
    Route::post('/work-orders', [SupervisorWorkOrderController::class, 'store'])->name('work-orders.store');
    Route::post('/work-orders/{workOrder}/review', [SupervisorWorkOrderController::class, 'review'])
        ->name('work-orders.review');
    Route::post('/work-orders/{workOrder}/close', [SupervisorWorkOrderController::class, 'close'])
        ->name('work-orders.close');
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
        ->name('field.work-orders.materials.store');
});
