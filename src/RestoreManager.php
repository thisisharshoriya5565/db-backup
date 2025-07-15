<?php

namespace Vendor\DbBackup;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Str;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Connection;

class RestoreManager
{
    public function restore(string $path = 'backups/', bool $truncate = true)
    {
        if (!Storage::exists($path)) {
            throw new \Exception("Backup path not found: $path");
        }

        $data = collect(Storage::allFiles($path))
            ->filter(fn($file) => str_ends_with($file, '.json'))
            ->mapWithKeys(fn($file) => [
                pathinfo($file, PATHINFO_FILENAME) => json_decode(Storage::get($file), true)
            ]);

        // remove first all tables
        $this->dropalltables();

        DB::beginTransaction();
        try {
            Schema::disableForeignKeyConstraints();

            foreach ($data as $table => $records) {
                if (!Schema::hasTable($table) || empty($records)) {
                    Log::warning("Skipping table [$table]: does not exist or has no records.");
                    continue;
                }

                $truncate && DB::table($table)->truncate();

                // Register ENUM type handling for Doctrine
                $conn = Schema::getConnection();
                if (!Type::hasType('enum')) {
                    Type::addType('enum', StringType::class);
                }
                $platform = $conn->getDoctrineConnection()->getDatabasePlatform();
                if (!$platform->hasDoctrineTypeMappingFor('enum')) {
                    $platform->registerDoctrineTypeMapping('enum', 'string');
                }

                $schemaManager = $conn->getDoctrineSchemaManager();
                $doctrineCols = $schemaManager->listTableColumns($table);

                $columnsMetadata = [];
                foreach (Schema::getColumnListing($table) as $colName) {
                    $columnsMetadata[$colName] = $doctrineCols[$colName] ?? null;
                }

                foreach (collect($records)->chunk(500) as $chunk) {
                    $prepared = $chunk->map(fn($row) => $this->prepareRow($row, $columnsMetadata));
                    DB::table($table)->insert($prepared->toArray());
                }

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

    protected function prepareRow(array $row, array $columnsMetadata): array
    {
        $data = [];

        foreach ($columnsMetadata as $colName => $colMeta) {
            $value = $row[$colName] ?? null;

            if (!$colMeta) {
                $data[$colName] = $value;
                continue;
            }

            $type = $colMeta->getType()->getName();
            $default = $colMeta->getDefault();
            $nullable = !$colMeta->getNotnull();

            // Fallback logic
            if ($value === null || $value === '') {
                if ($default !== null) {
                    $value = $default;
                } elseif (!$nullable) {
                    switch ($type) {
                        case 'boolean':
                        case 'tinyint':
                        case 'smallint':
                        case 'integer':
                            $value = 0;
                            break;
                        case 'string':
                        case 'text':
                            $value = '';
                            break;
                        case 'datetime':
                        case 'date':
                            $value = now()->format('Y-m-d H:i:s');
                            break;
                        default:
                            $value = null;
                    }
                }
            }

            // Casting per type
            switch ($type) {
                case 'boolean':
                    $converted = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    $data[$colName] = $converted ?? (!$nullable ? false : null);
                    break;

                case 'integer':
                case 'smallint':
                case 'tinyint':
                    $data[$colName] = is_numeric($value) ? (int) $value : ($nullable ? null : 0);
                    break;

                case 'float':
                case 'decimal':
                case 'double':
                    $data[$colName] = is_numeric($value) ? (float) $value : ($nullable ? null : 0.0);
                    break;

                case 'datetime':
                case 'date':
                    $data[$colName] = $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
                    break;

                default:
                    $data[$colName] = $value;
            }
        }

        return $data;
    }

    private function dropalltables()
    {
        Schema::disableForeignKeyConstraints();

        /** @var Connection $doctrineConn */
        $doctrineConn = DB::getDoctrineConnection();

        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = method_exists($doctrineConn, 'createSchemaManager')
            ? $doctrineConn->createSchemaManager()
            : $doctrineConn->getSchemaManager(); // For older Laravel/DBAL

        // Get all table names
        $tables = $schemaManager->listTableNames();

        foreach ($tables as $table) {
            DB::statement("DROP TABLE IF EXISTS `$table`");
        }

        // Re-run migrations
        Artisan::call('migrate', ['--force' => true]);

        Schema::enableForeignKeyConstraints();

        Log::info('All tables dropped and migrations re-run successfully.');
    }
}
