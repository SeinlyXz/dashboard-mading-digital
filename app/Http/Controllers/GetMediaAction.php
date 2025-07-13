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
            $uuid = $request->get('uuid');
            $id = $request->get('id');
            $type = $request->get('type');
            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            // Query builder untuk Media
            $query = Media::query();

            // Filter berdasarkan UUID jika ada
            if ($uuid) {
                $media = $query->where('uuid', $uuid)->first();
                
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

            // Filter berdasarkan type jika ada
            if ($type) {
                $query->where('type', $type);
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
            'uuid' => $media->uuid,
            'filename' => $media->filename,
            'original_name' => $media->original_name,
            'mime_type' => $media->mime_type,
            'extension' => $media->extension,
            'size' => $media->size,
            'size_formatted' => $this->formatFileSize($media->size),
            'disk' => $media->disk,
            'path' => $media->path,
            'url' => $media->url,
            'full_url' => $media->full_url,
            'type' => $media->type,
            'description' => $media->description,
            'metadata' => $media->metadata,
            'created_at' => $media->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $media->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Format ukuran file dalam bentuk yang mudah dibaca
     */
    private function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }
}
