<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Filament\Resources\MediaResource\RelationManagers;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file')
                    ->label('Upload File')
                    ->required()
                    ->disk('public')
                    ->directory('uploads')
                    ->maxSize(200 * 1024) // 200MB
                    ->uploadingMessage(message: 'Uploading file...')
                    ->live()
                    ->columnSpanFull()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            // File akan diproses di mutateFormDataBeforeCreate
                        }
                    }),
                
                Forms\Components\Select::make('type')
                    ->label('Media Type')
                    ->required()
                    ->options([
                        'image' => 'Image',
                        'video' => 'Video',
                        'audio' => 'Audio',
                        'document' => 'Document',
                        'other' => 'Other',
                    ])
                    ->columnSpanFull()
                    ->default('other')
                    ->selectablePlaceholder(false),
                
                Forms\Components\Textarea::make('description')
                    ->label('Description (Opsional)')
                    ->maxLength(500)
                    ->columnSpanFull(),
                
                // Hidden fields that will be populated automatically
                Forms\Components\Hidden::make('uuid'),
                Forms\Components\Hidden::make('filename'),
                Forms\Components\Hidden::make('original_name'),
                Forms\Components\Hidden::make('mime_type'),
                Forms\Components\Hidden::make('extension'),
                Forms\Components\Hidden::make('size'),
                Forms\Components\Hidden::make('disk'),
                Forms\Components\Hidden::make('path'),
                Forms\Components\Hidden::make('url'),
                Forms\Components\Hidden::make('metadata'),
            ]);
    }

    public static function editForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('path')
                    ->label('File Sekarang')
                    ->disk('public')
                    ->directory('uploads')
                    ->visibility('private')
                    ->disabled()
                    ->columnSpanFull()
                    ->image(),
                Forms\Components\FileUpload::make('file')
                    ->label('Replace File (Optional)')
                    ->disk('public')
                    ->directory('uploads')
                    ->acceptedFileTypes([
                        'image/*',
                        'video/*',
                        'audio/*',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->maxSize(50 * 1024) // 50MB
                    ->uploadingMessage('Uploading file...')
                    ->columnSpanFull()
                    ->helperText('Leave empty to keep the current file'),
                
                Forms\Components\Select::make('type')
                    ->label('Media Type')
                    ->required()
                    ->columnSpanFull()
                    ->options([
                        'image' => 'Image',
                        'video' => 'Video',
                        'audio' => 'Audio',
                        'document' => 'Document',
                        'other' => 'Other',
                    ])
                    ->selectablePlaceholder(false),
                
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->maxLength(500)
                    ->columnSpanFull(),
                
                // Display current file information
                Forms\Components\TextInput::make('original_name')
                    ->label('Current File Name')
                    ->disabled()
                    ->dehydrated(false),
                
                Forms\Components\TextInput::make('size')
                    ->label('File Size')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn ($state) => self::formatBytes($state)),
                
                Forms\Components\TextInput::make('mime_type')
                    ->label('MIME Type')
                    ->disabled()
                    ->dehydrated(false),
                
                // Hidden fields that will be populated automatically if file is replaced
                Forms\Components\Hidden::make('filename'),
                Forms\Components\Hidden::make('extension'),
                Forms\Components\Hidden::make('disk'),
                Forms\Components\Hidden::make('path'),
                Forms\Components\Hidden::make('url'),
                Forms\Components\Hidden::make('metadata'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('Preview')
                    ->size(40)
                    ->visibility('private'),
                Tables\Columns\TextColumn::make('original_name')
                    ->label('File Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'image' => 'success',
                        'video' => 'info',
                        'audio' => 'warning',
                        'document' => 'gray',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('size')
                    ->label('File Size')
                    ->formatStateUsing(fn ($state) => self::formatBytes($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label('MIME Type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('extension')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'image' => 'Image',
                        'video' => 'Video',
                        'audio' => 'Audio',
                        'document' => 'Document',
                        'other' => 'Other',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make()
                //     ->url(fn ($record) => $record->full_url)
                //     ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
