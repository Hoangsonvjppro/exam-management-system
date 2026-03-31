<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class StudentClass extends Model
{
    protected $fillable = [
        'code', 'name', 'major_id', 'academic_year', 'class_group', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'     => 'boolean',
            'academic_year' => 'integer',
            'class_group' => 'integer',
        ];
    }

    // ── Relationships ───────────────────────────────────────────
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }
    
    // Department truy cập qua major
    public function department(): HasOneThrough
    {
        return $this->hasOneThrough(
            Department::class,
            Major::class,
            'id',          // major.id
            'id',          // department.id
            'major_id',    // student_classes.major_id
            'department_id'// majors.department_id
        );
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByMajor(Builder $query, int $majorId): Builder
    {
        return $query->where('major_id', $majorId);
    }

    public function scopeByAcademicYear(Builder $query, int $year): Builder
    {
        return $query->where('academic_year', $year);
    }


    /**
     * Danh sách lớp active kèm major + department — dùng cho select.
     */
    public static function activeWithRelations(): Builder
    {
        return static::active()
            ->with(['major.department'])
            ->orderBy('academic_year', 'desc')
            ->orderBy('class_group');
    }

    /**
     * Lớp theo ngành — dùng cho cascading select.
     */
    public static function getClassesFromMajorAndAcademicYear(int $majorId, int $academicYear): Builder
    {
        return static::active()
            ->byMajor($majorId)
            ->byAcademicYear($academicYear)
            ->orderBy('class_group');
    }
    // ── Helpers ─────────────────────────────────────────────────

    public function toggleActive(): void
    {
        $this->update(['is_active' => ! $this->is_active]);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Đang hoạt động' : 'Đã khóa';
    }

    public function canBeDeactivated(): bool
    {
        return ! $this->users()->exists();
    }
}