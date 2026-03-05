<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * ============================================================
 * User Model — Người dùng hệ thống EMS
 * ============================================================
 * Hỗ trợ RBAC N-N qua bảng roles + user_roles.
 * Một người có thể vừa là SV vừa là Trợ giảng.
 * ============================================================
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar_file_id',
        'student_code',
        'lecturer_code',
        'class_name',
        'department',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ─── Scopes ───────────────────────────────────────────────────

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include students.
     */
    public function scopeStudents($query)
    {
        return $query->role('student');
    }

    /**
     * Scope a query to only include lecturers.
     */
    public function scopeLecturers($query)
    {
        return $query->role('lecturer');
    }

    // ─── Relationships ────────────────────────────────────────────

    /**
     * Ảnh đại diện.
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(File::class, 'avatar_file_id');
    }

    /**
     * Các câu hỏi do người dùng tạo.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    /**
     * Các lớp học phần mà giảng viên phụ trách.
     */
    public function courseSections(): HasMany
    {
        return $this->hasMany(CourseSection::class, 'lecturer_id');
    }

    /**
     * Các lớp học phần mà sinh viên đã đăng ký.
     */
    public function enrolledSections(): BelongsToMany
    {
        return $this->belongsToMany(CourseSection::class, 'course_section_students', 'student_id')
            ->withPivot('status', 'enrolled_at')
            ->withTimestamps();
    }

    /**
     * Files đã upload.
     */
    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(File::class, 'uploaded_by');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Get the user's avatar URL or a default placeholder.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_file_id && $this->avatar) {
            return asset('storage/' . $this->avatar->path);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4f46e5&color=fff';
    }

    /**
     * Check if the user account is locked.
     */
    public function isLocked(): bool
    {
        return ! $this->is_active;
    }

    /**
     * Get primary role display name.
     */
    public function getPrimaryRoleAttribute(): string
    {
        $role = $this->roles->first();
        return $role ? $role->name : 'Chưa phân quyền';
    }

    /**
     * Kiểm tra người dùng có phải admin không.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Kiểm tra người dùng có phải giảng viên không.
     */
    public function isLecturer(): bool
    {
        return $this->hasRole('lecturer');
    }

    /**
     * Kiểm tra người dùng có phải sinh viên không.
     */
    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }
}
