<?php

namespace App\Support;

use App\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /** @param array<string, mixed>|null $oldValues @param array<string, mixed>|null $newValues */
    public function log(
        AuditAction $action,
        ?Model $entity,
        ?User $actor,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): AuditLog {
        return AuditLog::query()->create([
            'user_id' => $actor?->id,
            'action' => $action,
            'entity_type' => $entity ? $entity::class : null,
            'entity_id' => $entity?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
