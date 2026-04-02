<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['code', 'name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    // ── Relationships ───────────────────────────────────────────
    public function majors(): HasMany
    {
        return $this->hasMany(Major::class);
    }

    public function studentClasses(): HasMany
    {
        return $this->hasMany(StudentClass::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
    
    // ── Scopes ──────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function listWithCounts(): Builder
    {
        return static::query()
            ->withCount('majors')
            ->orderBy('name');
    }

    public static function activeOptions(): \Illuminate\Support\Collection
    {
        return static::active()->orderBy('name')->get(['id', 'name', 'code']);
    }
    // ── Helpers ─────────────────────────────────────────────────

    /**
     * Toggle trạng thái active — dùng thay vì delete.
     */
    public function toggleActive(): void
    {
        $this->update(['is_active' => ! $this->is_active]);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Đang hoạt động' : 'Đã khóa';
    }

    /**
     * Kiểm tra có thể khóa không (không có user/class đang dùng).
     */
    public function canBeDeactivated(): bool
    {
        return ! $this->majors()->where('is_active', true)->exists();
    }
}
