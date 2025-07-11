<?php

namespace Vendor\DbBackup;

use Illuminate\Support\ServiceProvider;

class DbBackupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/dbbackup.php' => config_path('dbbackup.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/dbbackup.php',
            'dbbackup'
        );
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        $this->app->singleton('dbbackup.backup', BackupManager::class);
        $this->app->singleton('dbbackup.restore', RestoreManager::class);
    }
}
