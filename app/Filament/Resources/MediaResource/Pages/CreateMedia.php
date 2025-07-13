<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle file upload and extract metadata
        if (isset($data['file']) && $data['file']) {
            $filePath = $data['file'];
            
            // Generate UUID
            $data['uuid'] = (string) Str::uuid();
            $data['disk'] = 'public';
            
            // Get file information from the stored file
            $fullPath = Storage::disk('public')->path($filePath);
            $originalName = pathinfo($filePath, PATHINFO_BASENAME);
            
            // Extract file information
            $data['original_name'] = $originalName;
            $data['filename'] = $originalName;
            $data['path'] = $filePath;
            $data['extension'] = pathinfo($filePath, PATHINFO_EXTENSION);
            $data['size'] = Storage::disk('public')->size($filePath);
            $data['mime_type'] = Storage::disk('public')->mimeType($filePath);
            
            // Set URL
            $data['url'] = Storage::disk('public')->url($filePath);
            
            // Extract metadata based on file type
            $metadata = $this->extractMetadata($fullPath, $data['mime_type']);
            $data['metadata'] = $metadata;
            
            // Auto-detect type if not set properly
            if (!isset($data['type']) || $data['type'] === 'other') {
                $data['type'] = $this->detectFileType($data['mime_type']);
            }
            
            // Remove the temporary file field
            unset($data['file']);
        }
        
        return $data;
    }
    
    private function extractMetadata(string $fullFilePath, string $mimeType): array
    {
        $metadata = [];
        
        try {
            if (str_starts_with($mimeType, 'image/')) {
                $imageSize = getimagesize($fullFilePath);
                if ($imageSize) {
                    $metadata['width'] = $imageSize[0];
                    $metadata['height'] = $imageSize[1];
                    $metadata['aspect_ratio'] = round($imageSize[0] / $imageSize[1], 2);
                }
            } elseif (str_starts_with($mimeType, 'video/')) {
                // For video files, you might want to use FFmpeg or similar
                // This is a basic implementation
                $metadata['type'] = 'video';
                $metadata['file_size'] = filesize($fullFilePath);
            } elseif (str_starts_with($mimeType, 'audio/')) {
                // For audio files, you might want to use getID3 or similar
                $metadata['type'] = 'audio';
                $metadata['file_size'] = filesize($fullFilePath);
            } else {
                $metadata['file_size'] = filesize($fullFilePath);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::warning('Failed to extract metadata for file: ' . $e->getMessage());
        }
        
        return $metadata;
    }
    
    private function detectFileType(string $mimeType): string
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
            'text/plain',
        ])) {
            return 'document';
        }
        
        return 'other';
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
