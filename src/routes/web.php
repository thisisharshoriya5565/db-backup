<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// ✅ Updated /backup Route with Modern HTML + Tailwind CS
Route::get('/backup', function () {
    $last_folder = app('dbbackup.backup')->backup();

    $all_folders = Storage::allDirectories("backups/");
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Backup Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900 font-sans p-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">📦 Database Backups</h1>
        <ul class="space-y-4">
HTML;

    collect($all_folders)->each(function ($folder) use ($last_folder, &$html) {
        $foldername = basename($folder);
        if ($folder !== $last_folder) {
            $url = url("backup/distroy/{$foldername}");
            $html .= <<<HTML
<li class="p-4 bg-white shadow-md rounded flex justify-between items-center">
    <span class="text-gray-700">📁 {$foldername}</span>
    <a href="{$url}" class="text-red-500 hover:underline">Destroy</a>
</li>
HTML;
        } else {
            $url = url("restore/{$foldername}");
            $html .= <<<HTML
<li class="p-4 bg-green-100 shadow-md rounded flex justify-between items-center">
    <span class="text-gray-700 font-semibold">✅ Latest: {$foldername}</span>
    <a href="{$url}" class="text-blue-600 hover:underline">Restore Now</a>
</li>
HTML;
        }
    });

    $html .= <<<HTML
        </ul>
    </div>
</body>
</html>
HTML;

    return $html;
});


// 🧹 Updated /backup/distroy/{path} and /restore/{path} Routes
Route::get('/restore/{path?}', function (string $path = "") {
    app('dbbackup.restore')->restore("backups/{$path}");

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Restore Complete</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-100 text-green-800 font-sans p-8">
    <div class="max-w-xl mx-auto text-center">
        <h1 class="text-2xl font-bold mb-4">✅ Restore Successful</h1>
        <p>Backup <strong>{$path}</strong> has been restored.</p>
        <a href="/backup" class="mt-6 inline-block text-blue-600 hover:underline">← Back to Backup List</a>
    </div>
</body>
</html>
HTML;
});

Route::get('/backup/distroy/{path?}', function (string $path = "") {
    $directory = "backups/{$path}";

    if ($path) {
        if (!Storage::exists($directory)) {
            throw new \Exception("Backup folder not found at {$directory}");
        }

        Storage::deleteDirectory($directory);

        return redirect('/backup');
    }

    return redirect('/');
});
