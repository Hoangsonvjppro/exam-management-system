<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function logAdminAction(string $action, ?Model $target = null, array $metadata = []): void
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return;
        }

        AuditLog::query()->create([
            'actor_admin_id' => $admin->id,
            'action' => $action,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'metadata' => $metadata,
        ]);
    }
}
