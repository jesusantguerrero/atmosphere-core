<?php

use Illuminate\Support\Facades\Route;
use Freesgen\Atmosphere\Domains\Admin\Http\Controllers\AdminController;
use Freesgen\Atmosphere\Domains\Admin\Http\Controllers\AdminTeamController;
use Freesgen\Atmosphere\Domains\Admin\Http\Controllers\AdminUserController;
use Freesgen\Atmosphere\Domains\Admin\Http\Controllers\AdminBackupController;
use Freesgen\Atmosphere\Domains\Admin\Http\Controllers\AdminBillingController;
use Freesgen\Atmosphere\Domains\Admin\Http\Controllers\AdminSettingController;
use Freesgen\Atmosphere\Domains\Admin\Http\Controllers\AdminSubscriptionController;


Route::middleware( config('jetstream.middleware', ['web']))->prefix('/admin')->name('admin.')->group(function () {
    Route::impersonate();
    Route::get('/', AdminController::class);
    Route::resource('/teams', AdminTeamController::class);
    Route::post('/teams/{team}/approve', [AdminTeamController::class, 'approve'])->name('teams.approve');
    Route::post('/impersonate-user/{userId}', [AdminController::class, 'impersonateUser']);

    Route::post('/teams/{team}/subscribe/{planId}', [AdminBillingController::class, 'subscribe'])->name('billing.subscribe');

    Route::resource('/users', AdminUserController::class);
    Route::resource('/subscriptions', AdminSubscriptionController::class);

    Route::get('/commands', [AdminController::class, 'commandList']);
    Route::post('/commands', [AdminController::class, 'runCommand']);

    Route::resource('/settings', AdminSettingController::class);
    Route::get('/settings/{name}', [AdminSettingController::class, 'index']);
    Route::post('/settings/mail', [AdminSettingController::class, 'storeMailConfig']);

    Route::get('/backups', [AdminBackupController::class, 'list']);
    Route::post('/backups', [AdminBackupController::class, 'generate']);
    Route::post('/send-backup', [AdminBackupController::class, 'sendFile']);
    Route::delete('/delete-backup', [AdminBackupController::class, 'removeFile']);
    Route::get('/backups/download', [AdminBackupController::class, 'downloadFile']);
});
