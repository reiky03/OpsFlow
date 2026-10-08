<?php

namespace App\Actions\Assets;

use App\AssetStatus;
use App\AuditAction;
use App\Models\Asset;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignAsset
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Asset $asset, User $recipient, User $actor, ?string $notes = null): Asset
    {
        return DB::transaction(function () use ($asset, $recipient, $actor, $notes): Asset {
            $asset = Asset::query()->lockForUpdate()->findOrFail($asset->id);
            if ($asset->status !== AssetStatus::Available || $asset->assigned_user_id !== null) {
                throw ValidationException::withMessages(['asset' => 'Only available assets can be assigned.']);
            }

            $asset->update([
                'status' => AssetStatus::Assigned,
                'assigned_user_id' => $recipient->id,
            ]);
            $asset->assignments()->create([
                'user_id' => $recipient->id,
                'assigned_at' => now(),
                'assigned_by' => $actor->id,
                'notes' => $notes,
            ]);

            $this->auditLogger->log(
                AuditAction::AssetAssigned,
                $asset,
                $actor,
                ['status' => AssetStatus::Available->value, 'assigned_user_id' => null],
                ['status' => AssetStatus::Assigned->value, 'assigned_user_id' => $recipient->id],
            );
            $recipient->notify(new TicketEventNotification(
                'Asset assigned',
                "{$asset->asset_code} has been assigned to you.",
                ['asset_id' => $asset->id],
            ));

            return $asset;
        }, 3);
    }
}
