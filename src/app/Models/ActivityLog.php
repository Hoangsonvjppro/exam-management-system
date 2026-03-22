<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    // Chỉ có created_at, không có updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Static Helpers ───────────────────────────────────────────

    /**
     * Log an action conveniently.
     */
    public static function record(
        string $action,
        ?Model $model = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = []
    ): static {
        return static::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'model_type'  => $model ? get_class($model) : null,
            'model_id'    => $model?->getKey(),
            'description' => $description,
            'old_values'  => empty($oldValues) ? null : $oldValues,
            'new_values'  => empty($newValues) ? null : $newValues,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
