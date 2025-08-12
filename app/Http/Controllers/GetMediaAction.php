<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GetMediaAction extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            // Ambil parameter dari request
            $id = $request->get('id');
            $type = $request->get('type');
            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            // Query builder untuk Media
            $query = Media::query();

            // Filter berdasarkan ID jika ada
            if ($id) {
                $media = $query->where('id', $id)->first();
                
                if (!$media) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Media tidak ditemukan',
                        'data' => null
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Media berhasil ditemukan',
                    'data' => $this->formatMediaData($media)
                ]);
            }

            // Filter berdasarkan type jika ada (menggunakan accessor type dari model)
            if ($type) {
                if ($type === 'image') {
                    $query->where(function($q) {
                        $q->where('path', 'like', '%.jpg')
                          ->orWhere('path', 'like', '%.jpeg')
                          ->orWhere('path', 'like', '%.png')
                          ->orWhere('path', 'like', '%.gif')
                          ->orWhere('path', 'like', '%.webp')
                          ->orWhere('path', 'like', '%.bmp');
                    });
                } elseif ($type === 'video') {
                    $query->where(function($q) {
                        $q->where('path', 'like', '%.mp4')
                          ->orWhere('path', 'like', '%.webm')
                          ->orWhere('path', 'like', '%.avi')
                          ->orWhere('path', 'like', '%.mov')
                          ->orWhere('path', 'like', '%.mkv');
                    });
                }
            }

            // Pagination
            $medias = $query->orderBy('created_at', 'desc')
                           ->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Media berhasil diambil',
                'data' => [
                    'items' => $medias->items() ? array_map([$this, 'formatMediaData'], $medias->items()) : [],
                    'pagination' => [
                        'current_page' => $medias->currentPage(),
                        'per_page' => $medias->perPage(),
                        'total' => $medias->total(),
                        'last_page' => $medias->lastPage(),
                        'from' => $medias->firstItem(),
                        'to' => $medias->lastItem(),
                        'has_more_pages' => $medias->hasMorePages()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil media',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format data media untuk response JSON
     */
    private function formatMediaData(Media $media): array
    {
        return [
            'id' => $media->id,
            'path' => $media->path,
            'filename' => $media->filename, // accessor dari model
            'extension' => $media->extension, // accessor dari model
            'mime_type' => $media->mime_type, // accessor dari model
            'type' => $media->type, // accessor dari model (image/video/unknown)
            'full_url' => $media->full_url, // accessor dari model
            'file_exists' => $media->fileExists(), // method dari model
            'created_at' => $media->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $media->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
