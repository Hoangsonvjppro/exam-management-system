<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role Model — Vai trò người dùng (RBAC N-N).
 *
 * @property int    $id
 * @property string $code   Mã vai trò: admin, lecturer, student...
 * @property string $name   Tên hiển thị
 * @property bool   $is_active
 */
class Role extends Model
{
    protected $fillable = ['code', 'name', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * Các user có vai trò này.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }
}
