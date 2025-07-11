<?php

namespace Vendor\DbBackup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RestoreManager
{
    public function restore(string $path = 'storage/app/db_backup.json', bool $truncate = true)
    {
        if (!File::exists($path)) {
            throw new \Exception("Backup file not found at $path");
        }

        $data = json_decode(File::get($path), true);

        foreach ($data as $table => $records) {
            if ($truncate) {
                DB::table($table)->truncate();
            }

            foreach ($records as $record) {
                $record = $this->syncWithCurrentSchema($table, $record);
                DB::table($table)->insert($record);
            }
        }
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
