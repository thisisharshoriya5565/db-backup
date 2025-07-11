<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/backup', function () {
    $lastFolder = app('dbbackup.backup')->backup();
    // $folders = Storage::allDirectories("backups/");

    // return view('backup.index', [
    //     'lastFolder' => $lastFolder,
    //     'folders' => $folders,
    //     'title' => 'Database Backups'
    // ]);

    return redirect('/backup/list');
});

Route::get('/backup/list', function () {
    // $lastFolder = app('dbbackup.backup')->backup();
    $folders = Storage::allDirectories("backups/");
    $lastFolder = collect($folders)->last();

    return view('backup.index', [
        'lastFolder' => $lastFolder,
        'folders' => $folders,
        'title' => 'Database Backups'
    ]);
});

Route::get('/restore/{folder}', function ($folder) {
    app('dbbackup.restore')->restore("backups/{$folder}");

    return view('backup.restore_success', [
        'folder' => $folder,
        'title' => 'Restore Complete'
    ]);
});

Route::get('/backup/distroy/{folder}', function ($folder) {
    $directory = "backups/{$folder}";

    if (!Storage::exists($directory)) {
        abort(404, "Backup not found.");
    }

    Storage::deleteDirectory($directory);

    return view('backup.destroy_success', [
        'folder' => $folder,
        'title' => 'Delete Complete'
    ]);
});
