<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

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
     * Get the user's avatar file.
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(File::class, 'avatar_file_id');
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
}
