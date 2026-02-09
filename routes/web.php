<?php

use Inertia\Inertia;
use App\Models\SmsMessage;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssesmentCtrl;
use App\Http\Controllers\SMS\SmsController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\IncidentWatermarkController;

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

    Route::resource('classifications', ClassificationController::class);
    Route::get('/processed-sms-get-reference', [SmsController::class, 'getReference'])->name('sms.reference');
    Route::get('/download-incident/{incident}', [IncidentController::class, 'download'])->name('incident.download');

    Route::resource('incidentwatermarks', IncidentWatermarkController::class);
});

Route::middleware('auth', 'verified')->group(function () {
    
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users/store', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('/users/update/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/delete/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/assessments', [AssesmentCtrl::class, 'index'])->name('assessments.index');


    
});

require __DIR__.'/settings.php';
