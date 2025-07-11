# 📦 Laravel DB Backup/Restore Package
Easily backup and restore your Laravel database using simple commands or service calls. Each table is saved as a .json file inside a timestamped folder, making the backup format readable, versionable, and portable.

### 🚀 Features
- 🔄 Backup all or selected tables
- 💾 Each table saved as .json file
- 🧠 Smart restore handles schema differences
- 📁 Backups stored in storage/app/backups
-🔌 Simple API for custom UIs, routes, or Artisan commands

### 🛠️ Installation
#### 1. Add VCS Repository
Add this to your Laravel project’s composer.json:

```json:
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/thisisharshoriya5565/db-backup"
  }
]
```
This tells Composer to load the package from a local folder.

### 2. Install via Composer
RUN:

```json:
composer require thisisharshoriya5565/db-backup:dev-main
```

### 3. (Optional) Publish the Config File
```bash:
php artisan vendor:publish --provider="YourVendor\DbBackup\DbBackupServiceProvider"
```

This creates the config file:
```arduino:
config/dbbackup.php
```
You can configure the default backup directory here.

## ⚙️ Usage
### ▶ Backup All Tables
```php:
$path = app('dbbackup.backup')->backup();
// Example: storage/app/backups/2025-07-11_14-30-00/
```

### ▶ Backup Specific Tables
```php:
$tables = ['users', 'orders'];
$path = app('dbbackup.backup')->backup($tables);
```

### ▶ Restore From a Backup
```php:
$folderPath = storage_path('app/backups/2025-07-11_14-30-00');
app('dbbackup.restore')->restore($folderPath);
```

### 🧭 Route Guide
| URL                        | Action              | Description                                      |
| -------------------------- | ------------------- | ------------------------------------------------ |
| `/backup`                  | Create Backup       | Triggers a backup and redirects to the list view |
| `/backup/list`             | View Backup List    | Shows all available backup folders               |
| `/restore/{folder}`        | Restore from Backup | Restores the selected backup into the database   |
| `/backup/distroy/{folder}` | Delete Backup       | Deletes the specified backup folder              |

📝 All backups are stored under: storage/app/backups

### 🧠 Smart Restore Behavior
- ✅ Skips tables that don’t exist in the DB
- ✅ Creates missing columns automatically
- ✅ Truncates tables before restore (can be configured)
- ❌ Does not delete extra columns or drop tables

### 🔧 Custom Integration Options
#### You can build your own:

- 🌐 Web interface for managing backups
- 🖥️ Artisan CLI commands (e.g. php artisan db:backup)
- ⏰ Scheduled backups using Laravel Task Scheduler
- 📦 ZIP compression of backup folders
- 📧 Email or upload to cloud storage
  
  Let me know if you'd like a pre-built version of any of these.

### 🧪 Example: Listing Backup Folders
```php
$folders = Storage::allDirectories('backups');
```

### 🧪 Example: Deleting a Backup
```php
Storage::deleteDirectory('backups/2025-07-11_14-30-00');
```

### 🧩 Compatibility
- Laravel 8, 9, 10+
- Works with MySQL, PostgreSQL, SQLite
- Uses Laravel's Storage facade

### 📄 License
MIT – free for personal and commercial use.

### 🤝 Contributions & Support
PRs are welcome. If you'd like to help improve this package or need a custom feature, feel free to open an issue or fork the repo.

### 👨‍💻 Developer Support
If you need help implementing this or want to connect with the developer: 
👉 Visit: https://b2code.in/
