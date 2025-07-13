<?php

namespace App\Observers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MediaObserver
{
    /**
     * Handle the Media "created" event.
     */
    public function created(Media $media): void
    {
        //
    }

    /**
     * Handle the Media "updated" event.
     */
    public function updated(Media $media): void
    {
        // Jika file path berubah, hapus file lama
        if ($media->isDirty('path') && $media->getOriginal('path')) {
            $this->deleteFileFromStorage($media->getOriginal('path'), $media->getOriginal('disk'));
        }
    }

    /**
     * Handle the Media "deleted" event.
     */
    public function deleted(Media $media): void
    {
        // Hapus file dari storage ketika record dihapus (soft delete)
        $this->deleteFileFromStorage($media->path, $media->disk);
    }

    /**
     * Handle the Media "restored" event.
     */
    public function restored(Media $media): void
    {
        //
    }

    /**
     * Handle the Media "force deleted" event.
     */
    public function forceDeleted(Media $media): void
    {
        // Hapus file dari storage ketika record dihapus permanen
        $this->deleteFileFromStorage($media->path, $media->disk);
    }

    /**
     * Hapus file dari storage
     */
    private function deleteFileFromStorage(?string $path, ?string $disk = 'public'): void
    {
        if (!$path) {
            return;
        }

        try {
            $diskInstance = Storage::disk($disk ?? 'public');
            
            if ($diskInstance->exists($path)) {
                $diskInstance->delete($path);
                Log::info("File berhasil dihapus dari storage: {$path}");
            } else {
                Log::warning("File tidak ditemukan di storage: {$path}");
            }
        } catch (\Exception $e) {
            Log::error("Gagal menghapus file dari storage: {$path}. Error: " . $e->getMessage());
        }
    }
}
