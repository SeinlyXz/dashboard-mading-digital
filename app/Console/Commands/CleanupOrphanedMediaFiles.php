<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;

class CleanupOrphanedMediaFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:cleanup-orphaned {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup orphaned media files (files in storage without database records)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('🔍 Mencari file yang tidak terpakai...');
        
        try {
            $disk = Storage::disk('public');
            $uploadDirectory = 'uploads';
            
            if (!$disk->exists($uploadDirectory)) {
                $this->warn('⚠️  Direktori uploads tidak ditemukan');
                return Command::SUCCESS;
            }

            $allFiles = $disk->allFiles($uploadDirectory);
            $orphanedFiles = [];
            $totalSize = 0;
            
            $this->withProgressBar($allFiles, function ($file) use ($disk, &$orphanedFiles, &$totalSize) {
                // Cek apakah file ini ada di database
                $mediaExists = Media::where('path', $file)->exists();
                
                if (!$mediaExists) {
                    $fileSize = $disk->size($file);
                    $orphanedFiles[] = [
                        'path' => $file,
                        'size' => $fileSize
                    ];
                    $totalSize += $fileSize;
                }
            });

            $this->newLine(2);

            if (empty($orphanedFiles)) {
                $this->info('✅ Tidak ada file yang tidak terpakai ditemukan');
                return Command::SUCCESS;
            }

            $this->info(sprintf('📊 Ditemukan %d file yang tidak terpakai (Total ukuran: %s)', 
                count($orphanedFiles), 
                $this->formatBytes($totalSize)
            ));

            // Tampilkan daftar file
            $this->table(
                ['File Path', 'Size'],
                array_map(function ($file) {
                    return [$file['path'], $this->formatBytes($file['size'])];
                }, $orphanedFiles)
            );

            if ($isDryRun) {
                $this->warn('🧪 Mode dry-run: File tidak akan dihapus. Gunakan tanpa --dry-run untuk menghapus file.');
                return Command::SUCCESS;
            }

            if (!$this->confirm('❓ Apakah Anda yakin ingin menghapus semua file ini?')) {
                $this->info('❌ Pembersihan dibatalkan');
                return Command::SUCCESS;
            }

            // Hapus file
            $deletedCount = 0;
            $failedCount = 0;

            $this->info('🗑️  Menghapus file...');
            
            $this->withProgressBar($orphanedFiles, function ($fileInfo) use ($disk, &$deletedCount, &$failedCount) {
                try {
                    if ($disk->delete($fileInfo['path'])) {
                        $deletedCount++;
                    } else {
                        $failedCount++;
                    }
                } catch (\Exception $e) {
                    $this->error("Gagal menghapus {$fileInfo['path']}: " . $e->getMessage());
                    $failedCount++;
                }
            });

            $this->newLine(2);

            if ($failedCount === 0) {
                $this->info(sprintf('✅ Berhasil menghapus %d file (menghemat %s)', 
                    $deletedCount, 
                    $this->formatBytes($totalSize)
                ));
            } else {
                $this->warn(sprintf('⚠️  Berhasil menghapus %d file, gagal menghapus %d file', 
                    $deletedCount, 
                    $failedCount
                ));
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Terjadi kesalahan: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Format bytes menjadi human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
