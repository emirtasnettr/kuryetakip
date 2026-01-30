<?php

use App\Http\Controllers\Panel\AuthController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\ShiftController;
use App\Http\Controllers\Panel\CourierController;
use App\Http\Controllers\Panel\UserController;
use App\Http\Controllers\Courier\MobileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Operasyon paneli ve kurye mobil web arayüzü route'ları.
|
*/

// ==================== ANA SAYFA ====================

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isCourier()) {
            return redirect()->route('courier.home');
        }
        return redirect()->route('panel.dashboard');
    }
    return redirect()->route('panel.login');
});

// ==================== PANEL (OPERASYON) ====================

Route::prefix('panel')->name('panel.')->group(function () {

    // Auth (Misafir)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    });

    // Protected
    Route::middleware(['auth', 'can:access-panel'])->group(function () {
        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Vardiyalar
        Route::prefix('shifts')->name('shifts.')->group(function () {
            Route::get('/', [ShiftController::class, 'index'])->name('index');
            Route::get('/active', [ShiftController::class, 'active'])->name('active');
            Route::get('/reports', [ShiftController::class, 'reports'])->name('reports');
            Route::get('/{shift}', [ShiftController::class, 'show'])->name('show');
            Route::post('/{shift}/cancel', [ShiftController::class, 'cancel'])->name('cancel');
            Route::post('/{shift}/note', [ShiftController::class, 'addNote'])->name('add-note');
        });

        // Kuryeler
        Route::prefix('couriers')->name('couriers.')->group(function () {
            Route::get('/', [CourierController::class, 'index'])->name('index');
            Route::get('/create', [CourierController::class, 'create'])->name('create');
            Route::post('/', [CourierController::class, 'store'])->name('store');
            Route::get('/{courier}', [CourierController::class, 'show'])->name('show');
            Route::get('/{courier}/edit', [CourierController::class, 'edit'])->name('edit');
            Route::put('/{courier}', [CourierController::class, 'update'])->name('update');
            Route::post('/{courier}/toggle-active', [CourierController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/{courier}/reset-password', [CourierController::class, 'resetPassword'])->name('reset-password');
        });

        // Kullanıcılar (Sistem Yöneticisi)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        });
    });

});

// ==================== KURYE MOBİL WEB ====================

Route::prefix('courier')->name('courier.')->group(function () {

    // Auth (Misafir)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [MobileController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [MobileController::class, 'login'])->name('login.submit');
    });

    // Protected
    Route::middleware(['auth', 'can:access-mobile'])->group(function () {
        Route::post('/logout', [MobileController::class, 'logout'])->name('logout');
        Route::get('/', [MobileController::class, 'home'])->name('home');
        Route::get('/shift/start', [MobileController::class, 'showStartForm'])->name('shift.start');
        Route::post('/shift/start', [MobileController::class, 'startShift'])->name('shift.start.submit');
        Route::get('/shift/end', [MobileController::class, 'showEndForm'])->name('shift.end');
        Route::post('/shift/end', [MobileController::class, 'endShift'])->name('shift.end.submit');
        Route::get('/shifts', [MobileController::class, 'shiftHistory'])->name('shifts');
        Route::get('/profile', [MobileController::class, 'profile'])->name('profile');
    });

});
