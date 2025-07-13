# Media File Management - Laravel

Sistem manajemen file media dengan automatic cleanup ketika record dihapus dari database.

## Fitur yang Sudah Dibuat

### 1. MediaObserver
**File:** `app/Observers/MediaObserver.php`

Observer yang secara otomatis menghapus file dari storage ketika:
- Record media dihapus (soft delete) 
- Record media dihapus permanen (force delete)
- Record media diupdate dan path file berubah

### 2. Custom Methods di Model Media
**File:** `app/Models/Media.php`

Method tambahan:
- `deleteFileFromStorage()` - Hapus file dari storage secara manual
- `fileExists()` - Cek apakah file masih ada di storage
- `deleteWithFile()` - Hapus record beserta file dari storage
- `forceDeleteWithFile()` - Force delete record beserta file dari storage

### 3. MediaController
**File:** `app/Http/Controllers/MediaController.php`

API endpoints untuk:
- `DELETE /api/media/{id}` - Hapus media beserta file
- `DELETE /api/media/{id}/force` - Force delete media beserta file  
- `POST /api/media/bulk-delete` - Batch delete multiple media
- `POST /api/media/cleanup-orphaned` - Cleanup file yang tidak terpakai
- `GET /api/media/check-status` - Cek status file untuk semua media

### 4. Artisan Commands

#### Command: `media:cleanup-orphaned`
```bash
# Preview file yang akan dihapus (tidak menghapus)
php artisan media:cleanup-orphaned --dry-run

# Hapus file yang tidak terpakai
php artisan media:cleanup-orphaned
```

#### Command: `media:check-status`
```bash
# Cek status semua file media
php artisan media:check-status

# Tampilkan hanya media dengan file yang hilang
php artisan media:check-status --missing-only
```

### 5. Trait ManagesMediaFiles
**File:** `app/Traits/ManagesMediaFiles.php`

Helper methods untuk operasi file:
- `deleteMediaFile()` - Hapus file dari storage
- `mediaFileExists()` - Cek file exists
- `getMediaFileSize()` - Get ukuran file
- `getMediaFileUrl()` - Get URL file
- `moveMediaFile()` - Pindahkan file
- `copyMediaFile()` - Copy file
- `formatFileSize()` - Format ukuran file
- `getMimeTypeFromExtension()` - Get MIME type dari extension
- `getMediaTypeFromMime()` - Tentukan tipe media dari MIME type

## Cara Penggunaan

### Automatic Cleanup (Recommended)
File akan otomatis terhapus ketika record media dihapus karena adanya MediaObserver.

```php
// File akan otomatis terhapus dari storage
$media = Media::find(1);
$media->delete(); // Soft delete + hapus file

// Atau force delete
$media->forceDelete(); // Force delete + hapus file
```

### Manual Cleanup
```php
$media = Media::find(1);

// Hapus hanya file (record tetap ada)
$media->deleteFileFromStorage();

// Hapus record beserta file
$media->deleteWithFile();

// Force delete record beserta file
$media->forceDeleteWithFile();

// Cek apakah file masih ada
if ($media->fileExists()) {
    echo "File tersedia";
}
```

### Maintenance Commands
```bash
# Cek file yang hilang dan cleanup orphaned files
php artisan media:check-status
php artisan media:cleanup-orphaned

# Atau tambahkan ke cron job untuk maintenance rutin
# Di crontab:
0 2 * * * cd /path/to/project && php artisan media:cleanup-orphaned > /dev/null 2>&1
```

## Setup di Server Production

1. **Tambahkan ke Cron Job** (untuk maintenance otomatis):
```bash
# Cleanup orphaned files setiap hari jam 2 pagi
0 2 * * * cd /path/to/project && php artisan media:cleanup-orphaned >> /var/log/media-cleanup.log 2>&1

# Check status files setiap minggu
0 3 * * 0 cd /path/to/project && php artisan media:check-status >> /var/log/media-status.log 2>&1
```

2. **Monitoring**: Periksa log Laravel untuk memastikan file berhasil dihapus:
```bash
tail -f storage/logs/laravel.log | grep "File berhasil dihapus"
```

## Error Handling

Semua operasi file dilengkapi dengan:
- Try-catch blocks
- Logging ke Laravel log
- Return values yang konsisten
- Database transactions untuk operasi batch

File akan tetap aman jika terjadi error pada database operations.
