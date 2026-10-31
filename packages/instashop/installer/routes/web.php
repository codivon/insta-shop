<?php

/*
|--------------------------------------------------------------------------
| Installer wizard routes (registered by instashop/installer)
|--------------------------------------------------------------------------
| The 'web' group is required explicitly because package routes do not
| inherit it automatically (session + CSRF are needed by the wizard).
| Access is gated globally by HandleInstallerAccess (bootstrap/app.php).
*/

use Illuminate\Support\Facades\Route;
use InstaShop\Installer\Http\Controllers\InstallerController;

Route::middleware('web')->prefix('install')->name('installer.')->group(function () {
    Route::get('/', [InstallerController::class, 'welcome'])->name('welcome');
    Route::get('/assets/{file}', [InstallerController::class, 'asset'])
        ->name('asset')->where('file', '[a-z0-9._-]+');
    Route::post('/database', [InstallerController::class, 'saveDatabase'])->name('database');
    Route::post('/migrate', [InstallerController::class, 'runMigrations'])->name('migrate');
    Route::post('/seed', [InstallerController::class, 'runSeeders'])->name('seed');
    Route::post('/finish', [InstallerController::class, 'finish'])->name('finish');
});
