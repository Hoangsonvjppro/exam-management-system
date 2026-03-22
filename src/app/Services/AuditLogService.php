<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function logAdminAction(?int $actorAdminId, string $action, ?Model $target = null, array $metadata = []): void
    {
        if (! $actorAdminId) {
            return;
        }

        AuditLog::query()->create([
            'actor_admin_id' => $actorAdminId,
            'action' => $action,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'metadata' => $metadata,
        ]);
    }
}
