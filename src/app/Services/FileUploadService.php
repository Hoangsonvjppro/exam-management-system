<?php

namespace App\Services;

use App\Models\File;
use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ============================================================
 * FileUploadService — Xử lý upload file tập trung
 * ============================================================
 * Tất cả upload trong hệ thống EMS đều đi qua service này.
 * Đảm bảo:
 *   - File được lưu đúng thư mục theo loại
 *   - Tính SHA-256 checksum để phát hiện trùng lặp
 *   - Validate MIME type + kích thước
 *   - Tạo record trong bảng `files`
 *   - Dễ dàng migrate sang S3/MinIO sau này
 * ============================================================
 */
class FileUploadService
{
    /**
     * Upload file vào hệ thống.
     *
     * @param UploadedFile $uploadedFile File từ request
     * @param array $options Tuỳ chọn:
     *   - directory: string  Thư mục con (mặc định: 'uploads')
     *   - disk: string       Storage disk (mặc định: 'local')
     *   - is_public: bool    File public? (mặc định: false)
     *   - used_by_type: string  Model class sử dụng file
     *   - used_by_id: int       ID bản ghi sử dụng file
     *
     * @return File Record file đã tạo
     *
     * @throws \InvalidArgumentException Khi file không hợp lệ
     * @throws \RuntimeException         Khi lưu file thất bại
     */
    public function upload(UploadedFile $uploadedFile, array $options = [], ?int $uploadedBy = null): File
    {
        // ── Merge options với defaults ──────────────────────
        $directory = $options['directory'] ?? 'uploads';
        $disk = $options['disk'] ?? $this->getDefaultDisk();
        $isPublic = $options['is_public'] ?? false;

        // ── Validate file ──────────────────────────────────
        $this->validateFile($uploadedFile);

        // ── Tạo tên file unique để tránh trùng ─────────────
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $originalName = $uploadedFile->getClientOriginalName();
        $uniqueName = Str::uuid() . '.' . $extension;

        // ── Tổ chức thư mục theo ngày: uploads/2026/03/02/ ─
        $datePath = now()->format('Y/m/d');
        $storePath = "{$directory}/{$datePath}";

        // ── Tính SHA-256 checksum ──────────────────────────
        $checksum = hash_file('sha256', $uploadedFile->getRealPath());

        // ── Kiểm tra file trùng (cùng checksum) ────────────
        $existingFile = File::where('checksum', $checksum)
            ->whereNull('deleted_at')
            ->first();

        if ($existingFile) {
            Log::info("File trùng phát hiện: {$originalName} (checksum: {$checksum})");
            // Trả về file đã tồn tại thay vì upload lại
            return $existingFile;
        }

        // ── Lưu file lên disk ──────────────────────────────
        $storedPath = $uploadedFile->storeAs($storePath, $uniqueName, $disk);

        if (!$storedPath) {
            throw new \RuntimeException("Không thể lưu file: {$originalName}");
        }

        // ── Tạo record trong DB ────────────────────────────
        $file = File::create([
            'uploaded_by' => $uploadedBy ?? ($options['uploaded_by'] ?? null),
            'disk' => $disk,
            'path' => $storedPath,
            'original_name' => $originalName,
            'mime_type' => $uploadedFile->getMimeType(),
            'extension' => $extension,
            'size' => $uploadedFile->getSize(),
            'checksum' => $checksum,
            'is_public' => $isPublic,
            'used_by_type' => $options['used_by_type'] ?? null,
            'used_by_id' => $options['used_by_id'] ?? null,
        ]);

        Log::info("File uploaded: #{$file->id} - {$originalName} ({$file->human_size})");

        return $file;
    }

    /**
     * Xoá file khỏi hệ thống.
     * Soft delete record + xoá file thật trên disk.
     *
     * @param File $file Record file cần xoá
     * @param bool $forceDeleteFromDisk Xoá thật khỏi disk? (mặc định: true)
     */
    public function delete(File $file, bool $forceDeleteFromDisk = true): bool
    {
        // Xoá file trên disk
        if ($forceDeleteFromDisk && $file->existsOnDisk()) {
            Storage::disk($file->disk)->delete($file->path);
            Log::info("File xoá khỏi disk: #{$file->id} - {$file->path}");
        }

        // Soft delete record
        $file->delete();
        Log::info("File soft deleted: #{$file->id} - {$file->original_name}");

        return true;
    }

