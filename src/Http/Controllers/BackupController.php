<?php

namespace Vendor\DbBackup\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $lastFolder = app('dbbackup.backup')->backup();
        $url = config('app.url') . '/backup/list';

        return redirect($url);
    }

    public function list()
    {
        // $lastFolder = app('dbbackup.backup')->backup();
        $folders = Storage::allDirectories("backups/");
        $lastFolder = collect($folders)->last();

        return view('db-backup::backup.index', [
            'lastFolder' => $lastFolder,
            'folders' => $folders,
            'title' => 'Database Backups'
        ]);
    }

    public function restore(string $folder = '')
    {
        app('dbbackup.restore')->restore("backups/{$folder}");

        return view('db-backup::backup.restore_success', [
            'folder' => $folder,
            'title' => 'Restore Complete'
        ]);
    }

    public function distroy(string $folder = '')
    {
        $directory = "backups/{$folder}";

        if (!Storage::exists($directory)) {
            abort(404, "Backup not found.");
        }

        Storage::deleteDirectory($directory);

        return view('db-backup::backup.destroy_success', [
            'folder' => $folder,
            'title' => 'Delete Complete'
        ]);
    }
}
