<?php

use App\Http\Controllers\AssesmentCtrl;
use App\Http\Controllers\CashAdvanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\IncidentWatermarkController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SMS\SmsController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UserManagementController;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('auth/Login', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


Route::get('mapping', function () {
    return Inertia::render('IncidentMap');
})->middleware(['auth', 'verified'])->name('mapping');

Route::get('/mapping_incidents', [IncidentController::class, 'index']);
 
Route::middleware('auth', 'verified')->group(function () {
    
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/filter-address', [DashboardController::class, 'filterAddress'])->name('dashboard.filter.index');

    Route::get('/sms', [SmsController::class, 'index'])->name('sms');
    Route::post('/sms/fetch-message', [SmsController::class, 'store'])->name('sms.fetchMessages');
    Route::put('/sms/updateStatus', [SmsController::class, 'updateStatus'])->name('sms.update');

    Route::get('/processed-messages', [IncidentController::class, 'processedMessages'])->name('processed-messages.index');
    Route::get('/processed-messages/export', [IncidentController::class, 'export'])->name('processed-messages.export');

    Route::get('/survey-settings/fetch-cities', [SurveyController::class, 'fetchCities'])->name('surveys.fetchCities');
    Route::get('/survey-settings/fetch-barangays', [SurveyController::class, 'fetchBarangays']);
    Route::resource('survey-settings', SurveyController::class);
   
    Route::get('/processed-sms-get-reference', [SmsController::class, 'getReference'])->name('sms.reference');
    Route::get('/download-incident/{incident}', [IncidentController::class, 'download'])->name('incident.download');

    Route::resource('incidentwatermarks', IncidentWatermarkController::class);
 
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users/store', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('/users/update/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/delete/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/assessments', [AssesmentCtrl::class, 'index'])->name('assessments.index')->middleware('can:access ai assessment');


    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index') ->middleware('can:access role management');
    Route::post('/roles', [PermissionController::class, 'storeRole'])->name('roles.store');
    Route::put('/roles/{role}/permissions', [PermissionController::class, 'updatePermissions'])->name('roles.permissions.update');
    Route::post('/permissions', [PermissionController::class, 'storePermission'])->name('permissions.store');

    // Route para ipakita ang Dashboard/Form
    Route::get('/request-dashboard', function () {
        return Inertia::render('RequestDashboard/Index');
    })->name('request.dashboard');

    // Route para sa pag-submit ng Cash Advance
    Route::post('/cash-advances', [CashAdvanceController::class, 'store'])
        ->name('cash-advance.store');
        
    // Route para makita ang listahan ng mga requests (kung hiwalay na page)
    Route::get('/cash-advances', [CashAdvanceController::class, 'index'])
        ->name('cash-advance.index');

    Route::put('/cash-advances/{cashAdvance}', [CashAdvanceController::class, 'update'])->name('cash-advance.update');
    Route::put('/cash-advance/{cashAdvance}/status', [CashAdvanceController::class, 'updateStatus']);

    
});

require __DIR__.'/settings.php';
