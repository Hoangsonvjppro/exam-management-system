<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    protected $fillable = ['code', 'name', 'department_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    // ── Relationships ───────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function studentClasses(): HasMany
    {
        return $this->hasMany(StudentClass::class);
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

    public function scopeForDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    // ── Query helpers ────────────────────────────────────────────

    /**
     * Majors đang active kèm department — dùng cho select.
     */
    public static function activeWithDepartment(): Builder
    {
        return static::active()
            ->with('department')
            ->orderBy('name');
    }

    /**
     * Majors theo department — dùng cho cascading select.
     */
    public static function byDepartment(int $departmentId): Builder
    {
        return static::active()
            ->forDepartment($departmentId)
            ->orderBy('name');
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
        return ! $this->users()->exists()
            && ! $this->studentClasses()->where('is_active', true)->exists();
    }
}