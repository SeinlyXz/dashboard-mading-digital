<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'medias';

    protected $fillable = [
        'path',
    ];

    protected $casts = [
        // No casts needed for simple path string
    ];

    // Accessor untuk full URL
    public function getFullUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    // Accessor untuk mendapatkan tipe file (image/video)
    public function getTypeAttribute(): string
    {
        $extension = $this->getExtensionAttribute();
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $videoExtensions = ['mp4', 'webm', 'avi', 'mov', 'mkv'];

        if (in_array($extension, $imageExtensions)) {
            return 'image';
        } elseif (in_array($extension, $videoExtensions)) {
            return 'video';
        }

        return 'unknown';
    }

    // Accessor untuk mendapatkan ekstensi file
    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    // Accessor untuk mendapatkan nama file
    public function getFilenameAttribute(): string
    {
        return basename($this->path);
    }

    // Accessor untuk mendapatkan mime type
    public function getMimeTypeAttribute(): string
    {
        $extension = $this->getExtensionAttribute();
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'avi' => 'video/avi',
            'mov' => 'video/quicktime',
            'mkv' => 'video/x-matroska',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Hapus file dari storage secara manual
     */
    public function deleteFileFromStorage(): bool
    {
        try {
            if ($this->path && \Storage::disk('public')->exists($this->path)) {
                return \Storage::disk('public')->delete($this->path);
            }
            return true;
        } catch (\Exception $e) {
            \Log::error("Gagal menghapus file: {$this->path}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek apakah file masih ada di storage
     */
    public function fileExists(): bool
    {
        return $this->path && \Storage::disk('public')->exists($this->path);
    }

    /**
     * Hapus record beserta file dari storage
     */
    public function deleteWithFile(): bool
    {
        // Hapus file terlebih dahulu
        $fileDeleted = $this->deleteFileFromStorage();
        
        // Hapus record dari database
        $recordDeleted = $this->delete();
        
        return $fileDeleted && $recordDeleted;
    }

    /**
     * Force delete record beserta file dari storage
     */
    public function forceDeleteWithFile(): bool
    {
        // Hapus file terlebih dahulu
        $fileDeleted = $this->deleteFileFromStorage();
        
        // Force delete record dari database
        $recordDeleted = $this->forceDelete();
        
        return $fileDeleted && $recordDeleted;
    }
}
