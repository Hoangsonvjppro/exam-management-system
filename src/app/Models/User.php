<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'google_id',
        'password',
        'phone',
        'avatar_file_id',
        'google_avatar',
        'student_code',
        'lecturer_code',
        'class_name',
        'date_of_birth',
        'department',
        'is_active',
        'must_change_password',
        'password_changed_at',
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
            'date_of_birth'     => 'date',
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
        ];
    }

    // ─── Scopes ───────────────────────────────────────────────────

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include students.
     */
    public function scopeStudents(Builder $query): Builder
    {
        return $query->role('student');
    }

    /**
     * Scope a query to only include lecturers.
     */
    public function scopeLecturers(Builder $query): Builder
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
     * Các thông báo nhận được.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
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

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Get the user's avatar URL or a default placeholder.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->google_avatar) {
                return $this->google_avatar;
            }

            if ($this->avatar_file_id && $this->avatar) {
                return asset('storage/' . $this->avatar->path);
            }

            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4f46e5&color=fff';
        });
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
    protected function primaryRole(): Attribute
    {
        return Attribute::get(function () {
            if ($this->hasRole('lecturer')) {
                return 'Giảng viên';
            }

            if ($this->hasRole('student')) {
                return 'Sinh viên';
            }

            return 'Người dùng đã đăng nhập';
        });
    }

    /**
     * Kiểm tra người dùng có phải admin không.
     */
    public function isAdmin(): bool
    {
        return false;
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
