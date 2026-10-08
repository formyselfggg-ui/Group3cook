<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\RegistrationRequestController as AdminRegistrationRequestController;
use App\Http\Controllers\Auth\FieldLoginController;
use App\Http\Controllers\Auth\RegistrationRequestController;
use App\Http\Controllers\Field\WorkOrderDashboardController;
use App\Http\Controllers\Field\WorkOrderProgressController;
use App\Http\Controllers\ProfileSettingsController;
use App\Http\Controllers\Supervisor\SupervisorDashboardController;
use App\Http\Controllers\Supervisor\WorkOrderController;
use App\Http\Middleware\RequireAdministrator;
use App\Http\Middleware\RequireFieldPersonnel;
use App\Http\Middleware\RequireSupervisor;
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
    Route::post('/login', [FieldLoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
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

Route::middleware(['auth', RequireAdministrator::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard.view');
        Route::get('/registration-requests', [AdminRegistrationRequestController::class, 'index'])
            ->name('registration-requests');
        Route::post('/registration-requests/{registrationRequest}/approve', [AdminRegistrationRequestController::class, 'approve'])
            ->name('registration-requests.approve');
        Route::post('/registration-requests/{registrationRequest}/reject', [AdminRegistrationRequestController::class, 'reject'])
            ->name('registration-requests.reject');
        Route::get('/access-requests', [AdminRegistrationRequestController::class, 'index'])
            ->name('access-requests.index');
        Route::post('/access-requests/{registrationRequest}/approve', [AdminRegistrationRequestController::class, 'approve'])
            ->name('access-requests.approve');
        Route::post('/access-requests/{registrationRequest}/reject', [AdminRegistrationRequestController::class, 'reject'])
            ->name('access-requests.reject');
    });

Route::middleware(['auth', RequireSupervisor::class])
    ->prefix('supervisor')
    ->name('supervisor.')
    ->group(function (): void {
        Route::get('/', SupervisorDashboardController::class)->name('dashboard');
        Route::get('/dashboard', SupervisorDashboardController::class)->name('dashboard.view');
        Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
        Route::post('/work-orders/{workOrder}/review', [WorkOrderController::class, 'review'])
            ->name('work-orders.review');
        Route::post('/work-orders/{workOrder}/close', [WorkOrderController::class, 'close'])
            ->name('work-orders.close');
        Route::get('/work-orders/{workOrder}/photos/{photo}', [WorkOrderController::class, 'photo'])
            ->whereNumber('photo')
            ->name('work-orders.photos.show');
    });

Route::middleware(['auth', RequireFieldPersonnel::class])
    ->prefix('field')
    ->name('field.')
    ->group(function (): void {
        Route::get('/', WorkOrderDashboardController::class)->name('dashboard');
        Route::get('/dashboard', WorkOrderDashboardController::class)->name('dashboard.view');
        Route::post('/work-orders/{workOrder}/start', [WorkOrderProgressController::class, 'start'])
            ->name('work-orders.start');
        Route::post('/work-orders/{workOrder}/submit', [WorkOrderProgressController::class, 'submit'])
            ->name('work-orders.submit');
        Route::post('/work-orders/{workOrder}/materials', [WorkOrderProgressController::class, 'recordMaterial'])
            ->name('work-orders.materials.store');
    });
