<?php

// config/dbbackup.php
return [
    // 'default_backup_path' => storage_path('app/db_backup.json'),

    /*
    |--------------------------------------------------------------------------
    | Middleware for Backup Routes
    |--------------------------------------------------------------------------
    |
    | Define middleware for the backup routes here.
    | Example: ['web', 'auth'] or ['web', 'guest'] or just ['web']
    |
    */
    'middleware' => ['web'], // default: only web, no auth
];
