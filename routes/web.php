<?php

use App\Http\Controllers\Panel\AuthController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\ShiftController;
use App\Http\Controllers\Panel\ScheduledShiftController;
use App\Http\Controllers\Panel\CourierController;
use App\Http\Controllers\Panel\UserController;
use App\Http\Controllers\Panel\RegionController;
use App\Http\Controllers\Panel\RegionReportController;
use App\Http\Controllers\Panel\SettlementController;
use App\Http\Controllers\Courier\MobileController;
use App\Http\Controllers\Courier\ExpenseController as CourierExpenseController;
use App\Http\Controllers\Panel\ExpenseRequestController;
use App\Http\Controllers\Panel\MediaFileController;
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

// ==================== UYGULAMA İNDİRME ====================

Route::get('/uygulama', function () {
    return view('download');
})->name('download');

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
            Route::get('/export', [ShiftController::class, 'export'])->name('export');
            Route::get('/no-show', [ShiftController::class, 'noShow'])->name('no-show');
            Route::get('/no-show/export', [ShiftController::class, 'exportNoShow'])->name('no-show.export');
            Route::get('/reports', [ShiftController::class, 'reports'])->name('reports');
            Route::get('/reports/export', [ShiftController::class, 'exportReports'])->name('reports.export');
            Route::get('/{shift}', [ShiftController::class, 'show'])->name('show');
            Route::post('/{shift}/cancel', [ShiftController::class, 'cancel'])->name('cancel');
            Route::post('/{shift}/note', [ShiftController::class, 'addNote'])->name('add-note');
            Route::post('/{shift}/force-complete', [ShiftController::class, 'forceComplete'])->name('force-complete');
            Route::post('/{shift}/update-package-count', [ShiftController::class, 'updatePackageCount'])->name('update-package-count');
            Route::post('/{shift}/extend-hours', [ShiftController::class, 'extendHours'])->name('extend-hours');
        });

        // Kuryeler
        Route::prefix('couriers')->name('couriers.')->group(function () {
            Route::get('/', [CourierController::class, 'index'])->name('index');
            Route::get('/export', [CourierController::class, 'export'])->name('export');
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
            Route::get('/export', [UserController::class, 'export'])->name('export');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Hakediş
        Route::prefix('settlement')->name('settlement.')->group(function () {
            Route::get('/calculation', [SettlementController::class, 'calculation'])->name('calculation');
            Route::get('/calculation/export', [SettlementController::class, 'exportCalculation'])->name('calculation.export');
            Route::get('/photo-compliance-report/export', [SettlementController::class, 'exportPhotoComplianceReport'])->name('photo-compliance-report.export');
            Route::get('/deductions/export', [SettlementController::class, 'exportDeductions'])->name('deductions.export');
            Route::get('/settings', [SettlementController::class, 'settings'])->name('settings');
            Route::post('/settings', [SettlementController::class, 'updateSettings'])->name('settings.update');
            Route::get('/photo-review', [SettlementController::class, 'photoReview'])->name('photo-review');
            Route::post('/photo-review/{shift}/approve-bonus', [SettlementController::class, 'approvePhotoBonus'])->name('photo-review.approve');
            Route::post('/photo-review/{shift}/no-bonus', [SettlementController::class, 'noPhotoBonus'])->name('photo-review.no-bonus');
            Route::post('/photo-review/{shift}/request-retry', [SettlementController::class, 'requestPhotoRetry'])->name('photo-review.request-retry');
            Route::get('/photo-compliance-report', [SettlementController::class, 'photoComplianceReport'])->name('photo-compliance-report');
            Route::post('/extra-bonus', [SettlementController::class, 'storeExtraBonus'])->name('extra-bonus.store');
            Route::get('/extra-bonuses', [SettlementController::class, 'listExtraBonuses'])->name('extra-bonuses.list');
            Route::get('/expense-deductions', [SettlementController::class, 'listExpenseDeductions'])->name('expense-deductions.list');
            Route::get('/deductions', [SettlementController::class, 'deductionsIndex'])->name('deductions.index');
            Route::post('/deductions', [SettlementController::class, 'deductionsStore'])->name('deductions.store');
        });

        // Masraf Yönetimi
        Route::prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', [ExpenseRequestController::class, 'index'])->name('index');
            Route::get('/export', [ExpenseRequestController::class, 'export'])->name('export');
            Route::get('/history', [ExpenseRequestController::class, 'history'])->name('history');
            Route::get('/history/export', [ExpenseRequestController::class, 'exportHistory'])->name('history.export');
            Route::get('/{expenseRequest}', [ExpenseRequestController::class, 'show'])->name('show');
            Route::post('/{expenseRequest}/approve', [ExpenseRequestController::class, 'approve'])->name('approve');
        });

        // Bölgeler
        Route::prefix('regions')->name('regions.')->group(function () {
            Route::get('/', [RegionController::class, 'index'])->name('index');
            Route::get('/report', [RegionReportController::class, 'index'])->name('report');
            Route::get('/create', [RegionController::class, 'create'])->name('create');
            Route::post('/', [RegionController::class, 'store'])->name('store');
            Route::get('/list', [RegionController::class, 'list'])->name('list'); // AJAX
            Route::get('/{region}', [RegionController::class, 'show'])->name('show');
            Route::get('/{region}/edit', [RegionController::class, 'edit'])->name('edit');
            Route::put('/{region}', [RegionController::class, 'update'])->name('update');
            Route::delete('/{region}', [RegionController::class, 'destroy'])->name('destroy');
        });

        // Ortam Dosyaları
        Route::prefix('media-files')->name('media-files.')->group(function () {
            Route::get('/', [MediaFileController::class, 'index'])->name('index');
            Route::post('/', [MediaFileController::class, 'store'])->name('store');
            Route::get('/{mediaFile}/view', [MediaFileController::class, 'show'])->name('show');
            Route::get('/shift-photo/{shiftPhoto}/view', [MediaFileController::class, 'showShiftPhoto'])->name('show-shift-photo');
            Route::get('/expense-receipt/{expenseRequest}/view', [MediaFileController::class, 'showExpenseReceipt'])->name('show-expense-receipt');
            Route::delete('/{mediaFile}', [MediaFileController::class, 'destroy'])->name('destroy');
            Route::delete('/shift-photo/{shiftPhoto}', [MediaFileController::class, 'destroyShiftPhoto'])->name('destroy-shift-photo');
            Route::delete('/expense-receipt/{expenseRequest}', [MediaFileController::class, 'destroyExpenseReceipt'])->name('destroy-expense-receipt');
            Route::post('/bulk-destroy', [MediaFileController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::post('/delete-older-than-31', [MediaFileController::class, 'deleteOlderThan31'])->name('delete-older-than-31');
            Route::post('/delete-older-than-46', [MediaFileController::class, 'deleteOlderThan46'])->name('delete-older-than-46');
            Route::post('/download-zip', [MediaFileController::class, 'downloadZip'])->name('download-zip');
        });

        // Vardiya Planlama
        Route::prefix('schedule')->name('schedule.')->group(function () {
            Route::get('/', [ScheduledShiftController::class, 'index'])->name('index');
            // Tek vardiya detay sayfası (HTML)
            Route::get('/shifts/{scheduledShift}/page', [ScheduledShiftController::class, 'showPage'])->name('shifts.page')->whereNumber('scheduledShift');
            
            // Takvim eventleri (AJAX)
            Route::get('/events', [ScheduledShiftController::class, 'events'])->name('events');
            
            // İle göre ilçeler (AJAX)
            Route::get('/districts', [ScheduledShiftController::class, 'districtsByCity'])->name('districts');
            
            // Gün görünümü
            Route::get('/day/{date}', [ScheduledShiftController::class, 'dayView'])->name('day');
            
            // Uygun kuryeler (AJAX)
            Route::get('/couriers', [ScheduledShiftController::class, 'availableCouriers'])->name('couriers');
            
            // Vardiya CRUD ({scheduledShift} sadece sayı; "undefined" vb. 404 döner)
            Route::post('/shifts', [ScheduledShiftController::class, 'store'])->name('shifts.store');
            Route::get('/shifts/{scheduledShift}', [ScheduledShiftController::class, 'show'])->name('shifts.show')->whereNumber('scheduledShift');
            Route::put('/shifts/{scheduledShift}', [ScheduledShiftController::class, 'update'])->name('shifts.update')->whereNumber('scheduledShift');
            Route::delete('/shifts/{scheduledShift}', [ScheduledShiftController::class, 'destroy'])->name('shifts.destroy')->whereNumber('scheduledShift');
            Route::patch('/shifts/{scheduledShift}/move', [ScheduledShiftController::class, 'move'])->name('shifts.move')->whereNumber('scheduledShift');
            Route::post('/shifts/{scheduledShift}/duplicate', [ScheduledShiftController::class, 'duplicate'])->name('shifts.duplicate')->whereNumber('scheduledShift');
            
            // Toplu oluşturma
            Route::post('/bulk-create', [ScheduledShiftController::class, 'bulkCreate'])->name('bulk-create');
            Route::post('/bulk-store', [ScheduledShiftController::class, 'bulkStore'])->name('bulk-store');

            // Excel ile vardiya yükleme
            Route::get('/shift-template', [ScheduledShiftController::class, 'shiftTemplateDownload'])->name('shift-template');
            Route::post('/shift-upload', [ScheduledShiftController::class, 'shiftUploadFromExcel'])->name('shift-upload');
            
            // Kurye atama
            Route::post('/shifts/{scheduledShift}/assign', [ScheduledShiftController::class, 'assignCourier'])->name('shifts.assign')->whereNumber('scheduledShift');
            Route::post('/shifts/{scheduledShift}/unassign', [ScheduledShiftController::class, 'unassignCourierById'])->name('shifts.unassign-by-id')->whereNumber('scheduledShift');
            Route::delete('/shifts/{scheduledShift}/assign/{assignment}', [ScheduledShiftController::class, 'unassignCourier'])->name('shifts.unassign')->whereNumber(['scheduledShift', 'assignment']);
            
            // Kurye değişikliği
            Route::post('/shifts/{scheduledShift}/end-courier', [ScheduledShiftController::class, 'endCourierEarly'])->name('shifts.end-courier')->whereNumber('scheduledShift');
            Route::post('/shifts/{scheduledShift}/assign-with-time', [ScheduledShiftController::class, 'assignCourierWithStartTime'])->name('shifts.assign-with-time')->whereNumber('scheduledShift');
        });

        // Vardiya Test - Günlük Operasyon Özeti
        Route::get('/shift-overview', [ScheduledShiftController::class, 'dailyOverview'])->name('shift-overview');
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
        Route::get('/assignments', [MobileController::class, 'assignments'])->name('assignments');
        Route::get('/profile', [MobileController::class, 'profile'])->name('profile');
        Route::get('/photo-retry', [MobileController::class, 'photoRetryList'])->name('photo-retry');
        Route::get('/photo-retry/{shift}', [MobileController::class, 'photoRetryUpload'])->name('photo-retry.upload');
        Route::post('/photo-retry/{shift}', [MobileController::class, 'photoRetryUploadSubmit'])->name('photo-retry.upload.submit');
        Route::get('/expenses', [CourierExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [CourierExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [CourierExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/settlement', [MobileController::class, 'settlement'])->name('settlement');
    });

});
