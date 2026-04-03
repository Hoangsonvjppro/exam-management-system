<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_section_id',
        'student_id',
        'date',
        'reason',
        'proof_image_path',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function normalizedProofImagePath(): ?string
    {
        if (!$this->proof_image_path) {
            return null;
        }

        $path = trim($this->proof_image_path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return null;
        }

        $path = str_replace('\\', '/', ltrim($path, '/'));

        if (Str::startsWith($path, 'storage/')) {
            $path = Str::after($path, 'storage/');
        }

        if (Str::startsWith($path, 'public/')) {
            $path = Str::after($path, 'public/');
        }

        return $path !== '' ? $path : null;
    }

    public function getProofImageUrlAttribute(): ?string
    {
        if (!$this->proof_image_path) {
            return null;
        }

        $path = trim($this->proof_image_path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        if (!$this->exists || !$this->normalizedProofImagePath()) {
            return null;
        }

        $baseUrl = app()->bound('request') ? (string) request()->getBaseUrl() : '';
        $prefix = rtrim($baseUrl, '/');

        return $prefix.'/leave-requests/'.$this->getKey().'/proof-image';
    }
}
