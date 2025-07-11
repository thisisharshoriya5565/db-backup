<?php

namespace Vendor\DbBackup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class RestoreManager
{
    public function restore(string $path = 'backups/', bool $truncate = true)
    {
        if (!Storage::exists($path)) {
            throw new \Exception("Backup file not found at $path");
        }

        $all_files = Storage::allFiles($path);

        collect($all_files)->each(function ($file_path) use ($truncate) {
            $records = Storage::json($file_path);
            $file_name = basename($file_path);              // 'failed_jobs.json'
            $table = pathinfo($file_name, PATHINFO_FILENAME);  // 'failed_jobs'

            if ($truncate) {
                DB::table($table)->truncate();
            }

            $collection = collect($records);
            if (!$collection->isEmpty()) {
                $collection->each(function ($record) use ($table) {
                    if (Schema::hasTable($table)) {
                        $record = $this->syncWithCurrentSchema($table, $record);
                        DB::table($table)->insert($record);
                    }
                });
            }
        });
    }

    protected function syncWithCurrentSchema($table, $record)
    {
        $columns = DB::getSchemaBuilder()->getColumnListing($table);

        // Filter out unknown keys and fill missing keys with null
        $synced = [];
        foreach ($columns as $column) {
            $synced[$column] = $record[$column] ?? null;
        }

        return $synced;
    }
}
