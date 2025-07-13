<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MediaController extends Controller
{
    /**
     * Hapus media beserta file dari storage
     */
    public function destroy(Media $media): JsonResponse
    {
        try {
            DB::beginTransaction();

            $deleted = $media->deleteWithFile();

            if ($deleted) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Media berhasil dihapus beserta file dari storage'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus media'
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting media: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus media'
            ], 500);
        }
    }

    /**
     * Force delete media beserta file dari storage
     */
    public function forceDestroy(Media $media): JsonResponse
    {
        try {
            DB::beginTransaction();

            $deleted = $media->forceDeleteWithFile();

            if ($deleted) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Media berhasil dihapus permanen beserta file dari storage'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus media secara permanen'
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error force deleting media: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus media secara permanen'
            ], 500);
        }
    }

    /**
     * Batch delete multiple media
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:medias,id'
        ]);

        try {
            DB::beginTransaction();

            $deletedCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($request->ids as $id) {
                $media = Media::find($id);
                if ($media) {
                    if ($media->deleteWithFile()) {
                        $deletedCount++;
                    } else {
                        $failedCount++;
                        $errors[] = "Gagal menghapus media ID: {$id}";
                    }
                }
            }

            if ($failedCount === 0) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$deletedCount} media beserta file dari storage"
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Berhasil menghapus {$deletedCount} media, gagal menghapus {$failedCount} media",
                    'errors' => $errors
                ], 422);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk deleting media: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus media secara batch'
            ], 500);
        }
    }

    /**
     * Cleanup orphaned files (files yang ada di storage tapi tidak ada record di database)
     */
    public function cleanupOrphanedFiles(): JsonResponse
    {
        try {
            $disk = Storage::disk('public');
            $uploadDirectory = 'uploads';
            
            if (!$disk->exists($uploadDirectory)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Direktori uploads tidak ditemukan'
                ]);
            }

            $allFiles = $disk->allFiles($uploadDirectory);
            $deletedFiles = [];
            
            foreach ($allFiles as $file) {
                // Cek apakah file ini ada di database
                $mediaExists = Media::where('path', $file)->exists();
                
                if (!$mediaExists) {
                    $disk->delete($file);
                    $deletedFiles[] = $file;
                    Log::info("Orphaned file deleted: {$file}");
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($deletedFiles) > 0 ? 
                    'Berhasil menghapus ' . count($deletedFiles) . ' file yang tidak terpakai' : 
                    'Tidak ada file yang tidak terpakai',
                'deleted_files' => $deletedFiles
            ]);
        } catch (\Exception $e) {
            Log::error('Error cleaning up orphaned files: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membersihkan file yang tidak terpakai'
            ], 500);
        }
    }

    /**
     * Cek status file untuk semua media
     */
    public function checkFileStatus(): JsonResponse
    {
        try {
            $medias = Media::all();
            $missingFiles = [];
            $existingFiles = [];

            foreach ($medias as $media) {
                if ($media->fileExists()) {
                    $existingFiles[] = [
                        'id' => $media->id,
                        'filename' => $media->original_name,
                        'path' => $media->path
                    ];
                } else {
                    $missingFiles[] = [
                        'id' => $media->id,
                        'filename' => $media->original_name,
                        'path' => $media->path
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'total_media' => $medias->count(),
                'existing_files' => count($existingFiles),
                'missing_files' => count($missingFiles),
                'missing_file_details' => $missingFiles
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking file status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengecek status file'
            ], 500);
        }
    }
}
