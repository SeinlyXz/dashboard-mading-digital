<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Media;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh data media untuk testing
        // Pastikan file-file ini ada di storage/app/public/
        
        $mediaFiles = [
            'sample-images/sample1.jpg',
            'sample-images/sample2.png',
            'sample-images/sample3.gif',
            'sample-videos/sample1.mp4',
            'sample-videos/sample2.webm',
        ];

        foreach ($mediaFiles as $path) {
            Media::create([
                'path' => $path,
            ]);
        }

        $this->command->info('Media seeder completed! Added ' . count($mediaFiles) . ' media files.');
        $this->command->info('Note: Make sure the actual files exist in storage/app/public/');
    }
}
