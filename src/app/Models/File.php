<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * ============================================================
 * File Model — Quản lý file tập trung
 * ============================================================
 * Mọi upload đều đi qua bảng này. Các bảng khác tham chiếu
 * file_id thay vì lưu path rời rạc. Khi cần migrate sang
 * S3/MinIO, chỉ cần sửa disk + path.
 * ============================================================
 *
 * @property int    $id
 * @property int    $uploaded_by
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string $mime_type
 * @property string $extension
 * @property int    $size
 * @property string $checksum
 * @property bool   $is_public
 * @property string $used_by_type
 * @property int    $used_by_id
 */
class File extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Các cột được phép mass-assign.
     */
    protected $fillable = [
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'checksum',
        'is_public',
        'used_by_type',
        'used_by_id',
    ];

    /**
     * Kiểu dữ liệu cast cho các cột.
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'is_public' => 'boolean',
        ];
    }

    // ─── RELATIONSHIPS ───────────────────────────────────────

    /**
     * Người đã upload file này.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Polymorphic: bản ghi đang sử dụng file này.
     * VD: Question, Document, Syllabus, User (avatar)...
     */
    public function usedBy(): MorphTo
    {
        return $this->morphTo('used_by');
    }

    // ─── ACCESSORS ───────────────────────────────────────────

    /**
     * Tạo URL truy cập file.
     * - Public file → URL trực tiếp
     * - Private file → Temporary signed URL (30 phút)
     */
    public function getUrlAttribute(): ?string
    {
        $storage = Storage::disk($this->disk);

        if (!$storage->exists($this->path)) {
            return null;
        }

        if ($this->is_public) {
            return $storage->url($this->path);
        }

        // Signed URL có hiệu lực 30 phút
        return $storage->temporaryUrl($this->path, now()->addMinutes(30));
    }

    /**
     * Dung lượng file dạng dễ đọc (KB, MB, GB).
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    // ─── SCOPES ──────────────────────────────────────────────

    /**
     * Scope: File mồ côi (không ai sử dụng).
     * Dùng để dọn rác định kỳ.
     */
    public function scopeOrphaned($query)
    {
        return $query->whereNull('used_by_type')
            ->whereNull('used_by_id');
    }

    /**
     * Scope: Lọc theo MIME type.
     * VD: File::ofMimeType('image')->get()
     */
    public function scopeOfMimeType($query, string $mimePrefix)
    {
        return $query->where('mime_type', 'like', $mimePrefix . '%');
    }

    /**
     * Scope: Lọc theo extension.
     * VD: File::ofExtension('pdf')->get()
     */
    public function scopeOfExtension($query, string $extension)
    {
        return $query->where('extension', strtolower($extension));
    }

    // ─── HELPERS ─────────────────────────────────────────────

    /**
     * Kiểm tra file có phải ảnh không.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check whether the file is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Kiểm tra file có tồn tại trên disk không.
     */
    public function existsOnDisk(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }
}
