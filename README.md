# 📦 Laravel DB Backup/Restore Package – Installation Guide
This package allows you to back up and restore your database by exporting each table as a separate .json file inside a timestamped folder.

# ✅ 1. Update Laravel Project composer.json
Add the package to the repositories section of your main Laravel app:

```json:
"repositories": [
  {
    "type": "path",
    "url": "packages/your-vendor/db-backup"
  }
]
```
This tells Composer to load the package from a local folder.

# ✅ 2. Install the Package via Composer
RUN:

```json:
composer require your-vendor/db-backup:dev-main
```

# ✅ 3. Publish the Config File (Optional)
```bash:
php artisan vendor:publish --provider="YourVendor\DbBackup\DbBackupServiceProvider"
```

This will create:
```arduino:
config/dbbackup.php
```
You can configure the default backup folder path there.

# ✅ 4. How to Use the Package
## ▶ Backup All Tables
```php:
$path = app('dbbackup.backup')->backup();
// Returns backup folder path like: storage/app/backups/2025-07-11_14-30-00/
```

## ▶ Backup Specific Tables
```php:
$tables = ['users', 'admins'];
app('dbbackup.backup')->backup($tables);
```

## ▶ Restore Backup from Folder
```php:
$folderPath = storage_path('app/backups/2025-07-11_14-30-00');
app('dbbackup.restore')->restore($folderPath);
```

🛡️ Smart Restore Behavior
Handles new or missing columns

Skips non-existing tables

Truncates tables before inserting (can be toggled)

Let me know if you want to:

Zip the backup folder

Schedule automatic backups via cron

Add Artisan CLI commands (php artisan db:backup)

I can generate that for you too.
