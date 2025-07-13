<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Media;

class CheckMediaFileStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:check-status {--missing-only : Show only media with missing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check file status for all media records in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $showMissingOnly = $this->option('missing-only');
        
        $this->info('🔍 Memeriksa status file untuk semua media...');
        
        try {
            $medias = Media::all();
            $missingFiles = [];
            $existingFiles = [];

            $this->withProgressBar($medias, function ($media) use (&$missingFiles, &$existingFiles) {
                if ($media->fileExists()) {
                    $existingFiles[] = $media;
                } else {
                    $missingFiles[] = $media;
                }
            });

            $this->newLine(2);

            $this->info(sprintf('📊 Total media: %d', $medias->count()));
            $this->info(sprintf('✅ File tersedia: %d', count($existingFiles)));
            $this->info(sprintf('❌ File hilang: %d', count($missingFiles)));

            if (count($missingFiles) > 0) {
                $this->newLine();
                $this->warn('🚨 Media dengan file yang hilang:');
                
                $tableData = [];
                foreach ($missingFiles as $media) {
                    $tableData[] = [
                        $media->id,
                        $media->original_name ?: 'N/A',
                        $media->path ?: 'N/A',
                        $media->created_at->format('Y-m-d H:i:s')
                    ];
                }

                $this->table(
                    ['ID', 'Original Name', 'Path', 'Created At'],
                    $tableData
                );

                if ($this->confirm('❓ Apakah Anda ingin menghapus record media yang file-nya hilang?')) {
                    $deletedCount = 0;
                    foreach ($missingFiles as $media) {
                        try {
                            $media->delete();
                            $deletedCount++;
                        } catch (\Exception $e) {
                            $this->error("Gagal menghapus media ID {$media->id}: " . $e->getMessage());
                        }
                    }
                    $this->info("✅ Berhasil menghapus {$deletedCount} record media yang file-nya hilang");
                }
            } else {
                $this->info('🎉 Semua file media tersedia!');
            }

            if (!$showMissingOnly && count($existingFiles) > 0) {
                $this->newLine();
                $this->info('✅ Media dengan file yang tersedia:');
                
                $tableData = [];
                foreach (array_slice($existingFiles, 0, 10) as $media) { // Tampilkan hanya 10 teratas
                    $tableData[] = [
                        $media->id,
                        $media->original_name ?: 'N/A',
                        $media->path ?: 'N/A',
                        $this->formatBytes($media->size ?: 0),
                    ];
                }

                $this->table(
                    ['ID', 'Original Name', 'Path', 'Size'],
                    $tableData
                );

                if (count($existingFiles) > 10) {
                    $this->info(sprintf('... dan %d media lainnya', count($existingFiles) - 10));
                }
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
