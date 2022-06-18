<?php

use App\Http\Controllers\LocaleController;
use App\Domains\Auth\Http\Controllers\Backend\Settings\UpdateFileSettingsController;

/*
 * Global Routes
 * Routes that are used between both frontend and backend.
 */

// Switch between the included languages
Route::get('lang/{lang}', [LocaleController::class, 'change'])->name('locale.change');
Route::group([
    'prefix' => 'bkg_task',
    'as' => 'bkg_task.'
], function () {
    Route::get('update_image_uploading_progress', [UpdateFileSettingsController::class, 'update_image_uploading_progress'])->name('update_image_uploading_progress');
    Route::get('finish_compressing', [UpdateFileSettingsController::class, 'finish_compressing'])->name('finish_compressing');
    Route::get('get_files_from_sftp', [UpdateFileSettingsController::class, 'get_files_from_sftp'])->name('get_files_from_sftp');
});
/*
 * Frontend Routes
 */
Route::group(['as' => 'frontend.'], function () {
    includeRouteFiles(__DIR__.'/frontend/');
});

/*
 * Backend Routes
 */
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function () {
    /*
     * These routes need 'view backend' permission
     * (good if you want to allow more than one group in the backend,
     * then limit the backend features by different roles or permissions)
     *
     * Note: Administrator has all permissions so you do not have to specify the administrator role everywhere.
     * These routes can not be hit if the password is expired
     */
    includeRouteFiles(__DIR__.'/backend/');
});
