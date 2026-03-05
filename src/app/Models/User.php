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
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ─── RELATIONSHIPS ───────────────────────────────────────

    /**
     * Các vai trò của người dùng (N-N qua user_roles).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

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

    // ─── HELPERS ─────────────────────────────────────────────

    /**
     * Kiểm tra người dùng có vai trò cụ thể không.
     *
     * @param string $roleCode Mã vai trò: admin, lecturer, student...
     */
    public function hasRole(string $roleCode): bool
    {
        return $this->roles()->where('code', $roleCode)->exists();
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
