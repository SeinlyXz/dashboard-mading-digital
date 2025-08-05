<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SlideshowController extends Controller
{
    /**
     * Tampilkan halaman slideshow
     */
    public function index()
    {
        return view('slideshow.index');
    }

    /**
     * API untuk mendapatkan daftar media yang aktif
     */
    public function getMedia(): JsonResponse
    {
        $media = Media::all()
            ->filter(function ($item) {
                // Filter hanya file yang ada dan tipe yang didukung
                return $item->fileExists() && 
                       in_array($item->type, ['image', 'video']) &&
                       in_array($item->extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm']);
            })
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'url' => $item->full_url,
                    'type' => $item->type,
                    'extension' => $item->extension,
                    'filename' => $item->filename,
                    'mime_type' => $item->mime_type,
                    'path' => $item->path
                ];
            })
            ->values(); // Reset array keys

        return response()->json($media);
    }

    /**
     * API untuk mendapatkan media dengan cache control
     */
    public function getMediaWithCache(): JsonResponse
    {
        $cacheKey = 'slideshow_media_' . date('Y-m-d-H-i');
        
        $media = cache()->remember($cacheKey, 300, function () { // Cache 5 menit
            return Media::orderBy('created_at', 'desc')
                ->get()
                ->filter(function ($item) {
                    // Filter hanya file yang ada dan tipe yang didukung
                    return $item->fileExists() && 
                           in_array($item->type, ['image', 'video']) &&
                           in_array($item->extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm']);
                })
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'url' => $item->full_url,
                        'type' => $item->type,
                        'extension' => $item->extension,
                        'filename' => $item->filename,
                        'mime_type' => $item->mime_type,
                        'path' => $item->path
                    ];
                })
                ->values(); // Reset array keys
        });

        return response()->json($media);
    }
}
