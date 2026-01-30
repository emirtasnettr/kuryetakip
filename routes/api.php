<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\DistrictController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Kurye mobil uygulaması için RESTful API endpoint'leri.
| Tüm route'lar /api prefix'i ile başlar.
|
*/

// ==================== PUBLIC ROUTES ====================

Route::prefix('v1')->group(function () {

    // Authentication
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');

    // ==================== PROTECTED ROUTES ====================

    Route::middleware('auth:sanctum')->group(function () {

        // === Auth ===
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
            Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('api.auth.logout-all');
            Route::put('/profile', [AuthController::class, 'updateProfile'])->name('api.auth.profile');
            Route::put('/password', [AuthController::class, 'changePassword'])->name('api.auth.password');
        });

        // === Shifts (Vardiyalar) ===
        Route::prefix('shifts')->group(function () {
            // Liste ve istatistikler
            Route::get('/', [ShiftController::class, 'index'])->name('api.shifts.index');
            Route::get('/active', [ShiftController::class, 'active'])->name('api.shifts.active');
            Route::get('/statistics', [ShiftController::class, 'statistics'])->name('api.shifts.statistics');

            // Vardiya işlemleri
            Route::post('/start', [ShiftController::class, 'start'])->name('api.shifts.start');
            Route::post('/{shift}/end', [ShiftController::class, 'end'])->name('api.shifts.end');
            Route::get('/{shift}', [ShiftController::class, 'show'])->name('api.shifts.show');

            // Fotoğraf yükleme
            Route::post('/{shift}/photos', [ShiftController::class, 'uploadPhoto'])->name('api.shifts.photos');
        });

        // === Districts (İlçeler) ===
        Route::prefix('districts')->group(function () {
            Route::get('/', [DistrictController::class, 'index'])->name('api.districts.index');
            Route::get('/{district}', [DistrictController::class, 'show'])->name('api.districts.show');
        });

    });

});

/*
|--------------------------------------------------------------------------
| API Endpoint Özeti
|--------------------------------------------------------------------------
|
| POST   /api/v1/login                  - Giriş yap
|
| GET    /api/v1/auth/me                - Kullanıcı bilgileri
| POST   /api/v1/auth/logout            - Çıkış yap
| POST   /api/v1/auth/logout-all        - Tüm oturumlardan çık
| PUT    /api/v1/auth/profile           - Profil güncelle
| PUT    /api/v1/auth/password          - Şifre değiştir
|
| GET    /api/v1/shifts                 - Vardiya listesi
| GET    /api/v1/shifts/active          - Aktif vardiya
| GET    /api/v1/shifts/statistics      - İstatistikler
| POST   /api/v1/shifts/start           - Vardiya başlat
| POST   /api/v1/shifts/{id}/end        - Vardiya bitir
| GET    /api/v1/shifts/{id}            - Vardiya detayı
| POST   /api/v1/shifts/{id}/photos     - Fotoğraf yükle
|
| GET    /api/v1/districts              - İlçe listesi
| GET    /api/v1/districts/{id}         - İlçe detayı
|
*/
