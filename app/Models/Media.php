<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\ManagesMediaFiles;

class Media extends Model
{
    use SoftDeletes, ManagesMediaFiles;

    protected $table = 'medias';

    protected $fillable = [
        'uuid',
        'filename',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'disk',
        'path',
        'url',
        'type',
        'metadata',
        'description',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($media) {
            $media->uuid = (string) Str::uuid();
        });
    }

    // Accessor untuk full URL
    public function getFullUrlAttribute(): string
    {
        return $this->url ?? \Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Hapus file dari storage secara manual
     */
    public function deleteFileFromStorage(): bool
    {
        try {
            if ($this->path && \Storage::disk($this->disk)->exists($this->path)) {
                return \Storage::disk($this->disk)->delete($this->path);
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
        return $this->path && \Storage::disk($this->disk)->exists($this->path);
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