    /**
     * Tạo URL truy cập file.
     *
     * @param File $file
     * @param int  $expireMinutes Thời gian hết hạn cho signed URL (phút)
     *
     * @return string|null URL hoặc null nếu file không tồn tại
     */
    public function getUrl(File $file, int $expireMinutes = 30): ?string
    {
        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($file->disk);

        if (!$storage->exists($file->path)) {
            return null;
        }

        // File public → URL trực tiếp
        if ($file->is_public) {
            return $storage->url($file->path);
        }

        // File private → Temporary Signed URL
        // Lưu ý: Chỉ hoạt động với S3 hoặc khi driver hỗ trợ temporaryUrl
        try {
            return $storage->temporaryUrl($file->path, now()->addMinutes($expireMinutes));
        } catch (\RuntimeException $e) {
            return null;
        }
    }

    /**
     * Gán file cho một bản ghi cụ thể (polymorphic).
     *
     * @param File   $file
     * @param string $modelClass VD: App\Models\Question
     * @param int    $modelId    ID bản ghi
     */
    public function attachTo(File $file, string $modelClass, int $modelId): File
    {
        $file->update([
            'used_by_type' => $modelClass,
            'used_by_id' => $modelId,
        ]);

        return $file;
    }

    /**
     * Dọn file mồ côi (không ai sử dụng, >24h).
     * Chạy bởi scheduler hàng ngày.
     *
     * @return int Số file đã dọn
     */
    public function cleanOrphans(): int
    {
        $orphans = File::orphaned()
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        $count = 0;
        foreach ($orphans as $file) {
            if (! $file instanceof File) {
                continue;
            }
            $this->delete($file);
            $count++;
        }

        if ($count > 0) {
            Log::info("Dọn {$count} file mồ côi.");
        }

        return $count;
    }

    // ─── PRIVATE METHODS ─────────────────────────────────────

    /**
     * Validate file trước khi upload.
     *
     * @throws \InvalidArgumentException
     */
    private function validateFile(UploadedFile $file): void
    {
        // Lấy cấu hình từ bảng settings (fallback defaults)
        $maxSizeMb = (int) $this->getSetting('max_upload_size_mb', 20);
        $allowedTypes = $this->getSetting(
            'allowed_file_types',
            'pdf,docx,doc,pptx,ppt,xlsx,xls,jpg,jpeg,png,gif'
        );

        $maxSizeBytes = $maxSizeMb * 1024 * 1024;
        $allowedExtArray = array_map('trim', explode(',', $allowedTypes));

        // Kiểm tra kích thước
        if ($file->getSize() > $maxSizeBytes) {
            throw new \InvalidArgumentException(
                "File vượt quá dung lượng cho phép ({$maxSizeMb}MB). " .
                    "Kích thước file: " . number_format($file->getSize() / 1048576, 2) . "MB."
            );
        }

        // Kiểm tra extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtArray)) {
            throw new \InvalidArgumentException(
                "Loại file '.{$extension}' không được phép. " .
                    "Các loại cho phép: " . implode(', ', $allowedExtArray)
            );
        }

        // Kiểm tra MIME type khớp extension (chống giả mạo extension)
        $mimeExtensionMap = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
        ];

        $mimeType = $file->getMimeType();
        if (isset($mimeExtensionMap[$extension])) {
            if (!in_array($mimeType, $mimeExtensionMap[$extension])) {
                Log::warning("MIME type mismatch: file={$file->getClientOriginalName()}, ext={$extension}, mime={$mimeType}");
                throw new \InvalidArgumentException(
                    "File không hợp lệ: MIME type ({$mimeType}) không khớp với extension (.{$extension})."
                );
            }
        }
    }

    /**
     * Lấy giá trị cấu hình từ bảng settings.
     * Fallback nếu bảng chưa tồn tại (DB chưa migrate).
     */
    private function getSetting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::getValue($key, $default);
        } catch (QueryException | \RuntimeException $e) {
            return $default;
        }
    }

    /**
     * Lấy disk mặc định từ settings hoặc config.
     */
    private function getDefaultDisk(): string
    {
        return $this->getSetting('file_storage_disk', config('filesystems.default', 'local'));
    }
}
