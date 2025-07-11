<?php

namespace Vendor\DbBackup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupManager
{
    public function backup(array $tables = null, string $path = 'storage/app/db_backup.json')
    {
        $data = [];

        $tables = DB::select('SHOW TABLES');
        $tableNames = array_map('current', $tables);
        dd($tableNames);
        // $tables = $tables ?? DB::connection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($tables as $table) {
            $data[$table] = DB::table($table)->get()->toArray();
        }

        File::put($path, json_encode($data, JSON_PRETTY_PRINT));
    }
}
