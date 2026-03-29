<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'type',
        'is_published',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'urgent'  => 'Khẩn cấp',
            'warning' => 'Cảnh báo',
            'event'   => 'Sự kiện',
            default   => 'Thông báo',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'urgent'  => 'red',
            'warning' => 'yellow',
            'event'   => 'green',
            default   => 'blue',
        };
    }
}
