<?php

namespace Vendor\DbBackup;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RestoreManager
{
    public function restore(string $path = 'backups/', bool $truncate = true)
    {
        if (!Storage::exists($path)) {
            throw new \Exception("Backup path not found: $path");
        }

        // Get all JSON backup files
        $data = collect(Storage::allFiles($path))
            ->filter(fn($file) => str_ends_with($file, '.json'))
            ->mapWithKeys(function ($file) {
                $json = json_decode(Storage::get($file), true);
                $table = pathinfo($file, PATHINFO_FILENAME);
                return [$table => $json];
            });

        DB::beginTransaction();

        try {
            Schema::disableForeignKeyConstraints();

            foreach ($data as $table => $records) {
                if (!Schema::hasTable($table)) {
                    Log::warning("Skipping insert: Table [$table] does not exist.");
                    continue;
                }

                if (empty($records)) {
                    Log::info("No records to insert for table: $table");
                    continue;
                }

                if ($truncate) {
                    DB::table($table)->truncate();
                }

                collect($records)
                    ->chunk(500)
                    ->each(fn($chunk) => DB::table($table)->insert($chunk->toArray()));

                Log::info("Inserted " . count($records) . " records into [$table]");
            }

            Schema::enableForeignKeyConstraints();

            DB::commit();

            Log::info("Database restore completed successfully.");

            return response()->json(['message' => 'Database restore completed successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();

            Log::error("Restore aborted due to error: " . $e->getMessage());

            return response()->json(['message' => 'Database restore failed.', 'error' => $e->getMessage()], 500);
        }
    }

    // protected function syncWithCurrentSchema($table, $record)
    // {
    //     $columns = DB::getSchemaBuilder()->getColumnListing($table);

    //     // Filter out unknown keys and fill missing keys with null
    //     $synced = [];
    //     foreach ($columns as $column) {
    //         $synced[$column] = $record[$column] ?? null;
    //     }

    //     return $synced;
    // }
}
