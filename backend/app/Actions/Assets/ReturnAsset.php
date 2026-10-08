<?php

namespace App\Actions\Assets;

use App\AssetStatus;
use App\AuditAction;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnAsset
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Asset $asset, User $actor, ?string $notes = null): Asset
    {
        return DB::transaction(function () use ($asset, $actor, $notes): Asset {
            $asset = Asset::query()->lockForUpdate()->findOrFail($asset->id);
            if ($asset->status !== AssetStatus::Assigned || $asset->assigned_user_id === null) {
                throw ValidationException::withMessages(['asset' => 'Only assigned assets can be returned.']);
            }

            $assignment = AssetAssignment::query()
                ->where('asset_id', $asset->id)
                ->whereNull('returned_at')
                ->lockForUpdate()
                ->latest('assigned_at')
                ->first();
            if (! $assignment) {
                throw ValidationException::withMessages(['asset' => 'The active assignment history is missing.']);
            }

            $previousUserId = $asset->assigned_user_id;
            $assignment->update([
                'returned_at' => now(),
                'returned_by' => $actor->id,
                'notes' => $notes ?? $assignment->notes,
            ]);
            $asset->update([
                'status' => AssetStatus::Available,
                'assigned_user_id' => null,
            ]);

            $this->auditLogger->log(
                AuditAction::AssetReturned,
                $asset,
                $actor,
                ['status' => AssetStatus::Assigned->value, 'assigned_user_id' => $previousUserId],
                ['status' => AssetStatus::Available->value, 'assigned_user_id' => null],
            );

            return $asset;
        }, 3);
    }
}
