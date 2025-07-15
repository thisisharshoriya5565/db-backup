<?php

namespace Vendor\DbBackup;

// use Carbon\Carbon;
// use Error;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Str;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

class BackupManager
{
    public function backup(array $arr_tables = [], string $path = 'backups/')
    {
        // Create backup directory if it doesn't exist
        if (!Storage::exists($path)) {
            Storage::makeDirectory($path);
        }

        // Use Doctrine DBAL to get all table names
        /** @var Connection $doctrineConn */
        $doctrineConn = DB::getDoctrineConnection();

        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = method_exists($doctrineConn, 'createSchemaManager')
            ? $doctrineConn->createSchemaManager()
            : $doctrineConn->getSchemaManager(); // Fallback for older DBAL versions

        $allTables = $schemaManager->listTableNames();

        // Determine which tables to back up
        $tables = empty($arr_tables) ? $allTables : array_intersect($arr_tables, $allTables);

        // Prepare path with timestamp
        $date_time = now()->format('Y-m-d-H-i-s');
        $backupFolder = rtrim($path, '/') . "/db-{$date_time}";

        // Loop through each table and store JSON data
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)->get()->toArray();

            $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $file = "{$backupFolder}/{$table}.json";

            Storage::put($file, $json);
        }

        return $backupFolder;
    }
}
