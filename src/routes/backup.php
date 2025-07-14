<?php

use Illuminate\Support\Facades\Route;
use Vendor\DbBackup\Http\Controllers\BackupController;

Route::middleware(config('config/dbbackup.php'))->prefix('backup')->name('backup.')->group(function () {
    Route::get('/', [BackupController::class, 'index'])->name('index');
    Route::get('/list', [BackupController::class, 'list'])->name('list');
    Route::get('/restore/{folder}', [BackupController::class, 'restore'])->name('restore');
    Route::get('/distroy/{folder}', [BackupController::class, 'distroy'])->name('distroy');
});
