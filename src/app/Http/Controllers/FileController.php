<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Models\File;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ============================================================
 * FileController — Upload / Download / Xoá file
 * ============================================================
 * Endpoints:
 *   POST   /files       → store()   Upload file mới
 *   GET    /files/{id}  → show()    Download / preview file
 *   DELETE /files/{id}  → destroy() Xoá file (soft delete)
 * ============================================================
 */
class FileController extends Controller
{
    public function __construct(
        private readonly FileUploadService $uploadService
    ) {
    }

    /**
     * Upload file mới.
     *
     * POST /files
     * Body: multipart/form-data { file, ?directory, ?is_public, ?used_by_type, ?used_by_id }
     */
    public function store(FileUploadRequest $request): JsonResponse
    {
        try {
            $file = $this->uploadService->upload(
                $request->file('file'),
                [
                    'directory' => $request->input('directory', 'uploads'),
                    'is_public' => $request->boolean('is_public', false),
                    'used_by_type' => $request->input('used_by_type'),
                    'used_by_id' => $request->input('used_by_id'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'File đã được tải lên thành công.',
                'data' => [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'mime_type' => $file->mime_type,
                    'size' => $file->size,
                    'human_size' => $file->human_size,
                    'extension' => $file->extension,
                    'url' => $this->uploadService->getUrl($file),
                ],
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải file lên. Vui lòng thử lại.',
            ], 500);
        }
    }

    /**
     * Download / xem trước file.
     *
     * GET /files/{file}
     * Query: ?download=1 — bắt buộc tải xuống thay vì xem trước
     */
    public function show(Request $request, File $file): StreamedResponse|JsonResponse
    {
        // Kiểm tra file có tồn tại trên disk không
        if (!$file->existsOnDisk()) {
            return response()->json([
                'success' => false,
                'message' => 'File không tồn tại trên hệ thống.',
            ], 404);
        }

        $storage = Storage::disk($file->disk);

        // Download bắt buộc
        if ($request->boolean('download')) {
            return $storage->download($file->path, $file->original_name, [
                'Content-Type' => $file->mime_type,
            ]);
        }

        // Inline preview (cho ảnh, PDF)
        return $storage->response($file->path, $file->original_name, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => "inline; filename=\"{$file->original_name}\"",
        ]);
    }

    /**
     * Xoá file.
     *
     * DELETE /files/{file}
     */
    public function destroy(File $file): JsonResponse
    {
        // Kiểm tra quyền: chỉ người upload hoặc admin mới được xoá
        $user = auth()->user();
        if ($file->uploaded_by !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xoá file này.',
            ], 403);
        }

        $this->uploadService->delete($file);

        return response()->json([
            'success' => true,
            'message' => 'File đã được xoá thành công.',
        ]);
    }
}
