<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle multiple file uploads
        if (isset($data['files']) && is_array($data['files']) && !empty($data['files'])) {
            // For multiple files, we'll process the first one for this record
            // and create additional records for the rest
            $files = $data['files'];
            $firstFile = array_shift($files);
            
            // Store remaining files for later processing
            $this->remainingFiles = $files;
            
            // Only store the path for the first file
            return ['path' => $firstFile];
        }
        
        return $data;
    }
    
    protected array $remainingFiles = [];
    
    protected function afterCreate(): void
    {
        // Create additional records for remaining files
        foreach ($this->remainingFiles as $filePath) {
            \App\Models\Media::create(['path' => $filePath]);
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
