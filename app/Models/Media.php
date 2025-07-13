<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Media extends Model
{
    use SoftDeletes;

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
}
