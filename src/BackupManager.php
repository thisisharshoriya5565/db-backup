<?php

namespace Vendor\DbBackup;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class BackupManager
{
    public function backup(array $arr_tables = [], string $path = 'backups/')
    {
        if (!Storage::exists($path)) {
            Storage::makeDirectory($path);
        }

        $show_all_db_tables = DB::select('SHOW TABLES');
        $current_tables = array_map('current', $show_all_db_tables);
        $tables = collect($arr_tables)->isEmpty() ? $current_tables : $arr_tables;

        $date_time_format = 'Y-m-d-H-i-s';
        $date_time = Carbon::now()->format($date_time_format);
        $filePath = rtrim($path, '/') . "/db-{$date_time}";

        collect($tables)->each(function ($table) use ($filePath) {
            if (Schema::hasTable($table)) {
                $result_db = DB::table($table)->get()->toArray();
                $contents = json_encode($result_db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $path = rtrim($filePath, '/') . "/{$table}.json";

                Storage::put($path, $contents);
            }
        });

        return $filePath;
    }
}
