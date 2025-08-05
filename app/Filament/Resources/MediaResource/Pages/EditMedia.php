<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->url(fn () => $this->record->full_url)
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Simple - no need to unset anything
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If a new file is uploaded during edit, handle the replacement
        if (isset($data['new_file']) && $data['new_file']) {
            $filePath = $data['new_file'];
            
            // Delete old file if it exists
            if ($this->record->path && Storage::disk('public')->exists($this->record->path)) {
                Storage::disk('public')->delete($this->record->path);
            }
            
            // Update with new path only
            $data['path'] = $filePath;
            
            // Remove the temporary file field
            unset($data['new_file']);
        }
        
        return $data;
    }
    
    public function form(Form $form): Form
    {
        return MediaResource::editForm($form);
    }
}
