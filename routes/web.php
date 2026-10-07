<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationApprovalController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.access-requests.index');
        }

        return view('dashboard', [
            'roleLabel' => User::ROLES[auth()->user()->role] ?? 'Team Member',
        ]);
    })->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function (): void {
        Route::get('/access-requests', [RegistrationApprovalController::class, 'index'])
            ->name('access-requests.index');
        Route::post('/access-requests/{registrationRequest}/approve', [RegistrationApprovalController::class, 'approve'])
            ->name('access-requests.approve');
        Route::post('/access-requests/{registrationRequest}/reject', [RegistrationApprovalController::class, 'reject'])
            ->name('access-requests.reject');
    });
});