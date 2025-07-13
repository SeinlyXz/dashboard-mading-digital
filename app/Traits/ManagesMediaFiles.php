<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

trait ManagesMediaFiles
{
    /**
     * Hapus file dari storage
     */
    public function deleteMediaFile(string $path, string $disk = 'public'): bool
    {
        try {
            $diskInstance = Storage::disk($disk);
            
            if ($diskInstance->exists($path)) {
                $result = $diskInstance->delete($path);
                Log::info("File berhasil dihapus dari storage: {$path}");
                return $result;
            } else {
                Log::warning("File tidak ditemukan di storage: {$path}");
                return true; // Consider as success since file doesn't exist
            }
        } catch (\Exception $e) {
            Log::error("Gagal menghapus file dari storage: {$path}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek apakah file exists di storage
     */
    public function mediaFileExists(string $path, string $disk = 'public'): bool
    {
        try {
            return Storage::disk($disk)->exists($path);
        } catch (\Exception $e) {
            Log::error("Error checking file existence: {$path}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get file size dari storage
     */
    public function getMediaFileSize(string $path, string $disk = 'public'): int
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->size($path);
            }
            return 0;
        } catch (\Exception $e) {
            Log::error("Error getting file size: {$path}. Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get file URL dari storage
     */
    public function getMediaFileUrl(string $path, string $disk = 'public'): ?string
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->url($path);
            }
            return null;
        } catch (\Exception $e) {
            Log::error("Error getting file URL: {$path}. Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Move file dari satu lokasi ke lokasi lain
     */
    public function moveMediaFile(string $fromPath, string $toPath, string $disk = 'public'): bool
    {
        try {
            $diskInstance = Storage::disk($disk);
            
            if (!$diskInstance->exists($fromPath)) {
                Log::error("Source file tidak ditemukan: {$fromPath}");
                return false;
            }

            $result = $diskInstance->move($fromPath, $toPath);
            
            if ($result) {
                Log::info("File berhasil dipindahkan dari {$fromPath} ke {$toPath}");
            } else {
                Log::error("Gagal memindahkan file dari {$fromPath} ke {$toPath}");
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error("Error moving file dari {$fromPath} ke {$toPath}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Copy file dari satu lokasi ke lokasi lain
     */
    public function copyMediaFile(string $fromPath, string $toPath, string $disk = 'public'): bool
    {
        try {
            $diskInstance = Storage::disk($disk);
            
            if (!$diskInstance->exists($fromPath)) {
                Log::error("Source file tidak ditemukan: {$fromPath}");
                return false;
            }

            $result = $diskInstance->copy($fromPath, $toPath);
            
            if ($result) {
                Log::info("File berhasil dicopy dari {$fromPath} ke {$toPath}");
            } else {
                Log::error("Gagal meng-copy file dari {$fromPath} ke {$toPath}");
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error("Error copying file dari {$fromPath} ke {$toPath}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format file size menjadi human readable
     */
    public function formatFileSize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get MIME type dari file extension
     */
    public function getMimeTypeFromExtension(string $extension): string
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
        ];

        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }

    /**
     * Determine media type dari MIME type
     */
    public function getMediaTypeFromMime(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        } elseif (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain'
        ])) {
            return 'document';
        } else {
            return 'other';
        }
    }
}
