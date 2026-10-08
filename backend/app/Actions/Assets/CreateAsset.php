<?php

namespace App\Actions\Assets;

use App\AssetStatus;
use App\AuditAction;
use App\Models\Asset;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class CreateAsset
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data): Asset
    {
        return DB::transaction(function () use ($actor, $data): Asset {
            $asset = Asset::query()->create([
                ...$data,
                'asset_code' => 'PENDING-'.str()->uuid(),
                'status' => AssetStatus::Available,
            ]);
            $prefix = str($asset->category)->upper()->replaceMatches('/[^A-Z0-9]/', '')->substr(0, 3)->padRight(3, 'X');
            $asset->updateQuietly([
                'asset_code' => sprintf('AST-%s-%05d', $prefix, $asset->id),
            ]);

            $this->auditLogger->log(AuditAction::AssetCreated, $asset, $actor, newValues: [
                'asset_code' => $asset->asset_code,
                'name' => $asset->name,
                'status' => AssetStatus::Available->value,
            ]);

            return $asset;
        }, 3);
    }
}
